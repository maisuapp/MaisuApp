@extends('admin.layout.super_admin')
@section('title')
    @if(isset($transport_provider)) Edit @endif Provider Vehicle Details
@endsection
@section('page-css')
    <link href="{{ asset('/assets/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" media="screen">
    <style>
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 20px;
            margin-right: 7px;
        }

        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ other service horizontal navbar ] start -->
        <div class="other-service-horizontal-nav">
            @include('admin.include.transport-horizontal-navbar')
        </div>
        <!-- [ other service horizontal navbar ] end -->

        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>@if(isset($transport_provider)) Edit @endif Provider Vehicle Details</h5>
                            <span>@if(isset($transport_provider)) Edit @endif Provider Vehicle Details
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
                        <div class="card">
                            <div class="card-header">
                                <h5>@if(isset($transport_provider)) Edit @endif Provider Vehicle Details
                                    @if(isset($service_category) && $service_category->name != Null)
                                        of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post"
                                      action="{{ route('post:admin:update_transport_provider_vehicle_details',[$slug]) }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    @if(isset($transport_provider))
                                        <input type="hidden" name="id" value="{{$transport_provider->id}}">
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-8">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Vehicle Type:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <select name="vehicle_type_id" id="vehicle_type_id"
                                                            class="form-control"
                                                            required>
                                                        @if(isset($transport_provider))
                                                            @if(isset($transport_vehicle_type))
                                                                @foreach($transport_vehicle_type as $key => $transport_vehicle)
                                                                    @if(isset($transport_vehicle) && $transport_vehicle->id == $transport_provider->vehicle_type_id)
                                                                        <option value="{{ $transport_vehicle->id }}"
                                                                                selected>{{ ucwords(strtolower($transport_vehicle->name)) }}</option>
                                                                    @else
                                                                        <option value="{{ $transport_vehicle->id }}">{{ ucwords(strtolower($transport_vehicle->name)) }}</option>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            @if(isset($transport_vehicle_type))
                                                                @foreach($transport_vehicle_type as $key => $transport_vehicle)
                                                                    <option value="{{ $transport_vehicle->id }}">{{ ucwords(strtolower($transport_vehicle->name)) }}</option>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    </select>
                                                    <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Vehicle Company:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="vehicle_company"
                                                           required
                                                           id="vehicle_company" placeholder="Unique Vehicle Type Name"
                                                           value="{{ (isset($transport_provider)) ? $transport_provider->vehicle_company : old('vehicle_company') }}">
                                                    <span class="error">{{ $errors->first('vehicle_company') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Model Name:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="model_name"
                                                           required
                                                           id="model_name" placeholder="Model Name"
                                                           value="{{ (isset($transport_provider)) ? $transport_provider->model_name : old('model_name') }}">
                                                    <span class="error">{{ $errors->first('model_name') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Plat No.:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="plat_no"
                                                           required
                                                           id="plat_no" placeholder="Plat No"
                                                           value="{{ (isset($transport_provider)) ? $transport_provider->plat_no : old('plat_no') }}">
                                                    <span class="error">{{ $errors->first('plat_no') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Model Year:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">

                                                    <div class="input-group date form_datetime">
                                                        <input name="model_year" type="text"
                                                               class="form-control category"
                                                               placeholder="Model Year From Picker"
                                                               id="model_year"
                                                               value="{{ (isset($transport_provider)) ? $transport_provider->model_year : old('model_year') }}"
                                                               readonly required>
                                                        <span class="input-group-append" id="basic-addon3">
                                <label class="bg-c-green input-group-text">
                                <span class="fa fa-remove"></span>
                                </label>
                                </span>
                                                        <span class="input-group-append" id="basic-addon3">
                                <label class="bg-c-green input-group-text">
                                <span class="fa fa-th"></span>
                                </label>
                                </span>
                                                    </div>
                                                    @if ($errors->has('model_year'))
                                                        <span class="error">{{ $errors->first('model_year') }}</span>
                                                    @endif

                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Vehicle Color:</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="vehicle_color"
                                                           id="vehicle_color" placeholder="Vehicle Color"
                                                           value="{{ (isset($transport_provider)) ? $transport_provider->vehicle_color : old('vehicle_color') }}">
                                                    <span class="error">{{ $errors->first('vehicle_color') }}</span>
                                                </div>
                                            </div>

                                            @if(isset($service_category) && $service_category->id == 2)
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Handicap:</label>
                                                    <div class="col-sm-8">
                                                        <div class="border-checkbox-section row">
                                                            <div class="col-sm-4" style="margin: 10px 0">
                                                                <div class="border-checkbox-group border-checkbox-group-primary">
                                                                    <input name="handicap"
                                                                           class="border-checkbox"
                                                                           {{( 1 == $transport_provider->handicap ) ? "checked" : "" }}
                                                                           type="checkbox"
                                                                           id="handicap">
                                                                    <label class="border-checkbox-label"
                                                                           for="handicap"></label>
                                                                    {{--                                                                Handicap--}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span class="error">{{ $errors->first('handicap') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Child Seat Accessibility:</label>
                                                    <div class="col-sm-8">
                                                        <div class="border-checkbox-section row">
                                                            <div class="col-sm-4" style="margin: 10px 0">
                                                                <div class="border-checkbox-group border-checkbox-group-primary">
                                                                    <input name="child_seat"
                                                                           class="border-checkbox"
                                                                           {{( 1 == $transport_provider->child_seat ) ? "checked" : "" }}
                                                                           type="checkbox"
                                                                           id="child_seat">
                                                                    <label class="border-checkbox-label"
                                                                           for="child_seat"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span class="error">{{ $errors->first('child_seat') }}</span>
                                                    </div>
                                                </div>
                                            @endif


                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label"></label>
                                                <div class="col-sm-8">
                                                    <button type="submit" class="btn btn-success m-b-0">Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script type="text/javascript" src="{{asset('assets/js/bootstrap-datetimepicker.js')}}" charset="UTF-8"></script>
    <script type="text/javascript">
        var today = new Date();
        var startDate = new Date(today.getFullYear());
        var endDate = new Date(today.getFullYear(), 6, 31);
        $('.form_datetime').datetimepicker({
            format: "yyyy",
            startView: 'decade',
            minView: 'decade',
            viewSelect: 'decade',
            startDate: startDate,
            endDate: endDate,
            autoclose: true,
        });
    </script>
@endsection

