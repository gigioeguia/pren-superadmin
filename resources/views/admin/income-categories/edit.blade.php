<div class="modal fade" id="edit-income-categories-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Income Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="ajaxform_instant_reload incomeCategoryUpdateForm">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-lg-12">
                            <label>{{ __('Name') }}</label>
                            <input type="text" name="name" id="income_category_name" class="form-control" placeholder="{{ __('Enter Name') }}">
                        </div>

                        <div class="col-lg-12">
                            <label class="custom-top-label">{{ __('Description') }}</label>
                            <textarea rows="2" name="description" id="income_description" class="form-control" placeholder="{{ __('Enter Description') }}"></textarea>
                        </div>
                    </div>

                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
