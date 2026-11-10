@foreach ($security_deposits as $security_deposit)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($security_deposit->created_at ?? '') }} -
            {{ formatted_time($security_deposit->created_at ?? '') }}</td>
        <td>{{ $security_deposit->invoice_no ?? '' }}</td>
        <td>{{ $security_deposit->rent->tenant->name ?? '' }}</td>
        <td>{{ $security_deposit->rent->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ currency_format($security_deposit->rent_advance ?? 0) }}</td>
        <td>{{ currency_format($security_deposit->utility_advance ?? 0) }}</td>
        <td>{{ currency_format($security_deposit->deposit_amount ?? 0) }}</td>
        <td>{{ currency_format($security_deposit->total_amount ?? 0) }}</td>
        <td>{{ $security_deposit->gateway->name ?? '' }}</td>

        <td>
            @if ($security_deposit->status == 'unpaid')
                <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
            @elseif ($security_deposit->status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @elseif ($security_deposit->status == 'paid')
                <span class="badge bg-success">{{ __('Paid') }}</span>
            @elseif ($security_deposit->status == 'pending')
                <span class="badge bg-primary">{{ __('Pending') }}</span>
            @elseif ($security_deposit->status == 'refund')
                <span class="badge bg-info text-dark">{{ __('Refund') }}</span>
            @endif
        </td>

        <td>
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                @if (
                    $security_deposit->status != 'rejected' &&
                        (!in_array($security_deposit->status, ['paid', 'rejected', 'refund']) ||
                            !in_array($security_deposit->status, ['paid', 'refund', 'unpaid'])))
                    <ul class="dropdown-menu">
                        @if (!in_array($security_deposit->status, ['paid', 'rejected', 'refund']))
                            @can('security-deposit-update')
                                <li>
                                    <a href=""
                                        data-url="{{ route('admin.security-deposits.reject', $security_deposit->id) }}"
                                        class="security-deposits-reject-btn" data-bs-toggle="modal"
                                        data-bs-target="#security-deposits-reject-modal">
                                        <i class="fas fa-times"></i>
                                        {{ __('Reject') }}
                                    </a>
                                </li>
                            @endcan
                        @endif

                        @if (!in_array($security_deposit->status, ['paid', 'refund', 'unpaid']))
                            @can('security-deposit-update')
                                <li>
                                    <a href=""
                                        data-url="{{ route('admin.security-deposits.approve', $security_deposit->id) }}"
                                        data-payment-receipt="{{ route('admin.security-deposits.download', $security_deposit->id) }}"
                                        class="security-deposits-approve-btn" data-bs-toggle="modal"
                                        data-bs-target="#security-deposits-approve-modal">
                                        <i class="fas fa-check-square"></i>
                                        {{ __('Approve') }}
                                    </a>
                                </li>
                            @endcan
                        @endif
                    </ul>
                @endif

            </div>
        </td>
    </tr>
@endforeach

@push('modal')
    @include('admin.rent-payment.approved')
@endpush
