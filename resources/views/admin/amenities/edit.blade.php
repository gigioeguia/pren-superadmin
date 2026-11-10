<div class="modal fade" id="edit-amenity-modal">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Amenity') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="ajaxform_instant_reload amenityUpdateForm">
                    @csrf
                    @method('put')
                    <div class="row">
                    <div class="col-lg-12">
                        <label>{{ __('Name') }}</label>
                        <input type="text" name="name" id="amenity_name" class="form-control" placeholder="{{ __('Enter Name') }}">
                    </div>
                    <div class="col-lg-12">
                    <label>{{ __('Select Landlord') }}</label>
                    <select class="form-control" name="landlord_id" id="landlord_id" required>
                        <option value="">{{ __('Select a landlord') }}</option>
                        @foreach ($landlords as $landlord)
                            <option value="{{ $landlord->id }}">{{ $landlord->name }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="add-landlord-button-group">
                        <button type="button" class="btn  border-btn"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
