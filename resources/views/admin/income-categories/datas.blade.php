@foreach ($income_categories as $category)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $category->id }}">
        </td>
        <td>{{ ($income_categories->currentPage() - 1) * $income_categories->perPage() + $loop->iteration }}</td>
        <td>{{ $category->name }}</td>
        <td>{{ Str::limit($category->description, 20) }}</td>
        @can('income-categories-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $category->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.income-categories.status', $category->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('income-categories-update')
                <div>
                    <a href="#edit-income-categories-modal" data-bs-toggle="modal" class="income-category-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.income-categories.update', $category->id) }}"
                        data-income-category-name="{{ $category->name }}"
                        data-income-category-description="{{ $category->description }}"
                        >
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('income-categories-delete')
                <div>
                    <a href="{{ route('admin.income-categories.destroy', $category->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
