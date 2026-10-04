@extends('admin.layout.account_billing_admin')
@section('title')
    Delivery Details
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
                            <h5>Delivery Details</h5>
                            <span>Delivery Details</span>
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
                        <div class="card">
                            {{--<div class="card-header">--}}
                            {{--<h5>@if($slug == "courier-service") Delivery @else Ride @endif route on map</h5>--}}
                            {{--</div>--}}
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
                                                                {{ $user_details->contact_number }}
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
                                                                {{ $user_details->email }}
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
                                                                {{ $driver_details->contact_number }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Driver Email</th>
                                                        <td>:
                                                            @if((isset($driver_details)) && $driver_details->email != Null)
                                                                {{ $driver_details->email }}
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    {{--<tr>
                                                        <th>Delivery Start Time</th>
                                                        <td>
                                                            : {{ Carbon\Carbon::parse($ride_Details->pickup_datetime)->format('d F Y h:i:s') }} </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Delivery End Time</th>
                                                        <td>
                                                            : {{ Carbon\Carbon::parse($ride_Details->destination_datetime)->format('d F Y h:i:s') }}</td>
                                                    </tr>--}}
                                                    <tr>
                                                        <th>Ride Type</th>
                                                        <td>: {{ isset($ride_Details) && $ride_Details->ride_type == 1 ? "Schedule Order" : 'Book Now' }}</td>
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
                                        <div class="ride-detail-table-header">
                                            <h5>Delivery route on map</h5>
                                        </div>
                                        <div id="map" class="set-map"></div>
                                    </div>

                                    <div class="col-lg-12 col-xl-6">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Delivery Recipient Detail</h5>
                                            </div>
                                            <table class="table">
                                                <tr>
                                                    <th>Recipient Name</th>
                                                    <td>
                                                        : {{ isset($courier_details)? ucwords(strtolower($courier_details->recipient_name)) : "----" }}</td>
                                                </tr>
                                                <tr>
                                                    <th>House Name</th>
                                                    <td>
                                                        :
                                                        @if(isset($courier_details) && $courier_details->house_name != Null)
                                                            {{ ucwords(strtolower($courier_details->house_name)) }}
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
                                                <tr>
                                                    <th>Width / Height</th>
                                                    <td>
                                                        : @if(isset($courier_details)) {{ $courier_details->goods_width }}
                                                        <i class='fa fa-close'></i> {{ $courier_details->goods_height }} @else
                                                            ---- @endif
                                                    </td>
                                                </tr>
                                                {{--<tr>--}}
                                                {{--<th>Goods Width</th>--}}
                                                {{--<td>--}}
                                                {{--: {{ "d" }} cm</td>--}}
                                                {{--</tr>--}}
                                                <tr>
                                                    <th>Goods Weight</th>
                                                    <td>
                                                        : {{ isset($courier_details)? $courier_details->goods_weight .' kg' : "----" }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Delivery Instruction</th>
                                                    <td>
                                                        : @if(isset($courier_details) && $courier_details->delivery_instruction != Null){{ $courier_details->delivery_instruction }} @else
                                                            ---- @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Description</th>
                                                    <td>
                                                        : {{ isset($courier_details)? $courier_details->description : "----" }}</td>
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
                                                        <th>Base Fare :</th>
                                                        <td class="currency"> {{ $ride_Details->vehicle_cost_for_km }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Distance :</th>
                                                        <td> {{ $ride_Details->total_distance }}</td>
                                                    </tr>
                                                    {{--<tr>--}}
                                                    {{--<th>Ride Fare</th>--}}
                                                    {{--<td>: $ {{ $ride_Details->base_fare }}</td>--}}
                                                    {{--</tr>--}}
                                                    <tr>
                                                        <th>Tax :</th>
                                                        <td class="currency"> {{ $ride_Details->tax }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>SubTotal :</th>
                                                        <td class="currency">{{ isset($total)? $total : 0 }}</td>
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
                                                        <th>Payment Type</th>
                                                        <td>: {{ ucwords($ride_Details->payment_type) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Status</th>
                                                        <td>
                                                            : {{ ($ride_Details->payment_type == 1) ? 'Paid' : 'Not Paid' }}</td>
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
                                                            <span class="{{ $ride_status }}"> {{ ucwords($ride_status) }}</span>
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

