@extends('layouts.admin.master')

@section('title')
    {{ __('Roles') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="woodland-section-container">
            <div class="woodland-card-header">
                <h3>{{ __('Roles List') }}</h3>
            </div>
            <div class="woodland-card min-vh-100 ">
                <div class="row role-section-container">

                    <div class="col-xl-4 col-lg-6 col-md-6 mt-3">
                        <div class="card shadow-sm border-0 h-100 pt-3">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-sm-5">
                                    <div class="d-flex align-items-end justify-content-center h-100 role-img">
                                        <img class="cross" src="{{ asset('backend/assets/img/icon/user-roles.svg') }}"
                                            alt="">
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="card-body text-sm-end text-center ps-sm-0">
                                        @can('roles-create')
                                        <a href="{{ route('admin.roles.create') }}">
                                            <span
                                                class="btn btn-warning btn-custom-warning add-new-role-btn fw-bold text-uppercase btn-sm mb-1">{{ __('Add New Role') }}</span>
                                        </a>
                                        @endcan
                                        <small
                                            class="mb-0 d-block add-role-pera">{{ __('Add role, if it does not exist') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($roles as $role)
                        <div class="col-xl-4 col-lg-6 col-md-6 mt-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <span
                                            class="total-user">{{ __('Total :count users', ['count' => $role->users_count]) }}</span>
                                        <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                            @foreach ($role->users->take(5) as $user)
                                                <li class="avatar avatar-sm pull-up">
                                                    <img class="rounded-circle role-avatar" src="{{ asset($user->image ?? 'backend/assets/img/profile/user.png') }}"
                                                        alt="{{ ucfirst($user->name) }}">
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end mt-1 pt-25">
                                        <div class="role-heading">
                                            <h4 class="fw-bolder">{{ ucfirst($role->name) }} <i
                                                    class="{{ request('id') == $role->id ? 'fas fa-bell text-red' : '' }}"></i>
                                            </h4>

                                            @can('roles-update')
                                            <a class="text-primary" href="{{ route('admin.roles.edit', $role->id) }}">
                                                <small class="fw-bolder ">{{ __('Edit Role') }}</small>
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
