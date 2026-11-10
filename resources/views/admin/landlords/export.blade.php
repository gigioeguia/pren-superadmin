@extends('layouts.admin.master')

@section('title')
    {{ __('Export Landlord') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="woodland-card">
                    <div class="woodland-card-header">
                        <h3>@lang('Export Landlord')</h3>
                        <div class="action-btn-wrapper">
                            <a href="#" class="btn border-btn green-border" id="btnExport"  onclick="exportReportToExcel(this)">@lang('Export') <i class="far fa-upload"></i></a>
                        </div>
                    </div>
                    <div class="responsibe-table table-height">
                        <table class="table woodland-table">
                            <thead>
                                <tr>
                                    <th>@lang('Id')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Number of property')</th>
                                    <th>@lang('Number of tenants')</th>
                                    <th>@lang('Number of leads')</th>
                                    <th>@lang('Landlord type')</th>
                                    <th>@lang('Balance')</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($landlords as $landlord)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$landlord->name}}</td>
                                <td>{{$landlord->properties?$landlord->properties->count():0}}</td>
                                <td>{{$landlord->tenants?$landlord->tenants->count():0}}</td>
                                <td>
                                    @if ($landlord->allinfoLead)
                                        {{$landlord->allinfoLead->count()}}
                                    @endif
                                </td>
                                <td>
                                    @if ($landlord->subscriptionActive)
                                        <span class="approve-text">{{__('Subscribed')}}</span>
                                    @else
                                        {{__('Unsubscribed')}}
                                    @endif

                                </td>
                                <td>
                                    @if ($landlord->wallet)
                                       {{$landlord->wallet->balance}}
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    <div class="woodladn-pagination-wrapper">
                        <nav class="woodland-pagination">
                            <ul class="pagination">
                                <li class="page-item">{{$landlords->links()}}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

