@foreach ($comments as $comment)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete"
                value="{{ $comment->id }}">
        </td>
        <td>{{ $comments->firstItem() + $loop->index }}</td>
        <td>{{ $comment->name }}</td>
        <td>{{ $comment->email }}</td>
        <td>{{  Str::limit($comment->comment, 20, '...') }}</td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.comments.destroy', $comment->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
