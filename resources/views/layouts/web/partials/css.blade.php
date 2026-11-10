<link rel="shortcut icon" type="image/x-icon" href="{{ asset(get_option('general')['favicon'] ?? 'maan/images/logo/favicon.png')}}">
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/bootstrap.min.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/swiper-bundle.min.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/web/fonts/fontawesome/css/all.min.css') }}" />
<!-- Slick Slider -->
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/slick.css') }}" />
{{-- jquery-confirm --}}
<link rel="stylesheet" href="{{ asset('backend/assets/plugins/jquery-confirm/jquery-confirm.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/slick-theme.css') }}" />
<!-- Custom Css -->
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/styles.css') }}?v={{ time() }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/responsive.css') }}?v={{ time() }}" />
<link rel="stylesheet" href="{{ asset('backend/assets/css/payments.css') }}?v={{ time() }}" />

<!-- Toaster -->
<link rel="stylesheet" href="{{ asset('backend/plugins/toastr/toastr.min.css') }}">

{{-- aos animation --}}
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/aos.css') }}" />


@if (app()->getLocale() == 'ar')
<link rel="stylesheet" href="{{ asset('backend/assets/web/css/arabic.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap.rtl.min.css') }}">
@endif

@stack('css')
