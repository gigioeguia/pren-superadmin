<div class="modal fade" id="edit-labor-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Labor') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="editLaborForm ajaxform_instant_reload">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-lg-12">
                            <label>{{ __('Full name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="{{ __('Enter Name') }}">
                        </div>
                        <div class="col-lg-12">
                            <label>{{ __('Email Address') }}</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="{{ __('Enter Email') }}">
                        </div>
                        <div class="col-lg-12">
                            <label>{{ __('Phone Number') }}</label>
                            <div class="form-input-group">
                                <select class="form-control" name="phone[mobile_code]" id="tenant_phone_codes">
                                    @foreach ($phone_codes as $phone_code)
                                    <option value="{{ $phone_code['dial_code']}}">{{ $phone_code['dial_code']}}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="phone[mobile_no]" id="phone" class="form-control" required placeholder="{{ __('Enter Phone number') }}">
                            </div>
                        </div>
                        <div class="col-lg-12 mt-3">
                            <label>{{ __('Gender') }}</label>
                            <select class="form-control" name="gender" id="gender">
                                <option value="">{{ __('Select') }}</option>
                                <option value="Male">{{ __('Male') }}</option>
                                <option value="Female">{{ __('Female') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label>{{ __('Salary') }} </label>
                            <input type="number" name="salary" id="salary" class="form-control" required placeholder="{{ __('Enter salary') }}">
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
