<div class="modal fade" id="add-rent-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Invite Rent') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('landlord.rents.store') }}" method="POST" class="ajaxform_instant_reload">
                    @csrf
                    <div class="row">

                        <div class="col-lg-12 mt-3">
                            <label class="form-label">{{ __('Tenant') }}</label>
                            <select class="form-control" name="tenant_id" required>
                                <option value="">{{ __('Select Tenant') }}</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label class="form-label">{{ __('Property') }}</label>
                            <select class="form-control" id="propertySelect" name="property_id" required>
                                <option value="">{{ __('Select Property') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->propertyDetail->property_info['property_title'] ?? 'Unnamed' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="unitContainerWrapper"></div>

                        <h6>{{ __('Rental period') }}</h6>

                        <div class="col-lg-6 mt-3">
                            <label>{{ __('Form Date') }}</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-lg-6 mt-3">
                            <label>{{ __('To Date') }}</label>
                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label>{{ __('Rent Agreement *') }}</label>
                            <input type="file" name="landlord_agreement" class="form-control" required>
                        </div>
                    </div>
                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<input type="hidden" value="{{ route('landlord.get.units') }}" id="getUnitData">
