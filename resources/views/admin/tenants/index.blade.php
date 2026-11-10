@extends('layouts.admin.master')

@section('title')
    {{ __('Tenant List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>
                        {{ request('status') == 'active' ? __('Active Tenant') : (request('status') == 'expired' ? __('Expire Tenant') : __('All Tenant')) }}
                    </h3>
                    @can('tenants-create')
                        <a href="{{ route('admin.tenants.create') }}" class="modal-btn d-print-none">
                            <i class="fal fa-plus-circle"></i>
                            {{ __('Add Tenant') }}
                        </a>
                    @endcan
                </div>
                <div class="woodland-card">

                    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <form action="{{ route('admin.tenants.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center gap-3 flex-wrap"
                                table="#tenant-datas">
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
                        </div>

                        <div class="d-flex align-items-center gap-3 d-print-none">
                            <form action="{{ route('admin.tenants.filter') }}" method="post"
                                class="filter-form form-inline d-flex align-items-center gap-3 flex-wrap"
                                table="#tenant-datas">
                                @csrf
                                <div class="position-relative d-print-none">
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                    <input class="search-input"  placeholder="{{ __('Search') }}" name="search" aria-label="Search">
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
                                    <li>
                                        <a class="dropdown-item export-drop-item" href="{{ route('admin.tenants.csv', ['status' => request('status')]) }}">
                                            <img src="{{ asset('backend/assets/img/icon/csv.svg') }}" /> {{__('Csv')}}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-drop-item" href="{{ route('admin.tenants.excel', ['status' => request('status')]) }}">
                                            <img src="{{ asset('backend/assets/img/icon/esv.svg') }}" /> {{__('Excel')}}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-drop-item print-window">
                                            <img src="{{ asset('backend/assets/img/icon/print.svg') }}" /> {{__('Print')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="delete-item delete-show d-none">
                        <div class="delete-item-show">
                            <p class="fw-bold"><span class="selected-count"></span> {{ __('items show') }}</p>
                            <button data-bs-toggle="modal" class="trigger-modal" data-bs-target="#multi-delete-modal" data-url="{{ route('admin.tenants.delete-all') }}">{{ __('Delete') }}</button>
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
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Id') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Property Name') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="tenant-datas">
                                @include('admin.tenants.datas')
                            </tbody>
                        </table>
                    </div>
                    <div class="woodladn-pagination-wrapper">
                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{ $tenants->links() }}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('admin.component.multi-delete-modal')
@endpush
