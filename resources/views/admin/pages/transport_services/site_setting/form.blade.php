@extends('admin.layout.super_admin')
@section('title')
    Service Setting
@endsection
@section('page-css')
    <style>

    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="other-service-horizontal-nav">
            @include('admin.include.transport-horizontal-navbar')
        </div>
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Service Setting</h5>
                            <span>@if(!isset($service_settings))Add @else Edit @endif Service Setting
                                @if(isset($service_category) && $service_category->name != Null)
                                    of {{ ucwords(strtolower($service_category->name)) }} @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <form id="main" method="post"
                              action="{{ route('post:admin:update_transport_service_setting') }}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            @if(isset($service_settings))
                                <input type="hidden" name="id" value="{{$service_settings->id}}"
                                       placeholder="Service Setting Id">
                            @endif
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($service_settings))Add @else Edit @endif Service
                                                Setting @if(isset($service_category) && $service_category->name != Null)
                                                    of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>
                                        </div>
                                        <div class="card-block">
                                            @if(isset($service_category))
                                                <input type="hidden" name="service_cat_id"
                                                       id="service_cat_id"
                                                       value="{{$service_category->id}}">
                                                <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                            @endif
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Driver Accept
                                                    Timeout (in
                                                    Second):</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control"
                                                           name="provider_accept_timeout"
                                                           required
                                                           id="provider_accept_timeout"
                                                           placeholder="Driver Accept Timeout"
                                                           value="{{ (isset($service_settings)) ? $service_settings->provider_accept_timeout : old('provider_accept_timeout') }}">
                                                    <span class="error">{{ $errors->first('provider_accept_timeout') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Driver Search
                                                    Radius (in
                                                    KM):</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control"
                                                           name="provider_search_radius"
                                                           required
                                                           id="provider_search_radius"
                                                           placeholder="Driver Search Radius"
                                                           value="{{ (isset($service_settings)) ? $service_settings->provider_search_radius : old('provider_search_radius') }}">
                                                    <span class="error">{{ $errors->first('provider_search_radius') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Tax (in
                                                    %):</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" name="tax"
                                                           required
                                                           id="tax"
                                                           placeholder="Tax"
                                                           value="{{ (isset($service_settings)) ? $service_settings->tax : old('tax') }}">
                                                    <span class="error">{{ $errors->first('tax') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Admin Commision
                                                    (in
                                                    %):</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control"
                                                           name="admin_commission"
                                                           required
                                                           id="admin_commission"
                                                           placeholder="Admin Commission"
                                                           value="{{ (isset($service_settings)) ? $service_settings->admin_commission : old('admin_commission') }}">
                                                    <span class="error">{{ $errors->first('admin_commission') }}</span>
                                                </div>
                                            </div>
                                                @if($slug == "courier-service")
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Cancel Charge (in
                                                            %):</label>
                                                        <div class="col-sm-8">
                                                            <input type="number" class="form-control"
                                                                   name="cancel_charge"
                                                                   required
                                                                   id="cancel_charge"
                                                                   placeholder="Cancel Charge"
                                                                   value="{{ (isset($service_settings)) ? $service_settings->cancel_charge : old('cancel_charge') }}">
                                                            <span class="error">{{ $errors->first('cancel_charge') }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <center>
                                                <button type="submit" class="btn btn-success m-b-0">Save</button>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
@endsection

