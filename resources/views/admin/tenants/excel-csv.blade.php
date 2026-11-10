<table>
    <thead>
        <tr>
            <th class="text-start">{{ __('SL') }}.</th>
            <th class="text-start">{{ __('Tenant Id') }}</th>
            <th class="text-start">{{ __('Tenants Name') }}</th>
            <th class="text-start">{{ __('Tenants Type') }}</th>
            <th class="text-start">{{ __('Property Name') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($tenants as $tenant)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $tenant->unique_id }}</td>
            <td>{{ $tenant->name }}</td>
            <td>{{ $tenant->profile_type }}</td>
            <td>{{ $tenant->user_details?->workplace['company_name'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
