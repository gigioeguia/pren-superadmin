@extends('layouts.admin.master')

@section('title')
    {{ __('Add Faq') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Add Faq') }}</h3>
            </div>
            <form action="{{ route('admin.faqs.store') }}" method="POST" class="ajaxform_instant_reload">
                @csrf
                <div class="row">
                    <div class="col-lg-6 mt-2">
                        <label class="required">{{ __('Question') }}</label>
                        <input type="text" name="question" required class="form-control"
                            placeholder="{{ __('Enter Question') }}">
                    </div>

                    <div class="col-lg-6 mt-2">
                        <label>{{ __('Status') }}</label>
                        <div class="gpt-up-down-arrow position-relative">
                            <select name="status" class="form-control select-dropdown">
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Deactive') }}</option>
                            </select>
                            <span></span>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-2">
                        <label class="required">{{ __('Question') }}</label>
                        <textarea name="answer" class="form-control" placeholder="{{ __('Enter answer') }}" required></textarea>
                    </div>

                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn">@lang('Cancel')</button>
                        <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
                    </div>
            </form>
        </div>
    </div>
@endsection
