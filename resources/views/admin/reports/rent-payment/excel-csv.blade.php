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
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($rent_payments as $rent_payment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($rent_payment->created_at ?? '') }} - {{ formatted_time($rent_payment->created_at ?? '') }}</td>
            <td>{{ $rent_payment->invoice_no ?? '' }}</td>
            <td>{{ $rent_payment->rent->tenant->name ?? '' }}</td>
            <td>{{ $rent_payment->rent->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ currency_format($rent_payment->monthly_rent ?? 0) }}</td>
            <td>{{ currency_format($rent_payment->utility_bill ?? 0) }}</td>
            <td>{{ $rent_payment->gateway->name ?? '' }}</td>

            @if ($rent_payment->payment_status == 'unpaid')
                <td>{{ __('Unpaid') }}</td>
            @elseif ($rent_payment->payment_status == 'rejected')
                <td>{{ __('Rejected') }}</td>
            @elseif ($rent_payment->payment_status == 'paid')
                <td>{{ __('Paid') }}</td>
            @else
                <td>{{ __('Pending') }}</td>
            @endif
        </tr>
        @endforeach

    </tbody>
</table>
