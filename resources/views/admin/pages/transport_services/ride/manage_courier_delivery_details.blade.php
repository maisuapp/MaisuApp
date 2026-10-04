@extends('admin.layout.super_admin')
@section('title')
    Courier Details
@endsection
@section('page-css')
    <style>
        #map {
            z-index: 0;
        }
        .set-map {
            height: 350px;
            margin-bottom: 30px;
        }
        .ride-status span {
            font-size: 14px;
            padding: 3px 6px;
        }
        .courier-detail-table .table tr:last-of-type td, .courier-detail-table .table tr:last-of-type th {
            padding: .70rem;
            border-top: none;
            border-bottom: none;
        }
        .thwidth {
            width: 30%;
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
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Delivery Details</h5>
                            <span>Delivery Details</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    @if(in_array($ride_Details->status, [0,1,2,3,5,6,7,8]))
                        <button class="btn btn-danger waves-effect waves-light btn-right m-b-0 ride_cancel" rideid="{{$ride_Details->id}}" updatestatus="4">Cancel</button>
                    @endif
                    @if(in_array($ride_Details->status, [0]))
                        <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check() ) ? route('get:admin:transport_courier_manual_assign_driver',[$slug,$ride_Details->id]) : "#"}}" class="btn btn-success waves-effect waves-light float-right m-r-5">Assign Driver</a>
                    @endif
                    {{--0=pending, 1=accepted, 2=schedule-accepted, 3=arrived, 4=cancelled, 5=running, 6=drop, 7=payment, 8=rating, 9=completed, 10=failed--}}
                    @if((Illuminate\Support\Facades\Auth::guard("admin")->check()) && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                        @if((isset($general_settings)) && $general_settings != Null && isset($ride_Details))
                            @if(($general_settings->max_driver_reassign > $ride_Details->is_driver_reassign) && ($ride_Details->driver_id != Null) && in_array($ride_Details->status,[1,2,3,5]))
                                <a href="{{ route('get:admin:transport_re_assign_driver',[ "slug" => $slug, "ride_id" => $ride_Details->id ]) }}" class="btn btn-dark waves-effect waves-light  btn-right m-b-0 m-r-10 ride_re_assign">Re-Assign</a>
                            @endif
                        @endif
                    @endif
                    @if(in_array($ride_Details->status, [3,5,6,7,8]))
                        <button class="btn btn-success waves-effect waves-light btn-right m-b-0 ride_completed" rideid="{{$ride_Details->id}}" updatestatus="9" style="margin-right: 10px;">Completed</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">

                        {{--@if(Illuminate\Support\Facades\Auth::guard("admin")->check())--}}
                        {{--    @if($ride_Details->status == 3 || $ride_Details->status == 4)--}}
                        {{--        @if($ride_Details->payment_type == 2 || $ride_Details->payment_type == 3)--}}
                        {{--            <div class="card">--}}
                        {{--                <div class="card-header">--}}
                        {{--                    <h5 style="font-size: 18px; margin-left: 10px;margin-top: 30px">Refund--}}
                        {{--                        Details</h5>--}}
                        {{--                    @if($ride_Details->user_refund_status == 0)--}}
                        {{--                        <a href="#"--}}
                        {{--                           class="btn btn-success m-b-0 btn-right refund_settle"--}}
                        {{--                           order_id="{{$ride_Details->id}}">Refund Order</a>--}}
                        {{--                    @endif--}}
                        {{--                </div>--}}
                        {{--                <div class="card-block">--}}
                        {{--                    <div class="row">--}}
                        {{--                        <div class="col-lg-12">--}}
                        {{--                            <div class="table-responsive ride-detail-table"--}}
                        {{--                                 style="padding: 0 5px">--}}
                        {{--                                <table class="table">--}}
                        {{--                                    <tr>--}}
                        {{--                                        <th style="width: 15%">Order Amount:</th>--}}
                        {{--                                        <td class="currency">--}}
                        {{--                                            {{ isset($ride_Details) ? $ride_Details->total_pay : '' }}</td>--}}
                        {{--                                    </tr>--}}
                        {{--                                    <tr>--}}
                        {{--                                        <th style="width: 15%">Refund Status:</th>--}}
                        {{--                                        <td class="order-status">--}}
                        {{--                                            @if(isset($ride_Details) && ($ride_Details->user_refund_status == 1))--}}
                        {{--                                                <span class="completed">completed</span>--}}
                        {{--                                            @else--}}
                        {{--                                                <span class="cancelled">Pending</span>--}}
                        {{--                                            @endif--}}
                        {{--                                            --}}{{--{{ isset($ride_Details) ? ($ride_Details->user_refund_status == 1 ? "completed" : "pending") : '-----' }}--}}
                        {{--                                        </td>--}}
                        {{--                                    </tr>--}}
                        {{--                                    <tr>--}}
                        {{--                                        <th style="width: 15%">Cancel Charge:</th>--}}
                        {{--                                        <td class="currency"> {{ isset($ride_Details) ? number_format($ride_Details->cancel_charge,2) : '0.00' }}</td>--}}
                        {{--                                    </tr>--}}
                        {{--                                    <tr>--}}
                        {{--                                        <th style="width: 15%">Refund Amount:</th>--}}
                        {{--                                        <td class="currency"> {{ isset($ride_Details) ? number_format($ride_Details->refund_amount,2) : '0.00' }}</td>--}}
                        {{--                                    </tr>--}}
                        {{--                                </table>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        {{--                    </div>--}}
                        {{--                </div>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                        {{--    @endif--}}
                        {{--@endif--}}

                        <div class="card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12 col-xl-6">
                                        <div class="table-responsive ride-detail-table courier-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Delivery Detail</h5>
                                            </div>
                                            <table class="table">
                                                @if(isset($ride_Details))
                                                    <tr>
                                                        <th>Delivery No.</th>
                                                        <td>: {{ $ride_Details->ride_no }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <td>:
                                                            @if($ride_Details->user_name != Null)
                                                                {{ ucwords(strtolower($ride_Details->user_name)) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Contact Number</th>
                                                        <td>:
                                                            @if((isset($user_details)) && $user_details->contact_number != Null)
                                                                {{ App\model\User::ContactNumber2Stars($user_details->contact_number) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Email</th>
                                                        <td>:
                                                            @if((isset($user_details)) && $user_details->email != Null)
                                                                {{ App\model\User::Email2Stars($user_details->email) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Driver Name</th>
                                                        <td>:
                                                            @if((isset($driver_details)) &&  $driver_details->driver_name != Null)
                                                                {{ ucwords(strtolower($driver_details->driver_name)) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Driver Contact Number</th>
                                                        <td>:
                                                            @if((isset($driver_details)) && $driver_details->contact_number != Null)
                                                                {{ App\model\User::ContactNumber2Stars($driver_details->contact_number) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Driver Email</th>
                                                        <td>:
                                                            @if((isset($driver_details)) && $driver_details->email != Null)
                                                                {{ App\model\User::Email2Stars($driver_details->email) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    {{--<tr>--}}
                                                    {{--    <th>Delivery Start Time</th>--}}
                                                    {{--    <td>: {{ Carbon\Carbon::parse($ride_Details->pickup_datetime)->format('d F Y h:i:s') }} </td>--}}
                                                    {{--</tr>--}}
                                                    {{--<tr>--}}
                                                    {{--    <th>Delivery End Time</th>--}}
                                                    {{--    <td>: {{ Carbon\Carbon::parse($ride_Details->destination_datetime)->format('d F Y h:i:s') }}</td>--}}
                                                    {{--</tr>--}}
                                                    <tr>
                                                        <th>Ride Type</th>
                                                        <td>: {{ isset($ride_Details) && $ride_Details->ride_type == 1 ? "Schedule Ride" : 'Book Now' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Ride Booking Date</th>
                                                        <td>: {{ isset($ride_Details) ? $ride_Details->created_at : '' }}</td>
                                                    </tr>
                                                    @if(isset($ride_Details) && $ride_Details->ride_type == 1)
                                                        <tr>
                                                            <th>Schedule Ride Date</th>
                                                            <td>: {{ isset($ride_Details) ? $ride_Details->pickup_datetime : '' }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>Pick Address</th>
                                                        <td>: {{ $ride_Details->pickup_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Drop Address</th>
                                                        <td>: {{ $ride_Details->destination_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Vehicle Type</th>
                                                        <td>: @if($ride_Details->vehicle_type_name != Null)
                                                                {{ ucwords(strtolower($ride_Details->vehicle_type_name)) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Goods Weight</th>
                                                        <td>: {{ isset($courier_details)? $courier_details->weight_start_limit .' kg - '.$courier_details->weight_close_limit .' kg' : "----" }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Package Type</th>
                                                        <td>: {{ isset($courier_details->category_name)? $courier_details->category_name  : "----" }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Delivery Instruction</th>
                                                        <td >: @if(isset($courier_details) && $courier_details->delivery_instruction != Null) {{ $courier_details->delivery_instruction }} @else ---- @endif </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Description</th>
                                                        <td>: {{ isset($courier_details)? $courier_details->description : "----" }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-xl-6">
                                        <div class="ride-detail-table-header">
                                            <h5>Delivery route on map</h5>
                                        </div>
                                        <div id="map" class="set-map"></div>
                                    </div>

                                    <div class="col-lg-12 col-xl-6">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Delivery  Detail</h5>
                                            </div>
                                            <table class="table">
                                                <tr>
                                                    <th class="thwidth">Recipient  Name</th>
                                                    <td>: {{ isset($courier_details)? ucwords(strtolower($courier_details->recipient_name)) : "----" }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Shop name / Building Name</th>
                                                    <td>:
                                                        @if(isset($courier_details) && $courier_details->recipient_landmark != Null)
                                                            {{ ucwords(strtolower($courier_details->recipient_landmark)) }}
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Recipient Contact No.</th>
                                                    <td>:
                                                        @if(isset($courier_details) && $courier_details->recipient_contact_number != Null)
                                                            {{ ucwords(strtolower($courier_details->recipient_contact_number)) }}
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>
                                                </tr>
<!--                                                <tr>
                                                    <th>Width / Height</th>
                                                    <td>: @if(isset($courier_details)) {{ $courier_details->goods_width }} <i class='fa fa-close'></i> {{ $courier_details->goods_height }} @else &#45;&#45;&#45;&#45; @endif </td>
                                                </tr>-->

                                            </table>
                                        </div>
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Pickup Detail</h5>
                                            </div>
                                            <table class="table">
                                                <tr>
                                                    <th class="thwidth">Sender Name</th>
                                                    <td>: {{ isset($courier_details)? ucwords(strtolower($courier_details->sender_name)) : "----" }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Shop name / Building Name</th>
                                                    <td>:
                                                        @if(isset($courier_details) && $courier_details->house_name != Null)
                                                            {{ ucwords(strtolower($courier_details->house_name)) }}
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Home name / Landmark</th>
                                                    <td>:
                                                        @if(isset($courier_details) && $courier_details->sender_landmark != Null)
                                                            {{ ucwords(strtolower($courier_details->sender_landmark)) }}
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Sender Contact No.</th>
                                                    <td>:
                                                        @if(isset($courier_details) && $courier_details->sender_contact_number != Null)
                                                            {{ ucwords(strtolower($courier_details->sender_contact_number)) }}
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-xl-6">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Fare Detail</h5>
                                            </div>
                                            <table class="table">
                                                @if(isset($ride_Details))
                                                    <tr>
                                                        <th>Total Distance :</th>
                                                        <td> {{ $ride_Details->total_distance }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Time Taken :</th>
                                                        <td> {{ ($ride_Details->eta)?$ride_Details->eta:"0 min" }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cost For KM :</th>
                                                        <td class="currency"> {{ $ride_Details->vehicle_cost_for_km }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Distance Fare :</th>
                                                        <td class="currency"> {{ $ride_Details->total_distance_amount }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Base Fare :</th>
                                                        <td class="currency"> {{ $ride_Details->base_fare }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Time Fare :</th>
                                                        <td class="currency"> {{ $ride_Details->time_fare_amount }}</td>
                                                    </tr>
                                                    {{--<tr>--}}
                                                    {{--<th>Ride Fare</th>--}}
                                                    {{--<td>: $ {{ $ride_Details->base_fare }}</td>--}}
                                                    {{--</tr>--}}
                                                    @if($promo_code_discount > 0)
                                                        <tr>
                                                            <th>{{ $promo_code_name }}</th>
                                                            <td class="currency"> {{ $promo_code_discount }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>Min Adjustment Amount :</th>
                                                        <td class="currency"> {{ $ride_Details->adjustment_amount }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Service Fare :</th>
                                                        <td class="currency"> {{ ($service_fare_fee)?$service_fare_fee:0 }}</td>
                                                    </tr><tr>
                                                        <th>Tax :</th>
                                                        <td class="currency"> {{ $ride_Details->tax }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>SubTotal :</th>
                                                        <td class="currency"> {{ isset($total)? $total : 0 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tip :</th>
                                                        <td class="currency"> {{ isset($ride_Details->tip)? $ride_Details->tip : 0 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Discount :</th>
                                                        <td class="currency"> {{ isset($discount)? $discount : 0 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total :</th>
                                                        <td class="currency"> {{ isset($grand_total)? $grand_total : 0 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Type :</th>
                                                        <td> {{ $ride_Details->payment_type == 1 ? "Cash" : ($ride_Details->payment_type == 2 ? "Card" : "Wallet") }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Status :</th>
                                                        <td> {{ ($ride_Details->payment_status == 1) ? 'Paid' : 'Not Paid' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status :</th>
                                                        @if($ride_Details->status == 0)
                                                            @php $ride_status = "pending"; @endphp
                                                        @elseif($ride_Details->status == 1 || $ride_Details->status == 2 || $ride_Details->status == 3)
                                                            @php $ride_status = "approved"; @endphp
                                                            {{--@elseif($ride_list->status == 3)--}}
                                                            {{--@php $ride_status = "arrived"; @endphp--}}
                                                        @elseif($ride_Details->status == 4)
                                                            @php $ride_status = "cancelled"; @endphp
                                                        @elseif($ride_Details->status == 5 || $ride_Details->status == 6 || $ride_Details->status == 7 || $ride_Details->status == 8)
                                                            @php $ride_status = "running"; @endphp
                                                            {{--@elseif($ride_list->status == 6)--}}
                                                            {{--@php $ride_status = "drop"; @endphp--}}
                                                            {{--@elseif($ride_list->status == 7)--}}
                                                            {{--@php $ride_status = "payment"; @endphp--}}
                                                            {{--@elseif($ride_list->status == 8)--}}
                                                            {{--@php $ride_status = "rating"; @endphp--}}
                                                        @elseif($ride_Details->status == 9)
                                                            @php $ride_status = "completed"; @endphp
                                                        @elseif($ride_Details->status == 10)
                                                            @php $ride_status = "failed"; @endphp
                                                        @endif
                                                        <td class="ride-status">
                                                            <span id="ride_status_change" class="{{$ride_status}}"> {{ ucwords($ride_status) }}</span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-js')
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
        <script>
            $(document).ready(function (){

                $(document).on('click', '.ride_cancel', function (e) {
                    e.preventDefault();
                    var id = $(this).attr('rideid');
                    var update_status = $(this).attr('updatestatus');
                    var txt, title, status, url;
                    status = "Cancel";

                    url = '{{ route("get:admin:transport_update_ride_status") }}';
                    title = status + " Ride?";
                    txt = "if press yes then " + status + " ride!";
                    swal({
                            title: title,
                            text: txt,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                swal({
                                        title: status + " Ride!",
                                        text: "if " + status + " ride then Write " + status + " reason:",
                                        type: "input",
                                        showCancelButton: true,
                                        closeOnConfirm: false,
                                        animation: "slide-from-top",
                                        inputPlaceholder: status + " Reason"
                                    },
                                    function (inputValue) {
                                        if (inputValue === false) return false;
                                        if (inputValue === "") {
                                            swal.showInputError("You need to write " + status + " ride!");
                                            return false
                                        }
                                        $.ajax({
                                            type: 'get',
                                            url: url,
                                            data: {id: id, update_status: update_status, reason: inputValue},
                                            success: function (result) {
                                                if (result.success == true) {
                                                    $(".ride_cancel").css({"display": "none"});
                                                    $(".ride_re_assign").css({"display": "none"});
                                                    $(".ride_completed").css({"display": "none"});
                                                    if ($("#ride_status_change").hasClass("pending")) {
                                                        $("#ride_status_change").removeClass("pending");
                                                    }
                                                    if ($("#ride_status_change").hasClass("approved")) {
                                                        $("#ride_status_change").removeClass("approved");
                                                    }
                                                    if ($("#ride_status_change").hasClass("running")) {
                                                        $("#ride_status_change").removeClass("running");
                                                    }
                                                    $("#ride_status_change").addClass("cancelled");
                                                    $("#ride_status_change").empty();
                                                    $("#ride_status_change").text("Cancelled");
                                                    swal("success!", "You " + status + " ride reason is: " + inputValue, "success");
                                                }else {
                                                    swal("Warning", result.message, "warning");
                                                    console.log(result);
                                                }
                                            }
                                        })
                                    });
                            } else {
                                swal("Cancelled", "Cancel Ride", "error");
                            }
                        });
                });

                $(document).on('click', '.ride_completed', function (e) {
                    e.preventDefault();
                    var id = $(this).attr('rideid');
                    var update_status = $(this).attr('updatestatus');
                    var txt, title, status, url;
                    status = "Completed";

                    url = '{{ route("get:admin:transport_update_ride_status") }}';
                    title = status + " Ride?";
                    txt = "if press yes then " + status + " ride!";
                    swal({
                            title: title,
                            text: txt,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                    type: 'get',
                                    url: url,
                                    data: {id: id, update_status: update_status},
                                    success: function (result) {
                                        if (result.success == true) {
                                            $(".ride_cancel").css({"display": "none"});
                                            $(".ride_re_assign").css({"display": "none"});
                                            $(".ride_completed").css({"display": "none"});
                                            if ($("#ride_status_change").hasClass("pending")) {
                                                $("#ride_status_change").removeClass("pending");
                                            }
                                            if ($("#ride_status_change").hasClass("approved")) {
                                                $("#ride_status_change").removeClass("approved");
                                            }
                                            if ($("#ride_status_change").hasClass("running")) {
                                                $("#ride_status_change").removeClass("running");
                                            }
                                            $("#ride_status_change").addClass("completed");
                                            $("#ride_status_change").empty();
                                            $("#ride_status_change").text("Completed");
                                            swal("Success", "Ride status update successfully", "success");

                                        }else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                        // else {
                                        //
                                        //     swal("Cancelled", "Ride Status Not Updated Something Wrong!", "error");
                                        //         console.log(result);
                                        // }
                                    }
                                })
                            } else {
                                swal("Cancelled", "Ride Status Not Updated", "error");
                            }
                        });
                });

                $(document).on('click', '.refund_settle', function (e) {
                    e.preventDefault();
                    var id = $(this).attr('order_id');
                    var txt, title;
                    title = "Refund Settle?";
                    txt = "if press yes then settle order refund!";
                    var url = window.location.href;
                    swal({
                            title: title,
                            text: txt,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                    type: 'get',
                                    url: '{{ route("get:admin:user_courier_refund_amount_settle") }}',
                                    data: {id: id},
                                    success: function (result) {
                                        if (result.success == true) {
                                            swal({title: "Success", text: "Refund Settle Successfully", type: "success"},
                                                function () {
                                                    window.location.href = url;
                                                });
                                        } else {
                                            swal("Warning", result.message, "warning");
                                        }
                                    }
                                })
                            } else {
                                swal("Cancelled", "Refund Not Settle", "error");
                            }
                        });
                });
            });

        </script>
    @endif
    <script>
        function initMap() {
            var directionsService = new google.maps.DirectionsService;
            var directionsDisplay = new google.maps.DirectionsRenderer;
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: {lat: 14.5995, lng: 120.9842}
            });
            directionsDisplay.setMap(map);
            calculateAndDisplayRoute(directionsService, directionsDisplay);
        }

        function calculateAndDisplayRoute(directionsService, directionsDisplay) {
            directionsService.route({
                origin: "{{ isset($ride_Details)? $ride_Details->pickup_address : '' }}",
                destination: "{{ isset($ride_Details)? $ride_Details->destination_address : '' }}",
                travelMode: 'DRIVING'
            }, function (response, status) {
                if (status === 'OK') {
                    directionsDisplay.setDirections(response);
                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ (isset($general_settings) && ($general_settings->map_key != Null))? $general_settings->map_key : 0 }}&libraries=places&callback=initMap" async defer></script>
@endsection

