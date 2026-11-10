<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Currency') }}</th>
            <th>{{ __('Minimum Amount') }}</th>
            <th>{{ __('Maximum Amount') }}</th>
            <th>{{ __('Charge') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($withdrawMethods as $withdrawMethod)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $withdrawMethod->name ?? ''  }}</td>
            <td>{{ $withdrawMethod->currency->name ?? 'null'  }}</td>
            <td>{{ currency_format($withdrawMethod->min_amount ?? 0) }}</td>
            <td>{{ currency_format($withdrawMethod->max_amount ?? 0) }}</td>
            <td>{{ currency_format($withdrawMethod->charge ?? 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
