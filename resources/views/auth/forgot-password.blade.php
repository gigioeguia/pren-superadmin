@extends('layouts.auth.master')

@section('title')
    {{ __('Forgot Password') }}
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
                    <h4 class="greeting mb-2">@lang('Forgot Password?')</h4>
                    <p class="text mt-1 mb-3">
                        @lang('Please enter your email address. You will receive a link to create a new password via email.')
                    </p>
                </div>
                <div class="login-body">
                    <form action="{{ route('password.email') }}" method="POST" class="ajaxform">
                        @csrf
                        <div class="input-group">
                            <span class="input-icon">
                                <img src="{{ asset('backend/assets/img/icon/mail.svg') }}" alt="">
                            </span>
                            <input type="email" name="email" placeholder="{{__('Enter your email')}}" class="form-control">
                        </div>
                        <button type="submit" class="btn login-btn submit-btn mt-3">@lang('Submit')</button>
                    </form>
                    <div class="back-to-login">
                        <img src="{{ asset('assets/user-img/user.png') }}" alt="img">
                        <a href="{{ route('login') }}">{{ __('Back to Login') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-img">
            <img src="{{ asset(get_option('general')['login_page_img'] ?? 'backend/assets/img/login-img.svg') }}" />
        </div>
    </div>
</div>
@endsection
