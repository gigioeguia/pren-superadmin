@extends('layouts.admin.master')

@section('title')
    {{ __('Payments') }}
@endsection

@section('main_content')
    <!-- dashboard inner section start -->
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">
                    <div class="woodland-card-header">
                        <h3>{{__('Payment Settings')}} </h3>
                        <a href="#payment-settings" data-bs-toggle="modal" class="table-modal-btn">{{__('Add')}}
                            <span><i class="far fa-plus"></i></span></a>
                    </div>
                    <div class="responsibe-table table-height">
                        <table class="table woodland-table">
                            <thead>
                            <tr>
                                <th>@lang('SL')</th>
                                <th>@lang('Icon')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('URL')</th>
                                <th>@lang('Key')</th>
                                <th>@lang('Collection Id')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @include('admin.settings.payment.datas')
                            </tbody>
                        </table>
                    </div>
                    <div class="woodladn-pagination-wrapper">
                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item"></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="payment-settings">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content woodland-modal-content">
                <div class="modal-header d-none d-lg-flex">
                    <h5 class="modal-title">{{__('Payment Settings')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mt-5 payment-setting-modal-wrapper">
                    <form name="paymentGatewayForm" action="{{route('admin.payment-gateway.store')}}" method="POST"
                          enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>@lang('Upload NID / Passport')</label>
                                </div>
                                <div class="col-lg-12">
                                    <div class="multi-img-upload">
                                        <div class="upload__box">
                                            <div class="upload__btn-box">
                                                <div class="upload__btn">
                                                    <div class="upload__img-wrap multi-img-grid">
                                                        <div class="add-landlord-profile-info upload-squre">
                                                            <label class="profile-img">
                                                                <span class="image-preview-icon"><i
                                                                            class="fal fa-plus-circle"></i></span>
                                                                <input type="file" name="image" data-max_length="20"
                                                                       class="upload__inputfile d-none">
                                                            </label>
                                                        </div>
                                                        <div id="image-aria"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="name">@lang('Gateways Name')</label>
                                <input type="text" class="form-control" name="name" id="name"
                                       placeholder="@lang('Gateway name')" value="{{old('name')}}">
                                @error('name')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="col-lg-12">
                                <label for="url">@lang('API URL')</label>
                                <input type="url" class="form-control" name="url" id="url"
                                       placeholder="@lang('Gateway url')" value="{{old('url')}}">
                                @error('url')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="col-lg-12">
                                <label for="api_key">@lang('API Key')</label>
                                <input type="text" class="form-control" name="api_key" id="api_key"
                                       placeholder="@lang('Api key')" value="{{old('api_key')}}">
                                @error('api_key')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="col-lg-12">
                                <label for="collection_id">@lang('Collection ID')</label>
                                <input type="text" class="form-control" name="collection_id" id="collection_id"
                                       placeholder="@lang('Collection id')" value="{{old('collection_id')}}">
                                @error('collection_id')
                                <div class="alert alert-danger">{{$message}}</div>
                                @enderror
                            </div>

                            <div class="col-lg-12">
                                <div class="add-landlord-button-group">
                                    <button type="submit" class="btn theme-btn submit-btn">{{__('Payment')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
