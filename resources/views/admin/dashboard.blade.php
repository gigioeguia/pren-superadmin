@extends('layouts.admin.master')

@section('title')
    {{ __('Dashboard') }}
@endsection

@section('main_content')
    <div class="woodland-section-container ">

        <div class="row">
            <div class="col-xxl-8 col-xl-12">
                <div class="chart-container">
                    <div class="chart-stat-container">
                        <div class="stat-container">
                            <div class="stat-content mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.5 9.5H5.5M10.5 9.5H9.5M6.5 6.5H5.5M10.5 6.5H9.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" />
                                        <path d="M18.5 15.5H17.5M18.5 11.5H17.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" />
                                        <path d="M14 8.5V22.5H18C19.8856 22.5 20.8284 22.5 21.4142 21.9142C22 21.3284 22 20.3856 22 18.5V12.5C22 10.6144 22 9.67157 21.4142 9.08579C20.8284 8.5 19.8856 8.5 18 8.5H14ZM14 8.5C14 5.67157 14 4.25736 13.1213 3.37868C12.2426 2.5 10.8284 2.5 8 2.5C5.17157 2.5 3.75736 2.5 2.87868 3.37868C2 4.25736 2 5.67157 2 8.5V10.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8.02527 14.4545C8.02527 15.5583 7.12987 16.453 6.02533 16.453C4.9208 16.453 4.02539 15.5583 4.02539 14.4545C4.02539 13.3508 4.9208 12.4561 6.02533 12.4561C7.12987 12.4561 8.02527 13.3508 8.02527 14.4545Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" />
                                        <path d="M2.06982 20.7098C3.12817 19.0817 4.80886 18.4715 6.02491 18.4727C7.24097 18.474 8.8724 19.0817 9.93075 20.7098C9.99917 20.8151 10.018 20.9447 9.95628 21.0541C9.70877 21.4927 8.94028 22.363 8.38522 22.422C7.74746 22.4898 6.07914 22.4993 6.0262 22.4996C5.97322 22.4993 4.2534 22.4898 3.61535 22.422C3.06029 22.363 2.2918 21.4927 2.04429 21.0541C1.98254 20.9447 2.00139 20.8151 2.06982 20.7098Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" />
                                    </svg>
                                    <h4> {{__('Total Landlords')}} </h4>
                                </div>
                                <h5 id="total_landlord"></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="landlord_arrow"></span>

                                        <p class="amount" id="last_month_total_landlord"></p>
                                    </div>
                                    <h6 class="last-month"> {{__('From last month')}} </h6>
                                </div>
                            </div>
                            <div class="stat-content mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.6772 17.25C20.3329 17.25 20.8544 16.8374 21.3226 16.2605C22.2812 15.0795 20.7074 14.1357 20.1071 13.6735C19.497 13.2036 18.8157 12.9375 18.125 12.875M17.25 11.125C18.4581 11.125 19.4375 10.1456 19.4375 8.9375C19.4375 7.72938 18.4581 6.75 17.25 6.75" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round" />
                                        <path d="M4.32272 17.25C3.66707 17.25 3.14559 16.8374 2.67735 16.2605C1.71883 15.0795 3.29259 14.1357 3.89281 13.6735C4.50298 13.2036 5.18426 12.9375 5.875 12.875M6.3125 11.125C5.10438 11.125 4.125 10.1456 4.125 8.9375C4.125 7.72938 5.10438 6.75 6.3125 6.75" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round" />
                                        <path d="M8.57333 14.7223C7.67927 15.2751 5.33511 16.404 6.76286 17.8165C7.46031 18.5065 8.23708 19 9.21367 19H14.7864C15.763 19 16.5397 18.5065 17.2372 17.8165C18.6649 16.404 16.3208 15.2751 15.4267 14.7223C13.3301 13.4259 10.6698 13.4259 8.57333 14.7223Z" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M15.0625 8.0625C15.0625 9.75387 13.6914 11.125 12 11.125C10.3086 11.125 8.9375 9.75387 8.9375 8.0625C8.9375 6.37112 10.3086 5 12 5C13.6914 5 15.0625 6.37112 15.0625 8.0625Z" stroke="#4D4D4D" stroke-width="1.3" />
                                    </svg>

                                    <h4> {{__('Total Tenants')}} </h4>
                                </div>
                                <h5 id="total_tenant"></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="tenant_arrow"></span>
                                        <p class="amount" id="last_month_total_tenant"></p>
                                    </div>
                                    <h6 class="last-month"> {{__('From last month')}} </h6>
                                </div>
                            </div>
                            <div class="stat-content">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 5.11296C3 4.56029 3 4.28395 3.04289 4.05373C3.23304 3.03312 4.02565 2.23473 5.03889 2.0432C5.26745 2 5.54179 2 6.09048 2H17.9095C18.4582 2 18.7325 2 18.9611 2.0432C19.9743 2.23473 20.767 3.03312 20.9571 4.05373C21 4.28395 21 4.56029 21 5.11296C21 5.6552 21 5.92631 20.9685 6.17771C20.8309 7.27541 20.2512 8.26781 19.3654 8.92208C19.1625 9.07192 18.9272 9.20359 18.4566 9.46692L15.8851 10.9059C13.9861 11.9686 13.0365 12.5 12 12.5C10.9635 12.5 10.0139 11.9686 8.11486 10.9059L5.54338 9.46692C5.0728 9.20359 4.83752 9.07192 4.63465 8.92208C3.74885 8.26781 3.16914 7.27541 3.03152 6.17771C3 5.92631 3 5.6552 3 5.11296Z"
                                            stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" />
                                        <path d="M8 5V6M12 5V8M16 5V6" stroke="#4D4D4D" stroke-width="1.25"
                                            stroke-linecap="round" />
                                        <path
                                            d="M12.7774 13.6499L13.5693 15.2468C13.6773 15.4691 13.9653 15.6823 14.2083 15.7231L15.6436 15.9636C16.5615 16.1178 16.7775 16.7893 16.1161 17.4516L15.0002 18.5767C14.8112 18.7673 14.7077 19.1347 14.7662 19.3979L15.0857 20.7906C15.3377 21.893 14.7572 22.3195 13.7898 21.7433L12.4445 20.9403C12.2015 20.7952 11.801 20.7952 11.5536 20.9403L10.2082 21.7433C9.24533 22.3195 8.66039 21.8885 8.91236 20.7906L9.23183 19.3979C9.29032 19.1347 9.18683 18.7673 8.99785 18.5767L7.88198 17.4516C7.22505 16.7893 7.43653 16.1178 8.35443 15.9636L9.78977 15.7231C10.0282 15.6823 10.3162 15.4691 10.4242 15.2468L11.2161 13.6499C11.6481 12.7834 12.35 12.7834 12.7774 13.6499Z"
                                            stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                    <h4> {{__('Total Subscribers')}}  </h4>
                                </div>
                                <h5 id="total_subscribed"></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1 profit-amount">
                                        <span id="subscribed_arrow"></span>

                                        <p class="amount" id="last_month_total_subscribed"></p>
                                    </div>
                                    <h6 class="last-month"> {{__('From last month')}} </h6>
                                </div>

                            </div>
                        </div>
                        <div class="">
                            <div class="chart-header ">
                                <h3>@lang('Earning Statistic')</h3>
                                <select class="admin-account-chart-date">
                                    @for ($i = date('Y'); $i >= 2022; $i--)
                                        <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="dashboard-chart">
                                <div class=" d-flex align-items-center flex-wrap justify-content-center gap-3 pb-2 pt-2">
                                    <div class="total-balance">
                                        <p> {{__('Total Balance:')}} <span class="total_balance"></span></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <svg width="12" height="10" viewBox="0 0 12 10" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="6.5" width="5" height="10" rx="1" fill="#6200EA" />
                                            <rect x="0.5" y="4.5459" width="5" height="5.45455" rx="1"
                                                fill="#6200EA" />
                                        </svg>


                                        <p class="in-ex-label"> {{__('Income:')}} <strong
                                                class="profit-value label-value income_value"></strong></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <svg width="12" height="10" viewBox="0 0 12 10" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="6.5" y="-0.000228882" width="5" height="10" rx="1"
                                                fill="#FF715D" />
                                            <rect x="0.5" y="4.54567" width="5" height="5.45455" rx="1"
                                                fill="#FF715D" />
                                        </svg>

                                        <p class="in-ex-label"> {{__('Expense:')}} <strong class="loss-value label-value expense_value"></strong>
                                        </p>
                                    </div>
                                </div>
                                <canvas class="chart-css" id="incomeExpenseChart" width="400"
                                    height="200"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xxl-4 col-xl-12">
                <div class="chart-container property-chart-container">
                    <div class="chart-header">
                        <h3>@lang('Property Overview')</h3>
                        <select class="property-chart">
                            @for ($i = date('Y'); $i >= 2022; $i--)
                                <option @selected($i == date('Y')) value="{{ $i }}">{{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="dashboard-chart-property  align-items-center justify-content-center mt-2 mx-2">
                        <div class="position-relative">
                            <canvas class="overview-chart-css" id="overviewChart">
                            </canvas>
                            <div
                                class="position-absolute total-property text-center position-absolute top-50 start-50 translate-middle">
                                <h2 class="total_property"></h2>
                                <h4> {{__('Total Property')}} </h4>
                            </div>
                        </div>

                        <div class="overview-label-container">
                            <div class="d-flex align-items-center justify-content-center flex-column gap-1">
                                <img class="pdf-img" src="{{ asset('backend/assets/img/icon/pending-label.svg') }}"
                                    alt="">
                                <h5 class="property_pending"></h5>
                                <p> {{__('Pending')}} </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-center flex-column gap-1">
                                <img class="pdf-img" src="{{ asset('backend/assets/img/icon/approved-label.svg') }}"
                                    alt="">
                                <h5 class="property_approved"></h5>
                                <p> {{__('Approved')}} </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-center flex-column gap-1">
                                <img class="pdf-img" src="{{ asset('backend/assets/img/icon/rejected-icon.svg') }}"
                                    alt="">
                                <h5 class="property_rejected"></h5>
                                <p> {{__('Rejected')}} </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="chart-container mt-30">
                    <div class="">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="custom-tabs change-tab" id="customTabNav" data-withdraw-url="{{ route('admin.withdraw-request.index') }}" data-refund-url="{{ route('admin.refund-request.index') }}">
                                <div class="custom-tab active" data-bs-target="#tab1">
                                    {{__('Landlord Withdraw')}}  <span class="count-badge">{{ $withdraws->count() }}</span>
                                </div>
                                <div class="custom-tab" data-bs-target="#tab2">
                                    {{__('Refund Request')}}  <span class="count-badge">{{ $refund_requests->count() }}</span>
                                </div>
                            </div>
                            <a href="#" class="view-all change-view"> {{__('View All')}} <i class="far fa-chevron-right"></i></a>
                        </div>
                        <div class="tab-content pt-4">
                            <div class="tab-pane fade show active" id="tab1">
                                <div class="table-responsive added-properties-table">
                                    <table class="table woodland-table">
                                        <thead>
                                            <tr>
                                                <th>@lang('SL.')</th>
                                                <th class="text-start">@lang('Date & Time')</th>
                                                <th class="text-start">@lang('Landlord Name')</th>
                                                <th class="text-start">@lang('Amount')</th>
                                                <th class="text-start">@lang('Fee')</th>
                                                <th class="text-start">@lang('Withdraw Method')</th>
                                                <th class="text-start">@lang('Payment Status')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($withdraws as $withdraw)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>
                                                <td class="text-start">{{ formatted_date($withdraw->created_at) }} {{ formatted_time($withdraw->created_at) }}</td>
                                                <td class="text-start">{{ $withdraw->user->name ?? ''}}</td>
                                                <td class="text-start">{{ currency_format($withdraw->amount ?? 0) }}</td>
                                                <td class="text-start">{{ currency_format($withdraw->account_info->withdraw_method->charge ?? 0) }}</td>
                                                <td class="text-start">{{ $withdraw->account_info->infos['bank_name'] ?? '' }}</td>
                                                @if ($withdraw->status == 'pending')
                                                <td class="text-warning  text-start">
                                                    <div class="padding-status"> {{__('Pending')}} </div>
                                                </td>
                                                @elseif ($withdraw->status == 'approved')
                                                <td class="text-warning text-start">
                                                    <div class="approved-status"> {{__('Approved')}} </div>
                                                </td>
                                                @elseif ($withdraw->status == 'rejected')
                                                <td class="text-warning text-start">
                                                    <div class="rejected-status"> {{__('Rejected')}} </div>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="tab2">
                                <div class="table-responsive added-properties-table ">
                                    <table class="table woodland-table">
                                        <thead>
                                            <tr>
                                                <th>@lang('SL.')</th>
                                                <th class="text-start">@lang('Date & Time')</th>
                                                <th class="text-start">@lang('Invoice ID')</th>
                                                <th class="text-start">@lang('Tenant Name')</th>
                                                <th class="w-120 text-start">@lang('Landlord Name')</th>
                                                <th class="w-120 text-start">@lang('Property Name')</th>
                                                <th class="w-120 text-start">@lang('Property ID')</th>
                                                <th class="w-120 text-start">@lang('Payment Status')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($refund_requests as $refund_request)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>
                                                <td class="text-start">{{ formatted_date($refund_request->created_at ) }} - {{ formatted_time($refund_request->created_at ) }}</td>
                                                <td class="text-start">{{ $refund_request->invoice_no ?? '' }}</td>
                                                <td class="text-start">{{ $refund_request->tenant->name ?? '' }}</td>
                                                <td class="text-start">{{ $refund_request->landlord->name ?? '' }}</td>
                                                <td class="text-start">{{ $refund_request->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
                                                <td class="text-start">{{ $refund_request->property->puid ?? ''}}</td>
                                                @if ($refund_request->status == 'unpaid')
                                                <td class="text-warning  text-start">
                                                    <div class="padding-status"> {{__('Pending')}} </div>
                                                </td>
                                                @elseif ($refund_request->status == 'paid')
                                                <td class="text-warning text-start">
                                                    <div class="approved-status"> {{__('Approved')}} </div>
                                                </td>
                                                @elseif ($refund_request->status == 'rejected')
                                                <td class="text-warning text-start">
                                                    <div class="rejected-status"> {{__('Rejected')}} </div>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="{{ route('admin.getDashboardData') }}" id="get-dashboard">
    <input type="hidden" value="{{ route('admin.getPropertyData') }}" id="get-property-data">
    <input type="hidden" value="{{ route('admin.getAccountData') }}" id="admin-get-account-data">

    @php
    $currency = currency();
    @endphp
    {{-- Hidden input fields to store currency details --}}
    <input type="hidden" id="currency_symbol" value="{{ $currency->symbol }}">
    <input type="hidden" id="currency_position" value="{{ $currency->position }}">
    <input type="hidden" id="currency_code" value="{{ $currency->code }}">
@endsection

@push('script')
    <script src="{{ asset('assets/plugins/custom/admin-dashboard.js') }}"></script>
@endpush
