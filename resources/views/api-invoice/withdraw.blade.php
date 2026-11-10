@extends('layouts.blank')

@section('main_content')
<div class="invoice-container">
    <div class="invoice-content">
        <h4>{{ __('WITHDRAW INVOICE') }}</h4>
        <div class="d-flex align-items-center justify-content-between">
            <div class="">
                <img src="{{ asset(get_option('general')['logo'] ?? 'backend/assets/img/logo.svg') }}" alt="">
                <div class="pt-2">
                    <p class="invoice-header-p">{{ __('Receipt ID') }}: <span> {{ $withdraw->invoice_no }}</span></p>
                    <p class="invoice-header-p pt-1">{{ __('Receipt Date') }}: <span>{{ formatted_date($withdraw->created_at) }}</span></p>
                </div>
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6>{{ __('Bill From(Admin)') }}</h6>
                <p class="info-p">{{ __('Name') }}: <span>{{ get_option('general')['app_name'] }}</span></p>
                <p class="info-p pt-2">{{ __('Address') }}: <span>{{ get_option('general')['app_address'] }}</span></p>
                <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ get_option('general')['app_phone'] }}</span></p>
                <p class="info-p pt-2">{{ __('Email') }}: <span>{{ get_option('general')['app_email'] }}</span></p>
            </div>
            <div>
                <div class="text-end">
                <h6>{{ __('Landlord Information') }}</h6>
                <p class="info-p">{{ __('Name') }}: <span>{{ $withdraw->user?->name }}</span></p>
                <p class="info-p pt-2">{{ __('Address') }}: <span>{{ collect($withdraw->user?->user_details['address_info'] ?? [])->only(['address_one', 'city'])->filter()->implode(', ') }}</span></p>
                <p class="info-p pt-2">{{ __('Phone') }}: <span>{{ $withdraw->user?->phone['mobile_no'] ?? 'N/A' }}</span></p>
                <p class="info-p pt-2">{{ __('Email') }}: <span>{{ $withdraw->user?->email ?? ''}}</span></p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h6>{{ __('Item') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered invoice-table-new">
                    <thead class="invoice-table-th">
                        <tr>
                            <th>{{ __('Payout Request amount') }}</th>
                            <th>{{ __('Fees') }}</th>
                            <th>{{ __('Subtotal') }}</th>
                            <th>{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
                            <td>{{ currency_format($withdraw->charge ?? 0) }}</td>
                            <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
                            <td>{{ currency_format(($withdraw->amount ?? 0) + ($withdraw->charge ?? 0)) }}</td>
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
                                <td class="border-0 text-end">{{ currency_format($withdraw->amount ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 text-end">{{ __('Total') }}:</td>
                                <td class="border-0 text-end">{{ currency_format(($withdraw->amount ?? 0) + ($withdraw->charge ?? 0)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
