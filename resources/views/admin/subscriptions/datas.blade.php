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
                <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
            @elseif ($subscription->payment_status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @elseif ($subscription->payment_status == 'paid')
                <span class="badge bg-success">{{ __('Paid') }}</span>
            @endif
        </td>

        <td class="d-print-none">
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    @can('subscriptions-read')
                        <li>
                            <a target="_blank" href="{{ route('admin.subscriptions.invoice', $subscription->id) }}">
                                <i class="fas fa-file"></i>
                                {{ __('Invoice') }}
                            </a>
                        </li>
                    @endcan

                    @if ($subscription->payment_status == 'unpaid')
                        @can('subscriptions-update')
                            <li>
                                <a href="#approve-modal" class="modal-approve" data-bs-toggle="modal" data-bs-target="#approve-modal" data-subs-receipt="{{ route('admin.subscriptions.download', $subscription->id) }}" data-url="{{ route('admin.subscriptions.paid', $subscription->id) }}">
                                    <i class="fal fa-check"></i>
                                    {{ __('Accept') }}
                                </a>
                            </li>
                        @endcan
                        @can('subscriptions-update')
                            <li>
                                <a href="#reject-modal" class="modal-reject" data-bs-toggle="modal"
                                    data-bs-target="#reject-modal"
                                    data-url="{{ route('admin.subscriptions.reject', $subscription->id) }}">
                                    <i class="fal fa-times"></i>
                                    {{ __('Reject') }}
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>
            </div>
        </td>
    </tr>
@endforeach
