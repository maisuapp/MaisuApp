@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($provider))Add @else Edit @endif Delivery Person
@endsection
@section('page-css')
    <style>
        .input-group-append .input-group-text {
            background-color: #2ed8b6;
        }
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
            /*box-shadow: inset 0 0 0 1px #666565, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;*/
            box-shadow: 0 0 0 0 #91DEAC, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
        }
        input[type="radio"] + .label:hover {
            color: #44BB6E;
        }
        input[type="radio"] + .label:hover:before {
            animation-duration: .5s;
            animation-name: change-size;
            animation-iteration-count: infinite;
            animation-direction: alternate;
            box-shadow: inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
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
            box-shadow: inset 0 0 0 1px #44BB6E, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #44BB6E;
        }
        @keyframes change-size {
            from {
                box-shadow: 0 0 0 0 #44BB6E, inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            to {
                box-shadow: 0 0 0 1px #44BB6E, inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
        }
        @keyframes select-radio {
            0% {
                box-shadow: 0 0 0 0 #91DEAC, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            90% {
                box-shadow: 0 0 0 10px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #44BB6E, inset 0 0 0 2px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            100% {
                box-shadow: 0 0 0 12px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #44BB6E, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
        }
        @media screen and (max-width: 576px) {
            input[type="radio"] + .label {
                margin-left: 48%;
                display: block;
            }
        }

        /*button style*/
        .btn-success {
            background-color: #5dc271;
            border-color: #4db560;
        }
        .btn-success:hover, .btn-success:active, .btn-success:focus {
            background-color: #44af5a;
            border-color: #44af5a;
        }

        /*upload image style*/
        #upload-image-preview {
            height: 120px;
            background: no-repeat;
            background-size: contain !important;
            background-position: center !important;
        }
        #upload-image-preview label {
            width: 150px;
            height: 40px;
            font-size: 16px;
            line-height: 40px;
            z-index: 0;
        }

        @if(isset($provider))
        #upload-image-preview {
            background: url({{ asset('/assets/images/profile-images/provider/'.$provider->avatar) }}) no-repeat;
            background-size: contain;
            background-position: center;
        }

        @endif

         /*Modal style */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 50%;
            /*height: 100%; !* Full height *!*/
            height: fit-content;
            margin: auto;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 10px;
            /*padding: 20px;*/
            border: 1px solid #888;
            /*width: 80%;*/
        }
        .model_overlay {
            position: fixed;
            /*display: none;*/
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        /*provider service checkbox style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 20px;
            margin-right: 7px;
        }
        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }
        .provider_check_icon {
            color: red;
            margin-right: 7px;
        }
        .md_close {
            border: 1px solid black;
        }
    </style>
    <link href="{{ asset('/assets/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" media="screen">
@endsection
@section('page-content')
    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Delivery Person</h5>
                            <span>@if(!isset($provider))Add @else Edit @endif Delivery Person </span>
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
                        {{--<form id="main" method="post" action="{{route('post:admin:update_transport_provider',$slug)}}"--}}
                        <form id="main" method="post" action=""
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            @if(isset($provider))
                                <input type="text" name="id" value="{{$provider->id}}">
                            @endif
                            <div class="row">
                                <span id="service_category_append"></span>
                                <div class="form-group col-sm-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($provider))Add @else Edit @endif Delivery Person</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-12">
                                                    <input id="driver_service" name="driver_service" value="" class=""
                                                           type="hidden">
                                                    <input id="delivery_boy" name="delivery_boy" value="" class=""
                                                           type="hidden">
                                                    <input id="courier_boy" name="courier_boy" value="" class=""
                                                           type="hidden">

                                                    @if(isset($service_category))
                                                        <input type="hidden" name="service_cat_id"
                                                               id="service_cat_id"
                                                               value="{{$service_category->id}}">
                                                    @endif

                                                    <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Delivery Person Name:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="name" required
                                                                   id="name" placeholder="Delivery Person Name"
                                                                   value="{{ (isset($provider)) ? $provider->name : old('name') }}">
                                                            <span class="error">{{ $errors->first('name') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Email:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="email" class="form-control" name="email"
                                                                   required
                                                                   id="email" placeholder="Unique Email"
                                                                   value="{{ (isset($provider)) ? $provider->email : old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Contact Number:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control"
                                                                   name="contact_number"
                                                                   required
                                                                   id="contact_number"
                                                                   placeholder="Unique Contact Number"
                                                                   value="{{ (isset($provider)) ? $provider->contact_number : old('contact_number') }}">
                                                            <span class="error">{{ $errors->first('contact_number') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label image image-label">Profile
                                                            Image:</label>
                                                        <div class="col-sm-8">
                                                            <div id="upload-image-preview">
                                                                @if(isset($provider))
                                                                    <label for="image-upload" id="image-label">Change
                                                                        Image</label>
                                                                    <input type="file" id="image-upload" name="avatar"
                                                                           accept=".jpg,.jpeg,.png"/>
                                                                @else
                                                                    <label for="image-upload" id="image-label">Upload
                                                                        Image</label>
                                                                    <input type="file" id="image-upload" name="avatar"
                                                                           accept=".jpg,.jpeg,.png"/>
                                                                @endif
                                                            </div>

                                                            <span class="note">[Note: Upload only png, jpeg and jpg file.]</span>
                                                            <span class="error">{{ $errors->first('avatar') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Password:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="password" class="form-control" name="pass"
                                                                   id="pass" placeholder="Password" required
                                                                   value="{{ old('pass') }}">
                                                            <span class="error">{{ $errors->first('pass') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Confirm Password:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="password" class="form-control"
                                                                   name="confirm_password" required
                                                                   id="confirm_password" placeholder="Confirm Password"
                                                                   value="{{ old('confirm_password') }}">
                                                            <span class="error">{{ $errors->first('confirm_password') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Delivery Person Service
                                                            Radius:</label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" class="form-control"
                                                                   name="service_radius" required
                                                                   id="service_radius"
                                                                   placeholder="Delivery Person Service Radius"
                                                                   value="{{ (isset($provider)) ? $provider->service_radius : old('service_radius') }}">
                                                            <span class="0error">{{ $errors->first('service_radius') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Gender:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <div class="radio radio-outline radio-inline">
                                                                <input type="radio" id="featured-1" name="gender"
                                                                       {{--checked--}}
                                                                       value="0">
                                                                <label class="label" for="featured-1"> Male</label>
                                                            </div>
                                                            <div class="radio radio-outline radio-inline">
                                                                <input type="radio" id="featured-2" name="gender"
                                                                       value="1">
                                                                <label class="label" for="featured-2"> Female</label>
                                                            </div>
                                                            <span class="error">{{ $errors->first('gender') }}</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($provider))Add @else Edit @endif
                                                Delivery Person Vehicle Type Details</h5>
                                        </div>
                                        <div class="card-block">


                                            <div class="row">
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Type:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="vehicle_type_id" id="vehicle_type_id"
                                                                    class="form-control" required>
                                                                <option disabled selected>Select Vehicle Type</option>
                                                                @if(isset($vehicle_types))
                                                                    @foreach($vehicle_types as $key => $vehicle_type)
                                                                        <option value="{{ $vehicle_type->id }}">{{ $vehicle_type->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('vehicle_type_id') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Company:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control"
                                                                   name="vehicle_company"
                                                                   required
                                                                   id="vehicle_company"
                                                                   placeholder="Vehicle Company Name"
                                                                   value="{{ (isset($provider)) ? $provider->vehicle_company : old('vehicle_company') }}">
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
                                                                   value="{{ (isset($provider)) ? $provider->model_name : old('model_name') }}">
                                                            <span class="error">{{ $errors->first('model_name') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Plat No:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="plat_no"
                                                                   required
                                                                   id="plat_no" placeholder="Plat No"
                                                                   value="{{ (isset($provider)) ? $provider->plat_no : old('plat_no') }}">
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
                                                                       id="model_year" required
                                                                       {{ (isset($provider)) ? $provider->model_year : old('model_year') }}
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

                                                            <span class="error">{{ $errors->first('model_year') }}</span>
                                                        </div>
                                                    </div>


                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Color:</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="vehicle_color"
                                                                   id="vehicle_color" placeholder="Vehicle Color"
                                                                   value="{{ (isset($provider)) ? $provider->vehicle_color : old('vehicle_color') }}">
                                                            <span class="error">{{ $errors->first('vehicle_color') }}</span>
                                                        </div>
                                                    </div>

                                                    @if(isset($service_category) && $service_category->id == 1)
                                                    @elseif(isset($service_category) && $service_category->id == 2)
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Number of Seat:<sup
                                                                        class="error">*</sup></label>
                                                            <div class="col-sm-8">
                                                                <select name="no_of_seat" id="no_of_seat"
                                                                        class="form-control" required>
                                                                    <option disabled selected>Select Number of Seat
                                                                    </option>
                                                                    @if(isset($provider))
                                                                        @for($i=1; $i<=6; $i++)
                                                                            @if($provider->no_of_seat == $i )
                                                                                <option value="{{ $i }}"
                                                                                        selected>{{ $i }}</option>
                                                                            @else
                                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                                            @endif
                                                                        @endfor
                                                                    @else
                                                                        @for($i=1; $i<=6; $i++)
                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                        @endfor
                                                                    @endif
                                                                </select>
                                                                <span class="error">{{ $errors->first('vehicle_type_id') }}</span>
                                                            </div>
                                                        </div>
                                                    @elseif(isset($service_category) && $service_category->id == 4)
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Support
                                                                Weight:</label>
                                                            <div class="col-sm-8">
                                                                <input type="number" class="form-control"
                                                                       name="support_weight"
                                                                       id="support_weight" placeholder="Support Weight"
                                                                       value="{{ (isset($provider)) ? $provider->support_weight : old('support_weight') }}">
                                                                <span class="error">{{ $errors->first('support_weight') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="submit" class="btn btn-success m-b-0">Save</button>
                                                </div>
                                            </div>
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

    @if(isset($display_category) && $display_category != 0)
        <button id="myBtn">Open Modal</button>
        <div id="model_overlay" class="">
            <div id="myModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Delivery Person Services</h4>
                        {{--<a href="{{route('get:admin:transport_service_dashboard',$slug)}}" class="render_link">--}}
                        <a href="{{route('get:admin:delivery_person_list')}}" class="render_link">
                            <button type="button" class="btn btn-outline-danger" style="padding: 0px 8px;" data-dismiss="modal">&times;</button>
                        </a>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Service Category:<sup
                                            class="error">*</sup></label>
                                <div class="col-sm-12">
                                    <div class="border-checkbox-section">
                                        {{--<div class="border-checkbox-group border-checkbox-group-primary">--}}
                                            {{--<input name="provider_services_bike_ride" value="1"--}}
                                                   {{--class="border-checkbox"--}}
                                                   {{--@if($slug == "bike-ride")--}}
                                                   {{--checked="checked" value="1"--}}
                                                   {{--@else--}}
                                                   {{--value="0"--}}
                                                   {{--@endif--}}
                                                   {{--type="checkbox" id="checkbox1">--}}
                                            {{--<label class="border-checkbox-label" for="checkbox1"></label>--}}
                                            {{--<img src="{{ asset('/assets/images/service-category/bike-ride.png') }}"--}}
                                                 {{--class="provider_check_icon"--}}
                                                 {{--width="40px" height="40px">--}}
                                            {{--Bike Ride--}}
                                        {{--</div>--}}
                                        @if($display_category == 1)
                                            <div class="border-checkbox-group border-checkbox-group-primary">
                                                <input name="provider_services_store_delivery" value="1"
                                                       class="border-checkbox"
                                                       type="checkbox" id="checkbox2">

                                                <label class="border-checkbox-label" for="checkbox2"></label>
                                                <img src="{{ asset('/assets/images/service-category/bike-ride.png') }}"
                                                     class="provider_check_icon"
                                                     width="40px" height="40px">
                                                Store Delivery
                                            </div>
                                        @else
                                            <input name="provider_services_store_delivery" value="0"
                                                   class="border-checkbox"
                                                   type="checkbox" id="checkbox2">
                                        @endif
                                        <div class="border-checkbox-group border-checkbox-group-primary">
                                            <input name="provider_services_courier_service" value="3"
                                                   class="border-checkbox"
                                                   @if(isset($slug) &&  $slug == "courier-service")
                                                   checked value="1"
                                                   @else
                                                   value="0"
                                                   @endif
                                                   type="checkbox" id="checkbox3">
                                            <label class="border-checkbox-label" for="checkbox3"></label>
                                            <img src="{{ asset('/assets/images/service-category/courier-service.png') }}"
                                                 class="provider_check_icon"
                                                 width="40px" height="40px">
                                            Courier Services
                                        </div>
                                    </div>
                                    <span class="error">{{ $errors->first('name') }}</span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="close" style="padding: 8px 15px;" class="btn btn-success m-b-0">Done</button>
                    </div>
                </div>

            </div>
        </div>
    @endif
@endsection
@section('page-js')
    @if(isset($display_category) && $display_category != 0)
        <script>
            $('#close').click(function () {
                if ($("#checkbox1").prop('checked') == true) {
                    $('#driver_service').val(1);
                } else {
                    $('#driver_service').val(0);
                }
                if ($("#checkbox2").prop('checked') == true) {
                    $('#delivery_boy').val(1);
                } else {
                    $('#delivery_boy').val(0);
                }

                if ($("#checkbox3").prop('checked') == true) {
                    $('#courier_boy').val(1);
                } else {
                    $('#courier_boy').val(0);
                }
                $('#model_overlay').removeClass('model_overlay');
            });
            var modal = document.getElementById('myModal');
            var btn = document.getElementById("myBtn");
            var span = document.getElementById("close");
            btn.onclick = function () {
                modal.style.display = "block";
                $('#model_overlay').addClass('model_overlay');
            }
            span.onclick = function () {
                modal.style.display = "none";
                $('#model_overlay').removeClass('model_overlay');
            }
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                    $('#model_overlay').removeClass('model_overlay');
                }
            }
            var display_category = "{{ isset($display_category) ? $display_category : 0}}";
            if (display_category != 0) {
                $(window).on('load', function () {
                    var mod = document.getElementById("myModal");
                    mod.style.display = "block"
                    $('#model_overlay').addClass('model_overlay');
                });
                var mod = document.getElementById("myModal");
                mod.style.display = "block"
                $('#model_overlay').addClass('model_overlay');
            }
        </script>
    @endif

    <script type="text/javascript" src="{{ asset('assets/js/upload_image.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.uploadPreview({
                input_field: "#image-upload",   // Default: .image-upload
                preview_box: "#upload-image-preview",  // Default: .image-preview
                label_field: "#image-label",    // Default: .image-label
                label_default: "Choose Icon",   // Default: Choose File
                label_selected: "Change Icon",  // Default: Change File
                no_label: false                 // Default: false
            });
        });
    </script>

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

