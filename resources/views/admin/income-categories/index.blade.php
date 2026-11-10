@extends('layouts.admin.master')

@section('title')
    {{ __('Income Category List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Income Categories List') }}</h3>
                    @can('income-categories-create')
                        <a href="" data-bs-toggle="modal" data-bs-target="#add-income-categories-modal" class="modal-btn"><i
                                class="fal fa-plus-circle"></i>{{ __('Add New') }}</a>
                    @endcan
                </div>
                <div class="woodland-card">

                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3 ">
                        <div class="d-flex align-items-center justify-content-between w-100 gap-2 flex-wrap">
                            <form action="{{ route('admin.income-categories.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center justify-content-between  gap-3 flex-wrap"
                                table="#income-categories-data">
                                @csrf

                               <div class="d-flex align-items-center gap-2 d-print-none custom-select">
                                <select name="per_page" class="select-drop">
                                    <option value="10">{{ __('Show- 10') }}</option>
                                    <option value="25">{{ __('Show- 25') }}</option>
                                    <option value="50">{{ __('Show- 50') }}</option>
                                    <option value="100">{{ __('Show- 100') }}</option>
                                </select>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 5.50004C13 5.50004 9.31758 10.5 8 10.5C6.68233 10.5 3 5.5 3 5.5" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            </form>

                        <form action="{{ route('admin.income-categories.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center justify-content-between  gap-3 flex-wrap"
                                table="#income-categories-data">
                                @csrf
                                <div class="position-relative">
                                    <input class="search-input" type="" placeholder="{{ __('Search') }}"
                                        name="search" aria-label="Search">
                                    <i class="fas fa-search position-absolute search-icon"></i>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="delete-item delete-show d-none">
                        <div class="delete-item-show">
                            <p class="fw-bold"><span class="selected-count"></span> {{ __('items show') }}</p>
                            <button data-bs-toggle="modal" class="trigger-modal" data-bs-target="#multi-delete-modal"
                                data-url="{{ route('admin.income-categories.delete-all') }}">{{ __('Delete') }}</button>
                        </div>
                    </div>

                    <div class="responsibe-table">
                        <table class="table woodland-table">
                            <thead>
                                <tr>
                                    <th class="w-60">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="checkbox" class="select-all-delete  multi-delete">
                                        </div>
                                    </th>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="income-categories-data">
                                @include('admin.income-categories.datas')
                            </tbody>
                        </table>
                    </div>
                    <div class="woodladn-pagination-wrapper">

                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{ $income_categories->links() }}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('admin.income-categories.create')
    @include('admin.income-categories.edit')
    @include('admin.component.multi-delete-modal')
@endpush
