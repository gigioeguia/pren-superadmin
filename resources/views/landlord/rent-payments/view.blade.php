@extends('layouts.blank')

@section('main_content')
<div class="m-4 rounded">
<div class="invoice-container">
    <div class="woodland-card-header d-print-none">
        <h3>{{ __('View Invoice') }}</h3>
        <button class="modal-btn print-window">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_882_27096)">
                    <path d="M4 6.00065V1.33398H12V6.00065" stroke="white" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path
                        d="M4.00065 12H2.66732C2.3137 12 1.97456 11.8595 1.72451 11.6095C1.47446 11.3594 1.33398 11.0203 1.33398 10.6667V7.33333C1.33398 6.97971 1.47446 6.64057 1.72451 6.39052C1.97456 6.14048 2.3137 6 2.66732 6H13.334C13.6876 6 14.0267 6.14048 14.2768 6.39052C14.5268 6.64057 14.6673 6.97971 14.6673 7.33333V10.6667C14.6673 11.0203 14.5268 11.3594 14.2768 11.6095C14.0267 11.8595 13.6876 12 13.334 12H12.0007"
                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M12 9.33398H4V14.6673H12V9.33398Z" stroke="white" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </g>
                <defs>
                    <clipPath id="clip0_882_27096">
                        <rect width="16" height="16" fill="white" />
                    </clipPath>
                </defs>
            </svg>
            {{__('Print')}} </button>
    </div>
    <div class="invoice-content">
        <h4>{{ __('RENT PAYMENT') }}</h4>
        <div class="d-flex align-items-center justify-content-between">
            <div class="">
                <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                <div class="pt-2">
                    <p class="invoice-header-p"> {{__('Invoice ID:')}} <span> {{ $rent_payment->invoice_no }}</span></p>
                    <p class="invoice-header-p pt-1"> {{__('Invoice Date:')}}
                        <span>{{ formatted_date($rent_payment->created_at) }}</span>
                    </p>
                    <p class="invoice-header-p pt-1"> {{__('Rental Month:')}} <span>{{ currency_format($rent_payment->monthly_rent ?? 0) }}</span></p>
                </div>
            </div>
            <div class="">
                @if($rent_payment->payment_status == 'paid')
                <p class="paid-status"> {{__('Paid')}} </p>
                @else
                <p class="unpaid-status"> {{__('Unpaid')}} </p>
                @endif
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div>
                <h6> {{__('Bill From(Admin)')}} </h6>
                <p class="info-p"> {{__('Name:')}} <span>{{ get_option('general')['app_name'] }}</span></p>
                <p class="info-p pt-2"> {{__('Address:')}}
                    <span>{{ get_option('general')['app_address'] }}</span>
                </p>
                <p class="info-p pt-2"> {{__('Phone:')}} <span>{{ get_option('general')['app_phone'] }}</span></p>
                <p class="info-p pt-2"> {{__('Email:')}} <span>{{ get_option('general')['app_email'] }}</span></p>
            </div>
            <div>
                <div class="text-sm-end text-start">
                    <h6> {{__('Bill To (Tenant)')}} </h6>
                    <p class="info-p"> {{__('Name:')}} <span>{{ $rent_payment->rent?->tenant?->name }}</span></p>
                    <p class="info-p pt-2"> {{__('Address:')}} <span>{{ collect($rent_payment->rent?->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                    <p class="info-p pt-2"> {{__('Phone:')}} <span>{{ $rent_payment->rent?->tenant?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                    <p class="info-p pt-2"> {{__('Email:')}} <span>{{ $rent_payment->rent?->tenant?->email ?? ''}}</span></p>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h6> {{__('Item')}} </h6>
            <div class="table-responsive">
                <table class="table table-bordered invoice-table-new">
                    <thead class="invoice-table-th">
                        <tr>
                            <th> {{__('ID')}} </th>
                            <th> {{__('Property Name')}} </th>
                            <th class="text-end"> {{__('Total Amount')}} </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $rent_payment->rent?->property?->puid }}</td>
                            <td>{{ $rent_payment->rent?->property?->propertyDetail?->property_info['property_title'] ?? ''}}</td>
                            <td class="text-end">{{ currency_format($rent_payment->subtotal_amount ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="border-0 text-end"> {{__('Subtotal:')}} </td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->subtotal_amount ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 text-end"> {{__('Charge:')}} </td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->charge ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 text-end"> {{__('Receiveable:')}} </td>
                                <td class="border-0 text-end">{{ currency_format($rent_payment->total_amount ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div>
                <h6>{{ __('Landlord Information') }}</h6>
                <p class="info-p"> {{__('Name:')}} <span>{{ $rent_payment->rent?->landlord?->name }}</span></p>
                <p class="info-p pt-2"> {{__('Address:')}} <span>{{ collect($rent_payment->rent?->landlord?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                <p class="info-p pt-2"> {{__('Phone:')}} <span>{{ $rent_payment->rent?->landlord?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                <p class="info-p pt-2"> {{__('Email:')}} <span>{{ $rent_payment->rent?->landlord?->email ?? ''}}</span></p>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
