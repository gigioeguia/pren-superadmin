@extends('layouts.admin.master')

@section('title')
    {{ __('CMS Manage') }}
@endsection

@section('main_content')
    <div class="erp-table-section system-settings">
        <div class="container-fluid">
            <div class="table-header p-16">
                <div>
                    <h4 class="text-black p-3">🚩 {{ __('Page for Updating Website Sections') }}</h4>
                </div>
            </div>
            <div class="tab-content p-16">
                <div class="tab-pane fade active show" id="add-new-petty" role="tabpanel">
                    <div class="table-header border-0">
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-sm-12">
                            <div class="order-form-section">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-4 mb-3">
                                        <div class="cards-header shadow p-3">
                                            <div class="card-body">
                                                <ul class="nav nav-pills flex-column w-280">
                                                    <li class="nav-item">
                                                        <a href="#slider" id="home-tab4"
                                                            class="add-report-btn nav-link active"
                                                            data-bs-toggle="tab">{{ __('Slider Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#header" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Header Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#web_logo" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Web Logo') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#customer" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Customer Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#client" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Client Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#highlight" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Highlight Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#software_manage" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Soft. Manage Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#feature" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Feature Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#get_app" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Get App Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#testimonial" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Testimonial Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#blog" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Blog Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#about_us" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('About Us Page') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#contact_us" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Contuct Us Page') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#pricing" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Pricing Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#faq" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Faq Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#footer" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">{{ __('Footer Section') }}</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a href="#socials" class="add-report-btn nav-link"
                                                            data-bs-toggle="tab">
                                                            {{ __('Social Medias') }}
                                                        </a>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-md-8">
                                        <div class="cards-header shadow p-3">
                                            <div class="card-body">
                                                <form
                                                    action="{{ route('admin.website-settings.update', 'manage-pages') }}"
                                                    method="post" class="ajaxform">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="tab-content no-padding">
                                                        {{-- Slider Section Start --}}
                                                        <div class="tab-pane fade show active" id="slider">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>{{ __('Title') }}</label>
                                                                    <input type="text" name="slider_title"
                                                                        value="{{ $page_data['headings']['slider_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Title') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <label>{{ __('Button One') }}</label>
                                                                    <input type="text" name="slider_btn1"
                                                                        value="{{ $page_data['headings']['slider_btn1'] ?? '' }}"
                                                                        placeholder="{{ __('Button Text') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <label>{{ __('Button One Link') }}</label>
                                                                    <input type="text" name="slider_btn1_link"
                                                                        value="{{ $page_data['headings']['slider_btn1_link'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Link') }}" class="form-control">
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <label>{{ __('Button Two') }}</label>
                                                                    <input type="text" name="slider_btn2"
                                                                        value="{{ $page_data['headings']['slider_btn2'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Text') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <label>{{ __('Button Two Link') }}</label>
                                                                    <input type="text" name="slider_btn2_link"
                                                                        value="{{ $page_data['headings']['slider_btn2_link'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Link') }}" class="form-control">
                                                                    <span
                                                                        class="text-danger">{{ __('Note: Enter embedded video link') }}</span>
                                                                </div>

                                                                <div class="col-12">
                                                                    <label>{{ __('Description') }}</label>
                                                                    <textarea name="slider_description" placeholder="{{ __('Enter Description') }}" class="form-control">{{ $page_data['headings']['slider_description'] ?? '' }}</textarea>
                                                                </div>


                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Slider Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="slider_image" src="{{ asset($page_data['slider_image'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="slider_image" class="preview-image-input" data-id="#slider_image" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <button
                                                                        class="w-100 py-3 d-block text-center fw-bold mt-3 admin-collapse bg-light primary border-0"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#slider_shop"
                                                                        aria-expanded="false" aria-controls="slider_shop">
                                                                        {{ __('Shop') }} <i class="fas fa-arrow-circle-down ms-2"></i>
                                                                    </button>

                                                                    <div class="collapse mt-3" id="slider_shop">
                                                                        <div id="shop-wrapper">
                                                                            @php
                                                                                $shops = $page_data['headings']['slider_shop_text'] ?? [];
                                                                            @endphp

                                                                            @if(count($shops) > 0)
                                                                                @foreach($shops as $key => $slider_shop_text)
                                                                                    <div class="mb-3 s-count">
                                                                                        <label class="mb-1">{{ __('Shop') }}- {{ $key+1 }}</label>
                                                                                        <div class="row d-flex">
                                                                                            <div class="col-10">
                                                                                                <input type="text" name="slider_shop_text[]" required
                                                                                                    class="form-control me-2"
                                                                                                    value="{{ $slider_shop_text }}"
                                                                                                    placeholder="{{ __('Enter text') }}">
                                                                                            </div>
                                                                                            <div class="col-2">

                                                                                                @if($key == 0)
                                                                                                    <button type="button" class="btn dynamic-add-btn shop-btn-add">
                                                                                                        <svg class="mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                                            stroke-width="1.8" stroke="currentColor" class="size-6">
                                                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                                                d="M12 4.5v15m7.5-7.5h-15" />
                                                                                                        </svg>
                                                                                                    </button>
                                                                                                @else
                                                                                                    <button type="button" class="btn dynamic-delete-btn shop-btn-remove">
                                                                                                        <svg class="mt-1 text-danger" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                                                            class="size-6">
                                                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                                                        </svg>
                                                                                                    </button>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <!-- Default input if no data -->
                                                                                <div class="mb-3 s-count">
                                                                                    <label class="fw-bold mb-1">{{ __('Shop') }}- 1</label>
                                                                                    <div class="row d-flex">
                                                                                        <div class="col-10">
                                                                                            <input type="text" name="slider_shop_text[]" required
                                                                                            class="form-control me-2"
                                                                                            placeholder="{{ __('Enter text') }}">
                                                                                        </div>
                                                                                        <div class="col-2">
                                                                                            <button type="button" class="btn dynamic-add-btn shop-btn-add">
                                                                                            <svg class="mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                                stroke-width="1.8" stroke="currentColor" class="size-6">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                                                                            </svg>
                                                                                        </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Slider section End --}}

                                                        {{-- Header section start --}}
                                                        <div class="tab-pane fade" id="header">
                                                            <div class="form-group">
                                                                <label>{{ __('Header Button Text') }}</label>
                                                                <input type="text" name="header_btn_text"
                                                                    value="{{ $page_data['headings']['header_btn_text'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>{{ __('Header Button Link') }}</label>
                                                                <input type="text" name="header_btn_link"
                                                                    value="{{ $page_data['headings']['header_btn_link'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Header link') }}" class="form-control">
                                                            </div>
                                                        </div>
                                                        {{-- Header section End --}}

                                                        {{-- Web logo section start --}}
                                                        <div class="tab-pane fade" id="web_logo">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Web Logo One') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="web_logo_one" src="{{ asset($page_data['web_logo_one'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="web_logo_one" class="preview-image-input" data-id="#web_logo_one" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Web Logo Two') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="web_logo_two" src="{{ asset($page_data['web_logo_two'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="web_logo_two" class="preview-image-input" data-id="#web_logo_two" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        {{-- Web logo section End --}}

                                                        {{-- Customer section start --}}
                                                        <div class="tab-pane fade" id="customer">
                                                            <div class="form-group">
                                                                <label>{{ __('Total Customers Title') }}</label>
                                                                <input type="text" name="total_customers_title"
                                                                    value="{{ $page_data['headings']['total_customers_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Total Customer Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Total Customers') }}</label>
                                                                <input type="text" name="total_customers"
                                                                    value="{{ $page_data['headings']['total_customers'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Total Customer') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Rating Title') }}</label>
                                                                <input type="text" name="customers_rating_title"
                                                                    value="{{ $page_data['headings']['customers_rating_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Rating Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Rating') }}</label>
                                                                <input type="number" name="customers_rating"
                                                                    value="{{ $page_data['headings']['customers_rating'] ?? '' }}"
                                                                    placeholder="{{ __('Ex: 4.5 or 5') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Rating Out Of') }}</label>
                                                                <div class="gpt-up-down-arrow position-relative">
                                                                    <select name="customers_rating_out_of" class="form-control table-select w-100">
                                                                        <option {{ (isset($page_data['headings']['customers_rating_out_of']) && $page_data['headings']['customers_rating_out_of'] == 5) ? 'selected' : '' }} value="5">{{ __('Five') }}</option>
                                                                        <option {{ (isset($page_data['headings']['customers_rating_out_of']) && $page_data['headings']['customers_rating_out_of'] == 10) ? 'selected' : '' }} value="10">{{ __('Ten') }}</option>
                                                                    </select>
                                                                    <span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Customer section End --}}

                                                        {{-- Client section start --}}
                                                        <div class="tab-pane fade" id="client">
                                                            <div class="form-group">
                                                                <label>{{ __('Start Short Title') }}</label>
                                                                <input type="text" name="client_start_short_title"
                                                                    value="{{ $page_data['headings']['client_start_short_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Short Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('End Short Title') }}</label>
                                                                <input type="text" name="client_end_short_title"
                                                                    value="{{ $page_data['headings']['client_end_short_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Short Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Start Title') }}</label>
                                                                <input type="text" name="client_start_title"
                                                                    value="{{ $page_data['headings']['client_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Start Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('End Title') }}</label>
                                                                <input type="text" name="client_end_title"
                                                                    value="{{ $page_data['headings']['client_end_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter End Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Total Clients') }}</label>
                                                                <input type="text" name="total_clients"
                                                                    value="{{ $page_data['headings']['total_clients'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Total Client') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Rating') }}</label>
                                                                <input type="number" name="clinet_rating"
                                                                    value="{{ $page_data['headings']['clinet_rating'] ?? '' }}"
                                                                    placeholder="{{ __('Ex: 4.5 or 5') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Rating Out Of') }}</label>
                                                                <div class="gpt-up-down-arrow position-relative">
                                                                    <select name="clinet_rating_out_of" class="form-control table-select w-100">
                                                                        <option {{ (isset($page_data['headings']['clinet_rating_out_of']) && $page_data['headings']['clinet_rating_out_of'] == 5) ? 'selected' : '' }} value="5">{{ __('Five') }}</option>
                                                                        <option {{ (isset($page_data['headings']['clinet_rating_out_of']) && $page_data['headings']['clinet_rating_out_of'] == 10) ? 'selected' : '' }} value="10">{{ __('Ten') }}</option>
                                                                    </select>
                                                                    <span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Client section End --}}

                                                        {{-- Highlight section start --}}
                                                        <div class="tab-pane fade" id="highlight">
                                                            <div class="form-group">
                                                                <label>{{ __('Highlight Button Text') }}</label>
                                                                <input type="text" name="highlight_btn_txt"
                                                                    value="{{ $page_data['headings']['highlight_btn_txt'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Button Text') }}" required
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        {{-- Highlight section End --}}

                                                        {{-- Software manage section start --}}
                                                        <div class="tab-pane fade" id="software_manage">
                                                            <div class="form-group">
                                                                <label>{{ __('Start Title') }}</label>
                                                                <input type="text" name="soft_mng_start_title"
                                                                    value="{{ $page_data['headings']['soft_mng_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Mid Title') }}</label>
                                                                <input type="text" name="soft_mng_mid_title"
                                                                    value="{{ $page_data['headings']['soft_mng_mid_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('End Title') }}</label>
                                                                <input type="text" name="soft_mng_eng_title"
                                                                    value="{{ $page_data['headings']['soft_mng_eng_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Sub Title One') }}</label>
                                                                <input type="text" name="soft_mng_subtitle_one"
                                                                    value="{{ $page_data['headings']['soft_mng_subtitle_one'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Sub Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Description One') }}</label>
                                                                <textarea name="soft_mng_desc_one" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['soft_mng_desc_one'] ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Sub Title Two') }}</label>
                                                                <input type="text" name="soft_mng_subtitle_two"
                                                                    value="{{ $page_data['headings']['soft_mng_subtitle_two'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Sub Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Description Two') }}</label>
                                                                <textarea name="soft_mng_desc_two" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['soft_mng_desc_two'] ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Sub Title Three') }}</label>
                                                                <input type="text" name="soft_mng_subtitle_three"
                                                                    value="{{ $page_data['headings']['soft_mng_subtitle_three'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Sub Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Description Three') }}</label>
                                                                <textarea name="soft_mng_desc_three" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['soft_mng_desc_three'] ?? '' }}</textarea>
                                                            </div>

                                                            <div class="row">


                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title One Icon') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_one_icon" src="{{ asset($page_data['soft_mng_subtitle_one_icon'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_one_icon" class="preview-image-input" data-id="#soft_mng_subtitle_one_icon" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title One Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_one_img" src="{{ asset($page_data['soft_mng_subtitle_one_img'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_one_img" class="preview-image-input" data-id="#soft_mng_subtitle_one_img" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title Two Icon') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_two_icon" src="{{ asset($page_data['soft_mng_subtitle_two_icon'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_two_icon" class="preview-image-input" data-id="#soft_mng_subtitle_two_icon" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title Two Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_two_img" src="{{ asset($page_data['soft_mng_subtitle_two_img'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_two_img" class="preview-image-input" data-id="#soft_mng_subtitle_two_img" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title Three Icon') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_three_icon" src="{{ asset($page_data['soft_mng_subtitle_three_icon'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_three_icon" class="preview-image-input" data-id="#soft_mng_subtitle_three_icon" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Sub Title Three Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="soft_mng_subtitle_three_img" src="{{ asset($page_data['soft_mng_subtitle_three_img'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="soft_mng_subtitle_three_img" class="preview-image-input" data-id="#soft_mng_subtitle_three_img" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        {{-- Software manage section End --}}

                                                        {{-- Feature section start --}}
                                                        <div class="tab-pane fade" id="feature">
                                                            <div class="form-group">
                                                                <label>{{ __('Section Title') }}</label>
                                                                <input type="text" name="feature_start_title"
                                                                    value="{{ $page_data['headings']['feature_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Section Start Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('End Title') }}</label>
                                                                <input type="text" name="feature_end_title"
                                                                    value="{{ $page_data['headings']['feature_end_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Section End Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        {{-- Feature section End --}}

                                                        {{-- Get app section start --}}
                                                        <div class="tab-pane fade" id="get_app">
                                                            <div class="form-group">
                                                                <label>{{ __('Apple Store Link') }}</label>
                                                                <input type="text" name="get_app_apple_store_link"
                                                                    value="{{ $page_data['headings']['get_app_apple_store_link'] ?? '' }}"
                                                                    required class="form-control"
                                                                    placeholder="{{ __('Enter App Link') }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Play Store Link') }}</label>
                                                                <input type="text" name="get_app_play_store_link"
                                                                    value="{{ $page_data['headings']['get_app_play_store_link'] ?? '' }}"
                                                                    required class="form-control"
                                                                    placeholder="{{ __('Enter App Link') }}">
                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Apple Store Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="get_app_apple_store_img" src="{{ asset($page_data['get_app_apple_store_img'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="get_app_apple_store_img" class="preview-image-input" data-id="#get_app_apple_store_img" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Play Store Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="get_app_play_store_img" src="{{ asset($page_data['get_app_play_store_img'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="get_app_play_store_img" class="preview-image-input" data-id="#get_app_play_store_img" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        {{-- Get app section End --}}

                                                        {{-- Blog section start --}}
                                                        <div class="tab-pane fade" id="blog">
                                                            <div class="form-group">
                                                                <label>{{ __('Blog Title') }}</label>
                                                                <input type="text" name="blog_start_title"
                                                                    value="{{ $page_data['headings']['blog_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Start Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Blog End Title') }}</label>
                                                                <input type="text" name="blog_end_title"
                                                                    value="{{ $page_data['headings']['blog_end_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter End Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Read More Button Text') }}</label>
                                                                <input type="text" name="blog_btn_text"
                                                                    value="{{ $page_data['headings']['blog_btn_text'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <label>{{ __('View All Button Text') }}</label>
                                                                    <input type="text" name="blog_view_all_btn_text"
                                                                        value="{{ $page_data['headings']['blog_view_all_btn_text'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Text') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-lg-6">
                                                                    <label>{{ __('View All Link') }}</label>
                                                                    <input type="text" name="blog_view_all_btn_link"
                                                                        value="{{ $page_data['headings']['blog_view_all_btn_link'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Link') }}" required
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Blog section End --}}

                                                        {{-- Testimonial section start --}}
                                                        <div class="tab-pane fade" id="testimonial">
                                                            <div class="form-group">
                                                                <label>{{ __('Testimonial Start Title') }}</label>
                                                                <input type="text" name="testimonial_start_title"
                                                                    value="{{ $page_data['headings']['testimonial_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Start Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Testimonial Mid Title') }}</label>
                                                                <input type="text" name="testimonial_mid_title"
                                                                    value="{{ $page_data['headings']['testimonial_mid_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Min Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Testimonial End Title') }}</label>
                                                                <input type="text" name="testimonial_end_title"
                                                                    value="{{ $page_data['headings']['testimonial_end_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter End Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Testimonial Final Title') }}</label>
                                                                <input type="text" name="testimonial_final_title"
                                                                    value="{{ $page_data['headings']['testimonial_final_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Final Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        {{-- Testimonial section End --}}

                                                        {{-- About us section start --}}
                                                        <div class="tab-pane fade" id="about_us">
                                                            <div class="form-group">
                                                                <label>{{ __('Title') }}</label>
                                                                <input type="text" name="about_us_title"
                                                                    value="{{ $page_data['headings']['about_us_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <label class="img-label">{{ __('Image') }}</label>
                                                                    <div class="custom-upload-wrapper">
                                                                        <div class="custom-image-box">
                                                                            <div class="custom-image-content">
                                                                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                    <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                </svg>
                                                                                <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                            </div>

                                                                            <!-- Preview image -->
                                                                            <img class="custom-preview-image d-none" id="about_image" src="{{ asset($page_data['about_image'] ?? '') }}"alt="Preview">
                                                                            <input type="file" name="about_image" class="preview-image-input" data-id="#about_image" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Description') }}</label>
                                                                <textarea name="about_desc" class="form-control summernote" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['about_desc'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                        {{--  About Us section End --}}

                                                        {{-- Contact Us Section Start --}}
                                                        <div class="tab-pane fade" id="contact_us">
                                                            <div>
                                                                <div class="form-group">
                                                                    <label>{{ __('Start Title') }}</label>
                                                                    <input type="text" name="contact_us_start_title"
                                                                        value="{{ $page_data['headings']['contact_us_start_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Start Title') }}" required
                                                                        class="form-control">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ __('Mid Title') }}</label>
                                                                    <input type="text" name="contact_us_mid_title"
                                                                        value="{{ $page_data['headings']['contact_us_mid_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Mid Title') }}" required
                                                                        class="form-control">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ __('End Title') }}</label>
                                                                    <input type="text" name="contact_us_end_title"
                                                                        value="{{ $page_data['headings']['contact_us_end_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter End Title') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>{{ __('Button Text') }}</label>
                                                                    <input type="text" name="contact_us_btn_text"
                                                                        value="{{ $page_data['headings']['contact_us_btn_text'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Text') }}" class="form-control">
                                                                </div>

                                                                <div class="row">

                                                                    <div class="col-12">
                                                                        <label class="img-label">{{ __('Image') }}</label>
                                                                        <div class="custom-upload-wrapper">
                                                                            <div class="custom-image-box">
                                                                                <div class="custom-image-content">
                                                                                    <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                        <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                        <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                        <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                    </svg>
                                                                                    <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                                </div>

                                                                                <!-- Preview image -->
                                                                                <img class="custom-preview-image d-none" id="contact_us_img" src="{{ asset($page_data['contact_us_img'] ?? '') }}"alt="Preview">
                                                                                <input type="file" name="contact_us_img" class="preview-image-input" data-id="#contact_us_img" accept="image/*">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                                <div class="form-group">
                                                                    <label>{{ __('Description') }}</label>
                                                                    <textarea name="contact_us_description" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['contact_us_description'] ?? '' }}</textarea>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        {{-- Contact Us Section End --}}

                                                        {{-- Pricing section --}}
                                                        <div class="tab-pane fade" id="pricing">
                                                            <div class="form-group">
                                                                <label>{{ __('Title') }}</label>
                                                                <input type="text" name="pricing_start_title"
                                                                    value="{{ $page_data['headings']['pricing_start_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Start Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Mid Title') }}</label>
                                                                <input type="text" name="pricing_mid_title"
                                                                    value="{{ $page_data['headings']['pricing_mid_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Mid Title') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('End Title') }}</label>
                                                                <input type="text" name="pricing_end_title"
                                                                    value="{{ $page_data['headings']['pricing_end_title'] ?? '' }}"
                                                                    placeholder="{{ __('Enter End Title') }}" required
                                                                    class="form-control">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>{{ __('Description') }}</label>
                                                                <textarea name="pricing_description" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['pricing_description'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                        {{-- pricing section end --}}

                                                        {{-- Faq section --}}
                                                        <div class="tab-pane fade" id="faq">
                                                            <div class="form-group">
                                                                <label>{{ __('Title One') }}</label>
                                                                <input type="text" name="faq_title_one"
                                                                    value="{{ $page_data['headings']['faq_title_one'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title One') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Title Two') }}</label>
                                                                <input type="text" name="faq_title_two"
                                                                    value="{{ $page_data['headings']['faq_title_two'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title Two') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Title Three') }}</label>
                                                                <input type="text" name="faq_title_three"
                                                                    value="{{ $page_data['headings']['faq_title_three'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Title Three') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Button Text') }}</label>
                                                                <input type="text" name="faq_btn_text"
                                                                    value="{{ $page_data['headings']['faq_btn_text'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Button Text') }}" required
                                                                    class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ __('Button Link') }}</label>
                                                                <input type="text" name="faq_btn_link"
                                                                    value="{{ $page_data['headings']['faq_btn_link'] ?? '' }}"
                                                                    placeholder="{{ __('Enter Button Link') }}" required
                                                                    class="form-control">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>{{ __('Description') }}</label>
                                                                <textarea name="faq_description" class="form-control" placeholder="{{ __('Enter Description') }}">{{ $page_data['headings']['faq_description'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                        {{-- Faq section end --}}

                                                        {{-- Footer Section Start --}}
                                                        <div class="tab-pane fade" id="footer">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>{{ __('Short Title') }}</label>
                                                                    <input type="text" name="footer_short_title"
                                                                        value="{{ $page_data['headings']['footer_short_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Title') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-12">
                                                                    <label>{{ __('Right Footer Title') }}</label>
                                                                    <input type="text" name="right_footer_title"
                                                                        value="{{ $page_data['headings']['right_footer_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Title') }}" required
                                                                        class="form-control">
                                                                </div>

                                                                <div class="col-12">
                                                                    <label>{{ __('Middle Footer Title') }}</label>
                                                                    <input type="text" name="middle_footer_title"
                                                                        value="{{ $page_data['headings']['middle_footer_title'] ?? '' }}"
                                                                        placeholder="{{ __('Enter Title') }}" required
                                                                        class="form-control">
                                                                </div>

                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h4 class="mb-3">{{ __('Left Footer') }}</h4>
                                                                    <button
                                                                        class="w-100 py-3 d-block text-center fw-bold mt-3 admin-collapse bg-light primary border-0"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#right_footer"
                                                                        aria-expanded="false"
                                                                        aria-controls="right_footer">
                                                                       {{ __('Left Footer') }}
                                                                        <i class="fas fa-arrow-circle-down ms-2"></i>
                                                                    </button>
                                                                    <div class="mt-3 collapse row" id="right_footer">
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_one"
                                                                                    value="{{ $page_data['headings']['right_footer_one'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_one"
                                                                                    value="{{ $page_data['headings']['right_footer_link_one'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_two"
                                                                                    value="{{ $page_data['headings']['right_footer_two'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_two"
                                                                                    value="{{ $page_data['headings']['right_footer_link_two'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_three"
                                                                                    value="{{ $page_data['headings']['right_footer_three'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_three"
                                                                                    value="{{ $page_data['headings']['right_footer_link_three'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_four"
                                                                                    value="{{ $page_data['headings']['right_footer_four'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_four"
                                                                                    value="{{ $page_data['headings']['right_footer_link_four'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_five"
                                                                                    value="{{ $page_data['headings']['right_footer_five'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_five"
                                                                                    value="{{ $page_data['headings']['right_footer_link_five'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_six"
                                                                                    value="{{ $page_data['headings']['right_footer_six'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="right_footer_link_six"
                                                                                    value="{{ $page_data['headings']['right_footer_link_six'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h4 class="mb-3">{{ __('Middle Footer') }}</h4>
                                                                    <button
                                                                        class="w-100 py-3 d-block text-center fw-bold mt-3 admin-collapse bg-light primary border-0"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#middle_footer"
                                                                        aria-expanded="false"
                                                                        aria-controls="middle_footer">
                                                                       {{ __('Middle Footer') }}
                                                                        <i class="fas fa-arrow-circle-down ms-2"></i>
                                                                    </button>
                                                                    <div class="mt-3 collapse row" id="middle_footer">
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_one"
                                                                                    value="{{ $page_data['headings']['middle_footer_one'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_one"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_one'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_two"
                                                                                    value="{{ $page_data['headings']['middle_footer_two'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_two"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_two'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_three"
                                                                                    value="{{ $page_data['headings']['middle_footer_three'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_three"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_three'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_four"
                                                                                    value="{{ $page_data['headings']['middle_footer_four'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_four"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_four'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_five"
                                                                                    value="{{ $page_data['headings']['middle_footer_five'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_five"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_five'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_six"
                                                                                    value="{{ $page_data['headings']['middle_footer_six'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="middle_footer_link_six"
                                                                                    value="{{ $page_data['headings']['middle_footer_link_six'] ?? '' }}"
                                                                                    required class="form-control">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h4 class="mb-3">{{ __('Right Footer') }}</h4>
                                                                    <button
                                                                        class="w-100 py-3 d-block text-center fw-bold mt-3 admin-collapse bg-light primary border-0"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#left_footer"
                                                                        aria-expanded="false" aria-controls="left_footer">
                                                                        {{ __('Right Footer') }}
                                                                        <i class="fas fa-arrow-circle-down ms-2"></i>
                                                                    </button>
                                                                    <div class="mt-3 collapse row" id="left_footer">
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_one"
                                                                                    value="{{ $page_data['headings']['left_footer_one'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_link_one"
                                                                                    value="{{ $page_data['headings']['left_footer_link_one'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_two"
                                                                                    value="{{ $page_data['headings']['left_footer_two'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_link_two"
                                                                                    value="{{ $page_data['headings']['left_footer_link_two'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_three"
                                                                                    value="{{ $page_data['headings']['left_footer_three'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_link_three"
                                                                                    value="{{ $page_data['headings']['left_footer_link_three'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_four"
                                                                                    value="{{ $page_data['headings']['left_footer_four'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_link_four"
                                                                                    value="{{ $page_data['headings']['left_footer_link_four'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-4">
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Title') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_five"
                                                                                    value="{{ $page_data['headings']['left_footer_five'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                            <div class="col-sm-6">
                                                                                <label>{{ __('Link') }}</label>
                                                                                <input type="text"
                                                                                    name="left_footer_link_five"
                                                                                    value="{{ $page_data['headings']['left_footer_link_five'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Footer section End --}}

                                                        {{-- Social section start --}}
                                                        <div class="tab-pane fade" id="socials">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h4 class="mb-3">{{ __('Footer Socials') }}</h4>
                                                                    <button
                                                                        class="w-100 py-3 d-block text-center fw-bold mt-3 admin-collapse bg-light primary border-0"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#footer_socials"
                                                                        aria-expanded="false"
                                                                        aria-controls="footer_socials">
                                                                        {{ __('Footer Socials') }} <i
                                                                            class="fas fa-arrow-circle-down ms-2"></i>
                                                                    </button>
                                                                    <div class="collapse row mt-3" id="footer_socials">

                                                                        @foreach ($page_data['headings']['footer_socials_links'] ?? [] as $key => $footer_socials_links)
                                                                            <div
                                                                                class="sample-form-wrp duplicate-feature pe-3">
                                                                                <div class="row mb-4">
                                                                                    <div class="col-sm-6">
                                                                                        <label>{{ __('Link') }}</label>
                                                                                        <input type="text" name="footer_socials_links[]"
                                                                                            value="{{ $footer_socials_links ?? '' }}"
                                                                                            required class="form-control">
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <label class="img-label">{{ __('Icon') }}</label>
                                                                                        <div class="custom-upload-wrapper">
                                                                                            <div class="custom-image-box">
                                                                                                <div class="custom-image-content">
                                                                                                    <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                        <path d="M18.3327 7.5026V12.5026C18.3327 14.8596 18.3327 16.0381 17.6004 16.7704C16.8682 17.5026 15.6897 17.5026 13.3327 17.5026H6.66602C4.309 17.5026 3.13048 17.5026 2.39825 16.7704C1.66602 16.0381 1.66602 14.8596 1.66602 12.5026V9.21404C1.66602 8.39729 1.66602 7.98892 1.76053 7.65502C1.99698 6.81974 2.64982 6.1669 3.48509 5.93046C3.819 5.83594 4.22736 5.83594 5.04409 5.83594C5.34907 5.83594 5.50157 5.83594 5.64361 5.81118C5.99556 5.74983 6.31847 5.577 6.56476 5.3182C6.66415 5.21375 6.91352 4.8397 7.08268 4.58594C7.413 4.09048 7.57815 3.84275 7.80393 3.67233C7.94181 3.56826 8.09502 3.48627 8.25809 3.42927C8.52512 3.33594 8.82287 3.33594 9.41837 3.33594H10.8327" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                                        <path d="M13.3327 11.2474C13.3327 13.0883 11.8403 14.5807 9.99937 14.5807C8.1584 14.5807 6.66602 13.0883 6.66602 11.2474C6.66602 9.40641 8.1584 7.91406 9.99937 7.91406C11.8403 7.91406 13.3327 9.40641 13.3327 11.2474Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                                                        <path d="M13.334 4.58333H17.5007M15.4173 6.66667V2.5" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                                                    </svg>
                                                                                                    <span class="custom-upload-text">{{ __('Add Image') }}</span>
                                                                                                </div>

                                                                                                <!-- Preview image -->
                                                                                                <img class="custom-preview-image d-none" id="{{ 'footer_socials_icons_' . $key }}" src="{{ asset($page_data['footer_socials_icons'][$key] ?? '') }}" alt="Preview">
                                                                                                <input type="file" name="footer_socials_icons[]" class="preview-image-input" data-id="#{{ 'footer_socials_icons_' . $key }}" accept="image/*">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Social section end --}}

                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="button-group text-center mt-4">
                                                                    <button class="theme-btn m-2 submit-btn">{{ __('Update') }}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
