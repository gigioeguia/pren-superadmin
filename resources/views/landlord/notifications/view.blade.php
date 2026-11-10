<div class="modal fade" id="notification-view-modal" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header d-none d-lg-flex">
                <h5 class="modal-title">{{ __('Notification Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="package-list">
                    <li><span>{{ __('Message') }}</span><span>:</span><span id="notification_message"></span></li>
                    <li><span>{{ __('Created At') }}</span><span>:</span><span id="notification_created_at"></span></li>
                    <li><span>{{ __('Read At') }}</span><span>:</span><span id="notification_read_at"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
