@foreach ($facilities as $facility)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $facility->id }}">
        </td>
        <td>{{ $facilities->firstItem() + $loop->index }}</td>
        <td>{{ $facility->name }}</td>
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $facility->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('landlord.facilities.status', $facility->id) }}">
                <span class="slider round"></span>
            </label>
        </td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div class="">
                    <a href="#edit-facility-modal" data-bs-toggle="modal" class="facility-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.facilities.update', $facility->id) }}" data-facility-name="{{ $facility->name }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.facilities.destroy', $facility->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
