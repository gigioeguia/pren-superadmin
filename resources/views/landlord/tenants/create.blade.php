@extends('layouts.landlord.master')

@section('title')
    {{__('Create Tenant')}}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card-header">
            <h3>{{ __('Add tenant') }}</h3>
        </div>
        <div class="woodland-card">
            <form action="{{ route('landlord.tenants.store') }}" method="POST" class="ajaxform_instant_reload">
                @csrf
                <div class="row">

                    <div class="col-lg-4 ">
                        <label>{{ __('Types of Tenant') }}*</label>
                        <select class="form-control country reset_val" name="profile_type" required>
                            <option value="">{{ __('Select One') }}</option>
                            <option value="company">{{ __('Company') }}</option>
                            <option value="privateIndividual">{{ __('Private') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Full Name') }}</label>
                        <input type="text" class="form-control reset_val" name="name" placeholder="{{ __('Enter full name') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Email Address') }}</label>
                        <input type="text" class="form-control reset_val" name="email" required placeholder="{{ __('Enter email') }}">
                    </div>

                    <div class="col-lg-4">
                        <label for="password">{{ __('Password') }}</label>
                        <input type="password" name="password" required class="form-control" placeholder="******">
                    </div>

                    <div class="col-lg-4">
                        <label class="custome-label">{{ __('Phone') }}</label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="phone[mobile_code]" class="">
                                    @foreach($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}">
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="number" class="phone-number" name="phone[mobile_no]" placeholder="{{ __('Enter Phone Number') }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Address1')}} </label>
                        <input type="text" class="form-control" name="address_info[address_one]" placeholder="{{ __('Enter Address1') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Address2')}} </label>
                        <input type="text" class="form-control" name="address_info[address_two]" placeholder="{{ __('Enter Address2') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Postal Code')}} </label>
                        <input type="number" class="form-control" name="address_info[postal_code]" placeholder="{{ __('Enter Postal code') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('City')}} </label>
                        <input type="text" class="form-control" name="address_info[city]" placeholder="{{ __('Enter city') }}" value="">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('State')}} </label>
                        <input type="text" class="form-control" name="address_info[state]" placeholder="{{ __('Enter state') }}" value="">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Country Name')}} </label>
                        <select class="form-control" name="address_info[country]">
                            <option value=""> {{__('Select Country')}} </option>
                            @foreach ($countries as $country)
                            <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Date of Birth') }}</label>
                        <input type="date" name="birth_date" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Gender') }}</label>
                        <select class="form-control" name="gender">
                            <option value="">{{ __('Select Gender') }}</option>
                            <option value="Male">{{ __('Male') }}</option>
                            <option value="Female">{{ __('Female') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('NID/Passport Id') }}</label>
                        <input type="text" class="form-control " name="mykad_id" placeholder="{{ __('Enter NID/Passport Id') }}">
                    </div>

                    <div class="">
                        <h4 class="form-inner-title">{{ __('Nominee') }}</h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label>{{ __('Nominee Name') }}</label>
                        <input type="text" class="form-control " name="nominee_info[name]" placeholder="{{ __('Enter Name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label>{{ __('Nominee Email') }}</label>
                        <input type="text" class="form-control " name="nominee_info[email]" placeholder="{{ __('Enter Email') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label>{{ __('Mobile Number') }}</label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="nominee_info[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}">
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="nominee_info[mobile_no]" placeholder="{{ __('Enter phone number') }}" />
                        </div>
                    </div>
                    <div class="">
                        <h4 class="form-inner-title">{{ __('Emergency Contact') }}</h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label>{{ __('Relation With You') }}</label>
                        <select class="form-control" name="emergency_contact[relation_with]">
                            <option value="Wife">{{ __('Wife') }}</option>
                            <option value="Parent">{{ __('Parent') }}</option>
                            <option value="Friend">{{ __('Friend') }}</option>
                            <option value="Brother">{{ __('Brother') }}</option>
                            <option value="Sister">{{ __('Sister') }}</option>
                            <option value="Child">{{ __('Child') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Name')}} </label>
                        <input type="text" class="form-control" name="emergency_contact[name]" placeholder="{{ __('Enter Name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Mobile Number')}} </label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="emergency_contact[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}">
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="emergency_contact[mobile_no]" placeholder="{{ __('Enter Mobile Number') }}" />
                        </div>
                    </div>

                    <div class="">
                        <h4 class="form-inner-title"> {{__('Company')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company Name')}} </label>
                        <input type="text" class="form-control " name="company_info[company_name]" placeholder="{{ __('Enter company name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company SSM No')}}  </label>
                        <input type="number" class="form-control" name="company_info[company_ssm_no]" placeholder="{{ __('Enter company ssm no') }}">
                    </div>

                    <div>
                        <h4 class="form-inner-title"> {{__('Workplace')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company Name')}} </label>
                        <input type="text" class="form-control" name="workplace[company_name]" placeholder="{{ __('Enter company name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Address1')}} </label>
                        <input type="text" class="form-control" name="workplace[address_one]" placeholder="{{ __('Enter Address1') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Address2')}} </label>
                        <input type="text" class="form-control" name="workplace[address_two]" placeholder="{{ __('Enter Address2') }}">
                    </div>

                    <div class="col-lg-4 ">
                        <label> {{__('Postal Code')}} </label>
                        <input type="number" class="form-control" name="workplace[postal_code]" placeholder="{{ __('Enter Postal code') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('City')}} </label>
                        <input type="text" class="form-control" name="workplace[city]" placeholder="{{ __('Enter city') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('State')}} </label>
                        <input type="text" class="form-control" name="workplace[state]" placeholder="{{ __('Enter state') }}" value="">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Country Name')}} </label>
                        <select class="form-control" name="workplace[country]">
                            <option value=""> {{__('Select Country')}} </option>
                            @foreach ($countries as $country)
                            <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 ">
                        <label> {{__('Office Phone Number')}} </label>
                        <input type="number" class="form-control" name="workplace[office_phone]" placeholder="{{ __('Enter phone number') }}">
                    </div>

                    <div class="col-lg-4 ">
                        <label> {{__('Office Mobile Number')}} </label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="workplace[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}">
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="workplace[office_phone]" placeholder="{{ __('Enter phone number') }}" />
                        </div>
                    </div>

                    <div>
                        <h4 class="form-inner-title"> {{__('Vehicles Information (Optional)')}} </h4>
                    </div>


                    <div class="row" id="vehicles-container">
                        <div class="col-lg-4 mt-4">
                            <label> {{__('Vehicles Type')}} </label>
                            <select class="form-control" name="vehicles_info[0][type]">
                                <option value=""> {{__('Car/Motorcycles etc')}} </option>
                                <option value="Car">{{ __('Car') }}</option>
                                <option value="Motorcycles">{{ __('Motorcycles') }}</option>
                                <option value="Lorry">{{ __('Lorry') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4 mt-4">
                            <label> {{__('Registration No')}} </label>
                            <input type="number" class="form-control" name="vehicles_info[0][reg_no]" placeholder="{{ __('Enter Registration no') }}">
                        </div>

                        <div class="col-lg-4 mt-4">
                            <label> {{__('Vehicles Brand')}} </label>
                            <input type="text" class="form-control" name="vehicles_info[0][brand]" placeholder="{{ __('Enter Vehicles Brand') }}">
                        </div>
                    </div>


                    <a href="#" class="d-flex align-items-center gap-1 add-Vehicles-container">
                        <img class="add-Vehicles" src="{{ asset('backend/assets/img/icon/add-new.svg') }}" alt="">
                        <h6> {{__('Add New Vehicles')}} </h6>
                    </a>



                    <div class="d-flex align-items-center flex-wrap gap-5 mt-4">

                        <div>
                            <h6 class="upload-title"> {{__('Profile Image (optional)')}} </h6>
                            <div class="upload-field-profile mt-4">
                                <label>
                                    <input class="hidden-input image-input" type="file" accept="image/*" name="image" data-preview-id="profile-upload">
                                    <div class="upload-box">
                                        <img id="profile-upload" src="" alt="" class="hidden preview-image">
                                        <span class="upload-placeholder"><i class="fas fa-plus"></i></span>
                                    </div>
                                </label>
                                <div class="remove-btn d-none">
                                    <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h6 class="upload-title"> {{__('Upload NID / Passport')}} </h6>
                            <p class="upload-pera pb-2 pt-1"> {{__('Only file type image will be accepted. File limit up to 2.5 MB')}}  </p>
                                <div class="image-upload-container">
                                <div class="upload-field">
                                    <label>
                                        <input type="file" accept="image/*" name="mykad[front_image]"
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

                                    <div class="upload-field">
                                        <label>
                                            <input type="file" accept="image/*" name="mykad[back_image]"
                                                class="image-input" data-preview-id="preview2" style="display: none;">
                                            <div class="upload-box">
                                                <img id="preview2" src="" alt="" class="hidden preview-image">
                                                <span class="upload-placeholder"><i class="fas fa-plus"></i></span>
                                            </div>
                                        </label>
                                        <div class="remove-btn d-none">
                                            <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="add-landlord-button-group">
                    <button type="button" class="btn border-btn" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
