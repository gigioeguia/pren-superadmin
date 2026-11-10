<aside class="woodland-sidebar">
    <div class="woodland-sidebar-top">
        <div class="sidebar-close-btn"><i class="far fa-times" aria-hidden="true"></i></div>
        <a href="{{ route('landlord.dashboard') }}" class="woodland-sidebar-logo">
            <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt=""></a>
    </div>
    <div class="woodland-sidebar-menu">
        <ul>

            <li class="dropdown {{ Request::routeIs('landlord.dashboard') ? 'active' : '' }} ">
                <a href="{{ route('landlord.dashboard') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1914_4881)">
                            <path d="M8.33203 15H11.6654" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M1.96079 11.0116C1.66661 9.09716 1.51952 8.14002 1.88144 7.29148C2.24336 6.44293 3.04633 5.86235 4.65225 4.70121L5.85213 3.83366C7.84988 2.38922 8.84872 1.66699 10.0013 1.66699C11.1539 1.66699 12.1527 2.38922 14.1505 3.83366L15.3504 4.70121C16.9563 5.86235 17.7592 6.44293 18.1211 7.29148C18.4831 8.14002 18.336 9.09716 18.0418 11.0116L17.791 12.644C17.3739 15.3577 17.1654 16.7147 16.1921 17.5242C15.2189 18.3337 13.7961 18.3337 10.9503 18.3337H9.0523C6.20658 18.3337 4.78373 18.3337 3.81047 17.5242C2.83721 16.7147 2.62869 15.3577 2.21166 12.644L1.96079 11.0116Z" stroke="#666666" stroke-width="1.5" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_1914_4881">
                            <rect width="20" height="20" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </span>
                    {{ __('Dashboard') }} <i class="fal fa-angle-right"></i>
                </a>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.properties.index', 'landlord.properties.create', 'landlord.properties.edit', 'landlord.properties.show', 'landlord.properties.preview', 'landlord.properties.success') ? 'active' : '' }} ">
                <a href="{{ route('landlord.properties.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.8346 1.66699L1.66797 5.83366" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 2.5V18.3333H5.83333C4.26198 18.3333 3.47631 18.3333 2.98816 17.8452C2.5 17.357 2.5 16.5713 2.5 15V5.83333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 5.83301L18.3333 9.99967" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.33203 18.3328H14.1654C15.7367 18.3328 16.5224 18.3328 17.0105 17.8446C17.4987 17.3564 17.4987 16.5708 17.4987 14.9994V9.58301" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 8.33301V5.83301" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.83203 9.16699H6.66536M5.83203 12.5003H6.66536" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.332 11.667H14.1654" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.75 18.3333V15" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Properties') }} <i class="fal fa-angle-right"></i>
                </a>
            </li>

            <li
                class="dropdown {{ Request::routeIs('landlord.tenant-applications.index', 'landlord.tenant-applications.show') ? 'active' : '' }} ">
                <a href="{{ route('landlord.tenant-applications.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.5394 2.54476C15.7244 0.590119 2.07174 5.37832 2.08301 7.12648C2.0958 9.1089 7.41475 9.71873 8.88901 10.1324C9.77559 10.3811 10.013 10.6361 10.2174 11.5657C11.1433 15.7761 11.6081 17.8702 12.6675 17.917C14.3562 17.9916 19.3108 4.45232 17.5394 2.54476Z" stroke="#666666" stroke-width="1.25"/>
                            <path d="M9.58301 10.4167L12.4997 7.5" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Application') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>

            <li
                class="dropdown {{ Request::routeIs('landlord.tenants.index', 'landlord.tenants.create', 'landlord.tenants.edit', 'landlord.tenants.show') ? 'active' : '' }} ">
                <a href="#">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.3117 15C17.9361 15 18.4327 14.6071 18.8787 14.0576C19.7916 12.9328 18.2927 12.034 17.7211 11.5938C17.14 11.1463 16.4912 10.8928 15.8333 10.8333M15 9.16667C16.1506 9.16667 17.0833 8.23392 17.0833 7.08333C17.0833 5.93274 16.1506 5 15 5" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M2.68798 15C2.06355 15 1.5669 14.6071 1.12096 14.0576C0.208084 12.9328 1.7069 12.034 2.27854 11.5938C2.85965 11.1463 3.50849 10.8928 4.16634 10.8333M4.58301 9.16667C3.43242 9.16667 2.49968 8.23392 2.49968 7.08333C2.49968 5.93274 3.43242 5 4.58301 5" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M6.73618 12.5925C5.88469 13.119 3.65216 14.1941 5.01193 15.5393C5.67616 16.1965 6.41594 16.6665 7.34603 16.6665H12.6534C13.5834 16.6665 14.3232 16.1965 14.9874 15.5393C16.3472 14.1941 14.1147 13.119 13.2632 12.5925C11.2664 11.3578 8.73286 11.3578 6.73618 12.5925Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.9164 6.25016C12.9164 7.861 11.6105 9.16683 9.99969 9.16683C8.38886 9.16683 7.08301 7.861 7.08301 6.25016C7.08301 4.63933 8.38886 3.3335 9.99969 3.3335C11.6105 3.3335 12.9164 4.63933 12.9164 6.25016Z" stroke="#666666" stroke-width="1.25"/>
                            </svg>

                    </span>
                    {{ __('Tenant List') }} <i class="fal fa-angle-right"></i>
                </a>
                <ul
                    class="{{ Request::routeIs('landlord.tenants.index', 'landlord.tenants.create') ? 'active' : '' }}">
                    <li>
                        <a class="{{ Request::routeIs('landlord.tenants.index', 'landlord.tenants.create', 'landlord.tenants.edit', 'landlord.tenants.show') && !request('status') ? 'active' : '' }}"
                            href="{{ route('landlord.tenants.index') }}">
                            {{ __('All Tenant') }}
                        </a>
                    </li>
                    <li>
                        <a class="{{ Request::routeIs('landlord.tenants.index', 'landlord.tenants.create', 'landlord.tenants.edit', 'landlord.tenants.show') && request('status') == 'active' ? 'active' : '' }}"
                            href="{{ route('landlord.tenants.index', ['status' => 'active']) }}">
                            {{ __('Active Tenant') }}
                        </a>
                    </li>
                    <li>
                        <a class="{{ Request::routeIs('landlord.tenants.index', 'landlord.tenants.create', 'landlord.tenants.edit', 'landlord.tenants.show') && request('status') == 'expired' ? 'active' : '' }}"
                            href="{{ route('landlord.tenants.index', ['status' => 'expired']) }}">
                            {{ __('Expire Tenant') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li
                class="dropdown {{ Request::routeIs('landlord.maintenances.index', 'landlord.maintenance-reports.index', 'landlord.maintenances.show') ? 'active' : '' }}">
                <a href="#">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.99967 18.3327H8.74967C5.60695 18.3327 4.03559 18.3327 3.05928 17.3563C2.08297 16.38 2.08298 14.8087 2.08301 11.6659L2.08304 8.33262C2.08307 5.18995 2.08307 3.61862 3.05939 2.64232C4.0357 1.66602 5.60703 1.66602 8.74967 1.66602H9.58292C12.7257 1.66602 14.297 1.66602 15.2733 2.64232C16.2496 3.61864 16.2496 5.18998 16.2496 8.33268V9.16602" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.83301 5.83398H12.4997M5.83301 10.0007H9.58301" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5833 17.3112C16.0561 17.3112 17.25 16.0902 17.25 14.584C17.25 13.0777 16.0561 11.8567 14.5833 11.8567M14.5833 17.3112C13.1106 17.3112 11.9167 16.0902 11.9167 14.584C11.9167 13.0777 13.1106 11.8567 14.5833 11.8567M14.5833 17.3112V18.334M14.5833 11.8567V10.834M12.1594 13.0964L11.2503 12.5386M17.9167 16.6294L17.0077 16.0716M17.0073 13.0964L17.9163 12.5386M11.25 16.6294L12.159 16.0716" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            </svg>

                    </span>
                    {{ __('Maintenance') }} <i class="fal fa-angle-right"></i></a>
                </a>
                <ul class="sub-menu">
                    <li><a class="{{ Request::routeIs('landlord.maintenances.index', 'landlord.maintenances.show') ? 'active' : '' }}"
                            href="{{ route('landlord.maintenances.index') }}">{{ __('Maintenance List') }} </a></li>
                    <li><a class="{{ Request::routeIs('landlord.maintenance-reports.index') ? 'active' : '' }}"
                            href="{{ route('landlord.maintenance-reports.index') }}">{{ __('Maintenance Report') }} </a></li>
                </ul>
            </li>


            <li class="dropdown {{ Request::routeIs('landlord.labors.index') ? 'active' : '' }}">
                <a href="{{ route('landlord.labors.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6663 18.3333V15.8333C16.6663 14.262 16.6663 13.4763 16.1782 12.9882C15.69 12.5 14.9043 12.5 13.333 12.5H11.6663L9.99967 14.1667L8.33301 12.5H6.66634C5.09499 12.5 4.30932 12.5 3.82117 12.9882C3.33301 13.4763 3.33301 14.262 3.33301 15.8333V18.3333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.333 12.5V18.3333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.66699 12.5V18.3333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.9163 7.49935V5.83268C12.9163 4.22185 11.6105 2.91602 9.99967 2.91602C8.38884 2.91602 7.08301 4.22185 7.08301 5.83268V7.49935C7.08301 9.11018 8.38884 10.416 9.99967 10.416C11.6105 10.416 12.9163 9.11018 12.9163 7.49935Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.25 6.25H13.75" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 1.66602V2.91602" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Labors') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>


            <li class="dropdown {{ Request::routeIs('landlord.rents.index', 'landlord.rents.show') ? 'active' : '' }}">
                <a class=""
                    href="{{ route('landlord.rents.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.3337 9.19782C17.6595 9.88307 16.1887 10.9057 14.222 11.6997M14.222 11.6997C13.7717 11.8816 13.2932 12.0501 12.7892 12.1977C12.1617 12.4172 10.8587 13.1744 10.9066 14.8452L10.8521 17.4698C10.852 17.7582 11.1982 18.2177 11.8615 18.3121C12.6797 18.4285 13.8907 18.0807 14.4464 17.3709C15.2758 16.3117 15.2318 13.8145 14.222 11.6997ZM1.66699 9.17149C2.34117 9.85666 3.77218 10.9057 5.73888 11.6997M5.73888 11.6997C6.18926 11.8816 6.66773 12.0501 7.17164 12.1977C8.29363 12.5266 9.11349 13.4975 9.15841 14.7472C9.20333 15.9969 9.25941 16.7101 9.05433 17.0457C8.84916 17.3812 8.36708 17.5202 7.82833 17.5489C7.28957 17.5777 6.14476 17.2856 5.58904 16.5759C4.75962 15.5166 4.7291 13.8145 5.73888 11.6997ZM8.31324 8.74999C8.31324 9.44033 7.7516 9.99999 7.05878 9.99999C6.36597 9.99999 5.80433 9.44033 5.80433 8.74999C5.80433 8.05964 6.36597 7.5 7.05878 7.5C7.7516 7.5 8.31324 8.05964 8.31324 8.74999ZM14.1673 8.74999C14.1673 9.44033 13.6057 9.99999 12.9129 9.99999C12.2201 9.99999 11.6584 9.44033 11.6584 8.74999C11.6584 8.05964 12.2201 7.5 12.9129 7.5C13.6057 7.5 14.1673 8.05964 14.1673 8.74999Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.6742 7.49935C16.6665 4.9041 16.5843 3.52824 15.6952 2.64232C14.7154 1.66602 13.1385 1.66602 9.98462 1.66602C6.83067 1.66602 5.25373 1.66602 4.27393 2.64232C3.38487 3.52824 3.30255 4.9041 3.29492 7.49935" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Rent Invitation') }}<i class="fal fa-angle-right"></i>
                </a>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.subscriptions.index') ? 'active' : '' }}">
                <a class=""
                    href="{{ route('landlord.subscriptions.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.4154 14.1667V5.83333C15.4154 4.26198 15.4154 3.47631 14.9272 2.98816C14.439 2.5 13.6534 2.5 12.082 2.5H7.91536C6.34401 2.5 5.55834 2.5 5.07019 2.98816C4.58203 3.47631 4.58203 4.26198 4.58203 5.83333V14.1667C4.58203 15.738 4.58203 16.5237 5.07019 17.0118C5.55834 17.5 6.34401 17.5 7.91536 17.5H12.082C13.6534 17.5 14.439 17.5 14.9272 17.0118C15.4154 16.5237 15.4154 15.738 15.4154 14.1667Z" stroke="#666666" stroke-width="1.25"/>
                            <path d="M15.418 5H15.8346C17.0131 5 17.6024 5 17.9686 5.36612C18.3346 5.73223 18.3346 6.32149 18.3346 7.5V13.3333C18.3346 14.5118 18.3346 15.1011 17.9686 15.4672C17.6024 15.8333 17.0131 15.8333 15.8346 15.8333H15.418" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M4.58464 5H4.16797C2.98946 5 2.4002 5 2.03409 5.36612C1.66797 5.73223 1.66797 6.32149 1.66797 7.5V13.3333C1.66797 14.5118 1.66797 15.1011 2.03409 15.4672C2.4002 15.8333 2.98946 15.8333 4.16797 15.8333H4.58464" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M12.0846 6.66699H7.91797M12.0846 10.0003H7.91797M12.0846 13.3337H7.91797" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            </svg>


                    </span>
                    {{ __('Subscription') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>


            <li
                class="dropdown {{ Request::routeIs('landlord.rent-payments.index', 'landlord.security-deposits.index', 'landlord.refund-request.index') ? 'active' : '' }} ">
                <a href="#">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.66699 3.75H7.29813C7.96117 3.75 8.59708 4.01339 9.06591 4.48223L11.667 7.08333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.16699 11.25H1.66699" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.08366 6.25L8.75033 7.91667C9.21058 8.37692 9.21058 9.12308 8.75033 9.58333C8.29009 10.0436 7.5439 10.0436 7.08366 9.58333L5.83366 8.33333C5.11642 9.05058 3.98092 9.13125 3.16944 8.52267L2.91699 8.33333" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.16699 9.16732V12.9173C4.16699 14.4887 4.16699 15.2743 4.65515 15.7625C5.1433 16.2507 5.92898 16.2507 7.50033 16.2507H15.0003C16.5717 16.2507 17.3573 16.2507 17.8455 15.7625C18.3337 15.2743 18.3337 14.4887 18.3337 12.9173V10.4173C18.3337 8.84598 18.3337 8.06029 17.8455 7.57214C17.3573 7.08398 16.5717 7.08398 15.0003 7.08398H7.91699" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.7087 11.6673C12.7087 12.4727 12.0557 13.1257 11.2503 13.1257C10.4449 13.1257 9.79199 12.4727 9.79199 11.6673C9.79199 10.8619 10.4449 10.209 11.2503 10.209C12.0557 10.209 12.7087 10.8619 12.7087 11.6673Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Payments') }} <i class="fal fa-angle-right"></i></a>

                </a>
                <ul class="sub-menu">
                    <li><a class="{{ Request::routeIs('landlord.rent-payments.index') ? 'active' : '' }}" href="{{ route('landlord.rent-payments.index') }}">{{ __('Rent Payments') }} </a></li>
                    <li><a class="{{ Request::routeIs('landlord.security-deposits.index') ? 'active' : '' }}" href="{{ route('landlord.security-deposits.index') }}">{{ __('Security Deposit') }} </a></li>
                    <li><a class="{{ Request::routeIs('landlord.refund-request.index') ? 'active' : '' }}" href="{{ route('landlord.refund-request.index') }}">{{ __('Refund Request') }}</a></li>
                </ul>
            </li>

            <li
            class="dropdown {{ Request::routeIs('landlord.incomes.index', 'landlord.income-categories.index') ? 'active' : '' }}">
            <a href="#">
                <span class="icons">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.4522 14.0295C16.9053 10.7388 15.2027 8.28844 13.7225 6.84917C13.2917 6.43035 13.0764 6.22094 12.6007 6.02714C12.1249 5.83334 11.716 5.83334 10.8982 5.83334H9.10183C8.28401 5.83334 7.87508 5.83334 7.39935 6.02714C6.92361 6.22094 6.70826 6.43035 6.27753 6.84917C4.79735 8.28844 3.09467 10.7388 2.54772 14.0295C2.14077 16.4778 4.39939 18.3333 6.9236 18.3333H13.0764C15.6006 18.3333 17.8592 16.4778 17.4522 14.0295Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.04686 3.70239C5.87493 3.45215 5.62574 3.11249 6.1405 3.03504C6.66961 2.95542 7.21902 3.31761 7.7568 3.31017C8.24332 3.30344 8.49117 3.08766 8.75709 2.77956C9.03709 2.45514 9.47067 1.66666 9.99967 1.66666C10.5287 1.66666 10.9623 2.45514 11.2423 2.77956C11.5082 3.08766 11.756 3.30344 12.2425 3.31017C12.7803 3.31761 13.3298 2.95542 13.8588 3.03504C14.3736 3.11249 14.1244 3.45215 13.9525 3.70239L13.1751 4.83386C12.8426 5.31788 12.6763 5.55989 12.3283 5.69661C11.9804 5.83333 11.5308 5.83333 10.6315 5.83333H9.36784C8.46859 5.83333 8.01892 5.83333 7.67097 5.69661C7.32302 5.55989 7.15676 5.31788 6.82422 4.83386L6.04686 3.70239Z" stroke="#666666" stroke-width="1.25" stroke-linejoin="round"/>
                        <path d="M11.3559 10.7655C11.1757 10.1004 10.2587 9.50025 9.15799 9.94925C8.05724 10.3982 7.88239 11.8428 9.54741 11.9963C10.2999 12.0656 10.7906 11.9158 11.2397 12.3397C11.689 12.7635 11.7724 13.9423 10.6241 14.26C9.47574 14.5777 8.33866 14.0813 8.21484 13.3764M9.86841 9.16058V9.79425M9.86841 14.3577V14.9939" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                </span>
                {{ __('Incomes') }} <i class="fal fa-angle-right"></i></a>
            </a>

            <ul>
                <li>
                    <a class="{{ Request::routeIs('landlord.incomes.index') ? 'active' : '' }}"
                        href="{{ route('landlord.incomes.index') }}">{{ __('Incomes') }}</a>
                </li>

                <li>
                    <a class="{{ Request::routeIs('landlord.income-categories.index') ? 'active' : '' }}"
                        href="{{ route('landlord.income-categories.index') }}">{{ __('Income Category') }}</a>
                </li>

            </ul>
        </li>

            <li
                class="dropdown {{ Request::routeIs('landlord.expenses.index', 'landlord.expense-categories.index') ? 'active' : '' }}">
                <a href="#">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.0016 7.50121C9.08113 7.50121 8.33496 8.06085 8.33496 8.75124C8.33496 9.44158 9.08113 10.0012 10.0016 10.0012C10.922 10.0012 11.6683 10.5609 11.6683 11.2512C11.6683 11.9416 10.922 12.5012 10.0016 12.5012M10.0016 7.50121C10.7273 7.50121 11.3446 7.84904 11.5734 8.33458M10.0016 7.50121V6.66788M10.0016 12.5012C9.27588 12.5012 8.65854 12.1534 8.42979 11.6679M10.0016 12.5012V13.3346" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M11.2497 2.08455C11.2497 2.08455 10.5696 2.08455 9.99967 2.08455C6.26772 2.08455 4.40175 2.08455 3.24237 3.24392C2.08301 4.40329 2.08301 6.26927 2.08301 10.0012C2.08301 13.7332 2.08301 15.5992 3.24237 16.7586C4.40175 17.9179 6.26772 17.9179 9.99967 17.9179C13.7316 17.9179 15.5976 17.9179 16.757 16.7586C17.9163 15.5992 17.9163 13.7332 17.9163 10.0012C17.9163 9.43133 17.9163 8.75125 17.9163 8.75125" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M13.75 6.24938L17.2298 2.76755M17.9167 5.81458L17.8182 3.23834C17.8182 2.63113 17.4556 2.25282 16.7952 2.20509L14.1918 2.08272" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Expenses') }} <i class="fal fa-angle-right"></i></a>
                </a>

                <ul>
                    <li>
                        <a class="{{ Request::routeIs('landlord.expenses.index') ? 'active' : '' }}"
                            href="{{ route('landlord.expenses.index') }}">{{ __('Expenses') }}</a>
                    </li>

                    <li>
                        <a class="{{ Request::routeIs('landlord.expense-categories.index') ? 'active' : '' }}"
                            href="{{ route('landlord.expense-categories.index') }}">{{ __('Expenses Category') }}</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.facilities.index') ? 'active' : '' }}">
                <a href="{{ route('landlord.facilities.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_40000001_33860)">
                                <path d="M1.66675 18.3333H18.3334" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.5 18.3333V13.5605C12.5 12.0543 11.3807 10.8333 10 10.8333C8.61925 10.8333 7.5 12.0543 7.5 13.5605V18.3333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14.1667 8.33325H16.6667" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3.33325 8.33325H5.83325" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.58325 8.33325H10.4166" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3.33325 18.3333V5H16.6666V18.3333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.16675 5.00008V4.16675C4.16675 2.98824 4.16675 2.39898 4.53286 2.03286C4.89898 1.66675 5.48824 1.66675 6.66675 1.66675H13.3334C14.5119 1.66675 15.1012 1.66675 15.4673 2.03286C15.8334 2.39898 15.8334 2.98824 15.8334 4.16675V5.00008" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.5 5H17.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_40000001_33860">
                                    <rect width="20" height="20" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                    {{ __('Facility') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.amenities.index') ? 'active' : '' }}">
                <a href="{{ route('landlord.amenities.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_40000001_33876)">
                                <path d="M1.66675 18.3335H18.3334" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3.33325 18.3332V5.62585C3.33325 5.10563 3.33325 4.84553 3.47327 4.63651C3.61329 4.42748 3.86543 4.31116 4.36973 4.07851L8.11973 2.34853C9.33592 1.78748 9.944 1.50695 10.3886 1.76049C10.8333 2.01403 10.8333 2.64131 10.8333 3.89588V18.3332" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.6666 18.3335V9.36358C16.6666 8.86241 16.6666 8.61183 16.5214 8.41041C16.3762 8.20904 16.1147 8.09697 15.5918 7.87284L10.8333 5.8335" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.25 7.5H7.91667M6.25 10.8333H7.91667M6.25 14.1667H7.91667" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_40000001_33876">
                                    <rect width="20" height="20" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                    {{ __('Amenity') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.withdraw-method.index', 'landlord.withdraw.index') ? 'active' : '' }} ">
                <a href="#">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.99967 9.16648C9.07917 9.16648 8.33301 9.72557 8.33301 10.4152C8.33301 11.1049 9.07917 11.664 9.99967 11.664C10.9202 11.664 11.6663 12.2232 11.6663 12.9128C11.6663 13.6024 10.9202 14.1616 9.99967 14.1616M9.99967 9.16648C10.7253 9.16648 11.3427 9.51398 11.5715 9.99898M9.99967 9.16648V8.33398M9.99967 14.1616C9.27401 14.1616 8.65667 13.8141 8.42784 13.3291M9.99967 14.1616L10.002 15.0007" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M17.5003 9.16667C17.631 9.10075 17.7446 9.01767 17.8455 8.91292C18.3337 8.40625 18.3337 7.59077 18.3337 5.9598C18.3337 4.32883 18.3337 3.51335 17.8455 3.00668C17.3573 2.5 16.5717 2.5 15.0003 2.5H5.00033C3.42898 2.5 2.6433 2.5 2.15515 3.00668C1.66699 3.51335 1.66699 4.32883 1.66699 5.9598C1.66699 7.59077 1.66699 8.40625 2.15515 8.91292C2.25606 9.01767 2.36968 9.10075 2.50033 9.16667" stroke="#666666" stroke-width="1.25" stroke-linecap="round"/>
                            <path d="M10.0003 17.5007C13.222 17.5007 15.8337 14.889 15.8337 11.6673C15.8337 8.44566 13.222 5.83398 10.0003 5.83398C6.77866 5.83398 4.16699 8.44566 4.16699 11.6673C4.16699 14.889 6.77866 17.5007 10.0003 17.5007Z" stroke="#666666" stroke-width="1.25"/>
                            <path d="M4.16699 5.83398H15.8337" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Withdraw') }}<i class="fal fa-angle-right"></i></a>
                </a>
                <ul class="">
                    <li><a class="{{ Request::routeIs('landlord.withdraw-method.index') ? 'active' : '' }}"
                            href="{{ route('landlord.withdraw-method.index') }}">{{ __('Withdraw Request') }}</a>
                    </li>
                    <li><a class="{{ Request::routeIs('landlord.withdraw.index') ? 'active' : '' }}"
                            href="{{ route('landlord.withdraw.index') }}">{{ __('Withdraw History') }} </a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.supports.index') ? 'active' : '' }} ">
                <a href="{{ route('landlord.supports.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.08203 12.0837H12.9154M7.08203 7.91699H9.9987" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.8101 17.4084C15.296 17.1768 18.0726 14.3607 18.3011 10.8254C18.3458 10.1336 18.3458 9.41709 18.3011 8.72526C18.0726 5.18999 15.296 2.37395 11.8101 2.14223C10.6209 2.06318 9.3793 2.06335 8.19247 2.14223C4.70662 2.37395 1.92998 5.18999 1.7015 8.72526C1.65679 9.41709 1.65679 10.1336 1.7015 10.8254C1.78472 12.113 2.35416 13.3052 3.02456 14.3118C3.41381 15.0166 3.15692 15.8962 2.75147 16.6645C2.45914 17.2185 2.31297 17.4955 2.43033 17.6956C2.5477 17.8957 2.80985 17.9021 3.33416 17.9148C4.37102 17.9401 5.0702 17.6461 5.6252 17.2368C5.93997 17.0048 6.09736 16.8887 6.20583 16.8753C6.3143 16.862 6.52778 16.9499 6.95464 17.1258C7.3383 17.2838 7.78377 17.3813 8.19247 17.4084C9.3793 17.4873 10.6209 17.4875 11.8101 17.4084Z" stroke="#666666" stroke-width="1.25" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Contact Us') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>

            <li class="dropdown {{ Request::routeIs('landlord.notifications.index') ? 'active' : '' }} ">
                <a href="{{ route('landlord.notifications.index') }}">
                    <span class="icons">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.10794 11.9953C1.93073 13.1229 2.72301 13.9055 3.69305 14.2955C7.41202 15.7908 12.5873 15.7908 16.3063 14.2955C17.2763 13.9055 18.0686 13.1229 17.8914 11.9953C17.7825 11.3024 17.244 10.7254 16.845 10.162C16.3224 9.41491 16.2705 8.60008 16.2704 7.73317C16.2704 4.38291 13.4629 1.66699 9.99968 1.66699C6.53645 1.66699 3.72895 4.38291 3.72895 7.73317C3.72887 8.60008 3.67694 9.41491 3.15435 10.162C2.75538 10.7254 2.21685 11.3024 2.10794 11.9953Z" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.5 17.5C8.16344 18.0183 9.03958 18.3333 10 18.3333C10.9604 18.3333 11.8366 18.0183 12.5 17.5" stroke="#666666" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                    </span>
                    {{ __('Notifications') }} <i class="fal fa-angle-right"></i></a>
                </a>
            </li>

            <li>
                <div class="sub-plan">
                    <img src="{{ asset('backend/assets/web/images/plan-icon.svg') }}">
                </div>
            </li>

            <li>
                <div class="lg-sub-plan">
                    <div id="sidebar_plan" class=" sidebar-free-plan d-flex align-items-center justify-content-between p-3 flex-column">
                        <div class="text-center">
                            @if (plan_data() ?? false)
                                <h3>
                                    {{ plan_data()['plan_name'] ?? '' }}
                                </h3>
                                <h5>
                                    {{ __('Expired') }}: {{ formatted_date(plan_data()['will_expire'] ?? '') }}
                                </h5>
                            @else
                                <h3>{{ __('No Active Plan') }}</h3>
                                <h5>{{ __('Please subscribe to a plan') }}</h5>
                            @endif

                        </div>
                        <a href="{{ route('landlord.subscriptions.index') }}" class=" upgrate-btn fw-bold">{{ __('Upgrade Now') }}</a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</aside>
