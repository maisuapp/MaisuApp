@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($transport_vehicle_type))Add @else Edit @endif Vehicle Type
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/mdtimepicker.min.css')}}" type="text/css">
    <style>
        .answer {
            display:none
        }
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 30px;
            margin-right: 7px;
        }

        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }

        .border-checkbox-section .border-checkbox-group .checklbl {
            height: 0;
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
                        <form id="main" method="post" action="{{ route('post:admin:update_transport_vehicle_type') }}"
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
                                                        <label class="col-sm-4 col-form-label">Select Weight Limit:</label>
                                                        <div class="col-sm-8">
                                                            <select name="weight_limit" id="weight_limit" class="form-control"
                                                                    required>
                                                                    <option value="" selected>Please Select Weight Limit For Vehicle</option>
                                                                    @if(isset($transport_weight_limit) &&  !empty($transport_weight_limit))
                                                                        @foreach($transport_weight_limit as $single_weight_limit)
                                                                            <option value="{{$single_weight_limit->id}}"  {{ isset($transport_vehicle_type)?(($transport_vehicle_type->weight_limit == $single_weight_limit->id)?"selected":"" ) :"" }}>
                                                                                {{ isset($single_weight_limit)?( $single_weight_limit->start_limit." Kg - ".( ($single_weight_limit->close_limit>0)?$single_weight_limit->close_limit:"above ".$single_weight_limit->start_limit )." Kg" ):old('weight_limit')}}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('weight_limit') }}</span>
                                                        </div>
                                                    </div>
{{--                                                <div class="form-group row">--}}
{{--                                                    <label class="col-sm-4 col-form-label">Weight Limit (in kg):</label>--}}
{{--                                                    <div class="col-sm-8">--}}
{{--                                                        <input type="number" class="form-control" name="weight_limit"--}}
{{--                                                               id="weight_limit" placeholder="Weight Limit"--}}
{{--                                                               required min="0.1" step="0.1"--}}
{{--                                                               value="{{ (isset($transport_vehicle_type)) ? $transport_vehicle_type->weight_limit : old('weight_limit') }}">--}}
{{--                                                        <span class="error">{{ $errors->first('weight_limit') }}</span>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="form-group row">

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

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Surcharge Time Status:</label>
                                                    <div class="col-sm-8">
                                                        @if(isset($service_category))
                                                            <input type="hidden" name="service_cat_id" id="service_cat_id" value="{{$service_category->id}}">
                                                        @endif
                                                        <input name="surcharge_timings_status" class="" type="checkbox" id="surcharge_timings_status"
                                                               @if(isset($transport_vehicle_type) && ($transport_vehicle_type->surcharge_timings_status == 1))
                                                               value="1"
                                                               checked
                                                            @endif>
                                                        <span class="button-indecator"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group col-sm-12 answer" @if(isset($transport_vehicle_type) && ($transport_vehicle_type->surcharge_timings_status == 1)) style="display: block;" @endif>
                                                    <div class="row">
                                                        <div class="form-group col-sm-12">
                                                            <div class="form-group row">
                                                                <label class="col-sm-4 p-0 col-form-label">Surcharge Time:<sup class="error">*</sup></label>
                                                                <div class="col-sm-6 p-0">
                                                                    <div class="col-sm-12">
                                                                        <div>
                                                                            <div class="form-group row" id="every_d">
                                                                                <div class="col-sm-3">
                                                                                    <div class="border-checkbox-section row">
                                                                                        <div class="border-checkbox-group border-checkbox-group-primary">
                                                                                            <input name="day[all]" value="1" class="border-checkbox"
                                                                                                   @if(!isset($surcharge_open_timings) || isset($surcharge_open_timings) && count($surcharge_open_timings)==7 || count($surcharge_open_timings)==0) checked @endif
                                                                                                   type="checkbox" id="checkbox_every">
                                                                                            <label class="border-checkbox-label" for="checkbox_every"></label>
                                                                                            <span>EveryDay</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="every_d_opn_time input-top-bottom-margin col-sm-3"
                                                                                         @if(isset($surcharge_open_timings) && count($surcharge_open_timings)>=1) style="display:none" @endif>
                                                                                    <input type="text" class="form-control timepicker required time_all" id="" name="open_time[0]"
                                                                                           value="{{ (isset($surcharge_open_timings)) ? ((array_key_exists("all",$surcharge_open_timings)) ? $surcharge_open_timings['all'] : '' ) : old('open_time.0') }}">
                                                                                    <span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
                                                                                </div>
                                                                                <div class="col-sm-3 every_d_cls_time"
                                                                                     @if(isset($surcharge_open_timings) && count($surcharge_open_timings)>=1) style="display:none" @endif>
                                                                                    <input type="text" class="form-control timepicker required time_all" name="close_time[0]"
                                                                                           value="{{ (isset($surcharge_close_timings)) ? ((array_key_exists("all",$surcharge_close_timings)) ? $surcharge_close_timings['all'] : '' ) : old('close_time.0') }}">
                                                                                    <span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
                                                                                </div>
                                                                                <div class="col-sm-3 every_d_cls_time"
                                                                                     @if(isset($surcharge_open_timings) && count($surcharge_open_timings)>=1) style="display:none" @endif>
                                                                                    <input type="number" class="form-control required" name="price[0]" min="0" step="0.01" placeholder="Delivery Charge * amount"
                                                                                           value="{{ (isset($price)) ? ((array_key_exists("all",$price)) ? $price['all'] : '' ) : old('price.0') }}">
                                                                                    <span class="input-group-addon"></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div id="day_wise"
                                                                             @if(!isset($surcharge_open_timings) || isset($surcharge_open_timings) && count($surcharge_open_timings)==0) style="display: none;" @endif>
                                                                            <?php $days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"]; ?>
                                                                            @for($i=0;$i<count($days);$i++)
                                                                                <div class="form-group row">
                                                                                    <div class="col-sm-3">
                                                                                        <div class="border-checkbox-section row">
                                                                                            <div class="border-checkbox-group border-checkbox-group-primary">
                                                                                                <input name="day[{{$days[$i]}}]" value="1" class="border-checkbox checkbox_all"
                                                                                                       @if(isset($surcharge_open_timings) && array_key_exists($days[$i],$surcharge_open_timings) || !isset($surcharge_open_timings)) checked @endif
                                                                                                       type="checkbox" id="checkbox_{{$days[$i]}}">
                                                                                                <label class="border-checkbox-label" for="checkbox_{{$days[$i]}}"></label>
                                                                                                {{ $days[$i] }}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="input-top-bottom-margin col-sm-3">
                                                                                        @if(isset($surcharge_open_timings) &&  array_key_exists($days[$i],$surcharge_open_timings) )
                                                                                            @if($surcharge_open_timings[$days[$i]] > Date('12:00:00') && $surcharge_open_timings[$days[$i]]  < Date('13:00:00'))
                                                                                                <input type="text" class="form-control timepicker new_picker check_open_type_value_{{$i+1}}" check_open_type_value="{{$i+1}}" check_type="1" name="open_time[{{$i+1}}]"
                                                                                                       value="{{ (isset($surcharge_open_timings)) ? ((array_key_exists($days[$i],$surcharge_open_timings)) ? Date('H:i', strtotime($surcharge_open_timings[$days[$i]]))  .' PM': '' ) : old("open_time.$i+1") }}">
                                                                                            @else
                                                                                                <input type="text" class="form-control timepicker check_open_type_value_{{$i+1}}" check_open_type_value="{{$i+1}}" check_type="1" name="open_time[{{$i+1}}]"
                                                                                                       value="{{ (isset($surcharge_open_timings)) ? ((array_key_exists($days[$i],$surcharge_open_timings)) ? $surcharge_open_timings[$days[$i]] : '' ) : old("open_time.$i+1") }}">
                                                                                            @endif
                                                                                        @else
                                                                                            <input type="text" class="form-control timepicker check_open_type_value_{{$i+1}}" check_open_type_value="{{$i+1}}" check_type="1" name="open_time[{{$i+1}}]"
                                                                                                   value="{{ (isset($surcharge_open_timings)) ? ((array_key_exists($days[$i],$surcharge_open_timings)) ? $surcharge_open_timings[$days[$i]] : '' ) : old("open_time.$i+1") }}">
                                                                                        @endif
                                                                                        <span class="error check_open_type_error_{{$i+1}}"></span>
                                                                                    </div>
                                                                                    <div class="col-sm-3">
                                                                                        @if(isset($surcharge_close_timings) &&  array_key_exists($days[$i],$surcharge_close_timings) )
                                                                                            @if($surcharge_close_timings[$days[$i]] > Date('12:00:00') && $surcharge_close_timings[$days[$i]]  < Date('13:00:00'))
                                                                                                <span class="timepicker">
                                                                                    <input type="text" class="form-control new_picker check_close_type_value_{{$i+1}}" check_close_type_value="{{$i+1}}" check_type="2" name="show_new_close_time[{{$i+1}}]"
                                                                                           value="{{ (isset($surcharge_close_timings)) ? ((array_key_exists($days[$i],$surcharge_close_timings)) ? Date('H:i', strtotime($surcharge_close_timings[$days[$i]]))  .' PM': '' ) : old("close_time.$i+1") }}">
                                                                                    <input type="hidden" class="form-control timepicker_check check_close_type_value_{{$i+1}}" check_close_type_value="{{$i+1}}" check_type="2" name="close_time[{{$i+1}}]"
                                                                                           value="{{ (isset($surcharge_close_timings)) ? ((array_key_exists($days[$i],$surcharge_close_timings)) ? $surcharge_close_timings[$days[$i]] : '' ) : old("close_time.$i+1") }}">
                                                                                </span>
                                                                                            @else
                                                                                                <input type="text" class="form-control timepicker check_close_type_value_{{$i+1}}" check_close_type_value="{{$i+1}}" check_type="2" name="close_time[{{$i+1}}]"
                                                                                                       value="{{ (isset($surcharge_close_timings)) ? ((array_key_exists($days[$i],$surcharge_close_timings)) ? $surcharge_close_timings[$days[$i]] : '' ) : old("close_time.$i+1") }}">
                                                                                            @endif
                                                                                        @else
                                                                                            <input type="text" class="form-control timepicker check_close_type_value_{{$i+1}}" check_close_type_value="{{$i+1}}" check_type="2" name="close_time[{{$i+1}}]"
                                                                                                   value="{{ (isset($surcharge_close_timings)) ? ((array_key_exists($days[$i],$surcharge_close_timings)) ? $surcharge_close_timings[$days[$i]] : '' ) : old("close_time.$i+1") }}">
                                                                                        @endif
                                                                                        <span class="error check_close_type_error_{{$i+1}}"></span>
                                                                                    </div>
                                                                                    <div class="col-sm-3">
                                                                                        @if(isset($price) &&  array_key_exists($days[$i],$price) )
                                                                                            <input type="number" class="form-control " name="price[{{$i+1}}]" min="0" step="0.01" placeholder="Delivery Charge * amount"
                                                                                                   value="{{ (isset($price)) ? ((array_key_exists($days[$i],$price)) ? $price[$days[$i]] : '' ) : old("price.$i+1") }}">
                                                                                        @else
                                                                                            <input type="number" class="form-control " name="price[{{$i+1}}]" min="0" step="0.01" placeholder="Delivery Charge * amount"
                                                                                                   value="{{ (isset($price)) ? ((array_key_exists($days[$i],$price)) ? $price[$days[$i]] : '' ) : old("price.$i+1") }}">
                                                                                        @endif
                                                                                        <span class="error price_{{$i+1}}"></span>
                                                                                    </div>
                                                                                </div>
                                                                            @endfor
                                                                        </div>

                                                                        <span class="error">{{ $errors->first('open_timing') }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
    <script>
        $(function() {
            $("#surcharge_timings_status").on("click",function() {
                $(".answer").toggle(this.checked);
            });
        });
    </script>
    <script src="{{ asset('assets/js/plugin/mdtimepicker.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.timepicker').mdtimepicker().on('timechanged', function (e) {
                // console.log( $(this).children().first().val(e.value));
                // console.log( $(this).children().last().val(e.time));
                var time = e.time;
                var check_type = $(this).attr('check_type');
                if (check_type == 1) {
                    var check_type_value = $(this).attr('check_open_type_value');
                    var closeClassName = ".check_close_type_value_" + check_type_value;
                    var openClassName = ".check_open_type_value_" + check_type_value;
                    var errorClassName = ".check_open_type_error_" + check_type_value;

                    var check_close_type_value = $(closeClassName).attr('data-time');
                    if (check_close_type_value != "" && check_close_type_value != undefined) {
                        if (time >= check_close_type_value) {
                            $(errorClassName).show();
                            $(errorClassName).text("Invalid time range selection.");
                            $(openClassName).val('');
                            $(openClassName).attr('value', '');
                            $(openClassName).attr('data-time', '');
                            $(openClassName).attr('required', 'required');
                        } else {
                            $(errorClassName).hide();
                            console.log($(this).children().first().val(''));
                            console.log($(this).children().last().val(''));
                        }
                    } else {
                        $(errorClassName).hide();
                        console.log($(this).children().first().val(''));
                        console.log($(this).children().last().val(''));
                    }
                } else {
                    var check_type_value = $(this).attr('check_close_type_value');
                    var openClassName = ".check_open_type_value_" + check_type_value;
                    var closeClassName = ".check_close_type_value_" + check_type_value;
                    var errorClassName = ".check_close_type_error_" + check_type_value;

                    var check_open_type_value = $(openClassName).attr('data-time');
                    if (check_open_type_value != "" && check_open_type_value != undefined) {
                        if (time <= check_open_type_value) {
                            $(errorClassName).show();
                            $(errorClassName).text("Invalid time range selection.");
                            $(closeClassName).val('');
                            $(closeClassName).attr('value', '');
                            $(closeClassName).attr('data-time', '');
                            $(closeClassName).attr('required', 'required');
                        } else {
                            $(errorClassName).hide();
                            console.log($(this).children().first().val(''));
                            console.log($(this).children().last().val(''));
                        }
                    } else {
                        $(errorClassName).show();
                        $(errorClassName).text("First select open time.");
                        $(closeClassName).val('');
                        $(closeClassName).attr('value', '');
                        $(closeClassName).attr('data-time', '');
                        $(closeClassName).attr('required', 'required');
                    }
                }
            });
        });

        $(function () {
            $("#checkbox_every").click(function () {
                if ($(this).is(":checked")) {
                    $("#day_wise").hide();
                    $(".every_d_cls_time").show();
                    $(".every_d_opn_time").show();

                } else {
                    $("#day_wise").show();
                    $(".every_d_cls_time").hide();
                    $(".every_d_opn_time").hide();
                }
            });

            $("#checkbox_every").click(function () {
                if ($(this).is(":checked")) {
                    $("#day_wise").hide();
                    $("#day_wise .checkbox_all").removeClass('required');
                    $(".every_d_cls_time").show();
                    $(".every_d_opn_time").show();
                    $(".every_d_cls_time .time_all").addClass('required');
                    $(".every_d_opn_time .time_all").addClass('required');
                } else {
                    $("#day_wise").show();
                    $("#day_wise .checkbox_all").addClass('required');
                    $(".every_d_cls_time .time_all").removeClass('required');
                    $(".every_d_opn_time .time_all").removeClass('required');
                    $(".every_d_cls_time").hide();
                    $(".every_d_opn_time").hide();
                }
            });
        });
    </script>
@endsection

