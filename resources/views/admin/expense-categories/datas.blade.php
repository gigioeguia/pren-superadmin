@foreach ($expense_categories as $category)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $category->id }}">
        </td>
        <td>{{ ($expense_categories->currentPage() - 1) * $expense_categories->perPage() + $loop->iteration }}</td>
        <td>{{ $category->name }}</td>
        <td>{{ Str::limit($category->description, 20) }}</td>
        @can('expense-categories-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $category->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.expense-categories.status', $category->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('expense-categories-update')
                <div>
                    <a href="#edit-expense-categories-modal" data-bs-toggle="modal" class="expense-category-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.expense-categories.update', $category->id) }}" data-expense-category-name="{{ $category->name }}" data-expense-category-description="{{ $category->description }}"
                        >
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('expense-categories-delete')
                <div>
                    <a href="{{ route('admin.expense-categories.destroy', $category->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
