<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Landlord Name') }}</th>
            <th>{{ __('Subscription type') }}</th>
            <th>{{ __('Expire Date') }}</th>
            <th>{{ __('Balance') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($landlords as $landlord)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $landlord->unique_id }}</td>
                <td>{{ $landlord->name }}</td>
                <td>{{ $landlord->plan_subscribe?->plan?->subscriptionName }}</td>
                <td>{{ formatted_date($landlord->will_expire ?? '') }}</td>
                <td>{{ currency_format($landlord->balance ?? 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
