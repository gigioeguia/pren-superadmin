@extends('auth.master')

@section('main_content')
    <div class="mybazar-login-section">
        <div class="mybazar-login-wrapper">
            <div class="login-wrapper">
                <div class="login-header">
                    <img src="{{asset('backend/assets/img/logo.svg')}}" alt="logo">
                </div>
                <div class="login-body">
                    <h2 class="mb-2">@lang('Verification')</h2>
                    <p class="mb-3">@lang('Please check your email to take 6 digit code for continue.')</p>
                    <form action="{{route('verification-code.verify')}}" method="POST" class="border-0">
                        @csrf
                        <input type="hidden" name="user_id" value="{{$id}}">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="opt-verification">
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" required/>
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" required/>
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" required/>
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" required/>
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                    <input type="text" name="code[]" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" required/>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="add-landlord-button-group">
                                    <button type="submit" class="btn theme-btn" data-bs-toggle="modal">@lang('Continue')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
