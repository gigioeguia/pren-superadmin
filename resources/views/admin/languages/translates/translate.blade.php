@extends('layouts.admin.master')

@section('title')
    {{ __('Language Settings') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{  __('Translations') }} <span>({{ __('For') }} {{ $lang->name }})</span></h3>
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0)" class="modal-btn" data-bs-toggle="modal" data-bs-target="#addTraslationModal">
                            {{ __('Add Translation') }}
                        </a>
                        <button type="submit" class="save-change-btn submit-btn">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
                <div class="woodland-card">
                    <form action="{{ route('admin.translations.bulk-update', $lang->code) }}" method="post" class="ajaxform">
                        <div class="">
                            <div class="">
                                <div class="traslation-alert">🔄️ {{ __('Please reload the page after saving translations to apply the changes.') }}</div>
                                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div></div>
                                        <!-- Search -->
                                        <div class="filter-form-lang form-inline d-flex align-items-center gap-3 flex-wrap">
                                            <div class="position-relative">
                                                <input class="search-input" type="text" placeholder="{{ __('Search') }}" name="search" value="{{ request('search') }}" data-url="{{ route('admin.languages.show', $lang->code) }}">
                                                <input type="hidden" id="update-url" value="{{ route('admin.translations.update', $lang->code) }}">
                                                <i class="fas fa-search position-absolute search-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div id="translate-data">
                                @include('admin.languages.translates.datas')
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('modal')
    <div class="modal modal-md fade" id="addTraslationModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Add Translation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('admin.translations.store') }}" method="post" class="add_new_translation">
                        <div>
                            <label>{{ __('Key') }}</label>
                            <input type="text" name="key" required class="form-control new_key" placeholder="{{ __('Enter Key') }}">
                            <input type="hidden" name="lang_code" value="{{ $lang->code }}">
                        </div>

                            <div>
                            <label>{{ __('Value') }}</label>
                            <input type="text" name="value" required class="form-control new_value" placeholder="{{ __('Enter Value') }}">
                        </div>

                        <div class="add-landlord-button-group">
                            <button type="button" class="btn border-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            @can('languages-create')
                                <button type="submit" class="btn theme-btn submit-btn">{{ __('Save') }}</button>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush

