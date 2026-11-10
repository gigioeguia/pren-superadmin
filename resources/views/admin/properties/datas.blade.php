@foreach ($properties as $property)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $property->id }}">
        </td>
        <td>{{ $properties->firstItem() + $loop->index }}</td>
        <td>{{ $property->puid }}</td>
        <td>{{ $property->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ $property->category?->name }}</td>
        <td>
            <div class="{{ $property->type == 'rent' ? 'rent-type' : 'sale-type' }}">
                {{ __(ucfirst($property->type)) }}
            </div>
        </td>
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

        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('properties-read')
                    <div>
                        <a href="{{ route('admin.properties.show', $property->id) }}"
                            class="admin-labor-view-btn view-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
                @can('properties-update')
                    <div>
                        <a href="{{ route('admin.properties.edit', $property->id) }}"
                            class="edit-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                        </a>
                    </div>
                @endcan
                @can('properties-delete')
                    <div class="">
                        <a href="{{ route('admin.properties.destroy', $property->id) }}"
                            class="confirm-action delete-action d-flex align-items-center justify-content-center"
                            data-method="DELETE">
                            <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                        </a>
                    </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
