@foreach ($languages as $language)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $language->id }}">
        </td>
        <td>{{ $languages->firstItem() + $loop->index }}</td>
        <td>
            <img src="{{ asset($language->icon ?? 'backend/assets/img/no_image.jpg') }}" class="table-product-img" alt="">
        </td>
        <td>{{ $language->name }}</td>
        <td>{{ $language->code }}</td>
        <td>
            <label class="switch">
                <input type="checkbox" {{ $language->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.languages.status', $language->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
            <td>
            <label class="switch">
                <input type="checkbox" {{ $language->is_default == 1 ? 'checked' : '' }} class="default-lang"
                    data-url="{{ route('admin.languages.default', $language->id) }}">
                <span class="slider round"></span>
            </label>
        </td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('languages-update')
                    <div>
                        <a href="{{ route('admin.languages.show', $language->code) }}"
                            class="edit-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}">
                        </a>
                    </div>
                @endcan
                @can('languages-update')
                    <div>
                        <a href="javascript:void(0)"
                            class="language-edit-btn view-action d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal" data-bs-target="#editLanguageModal"
                            data-url="{{ route('admin.languages.update', $language->id) }}"
                            data-name="{{ $language->name }}" data-code="{{ $language->code }}"
                            data-order="{{ $language->order }}"
                            data-icon="{{ asset($language->icon ?? 'assets/img/icon/no-img-available.jpg') }}">
                            <img src="{{ asset('backend/assets/img/icon/edit.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
                @can('languages-delete')
                    <div>
                        <a href="{{ route('admin.languages.destroy', $language->id) }}" data-method="DELETE"
                            class="confirm-action delete-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                        </a>
                    </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
