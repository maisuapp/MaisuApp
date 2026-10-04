@extends('admin.layout.super_admin')
@section('title')
    @if(isset($slug) && $slug == "courier-service")
        Delivery Details
    @else
        Ride Details
    @endif
@endsection
@section('page-css')
    <style>
        #map {
            z-index: 0;
        }

        .set-map {
            height: 300px;
            margin-bottom: 30px;
        }

        .ride-status span {
            font-size: 14px;
            padding: 3px 6px;
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
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                <i class="feather icon-list bg-c-green"></i>
                                <div class="d-inline">
                                    <h5> @if(isset($slug) && $slug == "courier-service") Delivery Details @else Ride Details @endif</h5>
                                    <span> Full @if(isset($slug) && $slug == "courier-service") Delivery @else Ride @endif Details</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            @if(in_array($ride_Details->status, [0,1,2,3,5,6,7,8]))
                                <button class="btn btn-danger waves-effect waves-light btn-right m-b-0 ride_cancel" rideid="{{$ride_Details->id}}" updatestatus="4">Cancel</button>
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
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>@if(isset($slug) && $slug == "courier-service") Delivery @else Ride @endif route on
                                    map</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12 col-xl-12">
                                        <div id="map" class="set-map"></div>
                                    </div>
                                    <div class="col-lg-12 col-xl-6">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>@if(isset($slug) && $slug == "courier-service")
                                                        Delivery
                                                    @else Ride @endif Detail</h5>
                                            </div>
                                            <table class="table">
                                                @if(isset($ride_Details))
                                                    <tr>
                                                        <th>Ride No.</th>
                                                        <td>: {{ $ride_Details->ride_no }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <td>
                                                            :
                                                            @if($ride_Details->user_name != Null)
                                                                {{ ucwords(strtolower($ride_Details->user_name)) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Contact Number</th>
                                                        <td>
                                                            :
                                                            @if((isset($user_details)) && $user_details->contact_number != Null)
                                                                {{ App\model\User::ContactNumber2Stars($user_details->contact_number) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Email</th>
                                                        <td>
                                                            :
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
                                                            @if($ride_Details->driver_name != Null)
                                                                {{ ucwords(strtolower($ride_Details->driver_name)) }}
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
                                                    {{--<tr>
                                                        <th>Ride Time</th>
                                                        <td>
                                                            : {{ Carbon\Carbon::parse($ride_Details->pickup_datetime)->format('d F Y h:i:s') }} </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Ride End Time</th>
                                                        <td>
                                                            : {{ Carbon\Carbon::parse($ride_Details->destination_datetime)->format('d F Y h:i:s') }}</td>
                                                    </tr>--}}
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
                                                        <th>Pickup Address</th>
                                                        <td>: {{ $ride_Details->pickup_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Destination Address</th>
                                                        {{--<td>: Sydney Fish Market, 25 Bank St, Pyrmont NSW 2009, Australia</td>--}}
                                                        <td>: {{ $ride_Details->destination_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Vehicle Type</th>
                                                        <td>
                                                            : @if($ride_Details->vehicle_type_name != Null)
                                                                {{ ucwords(strtolower($ride_Details->vehicle_type_name)) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
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
                                                        <th>Base Fare</th>
                                                        <td class="">
                                                            <span class="currency"></span> {{ $ride_Details->base_fare }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Distance</th>
                                                        <td> {{ $ride_Details->total_distance }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tax</th>
                                                        <td class=""><span
                                                                    class="currency"></span> {{ $ride_Details->tax }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>SubTotal</th>
                                                        <td class=""> {{ isset($total)? $total : 0 }} </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Discount</th>
                                                        <td class="">
                                                            <span class="currency"></span> {{ isset($discount)? $discount : 0 }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total</th>
                                                        <td class="">
                                                            <span class="currency"></span> {{ isset($grand_total)? $grand_total : 0 }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Type</th>
                                                        <td>
                                                            : {{ isset($ride_Details) ? ($ride_Details->payment_type == 1 ? "Cash" : ($ride_Details->payment_type == 2 ? "Card" : ($ride_Details->payment_type == 3 ? "Wallet" : ''))) : '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Status</th>
                                                        <td>
                                                            : {{ ($ride_Details->payment_status == 1) ? 'Paid' : 'Not Paid' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status</th>
                                                        {{--<td class="ride-status"> :--}}
                                                        {{--<span class="{{ $ride_Details->status }}"> {{ $ride_Details->status }} </span>--}}
                                                        {{--</td>--}}

                                                        {{--@if($ride_Details->status == 0)--}}
                                                        {{--@php $status = "pending"; @endphp--}}
                                                        {{--@elseif($ride_Details->status == 1)--}}
                                                        {{--@php $status = "approved"; @endphp--}}
                                                        {{--@elseif($ride_Details->status == 2)--}}
                                                        {{--@php $status = "running"; @endphp--}}
                                                        {{--@elseif($ride_Details->status == 3)--}}
                                                        {{--@php $status = "completed"; @endphp--}}
                                                        {{--@elseif($ride_Details->status == 4)--}}
                                                        {{--@php $status = "cancelled"; @endphp--}}
                                                        {{--@endif--}}


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
                                                        <td class="ride-status">:
                                                            <span id="ride_status_change"
                                                                  class="{{ $ride_status }}"> {{ ucwords($ride_status) }}</span>
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
                <!-- Page body end -->
            </div>
        </div>
    </div>
@endsection
@section('page-js')
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
        <script>
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
                                    //     swal("Cancelled", "Ride Status Not Updated Something Wrong!", "error");
                                    //     console.log(result);
                                    // }
                                }
                            })
                        } else {
                            swal("Cancelled", "Ride Status Not Updated", "error");
                        }
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
    @php $map_key = \App\model\GeneralSettings::first() @endphp
    <script src="https://maps.googleapis.com/maps/api/js?key={{ (isset($map_key) && ($map_key->map_key != Null))? $map_key->map_key : 0 }}&libraries=places&callback=initMap"
            async defer></script>
@endsection

