<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Transaction Id') }}</th>
            <th>{{ __('Payment Type') }}</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Amount') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($transactions as $transaction)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($transaction->created_at) }}</td>
            <td>{{ $transaction->uid }}</td>
            <td>{{ $transaction->payment_type }}</td>
            <td>{{ $transaction->payment_method }}</td>
            <td>{{ currency_format($transaction->amount ?? 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
