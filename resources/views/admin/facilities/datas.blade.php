@foreach ($facilities as $facility)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $facility->id }}">
        </td>
        <td>{{ $facilities->firstItem() + $loop->index }}</td>
        <td>{{ $facility->landlord?->name }}</td>
        <td>{{ $facility->name }}</td>
        @can('facilities-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $facility->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.facilities.status', $facility->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('facilities-update')
                <div class="">
                    <a href="#edit-facility-modal" data-bs-toggle="modal" class="facility-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.facilities.update', $facility->id) }}" data-facility-name="{{ $facility->name }}"
                        data-landlord-id="{{ $facility->landlord_id }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('facilities-delete')
                <div>
                    <a href="{{ route('admin.facilities.destroy', $facility->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
