<section class="blog-section p-0">
    <div class="container ">
        <div class="row">
            <div class="col-xl-8">
          <div class="row" id="blogs-container">
            @foreach ($blogs as $index => $blog)
                <div class="col-lg-6 pb-4" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                    <div class="blog-shadow rounded-16">
                        <div class="text-center blog-image mb-3">
                            <img src="{{ asset($blog->image) }}" alt="product-image" class="landing-blog-img" />
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
                            <a href="{{ route('blogs.show', $blog->slug) }}" class="custom-clr-primary">
                                {{ $page_data['headings']['blog_btn_text'] ?? '' }}
                                <span class="font-monospace">></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

            </div>
                <div class="col-xl-4">
                @foreach ($recent_blogs as $index => $blog)
                    <div class="blog-shadow rounded-16 mb-4" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                        <div class="blog-sm-main-container">
                            <div class="blog-sm-container">
                                <img src="{{ asset($blog->image) }}" class="blog-sm-img" alt="..." />
                            </div>
                            <div class="mx-3">
                                <div class="d-flex align-items-center">
                                    <img src="assets/web/images/icons/clock.svg" alt="" />
                                    <p class="ms-1 mb-0">{{ formatted_date($blog->updated_at) }}</p>
                                </div>
                                <div class="my-2">
                                    <p class="p-2nd-line-clamp mb-1">
                                        <strong>{{ $blog->title }}</strong>
                                    </p>
                                </div>
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="custom-clr-primary">
                                    {{ $page_data['headings']['blog_btn_text'] ?? '' }}
                                    <span class="font-monospace">></span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
