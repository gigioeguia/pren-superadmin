@foreach ($transactions as $transaction)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($transaction->created_at ?? '') }}</td>
        <td>{{ $transaction->uid }}</td>
        <td>{{ ucfirst($transaction->payment_type) }}</td>
        <td>{{ $transaction->gateway->name ?? '' }}</td>
        <td class="text-center">{{ currency_format($transaction->amount ?? 0) }} </td>
    </tr>
@endforeach


