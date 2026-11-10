<div class="modal fade" id="application-processing-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="custom-modal">
                    <img src="{{ asset('backend/assets/img/icon/warning.svg') }}" alt="">
                    <div class="d-flex align-items-center justify-content-center mt-3">
                        <h4> {{__('Are you sure Processing this request for rent property')}} </h4>
                    </div>
                </div>
                <div class="add-landlord-button-group">
                    <button type="button" class="btn  border-btn" data-bs-dismiss="modal"> {{__('No')}} </button>
                    <button type="submit" id="application_processing" class="btn theme-btn submit-btn"> {{__('Yes')}} </button>
                </div>
            </div>
        </div>
    </div>
</div>
