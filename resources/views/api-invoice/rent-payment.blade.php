@extends('layouts.blank')

@section('main_content')
<div class="invoice-container">
    <div class="invoice-content">
        <h4>{{ __('RENT PAYMENT') }}</h4>
        <div class="d-flex align-items-center justify-content-between">
            <div class="">
                <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                <div class="pt-2">
                    <p class="invoice-header-p">{{ __('Invoice ID') }}: <span>{{ $rent_payment->invoice_no }}</span></p>
                    <p class="invoice-header-p pt-1">{{ __('Invoice Date') }}:
                        <span>{{ formatted_date($rent_payment->created_at) }}</span>
                    </p>
                    <p class="invoice-header-p pt-1">{{ __('Rental Month') }}: <span>{{ currency_format( $rent_payment->monthly_rent ?? 0 ) }}</span></p>
                </div>
            </div>
            <div class="">
                @if($rent_payment->payment_status == 'paid')
                <p class="paid-status">Paid</p>
                @elseif($rent_payment->payment_status == 'pending')
                <p class="pending-status">Pending</p>
                @else
                <p class="unpaid-status">Unpaid</p>
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
                    <p class="info-p">{{ __('Name') }}: <span>{{ $rent_payment->rent?->tenant?->name }}</span></p>
                    <p class="info-p pt-2">{{ __('Address') }}: <span>{{ collect($rent_payment->rent?->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                    <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ $rent_payment->rent?->tenant?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                    <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $rent_payment->rent?->tenant?->email ?? ''}}</span></p>
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
                            <th>{{ __('Total Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $rent_payment->rent?->property?->puid }}</td>
                            <td>{{ $rent_payment->rent?->property?->propertyDetail?->property_info['property_title'] ?? ''}}</td>
                            <td>{{ currency_format($rent_payment->subtotal_amount ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-6"></div>
                <div class="col-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="border-0 text-end">{{ __('Subtotal') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->subtotal_amount ?? 0) }}</td>
                            </tr>
                            @if (auth()->user()->role == 'landlord')
                            <tr>
                                <td class="border-0 text-end">{{ __('Charge') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->charge ?? 0) }}</td>
                            </tr>
                            @endif
                            @if (auth()->user()->role == 'tenant')
                            <tr>
                                <td class="border-0 text-end">{{ __('Total') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->subtotal_amount ?? 0) }}</td>
                            </tr>
                            @endif
                            @if (auth()->user()->role == 'landlord')
                            <tr>
                                <td class="border-0 text-end">{{ __('Receivable') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->total_amount ?? 0) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div>
                <h6>{{ __('Landlord Information') }}</h6>
                <p class="info-p">{{ __('Name') }}: <span>{{ $rent_payment->rent?->landlord?->name }}</span></p>
                <p class="info-p pt-2">{{ __('Address') }}: <span>{{ collect($rent_payment->rent?->landlord?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ $rent_payment->rent?->landlord?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $rent_payment->rent?->landlord?->email ?? ''}}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
