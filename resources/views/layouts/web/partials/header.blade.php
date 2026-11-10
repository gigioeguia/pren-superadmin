<header class="header-section home-header">
    <nav class="navbar navbar-expand-lg p-0">
        <div class="container">
            <button class="navbar-toggler p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
            <a href="{{ route('home') }}" class="header-logo">
                <img src="{{ asset($page_data['web_logo_one'] ?? 'backend/assets/img/logo.svg') }}" alt="header-logo" />
            </a>

            <a href="{{ Route::has($page_data['headings']['header_btn_link']) ? route($page_data['headings']['header_btn_link']) : route('login') }}"  class="get-app-btn login-btn ">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M5.48131 12.9012C4.30234 13.6032 1.21114 15.0366 3.09389 16.8304C4.01359 17.7065 5.03791 18.3332 6.32573 18.3332H13.6743C14.9621 18.3332 15.9864 17.7065 16.9061 16.8304C18.7888 15.0366 15.6977 13.6032 14.5187 12.9012C11.754 11.2549 8.24599 11.2549 5.48131 12.9012Z"
                        fill="#fff" />
                    <path
                        d="M13.75 5.4165C13.75 7.48757 12.0711 9.1665 10 9.1665C7.92893 9.1665 6.25 7.48757 6.25 5.4165C6.25 3.34544 7.92893 1.6665 10 1.6665C12.0711 1.6665 13.75 3.34544 13.75 5.4165Z"
                        fill="#fff" />
                </svg>
                {{ $page_data['headings']['header_btn_text'] ?? 'Login' }}
            </a>

            <!-- Mobile Menu -->
            <div class="offcanvas offcanvas-start mobile-menu" data-bs-backdrop="static" tabindex="-1"
                id="staticBackdrop" aria-labelledby="staticBackdropLabel">
                <div class="offcanvas-header home-offcanvas-header">
                    <a href="{{ route('home') }}" class="header-logo"><img
                            src="{{ asset($page_data['web_logo_one'] ?? 'backend/assets/img/logo.svg') }}"
                            alt="header-logo" /></a>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
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
                            <a href="#feature" class="accordion-button without-sub-menu"
                                type="button">{{ __('Feature') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('plan.index') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('Pricing') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="#faq" class="accordion-button without-sub-menu"
                                type="button">{{ __('FAQ') }}</a>
                        </div>
                        <div class="accordion-item">
                            <a href="{{ route('contact.index') }}" class="accordion-button without-sub-menu"
                                type="button">{{ __('Contact Us') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Desktop Menu -->
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link"
                            aria-current="page">{{ __('Home') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('about.index') }}">{{ __('About Us') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="#feature">{{ __('Feature') }}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('plan.index') }}">{{ __('Pricing') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="#faq">{{ __('FAQ') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('contact.index') }}">{{ __('Contact Us') }}</a>
                    </li>
                </ul>

                <a href="{{ Route::has($page_data['headings']['header_btn_link']) ? route($page_data['headings']['header_btn_link']) : route('login') }}"
                    class="get-app-btn">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.48131 12.9012C4.30234 13.6032 1.21114 15.0366 3.09389 16.8304C4.01359 17.7065 5.03791 18.3332 6.32573 18.3332H13.6743C14.9621 18.3332 15.9864 17.7065 16.9061 16.8304C18.7888 15.0366 15.6977 13.6032 14.5187 12.9012C11.754 11.2549 8.24599 11.2549 5.48131 12.9012Z"
                            fill="#fff" />
                        <path
                            d="M13.75 5.4165C13.75 7.48757 12.0711 9.1665 10 9.1665C7.92893 9.1665 6.25 7.48757 6.25 5.4165C6.25 3.34544 7.92893 1.6665 10 1.6665C12.0711 1.6665 13.75 3.34544 13.75 5.4165Z"
                            fill="#fff" />
                    </svg>

                    {{ $page_data['headings']['header_btn_text'] ?? 'Login' }}

                </a>
            </div>
        </div>
    </nav>
</header>
