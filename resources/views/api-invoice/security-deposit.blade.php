
@extends('layouts.blank')

@section('main_content')
    <div class="invoice-container ">
        <div class="invoice-content">
            <h4>{{ __('Deposit & Utility Invoice') }}</h4>
            <div class="d-flex align-items-center justify-content-between">
                <div class="">
                    <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                    <div class="pt-2">
                        <p class="invoice-header-p">Invoice ID: <span>{{ $security_deposit->invoice_no }}</span></p>
                        <p class="invoice-header-p pt-1">Invoice Date:
                            <span>{{ formatted_date($security_deposit->created_at) }}</span>
                        </p>
                        <p class="invoice-header-p pt-1">Rental Month: <span>{{ currency_format( $security_deposit->rent_advance ?? 0 ) }}</span></p>
                    </div>
                </div>
                <div class="">
                    @if ($security_deposit->status == 'paid')
                        <p class="paid-status">paid</p>
                    @elseif($security_deposit->status == 'unpaid')
                        <p class="unpaid-status">Unpaid</p>
                    @elseif($security_deposit->status == 'refund')
                        <p class="refund-status">Refund</p>
                    @elseif($security_deposit->status == 'pending')
                        <p class="pending-status">Pending</p>
                    @else
                        <p class="reject-status">Rejected</p>
                    @endif
                </div>
            </div>
            <hr>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>{{ __('Bill From(Admin)') }}</h6>
                    <p class="info-p">{{ __('Name') }}: <span>{{ get_option('general')['app_name'] }}</span></p>
                    <p class="info-p pt-2">{{ __('Address') }}:
                        <span>{{ get_option('general')['app_address'] }}</span>
                    </p>
                    <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ get_option('general')['app_phone'] }}</span></p>
                    <p class="info-p pt-2">{{ __('Email') }}: <span>{{ get_option('general')['app_email'] }}</span></p>
                </div>
                <div>
                    <div class="text-end">
                        <h6>{{ __('Bill To (Tenant)') }}</h6>
                        <p class="info-p">{{ __('Name') }}: <span>{{ $security_deposit->tenant?->name }}</span></p>
                        <p class="info-p pt-2">{{ __('Address') }}:
                            <span>{{ collect($security_deposit->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span>
                        </p>
                        <p class="info-p pt-2">{{ __('Phone') }}:
                            <span>{{ $security_deposit->tenant?->phone['mobile_no'] ?? 'N/A' }}</span>
                        </p>
                        <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $security_deposit->tenant?->email ?? '' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h6>{{ __('Item') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered invoice-table-new">
                        <thead class="invoice-table-th">
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Property Name') }}</th>
                                <th>{{ __('Security Deposit') }}</th>
                                <th>{{ __('Utility Deposit') }}</th>
                                <th class="text-end">{{ __('Advance Rental') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $security_deposit->property?->puid }}</td>
                                <td>{{ $security_deposit->property->propertyDetail->property_info['property_title'] ?? '' }}
                                </td>
                                <td>{{ currency_format($security_deposit->deposit_amount ?? 0) }}</td>
                                <td>{{ currency_format($security_deposit->utility_advance ?? 0) }}</td>
                                <td class="text-end">{{ currency_format($security_deposit->rent_advance ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-6"></div>
                    <div class="col-6">
                        <table class="table">
                            <tbody>
                                @if (auth()->user()->role == 'landlord')
                                <tr>
                                    <td class="border-0 text-end">Subtotal:</td>
                                    <td class="border-0 text-end">{{ currency_format($security_deposit->total_amount ?? 0)}}</td>
                                </tr>
                                @endif
                                @if (auth()->user()->role == 'tenant')
                                <tr>
                                    <td class="border-0 text-end">Subtotal:</td>
                                    <td class="border-0 text-end">{{ currency_format($security_deposit->total_amount ?? 0) }}</td>
                                </tr>
                                @endif
                                @if (auth()->user()->role == 'landlord')
                                <tr>
                                    <td class="border-0 text-end">Charge:</td>
                                    <td class="border-0 text-end">{{ currency_format($security_deposit->charge ?? 0) }}</td>
                                </tr>
                                @endif
                                @if (auth()->user()->role == 'landlord')
                                <tr>
                                    <td class="border-0 text-end">Receiveable:</td>
                                    <td class="border-0 text-end">{{ currency_format($security_deposit->landlord_balance ?? 0) }}</td>
                                </tr>
                                @endif
                                @if (auth()->user()->role == 'tenant')
                                <tr>
                                    <td class="border-0 text-end">Total:</td>
                                    <td class="border-0 text-end">{{ currency_format($security_deposit->total_amount ?? 0)}}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div>
                    <h6>{{ __('Landlord Information') }}</h6>
                    <p class="info-p">{{ __('Name') }}: <span>{{ $security_deposit->landlord?->name }}</span></p>
                    <p class="info-p pt-2">{{ __('Address') }}:
                        <span>{{ collect($security_deposit->landlord?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span>
                    </p>
                    <p class="info-p pt-2">{{ __('Phone') }}:
                        <span>{{ $security_deposit->landlord?->phone['mobile_no'] ?? 'N/A' }}</span>
                    </p>
                    <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $security_deposit->landlord?->email ?? '' }}</span></p>
                </div>
            </div>
        </div>
    </div>
@endsection
