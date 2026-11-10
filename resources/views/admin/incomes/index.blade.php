@extends('layouts.admin.master')

@section('title')
    {{ __('Income List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">

                <div class="woodland-card-header">
                    <h3>{{ __('Income List') }}</h3>
                    @can('incomes-create')
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add-income-modal" class="modal-btn d-print-none">
                            <i class="fal fa-plus-circle"></i>{{ __('Add New') }}
                        </a>
                    @endcan
                </div>

                <div class="mb-4 d-flex loss-flex  gap-3 loss-profit-container d-print-none">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="profit-card p-3 text-white">
                            <p class="stat-title">{{ __('Total Amount') }}</p>
                            <p class="stat-value mt-2" id="total_income">{{ currency_format($total_income ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <div class="woodland-card">
                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3 d-print-none">
                        <div class="d-flex align-items-end gap-3 flex-wrap">
                            <form action="{{ route('admin.incomes.filter') }}" method="post"
                                class="filter-form-income form-inline d-flex align-items-center gap-3 flex-wrap"
                                table="#incomes-data">
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

                            <form action="{{ route('admin.incomes.filter') }}" method="post" class="filter-form-income form-inline d-flex align-items-end  gap-3 flex-wrap" table="#incomes-data">
                                @csrf

                                <div class="d-flex align-items-center gap-2">
                                    <select name="custom_days" class="select-drop">
                                        <option value="#">{{ __('Select One') }}</option>
                                        <option value="today">{{ __('Today') }}</option>
                                        <option value="yesterday">{{ __('Yesterday') }}</option>
                                        <option value="last_seven_days">{{ __('Last 7 Days') }}</option>
                                        <option value="last_thirty_days">{{ __('Last 30 Days') }}</option>
                                        <option value="current_month">{{ __('Current Month') }}</option>
                                        <option value="last_month">{{ __('Last Month') }}</option>
                                        <option value="current_year">{{ __('Current Year') }}</option>
                                        <option value="custom_date">{{ __('Custom Date') }}</option>
                                    </select>
                                    <span></span>
                                </div>

                                <div id="custom-date-range" class="d-none d-flex flex-wrap gap-3">
                                    <div>
                                        <label>{{ __('From Date') }}</label>
                                        <input type="date" name="from_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="custom-date-input">
                                    </div>

                                    <div>
                                        <label>{{ __('To Date') }}</label>
                                        <input type="date" name="to_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="custom-date-input">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <form action="{{ route('admin.incomes.filter') }}" method="post" class="filter-form-income form-inline d-flex align-items-end  gap-3 flex-wrap" table="#incomes-data">
                                @csrf

                                <div class="position-relative">
                                    <input class="search-input" type="" placeholder="{{ __('Search') }}" name="search"
                                        aria-label="Search">
                                    <i class="fas fa-search position-absolute search-icon"></i>
                                </div>
                            </form>
                            <div class="dropdown">
                                <button class="btn export-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img src="{{ asset('backend/assets/img/icon/export-download.svg') }}" alt="">
                                    {{__('Export')}}
                                    <img src="{{ asset('backend/assets/img/icon/export-arrow.svg') }}" alt="">
                                </button>
                                <ul class="dropdown-menu export-dropdown">
                                    <li><a class="dropdown-item export-drop-item" href="{{ route('admin.incomes.csv') }}"><img src="{{ asset('backend/assets/img/icon/csv.svg') }}" /> {{__('Csv')}} </a></li>
                                    <li><a class="dropdown-item export-drop-item" href="{{ route('admin.incomes.excel') }}"><img src="{{ asset('backend/assets/img/icon/esv.svg') }}" /> {{__('Excel')}} </a></li>
                                    <li><a class="dropdown-item export-drop-item print-window" href="#"><img src="{{ asset('backend/assets/img/icon/print.svg') }}" /> {{__('Print')}} </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="delete-item delete-show d-none">
                        <div class="delete-item-show">
                            <p class="fw-bold"><span class="selected-count"></span> {{ __('items show') }}</p>
                            <button data-bs-toggle="modal" class="trigger-modal" data-bs-target="#multi-delete-modal" data-url="{{ route('admin.incomes.delete-all') }}">{{ __('Delete') }}</button>
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
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('income Category') }}</th>
                                    <th>{{ __('Payment Type') }}</th>
                                    <th>{{ __('Reference Number') }}</th>
                                    <th>{{ __('income Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="incomes-data">
                                @include('admin.incomes.datas')
                            </tbody>
                        </table>
                    </div>
                    <div class="woodladn-pagination-wrapper">

                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{ $incomes->links() }}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('admin.incomes.create')
    @include('admin.incomes.edit')
    @include('admin.component.multi-delete-modal')
@endpush
