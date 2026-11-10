<div class="modal fade" id="add-bank-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New Method') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('landlord.withdraw-method.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <label>@lang('Withdraw Method')</label>
                            <select class="form-control methodDropdown" id="" name="method_id">
                                <option value="">@lang('Select')</option>
                                @foreach ($methods as $method)
                                <option value="{{ $method->id }}" data-labels="{{ json_encode($method->meta['label'] ?? '') }}"
                                    data-inputs="{{ json_encode($method->meta['input'] ?? '') }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="col-lg-12">
                        <label> {{__('Account No')}} </label>
                        <input type="number" class="form-control reset_val" name="account_no" id="account_no"
                            placeholder="{{ __('Enter account no') }}">
                    </div>
                    <div id="" class="mt-3 dynamicFields"></div>

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
