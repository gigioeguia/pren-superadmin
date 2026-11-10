@foreach ($notifications  as $notification)
    <tr>
        <td>{{ $loop->index+1 }}</td>
        <td>{{ $notification->data['landlord_msg'] ?? '' }}</td>
        <td>{{ $notification->created_at ? formatted_date($notification->created_at) : null }}</td>
        <td>{{ $notification->read_at ? formatted_date($notification->read_at) : null }}</td>
        <td>
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{ route('landlord.notifications.mtView', $notification->id) }}"><i class="fas fa-eye"></i> @lang('View')</a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach
