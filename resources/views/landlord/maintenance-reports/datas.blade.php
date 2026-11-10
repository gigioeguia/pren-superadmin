@foreach ($maintenances as $maintenance)
<tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td class="text-center">{{ $maintenance->property?->propertyDetail?->property_info['property_title'] ?? '' }}</td>
    <td class="text-center">{{ $pendingCount }}</td>
    <td class="text-center">{{ $processingCount }}</td>
    <td class="text-center">{{ $completedCount }}</td>
    <td class="text-center">{{ $rejectedCount }}</td>
    <td class="text-center">{{ currency_format($maintenance->total_costing ?? 0) }}</td>
</tr>
@endforeach
