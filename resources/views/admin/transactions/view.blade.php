<div class="modal fade" id="admin-transaction-view" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header d-none d-lg-flex">
                <h5 class="modal-title">{{ __('View Transaction') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="package-list">
                    <li><span>{{ __('Date') }}</span><span>:</span><span id="admin_transaction_date"></span></li>
                    <li><span>{{ __('Name') }}</span><span>:</span><span id="admin_transaction_name"></span></li>
                    <li><span>{{ __('Payment Type') }}</span><span>:</span><span id="admin_transaction_type"></span></li>
                    <li><span>{{ __('Payment Method') }}</span><span>:</span><span id="admin_transaction_payment_method"></span></li>
                    <li><span>{{ __('Amount') }}</span><span>:</span><span id="admin_transaction_amount"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
