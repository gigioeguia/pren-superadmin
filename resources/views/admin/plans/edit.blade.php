@extends('layouts.admin.master')

@section('title')
    {{ __('Plan Edit') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Edit Subscription') }}</h3>
            </div>
            <div class="dashboard-card-body">
                <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" enctype="multipart/form-data" class="ajaxform_instant_reload">
                    @csrf
                    @method('put')
                    <div class="row">

                        <div class="col-lg-4">
                            <label>{{ __('Image') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Plan Name') }}</label>
                            <input type="text" name="subscriptionName" value="{{ $plan->subscriptionName }}"
                                class="form-control" placeholder="{{ __('Name your package') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Plan Duration') }}</label>
                            <div class="input-group align-items-center">
                                <input type="number" name="duration" value="{{ $plan->duration }}" class="form-control"
                                    placeholder="{{ __('Enter Duration') }}" required>
                                <select class="form-control h-48 cursor-pointer" name="duration_type">
                                    <option @selected($plan->duration_type == 'year') value="year">{{ __('Years') }}</option>
                                    <option @selected($plan->duration_type == 'month') value="month">{{ __('Months') }}</option>
                                    <option @selected($plan->duration_type == 'day') value="day">{{ __('Days') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label for="name" class="required">{{ __('Plan Price') }}</label>
                            <input type="text" name="subscriptionPrice" value="{{ $plan->subscriptionPrice }}"
                                class="form-control" placeholder="{{ __('Enter Price') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label for="name" class="required">{{ __('Offer Price(Optional)') }}</label>
                            <input type="text" name="offerPrice" value="{{ $plan->offerPrice }}" class="form-control"
                                placeholder="{{ __('Enter Price') }}" required>
                        </div>

                        <div class="col-lg-4">
                            <label class="required">{{ __('Number of Properties') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_property" id="number_of_property" value="{{ $plan->number_of_property != -1 ? $plan->number_of_property : '' }}"
                                    placeholder="{{ __('Number of Properties') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="property_unlimited_status" id="property_unlimited_status" {{ $plan->number_of_property == -1 ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Number Of Tenants') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_tenant" value="{{ $plan->number_of_tenant != -1 ? $plan->number_of_tenant : '' }}"
                                    id="number_of_tenant" placeholder="{{ __('Number Of Tenants') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="tenant_unlimited_status" id="tenant_unlimited_status" {{ $plan->number_of_tenant == -1 ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label>{{ __('Number Of Labor') }}</label>
                            <div class="form-control d-flex justify-content-between align-items-center">
                                <input type="number" name="number_of_labor" id="number_of_labor" value="{{ $plan->number_of_labor != -1 ? $plan->number_of_labor : '' }}"
                                    placeholder="{{ __('Number Of Labor') }}">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="labor_unlimited_status" id="labor_unlimited_status" {{ $plan->number_of_labor == -1 ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                    <span class="text">{{ __('Unlimited') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="col-lg-12">
                                <label>{{ __('Status') }}</label>
                                <div class="form-control d-flex justify-content-between align-items-center radio-switcher">
                                    <p class="dynamic-text">{{ $plan->status == 1 ? __('Active') : __('Deactive') }}</p>
                                    <label class="switch m-0 check-height custom-checkbox">
                                        <input type="checkbox" name="status" {{ $plan->status == 1 ? 'checked' : '' }} class="change-text">
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
                                @foreach ($plan->features ?? [] as $key => $item)
                                    <div class="col-lg-6 remove-list">
                                        <div class="feature-wrp">
                                        <div class="form-control d-flex justify-content-between align-items-center">
                                            <input name="features[{{ $key }}][name]" class="feature-name form-control"
                                                type="text" value="{{ $item['name'] ?? '' }}">
                                            <label class="switch m-0 check-height custom-checkbox">
                                                <input type="checkbox" class="feature-status" name="features[{{ $key }}][status]" value="1"
                                                {{ $item['status'] == 1 ? 'checked' : '' }}  >
                                                <span class="slider round checkmark"></span>
                                            </label>
                                        </div>
                                        </div>
                                    </div>
                                @endforeach
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
