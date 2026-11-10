@foreach ($maintenances as $maintenance)
    <tr>
        <td>{{ ($maintenances->currentPage() - 1) * $maintenances->perPage() + $loop->iteration }}</td>
        <td>{{ formatted_date($maintenance->created_at) }}</td>
        <td>{{ $maintenance->tenant?->name }}</td>
        <td>{{ $maintenance->tenant?->phone['mobile_no'] ?? '' }}</td>
        <td>{{ $maintenance->property->propertyDetail->property_info['property_title'] ?? '' }}</</td>
        <td>{{ $maintenance->title }}</td>
        <td>{{ currency_format($maintenance->total_costing ?? 0) }}</td>

        @if ($maintenance->status == 'pending')
            <td>
                <span class="text-start {{ $maintenance->status == 'pending' ? 'padding-status' : '' }}">
                    {{ __('Pending') }}
                </span>
            </td>
        @elseif($maintenance->status == 'processing')
            <td>
                <span
                    class="text-start {{ $maintenance->status == 'processing' ? 'processing-status' : '' }}">
                    {{ __('Processing') }}
                </span>
            </td>
        @elseif($maintenance->status == 'completed')
            <td>
                <span
                    class="text-start {{ $maintenance->status == 'completed' ? 'approved-status' : '' }}">
                    {{ __('Completed') }}
                </span>
            </td>
        @else
            <td>
                <span
                    class="text-start {{ $maintenance->status == 'rejected' ? 'rejected-status' : '' }}">
                    {{ __('Rejected') }}
                </span>
            </td>
        @endif

        <td class="text-end">
            <a href="{{ route('landlord.maintenances.show', $maintenance->id) }}" class="view-details-btn">
                {{ __('View Details') }}
            </a>
        </td>
    </tr>
@endforeach
