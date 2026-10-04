@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($driver_detials))Add @else Edit @endif Driver
@endsection
@section('page-css')
    <link href="{{ asset('/assets/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" media="screen">
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
        .hide{
            display: none;
        }
        .show{
            display: flex;
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

        @if(isset($driver_detials))
        #upload-image-preview {
            background: url({{ asset('/assets/images/profile-images/provider/'.$driver_detials->avatar) }}) no-repeat;
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

        /*The Close Button*/
        /*#close {*/
        /*color: #aaaaaa;*/
        /*float: right;*/
        /*font-size: 14px;*/
        /*font-weight: bold;*/
        /*}*/

        /*#close:hover,*/
        /*#close:focus {*/
        /*color: #4c4c4c;*/
        /*text-decoration: none;*/
        /*cursor: pointer;*/
        /*}*/
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
            z-index: 999;
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }
        .form_datetime{
            margin-bottom: 0;
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Driver</h5>
                            <span>@if(!isset($driver_detials))Add @else Edit @endif Driver</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <form id="main" method="post" action="{{route('post:admin:update_transport_service_driver')}}" enctype="multipart/form-data">
                            {{csrf_field() }}
                            @if(isset($driver_detials))
                                <input type="hidden" name="id" value="{{$driver_detials->provider_id}}">
                                <input type="hidden" name="provider_service_id" value="{{$driver_detials->provider_service_id}}">
                                <input type="hidden" name="driver_details_id" value="{{$driver_detials->driver_details_id}}">
                                <input type="hidden" name="service_cat_id" value="{{$driver_detials->service_cat_id}}">
                            @endif
                            <div class="row">
                                <span id="service_category_append"></span>

                                @if(isset($driver_detials->store_id) && $driver_detials->store_id > 0)
                                <!-- nothing to display in private driver lists -->
                                @else
                                    <div class="form-group col-sm-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Driver Services</h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="row">
                                                    @if(!isset($driver_detials))
                                                        <div class="form-group col-sm-12">
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Service Category :<sup class="error">*</sup></label>
                                                                <div class="col-sm-10">
                                                                    <select class="form-control" name="service_cat_id" id="service_cat_id" required>
                                                                        @if(isset($service_category))
                                                                            @foreach($service_category as $key => $get_service_category)
                                                                                <option value="{{ $get_service_category->id }}"> {{ $get_service_category->name }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                    <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <input type="hidden" id="service_cat_id" name="driver_service_cat_id" value="{{ $driver_detials->service_cat_id }}">
                                                    @endif

                                                    <div class="form-group col-sm-12" id="service_section">
                                                        <div class="form-group row">
                                                            <label class="col-sm-2 col-form-label">Driver Services :<sup class="error">*</sup></label>
                                                            <div class="col-sm-10">

                                                                <div class="border-checkbox-section">
                                                                    <div class="border-checkbox-group border-checkbox-group-primary" id="hide_driver_service">
                                                                        <input name="driver_service" class="border-checkbox"
                                                                               {{ isset($driver_detials) ? (($driver_detials->driver_service == 1)? 'checked="checked"' : '') : 'checked="checked"' }}
                                                                               type="checkbox" id="driver_service">
                                                                        <label class="border-checkbox-label" for="driver_service"></label>
                                                                        <img src="{{ asset('/assets/images/service-category/bike-ride-128.png') }}" id="service_icon_1" class="provider_check_icon" width="40px" height="40px"> <span id="service_name_1">Bike Ride</span>
                                                                        <img src="{{ asset('/assets/images/service-category/taxi-ride-128.png') }}" id="service_icon_2" class="provider_check_icon" width="40px" height="40px" style="display: none;"> <span id="service_name_2" style="display: none;">Taxi Ride</span>
                                                                    </div>
                                                                <!--                                                                <div class="border-checkbox-group border-checkbox-group-primary" id="hide_rental_service">
                                                                    <input name="rental_service" class="border-checkbox"
                                                                           {{ (isset($driver_detials)) && $driver_detials->rental_service == 1 ? 'checked="checked"' : "" }}
                                                                    type="checkbox" id="rental_service">
                                                             <label class="border-checkbox-label" for="rental_service"></label>
                                                             <img src="{{ asset('/assets/images/service-category/bike-ride-128.png') }}" id="rental_service_icon_1" class="provider_check_icon" width="40px" height="40px"> <span id="rental_service_name_1"> Bike Rental </span>
                                                                    <img src="{{ asset('/assets/images/service-category/taxi-ride-128.png') }}" id="rental_service_icon_2" class="provider_check_icon" width="40px" height="40px" style="display: none;"> <span id="rental_service_name_2" style="display: none;"> Taxi Rental </span>
                                                                </div>-->
                                                                    <div class="border-checkbox-group border-checkbox-group-primary" id="hide_delivery_boy">
                                                                        <input name="delivery_boy" class="border-checkbox"
                                                                               {{ (isset($driver_detials)) && $driver_detials->delivery_boy == 1 ? 'checked="checked"' : "" }}
                                                                               type="checkbox" id="delivery_boy">
                                                                        <label class="border-checkbox-label" for="delivery_boy"></label>
                                                                        <img src="{{ asset('/assets/images/service-category/bike-ride.png') }}" class="provider_check_icon" width="40px" height="40px"> Store Delivery
                                                                    </div>
                                                                    <div class="border-checkbox-group border-checkbox-group-primary">
                                                                        <input name="courier_boy" class="border-checkbox"
                                                                               {{ (isset($driver_detials)) && $driver_detials->courier_boy == 1 ? 'checked=checked' : "" }}
                                                                               type="checkbox" id="courier_boy">
                                                                        <label class="border-checkbox-label" for="courier_boy"></label>
                                                                        <img src="{{ asset('/assets/images/service-category/courier-service.png') }}" class="provider_check_icon" width="40px" height="40px"> Courier Services
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($driver_detials))Add @else Edit @endif Driver</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-6">

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Driver Full Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="name" required id="name" placeholder="Driver Full Name" value="{{ (isset($driver_detials)) ? $driver_detials->name : old('name') }}">
                                                            <span class="error">{{ $errors->first('name') }}</span>
                                                        </div>
                                                    </div>

<!--                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Driver Last Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="lname" required id="lname" placeholder="Driver Last Name" value="{{ (isset($driver_detials)) ? $driver_detials->lname : old('lname') }}">
                                                            <span class="error">{{ $errors->first('lname') }}</span>
                                                        </div>
                                                    </div>-->

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Email:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="email" class="form-control" name="email" required {{ ( (isset($driver_detials)) && $driver_detials->login_type != "email") ? "readonly" : "" }} id="email" placeholder="Unique Email" value="{{ (isset($driver_detials)) ? App\model\User::Email2Stars($driver_detials->email) : old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>
                                                    </div>

                                                    @if(!isset($driver_detials))
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Password:<sup class="error">*</sup></label>
                                                            <div class="col-sm-8">
                                                                <input type="password" class="form-control" name="pass"   minlength="6" maxlength="16"  id="pass" placeholder="Password" value="{{ old('pass') }}">
                                                                <span class="error">{{ $errors->first('pass') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Confirm Password:<sup class="error">*</sup></label>
                                                            <div class="col-sm-8">
                                                                <input type="password" class="form-control" name="confirm_password" required id="confirm_password" placeholder="Confirm Password" value="{{ old('confirm_password') }}">
                                                                <span class="error">{{ $errors->first('confirm_password') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Contact Number:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="contact_number" required  id="phone" placeholder="Unique Contact Number" value="{{ (isset($driver_detials)) ? $driver_detials->country_code."".App\model\User::ContactNumber2Stars($driver_detials->contact_number) : '' }}">
                                                            <input type="hidden" id="contact_numbers" name="contact_numbers" value="{{ (isset($driver_detials)) ? $driver_detials->country_code."".$driver_detials->contact_number : '' }}">
                                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span><br>
                                                            <span class="error">{{ $errors->first('full_number') }}</span><br>
                                                            <span class="error">{{ $errors->first('contact_numbers') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Gender:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <div class="radio radio-outline radio-inline">
                                                                <input type="radio" id="male" name="gender" {{ $male = (isset($driver_detials))? ($driver_detials->gender == 1)? "checked" : "" : "" }} value="1">
                                                                <label class="label" for="male"> Male</label>
                                                            </div>
                                                            <div class="radio radio-outline radio-inline">
                                                                <input type="radio" id="female" name="gender" value="2" {{ $female = (isset($driver_detials))? ($driver_detials->gender == 2)? "checked" : "" : "" }}>
                                                                <label class="label" for="female"> Female</label>
                                                            </div>
                                                            <div class="col-sm-12">
                                                                <span class="error">{{ $errors->first('gender') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label image image-label">Profile Image:</label>
                                                        <div class="col-sm-8">
                                                            <div id="upload-image-preview">
                                                                @if(isset($driver_detials))
                                                                    <label for="image-upload" id="image-label">Change Image</label>
                                                                    <input type="file" id="image-upload" name="avatar" accept=".jpg,.jpeg,.png"/>
                                                                @else
                                                                    <label for="image-upload" id="image-label">Upload Image</label>
                                                                    <input type="file" id="image-upload" name="avatar" accept=".jpg,.jpeg,.png"/>
                                                                @endif
                                                            </div>
                                                            <span class="note">[Note: Upload only png, jpeg and jpg file.]</span>
                                                            <span class="error">{{ $errors->first('avatar') }}</span>
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
                                            <h5>@if(!isset($driver_detials))Add @else Edit @endif Driver Vehicle Type Details</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Type:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="vehicle_type_id" id="vehicle_type_id" class="form-control" required>
                                                                <option disabled selected>Select Vehicle Type</option>
                                                                @if(isset($vehicle_types))
                                                                    @foreach($vehicle_types as $key => $vehicle_type)
                                                                        <option value="{{ $vehicle_type->id }}" {{ (isset($driver_detials))? ($driver_detials->vehicle_type_id == $vehicle_type->id)? "selected" : "" : ""  }}>{{ $vehicle_type->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('vehicle_type_id') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Company:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="vehicle_company" required id="vehicle_company" placeholder="Vehicle Company Name" value="{{ (isset($driver_detials)) ? $driver_detials->vehicle_company : old('vehicle_company') }}">
                                                            <span class="error">{{ $errors->first('vehicle_company') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Model Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="model_name" required id="model_name" placeholder="Model Name" value="{{ (isset($driver_detials)) ? $driver_detials->model_name : old('model_name') }}">
                                                            <span class="error">{{ $errors->first('model_name') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row" id="number_of_seat" >
                                                        <label class="col-sm-4 col-form-label">Number of Seat:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="no_of_seat" id="no_of_seat" class="form-control" required>
                                                                <option disabled selected>Select Number of Seat</option>
                                                                @if(isset($driver_detials))
                                                                    @for($i=1; $i<=6; $i++)
                                                                        @if($driver_detials->no_of_seat == $i )
                                                                            <option value="{{ $i }}" selected>{{ $i }}</option>
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
                                                            <span class="error">{{ $errors->first('no_of_seat') }}</span>
                                                        </div>
                                                    </div>
                                                    {{--@elseif(isset($service_category) && $service_category->id == 4)--}}
{{--                                                    <div class="form-group row" id="support_weight" >--}}
{{--                                                        <label class="col-sm-4 col-form-label">Support Weight:</label>--}}
{{--                                                        <div class="col-sm-8">--}}
{{--                                                            <input type="number" class="form-control" name="support_weight" id="support_weight" placeholder="Support Weight" value="{{ (isset($driver_detials)) ? $driver_detials->support_weight : old('support_weight') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('support_weight') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
                                                    {{--@endif--}}

                                                    <div class="form-group row  extra_rental_service {{ (isset($driver_detials)) && $driver_detials->driver_service == 1 ? "show" : "hide" }}"  >
                                                        <label class="col-sm-4 col-form-label">Rental Service:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <div class="border-checkbox-section">

                                                                <div class="border-checkbox-group border-checkbox-group-primary" id="hide_rental_service">
                                                                    <input name="rental_service" class="border-checkbox"
                                                                           {{ (isset($driver_detials)) && $driver_detials->rental_service == 1 ? 'checked="checked"' : "" }}
                                                                           type="checkbox" id="rental_service">
                                                                    <label class="border-checkbox-label" for="rental_service"></label>
                                                                     <span id="rental_service_name_1"> Bike Rental </span>
                                                                     <span id="rental_service_name_2" style="display: none;"> Taxi Rental </span>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                        <div class="form-group row seat_availablity {{ (isset($driver_detials)) && $driver_detials->driver_service == 1 && $driver_detials->service_cat_id == 2 ? "show" : "hide" }} " >
                                                        <label class="col-sm-4 col-form-label">Seat Availabity:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <div class="border-checkbox-section">

                                                                <div class="border-checkbox-group border-checkbox-group-primary" >

                                                                    <div class="child_seat_handicap_section">
                                                                        <input name="child_seat" class="border-checkbox"
                                                                               {{ (isset($driver_detials)) && $driver_detials->child_seat == 1 ? 'checked="checked"' : "" }}
                                                                               type="checkbox" id="child_seat">
                                                                        <label class="border-checkbox-label" for="child_seat"></label> Child Seat Availability
                                                                        <input name="handicap" class="border-checkbox"
                                                                               {{ (isset($driver_detials)) && $driver_detials->handicap == 1 ? 'checked="checked"' : "" }}
                                                                               type="checkbox" id="handicap">
                                                                        &nbsp;
                                                                        <label class="border-checkbox-label" for="handicap"></label> Handicap Seat Availability
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Model Year:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <div class="input-group date form_datetime">
                                                                <input name="model_year" type="text" class="form-control category" placeholder="Model Year From Picker" id="model_year" value="{{ (isset($driver_detials)) ? $driver_detials->model_year : old('model_year') }}" readonly required>
                                                                <span class="input-group-append" id="basic-addon3">
                                                                    <label class="bg-c-green input-group-text"><span class="fa fa-remove"></span></label>
                                                                </span>
                                                                <span class="input-group-append" id="basic-addon3">
                                                                    <label class="bg-c-green input-group-text"><span class="fa fa-th"></span></label>
                                                                </span>
                                                            </div>
                                                            <span class="error">{{ $errors->first('model_year') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Plat No:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="plat_no" required id="plat_no" placeholder="Plat No" value="{{ (isset($driver_detials)) ? $driver_detials->plat_no : old('plat_no') }}">
                                                            <span class="error">{{ $errors->first('plat_no') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Vehicle Color:</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="vehicle_color" id="vehicle_color" placeholder="Vehicle Color" value="{{ (isset($driver_detials)) ? $driver_detials->vehicle_color : old('vehicle_color') }}">
                                                            <span class="error">{{ $errors->first('vehicle_color') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <center>
                                        <button type="submit" class="btn btn-success m-b-0">Save</button>
                                    </center>
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
            $("#number_of_seat").hide();
            $("#support_weight").hide();
            // $(".seat_availablity").addClass('hide');
            @if(!isset($driver_detials))
                $(".extra_rental_service").addClass('show');
            @endif
            // $(".extra_rental_service").addClass('show');

            $(document).on("change","#driver_service",function (){

                var curr_service_cat_id = $("#service_cat_id").val();
                if($(this).is(":checked")){
                    $(".extra_rental_service").addClass('show');
                    $(".extra_rental_service").removeClass('hide');
                    if(curr_service_cat_id == 2){
                        $(".seat_availablity").addClass('show');
                        $(".seat_availablity").removeClass('hide');
                    }else{
                        $(".seat_availablity").addClass('hide');
                        $(".seat_availablity").removeClass('show');
                    }
                }else{
                    $(".extra_rental_service").addClass('hide');
                    $(".extra_rental_service").removeClass('show');

                    $(".seat_availablity").addClass('hide');
                    $(".seat_availablity").removeClass('show');

                }
            });
        });
        @isset($driver_detials)
            $(document).ready(function(){
                var service_cat_id = {{isset($driver_detials->service_cat_id)?$driver_detials->service_cat_id:1}};
                ChangeLoad(service_cat_id);
            });
        @endisset
        function ChangeLoad(service_cat_id){
            /*$("#driver_service").prop('checked', false);*/
            $("#number_of_seat").hide();
            $("#support_weight").hide();
            // $(".seat_availablity").hide();
            /*$("#rental_service").prop('checked', false);
            $("#delivery_boy").prop('checked', false);
            $("#courier_boy").prop('checked', false);*/
            if (service_cat_id == 1){
                @if(!isset($driver_detials->provider_id))
                $("#driver_service").prop('checked', true);
                @endif
                $("#service_section").show();

                $("#hide_driver_service").show();
                $("#hide_rental_service").show();
                $("#hide_delivery_boy").show();
                $(".border-checkbox-label").show();

                $("#service_name_2").hide();
                $("#service_icon_2").hide();
                $("#service_icon_1").show();
                $("#service_name_1").show();

                $("#rental_service_name_2").hide();
                $("#rental_service_icon_2").hide();
                $("#rental_service_icon_1").show();
                $("#rental_service_name_1").show();
                /*$(".extra_rental_service").addClass('show');
                $(".extra_rental_service").removeClass('hide');*/
                $("#number_of_seat").hide();
                $("#support_weight").hide();

                @if(!isset($driver_detials->provider_id))
                    $(".extra_rental_service").addClass('show');
                    $(".extra_rental_service").removeClass('hide');
                    $(".seat_availablity").addClass('hide');
                    $(".seat_availablity").removeClass('show');
                @endif
            }
            else if (service_cat_id == 2){
                @if(!isset($driver_detials->provider_id))
                $("#driver_service").prop('checked', true);
                @endif
                $("#service_section").show();

                $("#hide_driver_service").show();
                $("#hide_rental_service").show();
                $("#hide_delivery_boy").show();
                $(".border-checkbox-label").show();

                $("#service_name_1").hide();
                $("#service_icon_1").hide();
                $("#service_icon_2").show();
                $("#service_name_2").show();

                $("#rental_service_name_1").hide();
                $("#rental_service_icon_1").hide();
                $("#rental_service_icon_2").show();
                $("#rental_service_name_2").show();
                $("#number_of_seat").show();
                $("#support_weight").hide();
                /*$(".extra_rental_service").addClass('show');
                $(".extra_rental_service").removeClass('hide');*/
                @if(!isset($driver_detials->provider_id))
                $(".extra_rental_service").addClass('show');
                $(".extra_rental_service").removeClass('hide');
                $(".seat_availablity").addClass('show');
                $(".seat_availablity").removeClass('hide');
                @endif

            }
            else {
                @if(!isset($driver_detials->provider_id))
                $("#driver_service").prop('checked', true);
                @endif
                $("#hide_driver_service").hide();
                $(".border-checkbox-label").hide();
                $("#hide_rental_service").hide();
                $("#hide_delivery_boy").hide();
                $("#service_section").show();
                $("#support_weight").show();
                $("#number_of_seat").hide();
                $("#support_weight").show();
                @if(!isset($driver_detials->provider_id))
                    $(".extra_rental_service").addClass('hide');
                    $(".extra_rental_service").removeClass('show');
                    $(".seat_availablity").addClass('hide');
                    $(".seat_availablity").removeClass('show');
                @endif

            }
        }
        $('#service_cat_id').on('change', function (e) {
            var service_cat_id = this.value;
            ChangeLoad(service_cat_id);
            $.ajax({
                type: 'get',
                url: '{{ route('get:admin:vehicle_type_list') }}',
                data: {service_cat_id: service_cat_id},
                success: function (result) {
                    console.log(result);
                    $("#vehicle_type_id").empty();
                    if (result.success == true){
                        // $.each(result.vehicle_type_id, function (index, subcatObj) {
                        //     var data = subcatObj.location;
                        //     var time_data = subcatObj.time;
                        //     distance_count = parseFloat(distance_count) + parseFloat(data);
                        //     time_count = parseFloat(time_count) + parseFloat(time_data);
                        // });
                        $("#vehicle_type_id").append(result.vehicle_type_list);
                    }
                }
            })
        });

        $('#bank_name').on('change', function (e) {
            var bank_code_id = service_cat_id = this.value;
            var bank_code = $("#bank_name option:selected").attr('bank_code');
            console.log("bank_code_id => " + bank_code_id);
            console.log("bank_code => " + bank_code);
            $("#bank_code").val(bank_code);
        });

        $('#network_name').on('change', function (e) {
            var network_code_id = this.value;
            var network_code = $("#network_name option:selected").attr('network_code');
            console.log("network_code_id  => " + network_code_id );
            console.log("network_code => " + network_code);
            $("#network_code").val(network_code);
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

    <script type="text/javascript" src="{{ asset('assets/js/country-code/intlTelInput.min.js')}}"></script>
    <script type="text/javascript">
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
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
            initialCountry: "US",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            preferredCountries: ['us'],
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
        $("#phone").on('keyup', function (event) {
            var contact_number = $(this).val();
            // var n = contact_number.indexOf("0", 0);
            // var n = contact_number.charAt(contact_number);
            // if (n == 0) {
            //     document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
            //     document.getElementById("contact_numbers").value = "";
            // }
            // else {
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
    </script>
@endsection

