@extends('admin.layout.super_admin')
@section('title')
    @if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0) Un-Approved @elseif(isset($status) && $status == 2) Blocked @elseif(isset($status) && $status == 3) Rejected @endif Drivers List
@endsection
@section('page-css')
    <style>
        .tab-panel .card .card-header {
            padding: 5px 5px 0 5px;
        }

        .tab-panel .card .card-block {
            padding: 0 1.25rem 0.75rem 1.25rem;
        }

        .tab-panel .card .card-block p {
            margin-bottom: 0rem;
        }

        .media-body {
            padding-top: 5px;
        }

        .media-body h5 {
            margin-bottom: .5rem;
        }

        .media {
            /*cursor: pointer;*/
            padding: 15px 0;
            margin-top: 0px;
        }

        .generic-card-body {
            border-bottom: 1px solid lightgray;
        }

        .generic-card-body:last-of-type {
            border: none;
        }

        .generic-card-body:hover {
            background-color: #f8f8f8;
        }

        .provider-head {
            padding: 7px;
            text-align: center;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            /*background-color: #eeeeee; */
        }

        .provider-head-enroute {
            color: #fff;
            background-color: #be2020;
        }

        .nav-tabs {
            border: none;
        }

        .nav .nav-item {
            margin-right: 5px !important;
            background-color: #fefefe;
            box-shadow: 0 0 5px 0 rgba(43, 43, 43, 0.1), 0 11px 6px -7px rgba(43, 43, 43, 0.1);
        }

        .nav .nav-item a {
            border-radius: unset;
        }

        /*provider-head-reache*/
        .provider-head-reache {
            color: #fff;
            background-color: #337ab7;
        }

        .md-tabs .nav-item a#enroute_pickup_provider:active,
        .md-tabs .nav-item a#enroute_pickup_provider:focus,
        .md-tabs .nav-item a#enroute_pickup_provider:hover {
            color: #be2020;
        }

        .md-tabs .nav-item a#enroute_pickup_provider.active ~ #enroute_pickup_slide,
        .md-tabs .nav-item a#enroute_pickup_provider:active ~ #enroute_pickup_slide,
        .md-tabs .nav-item a#enroute_pickup_provider:focus ~ #enroute_pickup_slide,
        .md-tabs .nav-item a#enroute_pickup_provider:hover ~ #enroute_pickup_slide {
            opacity: 1;
            background-color: #be2020;
            width: 100%;
            height: 4px;
            position: absolute;
            -webkit-transition: left 0.3s ease-out;
            transition: all 0.3s ease-out;
        }

        /*provider-head-reache*/
        .provider-head-reache {
            color: #fff;
            background-color: #337ab7;
        }

        .md-tabs .nav-item a#reached_pickup_provider:active,
        .md-tabs .nav-item a#reached_pickup_provider:focus,
        .md-tabs .nav-item a#reached_pickup_provider:hover {
            color: #337ab7;
        }

        .md-tabs .nav-item a#reached_pickup_provider.active ~ #reached_pickup_slide,
        .md-tabs .nav-item a#reached_pickup_provider:active ~ #reached_pickup_slide,
        .md-tabs .nav-item a#reached_pickup_provider:focus ~ #reached_pickup_slide,
        .md-tabs .nav-item a#reached_pickup_provider:hover ~ #reached_pickup_slide {
            opacity: 1;
            background-color: #337ab7;
            width: 100%;
            height: 4px;
            position: absolute;
            -webkit-transition: left 0.3s ease-out;
            transition: all 0.3s ease-out;
        }

        /*ride start provider*/
        .provider-head-start {
            color: #fff;
            background-color: #263543;
        }

        .md-tabs .nav-item a#ride_start_provider:active,
        .md-tabs .nav-item a#ride_start_provider:focus,
        .md-tabs .nav-item a#ride_start_provider:hover {
            color: #263543;
        }

        .md-tabs .nav-item a#ride_start_provider.active ~ #ride_start_slide,
        .md-tabs .nav-item a#ride_start_provider:active ~ #ride_start_slide,
        .md-tabs .nav-item a#ride_start_provider:focus ~ #ride_start_slide,
        .md-tabs .nav-item a#ride_start_provider:hover ~ #ride_start_slide {
            opacity: 1;
            background-color: #263543;
            width: 100%;
            height: 4px;
            position: absolute;
            -webkit-transition: left 0.3s ease-out;
            transition: all 0.3s ease-out;
        }

        /*.provider available*/
        .provider-head-available {
            color: #fff;
            background-color: #246a24;
        }

        .md-tabs .nav-item a#available_provider:active,
        .md-tabs .nav-item a#available_provider:focus,
        .md-tabs .nav-item a#available_provider:hover {
            color: #246a24;
        }

        .md-tabs .nav-item a#available_provider.active ~ #available_slide,
        .md-tabs .nav-item a#available_provider:active ~ #available_slide,
        .md-tabs .nav-item a#available_provider:focus ~ #available_slide,
        .md-tabs .nav-item a#available_provider:hover ~ #available_slide {
            opacity: 1;
            background-color: #246a24;
            width: 100%;
            height: 4px;
            position: absolute;
            -webkit-transition: left 0.3s ease-out;
            transition: all 0.3s ease-out;
        }

        /*all provider*/
        .provider-head-all {
            color: #fff;
            background-color: #046b68;
        }

        .md-tabs .nav-item a#all_provider {
            color: #046b68;
        }

        .md-tabs .nav-item a#all_provider.active ~ #all_slide,
        .md-tabs .nav-item a#all_provider:active ~ #all_slide,
        .md-tabs .nav-item a#all_provider:focus ~ #all_slide,
        .md-tabs .nav-item a#all_provider:hover ~ #all_slide {
            opacity: 1;
            background-color: #046b68;
            width: 100%;
            height: 4px;
            position: absolute;
            -webkit-transition: left 0.3s ease-out;
            transition: all 0.3s ease-out;
        }

        .assign-provider {
            width: 100%;
            height: 330px;
            overflow-y: auto;
        }

        /* scroll width */
        .assign-provider::-webkit-scrollbar {
            width: 5px;
            border-radius: 50px;
        }

        /* scroll Track */
        .assign-provider::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 50px;
        }

        /* scroll Handle */
        .assign-provider::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 50px;
        }

        /* scroll Handle on hover */
        .assign-provider::-webkit-scrollbar-thumb:hover {
            background: #555;
            border-radius: 50px;
        }

        .provider-details {
            width: 70%;
            position: absolute;
            z-index: 100;
            border: 1px solid lightgray;
            background-color: #fff;
            left: 20px;
            top: 5px;
            border-top-left-radius: 5px;
        }

        .provider-body {
            /*width: 25%;*/
            /*display: table-cell;*/
            /*vertical-align: top;*/
            /*overflow: hidden;*/
            /*zoom: 1;*/

            display: inline-block;
            width: 50%;
        }

        .provider-ride {
            display: block;
            padding: 5px 0px;
        }

        .provider-details-head {
            background-color: lightgray;
            padding: 1px 10px;
        }

        .provider-details-content {
            padding: 5px 10px;
            font-size: 15px;
        }

        .provider-content {
            padding: 3px 0;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
        @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <!-- [ other service horizontal navbar ] start -->
                <div class="other-service-horizontal-nav">
                    @include('admin.include.transport-horizontal-navbar')
                </div>
                <!-- [ other service horizontal navbar ] end -->
        @endif
    @endif

    <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5> Drivers Location Track On Map</h5>
                            <span>All Drivers Location Track On Map @if(isset($service_category) && $service_category->name != Null)
                                    of {{ ucwords(strtolower($service_category->name)) }} @endif</span>
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

                        <div class="row">
                            <div class="col-md-12">
                                <div class="p-b-20">
                                    <ul class="nav nav-tabs md-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab"
                                               id="all_provider"
                                               href="#all" role="tab">All</a>
                                            <div id="all_slide" class="slide"></div>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab"
                                               id="available_provider"
                                               href="#available" role="tab">Available</a>
                                            <div id="available_slide" class="slide"></div>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab"
                                               id="ride_start_provider"
                                               href="#ride_start" role="tab">Ride Start</a>
                                            <div id="ride_start_slide" class="slide"></div>
                                        </li>


                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#reached_pickup"
                                               id="reached_pickup_provider"
                                               role="tab">Reached Pickup</a>
                                            <div id="reached_pickup_slide" class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#enroute_pickup"
                                               id="enroute_pickup_provider"
                                               role="tab">Enroute to Pickup</a>
                                            <div id="enroute_pickup_slide" class="slide"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 tab-panel">
                                <div class="card">
                                    <div class="card-header">
                                        <div id="provider-head" class="provider-head provider-head-all">
                                            <h4>All</h4>
                                        </div>
                                        <div style="padding: 12px; background-color: #fff; border-bottom: 1px solid #eeeeee;">
                                            <input type="search" name="all" id="search-provider"
                                                   class="form-control" placeholder="Search Driver">

                                        </div>
                                        {{--<h5> Drivers List</h5>--}}
                                    </div>
                                    <div class="card-block tab-content p-t-20 assign-provider">
                                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                                            @if(isset($providers))
                                                @foreach($providers as  $key=>$provider)
                                                    <div class="generic-card-body">
                                                        <div class="media" id="media-id">
                                                            <div class="media-left">
                                                                <img src="{{ ($provider['image'] != Null) ? asset('assets/images/profile-images/provider/'.$provider['image']) : asset('assets/images/website-logo-icon/user.png') }}"
                                                                     class="media-object" style="width:50px">
                                                            </div>
                                                            <div class="media-body">
                                                                <h5 class="media-heading">{{ ucwords($provider['name']." ".$provider['last_name']) }}</h5>
                                                                <p class="provider-phone">{{ $provider['phone'] }}</p>
                                                                <p class="rider-name" style="display: none;"></p>
                                                                <p class="rider-phone" style="display: none;"></p>
                                                                <p class="ride-pickup-address"
                                                                   style="display: none;"></p>
                                                                <p class="ride-dropped-address"
                                                                   style="display: none;"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                        </div>
                                        <div class="tab-pane fade" id="available" role="tabpanel"></div>
                                        <div class="tab-pane fade" id="ride_start" role="tabpanel"></div>
                                        <div class="tab-pane fade" id="reached_pickup" role="tabpanel"></div>
                                        <div class="tab-pane fade" id="enroute_pickup" role="tabpanel"></div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">

                                <div class="card">
                                    <div class="card-block">

                                        <div id="provider-details" class="provider-details" style="display: none;">
                                            <div class="provider-details-head">
                                                <h4>Driver Details</h4>
                                            </div>
                                            <div class="provider-details-content">
                                                <div class="provider-content">
                                                    <div class="provider-body">
                                                        <strong>Driver </strong> :
                                                    </div>
                                                    <div class="provider-body" id="provider_name">
                                                        John
                                                    </div>
                                                </div>
                                                <div class="provider-content">
                                                    <div class="provider-body">
                                                        <strong>Phone </strong> :
                                                    </div>
                                                    <div class="provider-body" id="provider_phone">
                                                        John
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="provider-details-head">
                                                <h4>Rider Details</h4>
                                            </div>
                                            <div class="provider-details-content"
                                                 style="padding-bottom: 20px; border-bottom: 1px solid lightgray;">
                                                <div class="provider-content">
                                                    <div class="provider-body">
                                                        <strong>Driver </strong> :
                                                    </div>
                                                    <div class="provider-body" id="user_name">
                                                        John
                                                    </div>
                                                </div>
                                                <div class="provider-content">
                                                    <div class="provider-body">
                                                        <strong>Phone </strong> :
                                                    </div>
                                                    <div class="provider-body" id="user_name_phone">
                                                        John
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="padding: 8px 10px;">
                                                <div class="provider-ride">
                                                    <i class="fa fa-map-marker text-success"
                                                       style="font-size: 22px; padding-right: 10px;"></i> <span>Asklipiou 28, Athina 106 80, Greece</span>
                                                </div>
                                                <div class="provider-ride">
                                                    <i class="fa fa-map-marker text-success"
                                                       style="font-size: 22px; padding-right: 10px;"></i> <span>Asklipiou 28, Athina 106 80, Greece</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="map" style="height: 400px; width: 100%; position: relative;">
                                        </div>

                                    </div>
                                </div>


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


    <script type="text/javascript">
        var temp = [];
        $(".media").click(ProviderClick);

        $("#search-provider").on('keyup', function (e) {
            // var search_provider = e.target.value;
            var search_provider = e.target.value;
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            var driver_current_status = $("#search-provider").attr("name");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            temp = [];

            $.ajax({
                url: '{{ route("get:admin:transport_search_provider_on_map") }}',
                type: "get",
                data: {
                    provider: search_provider,
                    service_cat_id: service_cat_id,
                    driver_current_status: driver_current_status
                },
                success: function (data) {
                    if (driver_current_status == "all" || driver_current_status == "available") {
                        var status = driver_current_status;
                        AjaxData(data, status);
                        initMap(temp);
                        $(".media").click(ProviderClick);
                    } else {
                        var status = driver_current_status;
                        AjaxRideData(data, status);
                        initMap(temp);
                        $(".media").click(Rideclick);
                    }
                },
            });
        });

        // All provider ajax
        $("#all_provider").click(function (e) {
            var all_provider = "";
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            $("#search-provider").attr("name", "all");
            $("#search-provider").val("");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            $("#provider-head").removeClass();
            $("#provider-head").addClass("provider-head").addClass("provider-head-all");
            $("#provider-head h4").text("All");
            temp = [];
            $.ajax({
                url: '{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1) ? route("get:admin:transport_all_provider_location") : route("get:dispatcher:transport_all_provider_location") }}',
                type: "get",
                data: {provider: all_provider, service_cat_id: service_cat_id},
                success: function (data) {
                    var status = 'all';
                    AjaxData(data, status);
                    initMap(temp);
                    $(".media").click(ProviderClick);
                },
            });
        });

        // Avialable provider ajax
        $("#available_provider").click(function (e) {
            var available_provider = 3;
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            $("#search-provider").attr("name", "available");
            $("#search-provider").val("");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            $("#provider-head").removeClass();
            $("#provider-head").addClass("provider-head").addClass("provider-head-available");
            $("#provider-head h4").text("Available");
            temp = [];
            $.ajax({
                url: '{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1) ? route("get:admin:transport_available_provider_location") : route("get:dispatcher:transport_available_provider_location") }}',
                type: "get",
                data: {provider: available_provider, service_cat_id: service_cat_id},
                success: function (data) {
                    var status = 'available';
                    AjaxData(data, status);
                    initMap(temp);
                    $(".media").click(ProviderClick);
                },
            });
        });

        // Start Ride provider ajax
        $("#ride_start_provider").click(function (e) {
            var ride_start_provider = 2;
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            $("#search-provider").attr("name", "ride_start");
            $("#search-provider").val("");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            $("#provider-head").removeClass();
            $("#provider-head").addClass("provider-head").addClass("provider-head-start");
            $("#provider-head h4").text("Ride Started");
            temp = [];
            $.ajax({
                url: '{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1) ? route("get:admin:transport_ride_start_provider_location") : route("get:dispatcher:transport_ride_start_provider_location") }}',
                type: "get",
                data: {provider: ride_start_provider, service_cat_id: service_cat_id},
                success: function (data) {
                    var status = 'ride_start';
                    AjaxRideData(data, status);
                    initMap(temp);
                    $(".media").click(Rideclick);
                },
            });
        });

        // Reached Pickup Provider provider ajax
        $("#reached_pickup_provider").click(function (e) {
            var reached_pickup_provider = 1;
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            $("#search-provider").attr("name", "reached_pickup");
            $("#search-provider").val("");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            $("#provider-head").removeClass();
            $("#provider-head").addClass("provider-head").addClass("provider-head-reache");
            $("#provider-head h4").text("Reached Pickup");
            temp = [];
            $.ajax({
                url: '{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1) ? route("get:admin:transport_ride_reached_provider_location") : route("get:dispatcher:transport_ride_reached_provider_location") }}',
                type: "get",
                data: {provider: reached_pickup_provider, service_cat_id: service_cat_id},
                success: function (data) {
                    var status = 'reached_pickup';
                    AjaxRideData(data, status);
                    initMap(temp);
                    $(".media").click(Rideclick);
                },
            });
        });

        // Enroute Pickup Ride provider ajax
        $("#enroute_pickup_provider").click(function (e) {
            var enroute_pickup_provider = 0;
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            $("#search-provider").attr("name", "enroute_pickup");
            $("#search-provider").val("");
            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
            $("#provider-head").removeClass();
            $("#provider-head").addClass("provider-head").addClass("provider-head-enroute");
            $("#provider-head h4").text("Enroute To Pickup");
            temp = [];
            $.ajax({
                url: '{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1) ? route("get:admin:transport_ride_enroute_provider_location") : route("get:dispatcher:transport_ride_enroute_provider_location") }}',
                type: "get",
                data: {provider: enroute_pickup_provider, service_cat_id: service_cat_id},
                success: function (data) {
                    var status = 'enroute_pickup';
                    AjaxRideData(data, status);
                    initMap(temp);
                    $(".media").click(Rideclick);
                },
            });
        });

        //mouse up click
        $(document).mouseup(function (e) {
            var container = $(".tab-content");
            var container1 = $("#provider-details");
            if (!container.is(e.target) && container.has(e.target).length === 0 && !container1.is(e.target) && container1.has(e.target).length === 0) {
                $("#provider-details").hide();
            }
        });

        function AjaxData(data, status) {
            var id_value = status;
            if (data == "") {
                document.getElementById(id_value).innerHTML = "<p style='color: red; margin: 10px 16px;'>Driver Not Found !</p>";
            } else {
                var html = document.getElementById(id_value).innerHTML = '';
                var k = 0;
                $.each(data, function (index, subcatObj) {
                    var last_name = "";
                    if (subcatObj.last_name != null) {
                        last_name = subcatObj.last_name;
                    }
                    temp[index] = [subcatObj.address, subcatObj.latitude, subcatObj.longitude, subcatObj.name, '' + subcatObj.ride_status + '', subcatObj.phone, last_name, subcatObj.service_cat_id];
                    var image_name = subcatObj.image;
                    var image_path = "{{ asset('assets/images/profile-images/provider/') }}";
                    if (image_name == null) {
                        var image_name = "{{ asset('assets/images/website-logo-icon/user.png') }}";
                    } else {
                        var image_name = image_path + "/" + image_name;
                    }
                    html += '<div class="generic-card-body"><div class="media" id="media-{{ (isset($providers) && $providers != Null)? $key : '' }}">\n' +
                        '<div class="media-left">\n' +
                        '<img  src="' + image_name + '" class="media-object" style="width:60px">\n' +
                        '</div>\n' +
                        '<div class="media-body">\n' +
                        '<h5 class="media-heading">' + subcatObj.name + " " + last_name + '</h5>\n' +
                        '<p class="provider-phone">' + subcatObj.phone + '</p>\n' +
                        '</div>\n' +
                        '</div></div>';
                    // $('#all').append(html);
                    document.getElementById(id_value).innerHTML = html;
                    k++;
                });
            }
        }

        function AjaxRideData(data, status) {
            var id_value = status;
            if (data == "") {
                document.getElementById(id_value).innerHTML = "<p style='color: red; margin: 10px 16px;'>Driver Not Found !</p>";
            } else {
                var html = document.getElementById(id_value).innerHTML = '';
                var k = 0;
                $.each(data, function (index, subcatObj) {
                    var last_name = "";
                    if (subcatObj.last_name != null) {
                        last_name = subcatObj.last_name;
                    }
                    var provider_name = subcatObj.name + ' ' + last_name;
                    var user_last_name = "";
                    if (subcatObj.user_last_name != null) {
                        user_last_name = subcatObj.user_last_name;
                    }
                    var rider = subcatObj.user_name;
                    if (rider == null) {
                        var rider_name = "";
                    } else {
                        var rider_name = subcatObj.user_name + " " + user_last_name;
                    }


                    temp[index] = [subcatObj.address, subcatObj.latitude, subcatObj.longitude, subcatObj.name, '' + subcatObj.ride_status + '', subcatObj.phone, last_name, subcatObj.service_cat_id];
                    var image_name = subcatObj.image;
                    var image_path = "{{ asset('assets/images/profile-images/provider/') }}";
                    if (image_name == null) {
                        var image_name = "{{ asset('assets/images/website-logo-icon/user.png') }}";
                    } else {
                        var image_name = image_path + "/" + image_name;
                    }
                    var contact = subcatObj.rider_phone;
                    if (contact == null) {
                        var rider_t = "";
                    } else {
                        var rider_t = contact;
                    }
                    html += '<div class="generic-card-body"><div class="media"  id="media-' + index + '">' +
                        '<div class="media-left">' +
                        '<img  src="' + image_name + '" class="media-object" style="width:60px">' +
                        '</div>' +
                        '<div class="media-body">' +
                        '<h5 class="media-heading">' + provider_name + '</h5>' +
                        '<p class="provider-phone">' + subcatObj.phone + '</p>' +
                        '<p class="rider-name" style="display: none;">' + rider_name + '</p>' +
                        '<p class="rider-phone" style="display: none;">' + rider_t + '</p>' +
                        '<p class="ride-pickup-address" style="display: none;">' + subcatObj.pickup_address + '</p>' +
                        '<p class="ride-dropped-address" style="display: none;">' + subcatObj.destination_address + '</p>' +
                        '</div>' +
                        '</div></div>';
                    document.getElementById(id_value).innerHTML = html;
                    k++;
                });
            }
        }

        //provider with rider details click
        function Rideclick() {
            var id_name = ($(this).attr('id'));
            if (!$('#provider-details').hasClass(id_name)) {
                $('#provider-details').addClass(id_name);
                $("#provider-details").hide();
                var provider_name = $('h5.media-heading', this).text();
                var provider_phone = $('p.provider-phone', this).text();
                var rider_name = $('p.rider-name', this).text();
                var rider_phone = $('p.rider-phone', this).text();
                var ride_pickup_address = $('p.ride-pickup-address', this).text();
                var ride_dropped_address = $('p.ride-dropped-address', this).text();
                $('#provider-details').empty();
                $('#provider-details').append('<div class="provider-details-head"><h4>Driver Details</h4></div><div class="provider-details-content"><div class="provider-content"><div class="provider-body"><strong>Driver </strong> : </div><div class="provider-body" id="provider_name">' + provider_name + '</div></div><div class="provider-content"><div class="provider-body"><strong>Phone </strong> : </div><div class="provider-body" id="provider_name">' + provider_phone + '</div></div></div><div class="provider-details-head"><h4>Rider Details</h4></div><div class="provider-details-content" style="padding-bottom: 20px; border-bottom: 1px solid lightgray;"><div class="provider-content"><div class="provider-body"><strong>Rider Name </strong> : </div><div class="provider-body" id="provider_name">' + rider_name + '</div></div><div class="provider-content"><div class="provider-body"><strong>Phone </strong> : </div><div class="provider-body" id="provider_name"> ' + rider_phone + ' </div></div></div><div style="padding: 8px 10px;"><div class="provider-ride"><i class="fa fa-map-marker text-success" style="font-size: 22px; padding-right: 10px;"></i> <span> ' + ride_pickup_address + ' </span></div><div class="provider-ride"><i class="fa fa-map-marker text-success" style="font-size: 22px; padding-right: 10px;"></i> <span> ' + ride_dropped_address + ' </span></div></div>');
                $("#provider-details").slideToggle("slow");
            } else {
                $("#provider-details").hide();
                $('#provider-details').removeClass(id_name);
            }
        }

        //all and provider details click
        function ProviderClick() {
            var id_name = ($(this).attr('id'));
            if (!$('#provider-details').hasClass(id_name)) {
                $('#provider-details').addClass(id_name);
                $("#provider-details").hide();
                var provider_name = $('h5.media-heading', this).text();
                var provider_phone = $('p.provider-phone', this).text();
                $('#provider-details').empty();
                $('#provider-details').append('<div class="provider-details-head"><h4>Driver Details</h4></div><div class="provider-details-content"><div class="provider-content"><div class="provider-body"><strong>Driver </strong> : </div><div class="provider-body" id="provider_name">' + provider_name + ' </div></div><div class="provider-content"><div class="provider-body"><strong>Phone </strong> : </div><div class="provider-body" id="provider_name">' + provider_phone + '</div></div></div>');
                $("#provider-details").slideToggle("slow");
            } else {
                $("#provider-details").hide();
                $('#provider-details').removeClass(id_name);
            }
        }
    </script>
    <script type="text/javascript">
        var temp = [];
        var map;
        var markers = [];
        var i;
        var icon;
        var bike_icon;

        function initMap(temp) {
            if (typeof temp !== 'undefined' && temp.length > 0) {
                var locations = temp;
            } else {
                if (temp) {
                    var locations = [];
                } else {
                    var locations = [
                            @if(isset($providers))
                            @foreach($providers as  $key=>$provider)
                        ['{{ $provider['address'] }}', {{ $provider['latitude'] }}, {{ $provider['longitude'] }}, '{{ $provider['name'] }}', '{{ $provider['ride_status'] }}', '{{ $provider['phone'] }}', '{{ $provider['last_name'] }}', '{{ $provider['service_cat_id'] }}'],
                        @endforeach
                        @endif
                    ];

                }
            }
            var mapOptions = {
                center: new google.maps.LatLng(22.3039, 70.8022),
                zoom: 10
            };
            map = new google.maps.Map(document.getElementById("map"),
                mapOptions);
            var icon;
            var bike_icon;
            for (var i = 0; i < locations.length; i++) {
                switch (locations[i][4]) {
                    case "0" :
                        icon = "{{ asset('assets/images/map-images/red.png') }}";
                        bike_icon = "{{ asset('assets/images/map-images/bike-red.png') }}";
                        break;
                    case "1" :
                        icon = "{{ asset('assets/images/map-images/blue.png') }}";
                        bike_icon = "{{ asset('assets/images/map-images/bike-blue.png') }}";
                        break;
                    case "2" :
                        icon = "{{ asset('assets/images/map-images/dark.png') }}";
                        bike_icon = "{{ asset('assets/images/map-images/bike-dark.png') }}";
                        break;
                    case "3" :
                        icon = "{{ asset('assets/images/map-images/green.png') }}";
                        bike_icon = "{{ asset('assets/images/map-images/bike-green.png') }}";
                        break;
                    default :
                        icon = "{{ asset('assets/images/map-images/yellow.png') }}";
                        bike_icon = "{{ asset('assets/images/map-images/bike-yellow.png') }}";
                        break;
                }
                var latlng = new google.maps.LatLng(locations[i][1], locations[i][2]);
                var marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    title: locations[i][3] + ' ' + locations[i][6],
                    icon: (locations[i][7] == 1) ? bike_icon : icon,
                });
                google.maps.event.addListener(map, 'load', function (event) {
                    var result = [event.latlng.lat(), event.latlng.lng()];
                    transition(result);
                });
                markers.push(marker);
            }
        }

        // google.maps.event.addDomListener(window, 'load', initMap(temp));

        //Ajax call to get position
        function set_center() {
            var current_status = $("#search-provider").attr("name");
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";
            var feedback = $.ajax({
                type: 'GET',
                url: "{{ route('get:admin:transport_location_on_map') }}",
                data: {current_status: current_status, service_cat_id: service_cat_id},
                success: function (data) {
                    if (data != null) {
                        var map_icon;
                        var bike_icon;
                        DeleteMarkers();
                        $.each(data, function (i, item) {
                            if (item != null) {
                                if (item['longitude'] != null && item['latitude'] != null) {
                                    if (item['ride_status'] == 0) {
                                        map_icon = "{{ asset('assets/images/map-images/red.png') }}";
                                        bike_icon = "{{ asset('assets/images/map-images/bike-red.png') }}";
                                    } else if (item['ride_status'] == 1) {
                                        map_icon = "{{ asset('assets/images/map-images/blue.png') }}";
                                        bike_icon = "{{ asset('assets/images/map-images/bike-blue.png') }}";
                                    } else if (item['ride_status'] == 2) {
                                        map_icon = "{{ asset('assets/images/map-images/dark.png') }}";
                                        bike_icon = "{{ asset('assets/images/map-images/bike-dark.png') }}";
                                    } else if (item['ride_status'] == 3) {
                                        map_icon = "{{ asset('assets/images/map-images/green.png') }}";
                                        bike_icon = "{{ asset('assets/images/map-images/bike-green.png') }}";
                                    } else {
                                        map_icon = "{{ asset('assets/images/map-images/yellow.png') }}";
                                        bike_icon = "{{ asset('assets/images/map-images/bike-yellow.png') }}";
                                    }
                                    var latlng = new google.maps.LatLng(item['latitude'], item['longitude']);
                                    var marker = new google.maps.Marker({
                                        position: latlng,
                                        map: map,
                                        title: item['name'] + " " + item['last_name'],
                                        icon: (item['service_cat_id'] == 1) ? bike_icon : map_icon,
                                    });
                                    google.maps.event.addListener(map, 'load', function (event) {
                                        var result = [event.latlng.lat(), event.latlng.lng()];
                                        transition(result);
                                    });
                                    markers.push(marker);
                                }
                            }
                        });
                        var click_area = $("#search-provider").attr("name");
                        if (click_area == "all" || click_area == "available") {
                            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
                            var status = click_area;
                            AjaxData(data, status);
                            $(".media").click(ProviderClick);
                        } else {
                            $('#all, #available ,#ride_start ,#reached_pickup, #enroute_pickup').empty();
                            var status = click_area;
                            AjaxRideData(data, status);
                            $(".media").click(Rideclick);
                        }
                    }
                    ;
                },
                error: function (data) {
                    $("#result").html(data);
                }
            });
        }

        function DeleteMarkers() {
            //Loop through all the markers and remove
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers = [];
        };
        // setInterval(set_center, 10000);

    </script>
    @php $map_key = \App\model\GeneralSettings::first() @endphp
    <script src="//maps.googleapis.com/maps/api/js?key={{ isset($map_key)? ($map_key->map_key != Null)? $map_key->map_key : 0 : 0 }}&libraries=places&callback=initMap"
            async defer></script>
@endsection

