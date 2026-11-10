<div class="modal fade" id="registration-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Create') }} <span id="subscription_name"></span> {{ __('Account') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form id="registration-form" action="{{ route('signup.store') }}" method="post"
                        enctype="multipart/form-data" class="add-brand-form pt-0 sign_up_form">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="custom-top-label">{{ __('Name') }}</label>
                                <input type="text" name="name"
                                    placeholder="{{ __('Enter Your Name') }}" class="form-control" required />
                            </div>
                            <div class="mt-2 col-lg-12">
                                <label class="custom-top-label">{{ __('Email') }}</label>
                                <input type="email" name="email" placeholder="{{ __('Enter Your Email') }}"
                                    class="form-control" required />
                            </div>
                            <div class="mt-2 col-lg-12">
                                <label class="custom-top-label">{{ __('Password') }}</label>
                                <input type="password" name="password" placeholder="{{ __('Enter Password') }}"
                                    class="form-control" required />
                            </div>
                        </div>

                        <input type="hidden" value="" id="plan_id" name="plan_id">

                        <div class="offcanvas-footer mt-3 d-flex justify-content-center gap-2">
                            <button type="button" data-bs-dismiss="modal" class="cancel-btn "
                                data-bs-dismiss="offcanvas" aria-label="Close">
                                {{ __('Close') }}
                            </button>
                            <button type="submit" class="submit-btn">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Verify Modal Start -->
<div class="modal fade" id="verifymodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content verify-content">
            <div class="modal-header border-bottom-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body verify-modal-body  text-center">

                <h4 class="mb-0 verification-title">{{ __('Email Verification') }}</h4>
                <p class="des p-8-0 pb-3">{{ __('we sent an OTP in your email address') }} <br>
                    <span id="dynamicEmail"></span>
                </p>
                <form action="{{ route('signup.submitOtp') }}" method="post" class="verify_form">
                    @csrf
                    <div class="code-input pin-container">
                        <input class="pin-input otp-input" id="pin-1" type="number" name="otp[]" maxlength="1">
                        <input class="pin-input otp-input" id="pin-2" type="number" name="otp[]" maxlength="1">
                        <input class="pin-input otp-input" id="pin-3" type="number" name="otp[]" maxlength="1">
                        <input class="pin-input otp-input" id="pin-4" type="number" name="otp[]" maxlength="1">
                        <input class="pin-input otp-input" id="pin-5" type="number" name="otp[]" maxlength="1">
                        <input class="pin-input otp-input" id="pin-6" type="number" name="otp[]" maxlength="1">
                    </div>

                    <p class="des p-24-0 pt-2">
                        {{ __('Code send in') }} <span id="countdown" class="countdown"></span>
                        <span class="reset text-primary cursor-pointer" id="otp-resend" data-route="{{ route('signup.otpResend') }}">{{ __('Resend code') }}</span>
                    </p>
                    <button class="verify-btn btn submit-btn ps-custom-btn mt-2">{{ __('Verify') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Verify Modal end -->
