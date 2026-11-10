@foreach ($landlords as $landlord)
    <tr>
        <td class="w-60 checkbox">
            <input type="checkbox" name="ids[]" class="delete-checkbox-item  multi-delete" value="{{ $landlord->id }}">
        </td>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $landlord->unique_id }}</td>
        <td>{{ $landlord->name }}</td>
        @if($landlord->plan_subscribe?->number_of_property == "-1")
        <td>{{ __('Unlimited') }}</td>
        @else
        <td>{{ $landlord->plan_subscribe?->number_of_property }}</td>
        @endif

        @if($landlord->plan_subscribe?->number_of_tenant == "-1")
        <td>{{ __('Unlimited') }}</td>
        @else
        <td>{{ $landlord->plan_subscribe?->number_of_tenant }}</td>
        @endif

        @if($landlord->plan_subscribe?->number_of_labor == "-1")
        <td>{{ __('Unlimited') }}</td>
        @else
        <td>{{ $landlord->plan_subscribe?->number_of_labor }}</td>
        @endif
        <td>{{ $landlord->plan_subscribe?->plan_name }}</td>
        <td>{{  formatted_date($landlord->will_expire ?? '') }}</td>
        <td>{{ currency_format($landlord->balance ?? 0) }}</td>

        <td>
            <div class="d-flex align-items-center justify-content-center action-container gap-2">
                @can('landlords-read')
                <div>
                    <a href="javascript:void(0)" class="landlord-view-btn view-action d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal"
                        data-bs-target="#landlord-view"
                        data-landlord-id="{{ $landlord->unique_id }}"
                        data-landlord-name="{{ $landlord->name }}"
                        data-number-of-property="{{ $landlord->plan_subscribe?->number_of_property == '-1' ? 'Unlimited' : $landlord->plan_subscribe?->number_of_property }}"
                        data-number-of-tenant="{{ $landlord->plan_subscribe?->number_of_tenant == '-1' ? 'Unlimited' : $landlord->plan_subscribe?->number_of_tenant }}"
                        data-number-of-rent="{{ $landlord->plan_subscribe?->number_of_labor == '-1' ? 'Unlimited' : $landlord->plan_subscribe?->number_of_labor }}"
                        data-subscription-type="{{ $landlord->plan_subscribe?->plan_name }}"
                        data-landlord-status="{{ $landlord->will_expire >= today() ? 'active' : 'expired' }}"
                        data-duration-date="{{ $landlord->created_at ? formatted_date($landlord->created_at->addDays($landlord->plan_subscribe?->duration)) : '' }}"
                        data-landlord-balance="{{ currency_format($landlord->balance ?? 0) }}"
                        >
                        <img src="{{ asset('backend/assets/img/icon/view.svg') }}" alt="View">
                    </a>
                </div>
                @endcan
                @can('landlords-update')
                <div>
                    <a href="{{ route('admin.landlords.edit', $landlord->id) }}"
                        class="edit-action d-flex align-items-center justify-content-center">
                        <img class="" src="{{ asset('backend/assets/img/icon/edit.svg') }}">
                    </a>
                </div>

                @endcan
                @can('landlords-delete')
                <div>
                    <a href="{{ route('admin.landlords.destroy', $landlord->id) }}"
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
