<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Income Category') }}</th>
            <th>{{ __('Payment Type') }}</th>
            <th>{{ __('Reference Number') }}</th>
            <th>{{ __('Income Date') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($incomes as $income)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $income->amount }}</td>
            <td>{{ $income->category?->name }}</td>
            <td>{{ $income->payment_type }}</td>
            <td>{{ $income->reference_no }}</td>
            <td>{{ formatted_date($income->expense_date ?? '') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
