<div class="modal fade" id="add-reminder-message-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add Messages')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.reminder-messages.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <label>{{ __('Title') }}</label>
                            <input type="text" name="title" class="form-control" required placeholder="{{ __('Enter title') }}">
                        </div>

                        <div class="col-lg-12 mt-2">
                            <label>{{ __('Payment Method') }}</label>
                            <textarea name="payment_method" class="form-control"></textarea>
                        </div>

                    </div>
                    <div class="add-landlord-button-group">
                        <button type="button" class="btn  border-btn"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

