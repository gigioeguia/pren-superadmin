<div class="modal fade" id="payoutRequestModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="text-black"> {{ __('Payout Request') }} </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="custom-modal">
                    <form action="" method="post" class="payout_request_form payoutRequestForm">
                        <div class="row">
                            <div class="col-lg-12">
                                <label> {{ __('Withdraw Amount') }} </label>
                                <input type="number" class="form-control reset_val" name="amount" id="withdraw_amount"
                                    placeholder="{{ __('Enter withdraw amount') }}">
                            </div>
                            <input type="hidden" name="user_method_id" id="user_method_id">
                            <div class="col-lg-12">
                                <div class="available-balance-container ">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <p> {{ __('Available Balance') }} </p>
                                        <p class="text-black">{{ currency_format($user->balance ?? 0) }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-2 mb-2">
                                        <p class=""> {{ __('Fee') }} </p>
                                        <p class="text-black" id="charge"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mt-4">
                                <label for=""> {{ __('Select Payment Method') }} </label>
                                <div class="payment-method-container ">
                                    <div class="">
                                        <h6 id="bank_name"></h6>
                                        <p id="bank_acc_no"></p>
                                    </div>
                                    <div class="">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="10" cy="10" r="10" fill="#00A551" />
                                            <path d="M14.6673 6.5L8.25065 12.9167L5.33398 10" stroke="white"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="d-flex align-items-center gap-3 modal-err-msg">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_883_26677)">
                                            <path
                                                d="M7.9987 14.6673C11.6806 14.6673 14.6654 11.6825 14.6654 8.00065C14.6654 4.31875 11.6806 1.33398 7.9987 1.33398C4.3168 1.33398 1.33203 4.31875 1.33203 8.00065C1.33203 11.6825 4.3168 14.6673 7.9987 14.6673Z"
                                                stroke="#F23B3D" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 5.33398V8.00065" stroke="#F23B3D" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M8 10.666H8.00667" stroke="#F23B3D" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_883_26677">
                                                <rect width="16" height="16" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <p class="m-0"> {{ __('The amount you entered is less than the minimum') }} <span
                                            id="method_min_amount"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mb-3 modal-btn-container">
                            <button type="submit" class="modal-btn payout-submit-btn"> {{ __('Save') }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden input for Thank You Modal -->
<input type="hidden" id="payment_success_title_hidden" value="{{ get_option('general')['withdraw_payment_success_title'] ?? '' }}">
<input type="hidden" id="payment_success_message_hidden" value="{{ get_option('general')['withdraw_payment_success_message'] ?? '' }}">
