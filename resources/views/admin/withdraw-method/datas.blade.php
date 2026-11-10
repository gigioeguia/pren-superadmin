@foreach ($withdrawMethods as $withdrawMethod)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $withdrawMethod->id }}">
        </td>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $withdrawMethod->name ?? '' }}</td>
        <td>{{ $withdrawMethod->currency->name ?? 'null' }}</td>
        <td>{{ currency_format($withdrawMethod->min_amount ?? 0) }}</td>
        <td>{{ currency_format($withdrawMethod->max_amount ?? 0) }}</td>
        <td>
            @if ($withdrawMethod->charge_type == 'fixed')
                {{ currency_format($withdrawMethod->charge ?? 0) }}
            @else
                {{ ($withdrawMethod->charge ?? '0') }} %
            @endif
        </td>
        @can('withdraw-methods-update')
            <td class="text-start w-150 d-print-none">
                <label class="switch">
                    <input type="checkbox" {{ $withdrawMethod->status == 1 ? 'checked' : '' }} class="status"
                        data-url="{{ route('admin.withdraw-methods.status', $withdrawMethod->id) }}">
                    <span class="slider round"></span>
                </label>
            </td>
        @endcan
        <td class="d-print-none">
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('withdraw-methods-read')
                    <div>
                        <a href="javascript:void(0)"
                            class="withdraw-method-view-btn view-action d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal"
                            data-bs-target="#withdraw-method-view-modal"
                            data-payment-method="{{ $withdrawMethod->name ?? '' }}"
                            data-method-currency="{{ $withdrawMethod->currency->name ?? '' }}"
                            data-payment-min-amount="{{ currency_format($withdrawMethod->min_amount ?? 0) }}"
                            data-payment-max-amount="{{ currency_format($withdrawMethod->max_amount ?? 0) }}"
                            data-payment-charge="{{ currency_format($withdrawMethod->charge ?? 0) }}"
                            data-method-status="{{ ($withdrawMethod->status == 1 ? 'Active' : 'Deactive') ?? '' }}">
                            <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                        </a>
                    </div>
                @endcan
                @can('withdraw-methods-update')
                    <div>
                        <a href="{{ route('admin.withdraw-methods.edit', $withdrawMethod->id) }}"
                            class="edit-action d-flex align-items-center justify-content-center">
                            <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                        </a>
                    </div>
                @endcan
                @can('withdraw-methods-delete')
                    <div>
                        <a href="{{ route('admin.withdraw-methods.destroy', $withdrawMethod->id) }}"
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
