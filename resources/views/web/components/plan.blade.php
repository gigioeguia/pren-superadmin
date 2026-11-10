<section class="pricing-plan-section plans-list pb-0">
    <div class="container">
        <div class="section-title text-center">
            <h2 data-aos="fade-up" data-aos-delay="100">{{ $page_data['headings']['pricing_start_title'] ?? '' }} <span class="highlight-title">{{ $page_data['headings']['pricing_mid_title'] ?? '' }}</span> {{ $page_data['headings']['pricing_end_title'] ?? '' }}</h2>
            <p data-aos="fade-up" data-aos-delay="300" class="section-description">
                {{ $page_data['headings']['pricing_description'] ?? '' }}
            </p>
            <div class="d-flex align-items-center justify-content-center gap-4">
                <div class="w-100 d-flex flex-column align-items-center">

                    <div class="tab-content w-100">
                        <div class="tab-pane fade show active" id="nav-monthly" role="tabpanel"
                            aria-labelledby="nav-monthly-tab">
                            <div class="plans-card position-relative">
                                @foreach ($plans as $plan)
                                    <div class=" mt-3">
                                        <div class="card">
                                            <div class=" position-relative">
                                             @php
                                                $planType = strtolower($plan['subscriptionName'] ?? '');
                                                $ribbonClass = match ($planType) {
                                                    'free' => 'ribbon-free',
                                                    'economy' => 'ribbon-economy',
                                                    'standard' => 'ribbon-standard',
                                                    default => 'ribbon-standard', // fallback
                                                };
                                            @endphp

                                            <div class="ribbon-tag {{ $ribbonClass }}">
                                                {{ $plan['subscriptionName'] ?? '' }}
                                            </div>
                                        </div>
                                        
                                            <div class="card-header pricing-top-card border-0 font-size-update">
                                                <div class="">
                                                  
                                                    <h4>
                                                        @if (($plan['offerPrice'] && $plan['subscriptionPrice'] !== null) || $plan['offerPrice'] || $plan['subscriptionPrice'])
                                                            @if ($plan['offerPrice'])
                                                                {{ currency_format($plan['offerPrice']) }}
                                                            @else
                                                                {{ currency_format($plan['subscriptionPrice']) }}
                                                            @endif
                                                        @else
                                                            @if ($plan['offerPrice'] || $plan['subscriptionPrice'])
                                                                {{ currency_format($plan['offerPrice'] ?? $plan['subscriptionPrice']) }}
                                                            @else
                                                                {{ __('Free') }}
                                                            @endif
                                                        @endif
                                                        <span
                                                            class="price-span">/{{ $plan['duration'] }} {{ ucfirst($plan['duration_type']) }}</span></small>
                                                    </h4>
                                                </div>
                                            </div>

                                            <div class="card-body px-4 text-start">
                                                <ul>
                                                    @foreach ($plan['features'] ?? [] as $key => $item)
                                                        <li class="d-flex align-items-center gap-2">
                                                            <div class="pb-2">{!! ($item['status'] ?? 0) == '1' ? '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.4482 1.227C10.2008 0.979662 9.79982 0.979679 9.55249 1.227L7.24041 3.53909H4.17291C3.82313 3.53909 3.53958 3.82264 3.53958 4.17242V7.23992L1.22749 9.552C0.980151 9.79934 0.980167 10.2003 1.22749 10.4477L3.53958 12.7598V15.8273C3.53958 16.177 3.82311 16.4606 4.17291 16.4606H7.2404L9.55249 18.7727C9.79982 19.02 10.2008 19.02 10.4482 18.7727L12.7602 16.4606H15.8277C16.1775 16.4606 16.4611 16.177 16.4611 15.8273V12.7598L18.7732 10.4477C19.0205 10.2003 19.0205 9.79934 18.7732 9.552L16.4611 7.23992V4.17242C16.4611 3.82262 16.1775 3.53909 15.8277 3.53909H12.7602L10.4482 1.227ZM12.8219 8.45217L13.3578 8.13062L12.7147 7.05875L12.1788 7.38032C11.0235 8.0735 10.0876 9.20159 9.45566 10.1119C9.22266 10.4476 9.02574 10.7615 8.86924 11.0253C8.69724 10.8637 8.53241 10.7248 8.38808 10.6105C8.23432 10.489 7.94308 10.2902 7.83492 10.2163L7.82473 10.2094L7.29048 9.885L6.64177 10.9535L7.17503 11.2773C7.2533 11.3308 7.47988 11.486 7.61268 11.591C7.88058 11.8028 8.21757 12.1049 8.51233 12.4733L9.13033 13.2458L9.56274 12.3557C9.60258 12.2791 9.71966 12.0547 9.79858 11.9148C9.95666 11.6348 10.1886 11.2482 10.4826 10.8248C11.0798 9.96425 11.8939 9.009 12.8219 8.45217Z" fill="#00932C"/>
                                                                </svg>' : '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M18.3337 9.99935C18.3337 5.39697 14.6027 1.66602 10.0003 1.66602C5.39795 1.66602 1.66699 5.39697 1.66699 9.99935C1.66699 14.6017 5.39795 18.3327 10.0003 18.3327C14.6027 18.3327 18.3337 14.6017 18.3337 9.99935Z" fill="#FF3B30"/>
                                                                <path d="M12.4995 12.5L7.5 7.5M7.50053 12.5L12.5 7.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                '!!}</div>
                                                            {{ $item['name'] ?? '' }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <a class="btn subscribe-plan d-block mt-4 mb-2"
                                                    data-plan-id="{{ $plan->id }}"
                                                    data-plan-name="{{ $plan->subscriptionName }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registration-modal">
                                                    {{ __('Buy Now') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
