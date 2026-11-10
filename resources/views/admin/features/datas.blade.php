@foreach ($features as $feature)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $feature->id }}">
        </td>
        <td>{{ $features->firstItem() + $loop->index }}</td>
        <td>
            <img class="table-img" src="{{ asset($feature->image ?? 'backend/assets/img/icon/no-img.svg') }}"
                alt="img">
        </td>
        <td>{{ $feature->title }}</td>
        <td class="w-150">
            <label class="switch">
                <input type="checkbox" {{ $feature->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.features.status', $feature->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.features.edit', $feature->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div class="">
                    <a href="{{ route('admin.features.destroy', $feature->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
