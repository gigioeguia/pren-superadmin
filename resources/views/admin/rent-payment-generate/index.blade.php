@extends('layouts.admin.master')

@section('title')
    {{ __('Rent Payment Generate') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Rent Payment List') }}</h3>
                </div>
                <div class="woodland-card">

                    <form action="{{ route('admin.rent-payment-generate.store') }}" method="post"
                        enctype="multipart/form-data" class="ajaxform_instant_reload salary-generate-input">
                        @csrf
                        <div class="">
                            <div class="d-flex align-items-center gap-2  pb-3">
                                <div class="">
                                    <select name="month" id="month" class="form-select eForm-select salary-month"
                                        required>
                                        <option value="">{{ __('Select a month') }}</option>
                                        <option @selected(date('m') == '01') value="january">{{ __('January') }}</option>
                                        <option @selected(date('m') == '02') value="february">{{ __('February') }}</option>
                                        <option @selected(date('m') == '03') value="march">{{ __('March') }}</option>
                                        <option @selected(date('m') == '04') value="april">{{ __('April') }}</option>
                                        <option @selected(date('m') == '05') value="may">{{ __('May') }}</option>
                                        <option @selected(date('m') == '06') value="june">{{ __('June') }}</option>
                                        <option @selected(date('m') == '07') value="july">{{ __('July') }}</option>
                                        <option @selected(date('m') == '08') value="august">{{ __('August') }}</option>
                                        <option @selected(date('m') == '09') value="september">{{ __('September') }}</option>
                                        <option @selected(date('m') == '10') value="october">{{ __('October') }}</option>
                                        <option @selected(date('m') == '11') value="november">{{ __('November') }}</option>
                                        <option @selected(date('m') == '12') value="december">{{ __('December') }}</option>
                                    </select>
                                </div>

                                <div class="">

                                    <select name="year" id="year" class="form-select eForm-select salary-year"
                                        required>
                                        <option value="">{{ __('Select a year') }}</option>
                                        @for ($i = date('Y'); $i >= 2015; $i--)
                                            <option @selected($i == date('Y')) value="{{ $i }}">
                                                {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @can('rent-payment-generate-create')
                                <div class="">
                                    <button type="submit" class="generate-btn modal-btn ">{{ __('Generate') }}</button>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </form>
                    <div class="responsibe-table table-height">
                        <table class="table woodland-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}.</th>
                                    <th>{{ __('Request Date') }}</th>
                                    <th>{{ __('Landlord Name') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Fee') }}</th>
                                    <th>{{ __('Payment Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="withdraw-request-data">
                                @include('admin.rent-payment-generate.datas')
                            </tbody>
                        </table>
                    </div>

                    <div class="woodladn-pagination-wrapper">
                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{ $rent_payments->links() }}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
