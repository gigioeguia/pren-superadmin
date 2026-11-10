<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date & Time') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Mobile Number') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Labor Cost') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($maintenances as $maintenance)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($maintenance->created_at) }}</td>
            <td>{{ $maintenance->tenant?->name }}</td>
            <td>{{ $maintenance->tenant?->phone['mobile_no'] ?? '' }}</td>
            <td>{{ $maintenance->property?->building_name }}</td>
            <td>{{ $maintenance->title }}</td>
            <td>{{ currency_format($maintenance->total_costing ?? 0) }}</td>

            @if ($maintenance->status == 'pending')
               <td class="text-warning">{{ __('Pending') }}</td>

            @elseif($maintenance->status == 'processing')
                <td class="text-primary">{{ __('Processing') }}</td>

            @elseif($maintenance->status == 'completed')
                <td class="text-success">{{ __('Completed') }}</td>
            @else
                <td class="text-danger">{{ __('Rejected') }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
