@extends('layouts.landlord.master')

@section('title')
    {{ __('Landlord Dashboard') }}
@endsection

@section('main_content')
    <div class="woodland-section-container ">
        @if (env('DEMO_MODE') && auth()->user()->email == 'landlord@acnoo.com')
        <div class="alert alert-dismissible fade show text-center mb-3 text-white" role="alert">
            <b class="text-light">Note:</b> This is a demo account — data resets every hour for this account only. Some of module are disabled in this account, to get full access please please create your own account.
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="row">
            <div class="col-xxl-8 col-xl-12">
                <div class="chart-container">
                    <div class="chart-stat-container">
                        <div class="stat-container">
                            <div class="stat-content mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13 2L2 7" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 3V22H7C5.11438 22 4.17157 22 3.58579 21.4142C3 20.8284 3 19.8856 3 18V7" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 7L22 12" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 21.9997H17C18.8856 21.9997 19.8284 21.9997 20.4142 21.4139C21 20.8281 21 19.8853 21 17.9997V11.5" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M18 10V7" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 11H8M7 15H8" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 14H17" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16.5 22V18" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                    <h4> {{__('Total Property')}} </h4>
                                </div>
                                <h5 id="total_property"></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="property_arrow"></span>

                                        <p class="amount" id="last_month_total_property"></p>
                                    </div>
                                    <h6 class="last-month"> {{__('From last month')}} </h6>
                                </div>

                            </div>
                            <div class="stat-content mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.6772 17.25C20.3329 17.25 20.8544 16.8374 21.3226 16.2605C22.2812 15.0795 20.7074 14.1357 20.1071 13.6735C19.497 13.2036 18.8157 12.9375 18.125 12.875M17.25 11.125C18.4581 11.125 19.4375 10.1456 19.4375 8.9375C19.4375 7.72938 18.4581 6.75 17.25 6.75" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round"/>
                                        <path d="M4.32272 17.25C3.66707 17.25 3.14559 16.8374 2.67735 16.2605C1.71883 15.0795 3.29259 14.1357 3.89281 13.6735C4.50298 13.2036 5.18426 12.9375 5.875 12.875M6.3125 11.125C5.10438 11.125 4.125 10.1456 4.125 8.9375C4.125 7.72938 5.10438 6.75 6.3125 6.75" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round"/>
                                        <path d="M8.57333 14.7223C7.67927 15.2751 5.33511 16.404 6.76286 17.8165C7.46031 18.5065 8.23708 19 9.21367 19H14.7864C15.763 19 16.5397 18.5065 17.2372 17.8165C18.6649 16.404 16.3208 15.2751 15.4267 14.7223C13.3301 13.4259 10.6698 13.4259 8.57333 14.7223Z" stroke="#4D4D4D" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.0625 8.0625C15.0625 9.75387 13.6914 11.125 12 11.125C10.3086 11.125 8.9375 9.75387 8.9375 8.0625C8.9375 6.37112 10.3086 5 12 5C13.6914 5 15.0625 6.37112 15.0625 8.0625Z" stroke="#4D4D4D" stroke-width="1.3"/>
                                        </svg>


                                    <h4> {{__('Total Tenant')}} </h4>
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.9427 16.8354C20.2864 12.8866 18.2432 9.94613 16.467 8.219C15.9501 7.71642 15.6917 7.46513 15.1208 7.23257C14.5499 7 14.0592 7 13.0778 7H10.9222C9.94081 7 9.4501 7 8.87922 7.23257C8.30834 7.46513 8.04991 7.71642 7.53304 8.219C5.75682 9.94613 3.71361 12.8866 3.05727 16.8354C2.56893 19.7734 5.27927 22 8.30832 22H15.6917C18.7207 22 21.4311 19.7734 20.9427 16.8354Z" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7.25662 4.44287C7.05031 4.14258 6.75128 3.73499 7.36899 3.64205C8.00392 3.54651 8.66321 3.98114 9.30855 3.97221C9.89237 3.96413 10.1898 3.70519 10.5089 3.33548C10.8449 2.94617 11.3652 2 12 2C12.6348 2 13.1551 2.94617 13.4911 3.33548C13.8102 3.70519 14.1076 3.96413 14.6914 3.97221C15.3368 3.98114 15.9961 3.54651 16.631 3.64205C17.2487 3.73499 16.9497 4.14258 16.7434 4.44287L15.8105 5.80064C15.4115 6.38146 15.212 6.67187 14.7944 6.83594C14.3769 7 13.8373 7 12.7582 7H11.2418C10.1627 7 9.6231 7 9.20556 6.83594C8.78802 6.67187 8.5885 6.38146 8.18945 5.80064L7.25662 4.44287Z" stroke="#4D4D4D" stroke-width="1.5" stroke-linejoin="round"/>
                                        <path d="M13.6267 12.9181C13.4105 12.12 12.3101 11.3998 10.9892 11.9386C9.66829 12.4773 9.45847 14.2108 11.4565 14.395C12.3595 14.4782 12.9483 14.2984 13.4873 14.8071C14.0264 15.3157 14.1265 16.7303 12.7485 17.1115C11.3705 17.4927 10.006 16.8971 9.85742 16.0512M11.8417 10.9922V11.7526M11.8417 17.2288V17.9922" stroke="#4D4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>


                                    <h4> {{__('Total Withdrawal')}} </h4>
                                </div>
                                <h5 id="total_withdraw"></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="withdraw_arrow"></span>

                                        <p class="amount" id="last_month_total_withdraw"></p>
                                    </div>
                                    <h6 class="last-month"> {{__('From last month')}} </h6>
                                </div>

                            </div>
                        </div>
                        <div class="">
                            <div class="chart-header ">
                                <h3>@lang('Earning Statistic')</h3>
                                <select class="landlord-account-chart-date">
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
                                <canvas class="chart-css" id="incomeExpenseChart"></canvas>
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
                <div class="chart-container mt-30 tab-table-container">
                    <div class="">
                        <!-- Custom Styled Tabs -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="custom-tabs change-tab" id="customTabNav" data-application-url="{{ route('landlord.tenant-applications.index') }}" data-maintenance-url="{{ route('landlord.maintenances.index') }}">
                                <div class="custom-tab active" data-bs-target="#tab1">
                                    {{__('Application Request')}} <span class="count-badge">{{ $applications->count() }}</span>
                                </div>
                                <div class="custom-tab" data-bs-target="#tab2">
                                    {{__('Maintenance Request')}} <span class="count-badge">{{ $maintenances->count() }}</span>
                                </div>
                            </div>
                            <a href="" class="view-all change-view"> {{__('View All')}} <i class="far fa-chevron-right"></i></a>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content pt-4">
                            <!-- Landlord Withdraw Tab -->
                            <div class="tab-pane fade show active" id="tab1">
                                <div class="table-responsive added-properties-table">
                                    <table class="table woodland-table">
                                        <thead>
                                            <tr>
                                                <th> {{__('SL.')}} </th>
                                                <th class="text-start"> {{__('Date & Time')}} </th>
                                                <th class="text-start"> {{__('Tenant Name')}} </th>
                                                <th class="text-start"> {{__('Email Address')}} </th>
                                                <th class="text-start"> {{__('Tenant Phone')}} </th>
                                                <th class="text-start"> {{__('Property ID')}} </th>
                                                <th class="text-start"> {{__('Property Name')}} </th>
                                                <th class="text-start"> {{__('Status')}} </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($applications as $application)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>
                                                <td class="text-start">{{ formatted_date($application->created_at) }} - {{ formatted_time($application->created_at) }}</td>
                                                <td class="text-start">{{ $application->tenant->name ?? ''}}</td>
                                                <td class="text-start">{{ $application->tenant->email ?? ''}}</td>
                                                <td class="text-start">{{ $application->tenant->phone['mobile_code'] ?? ''}} {{ $application->tenant->phone['mobile_no'] ?? ''}}</td>
                                                <td class="text-start">{{ $application->property->puid ?? ''}}</td>
                                                <td class="text-warning text-start">{{ $application->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
                                                @if ($application->status == 0)
                                                <td class="text-warning  text-start">
                                                    <div class="padding-status"> {{__('Pending')}} </div>
                                                </td>
                                                @elseif ($application->status == 1)
                                                <td class="text-warning text-start">
                                                    <div class="processing-status"> {{__('Processing')}} </div>
                                                </td>
                                                @elseif ($application->status == 2)
                                                <td class="text-warning text-start">
                                                    <div class="approved-status"> {{__('Approved')}} </div>
                                                </td>
                                                @elseif ($application->status == 3)
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

                            <!-- Refund Request Tab -->
                            <div class="tab-pane fade" id="tab2">
                                <div class="table-responsive added-properties-table">
                                    <table class="table woodland-table">
                                        <thead>
                                            <tr>
                                                <th> {{__('SL.')}} </th>
                                                <th class="text-start"> {{__('Date & Time')}} </th>
                                                <th class="text-start"> {{__('Tenant Name')}} </th>
                                                <th class="text-start">	{{__('Mobile Number')}} </th>
                                                <th class="w-120 text-start"> {{__('Property Name')}} </th>
                                                <th class="w-120 text-start"> {{__('Title')}} </th>
                                                <th class="w-120 text-start"> {{__('Labor Cost')}} </th>
                                                <th class="w-120 text-start"> {{__('Status')}} </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($maintenances as $maintenance)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>
                                                <td class="text-start">{{ formatted_date($maintenance->created_at) }} - {{ formatted_time($maintenance->created_at) }}</td>
                                                <td class="text-start">{{ $maintenance->tenant->name ?? ''}}</td>
                                                <td class="text-start">{{ $maintenance->tenant->phone['mobile_no'] ?? ''}}</td>
                                                <td class="text-start">{{ $maintenance->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
                                                <td class="text-start">{{ $maintenance->title }}</td>
                                                <td class="text-start">{{ currency_format($maintenance->total_costing ?? 0) }}</td>
                                                @if ($maintenance->status == 'pending')
                                                <td class="text-warning  text-start">
                                                    <div class="padding-status"> {{__('Pending')}} </div>
                                                </td>
                                                @elseif ($maintenance->status == 'processing')
                                                <td class="text-warning text-start">
                                                    <div class="processing-status"> {{__('Processing')}} </div>
                                                </td>
                                                @elseif ($maintenance->status == 'completed')
                                                <td class="text-warning text-start">
                                                    <div class="approved-status"> {{__('Completed')}} </div>
                                                </td>
                                                @elseif ($maintenance->status == 'rejected')
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
    <input type="hidden" value="{{ route('landlord.getDashboardData') }}" id="get-dashboard">
    <input type="hidden" value="{{ route('landlord.getPropertyData') }}" id="landlord-property-data">
    <input type="hidden" value="{{ route('landlord.getAccountData') }}" id="landlord-account-data">

    @php
    $currency = currency();
    @endphp
    {{-- Hidden input fields to store currency details --}}
    <input type="hidden" id="currency_symbol" value="{{ $currency->symbol }}">
    <input type="hidden" id="currency_position" value="{{ $currency->position }}">
    <input type="hidden" id="currency_code" value="{{ $currency->code }}">
@endsection

@push('script')
    <script src="{{ asset('assets/plugins/custom/landlordDashboard.js') }}"></script>
@endpush
