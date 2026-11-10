@foreach ($applications as $application)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($application->start_date ?? '') }}</td>
        <td>{{ $application->tenant?->name }}</td>
        <td>{{ $application->unit?->unit_name }}</td>
        <td>{{ $application->property?->puid }}</td>
        <td>{{ $application->property->propertyDetail->property_info['property_title'] ?? '' }}</td>

        <td>
            @if ($application->status == 0)
                <span class="padding-status text-start">{{ __('Pending') }}</span>
            @elseif($application->status == 1)
                <span class="processing-status text-start">{{ __('Processing') }}</span>
            @elseif($application->status == 2)
                <span class="approved-status text-start">{{ __('Approved') }}</span>
            @elseif($application->status == 3)
                <span class="rejected-status text-start">{{ __('Cancelled') }}</span>
            @elseif($application->status == 4)
                <span class="rejected-status text-start">{{ __('Rejected') }}</span>
            @endif
        </td>

        <td class="text-center d-print-none">
            <a href="{{ route('landlord.tenant-applications.show', $application->id) }}" class="view-details-btn">
                {{ __('View Details') }}
            </a>
        </td>
    </tr>
@endforeach
