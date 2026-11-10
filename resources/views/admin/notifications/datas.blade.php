@foreach ($notifications  as $notification)
    <tr>
        <td>{{ $loop->index+1 }}</td>
        <td>{{ $notification->data['admin_msg'] ?? '' }}</td>
        <td>{{ $notification->created_at ? formatted_date($notification->created_at) : null }}</td>
        <td>{{ $notification->read_at ? formatted_date($notification->read_at) : null }}</td>
        <td>
            <div class="dropdown table-action">
                <button type="button" data-bs-toggle="dropdown">
                    <i class="far fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    @can('notifications-read')
                    <li>
                        <a href="{{ route('admin.notifications.mtView', $notification->id) }}"><i class="fas fa-eye"></i> @lang('View')</a>
                    </li>
                    @endcan
                </ul>
            </div>
        </td>
    </tr>
@endforeach
