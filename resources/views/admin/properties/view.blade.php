@extends('layouts.admin.master')

@section('title')
    {{ __('Property View') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Property View') }}</h3>
                <div class="">
                    @if ($property->status == '0')
                    <button class="pending-btn">{{ __('Pending') }}</button>
                    @elseif($property->status == '1')
                    <button class="active-btn">{{ __('Active') }}</button>
                    @elseif($property->status == '2')
                    <button class="inactive-btn">{{ __('Inactive') }}</button>
                    @else
                    <button class="rejected-btn">{{ __('Rejected') }}</button>
                    @endif
                    <a href="{{ route('admin.properties.index') }}" class="text-black"><i class="far fa-times fs-5"></i></a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mt-2">
                        <div class="mt-2">
                           <div class="d-flex align-items-end justify-content-between">
                            <div class="">
                                 <p class="mb-2">{{ __('Landlord') }}: <span class="text-black">{{ $property->landlord?->name }}</span></p>
                                <h4 class="preview-title">{{ $property->propertyDetail->property_info['property_title'] ?? '' }}</h4>
                                @if ($property->status == '0')
                                    <p class="mt-2"> {{__('Status :')}} <span class="pending_status"> {{__('Pending')}} </span></p>
                                @elseif($property->status == '1')
                                    <p class="mt-2"> {{__('Status :')}} <span class="approve_status"> {{__('Active')}} </span></p>
                                @elseif($property->status == '2')
                                    <p class="mt-2"> {{__('Status :')}} <span class="procesing_status"> {{__('Inactive')}} </span></p>
                                @else
                                    <p class="mt-2"> {{__('Status :')}} <span class="reject_status"> {{__('Rejected')}} </span></p>
                                @endif

                                @if (!empty($property->address_info['address'] ?? ''))
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <img class="location-icon" src={{ asset('backend/assets/img/location.svg') }}>
                                        <p>{{ $property->address_info['address'] ?? '' }},
                                            {{ $property->address_info['state'] ?? '' }},{{ $property->address_info['city'] ?? '' }}
                                        </p>
                                    </div>
                                @endif
                                <h5 class="preview-price mt-2">{{ currency_format($property->rent_info['monthly_rent'] ?? 0) }}
                                    /
                                    <span> {{__('Month')}} </span>
                                </h5>
                            </div>
                            <div class="button-group">
                                <button class="rent-btn">{{ __(ucfirst($property->type)) }}</button>
                                <button class="flat-btn">{{ $property->category->name ?? '' }}</button>
                            </div>

                           </div>
                            <hr class="preview-hr">

                            @if (!empty($property->propertyDetail?->property_info['property_description'] ?? ''))
                                <h5 class="preview-des"> {{__('Description')}} </h5>
                                <p class="preview-pera">
                                    {{ $property->propertyDetail->property_info['property_description'] ?? '' }}
                                </p>
                            @endif

                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td colspan='2' class="border-0 p-0 pt-3 mb-2">
                                            <div class="table-header-title mt-2 mb-2">
                                                <h4 class=""> {{__('Building Details')}} </h4>
                                            </div>
                                        </td>
                                    </tr>
                                    @if (!empty($property->building_name))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Building Name')}} </td>
                                            <td class="border-0 wrap-table-data">: {{ $property->building_name }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->address_info['address'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Property Address')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->address_info['address'] ?? '' }},
                                                {{ $property->address_info['state'] ?? '' }},{{ $property->address_info['city'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->rent_info['completion_year'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Completion Year')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->rent_info['completion_year'] ?? '' }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->propertyDetail?->property_info['lot_number'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Lot Number')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->propertyDetail->property_info['lot_number'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan='2' class="border-0 p-0 pt-3 mb-2">
                                            <div class="table-header-title mt-2 mb-2">
                                                <h4> {{__('Property Types')}} </h4>
                                            </div>
                                        </td>
                                    </tr>
                                    @if (!empty($property->propertyDetail?->residential_type))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Residential Type')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->propertyDetail->residential_type ?? '' }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->propertyDetail?->furnishing))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Furnishing')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->propertyDetail->furnishing ?? '' }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->room_info['floor_rang'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Floor Range')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->room_info['floor_rang'] ?? '' }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->room_info['bedroom'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Bedroom')}} </td>
                                            <td class="border-0 wrap-table-data">: {{ $property->room_info['bedroom'] ?? '' }}
                                                {{__('beds')}} </td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->room_info['bathroom'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Bathroom')}} </td>
                                            <td class="border-0 wrap-table-data">: {{ $property->room_info['bathroom'] ?? '' }}
                                                {{__('baths')}} </td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->propertyDetail?->property_info['property_size'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Property Size')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->propertyDetail->property_info['property_size'] ?? '' }}  {{__('sq.ft.')}}
                                            </td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->security_deposit))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Security Deposit')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ currency_format($property->security_deposit ?? 0) }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if (!empty($property->utility_deposit))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Utility Deposit')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ currency_format($property->utility_deposit ?? 0) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="">
                        <swiper-container class="mySwiper" thumbs-swiper=".mySwiper2" space-between="10" navigation="false">
                            @foreach ($property->cover_image ?? [] as $key => $image)
                                <swiper-slide>
                                    <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}"
                                        alt="">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>

                        <swiper-container class="mySwiper2" space-between="10" slides-per-view="5" free-mode="true"
                            watch-slides-progress="true">
                            @foreach ($property->cover_image ?? [] as $image)
                                <swiper-slide>
                                    <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}"
                                        alt="">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                    <div class="info-container mt-3">
                        @if (!empty($property->propertyDetail?->facilities) || !empty($property->propertyDetail?->amenities))
                            <div class="table-header-title ">
                                <h4>{{ __('Features') }} <span>({{ __('Facilities & Amenities') }})</span></h4>
                            </div>
                        @endif
                        <div class="p-2">
                            @if (!empty($property->propertyDetail?->facilities))
                                <p class="check-title mt-3">{{ __('Facilities') }}</p>
                                <div class="facilities-container">

                                    @foreach ($property->facilities as $facility)
                                        <div class="d-flex align-items-center gap-2">
                                            <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }}
                                                alt="">
                                            <h6>{{ $facility }}</h6>
                                        </div>
                                    @endforeach

                                </div>
                            @endif
                            @if (!empty($property->propertyDetail?->amenities))
                                <p class="check-title mt-4">{{ __('Amenities') }}</p>
                                <div class="facilities-container">
                                    @foreach ($property->amenities as $amenity)
                                        <div class="d-flex align-items-center gap-2">
                                            <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }}
                                                alt="">
                                            <h6>{{ $amenity }}</h6>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="info-container mt-3">
                        <div class="table-header-title ">
                            <h4>{{ __('Floor Plans') }}</h4>
                        </div>
                        <div class="floor-plan-container">
                            <img class=""
                                src={{ asset($property->propertyDetail->floorplan_image ?? 'backend/assets/img/floor.svg') }}
                                alt="">
                            <p class="floor-text">{{ $property->room_info['floor_rang'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($property->status == "0" || $property->status == "2")
            <div class="add-landlord-button-group">
                <button type="button" class="btn  border-btn" data-bs-toggle="modal" data-bs-target="#property-reject-modal">{{ __('Rejected') }}</button>
                <button type="submit" class="btn theme-btn submit-btn" data-bs-toggle="modal" data-bs-target="#property-approval-modal">{{ __('Approved') }}</button>
            </div>
            @endif

        </div>
    </div>

@endsection

@push('modal')
    @include('admin.properties.rejected')
    @include('admin.properties.approved')
@endpush


