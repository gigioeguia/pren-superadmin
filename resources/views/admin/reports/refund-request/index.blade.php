@extends('layouts.admin.master')

@section('title')
    {{ __('Refund Request Report') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Refund Request Report') }}</h3>
                </div>
                <div class="woodland-card">

                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <form action="{{ route('admin.refund-request.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center gap-3 flex-wrap"
                                table="#refund-report-data">
                                @csrf

                                <div class="d-flex align-items-center gap-2 d-print-none custom-select">
                                    <select name="per_page" class="select-drop">
                                        <option value="10">{{ __('Show- 10') }}</option>
                                        <option value="25">{{ __('Show- 25') }}</option>
                                        <option value="50">{{ __('Show- 50') }}</option>
                                        <option value="100">{{ __('Show- 100') }}</option>
                                    </select>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13 5.50004C13 5.50004 9.31758 10.5 8 10.5C6.68233 10.5 3 5.5 3 5.5"
                                            stroke="#666666" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="row g-4 align-items-start">
                                    <div class="col-sm-6 d-flex align-items-center gap-1 flex-wrap flex-md-nowrap">
                                        <label for="startDate" class="form-label"> {{ __('From') }} </label>
                                        <input type="date" name="from_date" class="form-control date-range"
                                            value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-sm-6 d-flex align-items-center gap-1 flex-wrap flex-md-nowrap">
                                        <label for="endDate" class="form-label"> {{ __('To') }} </label>
                                        <input type="date" name="to_date" class="form-control date-range"
                                            value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3 d-print-none">
                            <form action="{{ route('admin.refund-request.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center gap-3 flex-wrap"
                                table="#refund-report-data">
                                @csrf
                                <div class="position-relative d-print-none">
                                    <input class="search-input" type="" placeholder="{{ __('Search') }}" name="search"
                                        aria-label="Search">
                                    <i class="fas fa-search position-absolute search-icon"></i>
                                </div>
                            </form>

                            <div class="dropdown">
                                <button class="btn export-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img src="{{ asset('backend/assets/img/icon/export-download.svg') }}" alt="">
                                    {{ __('Export') }}
                                    <img src="{{ asset('backend/assets/img/icon/export-arrow.svg') }}" alt="">
                                </button>
                                <ul class="dropdown-menu export-dropdown">
                                    <li><a class="dropdown-item  export-drop-item"
                                            href="{{ route('admin.refund-request-reports.csv') }}"><img
                                                src="{{ asset('backend/assets/img/icon/csv.svg') }}" />
                                            {{ __('Csv') }} </a></li>
                                    <li><a class="dropdown-item export-drop-item"
                                            href="{{ route('admin.refund-request-reports.excel') }}"><img
                                                src="{{ asset('backend/assets/img/icon/esv.svg') }}" />
                                            {{ __('Excel') }} </a></li>
                                    <li><a class="dropdown-item export-drop-item print-window"
                                            href="#"><img src="{{ asset('backend/assets/img/icon/print.svg') }}" />
                                            {{ __('Print') }} </a></li>
                                </ul>
                            </div>
                        </div>


                    </div>

                    <div class="responsibe-table table-height">
                        <table class="table woodland-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Invoice ID') }}</th>
                                    <th>{{ __('Tenant Name') }}</th>
                                    <th>{{ __('Landlord Name') }}</th>
                                    <th>{{ __('Property Name') }}</th>
                                    <th>{{ __('Property ID') }}</th>
                                    <th>{{ __('Total Deposit Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="refund-report-data">
                                @include('admin.reports.refund-request.datas')
                            </tbody>
                        </table>
                    </div>

                    <div class="woodladn-pagination-wrapper">
                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{ $refund_requests->links() }}</li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
