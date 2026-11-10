<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Landlord Name') }}</th>
            <th>{{ __('Package Name') }}</th>
            <th>{{ __('Payment Method') }}</th>
            <th>{{ __('Start Date') }}</th>
            <th>{{ __('End Date') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($subscriptions as $subscription)
        <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($subscription->created_at ?? '') }} - {{ formatted_time($subscription->created_at ?? '') }}</td>
        <td>{{ $subscription->user?->name ?? '' }}</td>
        <td>{{ $subscription->plan?->subscriptionName ?? '' }}</td>
        <td>{{ $subscription->gateway?->name ?? '' }}</td>
        <td>{{ formatted_date($subscription->created_at) }}</td>
        <td>{{ $subscription->created_at ? formatted_date($subscription->created_at->addDays($subscription->duration)) : '' }}</td>

        @if ($subscription->payment_status == 'unpaid')
            <td>{{ __('Unpaid') }}</td>
        @elseif ($subscription->payment_status == 'rejected')
            <td>{{ __('Rejected') }}</td>
        @elseif ($subscription->payment_status == 'paid')
            <td>{{ __('Paid') }}</td>
        @elseif ($subscription->payment_status == 'refunded')
            <td>{{ __('Refund') }}</td>
        @else
            <td>{{ __('Pending') }}</td>
        @endif
        </tr>
        @endforeach
    </tbody>
</table>
