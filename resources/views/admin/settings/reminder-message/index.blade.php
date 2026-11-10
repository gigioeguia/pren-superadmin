@extends('layouts.admin.master')

@section('title')
    {{ __('Reminder Messages List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row ">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Reminder Messages') }}</h3>
                    @can('reminder-message-create')
                    <a href="#add-reminder-message-modal" data-bs-toggle="modal" class="modal-btn"><i class="fal fa-plus-circle"></i>{{ __('Add Message') }}
                    </a>
                    @endcan
                </div>
                <div class="woodland-card">
                    <div class="row g-4">
                        @foreach ($messages as $message)
                            <div class="col-lg-6">
                                <div class='reminder-header d-flex align-items-center justify-content-between'>
                                    <h6>{{ $message->value['title'] ?? '' }}</h6>
                                    <div class="dropdown table-action">
                                        <button type="button" data-bs-toggle="dropdown">
                                            <i class="far fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('reminder-message-update')
                                            <a href="#edit-reminder-modal" data-bs-toggle="modal"
                                            class="btn action-btn bg-light-orange message-edit-btn"
                                            data-url="{{ route('admin.reminder-messages.update', $message->id) }}"
                                            data-message-title="{{ $message->value['title'] ?? '' }}"
                                            data-message-payment-method="{{ $message->value['payment_method'] ?? '' }}">
                                            <i class="far fa-edit"></i> {{__('Edit')}} </a>
                                            @endcan

                                            @can('reminder-message-delete')
                                            <a href="{{ route('admin.reminder-messages.destroy', $message->id) }}"
                                                class="confirm-action" data-method="DELETE">
                                                <i class="fas fa-trash"></i> {{__('Delete')}} </a>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                <div class="reminder-content">
                                    <p>{{ $message->value['payment_method'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('modal')
    @include('admin.settings.reminder-message.create')
    @include('admin.settings.reminder-message.edit')
@endpush
