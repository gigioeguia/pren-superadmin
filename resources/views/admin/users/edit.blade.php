@extends('layouts.admin.master')

@section('title')
    {{ __('Staff Edit') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Edit Staff') }}</h3>
            </div>
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="ajaxform_instant_reload">
                @csrf
                @method('put')
                <div class="row">
                    <div class="col-lg-4">
                        <label>{{ __('Full Name') }}</label>
                        <input type="text" class="form-control reset_val" name="name" value="{{ $user->name }}" placeholder="{{ __('Enter full name') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ __('Email Address') }}</label>
                        <input type="text" class="form-control reset_val" name="email" value="{{ $user->email }}" required
                            placeholder="{{ __('Enter email') }}">
                    </div>
                    <div class="col-lg-4">
                        <label class="custome-label">{{ __('Phone') }}</label>
                        <div class="phone-input-container">
                            <div class="country-dropdown">
                                <img id="country-flag" src="{{ asset('flags/us.svg') }}" alt="Country Flag" class="flag">
                                <select id="for-country-flag" name="phone[mobile_code]" class="">
                                    @foreach ($phone_codes as $phone_code)
                                        <option value="{{ $phone_code['dial_code'] }}"
                                            data-flag="{{ asset('flags/' . strtolower($phone_code['code']) . '.png') }}"
                                            {{ ($user->phone['mobile_code'] ?? '') == $phone_code['dial_code'] ? 'selected' : '' }}>
                                            {{ $phone_code['dial_code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="number" class="phone-number" name="phone[mobile_no]" value="{{ $user->phone['mobile_no'] ?? '' }}"
                                placeholder="{{ __('Enter Phone Number') }}">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label for="city">{{ __('Password') }}</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>
                    <div class="col-lg-4">
                        <label for="city">{{ __('Password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="******">
                    </div>

                    <div class="col-lg-4 ">
                        <label>{{ __('Role') }}*</label>
                        <select class="form-control country reset_val" name="role" required>
                            <option value="">{{ __('Select One') }}</option>
                            @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($user->role == $role->name)> {{ ucfirst($role->name) }} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center flex-wrap gap-5 mt-4">
                        <div>
                            <h6 class="upload-title"> {{__('Profile Image (optional)')}} </h6>
                           <div class="upload-field-profile mt-4">
                                <label>
                                    <input class="hidden-input image-input" type="file" accept="image/*" name="image"
                                        data-preview-id="profile-upload">
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
                </div>
                <div class="add-landlord-button-group">
                    <button type="button" class="btn border-btn">@lang('Cancel')</button>
                    <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
