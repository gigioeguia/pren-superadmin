@foreach ($properties as $property)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $property->id }}">
        </td>
        <td>{{ ($properties->currentPage() - 1) * $properties->perPage() + $loop->iteration }}</td>
        <td>{{ $property->puid }}</td>
        <td>{{ $property->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ $property->category->name ?? '' }}</td>
        <td>
            <div class="{{ $property->type == 'rent' ? 'rent-type' : 'sale-type' }}">
                {{ __(ucfirst($property->type)) }}
            </div>
        </td>
        <td>{{ $property->total_unit }}</td>
        <td>{{ $property->total_available_unit }}</td>
        <td class="text-start w-150 d-print-none">
            <label class="switch">
                <input type="checkbox" {{ $property->status == 1 ? 'checked' : '' }} class="property-status"
                    data-status="{{ $property->status }}"
                    data-url="{{ route('landlord.properties.status', $property->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
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

        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a class="view-action d-flex align-items-center justify-content-center" href="{{ route('landlord.properties.show', $property->id) }}">
                        <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                    </a>
                </div>

                <div>
                    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="action-btn edit-action d-flex align-items-center justify-content-center">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>

                <div>
                    <a href="{{ route('landlord.properties.destroy', $property->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
