<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date $ Time') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenant Name') }}</th>
            <th>{{ __('Landlord Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Property ID') }}</th>
            <th>{{ __('Total Deposit Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($refund_requests as $refund_request)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($refund_request->created_at ) }} - {{ formatted_time($refund_request->created_at ) }}</td>
            <td>123456</td>
            <td>{{ $refund_request->tenant->name ?? '' }}</td>
            <td>{{ $refund_request->landlord->name ?? '' }}</td>
            <td>{{ $refund_request->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ $refund_request->property->puid ?? '' }}</td>
            <td>{{ currency_format($refund_request->deposit->total_amount ?? 0) }}</td>
            <td>
                @if ($refund_request->status == 'unpaid')
                <p class="text-warning">{{ __('Unpaid') }}</p>
                @elseif ($refund_request->status == 'paid')
                    <p class="text-success">{{ __('Paid') }}</p>
                @elseif ($refund_request->status == 'rejected')
                    <p class="text-danger">{{ __('Rejected') }}</p>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
