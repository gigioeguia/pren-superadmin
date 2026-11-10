<div class="modal fade" id="add-expense-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.expenses.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" name="amount" class="form-control" placeholder="{{ __('Enter amount') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Category') }}</label>
                            <select class="form-control" name="expense_category_id" required>
                                <option selected>{{ __('Select') }}</option>
                                @foreach ($expense_categories as $expense_category)
                                    <option value="{{ $expense_category->id }}">{{ $expense_category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Expense For') }}</label>
                            <input type="text" name="expense_for" class="form-control" placeholder="{{ __('Enter expesne for') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Payment Type') }}</label>
                            <select class="form-control" name="payment_type">
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
                            <input type="text" name="reference_no" class="form-control" placeholder="{{ __('Enter Reference Number') }}">
                        </div>

                        <div class="col-lg-6">
                            <label>{{ __('Expense Date') }}</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-lg-12">
                            <label class="custom-top-label">{{ __('Note') }}</label>
                            <textarea rows="2" name="note" class="form-control" placeholder="{{ __('Enter text') }}"></textarea>
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
