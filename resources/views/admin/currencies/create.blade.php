@extends('layouts.admin.master')

@section('title')
    {{ __('Add Currency') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">

                    <div class="woodland-card-header border-bottom pb-3">
                        <h3>{{ __('Add Currency') }}</h3>
                        @can('currencies-read')
                        <a href="{{ route('admin.currencies.index') }}" class="modal-btn">{{ __('View List') }}</a>
                        @endcan
                    </div>

                    <form action="{{ route('admin.currencies.store') }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf

                        <div class="row p-16">
                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" required class="form-control" placeholder="{{ __('Enter Name') }}">
                            </div>
                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Code') }}</label>
                                <input type="text" name="code" required class="form-control" placeholder="{{ __('Enter Code') }}">
                            </div>
                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Symbol') }}</label>
                                <input type="text" name="symbol" class="form-control" placeholder="{{ __('Enter Symbol') }}">
                            </div>
                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Position') }}</label>
                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="position" class="form-control table-select w-100">
                                        <option value="">{{ __('Select a position') }}</option>
                                        <option value="left">{{ __('left') }}</option>
                                        <option value="right">{{ __('right') }}</option>
                                    </select>
                                    <span></span>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Country') }}</label>
                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="country_name" class="form-control table-select w-100">
                                        <option value="">{{ __('Select a Country') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <span></span>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-2">
                                <label>{{ __('Status') }}</label>
                                <div class="gpt-up-down-arrow position-relative">
                                    <select name="status" required class="form-control table-select w-100">
                                        <option value="1">{{ __('Active') }}</option>
                                        <option value="0">{{ __('Inactive') }}</option>
                                    </select>
                                    <span></span>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="add-landlord-button-group">
                                    <button type="reset" class="btn  border-btn">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
