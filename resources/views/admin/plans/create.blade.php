@extends('layouts.admin.master')

@section('title')
    {{ __('Plan Create') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Add New Subscription') }}</h3>
            </div>
            <div class="dashboard-card-body">
                <form action="{{ route('admin.plans.store') }}" method="POST" enctype="multipart/form-data" class="ajaxform_instant_reload">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4">
                            <label>{{ __('Image') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Plan Name') }}</label>
                            <input type="text" name="subscriptionName" class="form-control" placeholder="{{ __('Name your package') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Plan Duration') }}</label>
                            <div class="input-group align-items-center">
                                <input type="number" name="duration" class="form-control" placeholder="{{ __('Enter duration') }}" required>
                                <select class="form-control h-48 cursor-pointer" name="duration_type">
                                    <option value="year">{{ __('Years') }}</option>
                                    <option value="month">{{ __('Months') }}</option>
                                    <option value="day">{{ __('Days') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label for="name" class="required">{{ __('Plan Price') }}</label>
                            <input type="text" name="subscriptionPrice" class="form-control" placeholder="{{ __('Enter Price') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label for="name" class="required">{{ __('Offer Price(Optional)') }}</label>
                            <input type="text" name="offerPrice" class="form-control" placeholder="{{ __('Enter Price') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Number of Properties') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_property" id="number_of_property" placeholder="{{ __('Number of Properties') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="property_unlimited_status" id="property_unlimited_status" value="1" checked>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Number Of Tenants') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_tenant" id="number_of_tenant"
                                    placeholder="{{ __('Number Of Tenants') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="tenant_unlimited_status" id="tenant_unlimited_status" value="1" checked>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Number Of Labor') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_labor" id="number_of_labor" placeholder="{{ __('Number Of Labor') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="labor_unlimited_status" id="labor_unlimited_status" value="1" checked>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="col-lg-12">
                                <label>{{ __('Status') }}</label>
                                <div class="form-control d-flex justify-content-between align-items-center radio-switcher">
                                    <p class="dynamic-text">{{ __('Active') }}</p>
                                    <label class="switch m-0 check-height custom-checkbox">
                                        <input type="checkbox" name="status" class="change-text" checked>
                                        <span class="slider round checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Add New Features') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control add-feature"
                                    placeholder="{{ __('Enter features') }}" aria-invalid="false">
                                <button class="feature-btn modal-btn  " id="feature-btn">{{ __('Save') }}</button>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="row feature-list">
                                {{-- Will added dynamically. --}}
                            </div>
                        </div>

                        <div class="add-landlord-button-group">
                            <button type="button" class="btn  border-btn">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
