@extends('layouts.blank')

@section('main_content')
<div class="invoice-container">
    <div class="invoice-content">
        <h4>{{ __('Refund Invoice') }}</h4>
        <div class="d-flex align-items-center justify-content-between">
            <div class="">
                <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                <div class="pt-2">
                    <p class="invoice-header-p">{{ __('Invoice ID') }}: <span>{{ $refund->invoice_no }}</span></p>
                    <p class="invoice-header-p pt-1">{{ __('Invoice Date') }}: <span>{{ formatted_date($refund->created_at) }}</span></p>
                    <p class="invoice-header-p pt-1">{{ __('Rental Month') }}: <span>{{ currency_format($refund->rent_advance ?? 0) }}</span></p>
                </div>
            </div>
            <div class="">
                @if($refund->status == 'paid')
                <p class="paid-status">paid</p>
                @elseif($refund->status == 'unpaid')
                <p class="unpaid-status">Unpaid</p>
                @else
                <p class="reject-status">Rejected</p>
                @endif
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6>{{ __('Landlord Information') }}</h6>
                <p class="info-p">{{ __('Name') }}: <span>{{ $refund->landlord?->name }}</span></p>
                <p class="info-p pt-2">{{ __('Address') }}: <span>{{ collect($refund->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ $refund->landlord?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $refund->landlord?->email }}</span></p>
            </div>
            <div>
                <div class="text-end">
                    <h6>{{ __('Bill To (Tenant)') }}</h6>
                    <p class="info-p">{{ __('Name') }}: <span>{{ $refund->tenant?->name }}</span></p>
                    <p class="info-p pt-2">{{ __('Address') }}: <span>{{ collect($refund->tenant?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                    <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ $refund->tenant?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                    <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $refund->tenant?->email ?? '' }}</span>
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
                            <th>{{ __('Refund Reason') }}</th>
                            <th>{{ __('SEC. Deposit') }}</th>
                            <th>{{ __('Utility Deposit') }}</th>
                            <th>{{ __('Advance Rental (1)') }}</th>
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
                <div class="col-6"></div>
                <div class="col-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="border-0 text-end">{{ __('Subtotal') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($refund->amount ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 text-end">{{ __('Total') }}:</td>
                                <td class="border-0 text-end">{{ currency_format($refund->amount ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="">
                <h6>{{ __('Bank Information') }}</h6>
                <p class="info-p">{{ __('Account Name') }}: <span>{{ $refund->bank_info['account_name'] ?? '' }}</span></p>
                <p class="info-p pt-2">{{ __('Bank Name') }}: <span>{{ $refund->bank_info['bank_name'] ?? '' }}</span></p>
                <p class="info-p pt-2">{{ __('Account Number') }}: <span>{{ $refund->bank_info['account_no'] ?? ''}}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
