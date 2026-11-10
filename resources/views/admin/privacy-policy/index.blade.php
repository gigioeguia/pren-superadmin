@extends('layouts.admin.master')

@section('title')
    {{ __('Privacy Policy Settings') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Privacy Policy Settings') }}</h3>
                </div>
                <div class="woodland-card">
                    <div class="order-form-section p-16">
                        <form action="{{ route('admin.privacy-policy.store') }}" method="post" enctype="multipart/form-data"
                            class="ajaxform_instant_reload">
                            @csrf
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>{{ __('Title') }}</label>
                                        <input type="text" name="privacy_title"
                                            value="{{ $privacy_policy->value['privacy_title'] ?? '' }}"
                                            placeholder="{{ __('Enter Title') }}" required class="form-control">
                                    </div>
                                    <div class="col-lg-12">
                                        <label>{{ __('Description') }}</label>
                                        <textarea name="description" class="w-100 custom-textarea summernote" required rows="5"
                                            placeholder="{{ __('Enter Description') }}">{{ $privacy_policy->value['description'] ?? '' }}</textarea>
                                    </div>
                                    @can('privacy-policy-update')
                                        <div class="col-lg-12">
                                            <div class="text-center mt-5">
                                                <button type="submit" class="theme-btn m-2 submit-btn">{{ __('Update') }}</button>
                                            </div>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
