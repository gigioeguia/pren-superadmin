<?php

namespace App\Services;

use App\Helpers\HasUploader;
use App\Models\Language;
use App\Support\CacheKeys;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class LanguageService
{
    use HasUploader;

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Language::orderBy('order', 'asc')
            ->when(! empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function store(array $data): Language
    {
        $icon = $data['icon'] ?? null;
        $iconPath = $icon instanceof UploadedFile ? $this->uploadIcon($icon) : null;

        $language = Language::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'order' => $data['order'],
            'icon' => $iconPath,
        ]);

        // Copia del JSON base tras crear la fila (E/S no DB, Fase 4 / 4.8).
        $destination = base_path("lang/{$language->code}.json");
        if (! File::exists($destination)) {
            File::copy(base_path('lang/en.json'), $destination);
        }

        return $language;
    }

    public function update(Language $language, array $data): Language
    {
        // El JSON se mueve antes de tocar la fila (E/S no DB, Fase 4 / 4.8).
        if ($language->code !== $data['code']) {
            $oldPath = base_path("lang/{$language->code}.json");
            $newPath = base_path("lang/{$data['code']}.json");

            if (File::exists($oldPath)) {
                File::move($oldPath, $newPath);
            }
        }

        $icon = $data['icon'] ?? null;
        $iconPath = $icon instanceof UploadedFile
            ? $this->uploadIcon($icon, $language->icon)
            : $language->icon;

        $language->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'order' => $data['order'],
            'icon' => $iconPath,
        ]);

        return $language;
    }

    public function delete(Language $language): void
    {
        // Quirk preservado (spec-service-language-fase5 §5.1): el guard
        // file_exists() original resolvía relativo al CWD y nunca coincidía
        // con la ruta del disk — el icono no se borra hoy, no se toca.
        $langPath = base_path("lang/{$language->code}.json");
        if (File::exists($langPath)) {
            File::delete($langPath);
        }

        $language->delete();
    }

    public function deleteMany(array $ids): int
    {
        $languages = Language::whereIn('id', $ids)->where('is_default', 0)->get();

        // Desviación aprobada (spec §5): filas en tx primero, archivos tras
        // el commit (best-effort). Antes se borraban archivos antes que filas.
        $langPaths = $languages
            ->map(fn (Language $language) => base_path("lang/{$language->code}.json"))
            ->all();

        DB::transaction(fn () => $languages->each->delete());

        foreach ($langPaths as $langPath) {
            if (File::exists($langPath)) {
                File::delete($langPath);
            }
        }

        return $languages->count();
    }

    public function setStatus(Language $language, int $status): Language
    {
        // Guard is_default (spec §4): la default no se desactiva.
        if ($language->is_default) {
            return $language;
        }

        $language->update(['status' => $status]);

        return $language;
    }

    public function setDefault(string $id): ?Language
    {
        // Quirk preservado (spec §5.2): id inexistente corre la tx igual
        // y no invalida caché (find-or-null del controlador original).
        $language = Language::find($id);

        DB::transaction(function () use ($id) {
            Language::query()->update([
                'is_default' => 0,
            ]);

            Language::where('id', $id)->update([
                'is_default' => 1,
                'order' => 0,
                'created_at' => now(),
            ]);
        });

        // Excepción masiva (spec 4.9, Q15a): los updates masivos no
        // disparan observers; invalidación explícita tras el commit.
        if ($language) {
            CacheKeys::forgetModel($language);
        }

        return $language;
    }

    /**
     * Entradas de traducción de lang/{code}.json para la vista show.
     *
     * @return array{language: Language, entries: Collection<int, array{key: string, value: string}>}|null
     */
    public function translations(string $code, ?string $search = null): ?array
    {
        $language = Language::where('code', $code)->firstOrFail();

        $path = base_path("lang/{$code}.json");
        if (! File::exists($path)) {
            return null;
        }

        $translations = json_decode(File::get($path), true) ?? [];

        if ($search !== null && $search !== '') {
            $needle = strtolower($search);
            $translations = array_filter(
                $translations,
                fn ($value, $key) => str_contains(strtolower((string) $key), $needle)
                    || str_contains(strtolower((string) $value), $needle),
                ARRAY_FILTER_USE_BOTH
            );
        }

        $entries = collect($translations)
            ->map(fn ($value, $key) => ['key' => (string) $key, 'value' => (string) $value])
            ->values();

        return ['language' => $language, 'entries' => $entries];
    }

    protected function uploadIcon(UploadedFile $icon, ?string $oldIcon = null): string
    {
        $filePath = $icon->storeAs(
            'uploads/'.now()->format('y/m'),
            $this->generatedFilename($icon),
            config('filesystems.default')
        );

        if ($filePath === false) {
            throw new RuntimeException('Icon upload failed.');
        }

        $this->deleteOldFile($oldIcon);

        return $filePath;
    }
}
