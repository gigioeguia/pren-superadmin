@include('layouts.lang-text')

<script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>

{{-- jquery confirm --}}
<script src="{{asset('backend/assets/plugins/jquery-confirm/jquery-confirm.min.js')}}"></script>
{{-- jquery validation --}}
<script src="{{asset('backend/assets/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
{{-- Custom --}}
<script src="{{ asset('backend/assets/plugins/validation-setup/validation-setup.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/custom/notification.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/custom/form.js') }}?v={{ time() }}"></script>
{{-- Status --}}
<script src="{{ asset('backend/assets/js/custom-ajax.js') }}"></script>

<script src="{{ asset('backend/assets/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/lity.js') }}"></script>
<script src="{{ asset('backend/assets/js/jquery.nice-select.js') }}"></script>
<script src="{{ asset('backend/assets/js/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/summernote-lite.js') }}"></script>
<script src="{{ asset('backend/assets/js/image-uploader.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/tableToExcel.js') }}"></script>
<script src="{{ asset('backend/assets/js/chart.min.js') }}"></script>

<script src="{{ asset('backend/assets/js/theme.js') }}?v={{ time() }}"></script>
<script src="{{ asset('maan/js/sidebar.js') }}?v={{ time() }}"></script>
<!-- toastr -->
<script src="{{ asset('backend/plugins/toastr/toastr.min.js') }} "></script>
{{--custom js --}}
<script src="{{ asset('maan/js/custom.js') }}?v={{ time() }}"></script>
<script src="{{ asset('maan/js/custom-action.js') }}?v={{ time() }}"></script>


@stack('asset_js')

@stack('modal-view')

{{-- Toaster Message --}}
@if(Session::has('message'))
    <script>
        toastr.success( "{{ Session::get('message') }}");
    </script>
@endif
@if(Session::has('error'))
    <script>
        toastr.error( "{{ Session::get('error') }}");
    </script>
@endif
@if($errors->any())
    <script>
        toastr.warning('Error some occurs!');
    </script>
@endif
