<div class="modal fade" id="maintenance-confirm-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6> {{__('Maintenance Work')}} </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="custom-modal">

                    <form action="{{ route('landlord.maintenances.processing', $maintenance->id) }}" method="post" class="ajaxform_instant_reload">
                        @csrf
                        <div class="row">

                            <div class="col-lg-12 col-md-6">
                                <label>{{ __('Select Labor') }}</label>
                                <select class="form-control" name="labor_id">
                                    @foreach($labors as $labor)
                                        <option value="{{ $labor->id }}">{{ $labor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-12">
                                <label>{{ __('Maintenance Cost') }}</label>
                                <input type="number" name="total_costing" class="form-control" placeholder="{{ __('Enter Maintenance cost') }}">
                            </div>

                            <div class="col-lg-12">
                                <label>{{ __('Comment') }}</label>
                                <textarea class="form-control" name="comments" id="exampleTextarea" rows="4" placeholder="{{ __('Enter comment') }}"></textarea>
                            </div>

                        </div>
                        <div class="add-landlord-button-group">
                            <button type="button" class="btn  border-btn" data-bs-dismiss="modal"> {{__('Cancel')}} </button>
                            <button type="submit" class="btn theme-btn submit-btn"> {{__('Confirm')}} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
