
@foreach ($tenants as $tenant)
<tr>
    <td class="w-60 checkbox">
        <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $tenant->id }}">
    </td>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $tenant->unique_id }}</td>
    <td>{{ $tenant->name }}</td>
    <td>{{ ucfirst($tenant->profile_type) }}</td>
    <td>{{ $tenant->rents?->first()->property->propertyDetail->property_info['property_title'] ?? '' }}</td>
    <td>
        <div class="d-flex align-items-center justify-content-center action-container gap-2">
            @can('tenants-read')
            <div>
                <a href="{{ route('admin.tenants.show', $tenant->id) }}"
                    class="view-action d-flex align-items-center justify-content-center">
                    <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                </a>
            </div>
            @endcan
            @can('tenants-update')
            <div>
                <a href="{{ route('admin.tenants.edit', $tenant->id) }}"
                    class="edit-action d-flex align-items-center justify-content-center">
                <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                </a>
            </div>
            @endcan
            @can('tenants-delete')
            <div>
                <a href="{{ route('admin.tenants.destroy', $tenant->id) }}"
                    class="confirm-action delete-action d-flex align-items-center justify-content-center"
                    data-method="DELETE">
                    <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                </a>
            </div>
            @endcan
        </div>
    </td>
</tr>
@endforeach
