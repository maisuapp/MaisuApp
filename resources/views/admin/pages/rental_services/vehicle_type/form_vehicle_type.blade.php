@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($transport_vehicle_type))Add @else Edit @endif Vehicle Type
@endsection
@section('page-css')
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ other service horizontal navbar ] start -->
        <div class="other-service-horizontal-nav">
            @include('admin.include.transport-rental-horizontal-navbar')
        </div>
        <!-- [ other service horizontal navbar ] end -->

        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Vehicle Type</h5>
                            <span>@if(!isset($transport_vehicle_type))Add @else Edit @endif Vehicle Type
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
                        <form id="main" method="post" action="{{ route('post:admin:update_transport_rental_vehicle_type') }}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            @if(isset($transport_vehicle_type))
                                <input type="hidden" name="id" value="{{$transport_vehicle_type->id}}">
                            @endif
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($transport_vehicle_type))Add @else Edit @endif Vehicle
                                                Type @if(isset($service_category) && $service_category->name != Null)
                                                    of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>
                                        </div>
                                        <div class="card-block">
                                            @if(isset($service_category))
                                                <input type="hidden" name="service_cat_id"
                                                       id="service_cat_id"
                                                       value="{{$service_category->id}}">
                                            @endif
                                            {{--<div class="form-group row">--}}
                                            {{--<label class="col-sm-4 col-form-label">Vehicle Type For:<sup--}}
                                            {{--class="error">*</sup></label>--}}
                                            {{--<div class="col-sm-8">--}}
                                            {{--<select name="service_cat_id" id="service_cat_id" class="form-control"--}}
                                            {{--required>--}}
                                            {{--@if(isset($transport_vehicle_type))--}}
                                            {{--@if(isset($service_category_lists))--}}
                                            {{--@foreach($service_category_lists as $key => $service_category_list)--}}
                                            {{--@if(isset($transport_vehicle_type) && $service_category_list->id == $transport_vehicle_type->service_cat_id)--}}
                                            {{--<option value="{{ $service_category_list->id }}"--}}
                                            {{--selected>{{ ucwords(strtolower($service_category_list->name)) }}</option>--}}
                                            {{--@else--}}
                                            {{--<option value="{{ $service_category_list->id }}">{{ ucwords(strtolower($service_category_list->name)) }}</option>--}}
                                            {{--@endif--}}
                                            {{--@endforeach--}}
                                            {{--@endif--}}
                                            {{--@else--}}
                                            {{--@if(isset($service_category_lists))--}}
                                            {{--@foreach($service_category_lists as $key => $service_category_list)--}}
                                            {{--<option value="{{ $service_category_list->id }}">{{ ucwords(strtolower($service_category_list->name)) }}</option>--}}
                                            {{--@endforeach--}}
                                            {{--@endif--}}
                                            {{--@endif--}}
                                            {{--</select>--}}
                                            <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                            {{--</div>--}}
                                            {{--</div>--}}
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Vehicle Type Name:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="name" required
                                                           id="name" placeholder="Unique Vehicle Type Name"
                                                           value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->name : old('name') }}">
                                                    <span class="error">{{ $errors->first('name') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Vehicle Type Image:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    @if(isset($transport_vehicle_type))
                                                        <div class="col-sm-4">
                                                            <img src="{{ asset('/assets/images/service-category/transport-service-type/'.$transport_vehicle_type->icon_name)}}"
                                                                 style="width: 50px; height: 50px">
                                                        </div>
                                                    @endif
                                                    <input type="file" class="form-control" name="icon"
                                                           id="icon"
                                                           @if(!isset($transport_vehicle_type)) required @endif>
                                                    <span class="note">[Note: Upload only png icon dimension between 50*50 to 100*100 & max size 100kb.]</span>
                                                    <span class="error">{{ $errors->first('icon') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Cost For Km:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" name="cost_for_km"
                                                           required
                                                           id="cost_for_km" placeholder="Cost For Km"
                                                           step="0.01"
                                                           value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->cost_for_km : old('cost_for_km') }}">
                                                    <span class="error">{{ $errors->first('cost_for_km') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Time Fare(per min):<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" name="time_fare"
                                                           required
                                                           id="time_fare" placeholder="Time Fare Per Min"
                                                           step="0.01"
                                                           value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->time_fare : old('time_fare') }}">
                                                    <span class="error">{{ $errors->first('time_fare') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Base Fare:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" name="base_fare"
                                                           required
                                                           id="base_fare" placeholder="Base Fare"
                                                           step="0.01"
                                                           value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->base_fare : old('base_fare') }}">
                                                    <span class="error">{{ $errors->first('base_fare') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Min Fare Amount:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" name="min_fare_amount"
                                                           required
                                                           id="min_fare_amount" placeholder="Min Fare Amount"
                                                           step="0.01"
                                                           value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->min_fare_amount : old('min_fare_amount') }}">
                                                    <span class="error">{{ $errors->first('min_fare_amount') }}</span>
                                                </div>
                                            </div>
                                            @if(isset($service_category) && $service_category->category_type == 5)

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Weight Limit (in kg):</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" name="weight_limit"
                                                               id="weight_limit" placeholder="Weight Limit"
                                                               required min="0.1" step="0.1"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->weight_limit : old('weight_limit') }}">
                                                        <span class="error">{{ $errors->first('weight_limit') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    {{--dimension_limit--}}
                                                    <label class="col-sm-4 col-form-label">Width Limit (in cm):</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" name="width_limit"
                                                               id="width_limit" placeholder="000"
                                                               required min="0.1" step="0.1"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->width_limit : old('width_limit') }}">
                                                        <span class="error">{{ $errors->first('width_limit') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Height Limit (in cm):</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" name="height_limit"
                                                               id="height_limit" placeholder="000"
                                                               required min="0.1" step="0.1"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->height_limit : old('height_limit') }}">
                                                        <span class="error">{{ $errors->first('height_limit') }}</span>
                                                    </div>
                                                </div>

                                            @else

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Rental Cost For KM :</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" required
                                                               name="rental_cost_for_km" step="0.01"
                                                               id="dimension_limit" placeholder="Rental Cost For KM"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->rental_cost_for_km : old('rental_cost_for_km') }}">
                                                        <span class="error">{{ $errors->first('rental_cost_for_km') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Rental Amount For 1 Hour
                                                        :</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" required
                                                               name="rental_amount_for_1hour"
                                                               id="dimension_limit" step="0.01"
                                                               placeholder="Rental Amount For 1 Hour"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->rental_amount_for_1hour : old('rental_amount_for_1hour') }}">
                                                        <span class="error">{{ $errors->first('rental_amount_for_1hour') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Rental Km Limit For 1 Hour
                                                        :</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" class="form-control" required
                                                               name="rental_km_limit_for_1hour"
                                                               id="dimension_limit" step="0.01"
                                                               placeholder="Rental Km Limit For 1 Hour"
                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->rental_km_limit_for_1hour : old('rental_km_limit_for_1hour') }}">
                                                        <span class="error">{{ $errors->first('rental_km_limit_for_1hour') }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Status:</label>
                                                <div class="col-sm-8">
                                                    <select name="status" id="status" class="form-control"
                                                            required>
                                                        @if(isset($transport_vehicle_type) && $transport_vehicle_type->status==0)
                                                            <option value="1">Activate</option>
                                                            <option value="0" selected>Deactivate</option>
                                                        @else
                                                            <option value="1" selected>Activate</option>
                                                            <option value="0">Deactivate</option>
                                                        @endif
                                                    </select>
                                                    <span class="error">{{ $errors->first('status') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{--<label class="col-sm-4 col-form-label"></label>--}}
                                        <div class="col-sm-12">
                                            <center>
                                                <button type="submit" class="btn btn-success m-b-0">Save</button>
                                            </center>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </form>


                        {{--</div>--}}
                        {{--</div>--}}
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
@endsection

