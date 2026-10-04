@extends('front.layout.default')
@section('title')
    <title>Ride Booking</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Ride Services">
    <meta name="keywords" content="Ride Services">
@endsection
@section('page-css')

<style>

</style>

@endsection

@section('page-content')

<main id="main">

    <div class="container-fluid minheights mainSection">

        <div class="row">
            <div  class="col-lg-4 col-md-4 col-sm-12 col-xs-12 order-md-1 order-12 mb-5 contentPart minheights" id="style-3" >
                <div id="cover-spin-new" >
                </div>
                <div id="cover-spin">
                    <a class="boxclose cancelRide" data-toggle="modal" data-target="#exampleModal" id="boxclose" title="Cancel"></a>
                </div>
                <div id="retry-spin">
                    <a href="javascript:void(0)" class="boxclose1 retryRide" title="Retry"></a>
                    <a class="boxclose cancelRide" data-toggle="modal" data-target="#exampleModal" id="boxclose" title="Cancel"></a>
                </div>


                <div class="flex-column flex-nowrap vh-100 overflow-auto rightPartSection">
                    <div class="row">
                        <div class="col-12">
                            <div class="padding-10">
                                <div>
                                   <a href="{{ route('get:homepage') }}"> <i class="fas fa-angle-left"></i></a> <span class="ride-service-title">Ride Booking</span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <form name="rideFrm" class="rideFrm" id="rideFrm" method="post" href="#">
                        <input type="hidden" name="service_type" value="{{$service}}">
                        <input type="hidden" name="ride_id" id="ride_id" value="0">
                        <div class="row service-select-section">
                            <div class="col-md-12">
                                <div class="padding-10">
                                    <div class="text-center row">
                                        <div class="col ">
                                            <a href="{{ route('get:transport-service-booking',["bike-ride"]) }}" class="service_type ">
                                                <label class="btn btn-outline-success btn-block {{ (isset($service) && $service == "bike-ride" )?"active":""  }}" for="success-outlined">Bike Ride</label>
                                            </a>
                                        </div>
                                        <div class="col pl-0">
                                            <a href="{{ route('get:transport-service-booking',["taxi-ride"]) }}" class="service_type ">
                                                <label class="btn btn-outline-success btn-block {{ (isset($service) && $service == "taxi-ride" )?"active":""  }}" for="success-outlined1">Car Ride</label>
                                            </a>
                                        </div>

            <!--                        <div class="col pl-0">
                                            <input type="radio" class="btn-check" name="options-outlined" id="success-outlined2" autocomplete="off" >
                                            <label class="btn btn-outline-success" for="success-outlined2">Bike Rental</label>
                                        </div>
                                        <div class="col pl-0">
                                            <input type="radio" class="btn-check" name="options-outlined" id="success-outlined3" autocomplete="off" >
                                            <label class="btn btn-outline-success" for="success-outlined3">Taxi Rental</label>
                                        </div>-->

                                        <div class="col pl-0">
                                            <a href="{{ route('get:transport-service-booking',["courier-service"]) }}" class="service_type " >
                                                <label class="btn btn-outline-success btn-block {{ (isset($service) && $service == "courier-service" )?"active":""  }}" for="success-outlined4">Courier </label>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="padding-10">
                                    <div class="text-center">
                                        <div class="pickup-drop-location">
                                            <div class="form-group pickup">
                                                <input type="text" name="source_address" class="source_address" id="source_address" value="{{ ($current_address!="")?$current_address:"" }}" placeholder="Pick up location" required="" >
                                                <input type="hidden" name="source_lat" id="source_lat" class="source_lat" value="0" >
                                                <input type="hidden" name="source_long" id="source_long" class="source_long" value="0" >
{{--                                                <span class="curr_loc"> <span>Current Location</span>  <span class="material-icons getLocationicon">my_location</span></span>--}}
                                            </div>

                                            <div class="form-group dest">
                                                <input type="text" name="dest_address" id="dest_address" class="dest_address" value="" placeholder="Drop off location" required="" >
                                                <input type="hidden" name="dest_lat" id="dest_lat" class="dest_lat" value="0" >
                                                <input type="hidden" name="dest_long" id="dest_long" class="dest_long" value="0" >
                                            </div>
                                            <input type="hidden" name="eta_distance" id="eta_distance" class="eta_distance" value="0" >
                                            <input type="hidden" name="eta_distance_txt" id="eta_distance_txt" class="eta_distance_txt" value="" >
                                            <input type="hidden" name="eta_time" id="eta_time" class="eta_time" value="0" >
                                            <input type="hidden" name="eta_time_txt" id="eta_time_txt" class="eta_time_txt" value="" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="padding-10">
                                    <div class="ride-inner-title">
                                        <span >Available Rides</span>
                                    </div>
                                </div>
                                <div class="borderCls">
                                    <div class="vehicle_type">
                                        <div class="col-12 p-0">

                                            @if(isset($get_vehicle_lists))

                                                @foreach($get_vehicle_lists as $key=>$single_vehicle)
                                                    <div class=" form-font-cls  borderBtmCls">
                                                        <input type="radio" id="{{ $single_vehicle->id }}" name="vehicle_type" class="service_vehicle" value="{{ $single_vehicle->id }}" {{ ($key==0)?"checked":"" }}>
                                                        <label for="{{ $single_vehicle->id }}" class="m-0">
                                                            <span class="helper">
<!--                                                                <img src="http://localhost/php/order-angel/public/assets/images/service-category/transport-service-type/{{$single_vehicle->icon_name}}" />-->

                                                                @if(file_exists(public_path('assets/images/service-category/transport-service-type/'.$single_vehicle->icon_name)))
                                                                    <img src="{{ asset('assets/images/service-category/transport-service-type/'.$single_vehicle->icon_name) }}">
                                                                @else
                                                                    <img src="#">
                                                                @endif
                                                            </span> {{ $single_vehicle->name }}
                                                        </label>
                                                        <label class="float-right pr-3 pt-1">
                                                            <span class="currency">{{ $single_vehicle->cost_for_km }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach

                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="padding-10">
                                    <div class="ride-inner-title">
                                        <span >Payment Types</span>
                                    </div>
                                </div>
                                <div class="borderCls">
                                    <div class="input-group p-1">
                                        <select name="payment_type" class="custom-select border-0">
                                            <option value="1">Cash</option>
                                            <option value="2">Card</option>
                                            <option value="3">Wallet</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
{{--                            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">--}}
{{--                                <div class="padding-10">--}}
{{--                                    <div class="ride-inner-title">--}}
{{--                                        <span >Apply Promocode</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="borderCls">--}}
{{--                                    <div class="input-group p-1">--}}
{{--                                        <input type="text" name="promocode" class="form-control border-0" placeholder="Promocode" aria-label="Promocode" aria-describedby="basic-addon2">--}}
{{--                                        <div class="input-group-append">--}}
{{--                                            <button class="btn btn-outline-secondary btnColor" type="button">APPLY</button>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>

                        @if(isset($service) && $service == "taxi-ride")
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="padding-10">
                                        <div class="ride-inner-title">
                                            <span >More Options</span>
                                        </div>
                                    </div>
                                    <div class="checkboxSection">
                                        <div class=" custom-checkbox form-check form-check-inline">
                                            <input class="form-check-input custom-control-input" name="handicap" type="checkbox" id="handicap" value="y" >
                                            <label class="form-check-label custom-control-label pl-3" for="handicap">Handicap</label>
                                        </div>
                                        <div class=" custom-checkbox form-check form-check-inline">
                                            <input class="form-check-input custom-control-input" name="childseat" type="checkbox" id="childseat" value="y">
                                            <label class="form-check-label custom-control-label pl-3" for="childseat">Child Seat</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(isset($service) && ($service == "taxi-ride" || $service == "bike-ride"  ))
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="padding-10">
                                        <div class="ride-inner-title">
                                            <span >Ride Book Type</span>
                                        </div>
                                    </div>
                                    <div class="checkboxSection">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input rideTypeCls" id="ride_now" name="ride_type" value="0" checked>
                                            <label class="custom-control-label" for="ride_now">Ride Now</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input rideTypeCls" id="schedule_ride" name="ride_type" value="1">
                                            <label class="custom-control-label" for="schedule_ride">Schedule Ride</label>
                                        </div>
                                    </div>
                                    <div class="checkboxSection padding-10 rideNowSection" style="display: none;">
                                        <div class="input-append date form_datetime">
                                            <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                                                <input type="text" name="pickup_date_time" id="datepicker" class="form-control form-font-cls datetimepicker-input" data-target="#datetimepicker1" autocomplete="off"  required/>
                                                <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        <div class="row fare_data">
                            <div class="col-12">
                                <div class="padding-10">
                                </div>
                                <div class="faremainSetion pt-3">
                                    <div class="col-12 fareLabel">
                                            <label class="labelFld">Distance Travel</label>
                                            <label class="labelvalue">0 Km</label>

                                    </div>
                                    <div class="col-12 fareLabel">
                                            <label class="labelFld">Taken Time</label>
                                            <label class="labelvalue">0 Min</label>

                                    </div>
                                    <div class="col-12 fareLabel borderBtmCls">
                                            <label class="labelFld">Base Fare</label>
                                            <label class="labelvalue currency">0</label>
                                    </div>
                                    <div class="col-12 fareLabel pt-2">
                                        <label class="labelFld">Sub Total</label>
                                        <label class="labelvalue currency">0</label>

                                    </div>
                                    <div class="col-12 fareLabel borderBtmCls">
                                        <label class="labelFld">Tax</label>
                                        <label class="labelvalue currency">0</label>
                                    </div>
                                    <div class="col-12 fareLabel  font-weight-bold">
                                        <label class="labelFld">Total</label>
                                        <label class="labelvalue currency">0</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
<!--                            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                <div class="padding-10">
                                </div>
                                <div class="col">
                                    <button class="btn btn-outline-secondary btnColor text-uppercase btn-block booknowbtn " type="button">Schedule Later</button>
                                </div>

                            </div>-->
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                 <div class="padding-10">
                                 </div>
                                 <div class="col">
                                    <button class="btn btn-outline-secondary btnColor text-uppercase btn-block booknowbtn rideNowBtn"  type="button">Ride Now</button>
                                 </div>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 order-md-12 order-1 p-0 mapPart">
                <div class="map minheights" id="map" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cancel Ride</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    are you sure to cancel Ride ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary btnColor cancelRideBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

</main>

@endsection

@section('page-js')


<script type="text/javascript">
    if (window.location.hash && window.location.hash == '#_=_') {
        window.location = '';
    }
</script>
<script>
    var modal = document.getElementById('user');

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
</script>

<script>
    //new js for ride booking
    $(document).ready(function (){


        //map height set at run time
        var contentPart = $(".mainSection").height()

        $("#map").height(contentPart);

        $('.user').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true   // 100% fit in a container
        });

        //ride type radio check with display datepicker
        $(document).on("change",".rideTypeCls",function (){
                var ridetype = $(this).val();
                if(ridetype == 1){
                    $(".rideNowSection").css('display','block');
                }else{
                    $(".rideNowSection").css('display','none');
                }

        });

        // $(".booknowbtn").addClass('disabled');
        $(".booknowbtn").attr("disabled", true);
        $(document).on("change","#dest_address",function (){
            if($("#dest_address").val() != ""){
                // $(".booknowbtn").removeClass('disabled');
                $(".booknowbtn").attr("disabled", false);


                    // google.maps.event.trigger(document.getElementById('source_address'), 'focus', {});
                    $("#source_address").focus();
                    // $(".pac-container:first .pac-item:first").trigger("click");

                    // $(".pac-container").css("visibility","hidden");

                    // google.maps.event.trigger(document.getElementById('source_address'),'keydown',{keyCode:40})
                        /*if ($('.pac-container .pac-item').length > 0) {
                            google.maps.event.trigger(document.getElementById('source_address'), 'keydown', {
                                keyCode : 40
                            });
                            google.maps.event.trigger(document.getElementById('source_address'), 'keydown', {
                                keyCode : 13,triggered:true
                            });
                        }*/


                /*google.maps.event.trigger(document.getElementById('source_address'), 'keydown', {
                    keyCode : 13,triggered:true
                });*/

                /*google.maps.event.trigger(document.getElementById('source_address'), 'keydown', {
                    keyCode: 13
                });*/

            }else{
                // $(".booknowbtn").addClass('disabled');
                $(".booknowbtn").attr("disabled", true);
            }
        });

        //code for cancel ride
        $(document).on("click",".cancelRideBtn",function (){
           // alert("cancel Ride");
            var ride_id= $("#ride_id").val();

            if(ride_id > 0) {
                $.ajax({
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: false,
                    cache: false,
                    /*contentType: "application/json",*/
                    /* dataType: "json",*/
                    /*processData: false,*/
                    url: '{{ route('post:transport-ride-cancel') }}',
                    data: {
                        'ride_id': ride_id
                    },
                    success: function (data) {
                        if (data.success == true) {
                            window.location.href = data.redirect_path;
                        } else {
                            errormesssageDisplay(data.errmessage, "danger")
                        }
                    }
                });
            }else{
                errormesssageDisplay("Ride Not Found","danger")
            }

        });

        //code for omchange of vehicle type
        $(document).on("change",".service_vehicle",function (){
            $("#cover-spin-new").css("display","block");
            getRidePreview();


        });


        //code for datetime pickers
        var oldDate = new Date();
        var hour = oldDate.getHours();
        var newDate = oldDate.setHours(hour + 1);
        $('#datepicker').datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            minuteStep: 30,
            startDate: new Date(new Date().setHours(new Date().getHours() + 2 )),
            // 'minDateTime': new Date()
        });

    });

        function initialize() {
        var map = new google.maps.Map(document.getElementById('map'), {
            mapTypeControl: false,
            center: {lat: {{ isset($general_settings->map_lat)?$general_settings->map_lat:22.3039 }}, lng: {{ isset($general_settings->map_long)?$general_settings->map_long:70.8022 }}},
            zoom: 13
        });
        new AutocompleteDirectionsHandler(map);
    }

        /**
         * @constructor
         */
        function AutocompleteDirectionsHandler(map) {
            this.map = map;
            this.originPlaceId = null;
            this.destinationPlaceId = null;
            this.travelMode = 'DRIVING';
            var originInput = document.getElementById('source_address');
            var destinationInput = document.getElementById('dest_address');

            // var modeSelector = document.getElementById('mode-selector');
            this.directionsService = new google.maps.DirectionsService;
            this.directionsDisplay = new google.maps.DirectionsRenderer;
            this.directionsDisplay.setMap(map);

            var originAutocomplete = new google.maps.places.Autocomplete(
                originInput, {places: true});

            var destinationAutocomplete = new google.maps.places.Autocomplete(
                destinationInput, {places: true,});
            this.setupPlaceChangedListener(originAutocomplete, 'ORIG');
            this.setupPlaceChangedListener(destinationAutocomplete, 'DEST');
        }

        AutocompleteDirectionsHandler.prototype.setupPlaceChangedListener = function (autocomplete, mode) {

            var me = this;

            autocomplete.bindTo('bounds', this.map);
            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place.place_id) {
                    window.alert("Please select an option from the dropdown list.");
                    return;
                }
                if (mode === 'ORIG') {

                    // ChIJuU64UoXJWTkRTnYTFRkH2bY
                    // 22.3112483,70.7756567
                    me.originPlaceId = place.place_id;
                    var lat = place.geometry.location.lat();
                    var long = place.geometry.location.lng();
                    $("#source_lat").val(lat);
                    $("#source_long").val(long);
                    $("#cover-spin-new").css("display","none");
                     getRidePreview();
                } else if (mode === 'DEST') {

                    me.destinationPlaceId = place.place_id;
                    var lat = place.geometry.location.lat();
                    var long = place.geometry.location.lng();
                    $("#dest_lat").val(lat);
                    $("#dest_long").val(long);
                    $("#cover-spin-new").css("display","none");
                    getRidePreview();
                }
                me.route();

            });
        };
        AutocompleteDirectionsHandler.prototype.route = function () {
            if (!this.originPlaceId || !this.destinationPlaceId) {
                return;
            }
            var me = this;
            this.directionsService.route({
                origin: {'placeId': this.originPlaceId},
                destination: {'placeId': this.destinationPlaceId},
                travelMode: this.travelMode
            }, function (response, status) {
                if (status === 'OK') {
                    me.directionsDisplay.setDirections(response);
                    console.log(response.routes[0].legs[0].distance['value']);
                    console.log(response.routes[0].legs[0].duration['value']);
                    $("#eta_distance").val(response.routes[0].legs[0].distance['value'])
                    $("#eta_time").val(response.routes[0].legs[0].duration['value'])
                    $("#eta_distance_txt").val(response.routes[0].legs[0].distance['text'])
                    $("#eta_time_txt").val(response.routes[0].legs[0].duration['text'])
                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
        };
        google.maps.event.addDomListener(window, 'load', initialize);


        // $(document).ready(function (){

            //functoion send code for view fare caluclate
            function getRidePreview(){
                var processing="false";


                var timerId = setInterval(function(){

                    if (processing == "false") {
                        processing="true";
                        // $("#cover-spin-new").css("display","block");

                            //after 2 sec call ajax
                            var source_lat = $("#source_lat").val();
                            var source_long = $("#source_long").val();
                            var dest_lat = $("#dest_lat").val();
                            var dest_long = $("#dest_long").val();
                            var service = "{{ $service }}";
                            if(source_lat !="0" &&  source_long !="0" && dest_lat !="0" && dest_long !="0" )
                            {


                                var form=$("#rideFrm");
                                $.ajax({
                                    type:'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    async :false,
                                    cache: false,

                                    /*contentType: "application/json",*/
                                   /* dataType: "json",*/
                                    /*processData: false,*/
                                    url:'{{ route('post:transport-ride-preview',[$service]) }}',
                                    data: form.serialize(),
                                    success:function(data){
                                        // console.log(data);

                                        if(data.success == true){
                                            // errormesssageDisplay(data.errmessage,"success")

                                            if(data.htmlData !="")
                                            {
                                                $(".fare_data").empty();
                                                $(".fare_data").append(data.htmlData);
                                            }
                                            // $(".booknowbtn").removeClass('disabled');
                                            $(".booknowbtn").attr("disabled", false);
                                        }else{
                                            errormesssageDisplay(data.errmessage,"danger")
                                            if(data.htmlData !="")
                                            {
                                                $(".fare_data").empty();
                                                $(".fare_data").append(data.htmlData);
                                            }
                                            // $(".booknowbtn").addClass('disabled');
                                            $(".booknowbtn").attr("disabled", true);
                                        }
                                        $("#cover-spin-new").css("display","none");

                                    }
                                });
                            }else{
                                // errormesssageDisplay("Please Select Proper Location","error")
                                $("#cover-spin-new").css("display","none");
                            }
                        }

                    }, 1000);

            }

            //ride now btn close
            $(document).on("click",".rideNowBtn",function (){


                @if (Illuminate\Support\Facades\Auth::guard("user")->check())
                    //book ride
                    $('#cover-spin').css('display',"block");
                    $('#cover-spin').height($( ".rightPartSection" ).height());
                    $("body").css('overflow-y',"hidden");
                    getRideBook();

                @else
                    //code for chcekuser login
                    $('.btn-log-in').trigger("click");
                @endif
            });

            //retry ride
            $(document).on("click",".retryRide",function (){
                getRideBook();
            });

            @if (Illuminate\Support\Facades\Auth::guard("user")->check())
            //functon for ride status check
            function checkRideStatus(){

                var ride_id= $("#ride_id").val();

                if(ride_id > 0) {
                    return $.ajax({
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: false,
                        cache: false,
                        /*contentType: "application/json",*/
                        /* dataType: "json",*/
                        /*processData: false,*/
                        url: '{{ route('post:transport-check-ride-status') }}',
                        data: {
                            'ride_id': ride_id
                        },
                        success: function (data) {
                            /*console.log(data);
                            jQuery.parseJSON(data);*/
                            /*var arrayData =[];
                            arrayData['success']= data.success;
                            arrayData['redirect_path']= data.redirect_path;
                            console.log(arrayData);
                            return arrayData;*/
                            /*if (data.success == true) {
                                window.location.href = data.redirect_path;
                            } else {
                                errormesssageDisplay(data.errmessage, "danger")
                            }*/
                        }
                    });
                }else{
                    errormesssageDisplay("Ride Not Found","danger");
                }
            }

            //functon for ride book
            function getRideBook(){

                var source_lat = $("#source_lat").val();
                var source_long = $("#source_long").val();
                var dest_lat = $("#dest_lat").val();
                var dest_long = $("#dest_long").val();
                var service = "{{ $service }}";
                var x = 1;
                if(source_lat !="0" &&  source_long !="0" && dest_lat !="0" && dest_long !="0" )
                {
                    var form=$("#rideFrm");
                    $.ajax({
                        type:'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async :false,
                        cache: false,
                        /*contentType: "application/json",*/
                        /* dataType: "json",*/
                        /*processData: false,*/
                        url:'{{ route('post:transport-ride-book',[$service]) }}',
                        data: form.serialize(),
                        success:function(data){
                            // console.log(data);

                            if(data.success == true){
                                $("#cover-spin").css('display',"block");
                                $("body").css('overflow-y',"hidden");
                                $("#retry-spin").css('display',"none");
                                $("#ride_id").val(data.ride_id);
                                // var accept_time_out =  data.accept_time_out;
                                var accept_time_out =  10;
                                var total_mil_sec = parseInt(accept_time_out * 1000);
                                intv = setTimeout(function () {

                                    //ajax call for check ride accepted or not

                                        const statusData = checkRideStatus();
                                        arrayData =  statusData.responseJSON;

                                        if(arrayData.success == true){
                                            $("body").css('overflow-y',"auto");
                                            window.location.href = arrayData.redirect_path;
                                        }
                                        console.log("x"+x+"accept_time_out"+accept_time_out);
                                        $('#retry-spin').height($( ".rightPartSection" ).height());
                                        $("#retry-spin").css('display',"block");
                                        $("body").css('overflow-y',"hidden");
                                        $("#cover-spin").css('display',"none");
                                        clearInterval(intv);
                                        return false;
                                }, total_mil_sec);
                                // window.location.href = data.redirect_path;
                            }else{
                                errormesssageDisplay(data.errmessage,"danger");
                                $("#cover-spin").css('display',"none");
                                $("#retry-spin").css('display',"none");
                                $("body").css('overflow-y',"auto");
                            }
                        }
                    });
                }
            }

            $("body").css('overflow-y',"auto");
        @endif
        // });
</script>
@endsection
