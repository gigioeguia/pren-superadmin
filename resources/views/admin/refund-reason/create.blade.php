<div class="modal fade" id="add-reason-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New Reason')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.refund-reason.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <label>@lang('Reason')</label>
                            <input type="text" name="reason" class="form-control" placeholder="{{ __('Enter reason') }}">
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
