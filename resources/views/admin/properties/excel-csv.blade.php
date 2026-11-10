<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Units') }}</th>
            <th>{{ __('Available') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($properties as $property)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $property->puid }}</td>
                <td>{{ $property->propertyDetail->property_info['property_title'] ?? '' }}</td>
                <td>{{ $property->category?->name }}</td>
                <td>{{ ucfirst($property->type) }}</td>
                <td>{{ $property->total_unit }}</td>
                <td>{{ $property->total_available_unit }}</td>
                <td>
                    @if ($property->status == 1)
                        <span class="badge bg-success">{{ __('Approved') }}</span>
                    @elseif($property->status == 0)
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                    @elseif($property->status == 2)
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                    @else
                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
