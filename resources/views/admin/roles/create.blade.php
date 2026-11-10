@extends('layouts.admin.master')

@section('title')
    {{ __('Create Role') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="woodland-section-container">
            <div class="woodland-card-header">
                <h3>{{ __('Add New Role') }}</h3>
                <small class="text-warning"> {{__('Set role permissions')}} </small>
            </div>
            <div class="woodland-card min-vh-100  ">
                <div class="">
                    <div class="row justify-content-center mt-2 roles-permissions">
                        <div class="col-md-12">
                            <form action="{{ route('admin.roles.store') }}" method="post"
                                class="row ajaxform_instant_reload">
                                @csrf

                                <div class="col-12 form-group">
                                    <label for="name" class="required">{{ __('Role Name') }}</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="{{ __('Enter role name') }}" required>
                                </div>

                                <div class="col-12">
                                    <h4 class="mt-4 pt-50 role-permission-title">{{ __('Role Permissions') }}</h4>

                                    <div class="table-responsive">
                                        <table class="table ">
                                            <tbody>
                                                <tr>
                                                    <td class="text-nowrap fw-bolder">
                                                        {{ __('Setup role permissions') }}
                                                    </td>
                                                    <td>
                                                        <div
                                                            class="custom-control d-flex align-items-center justify-content-start gap-2  mr-3 me-lg-5">
                                                            <input type="checkbox"
                                                                class="custom-control-input form-check-input"
                                                                id="selectAll">
                                                            <label class="custom-control-label fw-bold mb-0"
                                                                for="selectAll">{{ __('Select All') }}</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @foreach ($groups as $key => $group)
                                                    <tr>
                                                        <td class="text-nowrap fw-bolder">{{ $key }}</td>
                                                        <td>
                                                            <div class="d-flex">
                                                                @foreach ($group as $permission)
                                                                    <div
                                                                        class="custom-control d-flex align-items-center justify-content-center gap-2  mr-3 me-4">
                                                                        <input type="checkbox" name="permissions[]"
                                                                            value="{{ $permission->id }}"
                                                                            class=" form-check-input"
                                                                            id="id_{{ $permission->id }}">
                                                                        <label class="custom-control-label mb-0"
                                                                            for="id_{{ $permission->id }}">
                                                                            {{ ucfirst(str($permission->name)->explode('-')->last()) }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="add-landlord-button-group">
                                        <button type="reset"
                                            class="btn border-btn role-reset-btn"> {{__('Reset')}} </button>
                                        <button type="submit" class="btn theme-btn submit-btn"> {{__('Save')}} </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
