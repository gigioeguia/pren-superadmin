@extends('layouts.admin.master')

@section('title')
    {{ __('Assigned Role') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
      <div class="woodland-section-container">
          <div class="woodland-card-header">
              <h3>{{ __('Assigned Role') }}</h3>
          </div>
          <div class="woodland-card min-vh-100 ">
              <div class="card-body">
                  <div class="row justify-content-center mt-4">
                      <div class="col-md-6">
                          <div class="card border ">
                              <div class="card-body">


                                  <form action="{{ route('admin.permissions.store') }}" method="post"
                                      class="row ajaxform_instant_reload">
                                      @csrf

                                      <div class="col-12 form-group mb-3">
                                          <label for="user" class="required">{{ __('User') }}</label>
                                          <select name="user" id="user" class="form-control" required>
                                              <option value="">{{ __('-Select User-') }}</option>
                                              @foreach ($users as $user)
                                                  <option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                                              @endforeach
                                          </select>
                                      </div>

                                      <div class="col-12 form-group mb-3">
                                          <label for="role" class="required">{{ __('Role') }}</label>
                                          <select name="roles" id="role" class="form-control" required>
                                              <option value=""> {{__('-Select Role-')}} </option>
                                              @foreach ($roles as $role)
                                                  <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      @can('permissions-create')
                                      <div class="add-landlord-button-group">
                                        <button type="reset"
                                            class="btn border-btn role-reset-btn"> {{__('Reset')}} </button>
                                        <button type="submit" class="btn theme-btn submit-btn"> {{__('Save')}} </button>
                                    </div>
                                      @endcan
                                  </form>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
    </div>
@endsection
