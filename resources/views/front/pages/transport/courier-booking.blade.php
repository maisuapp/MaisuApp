@extends('front.layout.default')
@section('title')
    <title>{{ isset($title)?$title:"Bike" }} Booking</title>
@endsection
@section('meta-tags')
    <meta name="description" content="{{ isset($title)?$title:"Bike" }} Services">
    <meta name="keywords" content="{{ isset($title)?$title:"Bike" }} Services">
@endsection
@section('page-css')

<style>
.card{
    border: none;
}
.card-header{
    background-color: #FFFFFF;
}
.pickup-drop-location .form-group{
    margin: 0;
}
.pickup-drop-location input{
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.card-header {
    padding: 0.75rem 0;
}
.card-body{
    padding: 1.25rem 0;
}
.card-header:hover{
    cursor: pointer;
}
.dest_address{
    padding-right: 145px !important;
}
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
                    <form name="courierFrm" class="courierFrm" id="courierFrm" method="post" href="javascript:void(0);">

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
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="accordion" id="accordionExample">
                                    <div class="card">
                                        <div class="card-header" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true">
                                            <span class="title ride-inner-title">Pickup Information </span>
                                            <span class="accicon"><i class="fas fa-angle-down rotate-icon float-right"></i></span>
                                        </div>
                                        <div id="collapseOne" class="collapse show" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 p-0">
                                                    <div class=" mb-2 ">
                                                        <div class="borderCls">
                                                            <div class="input-group">
                                                                <select name="weight_id" class="custom-select border-0" required>
<!--                                                                    <option value="" selected>Select Package Weight</option>-->
                                                                    @if(isset($transport_weight_list) &&  !empty($transport_weight_list))
                                                                        @foreach($transport_weight_list as $single_weight)
                                                                            <option value="{{$single_weight->id}}"  >{{ isset($single_weight)?( $single_weight->start_limit." Kg - ".( ($single_weight->close_limit>0)?$single_weight->close_limit:"above ".$single_weight->start_limit )." Kg" ):old('weight_limit')}}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="pickup-drop-location">
                                                            <div class="form-group">
                                                                <input type="text" name="source_address" class="source_address form-control" id="source_address" value="{{ ($current_address!="")?$current_address:"" }}" placeholder="Pick up location" required="" >
                                                                <input type="hidden" name="source_lat" id="source_lat" class="source_lat" value="0" >
                                                                <input type="hidden" name="source_long" id="source_long" class="source_long" value="0" >
{{--                                                                <span class="curr_loc"> <span>Current Location</span>  <span class="material-icons getLocationicon">my_location</span></span>--}}
                                                            </div>
                                                            <input type="hidden" name="eta_distance" id="eta_distance" class="eta_distance" value="0" >
                                                            <input type="hidden" name="eta_distance_txt" id="eta_distance_txt" class="eta_distance_txt" value="" >
                                                            <input type="hidden" name="eta_time" id="eta_time" class="eta_time" value="0" >
                                                            <input type="hidden" name="eta_time_txt" id="eta_time_txt" class="eta_time_txt" value="" >
                                                        </div>

                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="text" name="sender_landmark" id="sender_landmark" class="form-control  " placeholder="Shop Name/ Building Name"  autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="text" name="house_name" id="house_name" class="form-control  " placeholder="House Name/ Landmark"  autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="text" name="sender_name" id="sender_name" class="form-control  " placeholder="Sender Name"  autocomplete="off" >
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="text" name="sender_contact_number" id="sender_contact_number" class="form-control  " placeholder="Sender Contact Number"  autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <span class="title ride-inner-title">Receiver Information</span>
                                            <span class="accicon"><i class="fas fa-angle-down rotate-icon float-right"></i></span>
                                        </div>
                                        <div id="collapseTwo" class="collapse" data-parent="#accordionExample">
                                    <div class="card-body">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 p-0">
                                            <div class=" mb-2">
                                                <div class="pickup-drop-location">
                                                    <div class="form-group ">
                                                        <input type="text" name="dest_address" id="dest_address" class="dest_address" value="" placeholder="Drop off location" required="" >
                                                        <input type="hidden" name="dest_lat" id="dest_lat" class="dest_lat" value="0" >
                                                        <input type="hidden" name="dest_long" id="dest_long" class="dest_long" value="0" >
{{--                                                        <span class="curr_loc"> <span>Current Location</span>  <span class="material-icons getLocationicon">my_location</span></span>--}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class=" mb-2">
                                                <div class="input-group ">
                                                    <input type="text" name="recipient_landmark" id="recipient_landmark" class="form-control  " placeholder="House Name/ Landmark"  autocomplete="off">
                                                </div>
                                            </div>
                                            <div class=" mb-2">
                                                <div class="input-group ">
                                                    <input type="text" name="recipient_name" id="recipient_name" class="form-control  " placeholder="Receiver Name"  autocomplete="off" >
                                                </div>
                                            </div>
                                            <div class=" mb-2">
                                                <div class="input-group ">
                                                    <input type="text" name="recipient_contact_number" id="recipient_contact_number" class="form-control  " placeholder="Receiver Contact Number"  autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false">
                                            <span class="title ride-inner-title">Package Information</span>
                                            <span class="accicon"><i class="fas fa-angle-down rotate-icon float-right"></i></span>
                                        </div>
                                        <div id="collapseThree" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 p-0">
                                                    <div class=" mb-2 ">
                                                        <div class="borderCls">
                                                            <div class="input-group">
                                                                <select name="category_id" id="category_id" class="custom-select border-0" required>
                                                                    <option value="" selected>Select Package Type</option>
                                                                    @if(isset($transport_category_list) &&  !empty($transport_category_list))
                                                                        @foreach($transport_category_list as $single_cat_type)
                                                                            <option value="{{$single_cat_type->id}}"  >{{ isset($single_cat_type)?( $single_cat_type->category_name ):"--"}}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="number" name="estimate_price" id="estimate_price" class="form-control  " placeholder="Estimated Value (optional)"  autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="input-group ">
                                                            <input type="text" name="delivery_instruction" id="delivery_instruction" class="form-control  " placeholder="Delivery Instruction"  autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class=" mb-2">
                                                        <div class="form-group form-check">
                                                            <input type="checkbox" class="form-check-input" name="legal_doc" id="legal_doc">
                                                            <label class="form-check-label" for="exampleCheck1">i am not sending <span class="font-weight-bold serviceTitle">illegal items</span></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
                                        <select name="payment_type" id="payment_type" class="custom-select border-0 payment_type" required>
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
                        <div class="row hide"  id="card_lists">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="padding-10">
                                    <div class="ride-inner-title">
                                        <span >Card Lists</span>
                                    </div>
                                </div>
                                <div class="borderCls">
                                    <div class="input-group p-2">
                                        @if(isset($card_list) && count($card_list) > 0)
                                            @foreach($card_list as $key=>$single_card_list)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" {{ ($key == 0)?'checked="checked"':"" }} name="card_id" value="{{$single_card_list->id}}" id="payment_type_{{$single_card_list->id}}" value="option1">
                                                    <label class="form-check-label" for="payment_type_{{$single_card_list->id}}">xx-{{ substr($single_card_list->card_number,-4) }}</label>
                                                </div>
                                            @endforeach
                                        @else
                                            <label class="mb-0">Currently you have not added card. Click <a href="{{ route('get:user_payments') }}" target="_blank">Add New Card</a></label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="padding-10">
                                    <div class="ride-inner-title">
                                        <span >Book Type</span>
                                    </div>
                                </div>
                                <div class="checkboxSection">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input rideTypeCls" id="ride_now" name="ride_type" value="0" checked>
                                        <label class="custom-control-label" for="ride_now">Book Now</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input rideTypeCls" id="schedule_ride" name="ride_type" value="1">
                                        <label class="custom-control-label" for="schedule_ride">Schedule Book</label>
                                    </div>
                                </div>
                                <div class="checkboxSection padding-10 rideNowSection" style="display: none;">
                                    <div class="input-append date form_datetime">
                                        <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                                            <input type="text" name="pickup_date_time" id="datepicker" value="{{ date("Y-m-d H:i", strtotime('+6:30 hours')) }}" class="form-control form-font-cls datetimepicker-input" data-target="#datetimepicker1" autocomplete="off"  required/>
                                            <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                    <button class="btn btn-outline-secondary btnColor text-uppercase btn-block booknowbtn rideNowBtn" type="submit">Book Now</button>
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
<script src="{{ asset('assets/js/validation/jquery.validate.js')}}" type="text/javascript"></script>

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
            console.log('dest_address');
            var asdf = $("#dest_address").val();
            console.log(asdf);
            if($("#dest_address").val() != ""){
                console.log("in if");
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
                console.log("in else");
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

        //code set card list hide show
        $(document).on("change",".payment_type",function (){
            var payment_type =$(this).val();
            if(payment_type == 2){
                if($("#card_lists").hasClass("hide")){
                    $("#card_lists").removeClass("hide");
                    $("#card_lists").addClass("show");
                }else{
                    $("#card_lists").addClass("show");
                }
            }else{
                if($("#card_lists").hasClass("show")){
                    $("#card_lists").removeClass("show");
                    $("#card_lists").addClass("hide");
                }else{
                    $("#card_lists").addClass("hide");
                }
            }
        });


        //code for datetime pickers
        var oldDate = new Date();
        var hour = oldDate.getHours();
        var newDate = oldDate.setHours(hour + 1);
        $('#datepicker').datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            minuteStep: 10,
            startDate: new Date(new Date().setHours(new Date().getHours())),
            // 'minDateTime': new Date()
        });

        $("#courierFrm").validate({
            ignore: [],
            rules: {
                weight_id: {
                    required : true,
                },
                sender_landmark: {
                    required : true,
                },
                house_name: {
                    required : true,
                },
                sender_name: {
                    required : true,
                },
                sender_contact_number: {
                    required : true,
                    number : true,
                },
                recipient_landmark: {
                    required : true,
                },
                recipient_name: {
                    required : true,
                },
                recipient_contact_number: {
                    required : true,
                    number : true,
                },
                payment_type: {
                    required : true,
                    number : true,
                },
                card_id : {
                    required:{
                        depends:function(){
                            if ($('#payment_type').val()==='2'){
                                return true;
                            }else{
                                return false;
                            }
                        }
                    }
                },
                legal_doc : {
                    required : true,
                }
            },
            messages: {

                weight_id: {
                    required : "Please select package weight",
                },
                sender_landmark: {
                    required : "Please enter sender shop name/ building name",
                },
                house_name: {
                    required : "Please enter sender House Name/ Landmark",
                },
                sender_name: {
                    required : "Please enter sender name",
                },
                sender_contact_number: {
                    required :"Please enter contact number",
                    number :"Please enter valid number",
                },
                recipient_landmark: {
                    required : "Please enter recipient House Name/ Landmark",
                },
                recipient_name: {
                    required : "Please enter recipient name",
                },
                recipient_contact_number: {
                    required :"Please enter contact number",
                    number :"Please enter valid number",
                },
                payment_type: {
                    required : "Please enter payment type",
                },
                card_id: {
                    required : "Please select proper card for payment",
                },
                legal_doc : {
                    required : "Please check for legal document.",
                }
            },
            invalidHandler: function(form, validator) {
                /*console.log(validator.errorList[0]);
                console.log(validator.errorList[0]['element']['id']);*/
                var  elem = validator.errorList[0]['element']['id'];
                var colAdd =$("#"+elem).closest('.collapse').attr('id');
                /*console.log(colAdd);*/
                $("#"+colAdd).collapse('show');
            },
            errorPlacement: function(error, element) {
                    console.log(element);
                    if(element.attr("name") === "category_id" || element.attr("name") === "card_id")
                    {
                        error.insertAfter(element.parent("div").parent("div").parent("div"));
                    }/*else if(element.attr("name") === "card_id")
                    {
                        error.insertAfter(element.parent("div").parent("div").parent("div"));
                    }*/else{
                        error.insertAfter(element.parent("div"));
                    }


            },
            submitHandler: function(form) {

                // getRideBook();
                @if (Illuminate\Support\Facades\Auth::guard("user")->check())
                    getRideBook();
                @else
                    //code for chcekuser login
                    $('.btn-log-in').trigger("click");
                @endif
            }
        });

    });

        function initialize() {
            var map = new google.maps.Map(document.getElementById('map'), {
                mapTypeControl: false,
                center: {lat: {{ isset($general_settings->map_lat)?$general_settings->map_lat:22.3039 }}, lng: {{ isset($general_settings->map_long)?$general_settings->map_long:70.8022}}},
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
                    $("#cover-spin-new").css("display","block");
                     getRidePreview();
                } else if (mode === 'DEST') {

                    me.destinationPlaceId = place.place_id;
                    var lat = place.geometry.location.lat();
                    var long = place.geometry.location.lng();
                    $("#dest_lat").val(lat);
                    $("#dest_long").val(long);
                    $("#cover-spin-new").css("display","block");
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
                            //after 2 sec call ajax
                            var source_lat = $("#source_lat").val();
                            var source_long = $("#source_long").val();
                            var dest_lat = $("#dest_lat").val();
                            var dest_long = $("#dest_long").val();
                            var service = "{{ $service }}";
                            if(source_lat !="0" &&  source_long !="0" && dest_lat !="0" && dest_long !="0" )
                            {
                                var form=$("#courierFrm");
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
                                $("#cover-spin-new").css("display","none");
                                // errormesssageDisplay("Please Select Proper Location","error")
                            }
                        }
                    }, 1000);
            }

            //ride now btn close
            $(document).on("click",".rideNowBtn",function (){


                @if (Illuminate\Support\Facades\Auth::guard("user")->check())
                    //book ride
                    /*$('#cover-spin').css('display',"block");*/
                    $('#cover-spin').height($( ".rightPartSection" ).height());
                    // getRideBook()

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
                    errormesssageDisplay("Ride Not Found","danger")
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
                        $('#cover-spin').css('display',"block");
                        $("body").css('overflow-y',"hidden");
                        var form=$("#courierFrm");
                        console.log($("#courierFrm").valid());
                        if($("#courierFrm").valid())
                        {
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
                                    console.log(data);

                                    if(data.success == true){
                                        $("#cover-spin").css('display',"block");
                                        $("body").css('overflow-y',"hidden");
                                        $("#retry-spin").css('display',"none");
                                        $("#ride_id").val(data.ride_id);

                                        //waiting screen for order accept by driver
                                        // var accept_time_out =  data.accept_time_out;
                                        // var accept_time_out =  10;
                                        // var total_mil_sec = parseInt(accept_time_out * 1000);
                                        // intv = setTimeout(function () {
                                        //
                                        //     //ajax call for check ride accepted or not
                                        //
                                        //     const statusData = checkRideStatus();
                                        //     arrayData =  statusData.responseJSON;
                                        //
                                        //     if(arrayData.success == true){
                                        //         $("body").css('overflow-y',"auto");
                                        //         window.location.href = arrayData.redirect_path;
                                        //     }
                                        //     console.log("x"+x+"accept_time_out"+accept_time_out);
                                        //     $('#retry-spin').height($( ".rightPartSection" ).height());
                                        //     $("#retry-spin").css('display',"block");
                                        //     $("body").css('overflow-y',"hidden");
                                        //     $("#cover-spin").css('display',"none");
                                        //     clearInterval(intv);
                                        //     return false;
                                        // }, total_mil_sec);

                                        window.location.href = data.redirect_path;
                                    }else{
                                        errormesssageDisplay(data.errmessage,"danger")
                                        $("#cover-spin").css('display',"none");
                                        $("#retry-spin").css('display',"none");
                                        $("body").css('overflow-y',"auto");
                                    }
                                }
                            });
                        }else{

                            $("#cover-spin").css('display',"none");
                            $("#retry-spin").css('display',"none");
                            $("body").css('overflow-y',"auto");
                        }

                    }else{

                        $("#cover-spin").css('display',"none");
                        $("#retry-spin").css('display',"none");
                        $("body").css('overflow-y',"auto");
                    }
                }

            @endif


        // });
        $("body").css('overflow-y',"auto");

</script>
@endsection
