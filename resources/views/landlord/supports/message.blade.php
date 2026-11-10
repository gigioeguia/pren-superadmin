@foreach ($messages as $message)
    @if ($message->sender->role == 'landlord')
        <div class="my-msg">
            <div class="">
                @if ($message->file)
                    @if (in_array($message->file_info['ext'] ?? '', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']))
                        <img src="{{ asset($message->file) }}" alt="Image" class="chat-image" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-whatever="{{ asset($message->file) }}">
                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content img-preview-content">
                                    <div class="modal-body text-center">
                                        <img id="modalImage" src="" class="img-fluid preview-img"
                                            alt="Preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif (in_array($message->file_info['ext'] ?? '', ['pdf']))
                        <a href="{{ asset($message->file) }}" target="_blank" class="">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('View PDF')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @elseif (in_array($message->file_info['ext'] ?? '', ['doc', 'docx']))
                        <a href="{{ asset($message->file) }}" target="_blank" class="">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path
                                            d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path
                                            d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path
                                                d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z"
                                                stroke="#7B787B" stroke-width="1.25" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('Download Document')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @else
                        <a href="{{ asset($message->file) }}" target="_blank" class="">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path
                                            d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path
                                            d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('Download file')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endif
                @endif

                <div class="d-flex align-items-end justify-content-end">
                    @if (!empty($message->message))
                        <p>{{ $message->message }}</p>
                    @endif
                </div>
                <span class=" d-flex align-items-start justify-content-start">
                    {{ \Carbon\Carbon::parse($message->created_at)->format('g:i A') }}
                </span>
            </div>
        </div>
    @else
        <div class="opposite-msg">
            <div class="">
                @if ($message->file)
                    @if (in_array($message->file_info['ext'] ?? '', ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ asset($message->file) }}" alt="Image" class="chat-image">
                    @elseif (in_array($message->file_info['ext'] ?? '', ['pdf']))
                        <a href="{{ asset($message->file) }}" target="_blank">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('View PDF')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @elseif (in_array($message->file_info['ext'] ?? '', ['doc', 'docx']))
                        <a href="{{ asset($message->file) }}" target="_blank">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path
                                            d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path
                                            d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path
                                                d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z"
                                                stroke="#7B787B" stroke-width="1.25" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('Download Document')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @else
                        <a href="{{ asset($message->file) }}" target="_blank">
                            <div class="download-document-container ">
                                <div class="download-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="20" fill="#636364" />
                                        <path
                                            d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path
                                            d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="download-content d-flex align-items-center gap-2 mt-2">
                                    <div class="">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path
                                                d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z"
                                                stroke="#7B787B" stroke-width="1.25" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <div class="">
                                        <h6> {{__('House rent document for')}} </h6>
                                        <span> {{__('Download file')}} </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endif
                @endif

                @if (!empty($message->message))
                    <p>{{ $message->message }}</p>
                @endif

                <span class="d-flex align-items-end justify-content-end">{{ \Carbon\Carbon::parse($message->created_at)->format('g:i A') }}</span>
            </div>
        </div>
    @endif
@endforeach


@push('js')
    <script src="{{ asset('maan/js/profile.js') }}"></script>
@endpush
