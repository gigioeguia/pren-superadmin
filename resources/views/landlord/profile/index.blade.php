@extends('layouts.landlord.master')

@section('title')
    {{ __('Profile') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-xl-5 col-md-6">
                <div class="woodland-card profile-card">
                    <div class="woodland-card-header">
                        <h3>{{ __('My Profile') }}</h3>
                    </div>
                    <div class="profile-img-container">
                        <img src="{{ asset($user->image ?? 'backend/assets/img/icon/avatar.svg') }}" alt="" srcset="">
                        <h5 class="mt-2">{{ $user->name }}</h5>
                        <p>{{ $user->plan_subscribe?->plan?->subscriptionName }}</p>
                    </div>

                    <table class="table profile-info-table">
                        <tbody>
                            <tr>
                                <td colspan="2" class="border-0">
                                    <div class="profile-info-title mt-4 mb-2">
                                        <h3>{{ __('Personal Information') }}</h3>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Full Name') }}</td>
                                <td class="border-0">: {{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Email') }}</td>
                                <td class="border-0">: {{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Phone Number') }}</td>
                                <td class="border-0">: {{ $user->phone['mobile_no'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Address') }}</td>
                                <td class="border-0">: {{ $user->user_details->address_info['address_one'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Country Name') }}</td>
                                <td class="border-0">: {{ $user->user_details->address_info['country'] ?? '' }} </td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('State') }}</td>
                                <td class="border-0">:  {{ $user->user_details->address_info['state'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('City') }}</td>
                                <td class="border-0">: {{ $user->user_details->address_info['city'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Postal Code') }}</td>
                                <td class="border-0">: {{ $user->user_details->address_info['postal_code'] ?? '' }} </td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('Gender') }}</td>
                                <td class="border-0">: {{ $user->user_details->gender ?? '' }} </td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('NID / Passport Id') }}</td>
                                <td class="border-0">: {{ $user->user_details->mykad_id ?? '' }} </td>
                            </tr>
                            <tr>
                                <td class="border-0 details-name">{{ __('NID / Passport Image') }}</td>
                                <td class="border-0">
                                <div class="d-flex align-items-center gap-2">
                                    <img class="profile-mykad-img img-clickable"
                                        src="{{ asset($user->user_details->mykad['front_image'] ?? 'backend/assets/img/no_image.jpg') }}"
                                        alt="Front Image" data-bs-toggle="modal" data-bs-target="#imageModal">

                                    <img class="profile-mykad-img img-clickable"
                                        src="{{ asset($user->user_details->mykad['back_image'] ?? 'backend/assets/img/no_image.jpg') }}"
                                        alt="Back Image" data-bs-toggle="modal" data-bs-target="#imageModal">
                                </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-7 col-md-6">
                <div class="woodland-card profile-card">
                    <div class="woodland-card-header mb-3">
                        <h3>{{ __('Update Profile') }}</h3>
                    </div>

                    <form action="{{ route('landlord.profile.update', $user->id) }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        @method('put')
                    <div class="row g-3 mt-3">
                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control reset_val" name="name" value="{{ $user->name }}" placeholder="{{ __('Enter full name') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Email') }}</label>
                            <input type="text" class="form-control reset_val" name="email" value="{{ $user->email }}" required="" placeholder="{{ __('Enter email') }}" data-temp-mail-org="0">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Phone') }}</label>
                            <div class="phone-input-container">
                                <div class="country-dropdown">
                                    <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                    <select id="for-country-flag" name="phone[mobile_code]" class="select2">
                                        @foreach($phone_codes as $phone_code)
                                            <option value="{{ $phone_code['dial_code'] }}"
                                                data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                                {{ ($user->phone['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                                {{ $phone_code['dial_code'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="number" class="phone-number" name="phone[mobile_no]" value="{{ $user->phone['mobile_no'] ?? '' }}" placeholder="{{ __('Enter Phone Number') }}">
                            </div>
                        </div>


                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Address1') }}</label>
                            <input type="text" class="form-control" name="address_info[address_one]" value="{{ $user->user_details->address_info['address_one'] ?? '' }}" placeholder="{{ __('Enter Address1') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Address2 (Optional)') }}</label>
                            <input type="text" class="form-control" name="address_info[address_two]" value="{{ $user->user_details->address_info['address_two'] ?? '' }}" placeholder="{{ __('Enter Address2') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Country Name') }}</label>
                            <select class="form-control" name="address_info[country]">
                                @foreach ($countries as $country)
                                <option value="{{ $country['name'] }}" @selected($user->user_details->address_info['country'] ?? '' == $country['name'])>{{ $country['name'] }}</option>
                            @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('State') }}</label>
                            <input type="text" class="form-control" name="address_info[state]" value="{{ $user->user_details->address_info['state'] ?? ''}}" placeholder="{{ __('Enter state') }}" value="">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('City') }}</label>
                            <input type="text" class="form-control" name="address_info[city]" value="{{ $user->user_details->address_info['city'] ?? ''}}" placeholder="{{ __('Enter city') }}" value="">
                        </div>


                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Postal Code') }}</label>
                            <input type="number" class="form-control" name="address_info[postal_code]" value="{{ $user->user_details->address_info['postal_code'] ?? ''}}" placeholder="{{ __('Enter Postal code') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Gender') }}</label>
                            <select class="form-control country reset_val" name="gender">
                                <option value="">{{ __('Select Gender') }}</option>
                                <option @selected(optional($user->user_details)->gender == 'Male') value="Male">{{ __('Male') }}</option>
                                <option @selected(optional($user->user_details)->gender == 'Female') value="Female">{{ __('Female') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-12 custom-input">
                            <label class="custome-label">{{ __('NID / Passport Id') }}</label>
                            <input type="text" class="form-control" name="mykad_id" placeholder="{{ __('Enter NID / Passport Id') }}" value="{{ $user->user_details?->mykad_id ?? '' }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Current Password') }}</label>
                            <input type="password" name="current_password" class="form-control" placeholder="{{ __('Enter Your Current Password') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('New Password') }}</label>
                            <input type="password" name="password" class="form-control" placeholder="{{ __('Enter New Password') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Enter Confirm Password') }}">
                        </div>

                        <div class="d-flex align-items-center flex-wrap gap-5 mt-3">
                            <div>
                                <h6 class="upload-title"> {{__('Profile Image')}} </h6>
                            <div class="upload-field-profile mt-4">
                                <label>
                                    <input class="hidden-input image-input" type="file" accept="image/*" name="image"
                                        data-preview-id="profile-upload">
                                    <div class="upload-box">
                                        <img id="profile-upload" src="{{ asset($user->image ?? 'backend/assets/img/icon/avatar.svg') }}" alt="" class="preview-image">
                                        <span class="upload-placeholder"><i class="fas fa-plus"></i></span>
                                    </div>
                                </label>
                                <div class="remove-btn d-none">
                                    <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                </div>
                            </div>
                            </div>

                            <div>
                                <h6 class="upload-title">{{ __('Upload NID / Passport Id') }}</h6>
                                <p class="upload-pera pb-2 pt-1">{{ __('Only file type image will be accepted. File limit up to 2.5 MB') }}</p>
                                 <div class="image-upload-container">
                                <div class="upload-field">
                                    <label>
                                        <input type="file" accept="image/*" name="mykad[front_image]"
                                            class="image-input" data-preview-id="preview1" style="display: none;">
                                        <div class="upload-box">
                                            <img id="preview1" src="{{ asset($user->user_details->mykad['front_image'] ?? 'backend/assets/img/no_image.jpg') }}" alt="" class="preview-image">
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
                                                <img id="preview2" src="{{ asset($user->user_details->mykad['back_image'] ?? 'backend/assets/img/no_image.jpg') }}" alt="" class="preview-image">
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
                        <div class="add-landlord-button-group ">
                            <button type="button" class="btn border-btn" data-bs-dismiss="modal">{{ __('Reset') }}</button>
                            <button type="submit" class="btn theme-btn submit-btn">{{ __('Update') }}</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade " id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none position-relative">

                <!-- Close Button -->

                <!-- Modal Body -->
                <div class="modal-body p-0">
                    <button type="button" class="btn-close position-absolute profile-img-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalImage" src="" alt="Preview" class="w-100 img-fluid modal-view-img rounded">
                </div>
            </div>
        </div>
    </div>

@endsection


@push('js')
    <script src="{{ asset('maan/js/profile.js') }}"></script>
@endpush
