<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenants Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Property ID') }}</th>
            <th>{{ __('Deposit Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($security_deposits as $deposit)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($deposit->payment_date ?? '') }}</td>
            <td>{{ $deposit->invoice_no }}</td>
            <td>{{ $deposit->tenant?->name }}</td>
            <td>{{ $deposit->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ $deposit->property?->puid }}</td>
            <td>{{ currency_format($deposit->deposit_amount ?? 0) }}</td>

            @if ($deposit->status == 'paid')
            <td class="text-success">{{ __('Paid') }}</td>
            @elseif($deposit->status == 'unpaid')
            <td class="text-warning">{{ __('Unpaid') }}</td>
            @elseif($deposit->status == 'refunded')
            <td class="text-info">{{ __('Refund') }}</td>
            @elseif($deposit->status == 'rejected')
            <td class="text-danger">{{ __('Rejected') }}</td>
            @else
            <td class="text-primary">{{ __('Pending') }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
