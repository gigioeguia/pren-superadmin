<div class="modal fade" id="approve-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">{{ __('Are you sure?') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="personal-info">
                    <form action="" method="post" enctype="multipart/form-data"
                        class="add-brand-form pt-0 ajaxform_instant_reload modalApproveForm">
                        @csrf
                        <div class="row">
                            <div class="mt-3">
                                <label class="custom-top-label">{{ __('Enter Reason') }}</label>
                               <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('Enter reason') }}"></textarea>
                            </div>
                        </div>

                        <div class="">
                            <h6 class="upload-img-title"> {{__('Payment Receipt')}} </h6>
                            <div class="download-pdf-container pr-3 mt-3">
                                <div class="d-flex align-items-center gap-2 ">
                                    <img class="pdf-img" src="{{ asset('backend/assets/img/icon/download-pdf.svg') }}"
                                        alt="">
                                    <div class="">
                                        <h6 class="rent-agree"> {{__('Download Receipt')}} </h6>
                                        <a href="" target="_blank" download>
                                            {{__('Pdf')}}
                                        </a>
                                    </div>
                                </div>
                                <a id="subs_payment_receipt" href="" class="download-icon-container">
                                    <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                                </a>
                            </div>
                        </div>
                        <p class="mt-4 modal-note"> {{__('Note: Please confirm the payment by downloading the receipt')}} </p>

                        <div class="add-landlord-button-group">
                            <button type="button" class="btn border-btn"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
