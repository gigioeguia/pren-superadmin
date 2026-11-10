<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Request Date') }}</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Fee') }}</th>
            <th>{{ __('Generated Time') }}</th>
            <th>{{ __('Payment Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($withdraws as $withdraw)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ formatted_date($withdraw->created_at) }} - {{ formatted_time($withdraw->created_at) }}</td>
                <td>{{ $withdraw->account_info->withdraw_method->name ?? '' }}</td>
                <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
                @if ($withdraw->account_info->withdraw_method->charge_type == 'percentage')
                    <td>{{ $withdraw->charge . '%' }}</td>
                @else
                    <td>{{ currency_format($withdraw->charge ?? 0) }}</td>
                @endif
                <td>{{ formatted_time($withdraw->created_at) }}</td>

                @if ($withdraw->status == 'pending')
                    <td class="text-warning">{{ __('Pending') }}</td>
                @elseif ($withdraw->status == 'rejected')
                    <td class="text-danger">{{ __('Rejected') }}</td>
                @else
                    <td class="text-success">{{ __('Approve') }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
