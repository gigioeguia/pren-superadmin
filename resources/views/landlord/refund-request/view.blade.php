@extends('layouts.blank')

@section('main_content')
    <div class="m-4 rounded">
        <div class="invoice-container">
            <div class="woodland-card-header d-print-none">
                <h3> {{__('View Invoice')}} </h3>
                <button class="modal-btn print-window" >
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
                <h4>{{ __('Refund Invoice') }}</h4>
                <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between">
                    <div class="">
                        <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                        <div class="pt-2">
                            <p class="invoice-header-p"> {{__('Invoice ID:')}} <span>{{ $refund->invoice_no }}</span></p>
                            <p class="invoice-header-p pt-1"> {{__('Invoice Date:')}} <span>{{ formatted_date($refund->created_at) }}</span></p>
                            <p class="invoice-header-p pt-1"> {{__('Rental Month:')}} <span>{{ currency_format($refund->deposit?->rent_advance ?? 0) }}</span></p>
                        </div>
                    </div>
                    <div class="">
                        @if($refund->status == 'paid')
                        <p class="unpaid-status"> {{__('Paid')}} </p>
                        @elseif($refund->status == 'unpaid')
                        <p class="unpaid-status"> {{__('Unpaid')}} </p>
                        @else
                        <p class="unpaid-status"> {{__('Rejected')}} </p>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between">
                    <div>
                        <h6> {{__('Landlord Information')}} </h6>
                        <p class="info-p"> {{__('Name:')}} <span>{{ $refund->landlord?->name }}</span></p>
                        <p class="info-p pt-2"> {{__('Address:')}} <span>{{ collect($refund->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                        <p class="info-p pt-2"> {{__('Phone:')}} <span>{{ $refund->landlord?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                        <p class="info-p pt-2"> {{__('Email:')}} <span>{{ $refund->landlord?->email }}</span></p>
                    </div>
                    <div>
                        <div class="text-sm-end text-start">
                            <h6> {{__('Bill To (Tenant)')}} </h6>
                            <p class="info-p"> {{__('Name:')}} <span>{{ $refund->tenant?->name }}</span></p>
                            <p class="info-p pt-2"> {{__('Address:')}} <span>{{ collect($refund->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                            <p class="info-p pt-2"> {{__('Phone:')}} <span>{{ $refund->tenant?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                            <p class="info-p pt-2"> {{__('Email:')}} <span>{{ $refund->tenant?->email ?? '' }}</span>
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
                                    <th> {{__('Refund Reason')}}  </th>
                                    <th> {{__('SEC. Deposit')}} </th>
                                    <th> {{__('Utility Deposit')}} </th>
                                    <th> {{__('Advance Rental (1)')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $refund->property?->puid }}</td>
                                    <td>{{ $refund->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
                                    <td>{{ $refund->refund_reason }}</td>
                                    <td>{{ currency_format($refund->deposit?->deposit_amount ?? 0) }}</td>
                                    <td>{{ currency_format($refund->deposit?->utility_advance ?? 0) }}</td>
                                    <td>{{ currency_format($refund->deposit?->rent_advance ?? 0) }}</td>
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
                                        <td class="border-0 text-end">{{ currency_format($refund->amount ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 text-end"> {{__('Total:')}} </td>
                                        <td class="border-0 text-end">{{ currency_format($refund->amount ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="">
                        <h6> {{__('Bank Information')}} </h6>
                        <p class="info-p"> {{__('Account Name:')}} <span>{{ $refund->bank_info['account_name'] ?? '' }}</span></p>
                        <p class="info-p pt-2"> {{__('Bank Name:')}} <span>{{ $refund->bank_info['bank_name'] ?? '' }}</span></p>
                        <p class="info-p pt-2"> {{__('Account Number:')}} <span>{{ $refund->bank_info['account_no'] ?? ''}}</span></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
