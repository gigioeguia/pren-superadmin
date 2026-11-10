@foreach ($withdraws as $withdraw)
    <tr>
        <td>{{ $withdraws->firstItem() + $loop->index }}</td>
        <td>{{ formatted_date($withdraw->created_at ?? '') }}</td>
        <td>{{ $withdraw->user->name ?? '' }}</td>
        <td>{{ $withdraw->account_info->withdraw_method->name ?? '' }}</td>
        <td>{{ currency_format($withdraw->amount ?? 0) }}</td>
        <td>{{ currency_format($withdraw->charge ?? 0) }}</td>
        <td>
            @if ($withdraw->status == 'pending')
                <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
            @elseif ($withdraw->status == 'rejected')
                <span class="badge bg-danger">{{ __('Rejected') }}</span>
            @else
                <span class="badge bg-success">{{ __('Approved') }}</span>
            @endif
        </td>


        <td class="d-print-none">
                 <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('withdraw-request-read')
                    <div>
                        <a href="{{ route('admin.withdraw-request.show', $withdraw->id) }}"
                            class="admin-labor-view-btn view-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
                @if ($withdraw->status == 'pending')
                @can('withdraw-request-update')
                    <div>
                        <a href="" data-url="{{ route('admin.withdraw.approve', $withdraw->id) }}"
                            data-bs-toggle="modal" data-bs-target="#withdraw-approve-modal"
                            class="edit-action d-flex align-items-center justify-content-center approve-btn">
                            <img src="{{ asset('backend/assets/img/icon/approve.svg') }}">
                        </a>
                    </div>
                @endcan
                @can('withdraw-request-update')
                    <div class="">
                        <a href="" data-url="{{ route('admin.withdraw.reject', $withdraw->id) }}"
                            data-bs-toggle="modal" data-bs-target="#withdraw-reject-modal"
                            class="delete-action d-flex align-items-center justify-content-center reject-btn">
                            <img src="{{ asset('backend/assets/img/icon/reject.svg') }}">
                        </a>
                    </div>
                @endcan
                @endif
            </div>
        </td>
    </tr>
@endforeach
