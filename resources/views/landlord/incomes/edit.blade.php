<div class="modal fade" id="edit-income-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit income') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="ajaxform_instant_reload incomeUpdateForm">
                    @csrf
                    @method('put')

                    <div class="row">
                        <div class="col-lg-6">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" name="amount" id="income_amount" class="form-control" placeholder="{{ __('Enter amount') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Category') }}</label>
                            <select class="form-control" name="income_category_id" id="income_category_id" required>
                                <option selected>{{ __('Select') }}</option>
                                @foreach ($income_categories as $income_category)
                                    <option value="{{ $income_category->id }}">{{ $income_category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('income For') }}</label>
                            <input type="text" name="income_for" id="income_for" class="form-control" placeholder="{{ __('Enter income for') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Payment Type') }}</label>
                            <select class="form-control" name="payment_type" id="payment_type">
                                <option value="">{{ __('Select a payment type') }}</option>
                                <option value="cash">{{ __('Cash')}}</option>
                                <option value="bank">{{ __('Bank')}}</option>
                                <option value="card">{{ __('Card')}}</option>
                                <option value="mobile_payment">{{ __('Mobile Payment')}}</option>
                                <option value="due">{{ __('Due')}}</option>
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Reference Number') }}</label>
                            <input type="text" name="reference_no" id="reference_no" class="form-control" placeholder="{{ __('Enter Reference Number') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('income Date') }}</label>
                            <input type="date" name="income_date" id="income_date" class="form-control">
                        </div>

                        <div class="col-lg-12">
                            <label class="custom-top-label">{{ __('Note') }}</label>
                            <textarea rows="2" name="note" id="note" class="form-control" placeholder="{{ __('Enter text') }}"></textarea>
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
