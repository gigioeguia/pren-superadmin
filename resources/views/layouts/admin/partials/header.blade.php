<div class="woodland-header-section d-print-none sticky-top">
    <div class="woodland-header-left">
        <div class="woodland-sidebar-open" id="toggleArrow">
            <img id="arrowIcon" src="{{ asset('backend/assets/img/icon/sidebar-arrow.svg') }}" alt="">
        </div>
        <div class="header-left">
            <a target="_blank" class=" view-website" href="{{ route('home') }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 22C6.47715 22 2 17.5228 2 12C2 9.20746 3.14465 6.68227 4.99037 4.86802M12 22C11.037 21.2864 11.1907 20.4555 11.6738 19.6247C12.4166 18.3474 12.4166 18.3474 12.4166 16.6444C12.4166 14.9414 13.4286 14.1429 17 14.8571C18.6047 15.1781 19.7741 12.9609 21.8573 13.693M12 22C16.9458 22 21.053 18.4096 21.8573 13.693M4.99037 4.86802C5.83966 4.95765 6.31517 5.41264 7.10496 6.24716C8.6044 7.83152 10.1038 7.96372 11.1035 7.4356C12.6029 6.64343 11.3429 5.3603 13.1027 4.66298C14.1816 4.23551 14.3872 3.11599 13.8766 2.17579M4.99037 4.86802C6.79495 3.09421 9.26969 2 12 2C12.6414 2 13.2687 2.06039 13.8766 2.17579M21.8573 13.693C21.9511 13.1427 22 12.5771 22 12C22 7.11857 18.5024 3.05405 13.8766 2.17579"
                        stroke="black" stroke-width="1.5" stroke-linejoin="round" />
                </svg>
                <p>{{ __('View Website') }}</p>
            </a>
        </div>
    </div>
    <div class="woodland-header-right">
        <div class="woodland-header-curency">
            <div class="language-change">
                <div class="dropdown">
                    @php
                        if (app()->getLocale()) {
                            $lang = languages()->where('code', app()->getLocale())->first();
                        }

                        if (!$lang) {
                            $lang = languages()->where('is_default', 1)->first();
                        }
                    @endphp
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center language-dropdown"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset($lang->icon ?? 'assets/img/default-flag.svg') }}" alt=""
                            class="flag-icon me-2">
                        <span class="lang-name">
                            {{ $lang->name ?? 'English' }}
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-scroll">
                        @foreach (languages() as $language)
                            <li class="language-li">
                                <a class="dropdown-item"
                                    href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}">
                                    <img src="{{ asset($language->icon) }}" alt="" class="flag-icon me-2">
                                    {{ $language->name }}
                                </a>
                                @if (app()->getLocale() == $language->code)
                                    <i class="fas fa-check language-check"></i>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="woodland-header-right-side-meta">
            @if (auth()->user()->role !== 'landlord' || auth()->user()->role !== 'tenant')
                <div class="notifications dropdown">
                    <a href="#" class="dropdown-toggleer" data-bs-toggle="dropdown">
                        <svg width="21" height="27" viewBox="0 0 21 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M11.0676 4.14269C14.6787 4.53949 17.5001 7.68195 17.5001 11.4874V14.4297C17.5001 16.5181 18.3926 18.4892 19.948 19.838C20.3587 20.1968 20.5883 20.7085 20.5883 21.2489C20.5883 22.2673 19.7802 23.0956 18.7867 23.0956H14.0768C13.7183 24.8999 12.1586 26.2615 10.2941 26.2615C8.42959 26.2615 6.8699 24.8999 6.51132 23.0956H1.80141C0.80806 23.0956 0 22.2673 0 21.2489C0 20.7085 0.229582 20.1968 0.629937 19.8453C2.19573 18.4892 3.08823 16.5181 3.08823 14.4297V11.4874C3.08823 7.68095 5.91104 4.53783 9.52344 4.14238V1.72604C9.52344 1.28915 9.86932 0.93457 10.2955 0.93457C10.7217 0.93457 11.0676 1.28915 11.0676 1.72604V4.14269ZM10.2941 24.6786C11.3011 24.6786 12.1596 24.0171 12.4781 23.0956H8.11011C8.42865 24.0171 9.28724 24.6786 10.2941 24.6786ZM1.80141 21.5127L7.20584 21.5127H13.3823L18.7867 21.5127C18.9267 21.5127 19.0442 21.3923 19.0442 21.2489C19.0442 21.1434 18.9874 21.0779 18.9535 21.0484C17.0481 19.3968 15.9559 16.9844 15.9559 14.4297V11.4874C15.9559 8.28674 13.4164 5.68337 10.2941 5.68337C7.17189 5.68337 4.63235 8.28674 4.63235 11.4874V14.4297C4.63235 16.9844 3.54024 19.3968 1.63686 21.0462C1.60066 21.0779 1.54412 21.1434 1.54412 21.2489C1.54412 21.3923 1.66155 21.5127 1.80141 21.5127Z"
                                fill="#1F1F1F" />
                        </svg>
                        <span class="">{{ auth()->user()->unreadNotifications->count() }}</span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="notification-header">
                            <p>@lang('You Have') <strong>{{ auth()->user()->unreadNotifications->count() }}</strong>
                                @lang('new Notifications')</p>
                            <a href="{{ route('admin.notifications.mtReadAll') }}"
                                class="text-red">@lang('Mark all Read')</a>
                        </div>
                        <div class="notification-body">
                            <ul>
                                @foreach (auth()->user()->unreadNotifications as $notification)
                                    <li>
                                        <a href="{{ route('admin.notifications.mtView', $notification->id) }}">
                                            <strong>{{ __($notification->data['admin_msg'] ?? '') }}</strong>
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="notification-footer">
                            <a class="text-red"
                                href="{{ route('admin.notifications.index') }}">@lang('View all notifications')</a>
                        </div>
                    </div>
                </div>
            @endif
            <div class="profile-info dropdown">
                <a href="#" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center justify-content-center gap-2 profile-drop-btn">
                        <img src="{{ asset(auth()->user()->image ?? 'backend/assets/img/profile/user.png') }}"
                            alt="Profile">
                        <p> {{ auth()->user()->name ?? 'Sahidul Islam' }} </p>
                        <img class="arrow" src="{{ asset('backend/assets/img/icon/profile-arrow.svg') }}" alt="">
                    </div>
                </a>
                <ul class="dropdown-menu">
                    <li class="dropdown-profile-info">
                        <strong>{{ auth()->user()->name ?? '' }}</strong>
                    </li>
                    <li>
                        <a class="d-flex align-items-center gap-2" href="#">
                            <img class="cache-clear" src="{{ asset('backend/assets/img/icon/broom.png') }}" alt="">
                            {{ __('Cache Clear') }}
                        </a>
                    </li>
                    <li>
                        <a class="d-flex align-items-center gap-2" href="{{ route('admin.profile.index') }}">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M13.227 12.1286C13.227 14.3259 10.2137 14.5799 7.94766 14.5799L7.7855 14.5798C6.3418 14.5763 2.66699 14.4851 2.66699 12.1153C2.66699 9.96278 5.55923 9.67512 7.80798 9.66427L8.10981 9.66408C9.55342 9.66759 13.227 9.75874 13.227 12.1286ZM7.94766 10.6639C5.10699 10.6639 3.66699 11.1519 3.66699 12.1153C3.66699 13.0873 5.10699 13.5799 7.94766 13.5799C10.7877 13.5799 12.227 13.0919 12.227 12.1286C12.227 11.1566 10.7877 10.6639 7.94766 10.6639ZM7.94766 1.33301C9.89966 1.33301 11.487 2.92101 11.487 4.87301C11.487 6.82501 9.89966 8.41234 7.94766 8.41234H7.92633C5.97833 8.40634 4.40033 6.81767 4.40697 4.87101C4.40697 2.92101 5.99499 1.33301 7.94766 1.33301ZM7.94766 2.28501C6.52033 2.28501 5.35898 3.44567 5.35898 4.87301C5.35433 6.29567 6.50699 7.45568 7.92833 7.46101L7.94766 7.93701V7.46101C9.37433 7.46101 10.535 6.29967 10.535 4.87301C10.535 3.44567 9.37433 2.28501 7.94766 2.28501Z"
                                    fill="#1F1F1F" />
                            </svg>
                            @lang('Profile')
                        </a>
                    </li>
                    <li>
                        <a class="d-flex align-items-center gap-2" href="{{ route('logout') }}">
                            <svg width="15" height="14" viewBox="0 0 15 14" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M6.53932 0.333008C8.16998 0.333008 9.49665 1.65967 9.49665 3.29034V3.91167C9.49665 4.18767 9.27265 4.41167 8.99665 4.41167C8.72065 4.41167 8.49665 4.18767 8.49665 3.91167V3.29034C8.49665 2.21034 7.61865 1.33301 6.53932 1.33301H3.28932C2.21132 1.33301 1.33398 2.21034 1.33398 3.29034V10.7097C1.33398 11.789 2.21132 12.6663 3.28932 12.6663H6.54665C7.62132 12.6663 8.49665 11.7917 8.49665 10.717V10.0883C8.49665 9.81234 8.72065 9.58834 8.99665 9.58834C9.27265 9.58834 9.49665 9.81234 9.49665 10.0883V10.717C9.49665 12.3437 8.17265 13.6663 6.54665 13.6663H3.28932C1.65998 13.6663 0.333984 12.3403 0.333984 10.7097V3.29034C0.333984 1.65967 1.65998 0.333008 3.28932 0.333008H6.53932ZM11.9261 4.70234L13.8781 6.64567C13.8955 6.66295 13.9111 6.68092 13.9253 6.69994L13.8781 6.64567C13.9017 6.66896 13.9227 6.69441 13.9409 6.72154C13.9489 6.73384 13.9567 6.74655 13.9638 6.75962C13.9696 6.76983 13.975 6.78048 13.98 6.79132C13.9842 6.80089 13.9882 6.81046 13.992 6.82019C13.997 6.83286 14.0014 6.84588 14.0053 6.85908C14.0082 6.86947 14.0108 6.87985 14.0132 6.89034C14.0162 6.90308 14.0185 6.91589 14.0203 6.9288C14.0214 6.93719 14.0224 6.94598 14.0232 6.95485C14.0247 6.97005 14.0254 6.98483 14.0254 6.99967L14.022 7.04101L14.0206 7.06749C14.0205 7.06862 14.0203 7.06975 14.0201 7.07088L14.0254 6.99967C14.0254 7.03668 14.0213 7.07332 14.0133 7.10895C14.0108 7.1195 14.0082 7.12987 14.0052 7.14011C14.0014 7.15347 13.997 7.16649 13.9921 7.17931C13.9882 7.18888 13.9842 7.19846 13.9799 7.20788C13.975 7.21886 13.9697 7.22952 13.9639 7.23998C13.9567 7.2528 13.9489 7.26551 13.9407 7.27783C13.936 7.28514 13.9308 7.29236 13.9255 7.29944C13.9096 7.32033 13.8923 7.33997 13.8736 7.35817L11.9261 9.29767C11.8287 9.39501 11.7007 9.44367 11.5734 9.44367C11.4454 9.44367 11.3167 9.39501 11.2194 9.29634C11.0247 9.10034 11.0254 8.78434 11.2207 8.58967L12.314 7.49967H5.49785C5.22185 7.49967 4.99785 7.27567 4.99785 6.99967C4.99785 6.72367 5.22185 6.49967 5.49785 6.49967H12.3153L11.2207 5.41034C11.0254 5.21567 11.0241 4.89967 11.2194 4.70367C11.4141 4.50767 11.7301 4.50767 11.9261 4.70234Z"
                                    fill="#1F1F1F" />
                            </svg>
                            {{ __('Logout') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>