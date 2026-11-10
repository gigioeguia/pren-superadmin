@foreach ($labors as $labor)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $labor->id }}">
        </td>
        <td>{{ ($labors->currentPage() - 1) * $labors->perPage() + $loop->iteration }}</td>
        <td>{{ $labor->name }}</td>
        <td>{{ $labor->email }}</td>
        <td>{{ $labor->phone['mobile_no'] ?? '' }} </td>
        <td>{{ currency_format($labor->salary ?? 0) }}</td>
        <td>{{ $labor->gender }}</td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                <div>
                    <a href="#labor-view" data-bs-toggle="modal" class="view-item view-action d-flex align-items-center justify-content-center" id="labor-view-{{ $labor->id }}"
                        data-id="{{ $labor->id }}" data-name="{{ $labor->name }}"
                        data-phone="{{ $labor->phone['mobile_no'] ?? '' }}" data-email="{{ $labor->email }}"
                        data-gender="{{ $labor->gender }}" data-salary-type="{{ $labor->salary_type }}"
                        data-salary="{{ currency_format($labor->salary ?? 0) }}">
                        <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                    </a>
                </div>
                <div>
                    <a href="#edit-labor-modal" data-bs-toggle="modal" class="labor-edit-btn edit-action d-flex align-items-center justify-content-center"
                        data-url="{{ route('landlord.labors.update', $labor->id) }}" data-name="{{ $labor->name }}"
                        data-phone='@json($labor->phone)'
                        data-email="{{ $labor->email }}" data-gender="{{ $labor->gender }}"
                        data-salary="{{ $labor->salary }}">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>
                <div>
                    <a href="{{ route('landlord.labors.destroy', $labor->id) }}" class="confirm-action delete-action d-flex align-items-center justify-content-center"
                        data-method="DELETE">
                        <img class="" src="{{ asset('backend/assets/img/icon/delete.svg') }}">
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
