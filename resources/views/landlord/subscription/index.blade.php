@extends('layouts.landlord.master')

@section('title')
    {{ __('Plan List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="subscribtion-card subscribtion-container">
                    <div class="row w-1100 subscription-content">
                        @foreach ($plans as $plan)
                            @php
                                $activePlan = plan_data();
                                $isFreePlan = $plan->subscriptionPrice == 0;
                                $isPlanActivated = $activePlan != null;
                                $notPurchaseAble = ($activePlan && $activePlan->plan_id == $plan->id && ($activePlan->will_expire > now()->addDays(7)))
                                || ($activePlan && $activePlan->will_expire >= now()->addDays($plan->duration));
                            @endphp
                            <div class="col-xxl-4 col-lg-6 col-md-6 mt-4">
                                <div class="sub-package p-3 position-relative">
                                    @if ((plan_data() ?? false) && plan_data()->plan_id == $plan->id)
                                    <div class="recommended-banner-container ">
                                        <div class="recommended-banner">
                                            <span class="text-white">{{ __('Current Plan') }}</span>
                                          </div>
                                    </div>
                                    @endif
                                    <div class="d-flex align-items-center justify-content-center flex-column gap-3">
                                        <img src="{{ asset($plan->image ?? 'backend/assets/img/icon/basic.svg') }}"
                                            alt="">
                                        <p class="sub-title">{{ $plan->subscriptionName }} </p>
                                        <h6> {{ $plan->duration }}
                                            {{ $plan->duration_type }}</h6>
                                        <h3>{{ currency_format($plan->offerPrice ?? $plan->subscriptionPrice) }} <small>
                                                {{ $plan->subscriptionPrice ? '/' . currency_format($plan->subscriptionPrice) : '' }}</small>
                                        </h3>
                                    </div>
                                    <hr class="pack-hr">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="d-flex align-items-center gap-2 check-property">
                                            <img src="{{ asset('backend/assets/img/icon/check.svg') }}" alt="">
                                            <p>{{ $plan->number_of_property == -1 ? 'Unlimited' : $plan->number_of_property }}
                                                {{ __('Properties') }}</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 check-property">
                                            <img src="{{ asset('backend/assets/img/icon/check.svg') }}" alt="">
                                            <p>{{ $plan->number_of_tenant == -1 ? 'Unlimited' : $plan->number_of_tenant }}
                                                {{ __('Tenants') }}</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 check-property">
                                            <img src="{{ asset('backend/assets/img/icon/check.svg') }}" alt="">
                                            <p>{{ $plan->number_of_labor == -1 ? 'Unlimited' : $plan->number_of_labor }}
                                                {{ __('Labor') }}</p>
                                        </div>
                                        @foreach ($plan->features ?? [] as $feature)
                                            @if ($feature['status'] == 1)
                                                <div class="d-flex align-items-center gap-2 check-property">
                                                    <img src="{{ asset('backend/assets/img/icon/check.svg') }}"
                                                        alt="">
                                                    <p>{{ $feature['name'] ?? '' }}</p>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center gap-2 cross-property">
                                                    <img src="{{ asset('backend/assets/img/icon/sub-cross.svg') }}"
                                                        alt="">
                                                    <p>{{ $feature['name'] ?? '' }}</p>
                                                </div>
                                            @endif
                                        @endforeach

                                        @if ($isFreePlan && $isPlanActivated)
                                            <button class="buy-sub-btn fs-6 disabled">
                                                {{ __('Buy Now')  }}
                                            </button>
                                        @else
                                            <a href="{{ route('payments-gateways.index', ['plan_id' => $plan->id, 'landlord_id' => auth()->user()->id]) }}" class="buy-sub-btn fs-6">
                                                {{ __('Buy Now') }}
                                            </a>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
