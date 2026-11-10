<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Request Date') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Total Amount') }}</th>
            <th>{{ __('Utility Advance') }}</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($security_deposits as $deposit)
        <tr>
            <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($deposit->created_at ?? '') }} - {{ formatted_time($deposit->created_at ?? '') }}</td>
        <td>{{ $deposit->invoice_no ?? '' }}</td>
        <td>{{ $deposit->tenant?->name ?? '' }}</td>
        <td>{{ $deposit->property?->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ currency_format($deposit->total_amount ?? 0) }}</td>
        <td>{{ currency_format($deposit->utility_advance ?? 0) }}</td>
        <td>{{ $deposit->gateway?->name ?? '' }}</td>

        @if ($deposit->status == 'unpaid')
            <td class="text-warning">{{ __('Unpaid') }}</td>
        @elseif ($deposit->status == 'rejected')
            <td class="text-danger">{{ __('Rejected') }}</td>
        @elseif ($deposit->status == 'paid')
            <td class="text-success">{{ __('Paid') }}</td>
        @elseif ($deposit->status == 'refunded')
            <td class="text-primary">{{ __('Refund') }}</td>
        @else
            <td class="text-warning">{{ __('Pending') }}</td>
        @endif
        </tr>
        @endforeach
    </tbody>
</table>
