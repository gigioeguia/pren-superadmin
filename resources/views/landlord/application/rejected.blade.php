<div class="modal fade" id="application-rejected-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="text-black"> {{__('Rejected')}} </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="custom-modal">
                    <h5> {{__('Why are you Rejected Application?')}} </h5>
                    <form action="" method="post" class="applicationRejectedForm ajaxform_instant_reload">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <label> {{__('Enter Reason')}} </label>
                                <textarea class="form-control" name="rejected_cause" id="exampleTextarea" rows="4" placeholder="{{ __('Enter reason') }}"></textarea>
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
