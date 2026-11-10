@extends('layouts.landlord.master')

@section('title')
    {{ __('Rent View') }}
@endsection

@section('main_content')

    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Rent Details') }}</h3>
                @if ($rent->status == 'pending')
                <p class="pending_status">{{ __('Pending') }}</p>
                @elseif($rent->status == 'processing')
                <p class="processing_status">{{ __('Processing') }}</p>
                @elseif($rent->status == 'cancelled')
                <p class="reject_status">{{ __('Cancelled') }}</p>
                @elseif($rent->status == 'active')
                <p class="approve_status">{{ __('Active') }}</p>
                @else
                <p class="reject_status">{{ __('Expired') }}</p>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="info-container ">
                        <div class="table-header-title ">
                            <h4>{{ __('Property Details') }}</h4>
                        </div>
                        <div class="row ">
                            <div class="col-lg-12">
                                <div class="table-responsive mt-3">
                                    <table class="table">
                                        <tbody>
                                            @if ($rent->property?->category_id !== 6)
                                               @if (!empty($rent->property?->propertyDetail?->property_type))
                                                 <tr>
                                                     <td class="border-0 details-name">{{ __('Property Type') }}</td>
                                                     <td class="border-0">: {{ $rent->property?->propertyDetail?->property_type ?? '' }}</td>
                                                 </tr>
                                               @endif
                                                @if (!empty($rent->property?->propertyDetail?->property_info['property_title'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Property Name') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->address_info['address'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Property Address') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->address_info['address'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->propertyDetail?->property_info['lot_number'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Lot Number') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->propertyDetail?->property_info['lot_number'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->propertyDetail?->residential_type ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Residential Type') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->propertyDetail?->residential_type ?? '' }}</td>
                                                    </tr>
                                                @endif
                                               @if (!empty($rent->property?->propertyDetail?->furnishing ?? ''))
                                                 <tr>
                                                     <td class="border-0 details-name">{{ __('Furnishing') }}</td>
                                                     <td class="border-0">: {{ $rent->property?->propertyDetail?->furnishing ?? '' }}</td>
                                                 </tr>
                                               @endif
                                                @if (!empty($rent->property?->room_info['floor_rang'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Floor Range') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->room_info['floor_rang'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->room_info['bedroom'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Bedroom') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->room_info['bedroom'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->room_info['bathroom'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Bathroom') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->room_info['bathroom'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->propertyDetail?->property_info['property_size'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Property Size') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->propertyDetail?->property_info['property_size'] ?? '' }} {{__('sq.ft.')}}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->propertyDetail?->property_info['property_description'] ?? ''))
                                                    <tr>
                                                        <td class="border-0">
                                                            <div class="table-details-header-title mt-3 mb-2">
                                                                <h3>{{ __('Description') }}</h3>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="border-0 details-name">
                                                            <p>{{ $rent->property?->propertyDetail?->property_info['property_description'] ?? '' }}</p>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @else
                                                @if (!empty($rent->property?->propertyDetail?->property_info['property_title'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Property Name') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->address_info['address'] ?? ''))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Property Address') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->address_info['address'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->unit_name))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Unit Name') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->unit_name ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->unit_size))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Unit Size') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->unit_size ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->floor_range))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Floor Range') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->floor_range ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->bedroom))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Bedroom') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->bedroom ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->bathroom))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Bathroom') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->bathroom ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->kitchen))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Kitchen') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->kitchen ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->balcony))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Balcony') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->balcony ?? '' }}</td>
                                                    </tr>
                                                @endif
                                               @if (!empty($rent->unit?->condition))
                                                 <tr>
                                                     <td class="border-0 details-name">{{ __('Condition') }}</td>
                                                     <td class="border-0">: {{ $rent->unit?->condition ?? '' }}</td>
                                                 </tr>
                                               @endif
                                               @if (!empty($rent->unit?->parking_lot))
                                                 <tr>
                                                     <td class="border-0 details-name">{{ __('Parking Lot') }}</td>
                                                     <td class="border-0">: {{ $rent->unit?->parking_lot ?? '' }}</td>
                                                 </tr>
                                               @endif
                                                @if (!empty($rent->unit?->description))
                                                    <tr>
                                                        <td class="border-0">
                                                            <div class="table-details-header-title mt-3 mb-2">
                                                                <h3>{{ __('Description') }}</h3>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="border-0 details-name">
                                                            <p>{{ $rent->unit?->description ?? '' }}</p>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                @if (!empty($rent->property?->propertyDetail?->facilities) || !empty($rent->unit?->facilities))
                                    <div class="table-header-title mt-2 mb-2">
                                        <h4>{{ __('Features') }}<span>{{ __('(Facilities & Amenities )') }}</span></h4>
                                    </div>
                                @endif

                                @if ($rent->property?->category_id !== 6)
                                    @if (!empty($rent->property?->propertyDetail?->facilities))
                                        <p class="check-title mt-3 p-2">{{ __('Facilities') }}</p>
                                        <div class="facilities-container p-2 m-0">
                                            @foreach ($rent->facilities as $facility)
                                            <div class="d-flex align-items-center gap-2">
                                                <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }} alt="">
                                                <h6>{{ $facility }}</h6>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (!empty($rent->property?->propertyDetail?->amenities))
                                        <p class="check-title mt-4 p-2">{{ __('Amenities') }}</p>
                                        <div class="facilities-container p-2 m-0">
                                            @foreach ($rent->amenities as $amenity)
                                            <div class="d-flex align-items-center gap-2">
                                                <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }} alt="">
                                                <h6>{{ $amenity }}</h6>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    @if (!empty($rent->unit?->facilities))
                                        <p class="check-title mt-3 p-2">{{ __('Facilities') }}</p>
                                        <div class="facilities-container p-2 m-0">
                                            @foreach ($rent->facility_names as $facility)
                                            <div class="d-flex align-items-center gap-2">
                                                <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }} alt="">
                                                <h6>{{ $facility }}</h6>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (!empty($rent->unit?->amenities))
                                        <p class="check-title mt-4 p-2">{{ __('Amenities') }}</p>
                                        <div class="facilities-container p-2 m-0">
                                            @foreach ($rent->amenity_names as $amenity)
                                            <div class="d-flex align-items-center gap-2">
                                                <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }} alt="">
                                                <h6>{{ $amenity }}</h6>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                <div class="table-header-title mt-3 mb-2">
                                    <h4>{{ __('Floor Plans') }}</h4>
                                </div>
                                <div class="floor-plan-container">
                                    <img class="" src="{{ asset($rent->property->propertyDetail->floorplan_image ? : 'backend/assets/img/floor.svg') }}" alt="">
                                    <p class="floor-text">{{ $rent->property->room_info['floor_rang'] ?? '' }}</p>
                                </div>

                               @if($rent->tenant_agreement != null)
                               <div class="p-2">
                                <h6 class="upload-img-title">{{ __('Tenant Rent Agreement PDF') }}</h6>
                                <div class="download-pdf-container pr-3 mt-3">
                                    <div class="d-flex align-items-center gap-2 ">
                                        <img class="pdf-img" src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}" alt="">
                                        <div class="">
                                            <h6 class="rent-agree">{{ __('Rent Agreement') }}</h6>
                                            <a href="#">
                                                {{ __('Pdf') }}
                                            </a>
                                        </div>
                                    </div>

                                    <a href="{{ asset($rent->tenant_agreement) }}"
                                        class="download-icon-container" download>
                                        <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                                    </a>
                                </div>
                            </div>
                               @endif

                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6">

                    <div class="swip-container">

                        <swiper-container class="mySwiper" thumbs-swiper=".mySwiper2" space-between="10" navigation="false">

                            @foreach ($rent->property->cover_image ?? [] as $key=>$image)
                            <swiper-slide>
                                <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}" alt="">
                            </swiper-slide>
                            @endforeach

                        </swiper-container>

                        <swiper-container class="mySwiper2" space-between="10" slides-per-view="5" free-mode="true"
                            watch-slides-progress="true">
                            @foreach ($rent->property->cover_image ?? [] as $image)
                            <swiper-slide>
                                <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}" alt="">
                            </swiper-slide>
                        @endforeach
                        </swiper-container>
                    </div>

                    <div class="info-container ">
                        <div class="table-header-title ">
                            <h4>{{ __('Tenant Details') }}</span>
                            </h4>
                        </div>
                        <div class="row ">
                            <div class="col-lg-12">
                                <div class="table-responsive mt-3">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Tenant Name') }}</td>
                                                <td class="border-0">: {{ $rent->tenant?->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Tenant ID') }}</td>
                                                <td class="border-0">: {{ $rent->tenant?->unique_id ?? '' }} </td>
                                            </tr>
                                            <tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Mobile Number') }}</td>
                                                <td class="border-0">:  {{ $rent->tenant?->phone['mobile_no'] ?? ''  }}</td>
                                            </tr>
                                            <td class="border-0 details-name">{{ __('Tenant Email') }}</td>
                                            <td class="border-0">: {{ $rent->tenant?->email ?? '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-container mt-3">
                        <div class="table-header-title ">
                            <h4>{{ __('Payment Detail') }}</h4>
                        </div>
                        <div class="row ">
                            <div class="col-lg-12">
                                <div class="table-responsive mt-3">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Monthly Rental') }}</td>
                                                <td class="border-0">: {{ currency_format($rent->monthly_rent ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Security Deposit') }}</td>
                                                <td class="border-0">: {{ currency_format($rent->security_deposit?->deposit_amount ?? 0) }} </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 details-name">{{ __('Utility Bill') }}</td>
                                                <td class="border-0">: {{ currency_format($rent->utility_bill ?? 0) }} </td>
                                            </tr>
                                            <td class="border-0 details-name">{{ __('Payment Status') }}</td>
                                            <td class="border-0">: {{ ucfirst($rent->security_deposit?->status) }}</td>
                                            </tr>
                                            @if($rent->property?->category_id !== 6)
                                                @if (!empty($rent->property?->late_fee))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Late Fee') }}</td>
                                                        <td class="border-0">: {{ currency_format($rent->property?->late_fee) }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->property?->late_fee_after_days))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Late Fee After Days') }}</td>
                                                        <td class="border-0">: {{ $rent->property?->late_fee_after_days }}</td>
                                                    </tr>
                                                @endif
                                            @else
                                                @if (!empty($rent->unit?->late_fee))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Late Fee') }}</td>
                                                        <td class="border-0">: {{ currency_format($rent->unit?->late_fee) }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($rent->unit?->late_fee_after_days))
                                                    <tr>
                                                        <td class="border-0 details-name">{{ __('Late Fee After Days') }}</td>
                                                        <td class="border-0">: {{ $rent->unit?->late_fee_after_days }}</td>
                                                    </tr>
                                                @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-2">
                                    <h6 class="upload-img-title">{{ __('Rent Agreement PDF') }}</h6>
                                    <div class="download-pdf-container pr-3 mt-3">
                                        <div class="d-flex align-items-center gap-2 ">
                                            <img class="pdf-img" src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}" alt="">
                                            <div class="">
                                                <h6 class="rent-agree">{{ __('Rent Agreement') }}</h6>
                                                <a href="#">
                                                    {{ __('Pdf') }}
                                                </a>
                                            </div>
                                        </div>

                                        <a href="{{ asset($rent->landlord_agreement) }}"
                                            class="download-icon-container" download>
                                            <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($rent->status == 'processing' && $rent->security_deposit?->status == "paid")
            <div class="col-lg-12">
               <div class="add-landlord-button-group">
                   <button type="submit" data-bs-toggle="modal" data-bs-target="#rent-confirmation-modal" class="btn theme-btn submit-btn">{{ __('Approve') }}</button>
               </div>
           </div>
           @endif

           @if ($rent->status == 'active')
            <div class="col-lg-12">
               <div class="add-landlord-button-group">
                   <button type="submit" data-bs-toggle="modal" data-bs-target="#rent-payment-cancel-modal" class="btn  border-btn">{{ __('Cancel') }}</button>
               </div>
           </div>
           @endif
        </div>
    </div>
@endsection

@push('asset_js')
    <script src="{{ asset('maan/js/properties.js') }} "></script>
@endpush

@push('modal')
    @include('landlord.rents.confirm')
    @include('landlord.rents.cancel')
@endpush
