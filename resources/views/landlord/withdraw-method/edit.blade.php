<div class="modal fade" id="edit-bank-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Method')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" class="ajaxform_instant_reload" id="edit-method-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">

                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <label>@lang('Withdraw Method')</label>
                            <select class="form-control methodDropdown" id="edit_method_id" name="method_id">
                                <option value="">@lang('Select')</option>
                                @foreach ($methods as $method)
                                    <option value="{{ $method->id }}"
                                        data-labels="{{ json_encode($method->meta['label'] ?? []) }}"
                                        data-inputs="{{ json_encode($method->meta['input'] ?? []) }}">
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label>{{ __('Account No') }}</label>
                            <input type="number" class="form-control" name="account_no" id="edit_account_no"
                                placeholder="{{ __('Enter account no') }}">
                        </div>

                        <div id="editDynamicFields" class="mt-3 dynamicFields"></div>
                    </div>

                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn theme-btn submit-btn">@lang('Update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
