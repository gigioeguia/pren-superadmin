@extends('layouts.landlord.master')

@section('title')
    {{__('View Maintenance')}}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-7">
                <div class="woodland-card">
                    <div class="woodland-card-header">
                        <h3>{{ __('Maintenance details') }}</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Tenant Name') }}</td>
                                    <td class="border-0">: {{ $maintenance->tenant?->name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Mobile Number') }}</td>
                                    <td class="border-0">: {{ $maintenance->tenant?->phone['mobile_no'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Property Name') }}</td>
                                    <td class="border-0">: {{ $maintenance->property?->building_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Property Address') }}</td>
                                    <td class="border-0">: {{ $maintenance->property?->address_info['country'] ?? '' }} </td>
                                </tr>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Date & Time') }}</td>
                                    <td class="border-0">:  {{ $maintenance->created_at ?? '' }} </td>
                                </tr>
                                <tr>
                                    <td class="border-0 details-name">{{ __('Status') }}</td>
                                    @if($maintenance->status == 'pending')
                                    <td class="border-0 text-warning">: {{__('Pending')}}  </td>
                                    @elseif($maintenance->status == 'processing')
                                    <td class="border-0 text-primary">: {{__('Processing')}}  </td>
                                    @elseif($maintenance->status == 'completed')
                                    <td class="border-0 text-success">: {{__('Completed')}}  </td>
                                    @else
                                    <td class="border-0 text-danger">: {{__('Rejected')}}  </td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h6>{{ $maintenance->title ?? '' }}</h6>
                        <p>{{ $maintenance->description ?? '' }}</p>
                    </div>

                    @if ($maintenance->status == "rejected" )
                    <div>
                        <h6>{{ __('Rejected Reason') }}</h6>
                        <p>{{ $maintenance->notes }}</p>
                    </div>
                    @endif

                    @if ($maintenance->status == "completed" )
                    <div class="row">
                       <div class="col-6">
                        <h6>{{ __('Labor Name') }}</h6>
                        <p>{{ $maintenance->labor?->name ?? '' }}</p>
                       </div>

                       <div class="col-6">
                        <h6>{{ __('Maintenance Cost') }}</h6>
                        <p>{{ currency_format($maintenance->total_costing ?? 0) }}</p>
                       </div>

                       <div class="col-12">
                        <h6>{{ __('Comment') }}</h6>
                        <p>{{ $maintenance->comments  }}</p>
                       </div>
                    </div>
                    @endif

                    <div>
                        <h6>{{ __('Images') }}</h6>
                        @if (!empty($maintenance->images))
                        <div class="row">
                            @foreach ($maintenance->images as $index => $image)
                            <div class="col-4 mt-2">
                                <img class="mykad-img" src="{{ asset($image) }}" alt="Image {{ $index + 1 }}">
                            </div>
                            @endforeach
                        </div>
                        @else
                            <img class="mykad-img" src="{{ asset('backend/assets/img/no_image.jpg') }}" alt="No image">
                        @endif
                    </div>


                    @if($maintenance->status == "pending")
                    <div class="col-lg-12">
                        <div class="add-landlord-button-group">
                            <button type="submit" data-bs-toggle="modal" data-bs-target="#maintenance-rejected-modal" class="btn btn-danger maintenance-rejected-btn">@lang('Reject')</button>
                            <button type="submit" data-bs-toggle="modal" data-bs-target="#maintenance-processing-modal" class="btn theme-btn submit-btn">@lang('Processing')</button>
                        </div>
                    </div>
                    @elseif($maintenance->status == "processing")
                    <div class="col-lg-12">
                        <div class="add-landlord-button-group">
                            <button type="submit" data-bs-toggle="modal" data-bs-target="#maintenance-rejected-modal" class="btn btn-danger maintenance-rejected-btn">@lang('Reject')</button>
                            <button type="submit" data-bs-toggle="modal" data-bs-target="#maintenance-approved-modal" class="btn theme-btn submit-btn">@lang('Completed')</button>
                        </div>
                    </div>
                    @else
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modal')
@include('landlord.maintenance.processing')
@include('landlord.maintenance.rejected')
@include('landlord.maintenance.approved')
@include('landlord.maintenance.confirm')
@endpush

