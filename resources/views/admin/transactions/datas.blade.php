@foreach ($transactions as $transaction)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($transaction->created_at) }}</td>
        <td>{{ $transaction->uid }}</td>
        <td>{{ ucfirst($transaction->payment_type) }}</td>
        <td>{{ $transaction->gateway->name ?? '' }}</td>
        <td>{{ currency_format($transaction->amount ?? 0) }} </td>

        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('transactions-read')
                    <div>
                        <a href="javascript:void(0)"
                            class="admin-transaction-view-btn view-action d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal"
                            data-bs-target="#admin-transaction-view"
                            data-admin-invoice-number="{{ $transaction->invoice_no }}"
                            data-admin-transaction-name="{{ $transaction->paidBy->name ?? '' }}"
                            data-admin-transaction-date="{{ formatted_date($transaction->created_at) }}"
                            data-admin-transaction-payment="{{ $transaction->payment_method }}"
                            data-admin-transaction-amount="{{ currency_format($transaction->amount ?? 0) }}"
                            data-admin-transaction-type="{{ $transaction->payment_type }}">
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
