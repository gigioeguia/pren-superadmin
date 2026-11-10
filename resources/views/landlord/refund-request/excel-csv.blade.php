<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Invoice ID') }}</th>
            <th>{{ __('Tenants Name') }}</th>
            <th>{{ __('Property Name') }}</th>
            <th>{{ __('Property ID') }}</th>
            <th>{{ __('Deposit Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($refund_requests as $refund_request)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatted_date($refund_request->created_at) }}</td>
            <td>{{ $refund_request->invoice_no }}</td>
            <td>{{ $refund_request->tenant->name ?? '' }}</td>
            <td>{{ $refund_request->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
            <td>{{ $refund_request->property->puid ?? '' }}</td>
            <td>{{ currency_format($refund_request->amount ?? 0) }}</td>
            @if ($refund_request->status == 'paid')
            <td class="text-success">{{ __('Paid') }}</td>
            @elseif($refund_request->status == 'unpaid')
            <td class="text-warning">{{ __('Unpaid') }}</td>
            @elseif($refund_request->status == 'rejected')
            <td class="text-danger">{{ __('Rejected') }}</td>
            @endif
        </tr>
        @endforeach

    </tbody>
</table>
