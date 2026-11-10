<link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/fonts/fontawesome/css/fontawesome-all.min.css') }}">
{{-- jquery-confirm --}}
<link rel="stylesheet" href="{{ asset('backend/assets/plugins/jquery-confirm/jquery-confirm.min.css') }}">

<link rel="stylesheet" href="{{ asset('backend/assets/css/lity.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/nice-select.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/summernote-lite.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/image-uploader.min.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/style.css') }}?v={{ time() }}">
@if (app()->getLocale() == 'ar')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/arabic.css') }}?v={{ time() }}">
@endif
<!-- toastr -->
<link rel="stylesheet" href="{{ asset('backend/plugins/toastr/toastr.css') }}">
<link rel="stylesheet" href="{{ asset('backend/assets/css/summernote-lite.css') }}">

@stack('css')