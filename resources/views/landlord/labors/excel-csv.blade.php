<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}</th>
            <th>{{ __('Labor name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone Number') }}</th>
            <th>{{ __('Labor Cost') }}</th>
            <th>{{ __('Gender') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($labors as $labor)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $labor->name }}</td>
            <td>{{ $labor->email }}</td>
            <td>{{ $labor->phone['mobile_no'] ?? '' }} </td>
            <td>{{ currency_format($labor->salary ?? 0) }}</td>
            <td>{{ $labor->gender }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
