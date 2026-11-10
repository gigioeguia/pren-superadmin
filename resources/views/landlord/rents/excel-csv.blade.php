<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Tenant Id') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Property Type') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Security Deposit') }}</th>
            <th>{{ __('Monthly Rent') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($rents as $rent)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $rent->tenant?->unique_id }}</td>
            <td>{{ $rent->tenant?->name }}</td>
            <td>{{ $rent->property?->propertyDetail?->property_type ?? '' }}</td>
            <td>{{ $rent->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
            <td>{{ currency_format($rent->security_deposit?->deposit_amount ?? 0) }}</td>
            <td>{{ currency_format($rent->monthly_rent ?? 0) }}</td>

            @if($rent->status == 'pending')
            <td class="text-warning">{{ __('Pending') }}</td>
            @elseif($rent->status == 'active')
            <td class="text-success">{{ __('Active') }}</td>
            @elseif($rent->status == 'cancelled')
            <td class="text-danger">{{ __('Cancelled') }}</td>
            @elseif($rent->status == 'processing')
            <td class="text-primary">{{ __('Processing') }}</td>
            @else
            <td class="text-danger">{{ __('Expired') }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
