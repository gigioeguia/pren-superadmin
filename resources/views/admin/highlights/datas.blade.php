@foreach ($highlights as $highlight)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete"
                value="{{ $highlight->id }}">
        </td>
        <td>{{ $highlights->firstItem() + $loop->index }}</td>
        <td>
            <img height="45" width="45" class="table-img border-1"
                src="{{ asset($highlight->image ?? 'backend/assets/img/icon/no-img.svg') }}" alt="">
        </td>
        <td>{{ $highlight->short_title }}</td>
        <td>{{ Str::limit($highlight->title, 65, '...') }}</td>
        <td class="w-150">
            <label class="switch">
                <input type="checkbox" {{ $highlight->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.highlights.status', $highlight->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.highlights.edit', $highlight->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div class="">
                    <a href="{{ route('admin.highlights.destroy', $highlight->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
