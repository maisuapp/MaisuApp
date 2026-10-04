@extends('admin.layout.dispatcher_admin')
@section('title')
    Manual Ride Booking
@endsection
@section('page-css')
    <link href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" media="screen">
    <style>
        .input-group {
            margin-bottom: 0;
        }

        .input-group-append .input-group-text {
            background-color: #2ed8b6;
        }

        .ride-box {
            border: 1px solid #e0e0e0;
            padding: 15px;
            margin-bottom: 15px;
        }

        h4 {
            font-size: 16px;
        }

        /*Assign Drivers style*/
        .assign-driver-box {
            /*background-color: #999999;*/
            border: none;
            padding: 7px 15px;
            background-color: #eeeeee;
        }

        .search-drivers {
            width: 100%;
            margin-top: 20px;
            float: left;

        }

        .assign-drivers-wrapper {
            width: 100%;
            /*height: 328px;*/
            height: 270px;
            padding-right: 5px;
            float: left;
            border-top: 1px solid lightgray;
            overflow-y: auto;
        }

        .assign-drivers-content {
            width: 100%;
            float: left;
            margin: 0;
            padding: 10px 0;
            border-bottom: 1px solid lightgray;
        }

        .assign-drivers-content:last-of-type {
            border: none;
        }

        .drivers-image-media {
            width: 60px;
        }

        .drivers-left-content {
            width: 55%;
            float: left;
            margin-top: 5px;
            padding-top: 2px;
        }

        .drivers-right-content {
            width: 45%;
            float: left;
            text-align: right;
            margin-top: 2%;
        }

        .assign-drivers-btn {
            font-size: 14px;
            padding: 4px 8px;
            border-color: #2ed8b6;
            color: #2ed8b6;
        }

        .assign-drivers-btn:hover {
            background-color: #2ed8b6;
            border-color: #2ed8b6;
        }

        /* scroll width */
        .assign-drivers-wrapper::-webkit-scrollbar {
            width: 5px;
            border-radius: 50px;
        }

        /* scroll Track */
        .assign-drivers-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 50px;
        }

        /* scroll Handle */
        .assign-drivers-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 50px;
        }

        /* scroll Handle on hover */
        .assign-drivers-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
            border-radius: 50px;
        }

        @media (max-width: 512px) {
            .drivers-image-media {
                width: 95px;
            }

            .drivers-left-content {
                width: 100%;
                float: left;
            }

            .drivers-right-content {
                width: 100%;
                float: left;
                text-align: left;
                margin-top: 0;
            }
        }

        /*ride details*/
        .info-field {
            width: 100%;
            margin-top: 8px;
            float: left;
            padding-top: 15px;
            border-top: 1px solid lightgray;
        }

        .ride-detail-wrapper {
            width: 100%;
            float: left;
            margin: 0;
            padding: 5px 0;
        }

        .ride-detail-header {
            width: 70%;
            float: left;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        .ride-detail-content {
            width: 30%;
            float: left;
            margin: 0;
            padding: 0;
            text-align: right;
            color: #505b5f;
            font-weight: 600;
        }

        .veg {
            color: green;
        }

        .non-veg {
            color: #BF2131;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                <div class="other-service-horizontal-nav">
                    @include('admin.include.transport-horizontal-navbar')
                </div>
            @endif
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Manual Ride Booking</h5>
                            <span>Manual Ride Booking of
                                @if(isset($service_category) && $service_category->name != Null) {{ ucwords(strtolower($service_category->name)) }} @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">

                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                                <form id="main" method="post"
                                      action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? route('post:admin:update_transport_store_manual_ride',$slug) : route('post:dispatcher:update_manual_ride') : '' }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    @if(isset($service_category))
                                        <input type="hidden" name="service_cat_id"
                                               id="service_cat_id"
                                               value="{{$service_category->id}}">
                                    @endif
                                    @if($errors->first('service_cat_id'))
                                        <span class="error"> Service category is required.</span>
                                    @endif
                                    <div class="row">

                                        {{--<div class="col-sm-12">--}}
                                        {{--<div class="row">--}}

                                        <div class="form-group col-sm-12">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Customer details</h5>
                                                </div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="form-group col-sm-12">
                                                            <div class="form-group row">
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Contact
                                                                        No:<sup
                                                                                class="error">*</sup></label>
                                                                    <input type="text" class="form-control"
                                                                           name="contact_number" id="contact_number"
                                                                           placeholder="Customer Contact Number"
                                                                           value="{{ (isset($required_document)) ? $required_document->contact_number : old('contact_number') }}"
                                                                           required>
                                                                    <ul class="customer_contact dropdown-menu"
                                                                        role="listbox"
                                                                        id="contact_number_customer"
                                                                        style="top: 59px; left: 15px; display: none;">
                                                                    </ul>
                                                                    <span class="error">{{ $errors->first('contact_number') }}</span>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Name:<sup
                                                                                class="error">*</sup></label>
                                                                    <input id="user_id" type="hidden" name="user_id"
                                                                           class="form-control"
                                                                           value="{{isset($scheduleds)? $scheduleds->user_id : old('user_id')  }}"
                                                                           placeholder="User Id">
                                                                    <input type="text" class="form-control"
                                                                           name="customer_name"
                                                                           required
                                                                           id="customer_name"
                                                                           placeholder="Customer Name"
                                                                           value="{{ (isset($required_document)) ? $required_document->customer_name : old('customer_name') }}">
                                                                    @if($errors->first('user_id'))
                                                                        <span class="error"> Customer name is required.</span>
                                                                    @endif
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Email:<sup
                                                                                class="error">*</sup></label>
                                                                    <input type="text" class="form-control"
                                                                           name="customer_email"
                                                                           required
                                                                           id="customer_email"
                                                                           placeholder="Customer Email"
                                                                           value="{{ (isset($required_document)) ? $required_document->customer_email : old('customer_email') }}">
                                                                    <span class="error">{{ $errors->first('customer_email') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Ride Details
                                                        @if(isset($service_category) && $service_category->name != Null)
                                                            of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>
                                                    {{--<a href="{{ route('get:transport:vehicle_type') }}"--}}
                                                    {{--class="btn btn-success m-b-0 btn-right render_link"> Back</a>--}}
                                                </div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="col-lg-6">
                                                            <div class="ride-box">
                                                                <div class="row">
                                                                    <div class="form-group col-lg-12">
                                                                        <label class="col-form-label">Pickup
                                                                            Address:<sup
                                                                                    class="error">*</sup></label>
                                                                        <input type="text" class="form-control"
                                                                               name="pickup_address"
                                                                               required
                                                                               id="pickup_input"
                                                                               placeholder="Enter a Pickup Address"
                                                                               value="{{ (isset($required_document)) ? $required_document->pickup_address : old('pickup_address') }}">
                                                                        <span class="error">{{ $errors->first('pickup_address') }}</span>
                                                                        <input type="hidden" name="pickup_lat"
                                                                               id="pickup_lat"
                                                                               class="form-control">
                                                                        <input type="hidden" name="pickup_long"
                                                                               id="pickup_long"
                                                                               class="form-control">
                                                                    </div>
                                                                    <div class="form-group col-lg-12">
                                                                        <label class="col-form-label">Destination
                                                                            Address:<sup class="error">*</sup></label>
                                                                        <input type="text" class="form-control"
                                                                               name="destination_address"
                                                                               required
                                                                               id="destination_input"
                                                                               placeholder="Enter a Destination Address"
                                                                               value="{{ (isset($required_document)) ? $required_document->destination_address : old('destination_address') }}">
                                                                        <span class="error">{{ $errors->first('destination_address') }}</span>
                                                                        <input type="hidden" name="destination_lat"
                                                                               id="destination_lat"
                                                                               class="form-control">
                                                                        <input type="hidden" name="destination_long"
                                                                               id="destination_long"
                                                                               class="form-control">
                                                                    </div>
                                                                    <div class="form-group col-md-12 col-lg-12">
                                                                        <label class="col-form-label">Pickup Date
                                                                            Time:<sup
                                                                                    class="error">*</sup></label>
                                                                        <div class="input-group date form_datetime">
                                                                            <input name="pickup_datetime" type="text"
                                                                                   class="form-control category"
                                                                                   placeholder="Pickup Date Time From Picker"
                                                                                   id="pickup_time"
                                                                                   readonly required>
                                                                            <span class="input-group-append"
                                                                                  id="basic-addon3">
                                                                            <label class="bg-c-green input-group-text">
                                                                                <span class="fa fa-remove"></span>
                                                                            </label>
                                                                        </span>
                                                                            <span class="input-group-append"
                                                                                  id="basic-addon3">
                                                                            <label class="bg-c-green input-group-text">
                                                                                <span class="fa fa-th"></span>
                                                                            </label>
                                                                        </span>
                                                                        </div>

                                                                        <span class="error">{{ $errors->first('pickup_datetime') }}</span>
                                                                    </div>
                                                                    <div class="col-md-12 col-lg-12">
                                                                        <div class="form-control assign-driver-box">
                                                                            <label
                                                                                    class="col-form-label">Assign Driver
                                                                                <sup
                                                                                        class="error">*</sup></label>
                                                                        </div>
                                                                        <div class="search-drivers">
                                                                            <div class="form-group">
                                                                                <input type="search"
                                                                                       name="search-drivers"
                                                                                       id="search-drivers"
                                                                                       class="form-control"
                                                                                       placeholder="Search Driver">
                                                                                <span class="error">
                                                                            @if ($errors->first('driver_id'))
                                                                                        Assign driver is required.
                                                                                    @endif
                                                                        </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="assign-drivers-wrapper"
                                                                             id="assign-drivers-wrapper">
                                                                            @if(isset($provider_lists))
                                                                                @foreach($provider_lists as $key => $provider)
                                                                                    <div class="assign-drivers-content assign_drivers_content_{{ 1 }}">
                                                                                        <div class="media">
                                                                                            <div class="media-left media-middle">
                                                                                                @if($provider->avatar != Null)
                                                                                                    <img src="{{ asset('assets/images/profile-images/provider/'.$provider->avatar) }}"
                                                                                                         class="media-object drivers-image-media">
                                                                                                @else
                                                                                                    <img src="{{ asset('assets/images/website-logo-icon/user.png') }}"
                                                                                                         class="media-object drivers-image-media"
                                                                                                         style="width: 50px;">
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="media-body">
                                                                                                <div class="drivers-left-content media-middle">
                                                                                                    <h4 class="media-heading">{{ ucwords($provider->driver_name) }}</h4>
                                                                                                    <p>
                                                                                                        <i class="fa fa-phone text-success"></i> {{ $provider->contact_number }}
                                                                                                    </p>
                                                                                                </div>
                                                                                                <div class="drivers-right-content">
                                                                                            <span style="display: none;"
                                                                                                  class="vehicle_type_name_check"
                                                                                                  id="vehicle_type_name_check">{{ $provider->vehicle_type_name }}</span>
                                                                                                    <input style="display: contents;"
                                                                                                           type="radio"
                                                                                                           name="vehicle_type_id"
                                                                                                           class="vehicle_type_id"
                                                                                                           id="vehicle_type"
                                                                                                           data-price="{{ $provider->cost_for_km }}"
                                                                                                           value="{{ $provider->vehicle_type_id }}">
                                                                                                    <span style="display: none;"
                                                                                                          class="driver_name_check"
                                                                                                          id="driver_name_check">{{ $provider->driver_name }}</span>
                                                                                                    <input style="display: contents;"
                                                                                                           type="radio"
                                                                                                           name="driver_id"
                                                                                                           class="driver_id"
                                                                                                           value="{{ $provider->driver_details_id }}"
                                                                                                           id="service_{{ $key + 1 }}"
                                                                                                           tabindex="{{ $key + 1 }}">
                                                                                                    <label class="assign"
                                                                                                           for="service_{{ $key + 1 }}">
                                                                                                        <div class="btn btn-outline-primary assign-drivers-btn">
                                                                                                            Assign
                                                                                                            Driver
                                                                                                        </div>
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="ride-box">
                                                                <div class="row">
                                                                    <div class="form-group col-lg-12">
                                                                        <div class="form-group">
                                                                            <div class="map" id="map"
                                                                                 style="width: 100%; height: 300px; z-index: 0;"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <h4>Ride Estimate</h4>
                                                                        <div class="info-field">
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Vehicle
                                                                                    Fare
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="vehicle_fare"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">
                                                                                    Distance
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="distance">0 km</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">ETA
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="mins">00.00</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Base
                                                                                    Fare
                                                                                    Total
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="base_fare"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="detail-wrapper">
                                                                                <div class="ride-detail-header">Tax
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="tax" class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Total
                                                                                    Amount
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="total_pay"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                    <span class="error"
                                                                                          id="vehicle_type_error">
                                                                                @if($errors->first('vehicle_type_id'))
                                                                                            Assign driver is required.
                                                                                        @endif

                                                                            </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="driver_amount"
                                                                               id="driver_amount" class="form-control"
                                                                               placeholder="Driver Amount">
                                                                        <input type="hidden" name="vehicle_type_name"
                                                                               id="vehicle_type_name"
                                                                               class="form-control"
                                                                               placeholder="Vehicle Type Name">
                                                                        <input type="hidden" name="vehicle_cost_for_km"
                                                                               id="vehicle_cost_for_km"
                                                                               class="form-control"
                                                                               placeholder="Vehicle Cost For Km">
                                                                        <input type="hidden" name="driver_name"
                                                                               id="driver_name" class="form-control"
                                                                               placeholder="Driver Name">
                                                                        <input type="hidden" name="total_distance"
                                                                               id="distance_input" class="form-control"
                                                                               placeholder="Total Distance">
                                                                        <input type="hidden" name="eta" id="mins_input"
                                                                               class="form-control" placeholder="ETA">
                                                                        <input type="hidden" name="base_fare"
                                                                               id="base_fare_input" class="form-control"
                                                                               placeholder="Base Fare">
                                                                        <input type="hidden" name="total_pay"
                                                                               id="total_pay_input" class="form-control"
                                                                               placeholder="Total Ride Fare">
                                                                        <input type="hidden" name="tax" id="tax_input"
                                                                               class="form-control"
                                                                               placeholder="Tax Fare">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                {{--<label class="col-sm-4 col-form-label"></label>--}}
                                                <div class="col-sm-12">
                                                    <center>
                                                        <input type="submit" class="btn btn-success m-b-0"
                                                               id="submit"
                                                               value="Book Ride">
                                                        <input type="reset" class="btn btn-default"
                                                               value="Reset">
                                                    </center>
                                                </div>
                                            </div>
                                        </div>
                                        {{--</div>--}}
                                        {{--</div>--}}
                                    </div>
                                </form>
                            @endif
                            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2)
                                <form id="main" method="post"
                                      action="{{ route('post:dispatcher:update_manual_ride') }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    {{--@if(isset($service_category))--}}
                                    {{--<input type="hidden" name="service_cat_id"--}}
                                    {{--id="service_cat_id"--}}
                                    {{--value="{{$service_category->id}}">--}}
                                    {{--@endif--}}
                                    @if($errors->first('service_cat_id'))
                                        <span class="error"> Service category is required.</span>
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-12">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Customer details</h5>
                                                </div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="form-group col-sm-12">
                                                            <div class="form-group row">
                                                                <div class="form-group col-sm-12">
                                                                    <div class="form-group row">
                                                                        <label class="col-sm-12 col-form-label">Select
                                                                            Service Category:<sup class="error">*</sup></label>
                                                                        <div class="col-sm-12">
                                                                            <select name="service_cat_id"
                                                                                    id="service_category"
                                                                                    class="form-control"
                                                                                    required>
                                                                                @foreach($service_category_list as $key => $category)
                                                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <span class="error">{{ $errors->first('service_cat_id') }}</span>
                                                                        </div>
                                                                    </div>
                                                                    {{--<div class="form-group row">--}}
                                                                    {{--<label class="col-sm-4 col-form-label">Select Service Category:<sup class="error">*</sup></label>--}}
                                                                    {{--<div class="col-sm-8">--}}
                                                                    {{--<div class="toggle col-form-label food_type">--}}
                                                                    {{--<label class="col-sm-3 col-form-label veg">Bike Ride</label>--}}
                                                                    {{--<label>--}}
                                                                    {{--<input name="food_type"--}}
                                                                    {{--class="form-control"--}}
                                                                    {{--type="checkbox"--}}
                                                                    {{--id="food_type"--}}
                                                                    {{--@if(isset($product_details) && ($product_details->food_type == 1)) value="1"--}}
                                                                    {{--checked @endif>--}}
                                                                    {{--<span class="button-indecator"></span>--}}
                                                                    {{--</label>--}}
                                                                    {{--<label class="col-sm-4 col-form-label non-veg">Taxi Ride</label>--}}
                                                                    {{--</div>--}}
                                                                    {{--<span class="error">{{ $errors->first('product_name') }}</span>--}}
                                                                    {{--</div>--}}
                                                                    {{--</div>--}}
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Contact
                                                                        No:<sup class="error">*</sup></label>
                                                                    <input type="text" class="form-control"
                                                                           name="contact_number" id="contact_number"
                                                                           placeholder="Customer Contact Number"
                                                                           value="{{ (isset($required_document)) ? $required_document->contact_number : old('contact_number') }}"
                                                                           required>
                                                                    <ul class="customer_contact dropdown-menu"
                                                                        role="listbox"
                                                                        id="contact_number_customer"
                                                                        style="top: 59px; left: 15px; display: none;">
                                                                    </ul>
                                                                    <span class="error">{{ $errors->first('contact_number') }}</span>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Name:<sup
                                                                                class="error">*</sup></label>
                                                                    <input id="user_id" type="hidden" name="user_id"
                                                                           class="form-control"
                                                                           value="{{isset($scheduleds)? $scheduleds->user_id : old('user_id')  }}"
                                                                           placeholder="User Id">
                                                                    <input type="text" class="form-control"
                                                                           name="customer_name"
                                                                           required
                                                                           id="customer_name"
                                                                           placeholder="Customer Name"
                                                                           value="{{ (isset($required_document)) ? $required_document->customer_name : old('customer_name') }}">
                                                                    @if($errors->first('user_id'))
                                                                        <span class="error"> Customer name is required.</span>
                                                                    @endif
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label class="col-form-label">Customer Email:<sup
                                                                                class="error">*</sup></label>
                                                                    <input type="text" class="form-control"
                                                                           name="customer_email"
                                                                           required
                                                                           id="customer_email"
                                                                           placeholder="Customer Email"
                                                                           value="{{ (isset($required_document)) ? $required_document->customer_email : old('customer_email') }}">
                                                                    <span class="error">{{ $errors->first('customer_email') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Ride Details</h5>
                                                </div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="col-lg-6">
                                                            <div class="ride-box">
                                                                <div class="row">
                                                                    <div class="form-group col-lg-12">
                                                                        <label class="col-form-label">Pickup
                                                                            Address:<sup
                                                                                    class="error">*</sup></label>
                                                                        <input type="text" class="form-control"
                                                                               name="pickup_address"
                                                                               required
                                                                               id="pickup_input"
                                                                               placeholder="Enter a Pickup Address"
                                                                               value="{{ (isset($required_document)) ? $required_document->pickup_address : old('pickup_address') }}">
                                                                        <span class="error">{{ $errors->first('pickup_address') }}</span>
                                                                        <input type="hidden" name="pickup_lat"
                                                                               id="pickup_lat"
                                                                               class="form-control">
                                                                        <input type="hidden" name="pickup_long"
                                                                               id="pickup_long"
                                                                               class="form-control">
                                                                    </div>
                                                                    <div class="form-group col-lg-12">
                                                                        <label class="col-form-label">Destination
                                                                            Address:<sup class="error">*</sup></label>
                                                                        <input type="text" class="form-control"
                                                                               name="destination_address"
                                                                               required
                                                                               id="destination_input"
                                                                               placeholder="Enter a Destination Address"
                                                                               value="{{ (isset($required_document)) ? $required_document->destination_address : old('destination_address') }}">
                                                                        <span class="error">{{ $errors->first('destination_address') }}</span>
                                                                        <input type="hidden" name="destination_lat"
                                                                               id="destination_lat"
                                                                               class="form-control">
                                                                        <input type="hidden" name="destination_long"
                                                                               id="destination_long"
                                                                               class="form-control">
                                                                    </div>
                                                                    <div class="form-group col-md-12 col-lg-12">
                                                                        <label class="col-form-label">Pickup Date
                                                                            Time:<sup
                                                                                    class="error">*</sup></label>
                                                                        <div class="input-group date form_datetime">
                                                                            <input name="pickup_datetime" type="text"
                                                                                   class="form-control category"
                                                                                   placeholder="Pickup Date Time From Picker"
                                                                                   id="pickup_time"
                                                                                   readonly required>
                                                                            <span class="input-group-append"
                                                                                  id="basic-addon3">
                                                                            <label class="bg-c-green input-group-text">
                                                                                <span class="fa fa-remove"></span>
                                                                            </label>
                                                                        </span>
                                                                            <span class="input-group-append"
                                                                                  id="basic-addon3">
                                                                            <label class="bg-c-green input-group-text">
                                                                                <span class="fa fa-th"></span>
                                                                            </label>
                                                                        </span>
                                                                        </div>

                                                                        <span class="error">{{ $errors->first('pickup_datetime') }}</span>
                                                                    </div>
                                                                    <div class="col-md-12 col-lg-12">
                                                                        <div class="form-control assign-driver-box">
                                                                            <label
                                                                                    class="col-form-label">Assign Driver
                                                                                <sup
                                                                                        class="error">*</sup></label>
                                                                        </div>
                                                                        <div class="search-drivers">
                                                                            <div class="form-group">
                                                                                <input type="search"
                                                                                       name="search-drivers"
                                                                                       id="search-drivers"
                                                                                       class="form-control"
                                                                                       placeholder="Search Driver">
                                                                                <span class="error">
                                                                            @if ($errors->first('driver_id'))
                                                                                        Assign driver is required.
                                                                                    @endif
                                                                        </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="assign-drivers-wrapper"
                                                                             id="assign-drivers-wrapper">
                                                                            @if(isset($provider_lists))
                                                                                @foreach($provider_lists as $key => $provider)
                                                                                    <div class="assign-drivers-content assign_drivers_content_{{ 1 }}">
                                                                                        <div class="media">
                                                                                            <div class="media-left media-middle">
                                                                                                @if($provider->avatar != Null)
                                                                                                    <img src="{{ asset('assets/images/profile-images/provider/'.$provider->avatar) }}"
                                                                                                         class="media-object drivers-image-media">
                                                                                                @else
                                                                                                    <img src="{{ asset('assets/images/website-logo-icon/user.png') }}"
                                                                                                         class="media-object drivers-image-media"
                                                                                                         style="width: 50px;">
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="media-body">
                                                                                                <div class="drivers-left-content media-middle">
                                                                                                    <h4 class="media-heading">{{ ucwords($provider->driver_name) }}</h4>
                                                                                                    <p>
                                                                                                        <i class="fa fa-phone text-success"></i> {{ $provider->contact_number }}
                                                                                                    </p>
                                                                                                </div>
                                                                                                <div class="drivers-right-content">
                                                                                            <span style="display: none;"
                                                                                                  class="vehicle_type_name_check"
                                                                                                  id="vehicle_type_name_check">{{ $provider->vehicle_type_name }}</span>
                                                                                                    <input style="display: contents;"
                                                                                                           type="radio"
                                                                                                           name="vehicle_type_id"
                                                                                                           class="vehicle_type_id"
                                                                                                           id="vehicle_type"
                                                                                                           time-fare-price="{{ $provider->time_fare }}"
                                                                                                           data-price="{{ $provider->cost_for_km }}"
                                                                                                           value="{{ $provider->vehicle_type_id }}">
                                                                                                    <span style="display: none;"
                                                                                                          class="driver_name_check"
                                                                                                          id="driver_name_check">{{ $provider->driver_name }}</span>
                                                                                                    <input style="display: contents;"
                                                                                                           type="radio"
                                                                                                           name="driver_id"
                                                                                                           class="driver_id"
                                                                                                           value="{{ $provider->driver_details_id }}"
                                                                                                           id="service_{{ $key + 1 }}"
                                                                                                           tabindex="{{ $key + 1 }}">
                                                                                                    <label class="assign"
                                                                                                           for="service_{{ $key + 1 }}">
                                                                                                        <div class="btn btn-outline-primary assign-drivers-btn">
                                                                                                            Assign
                                                                                                            Driver
                                                                                                        </div>
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="ride-box">
                                                                <div class="row">
                                                                    <div class="form-group col-lg-12">
                                                                        <div class="form-group">
                                                                            <div class="map" id="map"
                                                                                 style="width: 100%; height: 300px; z-index: 0;"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <h4>Ride Estimate</h4>
                                                                        <div class="info-field">
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Vehicle
                                                                                    Fare
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="vehicle_fare"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">
                                                                                    Distance
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="distance">0 km</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">ETA
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="mins">00.00</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Time Fare
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="time_fare"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Base
                                                                                    Fare
                                                                                    Total
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="base_fare"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="detail-wrapper">
                                                                                <div class="ride-detail-header">Tax
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="tax" class="currency"> 0
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ride-detail-wrapper">
                                                                                <div class="ride-detail-header">Total
                                                                                    Amount
                                                                                </div>
                                                                                <div class="ride-detail-content">
                                                                                    <div id="total_pay"
                                                                                         class="currency"> 0
                                                                                    </div>
                                                                                    <span class="error" id="vehicle_type_error">
                                                                                        @if($errors->first('vehicle_type_id'))
                                                                                            Assign driver is required.
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="driver_amount"
                                                                               id="driver_amount" class="form-control"
                                                                               placeholder="Driver Amount">
                                                                        <input type="hidden" name="vehicle_type_name"
                                                                               id="vehicle_type_name"
                                                                               class="form-control"
                                                                               placeholder="Vehicle Type Name">
                                                                        <input type="hidden" name="vehicle_cost_for_km"
                                                                               id="vehicle_cost_for_km"
                                                                               class="form-control"
                                                                               placeholder="Vehicle Cost For Km">
                                                                        <input type="hidden" name="driver_name"
                                                                               id="driver_name" class="form-control"
                                                                               placeholder="Driver Name">
                                                                        <input type="hidden" name="total_distance"
                                                                               id="distance_input" class="form-control"
                                                                               placeholder="Total Distance">
                                                                        <input type="hidden" name="eta" id="mins_input"
                                                                               class="form-control" placeholder="ETA">
                                                                        <input type="hidden" name="time_fare_amount"
                                                                               id="time_fare_input"
                                                                               class="form-control"
                                                                               placeholder="Time Fare Amount">
                                                                        <input type="hidden" name="base_fare"
                                                                               id="base_fare_input" class="form-control"
                                                                               placeholder="Base Fare">
                                                                        <input type="hidden" name="total_pay"
                                                                               id="total_pay_input" class="form-control"
                                                                               placeholder="Total Ride Fare">
                                                                        <input type="hidden" name="tax" id="tax_input"
                                                                               class="form-control"
                                                                               placeholder="Tax Fare">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <center>
                                                        <input type="submit" class="btn btn-success m-b-0"
                                                               id="submit"
                                                               value="Book Ride">
                                                        <input type="reset" class="btn btn-default"
                                                               value="Reset">
                                                    </center>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script>
        $("input").not("#submit").keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
    </script>
    <script type="text/javascript" src="{{asset('assets/js/bootstrap-datetimepicker.js')}}" charset="UTF-8"></script>
    <script type="text/javascript">
        var param1 = new Date();
        var param2 = param1.getFullYear() + '-' + (param1.getMonth() + 1) + '-' + (param1.getDate() + 4) + ' 23:59';
        $('.form_datetime').datetimepicker({
            // format: "dd-mm-yyyy hh:ii",
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            startDate: param1,
            endDate: param2,
            minuteStep: 10
        });
    </script>
    <script>
        function getServiceSettings(id) {
            var url = '{{ route("get:ajax:dispatcher:service_setting") }}';
            var response = function () {
                var tmp = null;
                $.ajax({
                    'async': false,
                    'type': "GET",
                    'global': false,
                    'dataType': 'html',
                    'url': url,
                    'data': {service_cat_id: id},
                    'success': function (data) {
                        tmp = data;
                    }
                });
                return tmp;
            }();
            return JSON.parse(response);
        }

        @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2))
        $("#service_category").on('change', function (e) {
            $("#search-drivers").val('');
            $("#driver_name").val('');
            $("#vehicle_type_name").val('');
            document.getElementById("vehicle_cost_for_km").value = '';
            document.getElementById("driver_amount").value = '';
            document.getElementById("vehicle_fare").innerHTML = ' 0';
            document.getElementById("base_fare").innerHTML = ' 0';
            document.getElementById("base_fare_input").value = '';
            document.getElementById("total_pay").innerHTML = ' 0';
            document.getElementById("total_pay_input").value = '';
            document.getElementById("tax").innerHTML = ' 0';
            document.getElementById("tax_input").value = '';
            // document.getElementById("assign-drivers-wrapper").innerHTML = '';
            // document.getElementById("assign-drivers-wrapper").innerHTML = "<p style='color: red; margin-top: 10px;'>Drivers Not Found !</p>";
            document.getElementById("vehicle_type_error").innerHTML = 'First Assign Driver Is Required';

            var cat_id = e.target.value;
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2))
                var url = '{{ route("get:ajax:dispatcher:drivers_manual_book_change_category") }}';
                $.ajax({
                    url: url,
                    type: "get",
                    data: {slug: cat_id},
                    success: function (data) {
                        getAjaxData(data);
                    }
                });
            @endif
        });

        @endif
        $("#contact_number").on('keyup', function (e) {
            var contact_number = e.target.value;
            var slug = "{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? $slug : '' : '' }}";
            $("#contact_number_customer").empty();
            $.ajax({
                url: '{{ route("get:ajax:admin:transport_search_customer_manual_book") }}',
                type: "get",
                data: {slug: slug, contact_number: contact_number},
                success: function (data) {
                    if (data.success == false) {
                        $("#contact_number_customer").empty();
                        $("#contact_number_customer").append('<li><a disabled>Customers Contact Number not found!</a></li>');
                        $("#contact_number_customer li").click(function () {
                            $(".customer_contact").css("display", "none");
                        });
                    } else {
                        $(".customer_contact").css("display", "block");
                        $("#contact_number_customer").empty();
                        $.each(data['customers'], function (index, customers) {
                            $("#contact_number_customer").append('<li><span class="user_id" style="display: none">' + customers.id + '</span><span class="name" style="display: none">' + customers.first_name + '</span><span class="email" style="display: none">' + customers.email + '</span><a class="dropdown-item"  role="option"><strong> ' + customers.contact_number + "</strong> - " + customers.first_name + '</a></li>');
                            $("#contact_number_customer li").click(function () {
                                var phone = $('a.dropdown-item', this).text();
                                var name = $('span.name', this).text();
                                var email = $('span.email', this).text();
                                var user_id = $('span.user_id', this).text();
                                $("#contact_number").val(phone);
                                $("#user_id").val(user_id);
                                $('input[name="user_id"]').attr('value', user_id);
                                $("#customer_name").val(name);
                                $("#customer_email").val(email);
                                $(".customer_contact").css("display", "none");
                            });
                        });
                    }
                }
            });
        });
        $("#search-drivers").on('keyup', function (e) {
            var search_drivers = e.target.value;
            var pickup_time = $("#pickup_time").val();
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1))
                var slug = "{{ $slug }}";
                var url = '{{ route("get:ajax:admin:transport_search_drivers_manual_book") }}';
            @elseif(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2))
                var slug = $("#service_category").val();
                var url = '{{ route("get:ajax:dispatcher:transport_search_drivers_manual_book") }}';
            @endif
            $.ajax({
                url: url,
                type: "get",
                data: {slug: slug, search_drivers: search_drivers, pickup_time: pickup_time},
                success: function (data) {
                    getAjaxData(data);
                }
            });
        });

        $(".assign-drivers-content").click(Doclick);

        function jsUcfirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function Doclick() {
            var name = $('h4.media-heading', this).text();
            $("#search-drivers").val(name);
            var driver_name = $('.driver_name_check', this).text();
            $("#driver_name").val(driver_name);
            var vehicle_type = $('.vehicle_type_name_check', this).text();
            $("#vehicle_type_name").val(vehicle_type);
            var name = $('h4.media-heading', this).text();
            var a = $('input.vehicle_type_id', this).prop("checked", true);
            var a = $('input.driver_id', this).prop("checked", true);

            var tax_price = 0;
            var admin_commission = 0;
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1))
                tax_price = "{{ isset($get_tax)? ($get_tax != Null)? $get_tax : 0 : 0 }}";
                admin_commission = "{{ isset($admin_commission)? ($admin_commission != Null)? $admin_commission : 0 : 0 }}";
            @elseif(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2))
                var slug = $("#service_category").val();
                var service_setting = getServiceSettings(slug);
                tax_price = service_setting.tax;
                admin_commission = service_setting.admin_commission;
            @endif

            document.getElementById("vehicle_type_error").innerHTML = '';
            var distanced = $('#distance').text();
            var distanceFare = distanced.replace(/[^\d.]/g, '');

            var get_mins = $('#mins').text();
            var get_minsFare = get_mins.replace(/[^\d.]/g, '');

            var type_cost = $('input[name="vehicle_type_id"]:checked', this).attr('data-price');
            var time_fare = $('input[name="vehicle_type_id"]:checked', this).attr('time-fare-price');
            document.getElementById("vehicle_cost_for_km").value = type_cost;

            var time_fare_amount = time_fare * get_minsFare;
            var total = type_cost * distanceFare;
            var total_time_fare = total + time_fare_amount;
            var tax = (tax_price * total_time_fare) / 100;
            // var tax = (tax_price * total) / 100;
            // var grandtotal = total + tax;
            var grandtotal = total_time_fare + tax;

            var admin_earing = (grandtotal * admin_commission) / 100;
            var driver_amount = grandtotal - admin_earing;

            document.getElementById("driver_amount").value = driver_amount.toFixed(2);

            document.getElementById("vehicle_fare").innerHTML = ' ' + type_cost;
            document.getElementById("time_fare").innerHTML = ' ' + time_fare_amount;
            document.getElementById("time_fare_input").value = time_fare_amount.toFixed(2);
            document.getElementById("base_fare").innerHTML = ' ' + total.toFixed(2);
            document.getElementById("base_fare_input").value = total.toFixed(2);
            document.getElementById("total_pay").innerHTML = ' ' + grandtotal.toFixed(2);
            document.getElementById("total_pay_input").value = grandtotal.toFixed(2);
            document.getElementById("tax").innerHTML = ' ' + tax.toFixed(2);
            document.getElementById("tax_input").value = tax.toFixed(2);
        }

        function getAjaxData(data) {
            if (data.success == false) {
                document.getElementById("assign-drivers-wrapper").innerHTML = "<p style='color: red; margin-top: 10px;'>Drivers Not Found !</p>";
            } else {
                var html = document.getElementById("assign-drivers-wrapper").innerHTML = '';
                var k = 0;
                $.each(data['drivers'], function (index, drivers) {
                    var image_name = drivers.avatar;
                    var image_path = "{{ asset('assets/images/profile-images/provider') }}";
                    if (image_name == null) {
                        var image = "{{ asset('assets/images/website-logo-icon/user.png') }}";
                    } else {
                        var image = image_path + "/" + image_name;
                    }
                    html += '<div class="assign-drivers-content assign_drivers_content_' + drivers.id + '"><div class="media">' +
                        '<div class="media-left media-middle">' +
                        '<img src="' + image + '" class="media-object drivers-image-media"></div><div class="media-body">' +
                        '<div class="drivers-left-content media-middle">' +
                        '<h4 class="media-heading">' + jsUcfirst(drivers.driver_name) + '</h4>' +
                        '<p><i class="fa fa-phone text-success"></i> ' + drivers.contact_number + '</p>' +
                        '</div>' +
                        '<div class="drivers-right-content">' +
                        '<span style="display: none;" class="vehicle_type_name_check" id="vehicle_type_name_check">' + drivers.vehicle_type_name + '</span>' +
                        '<span style="display: none;" class="driver_name_check" id="driver_name_check">' + drivers.driver_name + '</span>' +
                        '<input style="display: contents;" type="radio"' +
                        'name="vehicle_type_id" class="vehicle_type_id" id="vehicle_type" time-fare-price="' + drivers.time_fare + '" data-price="' + drivers.cost_for_km + '" ' +
                        'value="' + drivers.vehicle_type_id + '">' +
                        '<input style="display: contents;" type="radio" name="driver_id" class="driver_id" value="' + drivers.driver_details_id + '" id="service_' + drivers.driver_details_id + '"' +
                        'tabindex="' + drivers.driver_details_id + '">' +
                        '<label class="assign" for="service_' + drivers.driver_details_id + '"><div class="btn btn-outline-primary assign-drivers-btn">Assign Driver</div></label></div></div></div></div>';
                    document.getElementById("assign-drivers-wrapper").innerHTML = html;
                    k++;
                    $(".assign-drivers-content").click(Doclick);
                });
            }
        }
    </script>
    <script>
        // $("#add-waypoints").click(function () {
        //
        //     var count = $(this).data("count") || 0;
        //     if (count == 0) {
        //         $("#way1").css('display', 'block');
        //     } else if (count == 1) {
        //         $("#way2").css('display', 'block');
        //     } else if (count == 2) {
        //         $("#way3").css('display', 'block');
        //     } else {
        //         $("#way-error").css({'display': 'block', 'color': 'red'});
        //     }
        //     $(this).data("count", ++count);
        // });

        function initMap() {
            // var directionsService = new google.maps.DirectionsService;
            // var directionsDisplay = new google.maps.DirectionsRenderer;
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 8,
                center: {lat: 14.5995, lng: 120.9842}
            });
            // directionsDisplay.setMap(map);
            var pickup_input = new google.maps.places.Autocomplete(
                document.getElementById('pickup_input'), {places: true})

            var destination_input = new google.maps.places.Autocomplete(
                document.getElementById('destination_input'), {places: true})
            // var waypoints1 = new google.maps.places.Autocomplete(
            //     document.getElementById('waypoints1'), {places: true})
            // var waypoints2 = new google.maps.places.Autocomplete(
            //     document.getElementById('waypoints2'), {places: true})
            // var waypoints3 = new google.maps.places.Autocomplete(
            //     document.getElementById('waypoints3'), {places: true})
            // this.map = map;
            setupPlaceChangedListener(pickup_input, 'pickup');
            setupPlaceChangedListener(destination_input, 'destination');
            // setupPlaceChangedListener(waypoints1, 'waypoints1');
            // setupPlaceChangedListener(waypoints2, 'waypoints2');
            // setupPlaceChangedListener(waypoints3, 'waypoints3');
            // document.getElementById('submit').addEventListener('click', function () {
            //     calculateAndDisplayRoute(directionsService, directionsDisplay);
            // });
        }

        function setupPlaceChangedListener(autocomplete, mode) {
            // autocomplete.bindTo('bounds', this.map);
            autocomplete.addListener("place_changed", function () {
                var place = autocomplete.getPlace();
                if (!place.place_id) {
                    window.alert("Please select an option from the dropdown list.");
                    return;
                }
                if (mode === 'pickup') {
                    document.getElementById('pickup_lat').value = place.geometry.location.lat();
                    document.getElementById('pickup_long').value = place.geometry.location.lng();
                } else if (mode === 'destination') {
                    document.getElementById('destination_lat').value = place.geometry.location.lat();
                    document.getElementById('destination_long').value = place.geometry.location.lng();
                }
                // else if (mode === 'waypoints1') {
                //     document.getElementById('waypoints1_lat').value = place.geometry.location.lat();
                //     document.getElementById('waypoints1_longi').value = place.geometry.location.lng();
                // } else if (mode === 'waypoints2') {
                //     document.getElementById('waypoints2_lat').value = place.geometry.location.lat();
                //     document.getElementById('waypoints2_longi').value = place.geometry.location.lng();
                // } else if (mode === 'waypoints3') {
                //     document.getElementById('waypoints3_lat').value = place.geometry.location.lat();
                //     document.getElementById('waypoints3_longi').value = place.geometry.location.lng();
                // }
                var directionsService = new google.maps.DirectionsService;
                var directionsDisplay = new google.maps.DirectionsRenderer;
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: {lat: 14.5995, lng: 120.9842}
                });
                directionsDisplay.setMap(map);
                calculateAndDisplayRoute(directionsService, directionsDisplay);
            });
        }

        function calculateAndDisplayRoute(directionsService, directionsDisplay) {
            var pickup_check = document.getElementById('pickup_input').value;
            var destination_check = document.getElementById('destination_input').value;
            if (!pickup_check || !destination_check) {
                return;
            }
            // var waypts = [];
            // var checkboxArray = [document.getElementById('waypoints1').value, document.getElementById('waypoints2').value, document.getElementById('waypoints3').value];
            // for (var i = 0; i < checkboxArray.length; i++) {
            //     if (checkboxArray[i] !== undefined && checkboxArray[i] !== null && checkboxArray[i] !== "") {
            //         waypts.push({
            //             location: checkboxArray[i],
            //             stopover: true
            //         });
            //     }
            // }
            directionsService.route({
                origin: document.getElementById('pickup_input').value,
                destination: document.getElementById('destination_input').value,
                // waypoints: waypts,
                optimizeWaypoints: true,
                travelMode: 'DRIVING'
            }, function (response, status) {
                if (status === 'OK') {
                    directionsDisplay.setDirections(response);
                    var route = response.routes[0];
                    // mins.innerHTML = "";
                    // mins.innerHTML += response.routes[0].legs[0].duration.text + '<br>';
                    // mins1.value = response.routes[0].legs[0].duration.text;
                    var distance_check = [];
                    for (var i = 0; i < route.legs.length; i++) {
                        var waytotal = route.legs[i].distance.text;
                        var wp = waytotal.replace(/[^\d.]/g, '');

                        var time = route.legs[i].duration.value;
                        var time_round = (time) / 60;
                        distance_check.push({
                            location: wp,
                            time: Math.round(time_round),
                        });
                    }
                    var distance_count = 0;
                    var time_count = 0;
                    $.each(distance_check, function (index, subcatObj) {
                        var data = subcatObj.location;
                        var time_data = subcatObj.time;
                        distance_count = parseFloat(distance_count) + parseFloat(data);
                        time_count = parseFloat(time_count) + parseFloat(time_data);
                    });
                    document.getElementById('mins').innerHTML = time_count + " mins";
                    document.getElementById('mins_input').value = time_count + " mins";
                    document.getElementById('distance').innerHTML = distance_count.toFixed(2) + " km";
                    document.getElementById('distance_input').value = distance_count.toFixed(2) + " km";
                    var distanceFare = distance_count.toFixed(2);
                    var type_cost = $('input[name="vehicle_type_id"]:checked').attr('data-price');
                    var time_fare = $('input[name="vehicle_type_id"]:checked').attr('time-fare-price');
                    if (type_cost == null || time_fare == null) {
                        type_cost = 0;
                        time_fare = 0;
                        document.getElementById("vehicle_type_error").innerHTML = 'First Assign Driver Is Required';
                    }
                    document.getElementById("vehicle_cost_for_km").value = type_cost;

                    var time_fare_amount = time_fare * time_count;

                    var tax_price = 0;
                    var admin_commission = 0;
                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1))
                        tax_price = "{{ isset($get_tax)? ($get_tax != Null)? $get_tax : 0 : 0 }}";
                        admin_commission = "{{ isset($admin_commission)? ($admin_commission != Null)? $admin_commission : 0 : 0 }}";
                    @elseif(Illuminate\Support\Facades\Auth::guard("admin")->check() && (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2))
                        var slug = $("#service_category").val();
                        var service_setting = getServiceSettings(slug);
                        tax_price = service_setting.tax;
                        admin_commission = service_setting.admin_commission;
                    @endif
                    var total = type_cost * distanceFare;
                    var total_time_fare = total + time_fare_amount;
                    var tax = (tax_price * total_time_fare) / 100;
                    // var tax = (tax_price * total) / 100;
                    // var grandtotal = total + tax;
                    var grandtotal = total_time_fare + tax;

                    var admin_earing = (grandtotal * admin_commission) / 100;
                    var driver_amount = grandtotal - admin_earing;

                    document.getElementById("driver_amount").value = driver_amount.toFixed(2);

                    document.getElementById("time_fare").innerHTML = ' ' + time_fare_amount;
                    document.getElementById("time_fare_input").value = time_fare_amount.toFixed(2);

                    document.getElementById("base_fare").innerHTML = ' ' + total.toFixed(2);
                    document.getElementById("base_fare_input").value = total.toFixed(2);
                    document.getElementById("total_pay").innerHTML = ' ' + grandtotal.toFixed(2);
                    document.getElementById("total_pay_input").value = grandtotal.toFixed(2);
                    document.getElementById("tax").innerHTML = ' ' + tax.toFixed(2);
                    document.getElementById("tax_input").value = tax.toFixed(2);
                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
        }
    </script>
    @php $map_key = \App\model\GeneralSettings::first() @endphp
    <script src="https://maps.googleapis.com/maps/api/js?key={{ isset($map_key)? ($map_key->map_key != Null)? $map_key->map_key : 0 : 0 }}&libraries=places&callback=initMap"
            async="" defer=""></script>
@endsection

