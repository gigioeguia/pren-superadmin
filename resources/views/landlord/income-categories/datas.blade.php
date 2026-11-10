@foreach ($income_categories as $category)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $category->id }}">
        </td>
        <td>{{ ($income_categories->currentPage() - 1) * $income_categories->perPage() + $loop->iteration }}</td>
        <td>{{ $category->name }}</td>
        <td>{{ Str::limit($category->description, 20) }}</td>
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $category->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('landlord.income-categories.status', $category->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="#edit-income-categories-modal" data-bs-toggle="modal" class="income-category-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.income-categories.update', $category->id) }}"
                        data-income-category-name="{{ $category->name }}"
                        data-income-category-description="{{ $category->description }}"
                        >
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.income-categories.destroy', $category->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
