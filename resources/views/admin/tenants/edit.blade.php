@extends('layouts.admin.master')

@section('title')
    {{ __('Tenant Edit') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Edit Tenant') }}</h3>
            </div>
            <form action="{{ route('admin.tenants.update',$tenant->id) }}" method="POST" class="ajaxform_instant_reload">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-4">
                        <label>{{ __('Types of Tenant') }}*</label>
                        <select class="form-control country reset_val" name="profile_type"  required>
                            <option value="">{{ __('Select One') }}</option>
                            <option @selected($tenant->profile_type == 'company') value="company">{{ __('Company') }}</option>
                            <option @selected($tenant->profile_type == 'privateIndividual') value="privateIndividual">{{ __('Private') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Full Name') }}</label>
                        <input type="text" class="form-control reset_val" name="name" value="{{ $tenant->name ?? '' }}" placeholder="{{ __('Enter full name') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Email Address') }}</label>
                        <input type="text" class="form-control reset_val" name="email" value="{{ $tenant->email ?? '' }}" required placeholder="{{ __('Enter email') }}">
                    </div>
                    <div class="col-lg-4">
                        <label for="city">{{ __('Password') }}</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>

                    <div class="col-lg-4">
                        <label class="custome-label">{{ __('Phone') }}</label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="phone[mobile_code]" class="select2">
                                    @foreach($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                            {{ ($tenant->phone['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="number" class="phone-number" name="phone[mobile_no]" value="{{ $tenant->phone['mobile_no'] ?? '' }}" placeholder="{{ __('Enter Phone Number') }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Address1')}} </label>
                        <input type="text" class="form-control" name="address_info[address_one]" value="{{ $tenant->user_details->address_info['address_one'] ?? '' }}" placeholder="{{ __('Enter Address1') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Address2')}} </label>
                        <input type="text" class="form-control" name="address_info[address_two]" value="{{ $tenant->user_details->address_info['address_two'] ?? '' }}" placeholder="{{ __('Enter Address2') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Postal Code')}} </label>
                        <input type="number" class="form-control" name="address_info[postal_code]" value="{{ $tenant->user_details->address_info['postal_code'] ?? ''}}" placeholder="{{ __('Enter Postal code') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('City')}} </label>
                        <input type="text" class="form-control" name="address_info[city]" value="{{ $tenant->user_details->address_info['city'] ?? ''}}" placeholder="{{ __('Enter city') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('State')}} </label>
                        <input type="text" class="form-control" name="address_info[state]" value="{{ $tenant->user_details->address_info['state'] ?? ''}}" placeholder="{{ __('Enter state') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Country Name')}} </label>
                        <select class="form-control" name="address_info[country]">
                            <option value=""> {{__('Select Country')}} </option>
                            @foreach ($countries as $country)
                                <option value="{{ $country['name'] }}" @selected($tenant->user_details->address_info['country'] ?? '' == $country['name'])>{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Date of Birth')}} </label>
                        <input type="date" name="birth_date" value="{{ $tenant->user_details?->birth_date ?? '' }}" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Gender') }}</label>
                        <select class="form-control" name="gender">
                            <option value="">{{ __('Select Gender') }}</option>
                            <option @selected(optional($tenant->user_details)->gender == 'Male') value="Male">{{ __('Male') }}</option>
                            <option @selected(optional($tenant->user_details)->gender == 'Female') value="Female">{{ __('Female') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('NID/Passport Id')}} </label>
                        <input type="text" class="form-control" name="mykad_id" value="{{ $tenant->user_details?->mykad_id ?? '' }}" placeholder="{{ __('Enter NID/Passport Id') }}">
                    </div>

                    <div class="">
                        <h4 class="form-inner-title"> {{__('Nominee')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Nominee Name')}} </label>
                        <input type="text" class="form-control" name="nominee_info[name]" value="{{ $tenant->user_details->nominee_info['name'] ?? '' }}" placeholder="{{ __('Enter Name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Nominee Email')}} </label>
                        <input type="text" class="form-control" name="nominee_info[email]" value="{{ $tenant->user_details->nominee_info['email'] ?? ''}}" placeholder="{{ __('Enter Email') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Mobile Number')}} </label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="nominee_info[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                            {{ ($tenant->user_details->nominee_info['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="nominee_info[mobile_no]" value="{{ $tenant->user_details->nominee_info['mobile_no'] ?? ''}}" placeholder="{{ __('Phone number') }}" />
                        </div>
                    </div>

                    <div>
                        <h4 class="form-inner-title"> {{__('Emergency Contact')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label>{{ __('Relation With You') }}</label>
                        <select class="form-control" name="emergency_contact[relation_with]">
                            <option value="parent" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Wife')>{{ __('Wife') }}</option>
                            <option value="parent" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Parent')>{{ __('Parent') }}</option>
                            <option value="parent" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Friend')>{{ __('Friend') }}</option>
                            <option value="brother" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Brother')>{{ __('Brother') }}</option>
                            <option value="sister" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Sister')>{{ __('Sister') }}</option>
                            <option value="sister" @selected($tenant->user_details->emergency_contact['relation_with'] ?? '' == 'Child')>{{ __('Child') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Name')}} </label>
                        <input type="text" class="form-control" name="emergency_contact[name]" value="{{ $tenant->user_details->emergency_contact['name'] ?? ''}}" placeholder="{{ __('Enter Name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Mobile Number')}} </label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="emergency_contact[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                            {{ ($tenant->user_details->emergency_contact['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="emergency_contact[mobile_no]" value="{{ $tenant->user_details->emergency_contact['mobile_no'] ?? '' }}" placeholder="{{ __('Phone number') }}" />
                        </div>
                    </div>

                    <div class="">
                        <h4 class="form-inner-title"> {{__('Company')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company Name')}} </label>
                        <input type="text" class="form-control" name="company_info[company_name]" value="{{ $tenant->user_details->company_info['company_name'] ?? ''}}" placeholder="{{ __('Enter company name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company SSM No')}}  </label>
                        <input type="number" class="form-control" name="company_info[company_ssm_no]" value="{{ $tenant->user_details->company_info['company_ssm_no'] ?? ''}}" placeholder="{{ __('Enter company ssm no') }}">
                    </div>

                    <div>
                        <h4 class="form-inner-title"> {{__('Workplace')}} </h4>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Company Name')}}  </label>
                        <input type="text" class="form-control" name="workplace[company_name]" value="{{ $tenant->user_details->workplace['company_name'] ?? '' }}" placeholder="{{ __('Enter company name') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Address1')}} </label>
                        <input type="text" class="form-control" name="workplace[address_one]" value="{{ $tenant->user_details->workplace['address_one'] ?? '' }}" placeholder="{{ __('Enter Address1') }}">
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label> {{__('Address2')}} </label>
                        <input type="text" class="form-control" name="workplace[address_two]" value="{{ $tenant->user_details->workplace['address_two'] ?? '' }}" placeholder="{{ __('Enter Address2') }}">
                    </div>

                    <div class="col-lg-4 ">
                        <label> {{__('Postal Code')}} </label>
                        <input type="number" class="form-control" name="workplace[postal_code]" value="{{ $tenant->user_details->workplace['postal_code'] ?? ''}}" placeholder="{{ __('Enter Postal code') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('City')}} </label>
                        <input type="text" class="form-control" name="workplace[city]" value="{{ $tenant->user_details->workplace['city'] ?? ''}}" placeholder="{{ __('Enter city') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('State')}} </label>
                        <input type="text" class="form-control" name="workplace[state]" value="{{ $tenant->user_details->workplace['state'] ?? ''}}" placeholder="{{ __('Enter state') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Country Name')}} </label>
                        <select class="form-control" name="workplace[country]">
                            <option value=""> {{__('Select Country')}} </option>
                            @foreach ($countries as $country)
                                <option value="{{ $country['name'] }}" @selected($tenant->user_details->workplace['country'] ?? '' == $country['name'])>{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 ">
                        <label> {{__('Office Phone Number')}} </label>
                        <input type="number" class="form-control" name="workplace[office_phone]" value="{{ $tenant->user_details->workplace['office_phone'] ?? '' }}" placeholder="{{ __('Enter phone number') }}">
                    </div>

                    <div class="col-lg-4">
                        <label> {{__('Office Mobile Number')}} </label>
                        <div class="phone-input-container">
                           <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="workplace[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                            {{ ($tenant->user_details->workplace['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="tel" class="phone-number" name="workplace[office_phone]" value="{{ $tenant->user_details->workplace['office_phone'] ?? '' }}" placeholder="{{ __('Enter phone number') }}" />
                        </div>
                    </div>

                    <div>
                        <h4 class="form-inner-title"> {{__('Vehicles Information (Optional)')}} </h4>
                    </div>

                    <div class="row" id="vehicles-container">
                        @foreach ($tenant->user_details['vehicles_info'] ?? [] as $index => $vehicle)
                            <div class="col-lg-4 mt-4">
                                <label> {{__('Vehicles Type')}} </label>
                                <select class="form-control" name="vehicles_info[{{ $index }}][type]">
                                    <option value=""> {{__('Car/Motorcycles etc')}} </option>
                                    <option value="Car" {{ $vehicle['type'] == 'Car' ? 'selected' : '' }}>{{ __('Car') }}</option>
                                    <option value="Motorcycles" {{ $vehicle['type'] == 'Motorcycles' ? 'selected' : '' }}>{{ __('Motorcycles') }}</option>
                                    <option value="Lorry" {{ $vehicle['type'] == 'Lorry' ? 'selected' : '' }}>{{ __('Lorry') }}</option>
                                </select>
                            </div>

                            <div class="col-lg-4 mt-4">
                                <label> {{__('Registration No')}} </label>
                                <input type="number" class="form-control" name="vehicles_info[{{ $index }}][reg_no]" value="{{ $vehicle['reg_no'] }}" placeholder="{{ __('Enter Plate number') }}">
                            </div>

                            <div class="col-lg-4 mt-4">
                                <label> {{__('Vehicles Brand')}} </label>
                                <input type="text" class="form-control" name="vehicles_info[{{ $index }}][brand]" value="{{ $vehicle['brand'] }}" placeholder="{{ __('Enter Vehicles Brand') }}">
                            </div>
                        @endforeach
                    </div>

                    <a href="#" class="d-flex align-items-center gap-1 add-Vehicles-container">
                        <img class="add-Vehicles" src="{{ asset('backend/assets/img/icon/add-new.svg') }}" alt="">
                        <h6>{{ __('Add New Vehicles') }}</h6>
                    </a>

                    <div class="d-flex align-items-center flex-wrap gap-5 mt-4">
                        <div>
                            <h6 class="upload-title">{{ __('Profile Image (optional)') }}</h6>
                            <div class="upload-field-profile mt-4">
                                <label>
                                    <input class="hidden-input image-input" type="file" accept="image/*" name="image"
                                        data-preview-id="profile-upload">
                                    <div class="upload-box">
                                        <img id="profile-upload" src="{{ asset($tenant->image) }}" alt="" class="preview-image {{ $tenant->image ? '' : 'hidden' }} ">
                                        <span class="upload-placeholder {{ $tenant->image ? 'hidden' : '' }} "><i class="fas fa-plus"></i></span>
                                    </div>
                                </label>
                                <div class="remove-btn d-none">
                                    <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h6 class="upload-title">{{ __('Upload NID/Passport ID') }}</h6>
                            <p class="upload-pera pb-2 pt-1">{{ __('Only file type image will be accepted. File limit up to 2.5 MB') }}</p>
                            <div class="image-upload-container">
                                <div class="upload-field">
                                    <label>
                                        <input type="file" accept="image/*" name="mykad[front_image]"
                                            class="image-input" data-preview-id="preview1" style="display: none;">
                                        <div class="upload-box">
                                            <img id="preview1" src="{{ !empty($tenant->user_details->mykad['front_image']) ? asset($tenant->user_details->mykad['front_image']) : '' }}" alt="" class="preview-image {{ !empty($tenant->user_details->mykad['front_image']) ? '' : 'hidden' }} ">
                                            <span class="upload-placeholder  {{ !empty($tenant->user_details->mykad['front_image']) ? 'hidden' : '' }}"><i class="fas fa-plus"></i></span>
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
                                            <img id="preview2" src="{{ !empty($tenant->user_details->mykad['back_image']) ? asset($tenant->user_details->mykad['back_image']) : '' }}" alt="" class="preview-image {{ !empty($tenant->user_details->mykad['back_image']) ? '' : 'hidden' }} ">
                                            <span class="upload-placeholder {{ !empty($tenant->user_details->mykad['back_image']) ? 'hidden' : '' }}"><i class="fas fa-plus"></i></span>
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
                    <button type="reset" class="btn  border-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn theme-btn submit-btn">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
