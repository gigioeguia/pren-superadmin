<aside class="woodland-sidebar">
    <div class="woodland-sidebar-top">
        <div class="sidebar-close-btn"><i class="far fa-times" aria-hidden="true"></i></div>
        <a href="{{ url('/admin') }}" class="woodland-sidebar-logo">
            <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
        </a>
    </div>
    <div class="woodland-sidebar-menu">
        <ul>
            @can('dashboard-read')
                <li class="dropdown {{ Request::routeIs('admin.dashboard') ? 'active' : '' }} ">
                    <a href="{{ route('admin.dashboard') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1915_4485)">
                                    <path d="M8.33203 15H11.6654" stroke="#666666" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M1.96079 11.0116C1.66661 9.09716 1.51952 8.14002 1.88144 7.29148C2.24336 6.44293 3.04633 5.86235 4.65225 4.70121L5.85213 3.83366C7.84988 2.38922 8.84872 1.66699 10.0013 1.66699C11.1539 1.66699 12.1527 2.38922 14.1505 3.83366L15.3504 4.70121C16.9563 5.86235 17.7592 6.44293 18.1211 7.29148C18.4831 8.14002 18.336 9.09716 18.0418 11.0116L17.791 12.644C17.3739 15.3577 17.1654 16.7147 16.1921 17.5242C15.2189 18.3337 13.7961 18.3337 10.9503 18.3337H9.0523C6.20658 18.3337 4.78373 18.3337 3.81047 17.5242C2.83721 16.7147 2.62869 15.3577 2.21166 12.644L1.96079 11.0116Z"
                                        stroke="#666666" stroke-width="1.5" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1915_4485">
                                        <rect width="20" height="20" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>

                        </span>
                        {{ __('Dashboard') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan
            @can('properties-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.properties.index', 'admin.properties.create', 'admin.properties.edit', 'admin.properties.show', 'admin.properties.preview', 'admin.properties.success') ? 'active' : '' }} ">
                    <a href="{{ route('admin.properties.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.8346 1.66699L1.66797 5.83366" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M10 2.5V18.3333H5.83333C4.26198 18.3333 3.47631 18.3333 2.98816 17.8452C2.5 17.357 2.5 16.5713 2.5 15V5.83333"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10 5.83301L18.3333 9.99967" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M8.33203 18.3328H14.1654C15.7367 18.3328 16.5224 18.3328 17.0105 17.8446C17.4987 17.3564 17.4987 16.5708 17.4987 14.9994V9.58301"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M15 8.33301V5.83301" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M5.83203 9.16699H6.66536M5.83203 12.5003H6.66536" stroke="#666666"
                                    stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M13.332 11.667H14.1654" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M13.75 18.3333V15" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Properties') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                </li>
            @endcan

            @can('subscriptions-read')
                <li class="dropdown {{ Request::routeIs('admin.subscriptions.index') ? 'active' : '' }} ">
                    <a href="{{ route('admin.subscriptions.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.91663 7.80701C2.91663 4.91241 2.91663 3.46512 3.7709 2.56589C4.62517 1.66666 6.0001 1.66666 8.74996 1.66666H11.25C13.9998 1.66666 15.3747 1.66666 16.229 2.56589C17.0833 3.46512 17.0833 4.91241 17.0833 7.80701V12.193C17.0833 15.0876 17.0833 16.5348 16.229 17.4341C15.3747 18.3333 13.9998 18.3333 11.25 18.3333H8.74996C6.0001 18.3333 4.62517 18.3333 3.7709 17.4341C2.91663 16.5348 2.91663 15.0876 2.91663 12.193V7.80701Z"
                                    stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M11.25 9.16666H14.1667" stroke="white" stroke-width="1.25"
                                    stroke-linecap="round" />
                                <path
                                    d="M5.83337 10C5.83337 10 6.25004 10 6.66671 10.8333C6.66671 10.8333 7.99024 8.75001 9.16671 8.33334"
                                    stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M11.25 14.1667H14.1667" stroke="white" stroke-width="1.25"
                                    stroke-linecap="round" />
                                <path
                                    d="M6.66663 1.66666L6.73513 2.07766C6.90141 3.07536 6.98455 3.57421 7.33423 3.87043C7.68392 4.16666 8.18964 4.16666 9.20113 4.16666H10.7988C11.8103 4.16666 12.316 4.16666 12.6657 3.87043C13.0154 3.57421 13.0985 3.07536 13.2648 2.07766L13.3333 1.66666"
                                    stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M6.66663 14.1667H7.49996" stroke="white" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </span>
                        {{ __('Subscription List') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                </li>
            @endcan

            @can('landlords-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.landlords.index', 'admin.landlords.create', 'admin.landlords.edit') ? 'active' : '' }} ">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.8337 5.83333C10.8337 7.67428 9.34124 9.16667 7.50033 9.16667C5.65938 9.16667 4.16699 7.67428 4.16699 5.83333C4.16699 3.99238 5.65938 2.5 7.50033 2.5C9.34124 2.5 10.8337 3.99238 10.8337 5.83333Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path
                                    d="M12.5 9.16667C14.3409 9.16667 15.8333 7.67428 15.8333 5.83333C15.8333 3.99238 14.3409 2.5 12.5 2.5"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M9.16699 11.6667H5.83366C3.53248 11.6667 1.66699 13.5322 1.66699 15.8334C1.66699 16.7539 2.41318 17.5001 3.33366 17.5001H11.667C12.5875 17.5001 13.3337 16.7539 13.3337 15.8334C13.3337 13.5322 11.4682 11.6667 9.16699 11.6667Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linejoin="round" />
                                <path
                                    d="M14.167 11.6667C16.4682 11.6667 18.3337 13.5322 18.3337 15.8334C18.3337 16.7539 17.5875 17.5001 16.667 17.5001H15.417"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Landlords') }} <i class="fal fa-angle-right"></i></a>

                    </a>
                    <ul class="">
                        <li>
                            <a class="{{ Request::routeIs('admin.landlords.index') && !request()->has('status') ? 'active' : '' }}"
                                href="{{ route('admin.landlords.index') }}">{{ __('All Landlord') }}</a>
                        </li>
                        <li>
                            <a class="{{ Request::routeIs('admin.landlords.create') && !request()->has('status') ? 'active' : '' }}"
                                href="{{ route('admin.landlords.create') }}">{{ __('Add Landlord') }}</a>
                        </li>
                        <li>
                            <a class="{{ Request::routeIs('admin.landlords.index') && request('status') === 'active' ? 'active' : '' }}"
                                href="{{ route('admin.landlords.index', ['status' => 'active']) }}">{{ __('Active Landlord') }}</a>
                        </li>
                        <li>
                            <a class="{{ Request::routeIs('admin.landlords.index') && request('status') === 'expired' ? 'active' : '' }}"
                                href="{{ route('admin.landlords.index', ['status' => 'expired']) }}">{{ __('Expired Landlord') }}</a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('tenants-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.tenants.index', 'admin.tenants.create', 'admin.tenants.edit', 'admin.tenants.show') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.3117 15C17.9361 15 18.4327 14.6071 18.8787 14.0576C19.7916 12.9328 18.2927 12.034 17.7211 11.5938C17.14 11.1463 16.4912 10.8928 15.8333 10.8333M15 9.16667C16.1506 9.16667 17.0833 8.23392 17.0833 7.08333C17.0833 5.93274 16.1506 5 15 5"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M2.68798 15C2.06355 15 1.5669 14.6071 1.12096 14.0576C0.208084 12.9328 1.7069 12.034 2.27854 11.5938C2.85965 11.1463 3.50849 10.8928 4.16634 10.8333M4.58301 9.16667C3.43242 9.16667 2.49968 8.23392 2.49968 7.08333C2.49968 5.93274 3.43242 5 4.58301 5"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M6.73618 12.5927C5.88469 13.1192 3.65216 14.1943 5.01193 15.5396C5.67616 16.1967 6.41594 16.6667 7.34603 16.6667H12.6534C13.5834 16.6667 14.3232 16.1967 14.9874 15.5396C16.3472 14.1943 14.1147 13.1192 13.2632 12.5927C11.2664 11.3581 8.73286 11.3581 6.73618 12.5927Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M12.9164 6.24992C12.9164 7.86075 11.6105 9.16659 9.99969 9.16659C8.38886 9.16659 7.08301 7.86075 7.08301 6.24992C7.08301 4.63909 8.38886 3.33325 9.99969 3.33325C11.6105 3.33325 12.9164 4.63909 12.9164 6.24992Z"
                                    stroke="#666666" stroke-width="1.25" />
                            </svg>

                        </span>
                        {{ __('Tenants') }}<i class="fal fa-angle-right"></i>
                    </a>
                    <ul
                        class="{{ Request::routeIs('admin.tenants.index', 'admin.tenants.create', 'admin.tenants.edit', 'admin.tenants.show') ? 'active' : '' }}">
                        <li>
                            <a class="{{ Request::routeIs('admin.tenants.index', 'admin.tenants.create') && request('status') === null ? 'active' : '' }}"
                                href="{{ route('admin.tenants.index') }}">
                                {{ __('New Tenant') }}
                            </a>
                        </li>
                        <li>
                            <a class="{{ Request::routeIs('admin.tenants.index') && request('status') === 'active' ? 'active' : '' }}"
                                href="{{ route('admin.tenants.index', ['status' => 'active']) }}">
                                {{ __('Active Tenant') }}
                            </a>
                        </li>
                        <li>
                            <a class="{{ Request::routeIs('admin.tenants.index') && request('status') === 'expired' ? 'active' : '' }}"
                                href="{{ route('admin.tenants.index', ['status' => 'expired']) }}">
                                {{ __('Expired Tenant') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('plans-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.plans.index', 'admin.plans.create', 'admin.plans.edit') ? 'active' : '' }}">
                    <a href="{{ route('admin.plans.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.4154 14.1667V5.83333C15.4154 4.26198 15.4154 3.47631 14.9272 2.98816C14.439 2.5 13.6534 2.5 12.082 2.5H7.91536C6.34401 2.5 5.55834 2.5 5.07019 2.98816C4.58203 3.47631 4.58203 4.26198 4.58203 5.83333V14.1667C4.58203 15.738 4.58203 16.5237 5.07019 17.0118C5.55834 17.5 6.34401 17.5 7.91536 17.5H12.082C13.6534 17.5 14.439 17.5 14.9272 17.0118C15.4154 16.5237 15.4154 15.738 15.4154 14.1667Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path
                                    d="M15.418 5H15.8346C17.0131 5 17.6024 5 17.9686 5.36612C18.3346 5.73223 18.3346 6.32149 18.3346 7.5V13.3333C18.3346 14.5118 18.3346 15.1011 17.9686 15.4672C17.6024 15.8333 17.0131 15.8333 15.8346 15.8333H15.418"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M4.58464 5H4.16797C2.98946 5 2.4002 5 2.03409 5.36612C1.66797 5.73223 1.66797 6.32149 1.66797 7.5V13.3333C1.66797 14.5118 1.66797 15.1011 2.03409 15.4672C2.4002 15.8333 2.98946 15.8333 4.16797 15.8333H4.58464"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path d="M12.0846 6.66699H7.91797M12.0846 10.0003H7.91797M12.0846 13.3337H7.91797"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                            </svg>


                        </span>
                        {{ __('Subscription Plan') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @can('transactions-read')
                <li class="dropdown {{ Request::routeIs('admin.transactions.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.transactions.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16.68 1.66699C15.7522 1.66699 15 3.90557 15 6.66699H16.68C17.4897 6.66699 17.8945 6.66699 18.1451 6.38745C18.3957 6.1079 18.3521 5.73977 18.2648 5.0035C18.0345 3.05985 17.4119 1.66699 16.68 1.66699Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path
                                    d="M15.0013 6.71221V15.5385C15.0013 16.7982 15.0013 17.4281 14.6163 17.676C13.9872 18.0812 13.0147 17.2315 12.5256 16.9231C12.1214 16.6682 11.9194 16.5407 11.6951 16.5334C11.4527 16.5254 11.2471 16.6477 10.8104 16.9231L9.21797 17.9273C8.78839 18.1982 8.57364 18.3337 8.33464 18.3337C8.09564 18.3337 7.88085 18.1982 7.4513 17.9273L5.85891 16.9231C5.45476 16.6682 5.25269 16.5407 5.02841 16.5334C4.78607 16.5254 4.58041 16.6477 4.14369 16.9231C3.65459 17.2315 2.68203 18.0812 2.05293 17.676C1.66797 17.4281 1.66797 16.7982 1.66797 15.5385V6.71221C1.66797 4.33387 1.66797 3.1447 2.4002 2.40585C3.13244 1.66699 4.31094 1.66699 6.66797 1.66699H16.668"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M5 5H11.6667" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M6.66667 8.33301H5" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M10.418 9.06217C9.72764 9.06217 9.16797 9.55184 9.16797 10.1559C9.16797 10.76 9.72764 11.2497 10.418 11.2497C11.1083 11.2497 11.668 11.7393 11.668 12.3434C11.668 12.9475 11.1083 13.4372 10.418 13.4372M10.418 9.06217C10.9622 9.06217 11.4252 9.36651 11.5968 9.79134M10.418 9.06217V8.33301M10.418 13.4372C9.87372 13.4372 9.41072 13.1328 9.23914 12.708M10.418 13.4372V14.1663"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                            </svg>


                        </span>
                        {{ __('Transactions') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                </li>
            @endcan

            @canany(['withdraw-methods-read', 'withdraw-request-read', 'rent-payment-read', 'security-deposit-read'])
                <li
                    class="dropdown {{ Request::routeIs('admin.withdraw-methods.index', 'admin.withdraw-methods.create', 'admin.withdraw-methods.edit', 'admin.security-deposits.index', 'admin.rent-payments.index', 'admin.withdraw-request.index', 'admin.withdraw-request.show') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.66699 3.75H7.29813C7.96117 3.75 8.59708 4.01339 9.06591 4.48223L11.667 7.08333"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4.16699 11.25H1.66699" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M7.08366 6.25L8.75033 7.91667C9.21058 8.37692 9.21058 9.12308 8.75033 9.58333C8.29009 10.0436 7.5439 10.0436 7.08366 9.58333L5.83366 8.33333C5.11642 9.05058 3.98092 9.13125 3.16944 8.52267L2.91699 8.33333"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M4.16699 9.16667V12.9167C4.16699 14.488 4.16699 15.2737 4.65515 15.7618C5.1433 16.25 5.92898 16.25 7.50033 16.25H15.0003C16.5717 16.25 17.3573 16.25 17.8455 15.7618C18.3337 15.2737 18.3337 14.488 18.3337 12.9167V10.4167C18.3337 8.84533 18.3337 8.05964 17.8455 7.57149C17.3573 7.08333 16.5717 7.08333 15.0003 7.08333H7.91699"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M12.7087 11.6667C12.7087 12.4721 12.0557 13.125 11.2503 13.125C10.4449 13.125 9.79199 12.4721 9.79199 11.6667C9.79199 10.8612 10.4449 10.2083 11.2503 10.2083C12.0557 10.2083 12.7087 10.8612 12.7087 11.6667Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </span>
                        {{ __('Payments') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                    <ul>
                        @can('withdraw-methods-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.withdraw-methods.index', 'admin.withdraw-methods.create', 'admin.withdraw-methods.edit') ? 'active' : '' }}"
                                    href="{{ route('admin.withdraw-methods.index') }}">{{ __('Withdraw Method') }}
                                </a>
                            </li>
                        @endcan
                        @can('withdraw-request-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.withdraw-request.index', 'admin.withdraw-request.show') ? 'active' : '' }}"
                                    href="{{ route('admin.withdraw-request.index') }}">{{ __('Landlord Withdraw') }}
                                </a>
                            </li>
                        @endcan
                        @can('rent-payment-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.rent-payments.index') ? 'active' : '' }}"
                                    href="{{ route('admin.rent-payments.index') }}">{{ __('Rent Payments') }}
                                </a>
                            </li>
                        @endcan
                        @can('security-deposit-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.security-deposits.index') ? 'active' : '' }}"
                                    href="{{ route('admin.security-deposits.index') }}">{{ __('Security Deposit') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @can('rent-payment-generate-read')
                <li class="dropdown {{ Request::routeIs('admin.rent-payment-generate.index') ? 'active' : '' }}"><a
                        href="{{ route('admin.rent-payment-generate.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16.4541 10.8337C17.1132 9.88882 17.4997 8.73966 17.4997 7.50032C17.4997 4.27867 14.888 1.66699 11.6663 1.66699C8.44467 1.66699 5.83301 4.27866 5.83301 7.50032C5.83301 8.39499 6.03442 9.24257 6.39437 10.0003"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M11.6667 5.00033C10.7462 5.00033 10 5.55997 10 6.25033C10 6.94068 10.7462 7.50033 11.6667 7.50033C12.5872 7.50033 13.3333 8.05997 13.3333 8.75033C13.3333 9.44066 12.5872 10.0003 11.6667 10.0003M11.6667 5.00033C12.3923 5.00033 13.0097 5.34816 13.2385 5.83366M11.6667 5.00033V4.16699M11.6667 10.0003C10.941 10.0003 10.3237 9.65249 10.0948 9.16699M11.6667 10.0003V10.8337"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M2.5 11.667H4.49568C4.74081 11.667 4.98257 11.7222 5.20181 11.8283L6.90346 12.6517C7.1227 12.7577 7.36446 12.8129 7.60958 12.8129H8.47842C9.31875 12.8129 10 13.4722 10 14.2853C10 14.3182 9.9775 14.3471 9.94483 14.3561L7.82739 14.9416C7.44756 15.0466 7.04083 15.01 6.6875 14.839L4.86843 13.9589M10 13.7503L13.8273 12.5744C14.5058 12.363 15.2392 12.6137 15.6642 13.2022C15.9716 13.6277 15.8464 14.2372 15.3987 14.4955L9.13575 18.1091C8.73742 18.3389 8.26745 18.395 7.8293 18.265L2.5 16.6836"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Rent Payment') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @can('reports-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.refund-request.index', 'admin.landlord-withdraws.index', 'admin.rent-payment-reports.index', 'admin.security-deposit-reports.index', 'admin.subscription-reports.index', 'admin.transaction-reports.index') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5.83203 14.9997V13.333M9.9987 14.9997V12.4997M14.1654 14.9997V10.833M2.08203 9.99967C2.08203 6.26772 2.08203 4.40175 3.2414 3.24237C4.40077 2.08301 6.26675 2.08301 9.9987 2.08301C13.7306 2.08301 15.5966 2.08301 16.756 3.24237C17.9154 4.40175 17.9154 6.26772 17.9154 9.99967C17.9154 13.7316 17.9154 15.5976 16.756 16.757C15.5966 17.9163 13.7306 17.9163 9.9987 17.9163C6.26675 17.9163 4.40077 17.9163 3.2414 16.757C2.08203 15.5976 2.08203 13.7316 2.08203 9.99967Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M4.99219 9.5719C6.7881 9.63173 10.8604 9.36065 13.1768 5.68442M11.6589 5.24027L13.2219 4.98872C13.4124 4.96446 13.692 5.11486 13.7608 5.29413L14.174 6.6595"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Reports') }} <i class="fal fa-angle-right"></i></a>
                    </a>

                    <ul>
                        <li>
                            <a class="{{ Request::routeIs('admin.landlord-withdraws.index') ? 'active' : '' }}"
                                href="{{ route('admin.landlord-withdraws.index') }}">{{ __('Landlords Withdraw') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.rent-payment-reports.index') ? 'active' : '' }}"
                                href="{{ route('admin.rent-payment-reports.index') }}">{{ __('Rent Payment') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.security-deposit-reports.index') ? 'active' : '' }}"
                                href="{{ route('admin.security-deposit-reports.index') }}">{{ __('Security Deposit') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.refund-request.index') ? 'active' : '' }}"
                                href="{{ route('admin.refund-request.index') }}">{{ __('Refund Request') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.subscription-reports.index') ? 'active' : '' }}"
                                href="{{ route('admin.subscription-reports.index') }}">{{ __('Subscription') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.transaction-reports.index') ? 'active' : '' }}"
                                href="{{ route('admin.transaction-reports.index') }}">{{ __('Transaction') }}</a>
                        </li>

                    </ul>
                </li>
            @endcan

            @can('messages-read')
                <li class="dropdown {{ Request::routeIs('admin.messages.index') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                        </span>
                        {{ __('Messages') }} <i class="fal fa-angle-right"></i></a>
                    </a>

                    <ul>
                        <li>
                            <a class="{{ Request::routeIs('admin.messages.index') ? 'active' : '' }}"
                                href="{{ route('admin.messages.index') }}">{{ __('Contact Messages') }}</a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['incomes-read', 'income-categories-read'])
                <li
                    class="dropdown {{ Request::routeIs('admin.incomes.index', 'admin.income-categories.index') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.4522 14.0295C16.9053 10.7388 15.2027 8.28844 13.7225 6.84917C13.2917 6.43035 13.0764 6.22094 12.6007 6.02714C12.1249 5.83334 11.716 5.83334 10.8982 5.83334H9.10183C8.28401 5.83334 7.87508 5.83334 7.39935 6.02714C6.92361 6.22094 6.70826 6.43035 6.27753 6.84917C4.79735 8.28844 3.09467 10.7388 2.54772 14.0295C2.14077 16.4778 4.39939 18.3333 6.9236 18.3333H13.0764C15.6006 18.3333 17.8592 16.4778 17.4522 14.0295Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M6.04686 3.70239C5.87493 3.45215 5.62574 3.11249 6.1405 3.03504C6.66961 2.95542 7.21902 3.31761 7.7568 3.31017C8.24332 3.30344 8.49117 3.08766 8.75709 2.77956C9.03709 2.45514 9.47067 1.66666 9.99967 1.66666C10.5287 1.66666 10.9623 2.45514 11.2423 2.77956C11.5082 3.08766 11.756 3.30344 12.2425 3.31017C12.7803 3.31761 13.3298 2.95542 13.8588 3.03504C14.3736 3.11249 14.1244 3.45215 13.9525 3.70239L13.1751 4.83386C12.8426 5.31788 12.6763 5.55989 12.3283 5.69661C11.9804 5.83333 11.5308 5.83333 10.6315 5.83333H9.36784C8.46859 5.83333 8.01892 5.83333 7.67097 5.69661C7.32302 5.55989 7.15676 5.31788 6.82422 4.83386L6.04686 3.70239Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linejoin="round" />
                                <path
                                    d="M11.3559 10.7655C11.1757 10.1004 10.2587 9.50025 9.15799 9.94925C8.05724 10.3982 7.88239 11.8428 9.54741 11.9963C10.2999 12.0656 10.7906 11.9158 11.2397 12.3397C11.689 12.7635 11.7724 13.9423 10.6241 14.26C9.47574 14.5777 8.33866 14.0813 8.21484 13.3764M9.86841 9.16058V9.79425M9.86841 14.3577V14.9939"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </span>
                        {{ __('Incomes') }} <i class="fal fa-angle-right"></i></a>
                    </a>

                    <ul>
                        <li>
                            <a class="{{ Request::routeIs('admin.incomes.index') ? 'active' : '' }}"
                                href="{{ route('admin.incomes.index') }}">{{ __('Incomes') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.income-categories.index') ? 'active' : '' }}"
                                href="{{ route('admin.income-categories.index') }}">{{ __('Income Category') }}</a>
                        </li>

                    </ul>
                </li>
            @endcanany

            @canany(['expenses-read', 'expense-categories-read'])
                <li
                    class="dropdown {{ Request::routeIs('admin.expenses.index', 'admin.expense-categories.index') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.0016 7.50121C9.08113 7.50121 8.33496 8.06085 8.33496 8.75124C8.33496 9.44158 9.08113 10.0012 10.0016 10.0012C10.922 10.0012 11.6683 10.5609 11.6683 11.2512C11.6683 11.9416 10.922 12.5012 10.0016 12.5012M10.0016 7.50121C10.7273 7.50121 11.3446 7.84904 11.5734 8.33458M10.0016 7.50121V6.66788M10.0016 12.5012C9.27588 12.5012 8.65854 12.1534 8.42979 11.6679M10.0016 12.5012V13.3346"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M11.2497 2.08455C11.2497 2.08455 10.5696 2.08455 9.99967 2.08455C6.26772 2.08455 4.40175 2.08455 3.24237 3.24392C2.08301 4.40329 2.08301 6.26927 2.08301 10.0012C2.08301 13.7332 2.08301 15.5992 3.24237 16.7586C4.40175 17.9179 6.26772 17.9179 9.99967 17.9179C13.7316 17.9179 15.5976 17.9179 16.757 16.7586C17.9163 15.5992 17.9163 13.7332 17.9163 10.0012C17.9163 9.43133 17.9163 8.75125 17.9163 8.75125"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M13.75 6.24938L17.2298 2.76755M17.9167 5.81458L17.8182 3.23834C17.8182 2.63113 17.4556 2.25282 16.7952 2.20509L14.1918 2.08272"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Expenses') }} <i class="fal fa-angle-right"></i></a>
                    </a>

                    <ul>
                        <li>
                            <a class="{{ Request::routeIs('admin.expenses.index') ? 'active' : '' }}"
                                href="{{ route('admin.expenses.index') }}">{{ __('Expenses') }}</a>
                        </li>

                        <li>
                            <a class="{{ Request::routeIs('admin.expense-categories.index') ? 'active' : '' }}"
                                href="{{ route('admin.expense-categories.index') }}">{{ __('Expenses Category') }}</a>
                        </li>

                    </ul>
                </li>
            @endcanany

            @can('refund_reasons-read')
                <li class="dropdown {{ Request::routeIs('admin.refund-reason.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.refund-reason.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18.3346 9.58366V4.26197C18.3346 3.60282 18.3346 3.27325 18.1729 2.93338C18.0806 2.73953 17.8705 2.47586 17.7021 2.34265C17.407 2.10908 17.1606 2.05267 16.668 1.93983C15.9016 1.76428 15.0562 1.66699 14.168 1.66699C12.5704 1.66699 11.1117 1.98169 10.0013 2.50033C8.89089 3.01896 7.43219 3.33366 5.83464 3.33366C4.94637 3.33366 4.10104 3.23637 3.33464 3.06083C2.53466 2.8776 1.66797 3.44128 1.66797 4.26197V13.2387C1.66797 13.8978 1.66797 14.2274 1.82974 14.5672C1.92199 14.7611 2.1321 15.0248 2.30045 15.158C2.59563 15.3916 2.84197 15.448 3.33464 15.5608C4.10104 15.7364 4.94637 15.8337 5.83464 15.8337C7.05867 15.8337 8.20116 15.6489 9.16797 15.3292"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                                <path
                                    d="M12.0846 8.75033C12.0846 9.90091 11.1519 10.8337 10.0013 10.8337C8.85072 10.8337 7.91797 9.90091 7.91797 8.75033C7.91797 7.59973 8.85072 6.66699 10.0013 6.66699C11.1519 6.66699 12.0846 7.59973 12.0846 8.75033Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path d="M4.58203 9.58301V9.59051" stroke="#666666" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M15.418 7.91016V7.91764" stroke="#666666" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M16.1124 11.667L16.7984 12.2986C16.9477 12.4453 17.0224 12.5187 16.9961 12.5811C16.9698 12.6434 16.8642 12.6434 16.6531 12.6434H14.0661C12.7416 12.6434 11.668 13.6987 11.668 15.0003C11.668 15.2937 11.7226 15.5746 11.8221 15.8337M13.8902 18.3337L13.2042 17.7021C13.0549 17.5553 12.9802 17.4819 13.0065 17.4196C13.0328 17.3572 13.1384 17.3572 13.3496 17.3572H15.9366C17.261 17.3572 18.3346 16.302 18.3346 15.0003C18.3346 14.7069 18.2801 14.4261 18.1805 14.167"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </span>
                        {{ __('Refund Reason') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @can('house-types-read')
                <li class="dropdown {{ Request::routeIs('admin.house-types.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.house-types.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M1.66797 8.33301L5.83464 3.33301M5.83464 3.33301L10.3465 7.84485C10.5873 8.08571 10.7078 8.20614 10.8609 8.26957C11.0141 8.33301 11.1844 8.33301 11.525 8.33301H18.3346L15.1675 4.53239C14.676 3.94261 14.4302 3.64771 14.0943 3.49036C13.7583 3.33301 13.3745 3.33301 12.6067 3.33301H5.83464Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.16667 6.66699V16.667H2.5V7.38128" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.16797 16.6667H17.5013V7.5" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M3.33203 6.24967V3.33301" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M5.83952 10H5.83203" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M5.83203 16.6663V13.333" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12.5 11.667H14.1667" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </span>
                        {{ __('House Type') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @can('facilities-read')
                <li class="dropdown {{ Request::routeIs('admin.facilities.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.facilities.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_40000001_33860)">
                                    <path d="M1.66675 18.3333H18.3334" stroke="white" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M12.5 18.3333V13.5605C12.5 12.0543 11.3807 10.8333 10 10.8333C8.61925 10.8333 7.5 12.0543 7.5 13.5605V18.3333"
                                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M14.1667 8.33325H16.6667" stroke="white" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3.33325 8.33325H5.83325" stroke="white" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M9.58325 8.33325H10.4166" stroke="white" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3.33325 18.3333V5H16.6666V18.3333" stroke="white" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M4.16675 5.00008V4.16675C4.16675 2.98824 4.16675 2.39898 4.53286 2.03286C4.89898 1.66675 5.48824 1.66675 6.66675 1.66675H13.3334C14.5119 1.66675 15.1012 1.66675 15.4673 2.03286C15.8334 2.39898 15.8334 2.98824 15.8334 4.16675V5.00008"
                                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M2.5 5H17.5" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_40000001_33860">
                                        <rect width="20" height="20" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </span>
                        {{ __('Facility') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @can('amenities-read')
                <li class="dropdown {{ Request::routeIs('admin.amenities.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.amenities.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_40000001_33876)">
                                    <path d="M1.66675 18.3335H18.3334" stroke="#666666" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M3.33325 18.3332V5.62585C3.33325 5.10563 3.33325 4.84553 3.47327 4.63651C3.61329 4.42748 3.86543 4.31116 4.36973 4.07851L8.11973 2.34853C9.33592 1.78748 9.944 1.50695 10.3886 1.76049C10.8333 2.01403 10.8333 2.64131 10.8333 3.89588V18.3332"
                                        stroke="#666666" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M16.6666 18.3335V9.36358C16.6666 8.86241 16.6666 8.61183 16.5214 8.41041C16.3762 8.20904 16.1147 8.09697 15.5918 7.87284L10.8333 5.8335"
                                        stroke="#666666" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M6.25 7.5H7.91667M6.25 10.8333H7.91667M6.25 14.1667H7.91667" stroke="#666666"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_40000001_33876">
                                        <rect width="20" height="20" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </span>
                        {{ __('Amenity') }} <i class="fal fa-angle-right"></i>
                    </a>
                </li>
            @endcan

            @canany(['term-conditions-read', 'privacy-policy-read'])
            <li
                class="dropdown {{ Request::routeIs('admin.website-settings.index', 'admin.term-conditions.index', 'admin.privacy-policy.index', 'admin.testimonials.index', 'admin.testimonials.create', 'admin.testimonials.edit', 'admin.features.index', 'admin.features.create', 'admin.features.edit', 'admin.clients.index', 'admin.clients.create', 'admin.clients.edit', 'admin.blogs.index', 'admin.blogs.create', 'admin.blogs.edit', 'admin.blogs.filter.comment', 'admin.faqs.index', 'admin.faqs.create', 'admin.faqs.edit', 'admin.highlights.index', 'admin.highlights.create', 'admin.highlights.edit') ? 'active' : '' }}">
                <a href="#">
                    <span class="icons">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.3"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                        </svg>
                    </span>
                    {{ __('CMS Manage') }} <i class="fal fa-angle-right"></i></a>
                </a>

                <ul>
                    <li>
                        <a class="{{ Request::routeIs('admin.website-settings.index') ? 'active' : '' }}"
                            href="{{ route('admin.website-settings.index') }}">{{ __('Manage Pages') }}</a>
                    </li>

                    @can('term-conditions-read')
                        <li>
                            <a class="{{ Request::routeIs('admin.term-conditions.index') ? 'active' : '' }}"
                                href="{{ route('admin.term-conditions.index') }}">{{ __('Term Conditions') }} </a>
                        </li>
                    @endcan

                    @can('privacy-policy-read')
                        <li>
                            <a class="{{ Request::routeIs('admin.privacy-policy.index') ? 'active' : '' }}"
                                href="{{ route('admin.privacy-policy.index') }}">{{ __('Privacy Policy') }} </a>
                        </li>
                    @endcan

                    <li>
                        <a class="{{ Request::routeIs('admin.testimonials.index', 'admin.testimonials.create', 'admin.testimonials.edit') ? 'active' : '' }}"
                            href="{{ route('admin.testimonials.index') }}">{{ __('Testimonials') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('admin.highlights.index', 'admin.highlights.create', 'admin.highlights.edit') ? 'active' : '' }}"
                            href="{{ route('admin.highlights.index', 'admin.highlights.create', 'admin.highlights.edit') }}">{{ __('Highlights') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('admin.features.index', 'admin.features.create', 'admin.features.edit') ? 'active' : '' }}"
                            href="{{ route('admin.features.index') }}">{{ __('Features') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('admin.clients.index', 'admin.clients.create', 'admin.clients.edit') ? 'active' : '' }}"
                            href="{{ route('admin.clients.index') }}">{{ __('Clients') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('admin.blogs.index', 'admin.blogs.create', 'admin.blogs.edit', 'admin.blogs.filter.comment') ? 'active' : '' }}"
                            href="{{ route('admin.blogs.index') }}">{{ __('Blogs') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('admin.faqs.index', 'admin.faqs.create', 'admin.faqs.edit') ? 'active' : '' }}"
                            href="{{ route('admin.faqs.index') }}">{{ __('Faq') }}</a>
                    </li>

                </ul>
            </li>
            @endcan

            @can('users-read')
                <li
                    class="dropdown {{ Request::routeIs('admin.users.index', 'admin.users.create', 'admin.users.edit') ? 'active' : '' }} ">
                    <a href="{{ route('admin.users.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.8346 1.66699L1.66797 5.83366" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M10 2.5V18.3333H5.83333C4.26198 18.3333 3.47631 18.3333 2.98816 17.8452C2.5 17.357 2.5 16.5713 2.5 15V5.83333"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10 5.83301L18.3333 9.99967" stroke="#666666" stroke-width="1.25"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M8.33203 18.3328H14.1654C15.7367 18.3328 16.5224 18.3328 17.0105 17.8446C17.4987 17.3564 17.4987 16.5708 17.4987 14.9994V9.58301"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M15 8.33301V5.83301" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M5.83203 9.16699H6.66536M5.83203 12.5003H6.66536" stroke="#666666"
                                    stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M13.332 11.667H14.1654" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M13.75 18.3333V15" stroke="#666666" stroke-width="1.25" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                        </span>
                        {{ __('Staff Management') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                </li>
            @endcan

            @canany(['roles-read', 'permissions-read'])
                <li
                    class="dropdown {{ Request::routeIs('admin.roles.index', 'admin.roles.create', 'admin.roles.edit', 'admin.permissions.index') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M3.15863 7.5309C2.56914 7.85 1.02354 8.50159 1.96491 9.31692C2.42476 9.71517 2.93693 10 3.58083 10H7.25511C7.89901 10 8.41114 9.71517 8.87105 9.31692C9.81239 8.50159 8.2668 7.85 7.67731 7.5309C6.29498 6.78263 4.54096 6.78263 3.15863 7.5309Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path
                                    d="M7.29297 3.56093C7.29297 4.60693 6.4535 5.45487 5.41797 5.45487C4.38244 5.45487 3.54297 4.60693 3.54297 3.56093C3.54297 2.51493 4.38244 1.66699 5.41797 1.66699C6.4535 1.66699 7.29297 2.51493 7.29297 3.56093Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path d="M3.33203 12.5C3.33203 15.2642 5.56775 17.5 8.33203 17.5L7.61775 16.0714"
                                    stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M16.668 7.5C16.668 4.73572 14.4322 2.5 11.668 2.5L12.3822 3.92858" stroke="#666666"
                                    stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M12.3227 15.8639C11.7332 16.183 10.1876 16.8346 11.129 17.6499C11.5889 18.0482 12.101 18.333 12.7449 18.333H16.4192C17.0631 18.333 17.5752 18.0482 18.0351 17.6499C18.9765 16.8346 17.4309 16.183 16.8414 15.8639C15.459 15.1157 13.705 15.1157 12.3227 15.8639Z"
                                    stroke="#666666" stroke-width="1.25" />
                                <path
                                    d="M16.457 11.8939C16.457 12.9399 15.6175 13.7879 14.582 13.7879C13.5465 13.7879 12.707 12.9399 12.707 11.8939C12.707 10.8479 13.5465 10 14.582 10C15.6175 10 16.457 10.8479 16.457 11.8939Z"
                                    stroke="#666666" stroke-width="1.25" />
                            </svg>


                        </span>
                        {{ __('Roles & Permissions') }} <i class="fal fa-angle-right"></i>
                    </a>
                    <ul>
                        @can('roles-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.roles.index', 'admin.roles.create', 'admin.roles.edit') ? 'active' : '' }}"
                                    href="{{ route('admin.roles.index') }}">
                                    {{ __('Roles') }}
                                </a>
                            </li>
                        @endcan

                        @can('permissions-read')
                            <li>
                                <a class="{{ Request::routeIs('admin.permissions.index') ? 'active' : '' }}"
                                    href="{{ route('admin.permissions.index') }}">
                                    {{ __('Permissions') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @can('contact-us-read')
                <li class="dropdown {{ Request::routeIs('admin.supports.index') ? 'active' : '' }} ">
                    <a href="{{ route('admin.supports.index') }}">
                        <span class="icons">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.08203 12.0837H12.9154M7.08203 7.91699H9.9987" stroke="#666666"
                                    stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M11.8101 17.4084C15.296 17.1768 18.0726 14.3607 18.3011 10.8254C18.3458 10.1336 18.3458 9.41709 18.3011 8.72526C18.0726 5.18999 15.296 2.37395 11.8101 2.14223C10.6209 2.06318 9.3793 2.06335 8.19247 2.14223C4.70662 2.37395 1.92998 5.18999 1.7015 8.72526C1.65679 9.41709 1.65679 10.1336 1.7015 10.8254C1.78472 12.113 2.35416 13.3052 3.02456 14.3118C3.41381 15.0166 3.15692 15.8962 2.75147 16.6645C2.45914 17.2185 2.31297 17.4955 2.43033 17.6956C2.5477 17.8957 2.80985 17.9021 3.33416 17.9148C4.37102 17.9401 5.0702 17.6461 5.6252 17.2368C5.93997 17.0048 6.09736 16.8887 6.20583 16.8753C6.3143 16.862 6.52778 16.9499 6.95464 17.1258C7.3383 17.2838 7.78377 17.3813 8.19247 17.4084C9.3793 17.4873 10.6209 17.4875 11.8101 17.4084Z"
                                    stroke="#666666" stroke-width="1.25" stroke-linejoin="round" />
                            </svg>
                        </span>
                        {{ __('Contact Us') }} <i class="fal fa-angle-right"></i></a>
                    </a>
                </li>
            @endcan

            <li
                class="dropdown {{ Request::routeIs('admin.manage-settings.index', 'admin.languages.index', 'admin.settings.index', 'admin.gateways.index', 'admin.currencies.index', 'admin.notifications.index', 'admin.system-settings.index', 'admin.languages.show', 'admin.reminder-messages.index', 'admin.service-charges.index') ? 'active' : '' }}">
                <a href="{{ route('admin.manage-settings.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.9154 9.99967C12.9154 11.6105 11.6095 12.9163 9.9987 12.9163C8.38786 12.9163 7.08203 11.6105 7.08203 9.99967C7.08203 8.38884 8.38786 7.08301 9.9987 7.08301C11.6095 7.08301 12.9154 8.38884 12.9154 9.99967Z"
                                stroke="#666666" stroke-width="1.25" />
                            <path
                                d="M17.5105 11.7473C17.9454 11.6301 18.1629 11.5714 18.2487 11.4593C18.3346 11.3472 18.3346 11.1668 18.3346 10.806V9.19459C18.3346 8.83384 18.3346 8.65343 18.2487 8.54134C18.1628 8.42918 17.9454 8.37051 17.5105 8.25324C15.8851 7.81492 14.8679 6.11568 15.2874 4.50098C15.4027 4.05692 15.4604 3.83489 15.4053 3.70467C15.3502 3.57444 15.1922 3.48471 14.8761 3.30523L13.4388 2.4892C13.1286 2.31308 12.9736 2.22503 12.8344 2.24378C12.6951 2.26253 12.5381 2.4192 12.224 2.73253C11.008 3.94566 8.99597 3.94561 7.77992 2.73246C7.46583 2.41912 7.30879 2.26245 7.16958 2.24369C7.03038 2.22494 6.87528 2.313 6.56508 2.48912L5.12784 3.30515C4.81174 3.48462 4.65369 3.57435 4.59862 3.70455C4.54354 3.83476 4.60119 4.05682 4.71651 4.50093C5.13578 6.11568 4.11774 7.81495 2.49215 8.25326C2.05724 8.37051 1.83978 8.42918 1.75387 8.54126C1.66797 8.65343 1.66797 8.83384 1.66797 9.19459V10.806C1.66797 11.1668 1.66797 11.3472 1.75387 11.4593C1.83976 11.5714 2.05723 11.6301 2.49215 11.7473C4.11747 12.1857 5.1347 13.8849 4.71524 15.4996C4.59988 15.9437 4.54219 16.1657 4.59727 16.2959C4.65235 16.4262 4.8104 16.5159 5.12651 16.6953L6.56376 17.5114C6.87398 17.6875 7.02909 17.7756 7.1683 17.7568C7.30752 17.7381 7.46454 17.5813 7.77856 17.268C8.99522 16.0539 11.0086 16.0538 12.2254 17.2679C12.5394 17.5813 12.6964 17.738 12.8356 17.7568C12.9748 17.7755 13.13 17.6874 13.4401 17.5113L14.8774 16.6953C15.1936 16.5158 15.3516 16.4261 15.4066 16.2958C15.4617 16.1656 15.4041 15.9436 15.2886 15.4995C14.869 13.8849 15.8854 12.1858 17.5105 11.7473Z"
                                stroke="#666666" stroke-width="1.25" stroke-linecap="round" />
                        </svg>
                    </span>
                    {{ __('Settings') }}
                </a>
            </li>
        </ul>
        
        @php
        $version = get_option('version');
        @endphp

        @if($version)
        <div class=" text-center mt-4 pb-4 version-text">Version : {{ $version }}</div>
        @endif
    </div>
</aside>