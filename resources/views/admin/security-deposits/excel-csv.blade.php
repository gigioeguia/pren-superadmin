<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Request Date') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Rent Amount') }}</th>
            <th>{{ __('Utility Deposit') }}</th>
            <th>{{ __('Payment Method') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($security_deposits as $deposit)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($deposit->created_at ?? '') }} - {{ formatted_time($deposit->created_at ?? '') }}</td>
            <td>{{ $deposit->invoice_no ?? '' }}</td>
            <td>{{ $deposit->rent->tenant->name ?? '' }}</td>
            <td>{{ $deposit->rent->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ currency_format($deposit->monthly_rent ?? 0) }}</td>
            <td>{{ currency_format($deposit->utility_bill ?? 0) }}</td>
            <td>{{ $deposit->gateway->name ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
