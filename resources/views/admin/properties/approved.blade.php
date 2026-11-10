<div class="modal fade" id="property-approval-modal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">{{ __('Confirm Approval') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="custom-modal">
                    <img src="{{ asset('backend/assets/img/icon/payments.svg') }}" alt="">
                    <div class="d-flex align-items-center justify-content-center mt-3">
                        <h4>{{ __('Are you sure to approve this property?') }}</h4>
                    </div>
                </div>
            </div>

            <div class="add-landlord-button-group mb-3">
                <button type="button" class="btn  border-btn" data-bs-dismiss="modal">{{ __('No') }}</button>
                <form method="POST" action="{{ route('admin.properties.approve', $property->id) }}" class="ajaxform_instant_reload">
                    @csrf
                    <button type="submit" class="btn theme-btn submit-btn">{{ __('Yes') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
