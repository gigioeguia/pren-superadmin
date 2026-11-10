<div class="modal fade" id="withdraw-method-view-modal" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header d-none d-lg-flex">
                <h5 class="modal-title">{{ __('View Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="package-list">
                    <li><span>{{ __('Payment Method') }}</span><span>:</span><span id="payment_method"></span></li>
                    <li><span>{{ __('Currency') }}</span><span>:</span><span id="method_currency"></span></li>
                    <li><span>{{ __('Minimum Amount') }}</span><span>:</span><span id="method_min_amount"></span></li>
                    <li><span>{{ __('Maximum Amount') }}</span><span>:</span><span id="method_max_amount"></span></li>
                    <li><span>{{ __('Charge') }}</span><span>:</span><span id="method_charge"></span></li>
                    <li><span>{{ __('Status') }}</span><span>:</span><span id="method_status"> {{__('Unpaid')}} </span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
