@extends('layouts.admin.master')

@section('title')
    {{ __('Tenant Details') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3> {{__('Tenant Details')}} </h3>
            </div>

            <div class="info-container ">
                <div class="table-header-title ">
                    <h4> {{__('Tenant Details')}} </span>
                    </h4>
                </div>
                <div class="row ">
                    <div class="col-lg-6">
                        <div class=" mt-3 table-responsive">
                            <table class="table">
                                <tbody>

                                    <tr>
                                        <td class="border-0 details-name"> {{__('Type of tenant')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->profile_type }}</td>

                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Full Name')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Email Address')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Phone Number')}} </td>
                                        <td class="border-0 details-content" >: {{ $tenant->phone['mobile_no'] ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td class="border-0 details-name"> {{__('NID/Passport Id')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->mykad_id ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Tenant ID')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->unique_id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Gender')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->gender ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 1')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->address_info['address_one'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 2')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->address_info['address_two'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Postal Code')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->address_info['postal_code'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('City')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->address_info['city'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Country Name')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->address_info['country'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-3 mb-2">
                                                <h3> {{__('Emergency Contact')}}
                                                </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Relation With You')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->emergency_contact['relation_with'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Name')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->emergency_contact['name'] ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td class="border-0 details-name"> {{__('Phone Number')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->emergency_contact['mobile_no'] ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-3 mb-2">
                                                <h3> {{__('Company')}} </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Company Name')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->company_info['company_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Company SSM No')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->company_info['company_ssm_no'] ?? '' }}</td>
                                    </tr>




                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-3 mb-2">
                                                <h3> {{__('Nominee')}}
                                                </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Nominee Name')}} </td>
                                        <td class="border-0  details-content">: {{ $tenant->user_details->nominee_info['name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Nominee Email')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->nominee_info['email'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Nominee NO.')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->nominee_info['mobile_no'] ?? '' }}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-3 mb-2">
                                                <h3> {{__('Workplace')}} </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Company name')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['company_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 1')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['address_one'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 2')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['address_two'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Postal Code')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['postal_code'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('City')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['city'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Country')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['country'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Off. Phone Number')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['mobile_no'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Off. Mobile Number')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['office_phone'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Email Address')}} </td>
                                        <td class="border-0 details-content">: {{ $tenant->user_details->workplace['email'] ?? '' }}</td>
                                    </tr>
                                    @if (!empty($tenant->user_details['vehicles_info']))
                                    @foreach ($tenant->user_details['vehicles_info'] as $index => $vehicle)
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-4 mb-2">
                                                <h3> {{__('Vehicles Information')}} {{$loop->iteration }}</h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Vehicles Type')}} </td>
                                        <td class="border-0 details-content">: {{ $vehicle['type'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Car Registration No')}} </td>
                                        <td class="border-0 details-content">: {{ $vehicle['reg_no'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Vehicles Brand')}} </td>
                                        <td class="border-0 details-content">: {{ $vehicle['brand'] ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2"> {{__('No vehicle information available.')}} </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-4 mb-2">
                                                <h3> {{__('NID/Passport')}} </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="border-0">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="mykad-img">
                                                    <img src="{{ asset($tenant->user_details->mykad['front_image'] ?? 'backend/assets/img/icon/mykad.svg') }}"
                                                        alt="">
                                                </div>
                                                <div class="mykad-img">
                                                    <img src="{{ asset( $tenant->user_details->mykad['back_image'] ?? 'backend/assets/img/icon/mykad.svg') }}"
                                                        alt="">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="accordion" id="accordionExample">
                    @foreach ($tenant->rents as $index => $rent)
                        @php
                            $property = $rent->property;
                            $propertyDetail = $property->propertyDetail ?? null;
                            $category = $property->category ?? null;
                            $securityDeposit = $rent->security_deposit ?? null;
                            $rentPayments = $rent->rent_payments->first() ?? null;
                        @endphp

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button custom-accordion-btn" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                    aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                    aria-controls="collapse{{ $index }}">
                                    {{ $propertyDetail->property_info['property_title'] ?? 'Unknown Property' }}
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-1">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="table-responsive mt-3">
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td class="border-0">
                                                                <div class="table-details-header-title mb-2">
                                                                    <h3> {{__('Property Detail')}} </h3>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Property ID')}} </td>
                                                            <td class="border-0">: {{ $property->puid ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Property Type')}} </td>
                                                            <td class="border-0">: {{ $category->name ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Property Name')}} </td>
                                                            <td class="border-0">: {{ $propertyDetail->property_info['property_title'] ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Property Address')}} </td>
                                                            <td class="border-0">: {{ $property->address_info['country'] ?? 'N/A' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if($rent->tenant_agreement)
                                                <div class="p-2">
                                                    <h6 class="upload-img-title"> {{__('Rent Agreement PDF')}} </h6>
                                                    <div class="download-pdf-container pr-3 mt-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img class="pdf-img"
                                                                src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}"
                                                                alt="">
                                                            <div>
                                                                <h6 class="rent-agree"> {{__('Rent Agreement')}} </h6>
                                                                <a href="{{ asset($rent->tenant_agreement) }}" download>
                                                                     {{__('Pdf')}}
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('admin.tenants.file-download', $rent->id)}}"
                                                        class="download-icon-container" download="">
                                                        <img class="download-icon"
                                                            src="http://127.0.0.1:8000/backend/assets/img/icon/download-icon.svg">
                                                      </a>
                                                    </div>
                                                </div>

                                            @endif
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="table-responsive mt-3">
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td class="border-0">
                                                                <div class="table-details-header-title mb-2">
                                                                    <h3> {{__('Payment Detail')}} </h3>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Monthly Rental')}} </td>
                                                            <td class="border-0">:  {{__('RM')}}  {{ $securityDeposit->rent_advance ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Security Deposit')}} </td>
                                                            <td class="border-0">: {{__('RM')}} {{ $securityDeposit->deposit_amount ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Utility Bill')}} </td>
                                                            <td class="border-0">: {{__('RM')}} {{ $securityDeposit->utility_advance ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('This Month Payment')}} </td>
                                                            <td class="border-0">: {{__('RM')}} {{ $rentPayments->this_month_payment ?? '0' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Total Paid Rent')}} </td>
                                                            <td class="border-0">: {{__('RM')}} {{ $rentPayments->total_paid_rent ?? '0' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Due Rent')}} </td>
                                                            <td class="border-0">: {{__('RM')}} {{ $rentPayments->due_rent ?? '0' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Rent Start Date')}} </td>
                                                            <td class="border-0">: {{ $rent->start_date ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Rent End Date')}} </td>
                                                            <td class="border-0">: {{ $rent->end_date ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 details-name"> {{__('Status')}} </td>
                                                            <td class="border-0 status-{{ $rentPayments->payment_status ?? 'unpaid' }}">
                                                                : {{ ucfirst($rentPayments->payment_status ?? 'Unpaid') }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
             </div>
            </div>
        </div>
    </div>
@endsection
