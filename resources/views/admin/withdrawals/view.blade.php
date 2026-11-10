@extends('layouts.admin.master')

@section('title')
    {{ __('Withdraw') }}
@endsection

@section('main_content')
    <div class=" woodland-section-container">
        <div class="container-fluid woodland-card">
            <div class="">
                <div class="card-body">
                    <div class="table-header woodland-card-header">
                        <h3 class="fw-bold">{{ __('Withdraw Request View') }}</h3>
                    </div>
                </div>

                <div>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="info-container">
                                <div class="">
                                    <div class="table-header-title ">
                                        <h4 class="fw-bold">{{ __('View Details') }}</h4>
                                    </div>
                                    <div class="personal-info m-3 ">
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Date') }}</p>
                                            </div>
                                            <div class="col-8">:
                                                {{ formatted_date($withdraw->created_at) }}<span id="expenses_category_name"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Landlord Name') }}</p>
                                            </div>
                                            <div class="col-8">: {{ $withdraw->user->name }}<span
                                                    id="expenses_category_description"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Payment Method') }}</p>
                                            </div>
                                            <div class="col-8">: {{ $withdraw->account_info->withdraw_method->name ?? ''  }}<span
                                                    id="expenses_category_description"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Total Amount') }}</p>
                                            </div>
                                            <div class="col-8 mb-2">: {{ currency_format($withdraw->amount ?? 0) }}<span
                                                    id="expenses_category_description"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Charge') }}</p>
                                            </div>
                                            <div class="col-8">: {{ currency_format($withdraw->charge ?? 0) }}<span
                                                    id="expenses_category_description"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Withdraw Amount') }}</p>
                                            </div>
                                            <div class="col-8">:
                                                {{ currency_format( $withdraw->amount ?? 0) }}<span
                                                    id="expenses_category_description"> </span></div>
                                        </div>
                                        <div class="row pb-2">
                                            <div class="col-4 mb-2">
                                                <p>{{ __('Status') }}</p>
                                            </div>
                                            <div class="col-8">:
                                                @if ($withdraw->status == 'pending')
                                                    <td class="text-warning">{{ __('Pending') }}</td>
                                                @elseif ($withdraw->status == 'rejected')
                                                    <td class="text-danger">{{ __('Rejected') }}</td>
                                                @else
                                                    <td class="text-success">{{ __('Approve') }}</td>
                                                @endif
                                                <span id="expenses_category_description"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="info-container">
                                <div class="">
                                    <div class="table-header-title ">
                                        <h4 class="fw-bold">{{ __('Account Information') }}</h4>
                                    </div>
                                    <div class="personal-info m-3">

                                        @isset($withdraw['account_info']['infos'])
                                            @if (is_array($withdraw['account_info']['infos']) && !empty($withdraw['account_info']['infos']))
                                                @foreach ($withdraw['account_info']['infos'] as $key => $value)
                                                    <div class="row pb-2">
                                                        <div class="col-4 mb-2">
                                                            <p>{{ str_replace('_', ' ', $key) }}</p>
                                                        </div>
                                                        <div class="col-8">: {{ $value }}<span id="expenses_category_name"> </span></div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="row pb-2">
                                                    <div class="col-md-12">
                                                        <p> {{__('No account information available.')}} </p>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="row pb-2">
                                                <div class="col-md-12">
                                                    <p> {{__('No account information available.')}} </p>
                                                </div>
                                            </div>
                                        @endisset

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($withdraw->status == 'pending')
                    <div class="add-landlord-button-group mb-4">
                        <button data-url="{{ route('admin.withdraw.reject', $withdraw->id) }}" class="btn border-btn reject-btn" data-bs-toggle="modal" data-bs-target="#withdraw-reject-modal">{{ __('Rejected') }}</button>
                        <button data-url="{{ route('admin.withdraw.approve', $withdraw->id) }}" class="btn theme-btn approve-btn submit-btn" data-bs-toggle="modal" data-bs-target="#withdraw-approve-modal">{{ __('Approved') }}</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin.withdrawals.reject')
    @include('admin.withdrawals.approve')
@endsection
