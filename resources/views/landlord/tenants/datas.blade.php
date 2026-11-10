@foreach ($tenants as $tenant)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $tenant->id }}">
        </td>
        <td>{{ ($tenants->currentPage() - 1) * $tenants->perPage() + $loop->iteration }}</td>
        <td>{{ $tenant->unique_id }}</td>
        <td>{{ $tenant->name }}</td>
        <td>{{ ucfirst($tenant->profile_type) }}</td>
        <td>{{ $tenant->rents?->first()->property->propertyDetail->property_info['property_title'] ?? '' }}</td>

        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a class="view-action d-flex align-items-center justify-content-center" href="{{ route('landlord.tenants.show', $tenant->id) }}">
                        <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                    </a>
                </div>
                <div>
                    <a class="edit-action d-flex align-items-center justify-content-center" href="{{ route('landlord.tenants.edit', $tenant->id) }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>

                <div>
                    <a href="{{ route('landlord.tenants.destroy', $tenant->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>

            </div>
        </td>

    </tr>
@endforeach
