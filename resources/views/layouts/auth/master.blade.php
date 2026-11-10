<!DOCTYPE html>
<html lang="en">

@include('assets.header')

<body>

<!-- main inner section start -->
@yield('main_content')
@stack('modal')
<!-- main inner section start -->

{{-- start include asset js --}}
@include('assets.footer')
{{-- end include asset js --}}
</body>
</html>
