@extends('layouts.admin.master')

@section('title')
    {{ __('Term & Condition Settings') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Term & Condition Settings') }}</h3>
                </div>
                <div class="woodland-card">

                    <div class="order-form-section p-16">
                        <form action="{{ route('admin.term-conditions.store') }}" method="post" enctype="multipart/form-data"
                            class="ajaxform_instant_reload">
                            @csrf
                            <div class="add-suplier-modal-wrapper d-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>{{ __('Title') }}</label>
                                        <input type="text" name="term_title" value="{{ $term_condition->value['term_title'] ?? '' }}" placeholder="{{ __('Enter Title') }}" required class="form-control">
                                    </div>
                                    <div class="col-lg-12">
                                        <label>{{ __('Description') }}</label>
                                        <textarea name="description" class=" w-100 custom-textarea summernote" required rows="7" placeholder="{{ __('Enter Description') }}">{{ $term_condition->value['description'] ?? '' }}</textarea>
                                    </div>
                                    @can('term-conditions-update')
                                        <div class="col-lg-12">
                                            <div class="text-center mt-5">
                                                <button type="submit" class="theme-btn m-2 submit-btn">{{ __('Update') }}</button>
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
    </div>
@endsection
