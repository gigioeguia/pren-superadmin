<table>
    <thead>
        <tr>
            <th>{{ __('SL') }}.</th>
            <th>{{ __('Tenant Id') }}</th>
            <th>{{ __('Tenants Name') }}</th>
            <th>{{ __('Tenants Type') }}</th>
            <th>{{ __('Property Name') }}</th>
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
