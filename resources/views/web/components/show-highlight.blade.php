@extends('layouts.web.master')

@section('title')
    {{ __('Highlight') }}
@endsection

@section('main_content')
    <section class="banner-bg p-4">
        <div class="container">
            <p class="mb-0 fw-bolder custom-clr-dark">
                {{ __('Home') }} <span class="font-monospace">></span> {{ $highlight->short_title }}
            </p>
        </div>
    </section>

    {{-- About Code Start --}}

    <section class="about-section">
        <div class="container">
            <div class="row mb-3 align-items-start">
                <div class="col-lg-6">
                    <div>
                        <h2 class="mb-3">
                            <span>{{ $highlight->title }}</span>
                        </h2>
                        <p>
                            {!! $highlight->description !!}
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 align-self-start">
                    <div class="w-90 position-relative ms-auto">
                        <img src="{{ asset($highlight->image ?? 'backend/assets/img/no_image.jpg') }}"
                        alt="image" class="about-img" />
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
