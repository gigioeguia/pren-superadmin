@foreach ($testimonials as $testimonial)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $testimonial->id }}">
        </td>
        <td>{{ $testimonials->firstItem() + $loop->index }}</td>
        <td>
            @for ($i = 0; $i < 5; $i++)
                <i @class(['fas fa-star ', 'text-warning' => $testimonial->star > $i])></i>
            @endfor
        </td>
        <td>{{ $testimonial->client_name }}</td>
        <td>{{ $testimonial->work_at }}</td>
        <td>
            <img class="table-img" src="{{ asset($testimonial->client_image ?? 'backend/assets/img/icon/no-img.svg') }}" alt="img">
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div class="">
                    <a href="{{ route('admin.testimonials.destroy', $testimonial->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
