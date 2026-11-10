@extends('master')

@section('main_content')

    <!-- dashboard inner section start -->
    <div class="woodland-section-container">
        <form action="{{route('landlord.tenant-application.approve',$tenantApplication->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_approve" id="is_approve" value="1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="woodland-card">
                        <div class="woodland-card-header border-bottom pb-3">
                            <h3>@lang('View Details')</h3>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-6">
                                <div class="application-view-items">
                                    <h6 class="small-title">@lang('Tenant Details')</h6>
                                    <ul class="package-list">
                                        <li><span>@lang('Full Name')</span><span>:</span><span>{{ $tenantApplication->tenant->name }}</span></li>
                                        <li><span>@lang('Phone Number')</span><span>:</span><span>{{ $tenantApplication->tenant->phone }} </span></li>
                                        <li><span>@lang('Email Address')</span><span>:</span><span>{{ $tenantApplication->tenant->email }}</span></li>
                                        <li><span>@lang('Gender')</span><span>:</span><span>{{ $tenantApplication->tenant->gender }}</span></li>
                                        <li><span>@lang('Address')</span><span>:</span><span>{{ $tenantApplication->tenant->address }} </span></li>
                                        <li><span>@lang('Postal Code') </span><span>:</span><span>{{ $tenantApplication->tenant->postal_code }}</span></li>
                                        <li><span>@lang('City')</span><span>:</span><span>{{ $tenantApplication->tenant->city }}</span></li>
                                        <li><span>@lang('State')</span><span>:</span><span>{{ $tenantApplication->tenant->state }}</span></li>
                                        <li><span>@lang('Country Name')</span><span>:</span><span>{{ $tenantApplication->tenant->country }}</span></li>
                                        <li><span>@lang('Property Name')</span><span>:</span><span>{{ $tenantApplication->property->property_title }}</span></li>
                                        <li><span>@lang('Property Types')</span><span>:</span><span>{{ $tenantApplication->property->category->name }}</span></li>
                                        <li><span>@lang('Price')</span><span>:</span><span>@lang('RM') {{ $tenantApplication->property->property_rent }}</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6">
                                <div class="application-view-items">
                                    <h6 class="small-title">@lang('Emergency Contact')</h6>
                                    <ul class="package-list">
                                        <li><span>@lang('Relation With You')</span><span>:</span><span>{{$tenantApplication->tenant->contacts->relation_with}}</span></li>
                                        <li><span>@lang('Name')</span><span>:</span><span>{{$tenantApplication->tenant->contacts->name}}</span></li>
                                        <li><span>@lang('Email')</span><span>:</span><span>{{$tenantApplication->tenant->contacts->email}}</span></li>
                                        <li><span>@lang('Phone Number')</span><span>:</span><span>+@lang('60') {{$tenantApplication->tenant->contacts->phone}} </span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6">
                                <div class="application-view-items">
                                    <h6 class="small-title">@lang('Vehicles Information')</h6>
                                    <ul class="package-list">
                                        <li><span>@lang('Vehicles Type')</span><span>:</span><span>{{$tenantApplication->tenant->vehicles->type}}</span></li>
                                        <li><span>@lang('Car Registration No')</span><span>:</span><span>{{$tenantApplication->tenant->vehicles->registration_no}}</span></li>
                                        <li><span>@lang('Vehicles Brand')</span><span>:</span><span>{{$tenantApplication->tenant->vehicles->brand}}</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="woodland-card-header my-4">
                                    <h3>@lang('Download PDF')</h3>
                                </div>
                                <div class="download-pdf-items">
                                    <div class="download-content">
                                        <div class="icons">
                                            <img src="{{asset('backend/assets/img/icon/pdf.svg')}}" alt="">
                                        </div>
                                        <a href="{{$tenantApplication->document_file}}" class="inner-text" download>
                                            <strong>@lang('Rent Agreement')</strong>
                                            <small>https://www.maan.com/pdf/222</small>
                                        </a>
                                    </div>
                                    <a href="{{$tenantApplication->document_file}}" class="download-icons" download><i class="far fa-arrow-to-bottom"></i></a>
                                </div>
                                <p class="download-note"><b>@lang('Note'):</b> @lang('Please read the pdf, then approve the request.')</p>
                            </div>
                            <div class="col-lg-12">
                                <div class="add-landlord-button-group">
                                    <a href="#reject-modal" class="btn  border-btn" data-bs-toggle="modal">@lang('Reject')</a>
                                    <button type="submit" class="btn theme-btn">@lang('Approve')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="reject-modal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content woodland-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Why are you reject Application?')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('landlord.tenant-application.approve',$tenantApplication->id)}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_approve" id="is_approve" value="0">
                        <label for="reason">@lang('Enter Reason')</label>
                        <textarea class="form-control" cols="30" rows="10" name="rejected_cause"></textarea>
                        <div class="add-landlord-button-group">
                            <button class="btn border-btn">@lang('Cancel')</button>
                            <button type="submit" class="btn theme-btn">@lang('Confirm')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
