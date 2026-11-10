<div class="modal fade" id="application-approved-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="custom-modal">
                    <img src="{{ asset('backend/assets/img/icon/warning.svg') }}" alt="">
                    <div class="d-flex align-items-center justify-content-center mt-3">
                        <h4> {{__('Tenant has paid the deposit?')}} </h4>
                    </div>
                </div>

                <form action="" method="post" class="applicationApprovedForm ajaxform_instant_reload">
                    @csrf
                    <div class="add-landlord-button-group">
                        <button type="button" class="btn  border-btn" data-bs-dismiss="modal"> {{__('No')}} </button>
                        <button type="submit" class="btn theme-btn submit-btn"> {{__('Yes')}} </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
