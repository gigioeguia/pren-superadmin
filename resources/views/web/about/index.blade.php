@extends('layouts.web.master')

@section('title')
    {{ __('About Us') }}
@endsection

@section('main_content')
    <section class="banner-bg p-4">
        <div class="container">
            <p class="mb-0 fw-bolder custom-clr-dark">
                {{ __('Home') }} <span class="font-monospace">></span> {{ __('About Us') }}
            </p>
        </div>
    </section>

    {{-- About Code Start --}}

    <section class="about-section">
        <div class="container">
            <div class="row mb-3">
                <div class="col-lg-6 align-self-center">
                    <div class="w-90 position-relative ms-auto">
                        <img src="{{ asset($page_data['about_image'] ?? 'backend/assets/img/no_image.jpg') }}"
                        alt="image" class="about-img" />
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div>
                        <h2 class="mb-3">
                            <span>{{ $page_data['headings']['about_us_title'] ?? '' }}</span>
                        </h2>
                        <p>
                            {!! $page_data['headings']['about_desc'] ?? '' !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature section Start Here --}}
    @include('web.components.feature')

    {{-- Pricing Plan --}}
    @include('web.components.plan')
    @include('web.components.signup')

@endsection
