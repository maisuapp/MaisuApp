{{--orders--}}
<style>
    #map {
        z-index: 0;
    }

    .set-map {
        height: 300px;
        margin-bottom: 30px;
    }
</style>
<div class="col-md-12 ride-detail-page">
    <div class="pcoded-content">
        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    @if(isset($slug) && $slug == "courier-service") Delivery @else Ride @endif route on map
                                        @if(isset($ride_Details))
                                            @if(in_array($ride_Details['status'], [0,1,2] ))
                                                <a href="#" orderid="{{$ride_Details->id}}" updatestatus="4" orderslug="{{ $slug }}" class="btn btn-outline-secondary btnColor btn-sm float-right ride_cancel">Cancel</a>
                                            @endif
                                        @endif
                                </h5>
                            </div>
                            <div class="card-block">
                                <div class="row m-0">
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
                                                                {{ $user_details->country_code.$user_details->contact_number }}
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
                                                                {{ $driver_details->country_code.$driver_details->contact_number }}
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
                                                    <tr>
                                                        <th>Ride Booking Time</th>
                                                        <td>
                                                            : {{--{{ Carbon\Carbon::parse($ride_Details->created_at)->format('d F Y h:i:s') }}--}}
                                                            {{ isset($ride_Details->created_at)?date("F j, Y, g:i a",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($ride_Details->created_at))):"" }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($ride_Details) && $ride_Details->ride_type == 1)
                                                        <tr>
                                                            <th>Ride Schedule Time</th>
                                                            <td>
                                                                : {{--{{ Carbon\Carbon::parse($ride_Details->pickup_datetime)->format('d F Y h:i:s') }}--}}
                                                                {{ isset($ride_Details->pickup_datetime)?date("F j, Y, g:i a",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($ride_Details->pickup_datetime))):"" }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    {{--<tr>
                                                        <th>Ride End Time</th>
                                                        <td>
                                                            : {{ Carbon\Carbon::parse($ride_Details->destination_datetime)->format('d F Y h:i:s') }}</td>
                                                    </tr>--}}
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
                                                            <span class="currency"></span> {{ $ride_Details->vehicle_cost_for_km }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Distance</th>
                                                        <td> {{ isset($ride_Details->total_distance)?$ride_Details->total_distance. " Km":"--" }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tax</th>
                                                        <td class=""><span
                                                                class="currency"></span> {{ $ride_Details->tax }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>SubTotal</th>
                                                        <td class="currency"> {{ isset($total)? $total : 0 }} </td>
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
                                                            : @if(isset($ride_Details))
                                                                @if($ride_Details->payment_type == 1)
                                                                    Cash
                                                                @elseif($ride_Details->payment_type == 2)
                                                                    Card
                                                                @elseif($ride_Details->payment_type == 3)
                                                                    Wallet
                                                                @else
                                                                    ----
                                                                @endif
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>Payment Status</th>
                                                        <td>
                                                            : {{ ($ride_Details->payment_status == 1) ? 'Paid' : 'Not Paid' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status</th>
                                                        <?php
                                                        if ($ride_Details->status == 0) {
                                                            $bg_status_class = "pending";
                                                            $title = "Pending";
                                                        } elseif (in_array($ride_Details->status, [1,2,3])) {
                                                            $bg_status_class = "approved";
                                                            $title = "Approve";
                                                        } elseif (in_array($ride_Details->status, [4])) {
                                                            $bg_status_class = "cancelled";
                                                            $title = "Cancelled";
                                                        } elseif (in_array($ride_Details->status, [5, 6, 7,8])) {
                                                            $bg_status_class = "running";
                                                            $title = "running";
                                                        } elseif ($ride_Details->status == 9) {
                                                            $bg_status_class = "completed";
                                                            $title = "Completed";
                                                        } else {
                                                            $bg_status_class = "";
                                                        }
                                                        ?>
                                                        <td class="ride-status ">:
                                                            <span class="small order-status">
                                                                <span class="{{$bg_status_class}}">
                                                                     {{ str_replace('-',' ',ucwords(trim($title))) }}
                                                                </span>
                                                            </span>
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
</div>




@php $map_key = \App\model\GeneralSettings::first() @endphp
<script>
    function initMap() {
        var lt = "{{ ((isset($map_key)) && $map_key->map_lat != Null ) ? $map_key->map_lat : ''  }}";
        var lg = "{{ ((isset($map_key)) && $map_key->map_long != Null) ? $map_key->map_long : ''  }}";
        if (lt == "" || lg == "") {
            var center = {lat: 22.3039, lng: 70.8022};
        } else {
            var center = {lat: lt - 0, lng: lg - 0};
        }

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 7,
            center: center
            //center: {lat: 14.5995, lng: 120.9842}
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

@if (Illuminate\Support\Facades\Auth::guard("user")->check())
    <script type="text/javascript">
        $(document).on('click', '.ride_cancel', function (e) {
            e.preventDefault();
            var id = $(this).attr('orderid');orderslug
            var orderslug = $(this).attr('orderslug');
            var update_status = $(this).attr('updatestatus');
            var txt, title, status, url;
            status = "Cancel";

            url = '{{ route("get:transport_update_ride_status") }}';
            title = status + " Order?";
            txt = "if press yes then " + status + " order!";
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
                                title: status + " Order!",
                                text: "if " + status + " order then Write " + status + " reason:",
                                type: "input",
                                showCancelButton: true,
                                closeOnConfirm: false,
                                animation: "slide-from-top",
                                inputPlaceholder: status + " Reason"
                            },
                            function (inputValue) {
                                if (inputValue === false) return false;
                                if (inputValue === "") {
                                    swal.showInputError("You need to write " + status + " order!");
                                    return false
                                }
                                $.ajax({
                                    type: 'get',
                                    url: url,
                                    data: {id: id, update_status: update_status, reason: inputValue, orderslug : orderslug},
                                    success: function (result) {
                                        if (result.success == true) {

                                            swal("success!", "You " + status + " order reason is: " + inputValue, "success");
                                            location.reload();

                                        } else {
                                            console.log(result);
                                        }
                                    }
                                })
                            });
                    } else {
                        swal("Cancelled", "Cancel Order", "error");
                    }
                });
        });
    </script>
@endif

<script src="https://maps.googleapis.com/maps/api/js?key={{ (isset($map_key) && ($map_key->map_key != Null))? $map_key->map_key : 0 }}&libraries=places&callback=initMap"></script>




