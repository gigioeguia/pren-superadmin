@foreach ($users as $user)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $user->id }}">
        </td>
        <td>{{ $users->firstItem() + $loop->index }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->phone['mobile_no'] ?? '' }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->role }}</td>
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('staffs-update')
                    <div>
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                            class="edit-action d-flex align-items-center justify-content-center">
                            <img src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                        </a>
                    </div>
                @endcan
                @can('staffs-delete')
                    <div class="">
                        <a href="{{ route('admin.users.destroy', $user->id) }}"
                            class="confirm-action delete-action d-flex align-items-center justify-content-center"
                            data-method="DELETE">
                            <img src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                        </a>
                    </div>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
