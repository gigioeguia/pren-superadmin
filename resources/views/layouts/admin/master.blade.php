<!DOCTYPE html>
@if (app()->getLocale() == 'ar')
    <html lang="ar" dir="rtl">
@else
    <html lang="en" dir="auto">
@endif

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="{{ __('IE=edge') }}">
    <meta name="viewport" content="{{ __('width=device-width, initial-scale=1.0') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title') @yield('title') | @endif {{ get_option('general')['app_name'] ?? config('app.name') }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset(get_option('general')['favicon'] ?? 'maan/images/logo/favicon.png') }}">
    @include('layouts.admin.partials.css')
</head>

<body>

    <main class="woodland-main-section">
        @include('layouts.admin.partials.header')

        @include('layouts.admin.partials.sidebar')

        <!-- main inner section start -->
        <div>
            @yield('main_content')
        </div>

        @stack('modal')

        @include('layouts.admin.partials.footer')

    </main>

    @include('layouts.admin.partials.script')
    @stack('script')

</body>

</html>
