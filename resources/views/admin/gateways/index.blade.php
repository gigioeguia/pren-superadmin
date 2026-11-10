@extends('layouts.admin.master')

@section('title')
    {{ __('Gateway Settings') }}
@endsection

@section('main_content')
<div class="erp-table-section woodland-section-container system-settings">
    <div class="woodland-card-header">
        <h3>{{ __('Payment Gateway Settings') }}</h3>
    </div>
    <div class="woodland-card min-vh-100 mt-3">
        <div class="card-bodys">
            <div class="tab-content order-summary-tab mt-0">
                <div class="tab-pane fade active show" id="add-new-petty" role="tabpanel">
                    <div class="card-bodys">
                        <div class="row">
                            <div class="col-sm-11">
                                <div class="order-form-section p-16">
                                    <div class="row">

                                        <div class="col-12 col-sm-12 col-md-4 mb-4">

                                            <ul class="nav nav-pills flex-column flex-column shadow w-280 p-2">
                                                @foreach ($gateways as $gateway)
                                                    <li class="nav-item">
                                                        <a href="#{{ str_replace(' ', '-', $gateway->name) }}"
                                                            id="{{ str_replace(' ', '-', $gateway->name) }}-tab4"
                                                            @class([
                                                                'add-report-btn nav-link',
                                                                'active' => $loop->first ? true : false,
                                                            ])
                                                            data-bs-toggle="tab">{{ $gateway->name }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>

                                        </div>
                                        <div class="col-12 col-sm-12 col-md-8">
                                            <div class="cards border-0 shadow p-3 rounded">
                                                <div class="card-body">
                                                    <div class="tab-content no-padding">
                                                        @foreach ($gateways as $gateway)
                                                            <div @class([
                                                                'tab-pane fade',
                                                                'show active' => $loop->first ? true : false,
                                                            ])
                                                                id="{{ str_replace(' ', '-', $gateway->name) }}">
                                                                <form
                                                                    action="{{ route('admin.gateways.update', $gateway->id) }}"
                                                                    method="post" class="ajaxform_instant_reload">
                                                                    @csrf
                                                                    @method('put')

                                                                    <div class="row">
                                                                        <div class="col-11 align-self-center mb-2">
                                                                            <label
                                                                                class="img-label">{{ __('GATEWAY IMAGE') }}</label>
                                                                            <input type="file" name="image"
                                                                                class="form-control">
                                                                        </div>

                                                                        <div class="col-1 align-self-center mb-2">
                                                                            <img src="{{ asset($gateway->image) }}"
                                                                                class="img-fluid" alt="">
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('GATEWAY NAME') }}</label>
                                                                            <input type="text" name="name"
                                                                                value="{{ $gateway->name }}"
                                                                                required class="form-control">
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Gateway Charge') }}</label>
                                                                            <input type="number" step="any" name="charge" value="{{ $gateway->charge }}" class="form-control">
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Minimum Transaction Amount') }}</label>
                                                                            <input type="number" step="any" name="min_amount" value="{{ $gateway->min_amount }}" class="form-control">
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Maximum Transaction Amount') }}</label>
                                                                            <input type="number" step="any" name="max_amount" value="{{ $gateway->max_amount }}" class="form-control">
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Gateway Currency') }}</label>
                                                                            <div
                                                                                class="gpt-up-down-arrow position-relative">
                                                                                <select class="form-control"
                                                                                    required name="currency_id">
                                                                                    @foreach ($currencies as $currency)
                                                                                        <option
                                                                                            @selected($gateway->currency_id == $currency->id)
                                                                                            value="{{ $currency->id }}">
                                                                                            {{ $currency->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <span></span>
                                                                            </div>
                                                                        </div>

                                                                        @if (!$gateway->is_manual)
                                                                            @foreach ($gateway->data as $key => $data)
                                                                                <div class="col-12 mb-2">
                                                                                    <label>{{ strtoupper(str_replace('_', ' ', $key)) }}</label>
                                                                                    <input type="text"
                                                                                        name="data[{{ $key }}]"
                                                                                        value="{{ $data }}"
                                                                                        required
                                                                                        class="form-control">
                                                                                </div>
                                                                            @endforeach

                                                                            <div class="col-12 mb-2">
                                                                                <label>{{ __('Gateway Mode') }}</label>
                                                                                <div
                                                                                    class="gpt-up-down-arrow position-relative">
                                                                                    <select class="form-control"
                                                                                        required name="mode">
                                                                                        <option
                                                                                            @selected($gateway->mode == 'Sandbox')
                                                                                            value="Sandbox">
                                                                                            {{ __('Sandbox') }}
                                                                                        </option>
                                                                                        <option
                                                                                            @selected($gateway->mode == 'Live')
                                                                                            value="Live">
                                                                                            {{ __('Live') }}
                                                                                        </option>
                                                                                    </select>
                                                                                    <span></span>
                                                                                </div>
                                                                            </div>
                                                                        @endif

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Status') }}</label>
                                                                            <div
                                                                                class="gpt-up-down-arrow position-relative">
                                                                                <select class="form-control"
                                                                                    required name="status">
                                                                                    <option
                                                                                        @selected($gateway->mode == 1)
                                                                                        value="1">
                                                                                        {{ __('Active') }}
                                                                                    </option>
                                                                                    <option
                                                                                        @selected($gateway->mode == 0)
                                                                                        value="0">
                                                                                        {{ __('Deactive') }}
                                                                                    </option>
                                                                                </select>
                                                                                <span></span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-12 mb-2">
                                                                            <label>{{ __('Is Manual') }}</label>
                                                                            <div
                                                                                class="gpt-up-down-arrow position-relative">
                                                                                <select class="form-control"
                                                                                    required name="is_manual">
                                                                                    <option
                                                                                        @selected($gateway->is_manual == 1)
                                                                                        value="1">
                                                                                        {{ __('Yes') }}
                                                                                    </option>
                                                                                    <option
                                                                                        @selected($gateway->is_manual == 0)
                                                                                        value="0">
                                                                                        {{ __('No') }}
                                                                                    </option>
                                                                                </select>
                                                                                <span></span>
                                                                            </div>
                                                                        </div>

                                                                        @if ($gateway->is_manual)
                                                                            <div class="col-12 mb-2">
                                                                                <label>{{ __('Bank Name') }}</label>
                                                                                <input type="text" step="any"
                                                                                    name="manual_data[bank_info][bank_name]"
                                                                                    value="{{ $gateway->manual_data['bank_info']['bank_name'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>

                                                                            <div class="col-12 mb-2">
                                                                                <label>{{ __('Account Holder Name') }}</label>
                                                                                <input type="text" step="any"
                                                                                    name="manual_data[bank_info][acc_holder]"
                                                                                    value="{{ $gateway->manual_data['bank_info']['acc_holder'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>

                                                                            <div class="col-12 mb-2">
                                                                                <label>{{ __('Account Number') }}</label>
                                                                                <input type="text" step="any"
                                                                                    name="manual_data[bank_info][acc_number]"
                                                                                    value="{{ $gateway->manual_data['bank_info']['acc_number'] ?? '' }}"
                                                                                    class="form-control">
                                                                            </div>
                                                                        @endif

                                                                        <div
                                                                            class="col-12 mb-2 {{ $gateway->is_manual ? '' : 'd-none' }}">
                                                                            <div class="manual-rows">
                                                                                @foreach ($gateway->manual_data['label'] ?? [] as $key => $row)
                                                                                    <div class="row row-items">
                                                                                        <div class="col-sm-5">
                                                                                            <label
                                                                                                for="">{{ __('Label') }}</label>
                                                                                            <input type="text"
                                                                                                name="manual_data[label][]"
                                                                                                value="{{ $row }}"
                                                                                                class="form-control"
                                                                                                required
                                                                                                placeholder="{{ __('Enter label name') }}">
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                            <label
                                                                                                for="">{{ __('Select Required/Optional') }}</label>
                                                                                            <div
                                                                                                class="gpt-up-down-arrow position-relative">
                                                                                                <select
                                                                                                    class="form-control"
                                                                                                    required
                                                                                                    name="manual_data[is_required][]">
                                                                                                    <option
                                                                                                        @selected($gateway->manual_data['is_required'][$key] == 1)
                                                                                                        value="1">
                                                                                                        {{ __('Required') }}
                                                                                                    </option>
                                                                                                    <option
                                                                                                        @selected($gateway->manual_data['is_required'][$key] == 0)
                                                                                                        value="0">
                                                                                                        {{ __('Optional') }}
                                                                                                    </option>
                                                                                                </select>
                                                                                                <span></span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div
                                                                                            class="col-sm-2 align-self-center mt-3">
                                                                                            <button type="button"
                                                                                                class="btn text-danger trash remove-btn-features"><i
                                                                                                    class="fas fa-trash"></i></button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-12 mt-2">
                                                                                    <a href="javascript:void(0)"
                                                                                        class="fw-bold primary add-new-item"><i
                                                                                            class="fas fa-plus-circle"></i>{{ __('Add new row') }}</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>


                                                                        <div class="col-12 mb-2">
                                                                            <label
                                                                                for="instructions">{{ __('INSTRUCTIONS') }}</label>
                                                                            <textarea name="instructions" class="form-control" placeholder="{{ __('Enter payment instructions here') }}">{{ $gateway->instructions }}</textarea>
                                                                        </div>

                                                                        <div class="col-lg-12">
                                                                            @can('payment-gateway-update')
                                                                                <div
                                                                                    class="button-group text-center mt-4 d-flex align-items-center justify-content-center">
                                                                                    <button
                                                                                        class="theme-btn m-2 submit-btn"><i
                                                                                            class="far fa-save me-1"></i>{{ __('Save') }}</button>
                                                                                </div>
                                                                            @endcan

                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
