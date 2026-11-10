@extends('layouts.admin.master')

@section('title')
    {{ __('Property Create') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <form action="{{ route('admin.properties.store') }}" method="post" id="regForm" enctype="multipart/form-data"
            class="ajaxform_instant_reload">
            @csrf
            <div class="row">
                <div>
                    <div class="chart-table">
                        <div class="woodland-card-header border-bottom pb-3">
                            <h3>{{ __('Add New property') }}</h3>
                        </div>
                        <div class="add-property-progress">
                            <ul id="stepList">
                                <li class="step active"><span class="numbering">@lang('1')</span>@lang('Basic Information')</li>
                                <li class="step"><span class="numbering">@lang('2')</span>@lang('Title & Description')</li>
                                <li class="step"><span class="numbering">@lang('3')</span><div id="step-text">@lang('Details')</div></li>
                                <li class="step"><span class="numbering">@lang('4')</span>@lang('Rent Pricing')</li>
                                <li class="step"><span class="numbering">@lang('5')</span>@lang('Contact Us')</li>
                            </ul>
                        </div>
                    </div>
                    <div class="woodland-card mt-30">
                        <div class="wizard-tab-form">
                            <div class="h4 title-nine">{{ __('What Would you like to Post?') }}</div>
                            <div class="woodland-form-wrapper pt-3">
                                <div class="row ">
                                    <div class="col-lg-12">
                                        <label>{{ __('Select A Category') }}</label>
                                        <select class="form-control" id="categoryDropdown" name="category_id" required>
                                            <option value="">{{ __('Select a category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" data-category-name="{{ $category->name }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-12">
                                        <label>{{ __('Select Landlord') }}</label>
                                        <select class="form-control" name="landlord_id" id="landlordSelect" required>
                                            <option value="">{{ __('Select a landlord') }}</option>
                                            @foreach ($landlords as $landlord)
                                                <option value="{{ $landlord->id }}">{{ $landlord->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="radio-group mt-3">
                                        <label class="radio-option">
                                            <input type="radio" name="type" value="rent" checked>
                                            <span class="custom-radio"></span>
                                            <span class="label-text">{{ __('Rent') }}</span>
                                        </label>

                                        <label class="radio-option">
                                            <input type="radio" name="type" value="sale">
                                            <span class="custom-radio"></span>
                                            <span class="label-text">{{ __('Sale') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Common filed for all category --}}
                        <div class="wizard-tab-form">
                            <div class="woodland-form-wrapper">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('Add Title') }}</label>
                                        <input type="text" class="form-control" name="property_info[property_title]" placeholder="{{ __('Enter property title') }}">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('Completion Year') }}</label>
                                        <select class="form-control  reset_val" name="rent_info[completion_year]">
                                            @for ($i = 2050; $i >= 1950; $i--)
                                                <option @selected($i == date('Y')) value="{{ $i }}">
                                                    {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-lg-4 col-md-12 mb-3">
                                        <label>{{ __('Address') }}</label>
                                        <input type="text" class="form-control" name="address_info[address]" placeholder="{{ __('Enter full Address') }}">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('Country') }}</label>
                                        <select class="form-control" name="address_info[country]">
                                            <option selected disabled>{{ __('Select your country') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('State') }}</label>
                                        <input type="text" class="form-control" name="address_info[state]" placeholder="{{ __('Enter your state') }}">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('City') }}</label>
                                        <input type="text" class="form-control" name="address_info[city]" placeholder="{{ __('Enter your city') }}">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label>{{ __('Postal Code') }}</label>
                                        <input type="text" class="form-control" name="address_info[postcode]" placeholder="{{ __('Enter postal code') }}">
                                    </div>

                                    <div class="col-lg-12 mb-3">
                                        <label>{{ __('Description') }}</label>
                                        <textarea class="form-control" rows="3" name="property_info[property_description]" placeholder="{{ __('Enter description...') }}"></textarea>
                                    </div>
                                </div>
                                <div class="container">
                                    <h6 class="mb-3">{{ __('Image') }}</h6>
                                    <div class="row g-3" id="property-image-upload-row">
                                        <!-- Add Cover Photo -->
                                        <div class="col-md-3">
                                            <div class="property-upload-box text-center" onclick="propertyTriggerFile('cover')">
                                                <img src="{{ asset('backend/assets/img/cover.svg') }}" alt="cover" class="property-icon mb-2">
                                                <p class="mb-0 fw-semibold">{{ __('Add Cover Photo') }}</p>
                                                <input type="file" name="cover_image[]" accept="image/*" id="cover" hidden onchange="propertyPreviewImage(this, 'cover')">
                                            </div>
                                        </div>

                                        <!-- Floorplan -->
                                        <div class="col-md-3">
                                            <div class="property-upload-box text-center" onclick="propertyTriggerFile('floorplan')">
                                                <img src="{{ asset('backend/assets/img/floorplan.png') }}" alt="floorplan" class="property-icon mb-2">
                                                <p class="mb-0 fw-semibold">{{ __('Floorplan') }}</p>
                                                <input type="file" name="floorplan_image" accept="image/*" id="floorplan" hidden onchange="propertyPreviewImage(this, 'floorplan')">
                                            </div>
                                        </div>

                                        <!-- Upload More -->
                                        <div class="col-md-3 property-upload-more">
                                            <div class="property-upload-box text-center" onclick="propertyTriggerFile('upload-more-1')">
                                                <img src="{{ asset('backend/assets/img/upload.png') }}" alt="upload" class="property-icon mb-2">
                                                <p class="mb-0 fw-semibold">{{ __('Upload More Image') }}</p>
                                                <input type="file" name="cover_image[]" accept="image/*" id="upload-more-1" hidden onchange="propertyPreviewMoreImage(this)" multiple>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- step 3 others category field here --}}
                        <div class="wizard-tab-form" data-step="3">
                            <div class="property-accordion-container">
                                <div class="property-accordion open">
                                <div class="property-accordion-content">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-3 building_name_div">
                                            <label>{{ __('Building Name') }}</label>
                                            <input type="text" class="form-control building_name reset_val"
                                                name="building_name" placeholder="@lang('Enter Building Name')">
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 house_type_div">
                                            <label>{{ __('House Type') }}</label>
                                            <select class="form-control house_type reset_val" name="house_type_id">
                                                <option value=""> {{__('Select one')}} </option>
                                                @foreach ($house_types as $house_type)
                                                    <option value="{{ $house_type->id }}">{{ $house_type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 property_type_div">
                                            <label>{{ __('Property Type') }}</label>
                                            <select class="form-control property_type reset_val" name="property_type">
                                                <option value="">@lang('Select one')</option>
                                                <option value="Office space">@lang('Office Space')</option>
                                                <option value="Retail space">@lang('Retail Space')</option>
                                                <option value="Shop lot">@lang('Shop lot')</option>
                                                <option value="Warehouse / Factory">@lang('Warehouse / Factory')</option>
                                                <option value="Hotel / Resort">@lang('Hotel / Resort')</option>
                                                <option value="Sofo">@lang('Sofo')</option>
                                                <option value="Soho">@lang('Soho')</option>
                                                <option value="Sovo">@lang('Sovo')</option>
                                                <option value="Others">@lang('Others')</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 land_property_type_div">
                                            <label>{{ __('Property Type') }}</label>
                                            <select class="form-control land_property_type reset_val" name="property_type">
                                                <option value="">@lang('Select one')</option>
                                                <option value="Residential">@lang('Residential')</option>
                                                <option value="Industrial">@lang('Industrial')</option>
                                                <option value="Agricultural">@lang('Agricultural')</option>
                                                <option value="Commercial">@lang('Commercial')</option>
                                                <option value="Mixed Development">@lang('Mixed Development')</option>
                                                <option value="Others">@lang('Others')</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 banglow_property_type_div">
                                            <label>{{ __('Property Type') }}</label>
                                            <select class="form-control banglow_property_type reset_val" name="property_type">
                                                <option value="">@lang('Select one')</option>
                                                <option value="Modern">@lang('Modern')</option>
                                                <option value="Cottage">@lang('Cottage')</option>
                                                <option value="Luxury">@lang('Luxury')</option>
                                                <option value="Eco / Smart">@lang('Eco / Smart')</option>
                                                <option value="Beachside">@lang('Beachside')</option>
                                                <option value="Others">@lang('Others')</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 furnishing_div">
                                            <label>{{ __('Furnishings') }}</label>
                                            <select class="form-control furnishing reset_val" name="furnishing">
                                                <option value="Fully Furnished">@lang('Fully Furnished')</option>
                                                <option value="Partially Furnished">@lang('Partially Furnished')</option>
                                                <option value="Not Furnished">@lang('Not Furnished')</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 residential_type_div">
                                            <label>{{ __('Residential Type') }}</label>
                                            <select class="form-control residential_type reset_val" name="residential_type">
                                                <option value="">{{ __('Select Residential Type') }}</option>
                                                <option value="Flat"> {{__('Flat')}} </option>
                                                <option value="Apartment"> {{__('Apartment')}} </option>
                                                <option value="Condominium"> {{__('Condominium')}} </option>
                                                <option value="Service Residence"> {{__('Service Residence')}} </option>
                                                <option value="Studio"> {{__('Studio')}} </option>
                                                <option value="Duplex"> {{__('Duplex')}} </option>
                                                <option value="Townhouse Condo"> {{__('Townhouse Condo')}} </option>
                                                <option value="Others"> {{__('Others')}} </option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 room_residential_type_div">
                                            <label>{{ __('Residential Type') }}</label>
                                            <select class="form-control room_residential_type reset_val"
                                                name="residential_type">
                                                <option value="">{{ __('Select Residential Type') }}</option>
                                                <option value="Condo / Services residence / Penthouse"> {{__('Condo/Services
                                                    residence/Penthouse')}} </option>
                                                <option value="Apartment / Flat"> {{__('Apartment/Flat')}} </option>
                                                <option value="Houses"> {{__('Houses')}} </option>
                                                <option value="Shoplot"> {{__('Shoplot')}} </option>
                                                <option value="Sharing a house / Flat"> {{__('Sharing a house/Flat')}} </option>
                                                <option value="Others"> {{__('Others')}} </option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 floor_range_div">
                                            <label>{{ __('Floor Range') }}</label>
                                            <input type="text" class="form-control floor_range reset_val" name="room_info[floor_rang]" placeholder="{{ __('Enter Floor Range') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 unit_number_div">
                                            <label>{{ __('Unit Number') }}</label>
                                            <input type="text" class="form-control unit_number reset_val" name="property_info[unit_number]" placeholder="{{ __('Enter Unit Number') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 parking_lot_div">
                                            <label>{{ __('Parking') }}</label>
                                            <input type="text" class="form-control parking_lot reset_val" name="parking_lot" placeholder="{{ __('Enter Parking') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 bedroom_div">
                                            <label>{{ __('Bedroom') }}</label>
                                            <input type="text" class="form-control bedroom reset_val" name="room_info[bedroom]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 bathroom_div">
                                            <label>{{ __('Baths') }}</label>
                                            <input type="text" class="form-control bathroom reset_val" name="room_info[bathroom]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 kitchen_div">
                                            <label>{{ __('Kitchen') }}</label>
                                            <input type="text" class="form-control kitchen reset_val" name="room_info[kitchen]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 balcony_div">
                                            <label>{{ __('Balcony') }}</label>
                                            <input type="text" class="form-control balcony reset_val" name="room_info[balcony]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 condition_div">
                                            <label>{{ __('Condition') }}</label>
                                            <input type="text" class="form-control condition reset_val" name="room_info[condition]" placeholder="{{ __('Enter condition') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 room_size_div">
                                            <label>{{ __('Room Size') }}</label>
                                            <input type="number" class="form-control room_size reset_val"
                                                name="room_info[room_size]" placeholder="{{ __('Enter Room Size') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 property_size_div">
                                            <label>{{ __('Property Size') }}</label>
                                            <input type="text" class="form-control property_size reset_val" name="property_info[property_size]" placeholder="{{ __('Ex: 250 sq.ft.') }}">
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3 land_size_div">
                                            <label>{{ __('Land Size') }}</label>
                                            <input type="number" class="form-control land_size reset_val"
                                                name="property_info[land_size]" placeholder="{{ __('Enter Land Size') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3 lot_number_div">
                                            <label>{{ __('Lot Number') }}</label>
                                            <input type="text" class="form-control lot_number reset_val" name="property_info[lot_number]" placeholder="{{ __('Enter lot number') }}">
                                        </div>

                                        <div class="col-lg-12 tag-grid mt-3">
                                            <div class="facilities_div">
                                                <div class="d-flex align-item-center flex-wrap justify-content-between">
                                                     <div class="facilities_div">
                                                         <div class="d-flex justify-content-between">
                                                             <div class="h4 title-nine">@lang('What are the facilities?')</div>
                                                         </div>
                                                     </div>
                                                     <div
                                                         class="custom-control d-flex align-items-center justify-content-start gap-1 facilities_div">
                                                         <input type="checkbox" class="form-check-input m-0 select-all-facilities">
                                                         <label class="custom-control-label fw-bold mb-0 facilities_div">{{ __('Select All') }}</label>
                                                     </div>
                                                 </div>
                                                 <div class="facilities_div">
                                                     <div class="property-radio-btn-wrapper">
                                                         <ul class="property-radio-btn-list select-all-facility facility_list">

                                                                {{-- dynamic facility come here --}}
                                                         </ul>
                                                     </div>
                                                 </div>
                                            </div>

                                            <div class="">
                                                <div class="d-flex align-item-center justify-content-between flex-wrap">
                                                    <div class="">
                                                        <div class="d-flex justify-content-between">
                                                            <div class="h4 title-nine">@lang('What are the amenities?')</div>
                                                        </div>
                                                    </div>
                                                    <div class="custom-control d-flex align-items-center justify-content-start gap-1 ">
                                                        <input type="checkbox" class=" form-check-input m-0 select-all-amenities">
                                                        <label class="custom-control-label fw-bold mb-0">{{ __('Select All') }}</label>
                                                    </div>
                                                </div>
                                                <div >
                                                    <div class="property-radio-btn-wrapper">
                                                        <ul class="property-radio-btn-list select-all-amenities amenity_list">

                                                            {{-- dynamic amenity come here --}}
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-4">
                                            <div class="d-flex justify-content-between">
                                                <div class="h4 title-nine">@lang('What are the tenant preference?')</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="property-radio-btn-wrapper">
                                                <ul class="property-radio-btn-list">
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="male">
                                                            <span class="checkmark">{{ __('Male') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="female">
                                                            <span class="checkmark">{{ __('Female') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="couple">
                                                            <span class="checkmark">{{ __('Couple') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="family">
                                                            <span class="checkmark">{{ __('Family') }}</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                </div>
                            </div>
                        </div>

                        {{-- step 3 unit category field here --}}
                        <div class="wizard-tab-form unit-wizard">
                            <div class="property-accordion-container" id="propertyAccordionContainer">
                                <div class="property-accordion open">
                                <div class="property-accordion-header">
                                    <strong>#1</strong>
                                    <div class="property-accordion-icons">
                                    <!-- Minus SVG (default open) -->
                                    <button class="property-toggle" title="Toggle">
                                        <svg class="property-icon-minus" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                            stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8 12H16" stroke="#525252" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    <!-- Delete SVG -->
                                    <button class="property-delete" title="Delete">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="#FF3B30" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M9 11.7349H15" stroke="#FF3B30" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M10.5 15.6543H13.5" stroke="#FF3B30" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M3 5.5H21M16.0555 5.5L15.3729 4.09173C14.9194 3.15626 14.6926 2.68852 14.3015 2.39681C14.2148 2.3321 14.1229 2.27454 14.0268 2.2247C13.5937 2 13.0739 2 12.0343 2C10.9686 2 10.4358 2 9.99549 2.23412C9.89791 2.28601 9.80479 2.3459 9.7171 2.41317C9.32145 2.7167 9.10044 3.20155 8.65842 4.17126L8.05273 5.5" stroke="#FF3B30" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    </div>
                                </div>

                                <div class="property-accordion-content">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Unit Name') }}</label>
                                            <input type="text" class="form-control unitName" name="units[][unit_name]" placeholder="{{ __('Enter unit name') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Floor Range') }}</label>
                                            <input type="text" class="form-control" name="units[][floor_range]" placeholder="{{ __('Enter floor name') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Bedroom') }}</label>
                                            <input type="text" class="form-control" name="units[][bedroom]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Bathroom') }}</label>
                                            <input type="text" class="form-control" name="units[][bathroom]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Kitchen') }}</label>
                                            <input type="text" class="form-control" name="units[][kitchen]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Unit Size') }}</label>
                                            <input type="number" class="form-control" name="units[][unit_size]" placeholder="{{ __('Ex: 250') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Balcony') }}</label>
                                            <input type="text" class="form-control" name="units[][balcony]" placeholder="{{ __('Ex: 1') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Condition') }}</label>
                                            <input type="text" class="form-control" name="units[][condition]" placeholder="{{ __('Enter condition') }}">
                                        </div>

                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label>{{ __('Parking') }}</label>
                                            <input type="text" class="form-control" name="units[][parking_lot]" placeholder="{{ __('Enter Parking') }}">
                                        </div>


                                        <div class="col-lg-12 tag-grid mt-3">
                                            <div class="">
                                                <div class="d-flex align-item-center flex-wrap justify-content-between">
                                                     <div class="facilities_div">
                                                         <div class="d-flex justify-content-between">
                                                             <div class="h4 title-nine">@lang('What are the facilities?')</div>
                                                         </div>
                                                     </div>
                                                     <div
                                                         class="custom-control d-flex align-items-center justify-content-start gap-1">
                                                         <input type="checkbox" class="form-check-input m-0 select-all-facilities">
                                                         <label class="custom-control-label fw-bold mb-0">{{ __('Select All') }}</label></div>
                                                 </div>
                                                 <div class="">
                                                     <div class="property-radio-btn-wrapper">
                                                         <ul class="property-radio-btn-list select-all-facility unit_facility_list">

                                                            {{-- dynamic facility come here --}}
                                                         </ul>
                                                     </div>
                                                 </div>
                                            </div>

                                            <div class="">
                                                <div class="d-flex align-item-center justify-content-between flex-wrap">
                                                    <div class="">
                                                        <div class="d-flex justify-content-between">
                                                            <div class="h4 title-nine">@lang('What are the amenities?')</div>
                                                        </div>
                                                    </div>
                                                    <div class="custom-control d-flex align-items-center justify-content-start gap-1">
                                                        <input type="checkbox" class=" form-check-input m-0 select-all-amenities">
                                                        <label class="custom-control-label fw-bold mb-0">{{ __('Select All') }}</label>
                                                    </div>
                                                </div>
                                                <div >
                                                    <div class="property-radio-btn-wrapper">
                                                        <ul class="property-radio-btn-list select-all-amenity unit_amenity_list">
                                                            {{-- dynamic amenity come here --}}

                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 mt-4">
                                            <div class="d-flex justify-content-between">
                                                <div class="h4 title-nine">@lang('What are the tenant preference?')</div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="property-radio-btn-wrapper">
                                                <ul class="property-radio-btn-list">
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="male">
                                                            <span class="checkmark">{{ __('Male') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="female">
                                                            <span class="checkmark">{{ __('Female') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="couple">
                                                            <span class="checkmark">{{ __('Couple') }}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="tag-btn check-btn">
                                                            <input type="checkbox" name="tenant_preference[]"
                                                                value="family">
                                                            <span class="checkmark">{{ __('Family') }}</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- Description -->
                                        <div class="col-lg-12 mb-3 mt-3">
                                            <label>{{ __('Description') }}</label>
                                            <textarea class="form-control" rows="3" name="units[][description]" placeholder="{{ __('Enter description...') }}"></textarea>
                                        </div>
                                    </div>
                                    <div class="container">
                                    <h6 class="mb-3">{{ __('Image') }}</h6>
                                    <div class="row g-3 custom-image-upload-row">
                                        <div class="col-md-3 custom-upload-item custom-upload-add">
                                            <div class="custom-upload-box text-center">
                                                <img src="/backend/assets/img/upload.png" class="custom-upload-icon mb-2">
                                                <p class="mb-0 fw-semibold">{{ __('Upload More Image') }}</p>
                                                <input type="file" accept="image/*" multiple hidden>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                 </div>
                                </div>
                            </div>
                            <span class="property-add-more" id="propertyAddMore">+ {{ __('Add More') }}</span>
                        </div>

                        {{-- step 4 unit and others category dynamically handle  --}}
                        <div class="wizard-tab-form" data-step="4">
                            <!-- Sale fields -->
                            <div class="sale-fields">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label>{{ __('Sale Amount') }}</label>
                                        <input type="number" class="form-control reset_val" name="sale_amount" placeholder="{{ __('Ex: $5000') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Rent fields -->
                            <div class="rent-fields">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label>{{ __('Rent Amount') }}</label>
                                        <input type="number" class="form-control reset_val" name="rent_info[monthly_rent]" placeholder="{{ __('Ex: $5000') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label>{{ __('Minimum Rental period') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control reset_val" name="rent_info[min_rental_value]" placeholder="{{ __('Ex: 1') }}">
                                            <select class="form-select rental-select reset_val" name="rent_info[min_rental_unit]">
                                                <option value="days">{{ __('Days') }}</option>
                                                <option value="weeks">{{ __('Weeks') }}</option>
                                                <option value="months" selected>{{ __('Months') }}</option>
                                                <option value="years">{{ __('Years') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label>{{ __('Security Deposit') }}</label>
                                        <input type="number" class="form-control" name="security_deposit" placeholder="{{ __('Ex: $200') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label>{{ __('Security Deposit Period') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control reset_val" name="rent_info[security_deposit_value]" placeholder="{{ __('Ex: 1') }}">
                                            <select class="form-select rental-select reset_val" name="rent_info[security_deposit_unit]">
                                                <option value="days">{{ __('Days') }}</option>
                                                <option value="weeks">{{ __('Weeks') }}</option>
                                                <option value="months" selected>{{ __('Months') }}</option>
                                                <option value="years">{{ __('Years') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 utility_deposit_div">
                                        <label>{{ __('Utility Deposit') }}</label>
                                        <input type="number" class="form-control reset_val utility_deposit" name="utility_deposit" placeholder="{{ __('Ex: $100') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label>{{ __('Late Fee') }}</label>
                                        <input type="number" class="form-control reset_val" name="late_fee" placeholder="{{ __('Ex: $50') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label>{{ __('Late Fee After Days') }}</label>
                                        <input type="number" class="form-control reset_val" name="late_fee_after_days" placeholder="{{ __('Ex: $50') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Unit Sale --}}
                            <div class="unit-wizard unit-sale">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <label>{{ __('Unit') }}</label>
                                        <select class="form-control unitDropdown">
                                            <option selected disabled>{{ __('Select a Unit') }}</option>

                                        </select>
                                    </div>

                                    <div class="col-md-8 ">
                                        <div class="custom-control d-flex align-items-center gap-2 justify-content-start gap-1 ">
                                            <input type="checkbox" class=" form-check-input m-0" id="unit">
                                            <label class="custom-control-label unit-label mb-0"
                                                for="unit">{{ __('Same rent for all unit') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="unit-accordion-container">
                                    <!-- daynamic Accordion Item here -->

                                </div>
                            </div>

                            {{-- Unit Rent --}}
                            <div class="unit-wizard unit-rent">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <label>{{ __('Unit') }}</label>
                                        <select class="form-control unitDropdown" name="">
                                            <option selected disabled>{{ __('Select a Unit') }}</option>

                                        </select>
                                    </div>

                                    <div class="col-md-8 ">
                                        <div class="custom-control d-flex align-items-center gap-2 justify-content-start gap-1 ">
                                            <input type="checkbox" class=" form-check-input m-0" id="unit">
                                            <label class="custom-control-label unit-label mb-0"
                                                for="unit">{{ __('Same rent for all unit') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="unit-accordion-container">
                                    <!-- daynamic Accordion Item here -->

                                </div>
                            </div>
                        </div>

                        {{-- Common filed for all category --}}
                        <div class="wizard-tab-form">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{ __('Full Name') }}</label>
                                    <input type="text" class="form-control reset_val" name="full_name" placeholder="{{ __('Enter full name') }}">
                                </div>

                                <div class="col-lg-4">
                                    <label>{{ __('Email Address') }}</label>
                                    <input type="text" class="form-control reset_val" name="email" required placeholder="{{ __('Enter email') }}">
                                </div>

                                <div class="col-lg-4">
                                    <label class="custome-label">{{ __('Phone') }}</label>
                                    <div class="phone-input-container">
                                        <div class="country-dropdown">
                                            <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                            <select id="for-country-flag" name="phone[mobile_code]" class="">
                                                @foreach ($phone_codes as $phone_code)
                                                    <option value="{{ $phone_code['dial_code'] }}"
                                                        data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}">
                                                        {{ $phone_code['dial_code'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="number" class="phone-number" name="phone[mobile_no]"
                                            placeholder="{{ __('Enter Phone Number') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-100 wizard-btn-wrapper">
                            <div class="d-flex justify-content-center justify-content-md-center add-landlord-button-group">
                                <button class="btn border-btn" id="prevBtn" type="button">@lang('Previous')</button>
                                <button type="button" class="btn theme-btn nextButton submit-btn" id="nextBtn">@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('asset_js')
    <script src="{{ asset('maan/js/properties.js') }}?v={{ time() }}"></script>
@endpush

