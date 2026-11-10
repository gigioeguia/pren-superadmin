@foreach ($house_types as $type)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $type->id }}">
        </td>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $type->name }}</td>
        @can('house-types-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $type->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.house-types.status', $type->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('house-types-update')
                <div class="">
                    <a href="#edit-house-types-modal" data-bs-toggle="modal" class="house-type-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.house-types.update', $type->id) }}" data-house-type-name="{{ $type->name }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('house-types-delete')
                <div>
                    <a href="{{ route('admin.house-types.destroy', $type->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
