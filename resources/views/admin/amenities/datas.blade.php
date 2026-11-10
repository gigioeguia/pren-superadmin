@foreach ($amenities as $amenity)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $amenity->id }}">
        </td>
        <td>{{ $amenities->firstItem() + $loop->index }}</td>
        <td>{{ $amenity->landlord?->name }}</td>
        <td>{{ $amenity->name }}</td>
        @can('amenities-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $amenity->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.amenities.status', $amenity->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('amenities-update')
                <div class="">
                    <a href="#edit-amenity-modal" data-bs-toggle="modal" class="amenity-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.amenities.update', $amenity->id) }}" data-amenity-name="{{ $amenity->name }}"
                        data-landlord-id="{{ $amenity->landlord_id }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('amenities-delete')
                <div>
                    <a href="{{ route('admin.amenities.destroy', $amenity->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
