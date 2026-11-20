<?php

namespace App\Services;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Helpers\HasUploader;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TenantService
{
    use HasUploader;

    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($channel === Channel::Admin) {
            $query = User::with(['user_details', 'rents', 'rents.property:id', 'rents.property.propertyDetail:property_id,property_info'])
                ->where('role', 'tenant');

            if (! empty($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $query->whereHas('rents', function ($q) {
                        $q->where('status', 'active');
                    });
                } elseif ($filters['status'] === 'expired') {
                    $query->whereHas('rents', function ($q) {
                        $q->where('status', 'expired');
                    })->whereDoesntHave('rents', function ($q) {
                        $q->where('status', 'active');
                    });
                }
            } else {
                $query->whereDoesntHave('rents');
            }

            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('unique_id', 'like', '%'.$search.'%')
                        ->orWhere('profile_type', 'like', '%'.$search.'%')
                        ->orWhereHas('user_details', function ($q) use ($search) {
                            $q->where('gender', 'like', '%'.$search.'%');
                        });
                });
            }
        } elseif ($channel === Channel::Landlord) {
            $query = User::with(['user_details', 'rents', 'rents.property:id', 'rents.property.propertyDetail:property_id,property_info'])
                ->where('role', 'tenant')
                ->where('landlord_id', $actor?->id);

            if (! empty($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $query->whereHas('rents', function ($q) {
                        $q->where('status', 'active');
                    });
                } elseif ($filters['status'] === 'expired') {
                    $query->whereHas('rents', function ($q) {
                        $q->where('status', 'expired');
                    })->whereDoesntHave('rents', function ($q) {
                        $q->where('status', 'active');
                    });
                }
            }
            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            }
        } else {
            $landlordId = $actor?->id;
            $query = User::where('landlord_id', $landlordId);

            if (! empty($filters['status'])) {
                $query->whereHas('rents', function ($q) use ($filters) {
                    $q->where('status', $filters['status']);
                });
            }
            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('id', $search);
                });
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function store(array $data, Channel $channel, ?User $actor, array $files = []): User
    {
        // plan limit (Api only, as original)
        if ($channel === Channel::Api) {
            $plan = plan_data($actor?->id ? (string) $actor->id : null)?->plan;
            $tenantCount = User::where([['landlord_id', $actor?->id], ['role', 'tenant']])->count();
            if ($plan && $plan->getAttribute('number_of_tenant') !== -1 && $tenantCount >= $plan->getAttribute('number_of_tenant')) {
                throw new PlanLimitExceededException(__('Your Tenant addition limit has been reached. Your current limit is '.$plan->getAttribute('number_of_tenant')));
            }
        }

        $landlordId = $actor?->id;
        $isSetupped = $channel === Channel::Api ? 0 : 1;

        DB::beginTransaction();
        try {
            $image = null;
            if (! empty($files['image'])) {
                $image = $this->uploadFromFiles($files['image'], 'image');
            }

            $user = User::create([
                'landlord_id' => $landlordId,
                'image' => $image,
                'is_setupped' => $isSetupped,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password'] ?? ''),
                'profile_type' => $data['profile_type'] ?? null,
            ]);

            $user->role = 'tenant';
            $user->save();

            $frontImage = null;
            $backImage = null;
            if (! empty($files['mykad_front'])) {
                $frontImage = $this->uploadFromFiles($files['mykad_front'], 'mykad.front_image');
            }
            if (! empty($files['mykad_back'])) {
                $backImage = $this->uploadFromFiles($files['mykad_back'], 'mykad.back_image');
            }

            UserDetail::create([
                'user_id' => $user->id,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'mykad_id' => $data['mykad_id'] ?? null,
                'company_info' => $data['company_info'] ?? null,
                'address_info' => $data['address_info'] ?? null,
                'nominee_info' => $data['nominee_info'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'workplace' => $data['workplace'] ?? null,
                'vehicles_info' => $data['vehicles_info'] ?? null,
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            if ($channel === Channel::Api) {
                $code = random_int(100000, 999999);
                $expire = now()->addMinutes((int) (getenv('OTP_VISIBILITY_TIME') ?: 3));
                $user->update([
                    'remember_token' => $code,
                    'email_verified_at' => $expire,
                ]);
                $user->role = 'tenant';
                $user->save();

                $mailData = [
                    'code' => $code,
                    'name' => $data['name'] ?? null,
                ];

                if (getenv('QUEUE_MAIL')) {
                    Mail::to($data['email'])->queue(new WelcomeMail($mailData));
                } else {
                    Mail::to($data['email'])->send(new WelcomeMail($mailData));
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // notifications
        if ($channel === Channel::Api) {
            sendNotification($user->id, notify_users([$user->landlord_id, $user->id]), admin_url: route('admin.tenants.index', ['id' => $user->id]), landlord_url: route('landlord.tenants.index', ['id' => $user->id]), admin_msg: __('New tenant has been added'), landlord_msg: __('You have successfully created a new tenant.'), tenant_msg: __('Your account has been created'));
        } else {
            sendNotification($user->id, notify_users([$user->landlord_id]), admin_url: route('admin.tenants.index', ['id' => $user->id]), landlord_url: route('landlord.tenants.index', ['id' => $user->id]), admin_msg: __('New tenant has been added'), tenant_msg: __('You account has been successfully created.'), landlord_msg: __('New tenant has been added.'));
        }

        return $user->fresh(['user_details']);
    }

    protected function uploadFromFiles($file, string $context): ?string
    {
        // helper to upload a single UploadedFile using HasUploader's generated filename
        if ($file instanceof UploadedFile) {
            $filename = $this->generatedFilename($file);
            $path = 'uploads/'.now()->format('y/m');

            return $file->storeAs($path, $filename, config('filesystems.default'));
        }

        return null;
    }

    public function update(User $user, array $data, Channel $channel, ?User $actor, array $files = []): User
    {
        DB::beginTransaction();
        try {
            if (! empty($files['image'])) {
                if (! empty($user->image)) {
                    $this->deleteOldFile($user->image);
                }
                $image = $this->uploadFromFiles($files['image'], 'image');
            } else {
                $image = $user->image;
            }

            $user->update([
                'landlord_id' => $actor ? $actor->id : $user->landlord_id,
                'image' => $image,
                'is_setupped' => 1,
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? $user->phone,
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $user->password,
                'profile_type' => $data['profile_type'] ?? $user->profile_type,
            ]);

            $user->role = 'tenant';
            $user->save();

            $userDetail = $user->user_details instanceof UserDetail ? $user->user_details : null;
            if ($userDetail) {
                if (! empty($files['mykad_front'])) {
                    if (! empty($userDetail->mykad['front_image'])) {
                        $this->deleteOldFile($userDetail->mykad['front_image']);
                    }
                    $frontImage = $this->uploadFromFiles($files['mykad_front'], 'mykad.front_image');
                } else {
                    $frontImage = $userDetail->mykad['front_image'] ?? null;
                }
                if (! empty($files['mykad_back'])) {
                    if (! empty($userDetail->mykad['back_image'])) {
                        $this->deleteOldFile($userDetail->mykad['back_image']);
                    }
                    $backImage = $this->uploadFromFiles($files['mykad_back'], 'mykad.back_image');
                } else {
                    $backImage = $userDetail->mykad['back_image'] ?? null;
                }

                $userDetail->update([
                    'gender' => $data['gender'] ?? $userDetail->gender,
                    'birth_date' => $data['birth_date'] ?? $userDetail->birth_date,
                    'mykad_id' => $data['mykad_id'] ?? $userDetail->mykad_id,
                    'company_info' => $data['company_info'] ?? $userDetail->company_info,
                    'address_info' => $data['address_info'] ?? $userDetail->address_info,
                    'nominee_info' => $data['nominee_info'] ?? $userDetail->nominee_info,
                    'emergency_contact' => $data['emergency_contact'] ?? $userDetail->emergency_contact,
                    'workplace' => $data['workplace'] ?? $userDetail->workplace,
                    'vehicles_info' => $data['vehicles_info'] ?? $userDetail->vehicles_info,
                    'mykad' => [
                        'front_image' => $frontImage,
                        'back_image' => $backImage,
                    ],
                ]);
            } else {
                UserDetail::create([
                    'user_id' => $user->id,
                    'gender' => $data['gender'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'mykad_id' => $data['mykad_id'] ?? null,
                    'company_info' => $data['company_info'] ?? null,
                    'address_info' => $data['address_info'] ?? null,
                    'nominee_info' => $data['nominee_info'] ?? null,
                    'emergency_contact' => $data['emergency_contact'] ?? null,
                    'workplace' => $data['workplace'] ?? null,
                    'vehicles_info' => $data['vehicles_info'] ?? null,
                    'mykad' => [
                        'front_image' => null,
                        'back_image' => null,
                    ],
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $user->fresh(['user_details']);
    }

    public function delete(User $user, Channel $channel): void
    {
        DB::beginTransaction();
        try {
            if (! empty($user->image)) {
                $this->deleteOldFile($user->image);
            }

            $userDetail = $user->user_details instanceof UserDetail ? $user->user_details : null;
            if ($userDetail) {
                if (! empty($userDetail->mykad['front_image'])) {
                    $this->deleteOldFile($userDetail->mykad['front_image']);
                }
                if (! empty($userDetail->mykad['back_image'])) {
                    $this->deleteOldFile($userDetail->mykad['back_image']);
                }
                $userDetail->delete();
            }

            $user->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $user = User::find($id);
            if ($user) {
                $this->delete($user, $channel);
                $count++;
            }
        }

        return $count;
    }
}
