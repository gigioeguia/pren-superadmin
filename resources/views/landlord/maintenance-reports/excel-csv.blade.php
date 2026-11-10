<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Pending') }}</th>
            <th>{{ __('Processing') }}</th>
            <th>{{ __('Completed') }}</th>
            <th>{{ __('Rejected') }}</th>
            <th>{{ __('Total Cost') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($maintenances as $maintenance)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $maintenance->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
            <td>{{ $pendingCount }}</td>
            <td>{{ $processingCount }}</td>
            <td>{{ $completedCount }}</td>
            <td>{{ $rejectedCount }}</td>
            <td>{{ currency_format($maintenance->total_costing ?? 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
