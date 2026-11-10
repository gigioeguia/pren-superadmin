@extends('layouts.admin.master')

@section('main_content')
    <!-- dashboard inner section start -->
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card mt-30">
                    <div class="woodland-card-header border-bottom pb-3">
                        <h3>@lang('Update Banner')</h3>
                    </div>
                    <form action="{{ route('admin.banner.update', $banner->id ?? '') }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="input-images-upload-wrapper">
                                    <div class="input-images"></div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="media-image-uploaded-form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="title">@lang('Title')</label>
                                            <input type="text" name="title" value="{{ $banner->title }}" id="title" class="form-control" placeholder="@lang('Enter name')">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="description">@lang('Description')</label>
                                            <textarea class="form-control" name="description" id="summernote" placeholder="@lang('Enter description')">{{ $banner->description }}</textarea>
                                        </div>
                                        <div class="col-md-12 mb-4">
                                            <label for="file-name">@lang('File Name')</label>
                                            <p id="file-name">@lang('b2ee453525564.3554665/upoad image. Jpg')</p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label for="file-type">@lang('File Type')</label>
                                            <p id="file-type">@lang('JPG')</p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label for="file-size">@lang('File Size')</label>
                                            <p id="file-size">@lang('221.22KB')</p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label for="dimensions">@lang('Dimensions')</label>
                                            <p id="dimensions">@lang('800 x 800')</p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label for="upload-date">@lang('Upload Date')</label>
                                            <p id="upload-date">@lang('800 x 800')</p>
                                        </div>
                                        <input type="hidden" name="id" value="{{ $banner->id ?? '' }}">
                                        <div class="col-md-12">
                                            <div class="add-landlord-button-group">
                                                <a href="{{ route('admin.banner.index') }}" class="btn  border-btn">@lang('Cancel')</a>
                                                <button type="submit" class="btn theme-btn submit-btn">@lang('Send')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
