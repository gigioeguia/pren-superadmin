@extends('layouts.admin.master')

@section('title')
    {{__('Property Review')}}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="w-1100">
                <div class="woodland-card mt-30">
                    <div class="woodland-card-header">
                        <div class="h4 title-nine">@lang('Thank you for posting on') {{ get_option('general')['app_name'] }}</div>
                    </div>
                    <div class="success-aler-msg">
                        <p><svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1188_10295)">
                                    <path
                                        d="M7.99967 14.6667C11.6816 14.6667 14.6663 11.6819 14.6663 8.00004C14.6663 4.31814 11.6816 1.33337 7.99967 1.33337C4.31778 1.33337 1.33301 4.31814 1.33301 8.00004C1.33301 11.6819 4.31778 14.6667 7.99967 14.6667Z"
                                        stroke="#00A551" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M8 5.33337V8.00004" stroke="#00A551" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M8 10.6666H8.00667" stroke="#00A551" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1188_10295">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                           @lang('Admin has successfully created a property for') <strong>{{ $property->landlord->name }}</strong>.
                        </p>
                    </div>

                    <div class="submit-container">
                        <div class="submit-inner-container">
                            <div class="">
                                <div class="submit-img">
                                    <img src="{{ asset($property->cover_image[0] ?? 'backend/assets/img/property-img/add-photo/camera.png') }}"
                                        alt="">
                                    <h6 class="pending-status start-50 translate-middle">
                                        {{ $property->status == 0 ? __('Pending') : ($property->status == 1 ? __('Approved') : ($property->status == 2 ? __('Inactive') : __('Rejected'))) }}
                                    </h6>
                                    <div class="wishlist">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_906_74954)">
                                                <path
                                                    d="M10.3219 2.38664C10.0722 2.13682 9.77574 1.93864 9.44943 1.80343C9.12312 1.66822 8.77336 1.59863 8.42015 1.59863C8.06693 1.59863 7.71717 1.66822 7.39086 1.80343C7.06455 1.93864 6.76807 2.13682 6.51837 2.38664L6.00015 2.90486L5.48192 2.38664C4.97754 1.88225 4.29345 1.59889 3.58014 1.59889C2.86684 1.59889 2.18275 1.88225 1.67837 2.38664C1.17398 2.89102 0.890625 3.57511 0.890625 4.28841C0.890625 5.00172 1.17398 5.68581 1.67837 6.19019L2.19659 6.70841L6.00015 10.512L9.8037 6.70841L10.3219 6.19019C10.5717 5.94049 10.7699 5.64401 10.9051 5.3177C11.0403 4.99139 11.1099 4.64163 11.1099 4.28841C11.1099 3.9352 11.0403 3.58544 10.9051 3.25913C10.7699 2.93282 10.5717 2.63634 10.3219 2.38664V2.38664Z"
                                                    stroke="white" stroke-width="1.2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_906_74954">
                                                    <rect width="11.7333" height="11.7333" fill="white"
                                                        transform="translate(0.132812 0.132812)" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="submit-content">
                                    <p class="name">{{ __('Landlord') }}:
                                        <span>{{ $property->landlord->name ?? '' }}</span></p>
                                    <hr>
                                    <p class="property-name">
                                        {{ $property->propertyDetail->property_info['property_title'] ?? '' }}</p>
                                    <p class="house">{{ $property->category->name ?? '' }}</p>
                                    <div class="d-flex align-items-center gap-1 ">
                                        <svg width="10" height="11" viewBox="0 0 10 11" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_906_76242)">
                                                <path
                                                    d="M8.75 5.16699C8.75 8.08366 5 10.5837 5 10.5837C5 10.5837 1.25 8.08366 1.25 5.16699C1.25 4.17243 1.64509 3.2186 2.34835 2.51534C3.05161 1.81208 4.00544 1.41699 5 1.41699C5.99456 1.41699 6.94839 1.81208 7.65165 2.51534C8.35491 3.2186 8.75 4.17243 8.75 5.16699Z"
                                                    stroke="#F23B3D" stroke-linecap="round" stroke-linejoin="round" />
                                                <path
                                                    d="M5 6.41699C5.69036 6.41699 6.25 5.85735 6.25 5.16699C6.25 4.47664 5.69036 3.91699 5 3.91699C4.30964 3.91699 3.75 4.47664 3.75 5.16699C3.75 5.85735 4.30964 6.41699 5 6.41699Z"
                                                    stroke="#F23B3D" stroke-linecap="round" stroke-linejoin="round" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_906_76242">
                                                    <rect width="10" height="11" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <p class="location">
                                            {{ ($property->address_info['address'] ?? '') . ', ' . ($property->address_info['city'] ?? '') . ', ' . ($property->address_info['state'] ?? '') }}
                                        </p>
                                    </div>
                                    @php $firstUnit = $property->units->first(); @endphp
                                    <h6 class="price">
                                        @if ($property->category_id == 6)
                                        <span>{{ currency_format($firstUnit->monthly_rent ?? 0) }}</span> /
                                        {{ $firstUnit->min_rental_value ?? '' }} {{ $firstUnit->min_rental_unit ?? '' }}
                                        @else
                                        <span>{{ currency_format($property->rent_info['monthly_rent'] ?? 0) }}</span> /
                                        {{ $property->rent_info['min_rental_value'] ?? '' }} {{ $property->rent_info['min_rental_unit'] ?? '' }}
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-100 wizard-btn-wrapper">
                        <div class="add-landlord-button-group ">
                            <a href="{{ route('admin.properties.show', $property->id) }}"
                                class="btn outline-btn">@lang('Browse your property')</a>
                            <a href="{{ route('admin.properties.create') }}" class="btn theme-btn">@lang('Post Another Property')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
