@foreach ($incomes as $income)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $income->id }}">
        </td>
        <td>{{ ($incomes->currentPage() - 1) * $incomes->perPage() + $loop->iteration }}</td>
        <td>{{ currency_format($income->amount ?? 0) }}</td>
        <td>{{ $income->category?->name }}</td>
        <td>{{ ucfirst($income->payment_type) }}</td>
        <td>{{ $income->reference_no }}</td>
        <td>{{ formatted_date($income->income_date ?? '') }}</td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">

                @can('incomes-update')
                <div>
                    <a href="#edit-income-modal" data-bs-toggle="modal" class="income-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.incomes.update', $income->id) }}"
                        data-income-category-id="{{ $income->income_category_id }}"
                        data-income-amount="{{ $income->amount }}"
                        data-income-for="{{ $income->income_for }}"
                        data-payment-type="{{ $income->payment_type }}"
                        data-reference-no="{{ $income->reference_no }}"
                        data-income-date="{{ \Carbon\Carbon::parse($income->income_date)->format('Y-m-d') }}"
                        data-income-note="{{ $income->note }}"
                        >
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan

                @can('incomes-delete')
                <div>
                    <a href="{{ route('admin.incomes.destroy', $income->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
