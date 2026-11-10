@foreach ($expenses as $expense)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $expense->id }}">
        </td>
        <td>{{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}</td>
        <td>{{ currency_format($expense->amount ?? 0) }}</td>
        <td>{{ $expense->category?->name }}</td>
        <td>{{ ucfirst($expense->payment_type) }}</td>
        <td>{{ $expense->reference_no }}</td>
        <td>{{ formatted_date($expense->expense_date ?? '') }}</td>
        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="#edit-expense-modal" data-bs-toggle="modal" class="expense-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.expenses.update', $expense->id) }}"
                        data-expense-category-id="{{ $expense->expense_category_id }}"
                        data-expense-amount="{{ $expense->amount }}"
                        data-expense-for="{{ $expense->expense_for }}"
                        data-payment-type="{{ $expense->payment_type }}"
                        data-reference-no="{{ $expense->reference_no }}"
                        data-expense-date="{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}"
                        data-expense-note="{{ $expense->note }}"
                        >
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.expenses.destroy', $expense->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
