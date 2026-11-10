@extends('layouts.landlord.master')

@section('title')
    {{ __('Notifications List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>@lang('Notifications List') </h3>
                </div>
                <div class="woodland-card">

                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <form action="{{ route('landlord.notifications.filter') }}" method="post" class="filter-form" table="#notifications-data">
                            @csrf
                            <div class="grid-5">
                                <select name="days" class="table-select form-control m-0 search-input">
                                    <option value="daily">{{__('Today')}}</option>
                                    <option value="weekly">{{__('Last 7 Days')}}</option>
                                    <option value="15_days">{{__('Last 15 Days')}}</option>
                                    <option value="monthly">{{__('Last Month')}}</option>
                                    <option value="yearly">{{__('Last Year')}}</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="responsibe-table">
                        <table class="table woodland-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Message') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Read At') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="notifications-data">
                                @include('landlord.notifications.datas')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('landlord.notifications.view')
@endpush
