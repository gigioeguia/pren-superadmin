@extends('layouts.admin.master')

@section('title')
    {{ __('App Settings') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>@lang('App Settings') </h3>
                </div>
                <div class="woodland-card">
                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <form action="{{ route('admin.settings.update', $general->id) }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                            @csrf
                            @method('put')
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('App Name') }}</label>
                                        <input type="text" name="app_name"
                                            value="{{ $general->value['app_name'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter Name') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Address') }}</label>
                                        <input type="text" name="app_address"
                                            value="{{ $general->value['app_address'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter address') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Phone') }}</label>
                                        <input type="number" name="app_phone"
                                            value="{{ $general->value['app_phone'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter phone') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Email') }}</label>
                                        <input type="email" name="app_email"
                                            value="{{ $general->value['app_email'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter email') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('App Url') }}</label>
                                        <input type="text" name="app_url" value="{{ $general->value['app_url'] ?? '' }}"
                                            required class="form-control" placeholder="{{ __('Enter Url') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Copy Right') }}</label>
                                        <input type="text" name="copy_right" value="{{ $general->value['copy_right'] ?? '' }}"
                                            required class="form-control" placeholder="{{ __('Enter copy right') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Admin Footer Text') }}</label>
                                        <input type="text" name="footer_text" value="{{ $general->value['footer_text'] ?? '' }}"
                                            required class="form-control" placeholder="{{ __('Enter text') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Admin Footer Link Text') }}</label>
                                        <input type="text" name="footer_link_text"
                                            value="{{ $general->value['footer_link_text'] ?? '' }}" required
                                            class="form-control" placeholder="{{ __('Enter link text') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Admin Footer Link') }}</label>
                                        <input type="text" name="footer_link"
                                            value="{{ $general->value['footer_link'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter Url') }}">
                                    </div>
                                    <div class="col-lg-6 mt-2">
                                        <label>{{ __('Payment Success Title') }}</label>
                                        <input type="text" name="withdraw_payment_success_title"
                                            value="{{ $general->value['withdraw_payment_success_title'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter Title') }}">
                                    </div>
                                    <div class="col-lg-12 mt-2">
                                        <label>{{ __('Payment Success Message') }}</label>
                                        <input type="text" name="withdraw_payment_success_message"
                                            value="{{ $general->value['withdraw_payment_success_message'] ?? '' }}" required class="form-control"
                                            placeholder="{{ __('Enter Message') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 settings-image-upload">
                                        <label class="title">{{ __('APP Logo') }}</label>
                                        <div class="upload-img-v2">
                                            <label class="upload-v4 settings-upload-v4">
                                                <div class="img-wrp">
                                                    <img src="{{ asset($general->value['logo'] ?? 'backend/assets/img/icon/dropimg.svg') }}"
                                                        alt="user" id="logo">
                                                </div>
                                              <input type="file" name="logo" class="d-none image-input" data-preview-id="logo" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 settings-image-upload mt-2">
                                        <label class="title">{{ __('Favicon') }}</label>
                                        <div class="upload-img-v2">
                                            <label class="upload-v4 settings-upload-v4">
                                                <div class="img-wrp">
                                                    <img src="{{ asset($general->value['favicon'] ?? 'backend/assets/img/icon/dropimg.svg') }}"
                                                        alt="user" id="favicon">
                                                </div>
                                                    <input type="file" name="favicon" class="d-none image-input" data-preview-id="favicon" accept="image/*">

                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 settings-image-upload">
                                        <label class="title">{{ __('Login Page Image') }}</label>
                                        <div class="upload-img-v2">
                                            <label class="upload-v4 settings-upload-v4">
                                                <div class="img-wrp">
                                                    <img src="{{ asset($general->value['login_page_img'] ?? 'backend/assets/img/icon/dropimg.svg') }}"
                                                        alt="user" id="login_page_img">
                                                </div>
                                         <input type="file" name="login_page_img" class="d-none image-input" data-preview-id="login_page_img" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                    @can('app-settings-update')
                                    <div class="col-lg-12">
                                        <div class="text-center mt-5">
                                            <button type="submit" class="theme-btn m-2 submit-btn">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
