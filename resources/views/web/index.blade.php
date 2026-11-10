@extends('layouts.web.master')

@section('title')
    {{ __('Home') }}
@endsection

@section('main_content')
    <section class="home-banner-section">
        <div class="container">
             {{-- hero-container --}}
            <div class="row align-items-center py-5 ">
                <div class="col-lg-6 order-2 order-lg-1 mt-lg-0 hero-content">
                    <div data-aos="fade-right" class="banner-content">
                        <h1 data-aos="fade-up" data-aos-delay="100">
                            {{ $page_data['headings']['slider_title'] ?? '' }}
                            <span
                                data-aos="fade-up"
                                data-aos-delay="300"
                                data-typer-targets='{"targets": [
                                    @foreach ($page_data['headings']['slider_shop_text'] ?? [] as $key => $shop)
                                        "{{ $shop }}"@if (!$loop->last),@endif
                                    @endforeach
                                ]}'>
                            </span>
                        </h1>

                        <p data-aos="fade-up" data-aos-delay="500">
                            {{ $page_data['headings']['slider_description'] ?? '' }}
                        </p>

                        <div class="demo-btn-group mb-3" data-aos="fade-up" data-aos-delay="700">
                            <a href="{{ url($page_data['headings']['slider_btn1_link'] ?? '') }}"
                            class="try-demo-button custom-primary-btn">
                                {{ $page_data['headings']['slider_btn1'] ?? '' }}
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 9.99996C12.5 10.4602 12.1269 10.8333 11.6667 10.8333H3.33333C2.87308 10.8333 2.5 10.4602 2.5 9.99996C2.5 9.53971 2.87308 9.16663 3.33333 9.16663H11.6667C12.1269 9.16663 12.5 9.53971 12.5 9.99996Z" fill="#fff"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2734 5.93173C11.0024 6.07674 10.8333 6.35913 10.8333 6.66647V13.3332C10.8333 13.6404 11.0024 13.9228 11.2733 14.0678C11.5443 14.2128 11.8731 14.197 12.1288 14.0265L17.1288 10.6935C17.3607 10.539 17.4999 10.2787 17.4999 10.0002C17.5 9.7215 17.3607 9.46133 17.1289 9.30675L12.1289 5.97311C11.8732 5.80262 11.5444 5.78672 11.2734 5.93173Z" fill="#fff"/>
                                </svg>
                            </a>

                            <a class="d-flex align-items-center justify-content-center gap-2"  data-bs-toggle="modal" data-bs-target="#watch-video-modal"  href="#">
                                <div  class="video-button">
                                <span class="pulse-ring"></span>
                                <span class="pulse-ring"></span>
                                <span class="pulse-ring"></span>

                                <span class="play-icon">
                                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.58203 4.58167C4.58203 3.7905 5.45728 3.31266 6.12279 3.74049L16.1069 10.1588C16.7192 10.5525 16.7192 11.4475 16.1069 11.8412L6.12279 18.2595C5.45728 18.6873 4.58203 18.2095 4.58203 17.4183V4.58167Z" fill="white"/>
                                    </svg>
                                </span>

                                </div>
                                <p class="hero-watch m-0">{{ $page_data['headings']['slider_btn2'] ?? '' }}</p>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex gap-5">
                        <div class="total-client">
                            <h5 class="text-white">{{ $page_data['headings']['total_customers'] ?? '' }}</h5>
                            <p class="text-white">{{ $page_data['headings']['total_customers_title'] ?? '' }}</p>
                        </div>
                        <div class="total-client">
                            <h5 class="text-white">{{ $page_data['headings']['customers_rating'] ?? 0 }}/{{ $page_data['headings']['customers_rating_out_of'] ?? 0 }}</h5>
                            <p class="text-white">
                                <span>
                                    @for ($i = 0; $i < ($page_data['headings']['customers_rating_out_of'] ?? 0); $i++)
                                        @if((floor($page_data['headings']['customers_rating'] ?? 0)) > $i)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-secondary"></i>
                                        @endif
                                    @endfor
                                </span>
                                {{ $page_data['headings']['customers_rating_title'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 p-0 ">
                    <div class=" text-center">
                        <div data-aos="fade-up" data-aos-delay="100" class="">
                            <img class="hero-img" src="{{ asset($page_data['slider_image'] ?? 'backend/assets/img/no_image.jpg') }}"
                            alt="banner-img" class="" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Video Modal --}}
    <div class="modal modal-custom-design" id="watch-video-modal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe width="100%" height="400px" src="{{ $page_data['headings']['slider_btn2_link'] ?? '' }}"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Clients Section Start --}}
    <section class="customer-section">
        <div class=" mb-4">
            <div class="section-title text-center">
                <p>
                    {{ $page_data['headings']['client_start_short_title'] ?? '' }} {{ $page_data['headings']['total_clients'] ?? '' }} {{ $page_data['headings']['client_end_short_title'] ?? '' }}
                    @for ($i = 0; $i < ($page_data['headings']['clinet_rating_out_of'] ?? 0); $i++)
                        @if((floor($page_data['headings']['clinet_rating'] ?? 0)) > $i)
                            <i class="fas fa-star text-warning"></i>
                        @else
                            <i class="far fa-star text-muted"></i>
                        @endif
                    @endfor
                </p>
                <h2 data-aos="fade-up" data-aos-delay="100">{{ $page_data['headings']['client_start_title'] ?? '' }} <span class="highlight-title">{{ $page_data['headings']['client_end_title'] ?? '' }}</span></h2>
            </div>

            <div>
                <div class="fast-marquee">
                    <div class="marquee-track">
                        @foreach ($clients as $client)
                            <div class="customer-card shadow-none m-2 p-2" data-aos="zoom-in" data-aos-delay="500">
                                <div class="d-flex align-items-center gap-3">
                                    <img class="marquee-img" src="{{ asset($client->image) }}" alt="">
                                    <h5 class="m-0">{{ $client->title }}</h5>
                                </div>
                            </div>
                        @endforeach

                        @foreach ($clients as $client)
                            <div class="customer-card shadow-none m-2 p-2" data-aos="zoom-in" data-aos-delay="500">
                                <div class="d-flex align-items-center gap-3">
                                    <img class="marquee-img" src="{{ asset($client->image) }}" alt="">
                                    <h5 class="m-0">{{ $client->title }}</h5>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Clients Section End --}}

    {{-- Highlight Section Start --}}
    <div class="highlights">
        <div class="container">
            @foreach ($highlights as $index => $highlight)
                <div class="pb-4 {{ $loop->first ? '' : 'pt-4' }}">
                    {{-- highlight-row for even items (image left), highlight-row-2 for odd (image right) --}}
                    <div class="{{ $index % 2 == 0 ? 'highlight-row' : 'highlight-row-2' }} rounded-16 align-items-center">

                        {{-- image left for even index --}}
                        @if ($index % 2 == 0)
                            <div class="text-center blog-image mb-3">
                                <img src="{{ asset($highlight->image) }}" alt="highlight-image" class="highlight-blog-img"/>
                            </div>
                        @endif

                        {{-- content section --}}
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <p class="highlight-short-title mb-0 fw-bold">{{ $highlight->short_title }}</p>
                            </div>
                            <h6 class="highlight-section-title">{{ $highlight->title }}</h6>
                            <p class="highlight-pera">
                                {!! Str::words(strip_tags($highlight->description), 35, '...') !!}
                            </p>

                            <a href="{{ route('highlights.show', $highlight->slug) }}" class="custom-btn custom-primary-btn">
                                {{ $page_data['headings']['highlight_btn_txt'] ?? 'View Details' }}
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M12.5 9.99996C12.5 10.4602 12.1269 10.8333 11.6667 10.8333H3.33333C2.87308 10.8333 2.5 10.4602 2.5 9.99996C2.5 9.53971 2.87308 9.16663 3.33333 9.16663H11.6667C12.1269 9.16663 12.5 9.53971 12.5 9.99996Z"
                                        fill="#fff" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M11.2734 5.93173C11.0024 6.07674 10.8333 6.35913 10.8333 6.66647V13.3332C10.8333 13.6404 11.0024 13.9228 11.2733 14.0678C11.5443 14.2128 11.8731 14.197 12.1288 14.0265L17.1288 10.6935C17.3607 10.539 17.4999 10.2787 17.4999 10.0002C17.5 9.7215 17.3607 9.46133 17.1289 9.30675L12.1289 5.97311C11.8732 5.80262 11.5444 5.78672 11.2734 5.93173Z"
                                        fill="#fff" />
                                </svg>
                            </a>
                        </div>

                        {{-- image right for odd index --}}
                        @if ($index % 2 == 1)
                            <div class="text-center blog-image mb-3">
                                <img src="{{ asset($highlight->image) }}" alt="highlight-image" class="highlight-blog-img"/>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {{-- Highlight Section End --}}

    {{-- Software Manage Start --}}
    <section class="manage-software-section-container">
        <div class="container">
            <div data-aos="fade-up" class="section-title manage-sofware-title text-center">
                <h2>{{ $page_data['headings']['soft_mng_start_title'] ?? '' }} <span class="highlight-title">{{ $page_data['headings']['soft_mng_mid_title'] ?? '' }}</span> {{ $page_data['headings']['soft_mng_eng_title'] ?? '' }}</h2>
            </div>
            <div class="manage-software-section mb-3">
                <div class="align-self-center">
                    <div>
                        <div class="d-flex align-items-center mb-md-3 content cursor-pointer subtitle-one-icon">
                            <img src="{{ asset($page_data['soft_mng_subtitle_one_icon'] ?? 'backend/assets/img/no_image.jpg') }}" alt="image" class="table-img me-3">
                            <div>
                                <h6 class="mb-1">{{ $page_data['headings']['soft_mng_subtitle_one'] ?? '' }}</h6>
                                <p class="mb-0">{{ $page_data['headings']['soft_mng_desc_one'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-md-3 content cursor-pointer subtitle-two-icon">
                            <img src="{{ asset($page_data['soft_mng_subtitle_two_icon'] ?? 'backend/assets/img/no_image.jpg') }}" alt="image" class="table-img me-3">
                            <div>
                                <h6 class="mb-1">{{ $page_data['headings']['soft_mng_subtitle_two'] ?? '' }}</h6>
                                <p class="mb-0">{{ $page_data['headings']['soft_mng_desc_two'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center content cursor-pointer subtitle-three-icon">
                            <img src="{{ asset($page_data['soft_mng_subtitle_three_icon'] ?? 'backend/assets/img/no_image.jpg') }}" alt="image" class="table-img me-3">
                            <div>
                                <h6 class="mb-1">{{ $page_data['headings']['soft_mng_subtitle_three'] ?? '' }}</h6>
                                <p class="mb-0">{{ $page_data['headings']['soft_mng_desc_three'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="align-self-center subtitle-one-img">
                    <div class="">
                        <img class="manage-software-img" src="{{ asset($page_data['soft_mng_subtitle_one_img'] ?? 'backend/assets/img/no_image.jpg') }}"
                        alt="image" class="rounded-2 " />
                    </div>
                </div>
                <div class="align-self-center subtitle-two-img">
                    <div class="">
                        <img class="manage-software-img" src="{{ asset($page_data['soft_mng_subtitle_two_img'] ?? 'backend/assets/img/no_image.jpg') }}"
                        alt="image" class="rounded-2 " />
                    </div>
                </div>
                <div class="align-self-center subtitle-three-img">
                    <div class="">
                        <img class="manage-software-img" src="{{ asset($page_data['soft_mng_subtitle_three_img'] ?? 'backend/assets/img/no_image.jpg') }}"
                        alt="image" class="rounded-2 " />
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Software Manage End --}}

    {{-- Feature Code Start --}}
    @include('web.components.feature')
    {{-- Feature Code End --}}

    {{-- Pricing-Plan-section Code Start --}}
    @include('web.components.plan')
    {{-- Pricing-Plan-section Code End --}}

    {{-- Testimonial Section Start --}}
    <section class="client-section-container">
        <div class="container">
            <div class="row">
                <div class="client-testimonial-left col-lg-4 pt-3">
                    <h2>{{ $page_data['headings']['testimonial_start_title'] ?? '' }} <span>{{ $page_data['headings']['testimonial_mid_title'] ?? '' }}</span><br>{{ $page_data['headings']['testimonial_end_title'] ?? '' }}<br>{{ $page_data['headings']['testimonial_final_title'] ?? '' }}</h2>

                    <div class="client-testimonial-nav-buttons">
                        <button class="client-testimonial-prev"><i class="fa-solid fa-arrow-left"></i></button>
                        <button class="client-testimonial-next"><i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="client-testimonial-right col-lg-8">
                    <div class="swiper client-testimonial-swiper">
                        <div class="swiper-wrapper">
                            @foreach ($testimonials as $testimonial)
                                <div class="swiper-slide client-testimonial-slide">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="client-testimonial-quote"><i class="fa-solid fa-quote-left"></i></div>
                                        <div class="client-testimonial-stars">
                                            @for ($i = 0; $i < 5; $i++)
                                                @if($testimonial->star > $i)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <p>{{ $testimonial->text }}</p>
                                    <div class="client-testimonial-client">
                                        <img src="{{ asset($testimonial->client_image) }}" alt="Sarah">
                                        <div>
                                            <h5>{{ $testimonial->client_name }}</h5>
                                            <small>{{ $testimonial->work_at }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Testimonial Section End --}}

    {{-- Faq Secton Start --}}
    <div class="faq-main-container" id="faq">
        <div class="container">
            <div class="section-title text-center">
                <h2 data-aos="fade-up" data-aos-delay="100"> {{ $page_data['headings']['faq_title_one'] ?? '' }} <span class="highlight-title">{{ $page_data['headings']['faq_title_two'] ?? '' }}</span> </h2>
            </div>
            <div class="faq-section-container">
                <div class="">
                    <div class="faq-get-in-content">
                        <img src="{{ asset('backend/assets/web/images/icons/WechatLogo.png') }}" alt="" srcset="">
                        <h4>{{ $page_data['headings']['faq_title_three'] ?? '' }}</h4>
                        <p>{{ $page_data['headings']['faq_description'] ?? '' }}</p>
                        <a href="{{ url($page_data['headings']['faq_btn_link'] ?? '') }}" class="get-in-touch-btn">
                            <i class="fa-solid fa-phone"></i> {{ $page_data['headings']['faq_btn_text'] ?? 'Get in Touch' }}
                        </a>
                    </div>
                </div>
                <div class="">
                    <div class="custom-accordion">
                        @foreach ($faqs as $index => $faq)
                            <div class="custom-accordion-item {{ $index == 0 ? 'active' : '' }}">
                                <div class="custom-accordion-header">
                                    {{ $faq->question }}
                                    <span class="custom-accordion-icon"></span>
                                </div>
                                <div class="custom-accordion-content">
                                    {{ $faq->answer }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Faq Secton End --}}

    {{-- Blogs Section Code Start --}}
    <section class="blogs-section ">
        <div class="container">
            <div class="section-title d-flex align-items-center justify-content-between flex-wrap">
                <h2 data-aos="fade-up" data-aos-delay="100">{{ $page_data['headings']['blog_start_title'] ?? '' }} <span class="highlight-title">{{ $page_data['headings']['blog_end_title'] ?? '' }}</span> </h2>
                <a data-aos="fade-up" data-aos-delay="300" href="{{ url($page_data['headings']['blog_view_all_btn_link'] ?? '') }}"
                    class="custom-btn custom-primary-btn">
                    {{ $page_data['headings']['blog_view_all_btn_text'] ?? '' }}
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5 9.99996C12.5 10.4602 12.1269 10.8333 11.6667 10.8333H3.33333C2.87308 10.8333 2.5 10.4602 2.5 9.99996C2.5 9.53971 2.87308 9.16663 3.33333 9.16663H11.6667C12.1269 9.16663 12.5 9.53971 12.5 9.99996Z" fill="white"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2734 5.93173C11.0024 6.07674 10.8333 6.35913 10.8333 6.66647V13.3332C10.8333 13.6404 11.0024 13.9228 11.2733 14.0678C11.5443 14.2128 11.8731 14.197 12.1288 14.0265L17.1288 10.6935C17.3607 10.539 17.4999 10.2787 17.4999 10.0002C17.5 9.7215 17.3607 9.46133 17.1289 9.30675L12.1289 5.97311C11.8732 5.80262 11.5444 5.78672 11.2734 5.93173Z" fill="white"/>
                    </svg>
                </a>
            </div>
        </div>
        @include('web.components.blog')
    </section>
    {{-- Blogs Section Code End --}}

    @include('web.components.signup')

@endsection

