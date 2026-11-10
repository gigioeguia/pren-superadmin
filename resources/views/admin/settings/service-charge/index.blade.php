@extends('layouts.admin.master')

@section('title')
    {{ __('Service Charge') }}
@endsection

@section('main_content')

    <div class="woodland-section-container">
        <div class="row">
            <div class="col-xxl-12 col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Service Charge') }}</h3>
                </div>
                <div class="woodland-card">
                    <form action="{{route('admin.service-charges.update', $service_charge->id)}}" method="POST" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        @method('Put')

                        <div class="woodland-form-wrapper new-subscription-form">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>@lang("Service Charge")</label>
                                    <div class="input-group service-charge-input align-items-center">
                                        <input type="number" step="any" name="amount" value="{{ $service_charge->value['amount'] ?? 0 }}" class="form-control" placeholder="@lang('Enter Service Charge')">
                                        <div class="custom-selector">
                                            <select class="form-control" name="type">
                                                <option value="fixed" @selected($service_charge->value['type'] == 'fixed')>@lang('Fixed')</option>
                                                <option value="percentage" @selected($service_charge->value['type'] == 'percentage')>@lang('Percentage')</option>
                                            </select>
                                           
                                        </div>
                                    </div>
                                </div>
                                @can('service-charge-update')
                                <div class="col-md-12">
                                    <div class="add-landlord-button-group">
                                        <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
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

@endsection
