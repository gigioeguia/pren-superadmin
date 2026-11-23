<?php

namespace App\Services;

use App\Enums\Channel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dueño único de las tx de profile (spec-tx-legadas-services-fase5):
 * update (Admin/Landlord) + profileSetUp (Api).
 *
 * Divergencias HTTP preservadas (spec §4):
 *  - Admin: password condicional (solo si viene) + user_details
 *    updateOrCreate solo con gender; route-id ignorado (Seg-29).
 *  - Landlord: is_setupped + profile_type + password siempre presente
 *    (re-hash o la actual) + forceFill(landlord_id) + user_details
 *    create/update con mykad.
 *  - Api (profileSetUp): sin password + user_details create/update con
 *    company/nominee/emergency/workplace/vehicles.
 */
class ProfileService
{
    /**
     * Actualiza el perfil y sus user_details en una sola unidad atómica.
     *
     * @param  array{name?: string, email?: string, phone?: mixed, image?: ?string, gender?: mixed, profile_type?: ?string, birth_date?: mixed, mykad_id?: mixed, address_info?: mixed, mykad?: array{front_image?: ?string, back_image?: ?string}, password?: ?string, landlord_id?: int}  $data
     */
    public function update(User $user, array $data, Channel $channel): void
    {
        DB::beginTransaction();

        try {
            $attributes = [
                'image' => $data['image'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ];

            if ($channel === Channel::Admin) {
                if (! empty($data['password'])) {
                    $attributes['password'] = Hash::make($data['password']);
                }

                $user->update($attributes);

                $user->user_details()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['gender' => $data['gender']]
                );
            } else {
                $attributes['is_setupped'] = 1;
                $attributes['profile_type'] = $data['profile_type'];
                $attributes['password'] = $data['password'] ? Hash::make((string) $data['password']) : $user->password;

                $user->update($attributes);

                // Columna de tenancy fuera del contrato fillable (spec 5.11)
                $user->forceFill(['landlord_id' => $data['landlord_id']])->save();

                $details = [
                    'gender' => $data['gender'],
                    'birth_date' => $data['birth_date'],
                    'mykad_id' => $data['mykad_id'],
                    'address_info' => $data['address_info'],
                    'mykad' => [
                        'front_image' => $data['mykad']['front_image'] ?? null,
                        'back_image' => $data['mykad']['back_image'] ?? null,
                    ],
                ];

                if ($user->user_details) {
                    $user->user_details->update(['user_id' => $user->id] + $details);
                } else {
                    $user->user_details()->create(['user_id' => $user->id] + $details);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Profile setup API: usuario + user_details en una sola unidad atómica.
     *
     * @param  array{name?: string, email?: string, phone?: mixed, image?: ?string, profile_type?: ?string, gender?: mixed, birth_date?: mixed, mykad_id?: mixed, company_info?: mixed, address_info?: mixed, nominee_info?: mixed, emergency_contact?: mixed, workplace?: mixed, vehicles_info?: mixed, mykad?: array{front_image?: ?string, back_image?: ?string}}  $data
     */
    public function profileSetUp(User $user, array $data): void
    {
        DB::beginTransaction();

        try {
            $user->update([
                'image' => $data['image'],
                'is_setupped' => 1,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'profile_type' => $data['profile_type'],
            ]);

            $details = [
                'user_id' => $user->id,
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'mykad_id' => $data['mykad_id'],
                'company_info' => $data['company_info'],
                'address_info' => $data['address_info'],
                'nominee_info' => $data['nominee_info'],
                'emergency_contact' => $data['emergency_contact'],
                'workplace' => $data['workplace'],
                'vehicles_info' => $data['vehicles_info'],
                'mykad' => [
                    'front_image' => $data['mykad']['front_image'] ?? null,
                    'back_image' => $data['mykad']['back_image'] ?? null,
                ],
            ];

            if ($user->user_details) {
                $user->user_details->update($details);
            } else {
                $user->user_details()->create($details);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
