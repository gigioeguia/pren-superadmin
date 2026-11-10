@foreach ($faqs as $faq)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete"
                value="{{ $faq->id }}">
        </td>
        <td>{{ $faqs->firstItem() + $loop->index }}</td>
        <td>{{ $faq->question }}</td>
        <td>{{ Str::words($faq->answer, 10, '...') }}</td>
        <td class="w-150">
            <label class="switch">
                <input type="checkbox" {{ $faq->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.faqs.status', $faq->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.faqs.edit', $faq->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div class="">
                    <a href="{{ route('admin.faqs.destroy', $faq->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
