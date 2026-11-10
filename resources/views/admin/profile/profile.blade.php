@extends('layouts.admin.master')

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
                                <td class="border-0 details-name">{{ __('User Role') }}</td>
                                <td class="border-0">: {{ $user->role }}</td>
                            </tr>

                            <tr>
                                <td class="border-0 details-name">{{ __('Gender') }}</td>
                                <td class="border-0">: {{ $user->user_details->gender ?? '' }} </td>
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
                    <form action="{{ route('admin.profile.update', $user->id) }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        @method('put')
                    <div class="row g-3 mt-3">
                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control reset_val" name="name" value="{{ $user->name }}" placeholder="{{ __('Enter full name') }}">
                        </div>

                        <div class="col-lg-6 custom-input">
                            <label class="custome-label">{{ __('Email') }}</label>
                            <input type="text" class="form-control reset_val" name="email" required="" value="{{ $user->email }}" placeholder="{{ __('Enter Email') }}" data-temp-mail-org="0">
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
                            <label class="custome-label">{{ __('Gender') }}</label>
                            <select class="form-control country reset_val" name="gender">
                                <option value="">{{ __('Select Gender') }}</option>
                                <option @selected(optional($user->user_details)->gender == 'Male') value="Male">{{ __('Male') }}</option>
                                <option @selected(optional($user->user_details)->gender == 'Female') value="Female">{{ __('Female') }}</option>
                            </select>
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

                        <div>
                            <h6 class="upload-title">{{ __('Profile Image') }}</h6>
                            <div class="upload-field-profile mt-4">
                                <label>
                                    <input class="hidden-input image-input" type="file" accept="image/*" name="image"
                                        data-preview-id="profile-upload">
                                    <div class="upload-box">
                            <img id="profile-upload" src="{{ asset($user->image) }}" alt=""
                                class="preview-image {{ $user->image ? '' : 'hidden' }}">
                            <span class="upload-placeholder {{ $user->image ? 'hidden' : '' }}"><i class="fas fa-plus"></i></span>
                                    </div>
                                </label>
                                <div class="remove-btn d-none">
                                    <img class="cross" src="{{ asset('backend/assets/img/icon/cross.svg') }}" alt="">
                                </div>
                            </div>
                        </div>

                        <div class="add-landlord-button-group ">
                            <button type="reset" class="btn  border-btn">{{ __('Reset') }}</button>
                            <button type="submit" class="btn theme-btn submit-btn">{{ __('Update') }}</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
