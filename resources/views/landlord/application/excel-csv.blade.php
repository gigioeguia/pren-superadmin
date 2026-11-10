<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Unit Name') }}</th>
            <th>{{ __('Property ID') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
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
                    <p>{{ __('Pending') }}</p>
                @elseif($application->status == 1)
                    <p>{{ __('Processing') }}</p>
                @elseif($application->status == 2)
                    <p>{{ __('Approved') }}</p>
                @elseif($application->status == 3)
                    <p>{{ __('Cancelled') }}</p>
                @elseif($application->status == 4)
                    <p>{{ __('Rejected') }}</p>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
