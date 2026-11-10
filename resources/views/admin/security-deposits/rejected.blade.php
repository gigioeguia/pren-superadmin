<div class="modal fade" id="security-deposits-reject-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="text-black">{{ __('Reject Security Deposit') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="custom-modal">
                    <h5>{{ __('Why are you reject the request?') }}</h5>
                    <form action="" method="post" class="ajaxform_instant_reload securityDepositRejectForm">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <label>{{ __('Enter Reason') }}</label>
                                <textarea class="form-control" name="note" id="exampleTextarea" rows="4" placeholder="{{ __('Enter reason') }}"></textarea>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <button class="modal-gradient-btn submit-btn">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
