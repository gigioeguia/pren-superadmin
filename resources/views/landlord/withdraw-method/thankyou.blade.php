<div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="custom-modal">
                        <img src="{{ asset('backend/assets/img/icon/thankyou.svg') }}" alt="">
                        <h3 id="payment_success_title"></h3>
                        <div class="d-flex align-items-center justify-content-center">
                            <p class="mt-2 thanks-modal-text pb-5" id="payment_success_message"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
