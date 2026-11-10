<div class="modal fade" id="edit-reason-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Reason')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="ajaxform_instant_reload refundReasonForm">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-lg-12">
                            <label>@lang('Reason')</label>
                            <input type="text" name="reason" id="reason" class="form-control" placeholder="{{ __('Enter reason') }}">
                        </div>
                        <div class="add-landlord-button-group">
                            <button type="button" class="btn  border-btn" data-bs-dismiss="modal">@lang('Cancel')</button>
                            <button type="submit" class="btn theme-btn submit-btn">@lang('Update')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
