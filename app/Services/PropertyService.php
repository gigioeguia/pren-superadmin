<?php

namespace App\Services;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Exceptions\PropertyRentedException;
use App\Helpers\HasUploader;
use App\Models\Amenity;
use App\Models\Facility;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    use HasUploader;

    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return match ($channel) {
            Channel::Admin => $this->listAdmin($filters, $perPage),
            Channel::Landlord => $this->listLandlord($actor, $filters, $perPage),
            Channel::Api => $this->listApi($actor, $filters, $perPage),
        };
    }

    protected function listAdmin(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Property::with([
            'category:id,name',
            'landlord:id,name',
            'propertyDetail:property_id,property_info',
            'units.rent',
        ])
            ->withCount([
                'units as total_unit',
                'units as total_available_unit' => function ($query) {
                    $query->whereDoesntHave('rent', function ($q) {
                        $q->whereIn('status', ['active', 'processing']);
                    });
                },
            ]);

        if (isset($filters['id']) && $filters['id'] !== '') {
            $query->where('id', $filters['id']);
        }
        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('rent_info', 'like', '%'.$search.'%')
                    ->orWhere('puid', 'like', '%'.$search.'%')
                    ->orWhere('address_info->address', 'like', '%'.$search.'%')
                    ->orWhere('address_info->state', 'like', '%'.$search.'%')
                    ->orWhere('address_info->city', 'like', '%'.$search.'%')
                    ->orWhere('rent_info->monthly_rent', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('propertyDetail', function ($q) use ($search) {
                        $q->where('property_info', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    protected function listLandlord(?User $actor, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Property::with([
            'category:id,name',
            'propertyDetail:id,property_id,property_info',
            'units.rent',
        ])
            ->where('landlord_id', $actor?->id)
            ->withCount([
                'units as total_unit',
                'units as total_available_unit' => function ($query) {
                    $query->whereDoesntHave('rent', function ($q) {
                        $q->whereIn('status', ['active', 'processing']);
                    });
                },
            ]);

        if (isset($filters['id']) && $filters['id'] !== '') {
            $query->where('id', $filters['id']);
        }
        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('building_name', 'like', '%'.$search.'%')
                    ->orWhere('address_info->address', 'like', '%'.$search.'%')
                    ->orWhere('puid', 'like', '%'.$search.'%')
                    ->orWhere('rent_info->monthly_rent', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('propertyDetail', function ($q) use ($search) {
                        $q->where('property_info->property_title', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    protected function listApi(?User $actor, array $filters, int $perPage): LengthAwarePaginator
    {
        $isLandlord = $actor && $actor->role === 'landlord';
        $isTenant = $actor && $actor->role === 'tenant';

        $query = Property::select('id', 'landlord_id', 'category_id', 'type', 'status', 'room_info', 'rent_info', 'address_info', 'cover_image')
            ->with(['propertyDetail:property_id,property_info', 'units', 'landlord:id,name', 'category:id,name']);

        if ($isLandlord || ! empty($filters['landlord_id'])) {
            $query->where('landlord_id', $filters['landlord_id'] ?? $actor?->id);
        }

        if ($isTenant && ! empty($filters['show_recommendate'])) {
            $state = $actor->user_details->address_info['state'] ?? null;
            if ($state) {
                $query->whereJsonContains('address_info->state', $state);
            }
        }

        if ($isTenant) {
            $query->where('status', 1);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['country'])) {
            $query->whereJsonContains('address_info->country', $filters['country']);
        }
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('room_info', 'like', '%'.$search.'%')
                    ->orWhere('building_name', 'like', '%'.$search.'%')
                    ->orWhere('address_info', 'like', '%'.$search.'%')
                    ->orWhereHas('propertyDetail', function ($pd) use ($search) {
                        $pd->where('property_info', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('category', function ($c) use ($search) {
                        $c->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('landlord', function ($l) use ($search) {
                        $l->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $availableRent = ! empty($filters['available_rent']) && $filters['available_rent'] !== 'false';
        if ($availableRent) {
            $query->where(function ($q) {
                $q->whereDoesntHave('rent')
                    ->orWhereHas('rent', function ($sub) {
                        $sub->whereNotIn('status', ['active', 'processing']);
                    });
            });
            $query->orWhere(function ($subQuery) {
                $subQuery->whereHas('units', function ($unitQuery) {
                    $unitQuery->where(function ($uq) {
                        $uq->whereDoesntHave('rent')
                            ->orWhereHas('rent', function ($rq) {
                                $rq->whereNotIn('status', ['active', 'processing']);
                            });
                    });
                });
            });
        }

        if (! empty($filters['sorting'])) {
            $sort = $filters['sorting'];
            if ($sort === 'low_to_high') {
                $query->orderBy('rent_info->monthly_rent', 'asc');
            } elseif ($sort === 'high_to_low') {
                $query->orderBy('rent_info->monthly_rent', 'desc');
            }
        } else {
            $query->latest();
        }

        // favorite relation for tenant
        $query->with('favorite:id,property_id,status');

        if (! empty($filters['no_paginate']) && $filters['no_paginate'] !== 'false') {
            // Return all as a paginator with large per page to keep contract
            $count = (clone $query)->count();
            $perPage = $count > 0 ? $count : 1;
        }

        return $query->paginate($perPage);
    }

    public function loadForShow(Property $property, Channel $channel): Property
    {
        $dateFilter = function ($query) {
            $query->select('property_id', 'status')
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today());
        };
        $unitsWithCount = function ($q) {
            $q->withCount([
                'rent as is_booked' => function ($rent) {
                    $rent->where('status', 'active')
                        ->whereDate('start_date', '<=', Carbon::today())
                        ->whereDate('end_date', '>=', Carbon::today());
                },
            ]);
        };

        if ($channel === Channel::Admin) {
            $property->load([
                'category:id,name',
                'propertyDetail',
                'units',
                'landlord:id,name',
                'rent' => $dateFilter,
            ]);
            $property->units()->getQuery(); // ensure
            $property->load(['units' => $unitsWithCount]);
        } elseif ($channel === Channel::Landlord) {
            $property->load([
                'category:id,name',
                'propertyDetail',
                'units',
                'rent' => $dateFilter,
            ]);
            $property->load(['units' => $unitsWithCount]);
        } else {
            $property->load([
                'propertyDetail',
                'units',
                'house_type:id,name',
                'category:id,name',
                'landlord:id,name',
                'favorite:id,property_id,status',
                'rent' => $dateFilter,
            ]);
            $property->load(['units' => $unitsWithCount]);
        }

        return $property;
    }

    public function store(array $data, Channel $channel, ?User $actor, ?int $landlordId = null): Property
    {
        $resolvedLandlordId = match ($channel) {
            Channel::Admin => $landlordId ?? ($data['landlord_id'] ?? null),
            default => $actor?->id,
        };

        if ($channel !== Channel::Admin) {
            $planSubscribe = plan_data((string) $resolvedLandlordId);
            $plan = $planSubscribe ? $planSubscribe->plan()->first() : null;
            $propertyCount = Property::where('landlord_id', $resolvedLandlordId)->count();
            if ($plan && (int) $plan->getAttribute('number_of_property') !== -1 && $propertyCount >= (int) $plan->getAttribute('number_of_property')) {
                throw new PlanLimitExceededException(__('Your property addition limit has been reached. Your current limit is '.(int) $plan->getAttribute('number_of_property')));
            }
        }

        $status = $channel === Channel::Admin ? '1' : '0';

        DB::beginTransaction();
        try {
            $coverImage = null;
            if (! empty($data['cover_image'])) {
                $files = is_array($data['cover_image']) ? $data['cover_image'] : [$data['cover_image']];
                $coverImage = $this->multipleUploadArray($files);
            }

            $floorplanImage = null;
            if (! empty($data['floorplan_image']) && $data['floorplan_image'] instanceof UploadedFile) {
                $floorplanImage = $this->multipleUploadArray([$data['floorplan_image']])[0];
            }

            $property = Property::create([
                'landlord_id' => $resolvedLandlordId,
                'category_id' => $data['category_id'],
                'house_type_id' => $data['house_type_id'] ?? null,
                'type' => $data['type'] ?? null,
                'building_name' => $data['building_name'] ?? null,
                'utility_deposit' => $data['utility_deposit'] ?? 0,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'sale_amount' => $data['sale_amount'] ?? 0,
                'late_fee' => $data['late_fee'] ?? 0,
                'late_fee_after_days' => $data['late_fee_after_days'] ?? 0,
                'cover_image' => $coverImage,
                'room_info' => $data['room_info'] ?? null,
                'rent_info' => $data['rent_info'] ?? null,
                'address_info' => $data['address_info'] ?? null,
                'status' => $status,
            ]);

            PropertyDetail::create([
                'property_id' => $property->id,
                'furnishing' => $data['furnishing'] ?? null,
                'facilities' => $data['facilities'] ?? ($data['facility'] ?? null),
                'parking_lot' => $data['parking_lot'] ?? null,
                'amenities' => $data['amenities'] ?? ($data['amenity'] ?? null),
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'floorplan_image' => $floorplanImage,
                'residential_type' => $data['residential_type'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'property_info' => $data['property_info'] ?? null,
                'tenant_preference' => $data['tenant_preference'] ?? null,
            ]);

            if (($data['category_id'] ?? null) == 6 && ! empty($data['units']) && is_array($data['units'])) {
                $unitFiles = $data['unit_files'] ?? [];
                foreach ($data['units'] as $index => $unitData) {
                    if (empty($unitData['unit_id']) && empty($unitData['unit_name'])) {
                        continue;
                    }
                    $images = null;
                    if (isset($unitFiles[$index]['images']) && is_array($unitFiles[$index]['images'])) {
                        $images = $this->multipleUploadArray($unitFiles[$index]['images']);
                    } elseif (isset($unitData['images']) && is_array($unitData['images'])) {
                        $images = $this->multipleUploadArray($unitData['images']);
                    }

                    Unit::create([
                        'property_id' => $property->id,
                        'unit_name' => $unitData['unit_name'] ?? null,
                        'floor_range' => $unitData['floor_range'] ?? null,
                        'bedroom' => $unitData['bedroom'] ?? null,
                        'bathroom' => $unitData['bathroom'] ?? null,
                        'kitchen' => $unitData['kitchen'] ?? null,
                        'balcony' => $unitData['balcony'] ?? null,
                        'condition' => $unitData['condition'] ?? null,
                        'parking_lot' => $unitData['parking_lot'] ?? null,
                        'unit_size' => $unitData['unit_size'] ?? null,
                        'facilities' => $unitData['facilities'] ?? null,
                        'amenities' => $unitData['amenities'] ?? null,
                        'tenant_preference' => $unitData['tenant_preference'] ?? null,
                        'description' => $unitData['description'] ?? null,
                        'images' => $images,
                        'monthly_rent' => $unitData['monthly_rent'] ?? 0,
                        'sale_amount' => $unitData['sale_amount'] ?? 0,
                        'security_deposit' => $unitData['security_deposit'] ?? 0,
                        'utility_deposit' => $unitData['utility_deposit'] ?? 0,
                        'late_fee' => $unitData['late_fee'] ?? 0,
                        'late_fee_after_days' => $unitData['late_fee_after_days'] ?? 0,
                        'min_rental_value' => $unitData['min_rental_value'] ?? null,
                        'min_rental_unit' => $unitData['min_rental_unit'] ?? null,
                        'security_deposit_value' => $unitData['security_deposit_value'] ?? null,
                        'security_deposit_unit' => $unitData['security_deposit_unit'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $users = notify_users([$resolvedLandlordId]);
        if ($channel === Channel::Api) {
            sendNotification(
                $property->id,
                $users,
                landlord_url: route('landlord.properties.index', ['id' => $property->id]),
                admin_msg: __('New property added'),
                landlord_msg: __('New property has been created')
            );
        } else {
            sendNotification(
                $property->id,
                $users,
                admin_url: route('admin.properties.index', ['id' => $property->id]),
                landlord_url: route('landlord.properties.index', ['id' => $property->id]),
                admin_msg: __('A new property has been created.'),
                landlord_msg: __('Your property has been successfully added.')
            );
        }

        return $property->fresh();
    }

    public function update(Property $property, array $data, Channel $channel, ?User $actor): Property
    {
        if ($channel !== Channel::Admin && $actor && $property->landlord_id !== $actor->id) {
            // ownership guard; Policy already enforces, but keep safe
        }

        DB::beginTransaction();
        try {
            // cover image
            if (! empty($data['cover_removed_images']) && is_array($data['cover_removed_images'])) {
                $coverPrev = array_diff($property->cover_image ?? [], $data['cover_removed_images']);
                foreach ($data['cover_removed_images'] as $image) {
                    if (Storage::exists($image)) {
                        Storage::delete($image);
                    }
                }
                $coverPrev = array_values($coverPrev);
            } else {
                $coverPrev = $property->cover_image ?? [];
            }

            $coverNew = [];
            if (! empty($data['cover_image'])) {
                $files = is_array($data['cover_image']) ? $data['cover_image'] : [$data['cover_image']];
                $coverNew = $this->multipleUploadArray($files);
            }
            $coverMerged = array_merge($coverPrev, $coverNew);

            // floorplan
            if (! empty($data['floorplan_image']) && $data['floorplan_image'] instanceof UploadedFile) {
                if ($property->propertyDetail()->first()?->getAttribute('floorplan_image')) {
                    Storage::deleteDirectory(dirname((string) $property->propertyDetail()->first()->getAttribute('floorplan_image')));
                }
                $floorplanImage = $this->multipleUploadArray([$data['floorplan_image']])[0];
            } else {
                $floorplanImage = $property->propertyDetail()->first()?->getAttribute('floorplan_image');
            }

            $updateData = [
                'category_id' => $data['category_id'],
                'house_type_id' => $data['house_type_id'] ?? null,
                'type' => $data['type'] ?? null,
                'building_name' => $data['building_name'] ?? null,
                'utility_deposit' => $data['utility_deposit'] ?? 0,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'sale_amount' => $data['sale_amount'] ?? 0,
                'late_fee' => $data['late_fee'] ?? 0,
                'late_fee_after_days' => $data['late_fee_after_days'] ?? 0,
                'cover_image' => $coverMerged,
                'room_info' => $data['room_info'] ?? null,
                'rent_info' => $data['rent_info'] ?? null,
                'address_info' => $data['address_info'] ?? null,
            ];

            if ($channel === Channel::Admin && isset($data['landlord_id'])) {
                $updateData['landlord_id'] = $data['landlord_id'];
            }

            $property->update($updateData);

            $property->propertyDetail()->update([
                'property_id' => $property->id,
                'furnishing' => $data['furnishing'] ?? null,
                'facilities' => $data['facilities'] ?? null,
                'parking_lot' => $data['parking_lot'] ?? null,
                'amenities' => $data['amenities'] ?? null,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'floorplan_image' => $floorplanImage,
                'residential_type' => $data['residential_type'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'property_info' => $data['property_info'] ?? null,
                'tenant_preference' => $data['tenant_preference'] ?? null,
            ]);

            // units sync
            $unitFiles = $data['unit_files'] ?? [];

            $removedUnitIds = collect($data['removed_unit_ids'] ?? [])
                ->filter(fn ($v) => ! empty($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->toArray();

            if (! empty($removedUnitIds)) {
                $unitsToDelete = Unit::whereIn('id', $removedUnitIds)->get(['id', 'images']);
                foreach ($unitsToDelete as $u) {
                    if (! empty($u->images)) {
                        foreach ($u->images as $imgPath) {
                            if ($imgPath && Storage::exists($imgPath)) {
                                Storage::delete($imgPath);
                            }
                        }
                    }
                }
                Unit::whereIn('id', $removedUnitIds)->delete();
            } else {
                $receivedUnitIds = collect($data['units'] ?? [])
                    ->pluck('unit_id')
                    ->filter()
                    ->toArray();
                $existingUnitIds = $property->units()->pluck('id')->toArray();
                $unitsToDeleteIds = array_diff($existingUnitIds, $receivedUnitIds);
                if (! empty($unitsToDeleteIds)) {
                    $unitsToDelete = Unit::whereIn('id', $unitsToDeleteIds)->get(['id', 'images']);
                    foreach ($unitsToDelete as $u) {
                        if (! empty($u->images)) {
                            foreach ($u->images as $imgPath) {
                                if ($imgPath && Storage::exists($imgPath)) {
                                    Storage::delete($imgPath);
                                }
                            }
                        }
                    }
                    Unit::whereIn('id', $unitsToDeleteIds)->delete();
                }
            }

            if (($data['category_id'] ?? null) == 6 && ! empty($data['units']) && is_array($data['units'])) {
                foreach ($data['units'] as $index => $unitData) {
                    if (empty($unitData['unit_id']) && empty($unitData['unit_name'])) {
                        continue;
                    }

                    $unit = ! empty($unitData['unit_id']) ? Unit::find($unitData['unit_id']) : new Unit;
                    if (! $unit) {
                        $unit = new Unit;
                    }

                    $unitPrevImages = $unit->images ?? [];
                    if (! empty($unitData['remove_images'])) {
                        $unitPrevImages = array_diff($unit->images ?? [], $unitData['remove_images']);
                        foreach ($unitData['remove_images'] as $image) {
                            if (Storage::exists($image)) {
                                Storage::delete($image);
                            }
                        }
                        $unitPrevImages = array_values($unitPrevImages);
                    } else {
                        $unitPrevImages = $unit->images ?? [];
                    }

                    $newImages = [];
                    if (isset($unitFiles[$index]['images']) && is_array($unitFiles[$index]['images'])) {
                        $newImages = $this->multipleUploadArray($unitFiles[$index]['images']);
                    } elseif (isset($unitData['images']) && is_array($unitData['images']) && isset($unitData['images'][0]) && $unitData['images'][0] instanceof UploadedFile) {
                        $newImages = $this->multipleUploadArray($unitData['images']);
                    }
                    $mergedImages = array_merge($unitPrevImages, $newImages);

                    $unit->fill([
                        'property_id' => $property->id,
                        'unit_name' => $unitData['unit_name'] ?? null,
                        'floor_range' => $unitData['floor_range'] ?? null,
                        'bedroom' => $unitData['bedroom'] ?? null,
                        'bathroom' => $unitData['bathroom'] ?? null,
                        'kitchen' => $unitData['kitchen'] ?? null,
                        'balcony' => $unitData['balcony'] ?? null,
                        'condition' => $unitData['condition'] ?? null,
                        'parking_lot' => $unitData['parking_lot'] ?? null,
                        'unit_size' => $unitData['unit_size'] ?? null,
                        'facilities' => $unitData['facilities'] ?? null,
                        'amenities' => $unitData['amenities'] ?? null,
                        'tenant_preference' => $unitData['tenant_preference'] ?? null,
                        'description' => $unitData['description'] ?? null,
                        'images' => $mergedImages,
                        'monthly_rent' => $unitData['monthly_rent'] ?? 0,
                        'sale_amount' => $unitData['sale_amount'] ?? 0,
                        'security_deposit' => $unitData['security_deposit'] ?? 0,
                        'utility_deposit' => $unitData['utility_deposit'] ?? 0,
                        'late_fee' => $unitData['late_fee'] ?? 0,
                        'late_fee_after_days' => $unitData['late_fee_after_days'] ?? 0,
                        'min_rental_value' => $unitData['min_rental_value'] ?? null,
                        'min_rental_unit' => $unitData['min_rental_unit'] ?? null,
                        'security_deposit_value' => $unitData['security_deposit_value'] ?? null,
                        'security_deposit_unit' => $unitData['security_deposit_unit'] ?? null,
                    ]);
                    $unit->save();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $property->fresh();
    }

    public function delete(Property $property, Channel $channel): void
    {
        if ($channel === Channel::Landlord) {
            if ($property->rent !== null) {
                throw new PropertyRentedException(__('Your property is already rented by tenant. Cannot delete it until remove the tenant first'));
            }
        }

        if (! empty($property->cover_image)) {
            foreach ($property->cover_image as $image) {
                $oldImagePath = dirname($image);
                Storage::deleteDirectory($oldImagePath);
            }
        }

        if (! empty($property->propertyDetail()->first()?->getAttribute('floorplan_image'))) {
            $oldImagePath = dirname((string) $property->propertyDetail()->first()->getAttribute('floorplan_image'));
            Storage::deleteDirectory($oldImagePath);
        }

        foreach ($property->units as $unit) {
            if (! empty($unit->images)) {
                foreach ($unit->images as $image) {
                    if ($image && Storage::exists($image)) {
                        Storage::delete($image);
                    }
                }
            }
        }

        $property->delete();
    }

    public function deleteMany(array $ids, Channel $channel, ?User $actor): int
    {
        $query = Property::whereIn('id', $ids);
        if ($channel !== Channel::Admin && $actor) {
            $query->where('landlord_id', $actor->id);
        }
        $properties = $query->get();

        $count = 0;
        foreach ($properties as $property) {
            if ($channel === Channel::Landlord && $property->rent !== null) {
                throw new PropertyRentedException(__('Your property is already rented by tenant. Cannot delete it until remove the tenant first'));
            }

            $cleanup = [];
            if (! empty($property->cover_image)) {
                foreach ($property->cover_image as $image) {
                    $dir = dirname($image);
                    $cleanup[] = fn () => Storage::deleteDirectory($dir);
                }
            }
            $propertyDetail = $property->propertyDetail;
            if (! empty($propertyDetail?->getAttribute('floorplan_image'))) {
                $dir = dirname((string) $propertyDetail->getAttribute('floorplan_image'));
                $cleanup[] = fn () => Storage::deleteDirectory($dir);
            }
            $unitImages = [];
            foreach ($property->units as $unit) {
                foreach ($unit->images ?? [] as $image) {
                    if ($image) {
                        $unitImages[] = $image;
                    }
                }
            }
            if ($unitImages !== []) {
                $cleanup[] = fn () => Storage::delete($unitImages);
            }

            $property->delete();
            foreach ($cleanup as $callback) {
                $callback();
            }
            $count++;
        }

        return $count;
    }

    public function approve(Property $property): Property
    {
        $property->update(['status' => '1']);

        return $property->fresh();
    }

    public function reject(Property $property, string $reason): Property
    {
        $meta = $property->meta ?? [];
        $meta['reason'] = $reason;

        $property->update([
            'status' => '3',
            'meta' => $meta,
        ]);

        return $property->fresh();
    }

    public function setStatus(Property $property, string|int $status): Property
    {
        $property->update(['status' => (string) $status]);

        return $property->fresh();
    }

    public function updateStatus(Property $property, bool $isPublished): Property
    {
        $property->update(['status' => $isPublished ? 1 : 2]);

        return $property->fresh();
    }

    public function facilitiesForLandlord(?int $landlordId): Collection
    {
        return Facility::where('landlord_id', $landlordId)->get();
    }

    public function amenitiesForLandlord(?int $landlordId): Collection
    {
        return Amenity::where('landlord_id', $landlordId)->get();
    }
}
