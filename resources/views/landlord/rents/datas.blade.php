@foreach ($rents as $rent)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $rent->tenant?->unique_id }}</td>
        <td>{{ $rent->tenant?->name }}</td>
        <td>{{ $rent->property?->category?->name ?? '' }}</td>
        <td>{{ $rent->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
        <td>{{ $rent->unit?->unit_name ?? '' }}</td>
        <td>{{ currency_format($rent->security_deposit?->deposit_amount ?? 0) }}</td>
        <td>{{ currency_format($rent->monthly_rent ?? 0) }}</td>

        <td>
            @if ($rent->status == 'pending')
                <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
            @elseif($rent->status == 'active')
                <span class="badge bg-success text-white">{{ __('Active') }}</span>
            @elseif($rent->status == 'cancelled')
                <span class="badge bg-danger text-white">{{ __('Cancelled') }}</span>
            @elseif($rent->status == 'processing')
                <span class="badge bg-primary text-white">{{ __('Processing') }}</span>
            @else
                <span class="badge bg-secondary text-white">{{ __('Expired') }}</span>
            @endif
        </td>
        <td class="text-end"><a href="{{ route('landlord.rents.show', $rent->id) }}" class="view-details-btn">
                {{ __('View Details') }} </a>
        </td>
    </tr>
@endforeach
