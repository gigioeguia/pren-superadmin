@foreach ($reasons as $reason)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $reason->id }}">
        </td>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $reason->reason }}</td>
        @can('refund_reasons-update')
        <td class="text-start w-150">
            <label class="switch">
                <input type="checkbox" {{ $reason->status == 1 ? 'checked' : '' }} class="status"
                    data-url="{{ route('admin.refund-reason.status', $reason->id) }}">
                <span class="slider round"></span>
            </label>
        </td>
        @endcan


        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('refund_reasons-update')
                <div>
                    <a href="#edit-reason-modal" data-bs-toggle="modal" class="reason-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('admin.refund-reason.update', $reason->id) }}" data-reason="{{ $reason->reason }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                @endcan
                @can('refund_reasons-update')
                <div>
                    <a href="{{ route('admin.refund-reason.destroy', $reason->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
