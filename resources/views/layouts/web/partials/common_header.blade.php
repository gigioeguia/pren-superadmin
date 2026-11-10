<header class="header-section">
    <nav class="navbar navbar-expand-lg p-0">
        <div class="container">
            <a href="{{ route('home') }}" class="header-logo">
                <img src="{{ asset($page_data['web_logo_two'] ?? 'backend/assets/img/logo.svg') }}"
                    alt="header-logo" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Mobile Menu -->
            <div href="javascript:void(0);" class="offcanvas offcanvas-start mobile-menu" data-bs-backdrop="static"
                tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel">
                <div class="offcanvas-header">
                    <a href="{{ route('home') }}" class="header-logo"><img
                            src="{{ asset($page_data['web_logo_two'] ?? 'backend/assets/img/logo.svg') }}"
                            alt="header-logo" /></a>
                    <button type="button" class="btn-close btn-close-commmon" data-bs-dismiss="offcanvas"
                        aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="offcanvas-body">
                    <div class="accordion accordion-flush mb-30" id="sidebarMenuAccordion">
                        <div class="accordion-item">
                            <a href="{{ route('home') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('Home') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('about.index') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('About Us') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('home') }}#feature" class="accordion-button without-sub-menu"
                                type="button">{{ __('Feature') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('plan.index') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('Pricing') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('home') }}#faq" class="accordion-button without-sub-menu"
                                type="button">{{ __('FAQ') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('contact.index') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('Contact Us') }}</a>
                        </div>
                    </div>

                       <div class="login-button-container">
                            <a href="{{ Route::has($page_data['headings']['header_btn_link']) ? route($page_data['headings']['header_btn_link']) : route('login') }}" class="login-btn-2">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.04175 5.83333C6.04175 3.64721 7.81396 1.875 10.0001 1.875C12.1862 1.875 13.9584 3.64721 13.9584 5.83333C13.9584 8.01946 12.1862 9.79167 10.0001 9.79167C7.81396 9.79167 6.04175 8.01946 6.04175 5.83333Z" fill="white"/>
                            <path d="M3.54175 15.8334C3.54175 13.187 5.68705 11.0417 8.33341 11.0417H11.6667C14.3131 11.0417 16.4584 13.187 16.4584 15.8334C16.4584 17.099 15.4324 18.125 14.1667 18.125H5.83341C4.56776 18.125 3.54175 17.099 3.54175 15.8334Z" fill="white"/>
                            </svg>
                                {{ $page_data['headings']['header_btn_text'] ?? 'Login' }}
                            </a>
                       </div>
                </div>
            </div>
            <!-- Desktop Menu -->
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link active"
                            aria-current="page">{{ __('Home') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('about.index') }}">{{ __('About Us') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('home') }}#feature">{{ __('Feature') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('plan.index') }}">{{ __('Pricing') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('home') }}#faq">{{ __('FAQ') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('contact.index') }}">{{ __('Contact Us') }}</a>
                    </li>
                </ul>

                <div class="login-button-container">
                    <a href="{{ Route::has($page_data['headings']['header_btn_link']) ? route($page_data['headings']['header_btn_link']) : route('login') }}" class="login-btn-2">
                       <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.04175 5.83333C6.04175 3.64721 7.81396 1.875 10.0001 1.875C12.1862 1.875 13.9584 3.64721 13.9584 5.83333C13.9584 8.01946 12.1862 9.79167 10.0001 9.79167C7.81396 9.79167 6.04175 8.01946 6.04175 5.83333Z" fill="white"/>
                        <path d="M3.54175 15.8334C3.54175 13.187 5.68705 11.0417 8.33341 11.0417H11.6667C14.3131 11.0417 16.4584 13.187 16.4584 15.8334C16.4584 17.099 15.4324 18.125 14.1667 18.125H5.83341C4.56776 18.125 3.54175 17.099 3.54175 15.8334Z" fill="white"/>
                        </svg>
                        {{ $page_data['headings']['header_btn_text'] ?? 'Login' }}
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
