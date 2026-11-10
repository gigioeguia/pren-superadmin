@extends('layouts.admin.master')

@section('title')
    {{ __('Edit Withdraw Method') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">
                    <div class="woodland-card-header border-bottom pb-3">
                        <h3>{{ __('Edit Withdraw Method') }}</h3>
                    </div>

                    <form action="{{ route('admin.withdraw-methods.update', $withdrawMethod->id) }}" method="POST" enctype="multipart/form-data" class="ajaxform_instant_reload">
                        @csrf
                        @method('put')
                        <div class="woodland-form-wrapper">
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <label for="first-name">{{ __('Payment Method Name') }}</label>
                                    <input type="text" name="name" value="{{ $withdrawMethod->name }}"
                                        class="form-control" placeholder="{{ __('Enter Name') }}">
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label for="city">{{ __('Currency') }}</label>
                                    <select class="form-control" required name="currency_id">
                                        <option value="">{{ __('Select a one') }}</option>
                                        @foreach ($currencies as $currency)
                                            <option @selected($withdrawMethod->currency_id == $currency->id) value="{{ $currency->id }}">
                                                {{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label for="last-name">{{ __('Minimum Amount') }}</label>
                                    <input type="number" name="min_amount" value="{{ $withdrawMethod->min_amount }}"
                                        class="form-control" placeholder="{{ __('Enter min amount') }}">
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label for="last-name">{{ __('Maximum Amount') }}</label>
                                    <input type="number" name="max_amount" value="{{ $withdrawMethod->max_amount }}"
                                        class="form-control" placeholder="{{ __('Enter max amount') }}">
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label for="phone">{{ __('Withdraw Charge') }}</label>
                                    <div class="form-input-group">
                                        <select class="form-control" name="charge_type">
                                            <option value="fixed" @if ($withdrawMethod->charge_type == 'fixed') selected @endif>
                                                {{ __('Fixed') }}</option>
                                            <option value="percentage" @if ($withdrawMethod->charge_type == 'percentage') selected @endif>
                                                {{ __('Percentage') }}</option>
                                        </select>
                                        <input type="number" name="charge" value="{{ $withdrawMethod->charge }}"
                                            class="form-control" placeholder="Enter charge">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label for="last-name">{{ __('Instruction') }}</label>
                                    <input type="text" name="instructions" class="form-control"
                                        value="{{ $withdrawMethod->instructions }}" placeholder="{{ __('Enter instruction') }}">
                                </div>


                                <div class="col-12 mb-2">
                                    <div class="row-for-method">
                                        @foreach ($withdrawMethod->meta['label'] ?? [] as $index => $label)
                                            <div class="row row-method">
                                                <div class="col-5 col-sm-6">
                                                    <label for="">{{ __('Label') }}</label>
                                                    <input type="text" name="meta[label][]" value="{{ $label }}"
                                                        class="form-control" placeholder="{{ __('Enter label name') }}" required>
                                                </div>

                                                <div class="col-5 col-sm-5">
                                                    <label for="">{{ __('Input') }}</label>
                                                    <input type="text" name="meta[input][]"
                                                        value="{{ $withdrawMethod->meta['input'][$index] ?? '' }}"
                                                        class="form-control" placeholder="{{ __('Enter input name') }}" required>
                                                </div>

                                                <div class="col-2 col-sm-1 align-self-center">
                                                    <button type="button" class="btn text-danger trash remove-withdraw-method"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="row">
                                        <div class="col-12 mt-2">
                                            <a href="javascript:void(0)"
                                                class="fw-bold d-flex align-items-center gap-2 withdraw-method-field">
                                                <i class="fas fa-plus-circle"></i>{{ __('Add new row') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="add-landlord-button-group">
                                        <button type="reset" class="btn  border-btn">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
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
