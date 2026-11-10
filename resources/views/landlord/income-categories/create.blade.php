<div class="modal fade" id="add-income-categories-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('landlord.income-categories.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <label>{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('Enter Name') }}">
                        </div>

                        <div class="col-lg-12">
                            <label class="custom-top-label">{{ __('Description') }}</label>
                            <textarea rows="2" name="description" class="form-control" placeholder="{{ __('Enter Description') }}"></textarea>
                        </div>
                    </div>

                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
