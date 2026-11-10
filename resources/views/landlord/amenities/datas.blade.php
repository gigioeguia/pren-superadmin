@foreach ($amenities as $amenity)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $amenity->id }}">
        </td>
        <td>{{ $amenities->firstItem() + $loop->index }}</td>
        <td>{{ $amenity->name }}</td>
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $amenity->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('landlord.amenities.status', $amenity->id) }}">
                <span class="slider round"></span>
            </label>
        </td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div class="">
                    <a href="#edit-amenity-modal" data-bs-toggle="modal" class="amenity-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.amenities.update', $amenity->id) }}" data-amenity-name="{{ $amenity->name }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.amenities.destroy', $amenity->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
