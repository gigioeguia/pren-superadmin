<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Implementación única de filter/deleteAll/status/exportExcel/exportCsv
 * (DT-C3, spec del trait de la Fase 0 — índice en `.agents/README.md`). Los 5
 * métodos públicos conservan el
 * comportamiento observable exacto de los métodos duplicados que reemplazan;
 * cada controlador configura solo los hooks que sus rutas usan.
 */
trait HasTableActions
{
    public function filter(Request $request)
    {
        $rows = $this->query($request)->paginate($request->per_page ?? $this->perPage());

        if ($request->ajax()) {
            return response()->json([
                'data' => view($this->view(), $this->viewData($rows))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    // NOTA DE SEGURIDAD — Seg-5 (hallazgo 5 de .analisis/seguridad.md): lookup
    // SIN ownership scoping, preservado 1:1 de las copias originales por
    // contrato del spec del trait de la Fase 0 (§5 preservación estricta; el fix
    // está en OUT → Fase 2, plan-accion 2.1, con Policies). El fix se aplicará
    // aquí una sola vez; el test H3 (@group characterization) está diseñado
    // para romper cuando llegue. No añadir scoping sin diseñar la separación
    // Admin (sin scope) / Landlord (scoped) — un cambio global rompería admin.
    public function status(Request $request, string $id)
    {
        $this->model()::findOrFail($id)->update(['status' => $request->status]);

        $payload = ['message' => $this->statusMessage()];

        if ($route = $this->statusRedirectRoute()) {
            $payload['redirect'] = route($route);
        }

        return response()->json($payload);
    }

    // NOTA DE SEGURIDAD — Seg-5: whereIn SIN ownership scoping, ídem status()
    // arriba (preexistente en las 33+19 copias; fix en Fase 2, plan-accion 2.1;
    // caracterizado por H3).
    public function deleteAll(Request $request)
    {
        // DT-M10 (Fase 2 / 2.6): la media se captura ANTES del delete (las
        // relaciones con cascadeOnDelete — propertyDetail, units — desaparecen
        // tras él) y se limpia DESPUÉS del delete exitoso. Si `$row->delete()`
        // lanza, los callables no se ejecutan y la media se preserva.
        if ($this->deletesRowByRow()) {
            $this->model()::whereIn('id', $request->ids)->get()
                ->each(function (Model $row) {
                    $cleanup = $this->deletingRow($row);

                    $row->delete();

                    foreach ($cleanup as $callback) {
                        if ($callback instanceof \Closure) {
                            $callback();
                        }
                    }
                });
        } else {
            $this->model()::whereIn('id', $request->ids)->delete();
        }

        return response()->json([
            'message' => $this->deleteMessage(),
            'redirect' => route($this->redirectRoute()),
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download($this->export($request), $this->excelFilename().'.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download($this->export($request), $this->csvFilename().'.csv');
    }

    // Hooks obligatorios para todo adoptante.

    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    abstract protected function query(Request $request): Builder;

    abstract protected function view(): string;

    abstract protected function viewData(LengthAwarePaginator $rows): array;

    // Hooks con default real (el valor común de la mayoría de adoptantes).

    protected function perPage(): int
    {
        return 10;
    }

    protected function statusRedirectRoute(): ?string
    {
        return null;
    }

    protected function deletesRowByRow(): bool
    {
        return false;
    }

    /**
     * Captura la media de la fila y devuelve la lista de callables que la
     * limpian. Los callables se ejecutan DESPUÉS de `$row->delete()` exitoso.
     *
     * @return array<int, \Closure>
     */
    protected function deletingRow(Model $row): array
    {
        return [];
    }

    // Hooks requeridos solo si la ruta correspondiente existe en el controlador.

    protected function redirectRoute(): string
    {
        throw new \LogicException(static::class.' debe definir redirectRoute() si usa deleteAll().');
    }

    protected function statusMessage(): string
    {
        throw new \LogicException(static::class.' debe definir statusMessage() si usa status().');
    }

    protected function deleteMessage(): string
    {
        throw new \LogicException(static::class.' debe definir deleteMessage() si usa deleteAll().');
    }

    protected function export(Request $request): object
    {
        throw new \LogicException(static::class.' debe definir export() si usa exportExcel()/exportCsv().');
    }

    protected function excelFilename(): string
    {
        throw new \LogicException(static::class.' debe definir excelFilename() si usa exportExcel().');
    }

    protected function csvFilename(): string
    {
        throw new \LogicException(static::class.' debe definir csvFilename() si usa exportCsv().');
    }
}
