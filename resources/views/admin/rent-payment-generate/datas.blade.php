@foreach ($rent_payments as $rent_payment)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($rent_payment->created_at ?? '') }}</td>
        <td>{{ $rent_payment->rent->landlord->name ?? '' }}</td>
        <td>{{ currency_format($rent_payment->total_amount ?? 0) }}</td>
        <td>{{ currency_format($rent_payment->charge ?? 0) }}</td>
        <td class="text-center">
            @if ($rent_payment->payment_status == 'unpaid')
                <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
            @elseif ($rent_payment->payment_status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @elseif ($rent_payment->payment_status == 'paid')
                <span class="badge bg-success">{{ __('Paid') }}</span>
            @elseif ($rent_payment->payment_status == 'pending')
                <span class="badge bg-primary">{{ __('Pending') }}</span>
            @endif
        </td>
    </tr>
@endforeach
