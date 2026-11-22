<?php

namespace App\Services;

use App\Enums\Channel;
use App\Exceptions\RentNotActiveException;
use App\Exceptions\RentPeriodTooShortException;
use App\Exceptions\SecurityDepositMissingException;
use App\Exceptions\SecurityDepositUnpaidException;
use App\Helpers\HasUploader;
use App\Models\Amenity;
use App\Models\Facility;
use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dueño único de la lógica de Rent (spec-service-rent-fase5): cálculo del
 * bloque service-charge/depósito y creación atómica Rent+SecurityDeposit
 * (`createWithDeposit`, reutilizada por ApplicationService en el hijo 3).
 *
 * Los controladores solo shapean respuestas por canal; las divergencias
 * HTTP documentadas viven en la tabla §4 del spec y se preservan aquí
 * vía Channel y excepciones de dominio.
 */
class RentService
{
    use HasUploader;

    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        if ($channel === Channel::Landlord) {
            return Rent::select('id', 'monthly_rent', 'status', 'tenant_id', 'property_id', 'unit_id')
                ->with([
                    'tenant:id,name,unique_id',
                    'property:id,category_id',
                    'property.propertyDetail:property_id,property_info,property_type',
                    'property.category:id,name',
                    'unit:id,unit_name',
                    'security_deposit:id,rent_id,deposit_amount',
                ])
                ->where('landlord_id', $actor?->id)
                ->latest()
                ->paginate(10);
        }

        return Rent::select('id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'status', 'landlord_id', 'tenant_id', 'property_id', 'unit_id')
            ->with('tenant:id,name,unique_id', 'landlord:id,name', 'property:id,category_id,address_info,rent_info', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name,min_rental_value,min_rental_unit')
            // Quirk preservado (spec §5.7): input landlord_id filtra por
            // landlord ajeno cuando el actor es landlord (F15 lo congela).
            ->when($actor?->role === 'landlord' || ! empty($filters['landlord_id']), function (Builder $query) use ($actor, $filters) {
                $query->where('landlord_id', $filters['landlord_id'] ?? $actor?->id);
            })
            ->when($actor?->role === 'tenant', function (Builder $query) use ($actor) {
                $query->where('tenant_id', $actor?->id);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(($filters['status'] ?? null) == 'expired', function (Builder $query) {
                $query->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today());
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 10));
    }

    /**
     * Rama `no_paginate` del Api index: Collection sin metadatos de
     * paginador (F14 congela la forma).
     */
    public function listAll(?User $actor, array $filters = []): Collection
    {
        return Rent::select('id', 'tenant_id', 'property_id', 'status')
            ->with('property:id', 'property.propertyDetail:property_id,property_info', 'security_deposit:rent_id,id,invoice_no')
            ->when($actor?->role === 'tenant', function (Builder $query) use ($actor) {
                $query->where('tenant_id', $actor?->id);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->get();
    }

    /**
     * Dropdowns del web index (F10): tenants del landlord + properties
     * tipo rent con unidad libre (o property sin rent activa).
     *
     * @return array{tenants: Collection<int, User>, properties: Collection<int, Property>}
     */
    public function indexDropdowns(int $landlordId): array
    {
        $tenants = User::with('user_details')->where('landlord_id', $landlordId)->latest()->get();

        $properties = Property::with('rent', 'units')->where('landlord_id', $landlordId)
            ->where('status', 1)
            ->where('type', 'rent')
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Property con múltiples unidades
                    $q->whereHas('units', function ($unitQuery) {
                        $unitQuery->where(function ($uq) {
                            $uq->whereDoesntHave('rent')
                                ->orWhereHas('rent', function ($rq) {
                                    $rq->whereNotIn('status', ['active', 'processing']);
                                });
                        });
                    });
                })->orWhere(function ($q) {
                    // Property sin unidades con rent propio
                    $q->whereDoesntHave('rent')
                        ->orWhereHas('rent', function ($rq) {
                            $rq->whereNotIn('status', ['active', 'processing']);
                        });
                });
            })
            ->latest()
            ->get();

        return ['tenants' => $tenants, 'properties' => $properties];
    }

    /**
     * Query del filter web (antes en HasTableActions::query, F13).
     */
    public function filterForLandlord(int $landlordId, array $filters = []): LengthAwarePaginator
    {
        return Rent::select('id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'status', 'landlord_id', 'tenant_id', 'property_id')
            ->with([
                'tenant:id,name,unique_id',
                'landlord:id,name',
                'property:id,address_info,rent_info',
                'property.propertyDetail:property_id,property_info,property_type',
                'security_deposit',
            ])
            ->where('landlord_id', $landlordId)
            ->when(! empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('tenant', function ($query) use ($search) {
                        $query->where('unique_id', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    })
                        ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                            $query->where('property_type', 'like', '%'.$search.'%')
                                ->orWhere('property_info->property_title', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('security_deposit', function ($query) use ($search) {
                            $query->where('deposit_amount', 'like', '%'.$search.'%');
                        })
                        ->orWhere('monthly_rent', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function loadForShow(Rent $rent, Channel $channel): Rent
    {
        if ($channel === Channel::Landlord) {
            $rent->load([
                'tenant:id,unique_id,name,phone,email',
                'property.propertyDetail',
                'security_deposit:id,rent_id,status',
                'unit',
            ]);

            $property = $rent->property;

            if ($property instanceof Property && $property->category_id == 6) {
                // getRelationValue: la relación ya está cargada y unit_id es
                // nullable — mismo valor que la propiedad mágica, sin el
                // falso-positivo nullsafe de larastan sobre $rent->unit.
                $unit = $rent->getRelationValue('unit');
                $unitFacilityIds = $unit?->getAttribute('facilities') ?? [];
                $rent->setAttribute('facility_names', Facility::whereIn('id', $unitFacilityIds)->pluck('name', 'id'));

                $unitAmenityIds = $unit?->getAttribute('amenities') ?? [];
                $rent->setAttribute('amenity_names', Amenity::whereIn('id', $unitAmenityIds)->pluck('name', 'id'));
            } else {
                $propertyDetail = $property instanceof Property ? $property->propertyDetail : null;

                $facilityIds = $propertyDetail ? $propertyDetail->getAttribute('facilities') : [];
                $facilityIds = is_array($facilityIds) ? $facilityIds : [];
                $rent->setAttribute('facilities', Facility::whereIn('id', $facilityIds)->pluck('name', 'id'));

                $amenityIds = $propertyDetail ? $propertyDetail->getAttribute('amenities') : [];
                $amenityIds = is_array($amenityIds) ? $amenityIds : [];
                $rent->setAttribute('amenities', Amenity::whereIn('id', $amenityIds)->pluck('name', 'id'));
            }

            return $rent;
        }

        $rent->load([
            'tenant:id,unique_id,name,phone,email',
            'landlord:id,unique_id,name,phone,email',
            'property.category:id,name',
            'property.propertyDetail',
            'unit',
            'security_deposit:id,rent_id,status',
            'rent_payments' => function ($query) {
                $query->select('rent_id', 'payment_status', 'late_fee')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN subtotal_amount ELSE 0 END) as total_paid_rent')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) as this_month_payment')
                    ->selectRaw('SUM(CASE WHEN payment_status = "unpaid" THEN subtotal_amount ELSE 0 END) as due_rent')
                    ->selectRaw('CASE WHEN SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) > 0
                                THEN "paid"
                                ELSE "unpaid"
                            END as this_month_payment_status')
                    ->groupBy('rent_id', 'payment_status', 'late_fee');
            },
        ]);

        return $rent;
    }

    /**
     * Única fuente del bloque service-charge/depósito (spec §5.1): la
     * llamará ApplicationService::agreementSign en el hijo 3.
     *
     * Orden 4.8: upload antes de abrir la tx, sendNotification tras el
     * commit. F1/F2/U1 congelan los números exactos.
     */
    public function createWithDeposit(array $data, ?UploadedFile $landlordAgreement = null): Rent
    {
        $property = Property::findOrFail($data['property_id']);

        if (! empty($data['unit_id'])) {
            $unit = Unit::where('property_id', $property->id)->findOrFail($data['unit_id']);

            $minValue = $unit->min_rental_value ?? null;
            $minUnit = $unit->min_rental_unit ?? null;

            $depositAmount = $unit->security_deposit ?? 0;
            $rentAdvance = $unit->monthly_rent ?? 0;
            $utilityAdvance = $unit->utility_deposit ?? 0;
        } else {
            $minValue = $property->rent_info['min_rental_value'] ?? null;
            $minUnit = $property->rent_info['min_rental_unit'] ?? null;

            $depositAmount = $property->security_deposit ?? 0;
            $rentAdvance = $property->rent_info['monthly_rent'] ?? 0;
            $utilityAdvance = $property->utility_deposit ?? 0;
        }

        if ($minValue && $minUnit) {
            $minDays = match ($minUnit) {
                'days' => $minValue,
                'weeks' => $minValue * 7,
                'months' => $minValue * 30,
                'years' => $minValue * 365,
                default => 0,
            };

            $rentalDays = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date']));
            if ($rentalDays < $minDays) {
                throw new RentPeriodTooShortException("The rental period must be at least {$minValue} {$minUnit}.");
            }
        }

        // Rent total = rent + utility
        $rentTotalAmount = $rentAdvance + $utilityAdvance;
        // Security total = rent + utility + deposit
        $securityTotalAmount = $rentAdvance + $utilityAdvance + $depositAmount;
        // Security deposit + rent + utility para el balance del landlord
        $landlordBalance = $depositAmount + $rentAdvance + $utilityAdvance;

        $calculatedCharge = $this->calculatedCharge($securityTotalAmount);

        // Upload antes de abrir la transacción (Fase 4 / 4.8)
        $landlordAgreementPath = $landlordAgreement ? $this->uploadAgreement($landlordAgreement) : null;

        DB::beginTransaction();
        try {
            $rent = Rent::create([
                'landlord_id' => $data['landlord_id'],
                'tenant_id' => $data['tenant_id'],
                'property_id' => $data['property_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'utility_bill' => $utilityAdvance,
                'monthly_rent' => $rentAdvance,
                'total_amount' => $rentTotalAmount,
                'landlord_agreement' => $landlordAgreementPath,
            ]);

            SecurityDeposit::create([
                'tenant_id' => $data['tenant_id'],
                'landlord_id' => $data['landlord_id'],
                'rent_id' => $rent->id,
                'property_id' => $data['property_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'deposit_amount' => $depositAmount,
                'rent_advance' => $rentAdvance,
                'utility_advance' => $utilityAdvance,
                'total_amount' => $securityTotalAmount,
                'landlord_balance' => $landlordBalance - $calculatedCharge,
                'charge' => $calculatedCharge,
                'status' => 'unpaid',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        sendNotification($rent->id, notify_users([$rent->tenant_id, $rent->landlord_id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), admin_msg: __('Landlord create a new rent invitation'), landlord_msg: __('A new rent invitation has been sent to the tenant. Waiting for approval.'), tenant_msg: __('You have received a new rent invitation. Please review and approve.'));

        return $rent;
    }

    /**
     * Creación de Rent+SecurityDeposit para ApplicationService::agreementSign
     * (spec hijo 3 §4): importes y acuerdos ya resueltos por las ramas
     * category del application; el service-charge se calcula aquí — única
     * lectura del Option 'service-charge' fuera de los controladores admin
     * (DoD hijo 3 §7).
     *
     * La transacción la abre el caller: agreement_sign envuelve el update
     * del application y esta creación en la misma tx (comportamiento
     * actual). Devuelve el Rent creado para el enlace del caller.
     *
     * @param  array<string, mixed>  $rentAttributes
     * @param  array<string, mixed>  $depositAttributes
     */
    public function createWithAgreement(array $rentAttributes, array $depositAttributes, float|int $chargeBaseAmount): Rent
    {
        $charge = $this->calculatedCharge($chargeBaseAmount);

        $rent = Rent::create($rentAttributes);

        SecurityDeposit::create($depositAttributes + [
            'rent_id' => $rent->id,
            'charge' => $charge,
            'landlord_balance' => ($depositAttributes['total_amount'] ?? 0) - $charge,
            'status' => 'unpaid',
        ]);

        return $rent;
    }

    /**
     * Única lectura del Option 'service-charge' del código no-admin
     * (DoD hijo 3 §7). Quirk preservado: sin fila 'service-charge' la
     * lectura sobre null crashea igual que en los controladores
     * originales (no se "arregla").
     */
    protected function calculatedCharge(float|int $baseTotalAmount): float
    {
        $chargeOption = Option::where('key', 'service-charge')->first();
        $chargeData = $chargeOption->value;

        $amount = $chargeData['amount'] ?? 0;
        $type = $chargeData['type'] ?? 'fixed';

        return $type === 'percentage' ? ($baseTotalAmount * $amount) / 100 : $amount;
    }

    /**
     * Error-paths divergentes por canal (spec §4): SecurityDepositMissing
     * → web 404 / api 500 (no capturada, quirk original); Unpaid → web
     * 400 / api 404 'Rent not found'.
     */
    public function approve(Rent $rent, Channel $channel): Rent
    {
        $securityDeposit = SecurityDeposit::where('rent_id', $rent->id)->first();

        if (! $securityDeposit) {
            throw new SecurityDepositMissingException;
        }

        if ($securityDeposit->status != 'paid') {
            throw new SecurityDepositUnpaidException;
        }

        $rent->update(['status' => 'active']);

        // Divergencia por canal: solo Api notifica el approval (F4/F5).
        if ($channel === Channel::Api) {
            sendNotification($rent->id, notify_users([$rent->tenant_id, $rent->landlord_id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), landlord_msg: __('The rent agreement has been approved and is now active.'), tenant_msg: __('Your rent agreement has been approved and is now active. Welcome!'));
        }

        return $rent;
    }

    /**
     * Cancel unificado: mismo cancel_info + status + notificación en
     * ambos canales; solo rents activas (si no, 404 'Rent not found').
     */
    public function cancel(Rent $rent, Channel $channel, ?string $title = null, ?string $reason = null): Rent
    {
        if ($rent->status != 'active') {
            throw new RentNotActiveException;
        }

        $rent->update([
            'cancel_info' => [
                'title' => $title,
                'reason' => $reason,
                'prev_end_date' => $rent->end_date,
            ],
            'status' => 'cancelled',
        ]);

        sendNotification($rent->id, notify_users([$rent->tenant_id, $rent->landlord_id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), landlord_msg: __('The tenant has cancelled the rent agreement.'), tenant_msg: __('You have successfully cancelled your rent agreement.'));

        return $rent;
    }

    /**
     * Comportamiento actual preservado (F8): 1 escritura sin tx, la
     * notificación va después del update.
     */
    public function agreementUpload(Rent $rent, UploadedFile $tenantAgreement): Rent
    {
        $rent->update([
            'tenant_agreement' => $this->uploadAgreement($tenantAgreement),
        ]);

        sendNotification($rent->id, notify_users([$rent->tenant_id, $rent->landlord_id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), landlord_msg: __('The tenant has submitted the signed rent agreement. Please review it.'), tenant_msg: __('Your rent agreement has been submitted successfully. Awaiting landlord’s review.'));

        return $rent;
    }

    public function tenantAccept(Rent $rent): Rent
    {
        $rent->update(['status' => 'processing']);

        sendNotification($rent->id, notify_users([$rent->tenant_id, $rent->landlord_id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), landlord_msg: __('The tenant has accepted the rent invitation.'), tenant_msg: __('You have successfully accepted the rent invitation.'));

        return $rent;
    }

    /**
     * Opciones de unidad del dropdown getUnit (F12): scope por property_id,
     * sin filtro landlord — quirk preservado (spec §5.7).
     */
    public function unitOptions(int $propertyId): Collection
    {
        return Unit::select('id', 'property_id', 'unit_name')
            ->where('property_id', $propertyId)
            ->where(function ($query) {
                $query->whereDoesntHave('rent')
                    ->orWhereHas('rent', function ($subQuery) {
                        $subQuery->whereNotIn('status', ['active', 'processing']);
                    });
            })
            ->latest()
            ->get();
    }

    /**
     * Upload con el nombre generado por HasUploader (mismo path/formato
     * que el upload() original de los controladores).
     */
    protected function uploadAgreement(UploadedFile $agreement, ?string $oldFile = null): string
    {
        $filePath = $agreement->storeAs(
            'uploads/'.now()->format('y/m'),
            $this->generatedFilename($agreement),
            config('filesystems.default')
        );

        if ($filePath === false) {
            throw new RuntimeException('Agreement upload failed.');
        }

        $this->deleteOldFile($oldFile);

        return $filePath;
    }
}
