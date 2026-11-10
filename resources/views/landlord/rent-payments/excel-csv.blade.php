<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenants Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Property ID') }}</th>
            <th>{{ __('Rent Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($rent_payments as $rent)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($rent->payment_date) }}</td>
            <td>{{ $rent->invoice_no }}</td>
            <td>{{ $rent->rent?->tenant?->name ?? ''}}</td>
            <td>{{ $rent->rent?->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
            <td>{{ $rent->rent?->property?->puid }}</td>
            <td>{{ currency_format($rent->total_amount ?? 0) }}</td>

            @if ($rent->payment_status == 'paid')
              <td class="text-success">{{ __('Paid') }}</td>
            @else
              <td class="text-warning">{{ __('Unpaid') }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
