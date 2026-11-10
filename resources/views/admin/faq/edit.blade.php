@extends('layouts.admin.master')

@section('title')
    {{ __('Edit Faq') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Edit Faq') }}</h3>
            </div>
            <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST" class="ajaxform_instant_reload">
                @csrf
                @method('put')
                <div class="row">
                    <div class="col-lg-6 mt-2">
                        <label class="required">{{ __('Question') }}</label>
                        <input type="text" name="question" required class="form-control"
                            placeholder="{{ __('Enter Question') }}" value="{{ $faq->question }}">
                    </div>

                    <div class="col-lg-6 mt-2">
                        <label>{{ __('Status') }}</label>
                        <div class="gpt-up-down-arrow position-relative">
                            <select name="status" class="form-control select-dropdown">
                                <option {{ $faq->status == '1' ? 'selected' : '' }} value="1">{{ __('Active') }}
                                </option>
                                <option {{ $faq->status == '0' ? 'selected' : '' }} value="0">{{ __('Deactive') }}
                                </option>
                            </select>
                            <span></span>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-2">
                        <label class="required">{{ __('Answer') }}</label>
                        <textarea name="answer" class="form-control" placeholder="{{ __('Enter answer') }}" required> {{ $faq->answer }} </textarea>
                    </div>

                    <div class="add-landlord-button-group">
                        <button type="button" class="btn border-btn">@lang('Cancel')</button>
                        <button type="submit" class="btn theme-btn submit-btn">@lang('Save')</button>
                    </div>
            </form>
        </div>
    </div>
@endsection
