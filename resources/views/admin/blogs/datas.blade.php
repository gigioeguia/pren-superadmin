@foreach ($blogs as $blog)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete"
                value="{{ $blog->id }}">
        </td>
        <td>{{ $blogs->firstItem() + $loop->index }}</td>
        <td>
            <img height="45" width="45" class="table-img border-1"
                src="{{ asset($blog->image ?? 'backend/assets/img/icon/no-img.svg') }}" alt="">
        </td>
        <td>{{ Str::limit($blog->title, 65, '...') }}</td>
        <td class="w-150">
            <label class="switch">
                <input type="checkbox" {{ $blog->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.blogs.status', $blog->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div class="">
                    <a href="{{ route('admin.blogs.destroy', $blog->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.blogs.filter.comment', $blog->id) }}"class="view-action d-flex align-items-center justify-content-center">
                        <i class='fas fa-comment text-success'></i>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
