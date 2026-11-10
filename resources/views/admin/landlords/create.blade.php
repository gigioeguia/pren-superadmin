@extends('layouts.admin.master')

@section('title')
    {{ __('Landlord Create') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">
                    <div class="woodland-card-header border-bottom pb-3">
                        <h3>{{ __('Add New landlord') }}</h3>
                        @can('landlords-read')
                        <a href="{{ route('admin.landlords.index') }}" class="modal-btn">{{ __('Landlord List') }}</a>
                        @endcan
                    </div>

                    <form action="{{ route('admin.landlords.store') }}" method="POST" enctype="multipart/form-data"
                        class="ajaxform_instant_reload">
                        @csrf
                        <div class="woodland-form-wrapper">
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <label for="first-name">{{ __('Full Name') }}</label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ __('Enter Name') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="last-name">{{ __('Email') }} </label>
                                    <input type="text" name="email" class="form-control"
                                        placeholder="{{ __('Enter Email') }}">
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
                                <div class="col-lg-4 col-md-6">
                                    <label for="address">{{ __('Address 1') }}</label>
                                    <input type="text" name="address_info[address_one]" class="form-control"
                                        placeholder="{{ __('81100 Johor Bahru, Johor, malaysia') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="address">{{ __('Address 2') }}</label>
                                    <input type="text" name="address_info[address_two]" class="form-control"
                                        placeholder="{{ __('81100 Johor Bahru, Johor, malaysia') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="post-code">{{ __('Post Code') }}</label>
                                    <input type="number" name="address_info[postal_code]" class="form-control"
                                        placeholder="{{ __('43000') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="city">{{ __('City') }}</label>
                                    <input type="text" name="address_info[city]" class="form-control"
                                        placeholder="{{ __('City') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="state">{{ __('State') }}</label>
                                    <input type="text" name="address_info[state]" class="form-control"
                                        placeholder="{{ __('Enter state here') }}">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="country">{{ __('Country Name') }}</label>
                                    <select class="form-control" name="address_info[country]">
                                        <option selected>{{ __('Select') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="city">{{ __('Gender') }}</label>
                                    <select class="form-control" name="gender">
                                        <option value="Male">{{ __('Male') }}</option>
                                        <option value="Female">{{ __('Female') }}</option>
                                        <option value="Others">{{ __('Others') }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label for="city">{{ __('Password') }}</label>
                                    <input type="password" name="password" class="form-control" placeholder="******">
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-5 mt-4">
                                    <div>
                                        <h6 class="upload-title">{{ __('Profile Image (optional)') }}</h6>
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

                                    <div>
                                        <h6 class="upload-title">{{ __('Upload NID / Passport ') }}</h6>
                                        <p class="upload-pera pb-2 pt-1">
                                            {{ __('Only file type image will be accepted. File limit up to 2.5 MB') }}</p>
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

                                <div class="col-lg-12">
                                    <div class="add-landlord-button-group">
                                        <button type="reset" class="btn  border-btn">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
