@foreach ($subscriptions as $subscription)
    <tr>
        <td>{{ $subscriptions->firstItem() + $loop->index }}</td>
        <td>{{ formatted_date($subscription->created_at ?? '') }} -
            {{ formatted_time($subscription->created_at ?? '') }}</td>
        <td>{{ $subscription->user?->name ?? '' }}</td>
        <td>{{ $subscription->plan?->subscriptionName ?? '' }}</td>
        <td>{{ $subscription->gateway?->name ?? '' }}</td>
        <td>{{ formatted_date($subscription->created_at) }}</td>
        <td>{{ $subscription->created_at ? formatted_date($subscription->created_at->addDays($subscription->duration)) : '' }}
        </td>
        <td>
            @if ($subscription->payment_status == 'unpaid')
                <span class="badge bg-warning text-dark status-badge">{{ __('Unpaid') }}</span>
            @elseif ($subscription->payment_status == 'rejected')
                <span class="badge bg-danger status-badge">{{ __('Rejected') }}</span>
            @elseif ($subscription->payment_status == 'paid')
                <span class="badge bg-success status-badge">{{ __('Paid') }}</span>
            @endif
        </td>
    </tr>
@endforeach
