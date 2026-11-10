<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Landlord Name') }}</th>
            <th>{{ __('Payment Type') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Fee') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($withdraws as $withdraw)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $withdraw->user->name ?? '' }}</td>
            <td>{{ $withdraw->account_info->infos['bank_name'] ?? '' }}</td>
            <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
            <td>{{ currency_format($withdraw->charge ?? 0)  }}</td>
        </tr>
        @endforeach

    </tbody>
</table>
