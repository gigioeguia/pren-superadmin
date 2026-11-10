@extends('layouts.landlord.master')

@section('title')
    {{ __('Support List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="chat-right-card p-0 main-chat-container position-relative">
            <div class="chat-header d-flex gap-2 align-items-center">
                <img class="" src="{{ asset($admin->image ?? 'backend/assets/img/icon/chat-profile.svg') }}">
                <p class="user-name">{{ $admin->name }}</p>
            </div>

            <div class="chats-container p-3" id="message-container">
                @include('landlord.supports.message')
            </div>
            <form action="{{ route('landlord.supports.store') }}" method="post" class="chatFormSubmit chat-form"
                data-route="{{ route('landlord.supports.get-message') }}" enctype="multipart/form-data">
                @csrf
                <div class="chat-input-container d-flex align-items-center gap-2 m-3 mb-0 pb-3 pt-3 position-relative ">
                    <input type="file" name="file" class="form-control-file chat-file-input restore-file" id="landlordFile1">
                    <div class="chat-file" onclick="openFileInput()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 13C10.4295 13.5741 10.9774 14.0491 11.6066 14.3929C12.2357 14.7367 12.9315 14.9411 13.6467 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9548 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.5521 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.46997L11.75 5.17997"
                                stroke="#00A551" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14.0002 10.9992C13.5707 10.4251 13.0228 9.95007 12.3937 9.60631C11.7645 9.26255 11.0687 9.05813 10.3535 9.00691C9.63841 8.9557 8.92061 9.05888 8.24885 9.30947C7.5771 9.56005 6.96709 9.95218 6.4602 10.4592L3.4602 13.4592C2.54941 14.4023 2.04544 15.6653 2.05683 16.9763C2.06822 18.2872 2.59407 19.5413 3.52111 20.4683C4.44815 21.3954 5.70221 21.9212 7.01319 21.9326C8.32418 21.944 9.58719 21.44 10.5302 20.5292L12.2402 18.8192"
                                stroke="#00A551" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <input type="hidden" name="parent_id" value="{{ $messages->first()->id ?? '' }}">
                    <div class="chat-input ">
                        <input placeholder="{{ __('Type here...') }}" type="text" class="restore-message" name="message">
                    </div>
                    <button class="position-absolute chat-submit-btn">{{ __('Submit') }}
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.4384 2.56037C16.8534 1.97537 16.025 1.74537 15.22 1.94621L4.29269 4.67788C2.83603 5.04205 1.86426 6.29537 1.87509 7.79703C1.88592 9.2987 2.87436 10.5379 4.33603 10.882L8.20769 11.7929L9.11854 15.6637C9.46271 17.1254 10.7018 18.1137 12.2035 18.1245C12.211 18.1245 12.2194 18.1245 12.2269 18.1245C13.7177 18.1245 14.9592 17.1554 15.3217 15.707L18.0535 4.77953C18.2535 3.97537 18.0243 3.1462 17.4384 2.56037ZM16.8401 4.47621L14.1084 15.4037C13.8342 16.4979 12.9502 16.8812 12.211 16.8745C11.5068 16.8695 10.5935 16.4745 10.3352 15.3779L9.41517 11.4687L12.941 7.94204C13.1851 7.69788 13.1851 7.30204 12.941 7.05787C12.6968 6.81371 12.3009 6.81371 12.0568 7.05787L8.53016 10.5845L4.62086 9.66453C3.52336 9.4062 3.12846 8.49205 3.12346 7.78788C3.11846 7.08371 3.50087 6.16371 4.59421 5.89037L15.5217 3.15869C15.6109 3.13619 15.7009 3.12538 15.7893 3.12538C16.0726 3.12538 16.3459 3.23704 16.5534 3.44454C16.8259 3.71621 16.9334 4.10205 16.8401 4.47621Z" fill="white" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
