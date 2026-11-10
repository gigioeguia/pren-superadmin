@extends('layouts.auth.master')

@section('title')
    {{ __('Login') }}
@endsection

@section('main_content')
<div class="login-section-container">
    <div class="mybazar-login-section ">
        <div class="mybazar-login-wrapper">
            <div class="login-wrapper">
                <div class="login-header">
                    <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="logo">
                </div>
                <div class="title-container">
                    <h4 class="greeting">{{ __('Welcome Back!') }}</h4>
                    <p class="text mt-1">{{ __('Welcome back, Please login in to your account') }}</p>
                </div>
                <div class="login-body">
                    <form id="loginForm" action="{{ url('/login/store') }}" method="POST" class="ajaxform_instant_reload no-error-labels">
                        @csrf
                        <div class="input-group">
                            <span class="input-icon">
                                <img src="{{ asset('backend/assets/img/icon/mail.svg') }}" alt="">
                            </span>
                            <input type="email" name="email" id="email" placeholder="Email" class="form-control">
                        </div>
                        <div class="input-group">
                            <span class="hide-pass">
                                <img src="{{ asset('backend/assets/img/icon/Hide.svg') }}" alt="">
                                <img src="{{ asset('backend/assets/img/icon/show.svg') }}" alt="">
                            </span>
                            <span class="input-icon">
                                <img src="{{ asset('backend/assets/img/icon/Lock.svg') }}" alt="">
                            </span>
                            <input type="password" name="password" id="password" placeholder="Password" class="form-control">
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div class="form-check d-flex align-items-center justify-content-center gap-2">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" >
                                <label class="form-check-label m-0" for="flexCheckChecked">
                                    {{__('Remember me')}}
                                </label>
                              </div>
                              <a href="{{ route('password.request') }}"> {{__('Forgot Password?')}} </a>
                        </div>
                        <button type="submit" class="btn login-btn submit-btn">@lang('Login')</button>
                    </form>

                    <div class="mt-2 mb-3 mt-2 pt-2 d-flex align-items-center justify-content-between">
                        <a class="back-home" href="/">{{ __('Back to Home') }}</a>
                        <span class="quick-demo-login  "> {{__('Are You New Hare?')}}  <a href="{{ route('plan.index') }}">@lang('Create an account')</a></span>
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

