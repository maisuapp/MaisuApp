@extends('admin.layout.super_admin')
@section('title')
    Surcharge Setting
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
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Surcharge Setting</h5>
                            <span>Edit Surcharge Setting</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>Surcharge Setting</h5>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post" action="{{route('post:admin:update_store_surcharge_setting')}}" enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    <div class="row">
                                        <div class="form-group col-sm-12">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Surcharge Time Status:</label>
                                                <div class="col-sm-8">
                                                    @if(isset($service_category))
                                                        <input type="hidden" name="service_cat_id" id="service_cat_id" value="{{$service_category->id}}">
                                                    @endif
                                                    <input name="surcharge_timings_status" class="" type="checkbox" id="surcharge_timings_status"
                                                           @if(isset($service_settings) && ($service_settings->surcharge_timings_status == 1))
                                                                value="1"
                                                                checked
                                                           @endif>
                                                    <span class="button-indecator"></span>
                                                </div>
                                            </div>

                                            <div class="form-group col-sm-12 answer" @if(isset($service_settings) && ($service_settings->surcharge_timings_status == 1)) style="display: block;" @endif>
                                                <div class="row">
                                                    <div class="form-group col-sm-12">
                                                        <div class="form-group row">
                                                            <label class="col-sm-2 p-0 col-form-label">Surcharge Time:<sup class="error">*</sup></label>
                                                            <div class="col-sm-10 p-0">
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

                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-10">
                                            <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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

