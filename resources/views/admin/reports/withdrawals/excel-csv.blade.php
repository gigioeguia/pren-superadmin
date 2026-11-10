<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date & Time') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Landlord Name') }}</th>
            <th>{{ __('Withdraw Method') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Fee') }}</th>
            <th>{{ __('Payment Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($withdraws as $withdraw)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($withdraw->created_at ?? '') }} - {{ formatted_time($withdraw->created_at ) }}</td>
            <td>{{ $withdraw->invoice_no }}</td>
            <td>{{ $withdraw->user->name ?? '' }}</td>
            <td>{{ $withdraw->account_info->infos['bank_name'] ?? '' }}</td>
            <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
            <td>{{ currency_format($withdraw->charge ?? 0) }}</td>

            @if ($withdraw->status == 'pending')
                <td>{{ __('Pending') }}</td>
            @elseif ($withdraw->status == 'rejected')
                <td>{{ __('Rejected') }}</td>
            @else
                <td>{{ __('Approved') }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
