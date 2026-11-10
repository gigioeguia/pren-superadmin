@foreach ($withdraw_methods as $withdraw_method)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item multi-delete" value="{{ $withdraw_method->id }}">
        </td>
        <td>{{ ($withdraw_methods->currentPage() - 1) * $withdraw_methods->perPage() + $loop->iteration }}</td>
        <td>{{ $withdraw_method->withdraw_method->name ?? '' }}</td>
        <td>{{ $withdraw_method->account_no }}</td>
        <td class="text-center cursor-pointer">
            <a data-bs-toggle="modal" data-url="{{ route('landlord.withdraw.store') }}"
                data-id="{{ $withdraw_method->id }}" data-bank-name="{{ $withdraw_method->infos['bank_name'] ?? '' }}"
                data-bank-acc-no="{{ $withdraw_method->account_no }}"
                data-method-min-amount="{{ $withdraw_method->withdraw_method->min_amount ?? '' }}"
                data-charge="{{ $withdraw_method->withdraw_method->charge_type == 'fixed' ? currency_format($withdraw_method->withdraw_method->charge) : $withdraw_method->withdraw_method->charge . '%' }}"
                data-bs-target="#payoutRequestModal"
                class="badge text-bg-success payout-request-btn">{{ __('Payout Request') }}</a>
        </td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="#edit-bank-modal" data-bs-toggle="modal"
                        class="withdraw-method-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.withdraw-method.update', $withdraw_method->id) }}"
                        data-method='@json($withdraw_method)'>
                        <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.withdraw-method.destroy', $withdraw_method->id) }}"
                        class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
