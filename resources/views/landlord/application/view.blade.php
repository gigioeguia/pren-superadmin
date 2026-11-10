@extends('layouts.landlord.master')

@section('title')
    {{ __('Application View') }}
@endsection

@section('main_content')

    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Application Details') }}</h3>
                @if ($application->status == 0)
                <p class="pending_status">{{ __('Pending') }}</p>
                @elseif($application->status == 1)
                <p class="processing_status">{{ __('Processing') }}</p>
                @elseif($application->status == 2)
                <p class="approve_status">{{ __('Approved') }}</p>
                @else
                <p class="reject_status">{{ __('Rejected') }}</p>
                @endif
            </div>

            <div class="info-container">
                <div class="table-header-title ">
                    <h4>{{ __('Tenant Details') }}</h4>
                </div>
                <div class="row ">
                    <div class="col-lg-6">
                        <div class="table-responsive mt-3">
                            <table class="table">
                                <tbody>

                                    <tr>
                                        <td class="border-0 details-name">{{ __('Type of Tenant') }}</td>
                                        <td class="border-0 details-content">: {{ ucfirst($application->tenant->profile_type ?? '') }}</td>

                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Full Name') }}</td>
                                        <td class="border-0">: {{ $application->tenant->name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Email Address') }}</td>
                                        <td class="border-0">: {{ $application->tenant->email ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Phone Number') }}</td>
                                        <td class="border-0">: {{ $application->tenant->phone['mobile_no'] ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td class="border-0 details-name">{{ __('NID/Passport Id') }}</td>
                                        <td class="border-0">: {{ $application->tenant->user_details->mykad_id ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Tenant ID') }}</td>
                                        <td class="border-0">: {{ $application->tenant->unique_id ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Gender') }}</td>
                                        <td class="border-0">: {{ $application->tenant->user_details->gender ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Address 1') }}</td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->address_info['address_one'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Address 2') }}</td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->address_info['address_two'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('Postal Code') }}</td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->address_info['postal_code'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name">{{ __('City') }}</td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->address_info['city'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Country Name')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->address_info['country'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-3 mb-2">
                                                <h3> {{__('Emergency Contact')}}  </h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Relation With You')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->emergency_contact['relation_with'] ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Name')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->emergency_contact['name'] ?? '' }}</td>
                                    </tr>

                                    <tr>
                                        <td class="border-0 details-name"> {{__('Phone Number')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->emergency_contact['mobile_no'] ?? '' }}
                                        </td>
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
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->company_info['company_name'] ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Company SSM No')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->company_info['company_ssm_no'] ?? '' }}
                                        </td>
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
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->nominee_info['name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Nominee Email')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->nominee_info['email'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Nominee MO. NO.')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->nominee_info['mobile_no'] ?? '' }}</td>
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
                                        <td class="border-0 details-content">:
                                            {{ $application->tenant->user_details->workplace['company_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 1')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['address_one'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Address 2')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['address_two'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Postal Code')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['postal_code'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('City')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['city'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Country')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['country'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Off. Phone Number')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['mobile_no'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Off. Mobile Number')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['office_phone'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Email Address')}} </td>
                                        <td class="border-0">:
                                            {{ $application->tenant->user_details->workplace['email'] ?? '' }}</td>
                                    </tr>
                                    @if (!empty($application->tenant->user_details['vehicles_info']))
                                        @foreach ($application->tenant->user_details['vehicles_info'] as $index => $vehicle)
                                            <tr>
                                                <td class="border-0">
                                                    <div class="table-details-header-title mt-4 mb-2">
                                                        <h3> {{__('Vehicles Information')}}  {{ $loop->iteration }}</h3>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name"> {{__('Vehicles Type')}} </td>
                                                <td class="border-0">: {{ $vehicle['type'] ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name"> {{__('Car Registration No')}} </td>
                                                <td class="border-0">: {{ $vehicle['reg_no'] ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name"> {{__('Vehicles Brand')}} </td>
                                                <td class="border-0">: {{ $vehicle['brand'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2">{{ __('No vehicle information available') }}.</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="border-0">
                                            <div class="table-details-header-title mt-4 mb-2">
                                                <h3>{{ __('NID/Passport') }}</h3>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="border-0">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="mykad-img">
                                                    <img class="mkad-image" src="{{ asset($application->tenant->user_details->mykad['front_image'] ?? 'backend/assets/img/icon/mykad.svg') }}"
                                                        alt="">
                                                </div>
                                                <div class="mykad-img">
                                                    <img class="mkad-image" src="{{ asset($application->tenant->user_details->mykad['back_image'] ?? 'backend/assets/img/icon/mykad.svg') }}"
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

            <div class="row">
                <div class="col-lg-6">
                    <div class="info-container mt-3 ">
                        <div class="table-header-title ">
                            <h4>{{ __('Property Detail') }}</h4>
                        </div>
                        <div class="row ">
                            <div class="col-lg-12">
                                <div class="table-responsive mt-3">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Property ID') }}</td>
                                                <td class="border-0 details-content">: {{ $application->property?->puid ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Property Category') }}</td>
                                                <td class="border-0">: {{ $application->property->category->name ?? '' }}</td>
                                            </tr>
                                            @if ($application->unit_id)
                                                <tr>
                                                    <td class="border-0 details-name">{{ __('Unit Name') }}</td>
                                                    <td class="border-0">: {{ $application->unit?->unit_name ?? '' }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Property Name') }}</td>
                                                <td class="border-0">: {{ $application->property->propertyDetail->property_info['property_title'] ?? ''  }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Property Address') }}</td>
                                                <td class="border-0">: {{ $application->property->address_info['address'] ?? '' }}, {{ $application->property->address_info['state'] ?? '' }}, {{ $application->property->address_info['city'] ?? '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($application->status == 0)
                    <form action="{{ route('landlord.application.processing', $application->id) }}" method="post" class="ajaxform_instant_reload" id="landlordAgreementForm" enctype="multipart/form-data">
                        @csrf
                        <div class="info-container mt-3 ">
                            <div class="table-header-title ">
                                <h4>{{ __('Upload Document') }}</h4>
                            </div>
                            <div class="">
                              <div class="upload-field">
                                    <label>
                                        <input type="file" name="landlord_agreement"
                                            class="image-input" data-preview-id="preview1" style="display: none;">
                                        <div class="upload-box">
                                            <img id="preview1" src="" alt="" class="hidden preview-image">
                                            <span class="upload-placeholder"><i class="fas fa-plus"></i></span>
                                        </div>
                                    </label>
                                    <div class="remove-btn d-none">
                                        <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>

                <div class="col-lg-6">
                    <div class="info-container mt-3 ">
                        <div class="table-header-title ">
                            <h4>{{ __('Payment Detail') }}</h4>
                        </div>
                        <div class="row ">
                            <div class="col-lg-12">
                                <div class="table-responsive mt-3">
                                    <table class="table">
                                        <tbody>
                                            @if ($application->property?->category_id !== 6)
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Monthly Rental') }}</td>
                                                <td class="border-0 details-content" >: {{ currency_format($application->property->rent_info['monthly_rent'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Security Deposit') }}</td>
                                                <td class="border-0">: {{ currency_format($application->property?->security_deposit ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Utility Bill') }}</td>
                                                <td class="border-0">: {{ currency_format($application->property?->utility_deposit ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Late Fee') }}</td>
                                                <td class="border-0">: {{ currency_format($application->property?->late_fee ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Late Fee After Days') }}</td>
                                                <td class="border-0">: {{ $application->property?->late_fee_after_days ?? 0 }}</td>
                                            </tr>

                                          @else

                                            <tr>
                                                <td class="border-0 details-name">{{ __('Monthly Rental') }}</td>
                                                <td class="border-0 details-content" >: {{ currency_format($application->unit?->monthly_rent ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Security Deposit') }}</td>
                                                <td class="border-0">: {{ currency_format($application->unit?->security_deposit ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Utility Bill') }}</td>
                                                <td class="border-0">: {{ currency_format($application->unit?->utility_deposit ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Late Fee') }}</td>
                                                <td class="border-0">: {{ currency_format($application->unit?->late_fee ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Late Fee After Days') }}</td>
                                                <td class="border-0">: {{ $application->unit?->late_fee_after_days ?? 0 }}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                @if ($application->status != 0)
                <div class="p-2 col-lg-6">
                    <h6 class="upload-img-title">{{ __('My Rent Agreement PDF') }}</h6>
                    <div class="download-pdf-container pr-3 mt-3">
                        <div class="d-flex align-items-center gap-2 ">
                            <img class="pdf-img" src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}" alt="">
                            <div class="">
                                <h6 class="rent-agree">{{ basename($application->landlord_agreement) }}</h6>
                                <a href="{{ asset($application->landlord_agreement) }}">
                                    {{ __('Pdf') }}
                                </a>
                            </div>
                        </div>
                        <a href="{{ asset($application->landlord_agreement) }}"
                            class="download-icon-container" download>
                            <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                        </a>
                    </div>
                </div>
                @if (!empty($application->tenant_agreement) && Storage::exists($application->tenant_agreement))
                <div class="p-2 col-lg-6">
                    <h6 class="upload-img-title">{{ __('Tenant Rent Agreement PDF') }}</h6>
                    <div class="download-pdf-container pr-3 mt-3">
                        <div class="d-flex align-items-center gap-2 ">
                            <img class="pdf-img" src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}" alt="">
                            <div class="">
                                <h6 class="rent-agree">{{ basename($application->tenant_agreement) }}</h6>
                                <a href="{{ asset($application->tenant_agreement) }}">
                                    {{ __('Pdf') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ asset($application->tenant_agreement) }}"
                            class="download-icon-container" download>
                            <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                        </a>
                    </div>
                </div>
                @endif
                @endif
            </div>

            <div class="col-lg-12">
                <div class="add-landlord-button-group">
                    @if ($application->status == 0)
                    <button type="submit" data-url="{{ route('landlord.application.rejected', $application->id) }}"
                        data-bs-toggle="modal" data-bs-target="#application-rejected-modal"
                        class="btn  border-btn application-rejected-btn">@lang('Reject')</button>
                    <button type="submit" data-bs-toggle="modal" data-bs-target="#application-processing-modal"
                        class="btn theme-btn submit-btn">@lang('Processing')</button>
                    @elseif ($application->status == 1)
                    <button type="submit" data-url="{{ route('landlord.application.rejected', $application->id) }}"
                        data-bs-toggle="modal" data-bs-target="#application-rejected-modal"
                        class="btn  border-btn application-rejected-btn">@lang('Reject')</button>
                    <button type="submit" data-bs-toggle="modal" data-url="{{ route('landlord.application.approved', $application->id) }}"
                        data-bs-target="#application-approved-modal" class="btn theme-btn submit-btn application-approved-btn">@lang('Approved')</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('landlord.application.processing')
    @include('landlord.application.rejected')
    @include('landlord.application.approved')
@endpush
