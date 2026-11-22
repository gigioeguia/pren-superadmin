<?php

namespace App\Services;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Dueño único de la lógica de landlord (spec-service-landlord-fase5): CRUD
 * admin + creación de cuenta por canal (web signup / api registration).
 *
 * Divergencias HTTP preservadas (tabla §4 del spec):
 *  - store (admin): User::create + UserDetail + trío free-plan de 8 campos.
 *  - createAccount (web/api): updateOrCreate + `is_setupped` solo en web;
 *    SIN UserDetail, SIN trío, SIN mail — los controladores orquestan OTP
 *    y envían WelcomeMail post-commit (Fase 4 / 4.8).
 *  - activateFreePlan: campos por canal (Admin 8 / Landlord 5 / Api 3).
 *    El canal api deja payment_status en su default 'unpaid' (quirk
 *    congelado por F11a); admin/web lo setean 'paid'.
 *  - activateFreePlan no abre transacción: la unidad atómica la define el
 *    caller (store/maanRegistration envuelven; submitOtp va sin tx, igual
 *    que el original).
 *  - delete/deleteMany: media capturada dentro de la tx, borrado
 *    best-effort tras el commit vía Storage::exists (spec §5).
 */
class LandlordService
{
    use HasUploader;

    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::with(['user_details', 'plan_subscribe:id,plan_name,number_of_property,number_of_tenant,number_of_labor'])
            ->where('role', 'landlord')
            ->when(! empty($filters['id']), function (Builder $query) use ($filters) {
                $query->where('id', $filters['id']);
            })
            ->when(($filters['status'] ?? null) === 'active', function (Builder $query) {
                $query->whereDate('will_expire', '>=', today());
            })
            ->when(($filters['status'] ?? null) === 'expired', function (Builder $query) {
                $query->whereDate('will_expire', '<=', today());
            })
            ->latest()
            ->paginate(10);
    }

    /**
     * Listado del endpoint filter (búsqueda por nombre/email/género).
     */
    public function listForFilter(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return User::with('user_details')
            ->where('role', 'landlord')
            ->when(! empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('user_details', function (Builder $query) use ($search) {
                            $query->where('gender', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Primer plan con precio <= 0 (lookup compartido admin/api).
     */
    public function freePlan(): ?Plan
    {
        return Plan::where('subscriptionPrice', '<=', 0)
            ->orWhere('offerPrice', '<=', 0)
            ->first();
    }

    public function store(array $data, array $files = []): User
    {
        // Uploads antes de abrir la transacción (Fase 4 / 4.8)
        $image = $this->uploadFromFiles($files['image'] ?? null);
        $frontImage = $this->uploadFromFiles($files['mykad_front'] ?? null);
        $backImage = $this->uploadFromFiles($files['mykad_back'] ?? null);

        DB::beginTransaction();

        try {
            $user = User::create([
                'image' => $image,
                'is_setupped' => 1,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            // `role`: asignación server-side (Seg-28 / spec 5.11)
            $user->role = 'landlord';
            $user->save();

            UserDetail::create([
                'user_id' => $user->id,
                'gender' => $data['gender'] ?? null,
                'mykad_id' => $data['mykad_id'] ?? null,
                'address_info' => $data['address_info'] ?? null,
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            $this->activateFreePlan($user, Channel::Admin, $this->freePlan());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    public function update(User $landlord, array $data, array $files = []): User
    {
        // Uploads antes de abrir la transacción; directorios viejos se borran tras el commit (Fase 4 / 4.8)
        $oldDirectories = [];

        $image = $landlord->image;
        if (isset($files['image'])) {
            if (! empty($landlord->image)) {
                $oldDirectories[] = dirname($landlord->image);
            }
            $image = $this->uploadFromFiles($files['image']);
        }

        $detail = $landlord->user_details instanceof UserDetail ? $landlord->user_details : null;
        $frontImage = $detail->getAttribute('mykad')['front_image'] ?? null;
        $backImage = $detail->getAttribute('mykad')['back_image'] ?? null;

        if (isset($files['mykad_front'])) {
            if (! empty($frontImage)) {
                $oldDirectories[] = dirname($frontImage);
            }
            $frontImage = $this->uploadFromFiles($files['mykad_front']);
        }

        if (isset($files['mykad_back'])) {
            if (! empty($backImage)) {
                $oldDirectories[] = dirname($backImage);
            }
            $backImage = $this->uploadFromFiles($files['mykad_back']);
        }

        DB::beginTransaction();

        try {
            $landlord->update([
                'image' => $image,
                'is_setupped' => 1,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $landlord->password,
            ]);

            // El update original crasheaba (500) sin user_details; guard
            // explícito — desviación documentada en spec §11.
            if ($detail) {
                $detail->update([
                    'user_id' => $landlord->id,
                    'gender' => $data['gender'] ?? null,
                    'mykad_id' => $data['mykad_id'] ?? null,
                    'address_info' => $data['address_info'] ?? null,
                    'mykad' => [
                        'front_image' => $frontImage,
                        'back_image' => $backImage,
                    ],
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Borrado best-effort tras el commit
        foreach ($oldDirectories as $directory) {
            Storage::deleteDirectory($directory);
        }

        return $landlord->fresh();
    }

    public function delete(User $landlord): void
    {
        // Rutas capturadas antes de la transacción; borrado tras el commit (Fase 4 / 4.8)
        $oldPaths = [];

        if ($landlord->image) {
            $oldPaths[] = $landlord->image;
        }

        $userDetail = $landlord->user_details instanceof UserDetail ? $landlord->user_details : null;

        if ($userDetail) {
            $frontImage = $userDetail->getAttribute('mykad')['front_image'] ?? null;
            $backImage = $userDetail->getAttribute('mykad')['back_image'] ?? null;

            if ($frontImage) {
                $oldPaths[] = $frontImage;
            }
            if ($backImage) {
                $oldPaths[] = $backImage;
            }
        }

        DB::beginTransaction();

        try {
            // user_details cae por FK cascade (comportamiento actual del destroy)
            $landlord->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Borrado best-effort tras el commit — Storage::exists (spec §5, ex file_exists)
        foreach ($oldPaths as $path) {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }

    public function deleteMany(array $ids): int
    {
        // Rutas capturadas dentro de la transacción; borrado tras el commit (Fase 4 / 4.8)
        $oldPaths = [];

        DB::beginTransaction();

        try {
            $landlords = User::whereIn('id', $ids)->get();

            foreach ($landlords as $landlord) {
                if ($landlord->image) {
                    $oldPaths[] = $landlord->image;
                }

                $userDetail = $landlord->user_details()->first();

                if ($userDetail) {
                    $frontImage = $userDetail->getAttribute('mykad')['front_image'] ?? null;
                    $backImage = $userDetail->getAttribute('mykad')['back_image'] ?? null;

                    if ($frontImage) {
                        $oldPaths[] = $frontImage;
                    }
                    if ($backImage) {
                        $oldPaths[] = $backImage;
                    }

                    // deleteMany sí borra el user_detail explícitamente (comportamiento actual)
                    $userDetail->delete();
                }

                $landlord->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Borrado best-effort tras el commit — Storage::exists (spec §5, ex file_exists)
        foreach ($oldPaths as $path) {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }

        return $landlords->count();
    }

    /**
     * Creación de la cuenta landlord (web signup / api registration).
     *
     * @param array{name?: string, email?: string, password?: string} $data claves
     *      explícitas; cualquier otra clave del payload se ignora (Seg-28).
     * @param string|null $otpCode token OTP server-side (null cuando OTP está off)
     * @param CarbonInterface|null $expiresAt expiración del OTP (null → verified now)
     */
    public function createAccount(array $data, Channel $channel, ?string $otpCode = null, ?CarbonInterface $expiresAt = null): User
    {
        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        // `is_setupped`: 1 en web (comportamiento actual), default en api
        if ($channel === Channel::Landlord) {
            $attributes['is_setupped'] = 1;
        }

        $user = User::updateOrCreate(['email' => $data['email']], $attributes);

        // `role` no es fillable; asignación server-side (Seg-28 / spec 5.11)
        $user->role = 'landlord';

        // Columnas fuera del contrato fillable: exclusivas server-side
        $user->forceFill([
            'remember_token' => $otpCode,
            'email_verified_at' => $expiresAt ?? now(),
        ])->save();

        return $user;
    }

    /**
     * Única fuente del trío free-plan (DoD: grep PlanSubscribe::create en
     * controllers = 0): PlanSubscribe con campos por canal + forceFill del
     * trío. Sin plan → null, sin efectos.
     */
    public function activateFreePlan(User $user, Channel $channel, ?Plan $plan = null): ?PlanSubscribe
    {
        if (! $plan) {
            return null;
        }

        $duration = $plan->durationInDays();

        $attributes = match ($channel) {
            Channel::Admin => [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'duration' => $duration,
                'plan_name' => $plan->subscriptionName,
                'number_of_property' => $plan->number_of_property,
                'number_of_tenant' => $plan->number_of_tenant,
                'number_of_labor' => $plan->number_of_labor,
                'payment_status' => 'paid',
            ],
            Channel::Landlord => [
                'plan_id' => $plan->id,
                'plan_name' => $plan->subscriptionName,
                'user_id' => $user->id,
                'duration' => $duration,
                'payment_status' => 'paid',
            ],
            Channel::Api => [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'duration' => $duration,
            ],
        };

        $subscribe = PlanSubscribe::create($attributes);

        $user->forceFill([
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addDays($duration),
            'subscriptionDate' => now(),
        ])->save();

        return $subscribe;
    }

    /**
     * Upload de un único UploadedFile con nombre generado (patrón TenantService).
     */
    protected function uploadFromFiles(UploadedFile|null $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $filename = $this->generatedFilename($file);
        $path = 'uploads/'.now()->format('y/m');

        return $file->storeAs($path, $filename, config('filesystems.default'));
    }
}
