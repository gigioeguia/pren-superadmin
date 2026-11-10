@extends('layouts.web.master')

@section('title')
    {{ __('Term And Condition') }}
@endsection

@section('main_content')
    <section class="banner-bg p-4">
        <div class="container">
        <p class="mb-0 fw-bolder custom-clr-dark">
            {{ __('Home') }} <span class="font-monospace">></span> {{ __('Terms And Conditions') }}
        </p>
        </div>
    </section>

    <section class="terms-policy-section">
        <div class="container">
        <h2 class="mb-3">{{ $term_condition->value['term_title'] ?? ''}}</h2>
        <div>
            <div>
                <p>{!! $term_condition->value['description'] ?? '' !!}</p>
            </div>
        </div>
        </div>
    </section>

@endsection
