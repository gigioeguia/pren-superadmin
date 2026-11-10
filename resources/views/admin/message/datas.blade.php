@foreach ($messages as $message)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $message->id }}">
        </td>
        <td>{{ $messages->firstItem() + $loop->index }}</td>
        <td>{{ $message->name ?? '' }}</td>
        <td>{{ $message->phone ?? '' }}</td>
        <td>{{ $message->email ?? '' }}</td>
        <td>{{ $message->company_name ?? 'Unknown' }}</td>
        <td>{{ Str::words($message->message ?? '', 3, '...') }}</td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('messages-read')
                    <div>
                        <a href="javascript:void(0)" class="message-view-btn view-action d-flex align-items-center justify-content-center" data-bs-toggle="modal"
                            data-bs-target="#message-view-modal"
                            data-name="{{ $message->name ?? '' }}"
                            data-phone="{{ $message->phone ?? '' }}"
                            data-email="{{ $message->email ?? '' }}"
                            data-company-name="{{ $message->company_name ?? 'Unknown' }}"
                            data-message="{{ $message->message ?? '' }}"
                            >
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
                @can('messages-delete')
                    <div>
                        <a href="{{ route('admin.messages.destroy', $message->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                            data-method="DELETE">
                            <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                        </a>
                    </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
