@foreach ($security_deposits as $deposit)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ formatted_date($deposit->created_at ?? '') }} - {{ formatted_time($deposit->created_at ?? '') }}</td>
        <td>{{ $deposit->invoice_no ?? '' }}</td>
        <td>{{ $deposit->tenant?->name ?? '' }}</td>
        <td>{{ $deposit->property?->propertyDetail->property_info['property_title'] ?? '' }}</td>
        <td>{{ currency_format($deposit->total_amount ?? 0) }}</td>
        <td>{{ currency_format($deposit->utility_advance ?? 0) }}</td>
        <td>{{ $deposit->gateway?->name ?? '' }}</td>

        <td>
            @if ($deposit->status == 'unpaid')
                <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
            @elseif ($deposit->status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @elseif ($deposit->status == 'paid')
                <span class="badge bg-success">{{ __('Paid') }}</span>
            @elseif ($deposit->status == 'pending')
                <span class="badge bg-primary">{{ __('Pending') }}</span>
            @elseif ($deposit->status == 'refund')
                <span class="badge bg-info text-dark">{{ __('Refund') }}</span>
            @endif
        </td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a class="view-action d-flex align-items-center justify-content-center" href="{{ route('admin.security-deposit-reports.show', $deposit->id) }}">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.488 1.87607H9.68695V0H2.4774V1.87607H1.67634C1.25143 1.87607 0.843925 2.07373 0.543469 2.42556C0.243013 2.77739 0.0742188 3.25458 0.0742188 3.75215V8.91135H1.6443V8.01552H0.87528V3.75215C0.87528 3.50336 0.959677 3.26477 1.10991 3.08885C1.26013 2.91294 1.46389 2.81411 1.67634 2.81411H10.488C10.7005 2.81411 10.9042 2.91294 11.0544 3.08885C11.2047 3.26477 11.2891 3.50336 11.2891 3.75215V7.97331H10.5201V8.91135H12.0901V3.75215C12.0901 3.25458 11.9213 2.77739 11.6209 2.42556C11.3204 2.07373 10.9129 1.87607 10.488 1.87607ZM8.88589 1.87607H3.27846V0.938036H8.88589V1.87607Z" fill="#00A551" />
                            <path d="M10.0908 6.09766H2.08022C1.97399 6.09766 1.87211 6.14707 1.797 6.23503C1.72189 6.32299 1.67969 6.44228 1.67969 6.56667C1.67969 6.69107 1.72189 6.81036 1.797 6.89832C1.87211 6.98628 1.97399 7.03569 2.08022 7.03569H2.48075V12.6639H9.6903V7.03569H10.0908C10.1971 7.03569 10.2989 6.98628 10.374 6.89832C10.4492 6.81036 10.4914 6.69107 10.4914 6.56667C10.4914 6.44228 10.4492 6.32299 10.374 6.23503C10.2989 6.14707 10.1971 6.09766 10.0908 6.09766ZM8.88924 11.7259H3.28181V7.03569H8.88924V11.7259Z" fill="#00A551" />
                            <path d="M9.68555 3.77148H10.4855V4.70952H9.68555V3.77148Z" fill="#00A551" />
                        </svg>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach


