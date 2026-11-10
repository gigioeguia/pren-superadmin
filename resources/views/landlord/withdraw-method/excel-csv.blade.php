<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Withdraw Method') }}</th>
            <th>{{ __('Account Holder Name') }}</th>
            <th>{{ __('Swift Code') }}</th>
            <th>{{ __('Bank Name') }}</th>
            <th>{{ __('Account Number') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($withdraw_methods as $withdraw_method)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $withdraw_method->withdraw_method['name'] ?? '' }}</td>
            <td>{{ $withdraw_method->infos['account_name'] ?? '' }}</td>
            <td>{{ $withdraw_method->infos['swift_code'] ?? '' }}</td>
            <td>{{ $withdraw_method->infos['bank_name'] ?? '' }}</td>
            <td>{{ $withdraw_method->infos['account_no'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
