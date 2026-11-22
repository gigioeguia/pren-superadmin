<?php

namespace App\Services;

use App\Enums\Channel;
use App\Exceptions\ApplicationAgreementMissingException;
use App\Exceptions\ApplicationAgreementSignedException;
use App\Exceptions\ApplicationDepositUnpaidException;
use App\Exceptions\DuplicateApplicationException;
use App\Exceptions\RentPeriodTooShortException;
use App\Helpers\HasUploader;
use App\Models\Application;
use App\Models\Property;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Dueño único de la lógica de Application (spec-service-application-fase5):
 * unifica los 3 controladores casi-iguales (Api tenant, Api landlord,
 * Landlord web). Las divergencias HTTP documentadas en la tabla §4 del
 * spec se preservan vía Channel y excepciones de dominio:
 *
 *  - processing/approve/reject: solo el canal Api emite sendNotification.
 *  - reject con tenant_agreement firmado: excepción → web 200 / api 409.
 *  - approve sin tenant_agreement: web 404 / api 200 (quirk actual).
 *  - approve con deposit unpaid: web 404 'Application Unpaid' / api 404
 *    'Application security deposit unpaid'.
 *  - fileDownload: web ignora $type y sirve landlord_agreement; api lee
 *    el atributo `file` (inexistente → null siempre). Quirk congelado
 *    por F10/F11 (characterization) — fix en follow-up fuera de 5.13.
 *
 * agreementSign delega el bloque service-charge y la creación de
 * Rent+SecurityDeposit a RentService (única lectura del Option
 * 'service-charge' fuera de admin, DoD §7); las ramas por category_id
 * 1–8 y su composición de importes se preservan aquí.
 */
class ApplicationService
{
    use HasUploader;

    public function __construct(protected RentService $rents) {}

    public function listForChannel(Channel $channel, ?User $actor, array $filters = []): LengthAwarePaginator
    {
        if ($channel === Channel::Landlord) {
            $propertyIds = Property::where('landlord_id', $actor?->id)->pluck('id');

            return Application::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name'])
                ->whereIn('property_id', $propertyIds)
                ->latest()
                ->paginate(10);
        }

        return Application::with(['tenant:id,name,image,email', 'property:id,puid,address_info,cover_image,landlord_id', 'property.propertyDetail:property_id,property_info', 'property.landlord:id,name', 'unit'])
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($actor?->role == 'landlord' || ! empty($filters['landlord_id']), function (Builder $query) use ($actor) {
                $query->whereHas('property', function (Builder $query) use ($actor) {
                    $query->where('landlord_id', $actor?->id);
                });
            })
            ->when($actor?->role == 'tenant', function (Builder $query) use ($actor) {
                $query->where('tenant_id', $actor?->id);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search, $filters) {
                    $query->where('start_date', 'like', '%'.$search.'%')
                        ->orWhere('end_date', 'like', '%'.$search.'%')
                        ->orWhereHas('property.landlord', function (Builder $query) use ($filters) {
                            $query->where('name', 'like', '%'.$filters['search'].'%');
                        })
                        ->orWhereHas('tenant', function (Builder $query) use ($filters) {
                            $query->where('name', 'like', '%'.$filters['search'].'%')
                                ->orWhere('email', 'like', '%'.$filters['search'].'%');
                        })
                        ->orWhereHas('property.propertyDetail', function (Builder $query) use ($filters) {
                            $query->where('property_info', 'like', '%'.$filters['search'].'%');
                        })
                        ->orWhereHas('property', function (Builder $query) use ($filters) {
                            $query->where('address_info', 'like', '%'.$filters['search'].'%')
                                ->orWhere('puid', 'like', '%'.$filters['search'].'%');
                        });
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 10));
    }

    /**
     * Query del filter web (antes en HasTableActions::query, F12).
     */
    public function filterForLandlord(int $landlordId, array $filters = []): LengthAwarePaginator
    {
        $propertyIds = Property::where('landlord_id', $landlordId)->pluck('id');

        return Application::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name'])
            ->whereIn('property_id', $propertyIds)
            ->when(! empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('tenant', function (Builder $query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    })
                        ->orWhereHas('property', function (Builder $query) use ($search) {
                            $query->where('puid', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('unit', function (Builder $query) use ($search) {
                            $query->where('unit_name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('property.propertyDetail', function (Builder $query) use ($search) {
                            $query->where('property_info->property_title', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function loadForShow(Application $application, Channel $channel): Application
    {
        if ($channel === Channel::Landlord) {
            $application->load([
                'tenant',
                'tenant.user_details',
                'property:id,puid,category_id,address_info,rent_info,utility_deposit,security_deposit,late_fee,late_fee_after_days,landlord_id',
                'property.propertyDetail:property_id,property_type,property_info',
                'property.category:id,name',
                'property.landlord:id,name,phone,email',
                'property.rent:property_id,id',
                'unit:id,monthly_rent,utility_deposit,security_deposit,late_fee,late_fee_after_days',
            ]);

            return $application;
        }

        $application->load([
            'tenant',
            'tenant.user_details',
            'property:id,puid,category_id,landlord_id,address_info,rent_info,utility_deposit,security_deposit,late_fee,late_fee_after_days',
            'property.propertyDetail:property_id,property_type,property_info',
            'property.category:id,name',
            'property.landlord:id,name,phone,email',
            'property.rent:property_id,id',
            'unit',
        ]);

        // Quirk preservado: el lookup ignora unit_id (solo property_id).
        $securityDeposit = SecurityDeposit::where('property_id', $application->property_id)->first();

        $application->setAttribute('security_deposit', [
            'id' => $securityDeposit->id ?? null,
            'status' => $securityDeposit->status ?? null,
        ]);

        return $application;
    }

    /**
     * Store del Api tenant (sin tx: 1 escritura — comportamiento actual).
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, ?User $tenant): Application
    {
        $duplicate = Application::where('property_id', $data['property_id'])
            ->where('unit_id', $data['unit_id'] ?? null)
            ->where('tenant_id', $tenant?->id)
            ->where('status', '!=', 3)
            ->first();

        if ($duplicate) {
            throw new DuplicateApplicationException('You have already apply for this property');
        }

        $property = Property::findOrFail($data['property_id']);

        $this->assertRentalPeriodAllowed($data, $property);

        // Quirk preservado: document_file se sube a storage pero el path
        // NO se persiste (columna inexistente, clave descartada por el
        // mass-assignment original) — F1a congela el archivo huérfano.
        if (($data['document_file'] ?? null) instanceof UploadedFile) {
            $this->uploadFile($data['document_file']);
        }

        $application = Application::create([
            'tenant_id' => $tenant?->id,
            'property_id' => $data['property_id'],
            'unit_id' => $data['unit_id'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'rejected_cause' => $data['rejected_cause'] ?? null,
        ]);

        sendNotification($application->id, notify_users([$application->tenant_id, $property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application->id]), admin_msg: __('New Property Application has been created'), tenant_msg: __('Your application has been submited'), landlord_msg: __('You have a new application'));

        return $application;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertRentalPeriodAllowed(array $data, Property $property): void
    {
        // Carbon::parse(null) === now: mismo comportamiento que el
        // controlador original cuando las fechas vienen ausentes.
        $rentalDays = Carbon::parse($data['start_date'] ?? null)->diffInDays(Carbon::parse($data['end_date'] ?? null));

        if (! empty($data['unit_id'])) {
            $unit = Unit::where('property_id', $property->id)->findOrFail($data['unit_id']);
            $minValue = $unit->min_rental_value ?? null;
            $minUnit = $unit->min_rental_unit ?? null;
        } else {
            $minValue = $property->rent_info['min_rental_value'] ?? null;
            $minUnit = $property->rent_info['min_rental_unit'] ?? null;
        }

        if ($minValue && $minUnit) {
            $minDays = match ($minUnit) {
                'days' => $minValue,
                'weeks' => $minValue * 7,
                'months' => $minValue * 30,
                'years' => $minValue * 365,
                default => 0,
            };

            if ($rentalDays < $minDays) {
                throw new RentPeriodTooShortException("The rental period must be at least {$minValue} {$minUnit}.");
            }
        }
    }

    /**
     * agreement_sign del Api tenant: fija el tenant_agreement y crea
     * SecurityDeposit+Rent vía RentService::createWithAgreement (spec §4),
     * con las ramas por category_id 1–8 preservadas y la notificación
     * tras el commit.
     */
    public function agreementSign(Application $application, ?UploadedFile $tenantAgreement): Application
    {
        $property = $application->property;
        $branch = $this->resolveAgreementBranch($application, $property);

        // Upload antes de abrir la transacción (Fase 4 / 4.8)
        $tenantAgreementPath = $tenantAgreement ? $this->uploadFile($tenantAgreement) : null;

        DB::beginTransaction();
        try {
            $application->update([
                'tenant_agreement' => $tenantAgreementPath,
            ]);

            $this->rents->createWithAgreement(
                [
                    'landlord_id' => $property->landlord_id,
                    'tenant_id' => $application->tenant_id,
                    'property_id' => $application->property_id,
                    // Quirk preservado: las ramas 1–8 no propagan unit_id
                    // al Rent aunque la application lo tenga (F4).
                    'unit_id' => $branch['rent']['unit_id'],
                    'monthly_rent' => $branch['deposit']['rent_advance'],
                    // Igual que el original: se lee del deposit en
                    // memoria — cats 4/5/7/8 no fijan utility_advance
                    // y el Rent queda con utility_bill NULL.
                    'utility_bill' => $branch['deposit']['utility_advance'] ?? null,
                    'total_amount' => $branch['rent']['total_amount'],
                    'start_date' => $application->start_date,
                    'end_date' => $application->end_date,
                    'landlord_agreement' => $application->landlord_agreement,
                    'tenant_agreement' => $application->tenant_agreement,
                ],
                $branch['deposit'],
                $branch['charge_base'],
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        // sendNotification tras el commit (Fase 4 / 4.8)
        sendNotification($application->id, notify_users([$application->tenant_id, $property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application->id]), tenant_msg: __('You have successfully signed the rental agreement. Awaiting landlord review.'), landlord_msg: __('The tenant has signed the rental agreement, please review and proceed with the next steps.'));

        return $application;
    }

    /**
     * Ramas por category_id del agreement_sign (spec §4, preservadas tal
     * cual): cats 1–3 y 6 incluyen utility en el SecurityDeposit, cats
     * 4/5/7/8 la excluyen; cat 6 resuelve importes desde la unidad. El
     * charge base siempre suma deposit+rent+utility.
     *
     * @return array{deposit: array<string, mixed>, rent: array{unit_id: ?int, total_amount: float|int}, charge_base: float|int}
     */
    private function resolveAgreementBranch(Application $application, Property $property): array
    {
        $categoryId = $property->category_id;

        if (in_array($categoryId, [1, 2, 3])) {
            return [
                'deposit' => [
                    'tenant_id' => $application->tenant_id,
                    'landlord_id' => $property->landlord_id,
                    'property_id' => $property->id,
                    'deposit_amount' => $property->security_deposit,
                    'utility_advance' => $property->utility_deposit,
                    'rent_advance' => $property->rent_info['monthly_rent'] ?? null,
                    'total_amount' => ($property->security_deposit ?? 0) + ($property->utility_deposit ?? 0) + ($property->rent_info['monthly_rent'] ?? 0),
                ],
                'rent' => [
                    'unit_id' => null,
                    'total_amount' => ($property->rent_info['monthly_rent'] ?? 0) + ($property->utility_deposit ?? 0),
                ],
                'charge_base' => ($property->security_deposit ?? 0) + ($property->rent_info['monthly_rent'] ?? 0) + ($property->utility_deposit ?? 0),
            ];
        }

        if (in_array($categoryId, [4, 5, 7, 8])) {
            return [
                'deposit' => [
                    'tenant_id' => $application->tenant_id,
                    'landlord_id' => $property->landlord_id,
                    'property_id' => $property->id,
                    'deposit_amount' => $property->security_deposit,
                    'rent_advance' => $property->rent_info['monthly_rent'] ?? null,
                    'total_amount' => ($property->security_deposit ?? 0) + ($property->rent_info['monthly_rent'] ?? 0),
                ],
                'rent' => [
                    'unit_id' => null,
                    'total_amount' => ($property->rent_info['monthly_rent'] ?? 0) + ($property->utility_deposit ?? 0),
                ],
                'charge_base' => ($property->security_deposit ?? 0) + ($property->rent_info['monthly_rent'] ?? 0) + ($property->utility_deposit ?? 0),
            ];
        }

        if ($categoryId === 6) {
            // Quirk preservado: sin unit_id findOrFail(null) lanza
            // ModelNotFound → 500 'Agreement sign failed' (actual).
            $unit = Unit::where('property_id', $property->id)->findOrFail($application->unit_id);

            return [
                'deposit' => [
                    'tenant_id' => $application->tenant_id,
                    'landlord_id' => $property->landlord_id,
                    'property_id' => $property->id,
                    'unit_id' => $unit->id,
                    'deposit_amount' => $unit->security_deposit,
                    'utility_advance' => $unit->utility_deposit,
                    'rent_advance' => $unit->monthly_rent,
                    'total_amount' => ($unit->security_deposit ?? 0) + ($unit->utility_deposit ?? 0) + ($unit->monthly_rent ?? 0),
                ],
                'rent' => [
                    'unit_id' => $unit->id,
                    'total_amount' => ($unit->monthly_rent ?? 0) + ($unit->utility_deposit ?? 0),
                ],
                'charge_base' => ($unit->security_deposit ?? 0) + ($unit->utility_deposit ?? 0) + ($unit->monthly_rent ?? 0),
            ];
        }

        // Categoría fuera de 1–8: hoy no se crea deposit y el update
        // sobre null crashea → 500 'Agreement sign failed' (actual).
        throw new RuntimeException('Agreement sign failed');
    }

    /**
     * Quirk preservado: sin archivo se sobreescribe landlord_agreement
     * con null (canal api, F6).
     */
    public function processing(Application $application, ?UploadedFile $file, Channel $channel): Application
    {
        $application->update([
            'landlord_agreement' => $file ? $this->uploadFile($file) : null,
            'status' => 1,
        ]);

        if ($channel === Channel::Api) {
            sendNotification($application->id, notify_users([$application->tenant_id, $application->property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application->id]), landlord_msg: __('Your application has been processed successfully.'), tenant_msg: __('Your application has been processed.'));
        }

        return $application;
    }

    /**
     * Quirk preservado: deposit ausente → crash de null-dereference igual
     * que el original (sin guard añadido).
     */
    public function approve(Application $application, Channel $channel): Application
    {
        $securityDeposit = SecurityDeposit::where('property_id', $application->property_id)
            ->where('unit_id', $application->unit_id)
            ->first();

        if (empty($application->tenant_agreement) || ! Storage::exists($application->tenant_agreement)) {
            throw new ApplicationAgreementMissingException('Tenant agreement file does not exist');
        }

        if ($securityDeposit->status != 'paid') {
            throw new ApplicationDepositUnpaidException;
        }

        $application->update([
            'status' => 2,
        ]);

        $securityDeposit->rent()->update([
            'status' => 'active',
        ]);

        if ($channel === Channel::Api) {
            sendNotification($application->id, notify_users([$application->tenant_id, $application->property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application->id]), landlord_msg: __('The application has been approved successfully.'), tenant_msg: __('Congratulations! Your application has been approved.'));
        }

        return $application;
    }

    public function reject(Application $application, string $rejectedCause, Channel $channel): Application
    {
        if (! empty($application->tenant_agreement) && Storage::exists($application->tenant_agreement)) {
            throw new ApplicationAgreementSignedException('Application cannot be rejected because a tenant agreement already exists.');
        }

        $application->update([
            'rejected_cause' => $rejectedCause,
            'status' => 3,
        ]);

        if ($channel === Channel::Api) {
            sendNotification($application->id, notify_users([$application->tenant_id, $application->property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application->id]), landlord_msg: __('The application has been rejected.'), tenant_msg: __('We regret to inform you that your application has been rejected.'));
        }

        return $application;
    }

    /**
     * Devuelve el path a servir o null cuando no hay archivo — el
     * controller responde el JSON 'File not found' actual.
     *
     * Quirk congelado (F10/F11, @group characterization): el canal web
     * ignora $type y sirve landlord_agreement; el canal api lee el
     * atributo `file`, que no existe en el modelo → null siempre.
     */
    public function fileDownload(Application $application, Channel $channel, ?string $type): ?string
    {
        return match ($channel) {
            Channel::Landlord => $application->landlord_agreement,
            Channel::Api => $application->file,
            Channel::Admin => null,
        };
    }
}
