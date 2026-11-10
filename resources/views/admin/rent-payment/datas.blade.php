@foreach ($rent_payments as $rent_payment)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($rent_payment->created_at ?? '') }} -
            {{ formatted_time($rent_payment->created_at ?? '') }}</td>
        <td>{{ $rent_payment->invoice_no ?? '' }}</td>
        <td>{{ $rent_payment->rent->tenant->name ?? '' }}</td>
        <td>{{ $rent_payment->rent->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ currency_format($rent_payment->monthly_rent ?? 0) }}</td>
        <td>{{ currency_format($rent_payment->utility_bill ?? 0) }}</td>
        <td>{{ $rent_payment->gateway->name ?? '' }}</td>

        <td>
            @if ($rent_payment->payment_status == 'unpaid')
                <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
            @elseif ($rent_payment->payment_status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @elseif ($rent_payment->payment_status == 'paid')
                <span class="badge bg-success">{{ __('Paid') }}</span>
            @elseif ($rent_payment->payment_status == 'pending')
                <span class="badge bg-primary">{{ __('Pending') }}</span>
            @endif
        </td>


        <td>
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                @if ($rent_payment->payment_status != 'rejected')
                    <ul class="dropdown-menu">
                        @if (!in_array($rent_payment->payment_status, ['paid', 'rejected']))
                            @can('rent-payment-update')
                                <li>
                                    <a href=""
                                        data-url="{{ route('admin.rent-payments.reject', $rent_payment->id) }}"
                                        class="rent-payment-reject-btn" data-bs-toggle="modal"
                                        data-bs-target="#rent-payment-reject-modal">
                                        <i class="fas fa-times"></i>
                                        {{ __('Reject') }}
                                    </a>
                                </li>
                            @endcan
                        @endif

                        @if (!in_array($rent_payment->payment_status, ['paid', 'unpaid']))
                            @can('rent-payment-update')
                                <li>
                                    <a href=""
                                        data-url="{{ route('admin.rent-payments.approve', $rent_payment->id) }}"
                                        data-payment-receipt="{{ route('admin.rent-payments.download', $rent_payment->id) }}"
                                        class="rent-payment-approve-btn" data-bs-toggle="modal"
                                        data-bs-target="#rent-payment-approve-modal">
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
