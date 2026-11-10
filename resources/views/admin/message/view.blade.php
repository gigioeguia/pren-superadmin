<div class="modal fade" id="message-view-modal" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content woodland-modal-content">
            <div class="modal-header d-none d-lg-flex">
                <h5 class="modal-title">{{ __('View Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="package-list">
                    <li><span>{{ __('Name') }}</span><span>:</span><span id="name"></span></li>
                    <li><span>{{ __('Phone') }}</span><span>:</span><span id="phone"></span></li>
                    <li><span>{{ __('Email') }}</span><span>:</span><span id="email"></span></li>
                    <li><span>{{ __('Company Name') }}</span><span>:</span><span id="company_name"></span></li>
                    <li><span>{{ __('Message') }}</span><span>:</span><span id="message"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
