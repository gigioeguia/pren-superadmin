@extends('layouts.auth.master')

@section('title')
    {{ __('Reset Password') }}
@endsection

@section('main_content')
<div class="login-section-container">
    <div class="mybazar-login-section">
        <div class="mybazar-login-wrapper">
            <div class="login-wrapper">
                <div class="login-header">
                    <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="logo">
                </div>
                <div class="title-container">
                    <h4 class="greeting">{{ __('Reset Password') }}</h4>
                    <p class="text mt-1">{{ __('Set your account new password.') }}</p>
                </div>

                <div class="login-body">
                    <form action="{{ route('password.store') }}" method="POST" class="ajaxform_instant_reload border-0">
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                        <div class="input-group">
                            <span class="hide-pass">
                                <img src="{{ asset('backend/assets/img/icon/Hide.svg') }}" alt="">
                                <img src="{{ asset('backend/assets/img/icon/show.svg') }}" alt="">
                            </span>
                            <div class="login-input-container d-flex w-100">
                                <div>
                                    <img src="{{ asset('backend/assets/img/icon/Lock.svg') }}" alt="">
                                </div>
                                <input id="password" type="password" name="password" placeholder="{{ __('New Password') }}" class="form-control">
                            </div>
                        </div>

                        <div class="input-group">
                            <span class="hide-pass">
                                <img src="{{ asset('backend/assets/img/icon/Hide.svg') }}" alt="">
                                <img src="{{ asset('backend/assets/img/icon/show.svg') }}" alt="">
                            </span>
                            <div class="login-input-container d-flex w-100">
                                <div>
                                    <img src="{{ asset('backend/assets/img/icon/Lock.svg') }}" alt="">
                                </div>
                                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('Confirm New Password') }}" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn login-btn submit-btn mt-3">@lang('Change')</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="login-img">
            <img src="{{ asset(get_option('general')['login_page_img'] ?? 'backend/assets/img/login-img.svg') }}" />
        </div>
    </div>
</div>
@endsection
