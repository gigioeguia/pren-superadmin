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
        @foreach ($rent_payments as $payment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($payment->created_at ?? '') }} - {{ formatted_time($payment->created_at ?? '') }}</td>
            <td>{{ $payment->invoice_no ?? '' }}</td>
            <td>{{ $payment->rent->tenant->name ?? '' }}</td>
            <td>{{ $payment->rent->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ currency_format($payment->monthly_rent ?? 0) }}</td>
            <td>{{ currency_format($payment->utility_bill ?? 0) }}</td>
            <td>{{ $payment->gateway->name ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
