@extends('layouts.admin.master')

@section('main_content')
    <!-- dashboard inner section start -->
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">
                    <div class="woodland-card-header">
                        <h3>@lang('Banner Settings')</h3>
                        <div class="action-btn-wrapper">
                            <a href="{{ route('admin.banner.create') }}" class="modal-btn"><i class="fal fa-plus-circle"></i> @lang('Update Banner')</a>
                        </div>
                    </div>
                    <div class="media-header-wrapper">
                        <div class="media-btn-wrapper flex-wrap">
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @lang('All Medai items')
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">@lang('Rent Media')</a></li>
                                    <li><a class="dropdown-item" href="#">@lang('Sale Media')</a></li>
                                    <li><a class="dropdown-item" href="#">@lang('Property Media')</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @lang('All Dates')
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">@lang('10/02/2022')</a></li>
                                    <li><a class="dropdown-item" href="#">@lang('10/03/2022')</a></li>
                                    <li><a class="dropdown-item" href="#">@lang('10/04/2022')</a></li>
                                </ul>
                            </div>
                        </div>
                        <form action="#">
                            <div class="media-img-search">
                                <input type="text" class="form-control" placeholder="@lang('Search media items...')">
                                <i class="far fa-search" aria-hidden="true"></i>
                            </div>
                        </form>
                    </div>
                    <div class="media-images-list">
                        <ul>
                            @foreach($bannerImages as $bannerImage)
                            <li><img src="{{asset('uploads/'.$bannerImage->image)}}" alt=""></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
