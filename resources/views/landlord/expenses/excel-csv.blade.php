<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Expense Category') }}</th>
            <th>{{ __('Payment Type') }}</th>
            <th>{{ __('Reference Number') }}</th>
            <th>{{ __('Expense Date') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($expenses as $expense)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $expense->amount }}</td>
            <td>{{ $expense->category?->name }}</td>
            <td>{{ $expense->payment_type }}</td>
            <td>{{ $expense->reference_no }}</td>
            <td>{{ formatted_date($expense->expense_date ?? '') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
