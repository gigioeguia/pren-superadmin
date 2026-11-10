<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title') @yield('title') | @endif {{ get_option('general')['app_name'] ?? config('app.name') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset(get_option('general')['favicon'] ?? 'assets/images/logo/favicon.png')}}">
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
    <!-- toastr -->
    <link rel="stylesheet" href="{{ asset('backend/plugins/toastr/toastr.min.css') }}">

    @stack('css')
</head>
