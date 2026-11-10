@extends('layouts.web.master')

@section('title')
    {{ __('Blog') }}
@endsection

@section('main_content')
    <section class="banner-bg p-4">
        <div class="container">
            <p class="mb-0 fw-bolder custom-clr-dark">
                {{ __('Home') }} <span class="font-monospace">></span> {{ __('Blog List') }}
            </p>
        </div>
    </section>

<section class="blog-section p-0">
    <div class="container">
        <div class="row">
            <div class="col-xl-8">
                <div class="row" id="blogs-container">
                    @foreach ($blogs as $blog)
                        <div class="col-lg-6 pb-4">
                            <div class="blog-shadow rounded-16">
                                <div class="text-center blog-image mb-3">
                                    <img src="{{ asset($blog->image) }}" alt="product-image"
                                        class="landing-blog-img" />
                                </div>
                                <div class="p-3 pt-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="assets/web/images/icons/clock.svg" alt="" />
                                        <p class="ms-1 mb-0">{{ formatted_date($blog->updated_at) }}</p>
                                    </div>
                                    <h6 class="h6-line-clamp">{{ $blog->title }}</h6>
                                    <p>
                                        {!! Str::limit(strip_tags($blog->descriptions), 120) !!}
                                        @if (strpos($blog->descriptions, '<img') !== false)
                                            <span class="text-muted"></span>
                                        @endif
                                    </p>

                                    <a href="{{ route('blogs.show', $blog->slug) }}"
                                        class="custom-clr-primary">{{ $page_data['headings']['blog_btn_text'] ?? '' }}
                                        <span class="font-monospace">></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="blogs-pagination">
                    {{ $blogs->links() }}
                </div>
            </div>
            <div class="col-xl-4">
                @foreach ($recent_blogs as $blog)
                    <div class="blog-shadow rounded-16 mb-4">
                        <div class="d-flex align-items-center">

                                <img src="{{ asset($blog->image) }}"
                                    class="blog-sm-img" alt="..." />

                            <div class="mx-3">
                                <div class="d-flex align-items-center">
                                    <img src="assets/web/images/icons/clock.svg" alt="" />
                                    <p class="ms-1 mb-0">{{ formatted_date($blog->updated_at) }}</p>
                                </div>
                                <p class="p-2nd-line-clamp mb-1">
                                    <strong>{{ $blog->title }}</strong>
                                </p>
                                <a href="{{ route('blogs.show', $blog->slug) }}"
                                    class="custom-clr-primary">{{ $page_data['headings']['blog_btn_text'] ?? '' }}<span
                                        class="font-monospace">></span></a>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if (Request::routeIs('blogs.index'))
                    <h6>{{ __('Tags') }}</h6>
                    <div class="tags-btns mb-5">
                        @foreach ($blogs as $blog)
                            @foreach ($blog['tags'] ?? [] as $tag)
                                <a href="javascript:void(0);"
                                    class="btn btn-secondary ps-custom-btn mb-1 tags-btn blogs-tag-btn-selected"
                                    data-tag="{{ $tag }}" data-route="{{ route('blogs.tag.filter') }}">
                                    {{ $tag }}
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>


@endsection
