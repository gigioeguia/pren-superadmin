<!DOCTYPE html>
<html lang="en">
@php
    $website = website();
@endphp
{{-- start include asset css--}}
@include('assets.header')
{{-- end include asset css--}}
<body>

<main class="woodland-main-section">
    @yield('main_content')
</main>

{{-- start include asset js --}}
@include('assets.footer')

</body>
</html>
