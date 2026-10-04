@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($store_details))
        Add
    @else
        Edit
    @endif
    Store Detailss
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/mdtimepicker.min.css')}}" type="text/css">
    <style>
        .form-control:disabled, .form-control[readonly] {
            background-color: #ffffff;
        }

        #menu-wrap {
            z-index: 100 !important;
        }

        /*.toggle input[type="checkbox"] + .button-indecator:before {*/
        /*    font-size: 25px;*/
        /*}*/

        .discount {
            display: none;
        }

        .input-group-append .input-group-text {
            background-color: #2ed8b6;
        }

        /* store Type Select Radio Button Style*/
        input[type="radio"] {
            display: none;
        }

        input[type="radio"] + .label {
            position: relative;
            /*margin-left: 43%;*/
            /*display: block;*/
            padding-left: 25px;
            margin-right: 10px;
            cursor: pointer;
            /*line-height: 16px;*/
            color: black;
            font-size: 14px;
            transition: all .2s ease-in-out;
            margin-bottom: 10px;
        }

        input[type="radio"] + .label:before, input[type="radio"] > .label:after {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            width: 20px;
            height: 20px;
            text-align: center;
            color: black;
            cursor: pointer;
            border-radius: 50%;
            transition: all .3s ease;
        }

        input[type="radio"] + .label:before {
            /*box-shadow: inset 0 0 0 1px #666565, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;*/
            box-shadow: 0 0 0 0 #CBDCFF, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
        }

        input[type="radio"] + .label:hover {
            color: #4099ff;
        }

        input[type="radio"] + .label:hover:before {
            animation-duration: .5s;
            animation-name: change-size;
            animation-iteration-count: infinite;
            animation-direction: alternate;
            box-shadow: inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
        }

        input[type="radio"]:checked + .label:hover {
            color: #333333;
            cursor: default;
        }

        input[type="radio"]:checked + .label:before {
            animation-duration: .2s;
            animation-name: select-radio;
            animation-iteration-count: 1;
            animation-direction: Normal;
            box-shadow: inset 0 0 0 1px #4099ff, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #4099ff;
        }

        @keyframes change-size {
            from {
                box-shadow: 0 0 0 0 #4099ff, inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            to {
                box-shadow: 0 0 0 1px #4099ff, inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
        }

        @keyframes select-radio {
            0% {
                box-shadow: 0 0 0 0 #CBDCFF, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            90% {
                box-shadow: 0 0 0 10px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #4099ff, inset 0 0 0 2px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            100% {
                box-shadow: 0 0 0 12px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #4099ff, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
        }

        @media screen and (max-width: 576px) {
            input[type="radio"] + .label {
                margin-left: 48%;
                display: block;
            }

            .input-top-bottom-margin {
                margin: 15px 0;
            }
        }

        /*checkbox style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            /*padding-left: 20px;*/
            padding-left: 30px;
            margin-right: 7px;
        }

        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }

        .border-checkbox-section .border-checkbox-group .checklbl {
            height: 0px;
        }

        /*image upload style*/
        .image {
            padding-top: 0;
        }

        #image-preview-1 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 160px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #image-preview-2 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #image-preview-1 input, #image-preview-2 input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }

        #image-preview-1 label, #image-preview-2 label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer !important;
            background-color: #000000d1;
            color: white;
            width: 200px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-transform: uppercase;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            text-align: center;
        }

        .res-banner-label {
            margin-top: -20px;
            margin-bottom: 15px;
        }

        @if(isset($store_details) && $store_details->store_banner != Null)
            #image-preview-1 {
            background: url({{ asset('/assets/images/store-images/'.$store_details->store_banner) }}) no-repeat;
            background-size: cover;
            /*background-size: 350px 115px;*/
            background-position: center;
        }

        @endif
        @if(isset($store_details) && $store_details->profile_image != Null)
            #image-preview-2 {
            background: url({{ asset('/assets/images/profile-images/provider/'.$store_details->profile_image) }}) no-repeat;
            background-size: cover;
            /*background-size: 100px 100px;*/
            /*background-attachment: fixed;*/
            background-position: center;
        }

        @endif
        @if(isset($store_details) && $store_details->profile_image != Null && filter_var($store_details->profile_image, FILTER_VALIDATE_URL) == true)
            #image-preview-2 {
            background: url({{ $store_details->profile_image}}) no-repeat;
            background-size: cover;
            /*background-size: 100px 100px;*/
            /*background-attachment: fixed;*/
            background-position: center;
        }

        @endif

        .input-controls {
            margin-top: 10px;
            border: 1px solid transparent;
            border-radius: 2px 0 0 2px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            height: 32px;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        #searchInput {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            margin-left: 12px;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 50%;
        }

        #searchInput:focus {
            border-color: #4d90fe;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    {{--    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/demo.css')}}">--}}
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="external-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Store Details</h5>
                            <span>@if(!isset($service_category))Add @else Edit @endif Store Details</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">

                        <form id="main" method="post" action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:update_store',$slug) : route('get:store-admin:update_store')}}" enctype="multipart/form-data">
                            {{csrf_field() }}

                            <span class="error">{{ $errors->first('store_name') }}</span>
                            <span class="error">{{ $errors->first('delivery_radius') }}</span>
                            <span class="error">{{ $errors->first('eta_delivery_time') }}</span>
                            <span class="error">{{ $errors->first('store_banner') }}</span>
                            <span class="error">{{ $errors->first('offer_type') }}</span>
                            <span class="error">{{ $errors->first('offer_amount') }}</span>
                            <span class="error">{{ $errors->first('offer_min_amount') }}</span>
                            <span class="error">{{ $errors->first('order_min_amount') }}</span>
                            <span class="error">{{ $errors->first('packaging_charges') }}</span>
                            <span class="error">{{ $errors->first('description') }}</span>
                            <span class="error">{{ $errors->first('store_address') }}</span>
                            <span class="error">{{ $errors->first('address_lat') }}</span>
                            <span class="error">{{ $errors->first('address_long') }}</span>
                            <span class="error">{{ $errors->first('current_status') }}</span>
                            <span class="error">{{ $errors->first('status') }}</span>
                            <span class="error">{{ $errors->first('contact_person_name') }}</span>
                            <span class="error">{{ $errors->first('gender') }}</span>
                            <span class="error">{{ $errors->first('email') }}</span>
                            <span class="error">{{ $errors->first('password') }}</span>
                            <span class="error">{{ $errors->first('confirm_password') }}</span>
                            <span class="error">{{ $errors->first('contact_number') }}</span>
                            <span class="error">{{ $errors->first('contact_numbers') }}</span>
                            <span class="error">{{ $errors->first('profile_image') }}</span>
                            <span class="error">{{ $errors->first('bank_name') }}</span>
                            <span class="error">{{ $errors->first('account_number') }}</span>
                            <span class="error">{{ $errors->first('payment_email') }}</span>
                            <span class="error">{{ $errors->first('bank_location') }}</span>
                            <span class="error">{{ $errors->first('holder_name') }}</span>
                            <span class="error">{{ $errors->first('bic_swift_code') }}</span>

                            <div class="row">
                                <div class="form-group col-sm-12">

                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($store_details))Add @else Edit @endif Store Details</h5>
                                            {{--<a href="{{ route('get:store:product_list') }}"--}}
                                            {{--class="btn btn-primary m-b-0 btn-right render_link"> Back</a>--}}
                                        </div>
                                        <div class="card-block">
                                            @if(isset($store_details))
                                                <input type="hidden" name="id" value="{{$store_details->store_id}}">
                                                <input type="hidden" name="provider_id" value="{{$store_details->provider_id}}">
                                                <input type="hidden" name="provider_service_id" value="{{$store_details->provider_service_id}}">
                                            @endif
                                            <div class="row">
                                                <div class="form-group col-sm-6">

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Store Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="store_name" required id="store_name" placeholder="Store Name"
                                                                   value="{{ (isset($store_details)) ? $store_details->store_name : old('store_name') }}">
                                                            <span class="error">{{ $errors->first('store_name') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Delivery Radius:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <select type="text" class="form-control" name="delivery_radius" required id="delivery_radius">
                                                                <option disabled selected>Select Service Radius</option>
                                                                <option value="0.5" @if(isset($store_details) && $store_details->delivery_radius == 0.5) selected @endif>0.5 KM</option>
                                                                <option value="1" @if(isset($store_details) && $store_details->delivery_radius == 1) selected @endif>1 KM</option>
                                                                <option value="5" @if(isset($store_details) && $store_details->delivery_radius == 5) selected @endif>5 KM</option>
                                                                <option value="10" @if(isset($store_details) && $store_details->delivery_radius == 10) selected @endif>10 KM</option>
                                                                <option value="15" @if(isset($store_details) && $store_details->delivery_radius == 15) selected @endif>15 KM</option>
                                                                <option value="20" @if(isset($store_details) && $store_details->delivery_radius == 20) selected @endif>20 KM</option>
                                                                <option value="25" @if(isset($store_details) && $store_details->delivery_radius == 25) selected @endif>25 KM</option>
                                                                <option value="30" @if(isset($store_details) && $store_details->delivery_radius == 30) selected @endif>30 KM</option>
                                                            </select>
                                                            <span class="error">{{ $errors->first('delivery_radius') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Estimated Delivery Time(Minutes):<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <select type="text" class="form-control" name="eta_delivery_time" id="eta_delivery_time">
                                                                <option disabled selected>Select Estimated Delivery Time</option>
                                                                <option value="10" @if(isset($store_details) && $store_details->eta_delivery_time == 10) selected @endif>10 Min</option>
                                                                <option value="15" @if(isset($store_details) && $store_details->eta_delivery_time == 15) selected @endif>15 Min</option>
                                                                <option value="20" @if(isset($store_details) && $store_details->eta_delivery_time == 20) selected @endif>20 Min</option>
                                                                <option value="30" @if(isset($store_details) && $store_details->eta_delivery_time == 30) selected @endif>30 Min</option>
                                                                <option value="45" @if(isset($store_details) && $store_details->eta_delivery_time == 45) selected @endif>45 Min</option>
                                                                <option value="60" @if(isset($store_details) && $store_details->eta_delivery_time == 60) selected @endif>60 Min</option>
                                                            </select>
                                                            <span class="error">{{ $errors->first('eta_delivery_time') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label image">Store Banner Image:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <div id="image-preview-1">
                                                                @if(isset($store_details) && $store_details->store_banner != Null)
                                                                    <label for="image-upload" id="image-label">Change Image</label>
                                                                    <input type="file" id="image-upload-1" name="store_banner"/>
                                                                @else
                                                                    <label for="image-upload" id="image-label">Upload Image</label>
                                                                    <input type="file" id="image-upload-1" name="store_banner" required/>
                                                                @endif
                                                            </div>
                                                            <span class="note">[Note: Image Dimension in pixels[Image dimensions between 250*250 to 512*512]</span>
                                                            <span class="error">{{ $errors->first('store_banner') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Order Min Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="order_min_amount" id="order_min_amount"
                                                                   placeholder="Order Min Amount"
                                                                   value="{{ (isset($store_details)) ? $store_details->order_min_amount : old('order_min_amount') }}">
                                                            <span class="error">{{ $errors->first('order_min_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Do you want?</label>
                                                        <div class="col-sm-12">
                                                            {{--<select name="taken_away_type" id="taken_away_type"--}}
                                                            {{--        class="form-control" required>--}}
                                                            {{--    @if(isset($store_details) && $store_details->taken_away_type == 2)--}}
                                                            {{--        <option value="1">Delivery</option>--}}
                                                            {{--        <option value="2" selected>Pickup</option>--}}
                                                            {{--    @else--}}
                                                            {{--        <option value="1" selected>Delivery</option>--}}
                                                            {{--        <option value="2">Pickup</option>--}}
                                                            {{--    @endif--}}
                                                            {{--</select>--}}

                                                            <div class="border-checkbox-section">
                                                                <div class="border-checkbox-group border-checkbox-group-primary">
                                                                    <input name="taken_away_type"
                                                                           class="border-checkbox"
                                                                           @if((isset($store_details)) && $store_details->taken_away_type == 2 )
                                                                           checked
                                                                           @endif
                                                                           type="checkbox" id="taken_away_type">
                                                                    <label class="border-checkbox-label"
                                                                           for="taken_away_type"> </label>
                                                                    <span>Allow for Pickup?</span>
                                                                </div>
                                                                <span class="error">{{ $errors->first('taken_away_type') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Packaging Charges (Admin):</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   {{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? "" : "readonly disabled" }}
                                                                   name="packaging_charges" id="packaging_charges" placeholder="Packaging Charges"
                                                                   value="{{ (isset($store_details)) ? $store_details->packaging_charges : old('packaging_charges') }}">
                                                            <span class="error">{{ $errors->first('packaging_charges') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($general_settings->is_private_driver_module == 1)

                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Driver Notification:</label>
                                                        <div class="col-sm-12">
                                                            <select name="is_private_driver_notification" class="form-control " id="is_private_driver_notification">
                                                                    <option value="0" {{ ( (isset($store_details))  && $store_details->is_private_driver_notification == 0 ?"selected":"" ) }}>Both Driver</option>
                                                                    <option value="1" {{ ( (isset($store_details)) && $store_details->is_private_driver_notification == 1 ?"selected":"" ) }}>Only Personal Driver</option>
                                                                    <option value="2" {{ ( (isset($store_details))  && $store_details->is_private_driver_notification == 2 ?"selected":"" ) }}>Only Public Driver</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Sort Description: </label>
                                                        <div class="col-sm-12">
                                                            <textarea class="form-control" name="description" id="description"
                                                              placeholder="$1,100 for two people (approx.) Exclusive Charges May Apply... Cash and Cards accepted">{{ (isset($store_details)) ? $store_details->description : old('description') }}</textarea>
                                                            <span class="error">{{ $errors->first('description') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-5">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Store Address:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="store_address" required readonly id="address" placeholder="Select Address on Map" value="{{ (isset($store_details)) ? $store_details->store_address : old('store_address') }}">
                                                            <input type="hidden" value="{{ (isset($store_details)) ? $store_details->address_lat : old('address_lat')}}" name="address_lat" id="lat">
                                                            <input type="hidden" value="{{ (isset($store_details)) ? $store_details->address_long : old('address_long')}}" name="address_long" id="lng">
                                                            <span class="error">{{ $errors->first('store_address') }}</span>
                                                            <span class="error">{{ $errors->first('address_lat') }}</span>
                                                            <span class="error">{{ $errors->first('address_long') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Current Status:</label>
                                                        <div class="col-sm-12">
                                                            <select name="current_status" id="current_status" class="form-control" required>
                                                                @if(isset($store_details) && $store_details->current_status == 0)
                                                                    <option value="1">Activate</option>
                                                                    <option value="0" selected>Deactivate</option>
                                                                @else
                                                                    <option value="1" selected>Activate</option>
                                                                    <option value="0">Deactivate</option>
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('current_status') }}</span>
                                                        </div>
                                                    </div>

                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-form-label">Status:</label>
                                                            <div class="col-sm-12">
                                                                <select name="status" id="status" class="form-control" required disabled>
                                                                    @if(isset($store_details) && $store_details->status==0)
                                                                        <option value="0" selected>Pending</option>
                                                                        <option value="1">Approved</option>
                                                                        <option value="2">Rejected</option>
                                                                    @elseif(isset($store_details) && $store_details->status==1)
                                                                        <option value="0">Pending</option>
                                                                        <option value="1" selected>Approved</option>
                                                                        <option value="2">Rejected</option>
                                                                    @else
                                                                        <option value="0">Pending</option>
                                                                        <option value="1" selected>Approved</option>
                                                                        <option value="2">Rejected</option>
                                                                    @endif
                                                                </select>
                                                                <span class="error">{{ $errors->first('status') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{--@if(Illuminate\Support\Facades\Auth::guard("admin")->check())--}}
                                                    {{--    <div class="form-group row">--}}
                                                    {{--        <label class="col-sm-12 col-form-label">Store ID(Code):</label>--}}
                                                    {{--        <div class="col-sm-12">--}}
                                                    {{--            <input type="text" class="form-control" name="store_code"--}}
                                                    {{--                   id="store_code" placeholder="Store Code"--}}
                                                    {{--                   value="{{ (isset($store_details)) ? $store_details->store_code : old('store_code') }}">--}}
                                                    {{--            <span class="error">{{ $errors->first('store_code') }}</span>--}}
                                                    {{--        </div>--}}
                                                    {{--    </div>--}}
                                                    {{--@endif--}}

                                                </div>
                                                <div class="form-group col-sm-7">
                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group">
                                                                <input id="searchInput" name="store_address" class="input-controls"
                                                                       value="{{ (isset($store_details)) ? $store_details->store_address : old('store_address')}}"
                                                                       type="text" placeholder="Enter a location">
                                                                <div class="map" id="map" style="width: 100%; height: 300px;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--Store Contact Person Details Start-->
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Store_timings</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-12" style="margin-bottom: -15px;">
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Store Timing:<sup class="error">*</sup></label>
                                                        <div class="col-sm-10">
                                                            <div class="col-sm-12">
                                                                @include('admin.pages.store_services.store.date_time')
                                                                <span class="error">{{ $errors->first('open_timing') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Offer Details</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Offer Type:</label>
                                                        <div class="col-sm-12">
                                                            <select name="offer_type" id="offer_type" class="form-control">
                                                                <option selected value="">Select Offer Type</option>
                                                                <option value="1" @if(isset($store_details) && $store_details->offer_type == 1) selected @endif> Amount</option>
                                                                <option value="2" @if(isset($store_details) && $store_details->offer_type == 2) selected @endif> Percentage</option>
                                                            </select>
                                                            <span class="error">{{ $errors->first('offer_type') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Offer Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control" name="offer_amount" step="0.01" id="offer_amount" placeholder="Offer Amount"
                                                                   value="{{ (isset($store_details) && $store_details->offer_amount != 0) ? $store_details->offer_amount : old('offer_amount') }}">
                                                            <span class="error">{{ $errors->first('offer_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Offer Min Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control" name="offer_min_amount" id="offer_min_amount" placeholder="Offer Min Amount" step="0.01"
                                                                   value="{{ (isset($store_details) && $store_details->offer_amount != 0) ? $store_details->offer_min_amount : old('offer_min_amount') }}">
                                                            <span class="error">{{ $errors->first('offer_min_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($store_details))Add @else Edit @endif Contact Person Details</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group @if(isset($store_details)) col-sm-6 @else col-sm-7 @endif">
                                                    <div class="form-group row">
                                                        <label class="@if(isset($store_details)) col-sm-12 @else col-sm-4 @endif col-form-label">Contact Person Full Name:<sup class="error">*</sup></label>
                                                        <div class="@if(isset($store_details)) col-sm-12 @else col-sm-8 @endif">
                                                            <input type="text" class="form-control" name="first_name" required id="first_name" placeholder="Contact Person Full Name"
                                                                   value="{{ (isset($store_details)) ? $store_details->first_name : old('first_name') }}">
                                                            <span class="error">{{ $errors->first('first_name') }}</span>
                                                        </div>
                                                    </div>
<!--                                                    <div class="form-group row">
                                                        <label class="@if(isset($store_details)) col-sm-12 @else col-sm-4 @endif col-form-label">Contact Person Last Name:<sup class="error">*</sup></label>
                                                        <div class="@if(isset($store_details)) col-sm-12 @else col-sm-8 @endif">
                                                            <input type="text" class="form-control" name="last_name" required id="last_name" placeholder="Contact Person Last Name"
                                                                   value="{{ (isset($store_details)) ? $store_details->last_name : old('last_name') }}">
                                                            <span class="error">{{ $errors->first('last_name') }}</span>
                                                        </div>
                                                    </div>-->
                                                    <div class="form-group row">
                                                        <label class="@if(isset($store_details)) col-sm-12 @else col-sm-4 @endif col-form-label">Email Address:<sup class="error">*</sup></label>
                                                        <div class="@if(isset($store_details)) col-sm-12 @else col-sm-8 @endif">
                                                            <input type="email" class="form-control" name="email"
                                                                   required

                                                                   {{ ((Illuminate\Support\Facades\Auth::guard("store")->check()) ) || ( isset($store_details) && $store_details->login_type != "email") ? "readonly" : "" }}
                                                                   id="email" placeholder="Email Address"
                                                                   value="{{ (isset($store_details))? ((Illuminate\Support\Facades\Auth::guard("admin")->check()) ? App\model\User::Email2Stars($store_details->email) : $store_details->email) : old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>
                                                    </div>

                                                    @if(!isset($store_details))
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Password:<sup class="error">*</sup></label>
                                                            <div class="col-sm-8">
                                                                <input type="password" class="form-control" name="password" @if(!isset($store_details)) required @endif id="password" placeholder="Password" minlength="6" maxlength="16"
                                                                       value="{{ (isset($store)) ? $store->password : old('password') }}">
                                                                <span class="error">{{ $errors->first('password') }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Confirm Password:<sup class="error">*</sup></label>
                                                            <div class="col-sm-8">
                                                                <input type="password" class="form-control" name="confirm_password" @if(!isset($store_details)) required @endif id="confirm_password" placeholder="Confirm Password"
                                                                       value="{{ (isset($store)) ? $store->confirm_password : old('confirm_password') }}">
                                                                <span class="error">{{ $errors->first('confirm_password') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="form-group row">
                                                        <label class="@if(isset($store_details)) col-sm-12 @else col-sm-4 @endif col-form-label">Contact Number:<sup class="error">*</sup></label>
                                                        <div class="@if(isset($store_details)) col-sm-12 @else col-sm-8 @endif">
                                                            <input type="text" class="form-control" name="contact_number" required
                                                                   {{--@if(isset($store_details)) readonly @endif--}}
                                                                   {{ ((Illuminate\Support\Facades\Auth::guard("store")->check()) ) || ( isset($store_details) && $store_details->login_type != "email") ? "readonly" : "" }}
                                                                   id="phone" placeholder="Contact Number" value="{{ (isset($store_details)) ? ((Illuminate\Support\Facades\Auth::guard("admin")->check()) ? $store_details->country_code.App\model\User::ContactNumber2Stars($store_details->contact_number) : $store_details->country_code.$store_details->contact_number) : '' }}">
                                                            <input type="hidden" id="contact_numbers" name="contact_numbers" value="{{ (isset($store_details)) ? $store_details->country_code.$store_details->contact_number : '' }}">
                                                            <input type="hidden" id="country_code" name="country_code" value="{{ ((isset($store_details)&& $store_details->country_code != Null )) ? $store_details->country_code : '+1' }}">
                                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span><br>
                                                            <span class="error">{{ $errors->first('full_number') }}</span><br>
                                                            <span class="error">{{ $errors->first('contact_numbers') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="@if(isset($store_details)) col-sm-3 @else col-sm-4 @endif col-form-label">Gender:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="@if(isset($store_details)) col-sm-6 @else col-sm-8 @endif">
                                                            <div class="form-radio">
                                                                <div class="radio radio-inline">
                                                                    <label>
                                                                        <input type="radio" name="gender"
                                                                               value="1"
                                                                               @if(isset($store_details) && $store_details->gender == 1) checked="checked"
                                                                               @else checked="checked" @endif>
                                                                        <i class="helper"></i>Male
                                                                    </label>
                                                                </div>
                                                                <div class="radio radio-inline">
                                                                    <label>
                                                                        <input type="radio" name="gender" value="2"
                                                                               @if(isset($store_details) && $store_details->gender == 2) checked="checked" @endif >
                                                                        <i class="helper"></i>Female
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <span class="error">{{ $errors->first('gender') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group @if(isset($store_details)) col-sm-6 @else col-sm-5 @endif">

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label image">Profile Image:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <div id="image-preview-2">
                                                                @if(isset($store_details) && $store_details->profile_image != Null)
                                                                    <label for="image-upload-2" id="image-label">Change
                                                                        Image</label>

                                                                    <input type="file" id="image-upload-2"
                                                                           name="profile_image"/>
                                                                @else
                                                                    <label for="image-upload-2" id="image-label">Upload
                                                                        Image</label>
                                                                    <input type="file" id="image-upload-2"
                                                                           name="profile_image"
                                                                           required
                                                                    />
                                                                @endif
                                                            </div>
                                                            <span class="note">[Note: Image dimensions between 250*250 to 512*512]</span>
                                                            <span class="error">{{ $errors->first('profile_image') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Store Contact Person Details End-->

                                <!--Store Bank Details Start-->
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($bank_details))Add @else Edit @endif Bank Details</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Bank Name:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="bank_name"
                                                                   id="bank_name" placeholder="Bank Name"
                                                                   value="{{ (isset($bank_details)) ? $bank_details->bank_name : old('bank_name') }}">
                                                            <span class="error">{{ $errors->first('bank_name') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Account Number:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control"
                                                                   name="account_number"
                                                                   id="account_number" placeholder="Account Number"
                                                                   value="{{ (isset($bank_details)) ? $bank_details->account_number : old('account_number') }}">
                                                            <span class="error">{{ $errors->first('account_number') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Payment Email
                                                            Address:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="payment_email"
                                                                   id="payment_email"
                                                                   placeholder="Email Address for Payment Notification"
                                                                   value="{{ (isset($bank_details)) ? App\model\User::Email2Stars($bank_details->payment_email) : old('payment_email') }}">
                                                            <span class="error">{{ $errors->first('payment_email') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Bank Location(City
                                                            Name):</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="bank_location"
                                                                   id="bank_location"
                                                                   placeholder="Bank Location(City Name)"
                                                                   value="{{ (isset($bank_details)) ? $bank_details->bank_location : old('bank_location') }}">
                                                            <span class="error">{{ $errors->first('bank_location') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Account Holder
                                                            Name:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="holder_name"
                                                                   id="holder_name" placeholder="Account Holder Name"
                                                                   value="{{ (isset($bank_details)) ? $bank_details->holder_name : old('holder_name') }}">
                                                            <span class="error">{{ $errors->first('holder_name') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">BIC Code:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control"
                                                                   name="bic_swift_code"
                                                                   id="bic_swift_code" placeholder="BIC Code"
                                                                   value="{{ (isset($bank_details)) ? $bank_details->bic_swift_code : old('bic_swift_code') }}">
                                                            <span class="error">{{ $errors->first('bic_swift_code') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Store Bank Details End-->

                                <div class="form-group col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <center><button type="submit" class="btn btn-primary m-b-0">Save
                                                </button></center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    @include('admin.pages.store_services.store.script')
    <script type="text/javascript" src="{{ asset('assets/js/country-code/intlTelInput.min.js')}}"></script>
    <script type="text/javascript">
        //$(document).ready(function ($) {
        //Use this inside your document ready jQuery
        //$(window).on('popstate', function () {
        //    location.reload(true);
        //});
        //});

        var input = document.querySelector("#phone");
        var iti  = window.intlTelInput(input, {
            // allowDropdown: false,
            // autoHideDialCode: false,
            // autoPlaceholder: "off",
            // dropdownContainer: document.body,
            // excludeCountries: ["us"],
            formatOnDisplay: false,
            // geoIpLookup: function(callback) {
            //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //     var countryCode = (resp && resp.country) ? resp.country : "";
            //     callback(countryCode);
            //   });
            // },
            hiddenInput: "full_number",
            initialCountry: "us",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['ph'],
            separateDialCode: true,
            // initialCountry: "auto",
            // geoIpLookup: function(success, failure) {
            //     $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //         var countryCode = (resp && resp.country) ? resp.country : "";
            //         success(countryCode);
            //     });
            // },
            utilsScript: "{{ asset('assets/js/country-code/utils.js')}}",
        });

        $(document).ready(function () {
            input.addEventListener("countrychange", function () {
                console.log(iti.getSelectedCountryData()['dialCode']);
                var country_code = iti.getSelectedCountryData()['dialCode'];
                if (country_code > 0) {
                    country_code = "+" + country_code;
                    document.getElementById("phone_error").innerHTML = '';
                } else {
                    country_code = "+1";
                    document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
                }
                $("#country_code").val(country_code);
            });
            $("#phone").on('keyup', function () {
                var contact_number = $(this).val();
                // var n = contact_number.indexOf("0", 0);
                // // var n = contact_number.charAt(contact_number);
                // if (n == 0) {
                //     document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                //     document.getElementById("contact_numbers").value = "";
                // } else {
                //     document.getElementById("contact_numbers").value = contact_number;
                //     document.getElementById("phone_error").innerHTML = '';
                //     console.log(contact_number);
                // }

                //check code for numeric value
                if (isNaN(contact_number)) {
                    document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                    document.getElementById("contact_numbers").value = "";
                } else {
                    document.getElementById("contact_numbers").value = contact_number;
                    document.getElementById("phone_error").innerHTML = '';
                }
            });
        });
    </script>
@endsection

