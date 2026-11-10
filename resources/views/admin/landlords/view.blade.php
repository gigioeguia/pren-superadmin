<div class="modal fade" id="landlord-view">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header d-none d-lg-flex">
                <h5 class="modal-title">{{ __('View Landlord') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="package-list">
                    <li><span>{{ __('Landlord ID') }}</span><span>:</span><span id="landlord_id"></span></li>
                    <li><span>{{ __('Landlord name') }}</span><span>:</span><span id="landlord_name"></span></li>
                    <li><span>{{ __('Number of Property') }}</span><span>:</span><span id="number_of_property"></span></li>
                    <li><span>{{ __('Number of Tenant') }}</span><span>:</span><span id="number_of_tenant"></span></li>
                    <li><span>{{ __('Number of Rent') }}</span><span>:</span><span id="number_of_rent"></span></li>
                    <li><span>{{ __('Subscription Type') }}</span><span>:</span><span id="subscription_type"></span></li>
                    <li><span>{{ __('Duration Date') }}</span><span>:</span><span id="duration_date"></span></li>
                    <li><span>{{ __('Landlord Balance') }}</span><span>:</span><span id="landlord_balance"></span></li>
                    <li><span>{{ __('Status') }}</span><span>:</span><span id="landlord_status"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
