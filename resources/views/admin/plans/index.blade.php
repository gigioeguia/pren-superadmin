@extends('layouts.admin.master')

@section('title')
    {{ __('Plan List') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card-header">
                    <h3>{{ __('Subscription Plan') }}</h3>
                    @can('plans-create')
                    <a href="{{ route('admin.plans.create') }}" class="modal-btn"><i class="fal fa-plus-circle"></i>
                        {{ __('Add Plan') }}</a>
                    @endcan
                </div>
                <div class="subscribtion-card subscribtion-container">
                    <div class="row w-1100 m-auto">
                        @foreach ($plans as $plan)
                            <div class="col-xxl-4 sub-pack col-lg-6 col-md-6 mt-4">
                                <div class="sub-package p-3">
                                    <div class="d-flex align-items-center justify-content-center flex-column gap-3">
                                        <img class="pachage-img" src="{{ asset($plan->image ?? 'backend/assets/img/icon/basic.svg') }}"
                                            alt="">
                                        <p class="sub-title">{{ $plan->subscriptionName }} </p>
                                        <h6> {{ $plan->duration }}
                                            {{ $plan->duration_type }}</h6>
                                        <h3>{{ currency_format($plan->offerPrice ?? $plan->subscriptionPrice ?? 0) }} <small>
                                                 {{ $plan->subscriptionPrice ? '/'. currency_format($plan->subscriptionPrice) : '' }}</small></h3>
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

                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 justify-content-center mt-3 ">
                                    @can('plans-update')
                                    <a class="sub-edit" href="{{ route('admin.plans.edit', $plan->id) }}"><svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.67157 19.8284L20.3285 8.17157C20.8737 7.62632 21.1463 7.3537 21.2921 7.0596C21.5694 6.50005 21.5694 5.8431 21.2921 5.28354C21.1463 4.98945 20.8737 4.71682 20.3285 4.17157C19.7832 3.62632 19.5106 3.3537 19.2165 3.20796C18.6569 2.93068 18 2.93068 17.4404 3.20796C17.1463 3.3537 16.8737 3.62632 16.3285 4.17157L4.67157 15.8284C4.09351 16.4064 3.80448 16.6955 3.65224 17.063C3.5 17.4305 3.5 17.8393 3.5 18.6568V20.9999H5.84314C6.66065 20.9999 7.0694 20.9999 7.43694 20.8477C7.80448 20.6955 8.09351 20.4064 8.67157 19.8284Z" stroke="#00A551" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12.5 21H18.5" stroke="#00A551" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15 5.5L19 9.5" stroke="#00A551" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{__('Edit')}} </a>
                                    @endcan
                                    @can('plans-delete')
                                    <a href="{{ route('admin.plans.destroy', $plan->id) }}"
                                        class="btn action-btn confirm-action sub-delete" data-method="DELETE"><svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 5.5L19.3803 15.5251C19.2219 18.0864 19.1428 19.3671 18.5008 20.2879C18.1833 20.7431 17.7747 21.1273 17.3007 21.416C16.3421 22 15.059 22 12.4927 22C9.92312 22 8.6383 22 7.67905 21.4149C7.2048 21.1257 6.796 20.7408 6.47868 20.2848C5.83688 19.3626 5.75945 18.0801 5.60461 15.5152L5 5.5" stroke="#FF3030" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M3.5 5.5H21.5M16.5557 5.5L15.8731 4.09173C15.4196 3.15626 15.1928 2.68852 14.8017 2.39681C14.715 2.3321 14.6231 2.27454 14.527 2.2247C14.0939 2 13.5741 2 12.5345 2C11.4688 2 10.936 2 10.4957 2.23412C10.3981 2.28601 10.305 2.3459 10.2173 2.41317C9.82164 2.7167 9.60063 3.20155 9.15861 4.17126L8.55292 5.5" stroke="#FF3030" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M10 16.5V10.5" stroke="#FF3030" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M15 16.5V10.5" stroke="#FF3030" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            {{__('Delete')}} </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
