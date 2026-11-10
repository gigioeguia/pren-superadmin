<div class="modal fade" id="rent-payment-approve-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="text-black"> {{__('Approved Rent Payment')}} </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="custom-modal">
                    <form action="" method="post" class="ajaxform_instant_reload rentPaymentForm">
                        <div class="row">
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
                                <a id="payment_receipt" href="" class="download-icon-container">
                                    <img class="download-icon" src="{{ asset('backend/assets/img/icon/download-icon.svg') }}">
                                </a>
                            </div>
                        </div>
                        <p> {{__('Note: Please confirm the payment by downloading the receipt')}} </p>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <button type="submit" class="modal-gradient-btn submit-btn"> {{__('Approved')}} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
