@extends('admin.layout.other_service')
@section('title')
    Order Details
@endsection
@section('page-css')
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="external-horizontal-nav">
                @include('admin.include.other-service-horizontal-navbar')
            </div>
        @elseif(Illuminate\Support\Facades\Auth::guard("on_demand")->check() && isset($slug))
            <div class="external-horizontal-nav">
                @include('admin.include.other-service-provider-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                <i class="feather icon-list bg-c-green"></i>
                                <div class="d-inline">
                                    <h5>Order Details</h5>
                                    <span>Full Order Details</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            @if(isset($orders_details))
                                @if(in_array($orders_details->status, $orders_status_array ))
                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                        <button class="btn btn-danger waves-effect waves-light btn-right m-b-0 order_cancel"
                                                orderid="{{$orders_details->id}}" updatestatus="5">Cancel
                                        </button>
                                    @endif
                                    <button class="btn btn-success waves-effect waves-light btn-right m-b-0 order_completed"
                                            orderid="{{$orders_details->id}}" updatestatus="9"
                                            style="margin-right: 10px;">Completed
                                    </button>
                                @endif
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

                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && isset($orders_details) && $orders_details->status == 5)
                            @if($orders_details->payment_type == 2 || $orders_details->payment_type == 3)
                                <div class="card">
                                    <div class="card-header">
                                        <h5 style="font-size: 18px; margin-left: 10px;margin-top: 30px">Refund Details</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive ride-detail-table" style="padding: 0 5px">
                                                    <table class="table">
                                                        <tr>
                                                            <th style="width: 15%">Order Amount:</th>
                                                            <td class="currency">
                                                                {{ isset($orders_details) ? $orders_details->total_pay : '' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="width: 15%">Refund Status:</th>
                                                            <td class="order-status">
                                                                @if(isset($orders_details) && ($orders_details->user_refund_status == 1))
                                                                    <span class="completed">completed</span>
                                                                @else
                                                                    <span class="cancelled">Pending</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th style="width: 15%">Cancel Charge:</th>
                                                            <td class="currency"> {{ isset($orders_details) ? number_format($orders_details->cancel_charge,2) : '0.00' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="width: 15%">Refund Amount:</th>
                                                            <td class="currency"> {{ isset($orders_details) ? number_format($orders_details->refund_amount,2) : '0.00' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h5>Order Details</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            @if(isset($orders_details))
                                                <table class="table">
                                                    <tr>
                                                        <th>Order ID.</th>
                                                        <td>#{{ $orders_details->order_no }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Provider Name</th>
                                                        <td>{{ ($orders_details->provider_name == Null)? "----" : ucwords($orders_details->provider_name) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <td>{{ ($orders_details->user_name == Null)? "----" : ucwords($orders_details->user_name) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer Address</th>
                                                        <td style="width: 80%;">{{ $orders_details->delivery_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Phone No</th>
                                                        <td>{{$orders_details->country_code}} {{ App\model\User::ContactNumber2Stars($orders_details->contact_number) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Order Date</th>
                                                        <td>{{ (Illuminate\Support\Facades\Auth::guard("on_demand")->check())?\App\Classes\NotificationClass::convertTimezone($orders_details->created_at,"",$orders_details->booking_time_zone,"d")  : $orders_details->created_at }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Booked Order Date Time</th>
                                                        <td>{{ $orders_details->service_date.' '.date("h:i A",strtotime($orders_details->book_start_time))." - ".date("h:i A",strtotime($orders_details->book_end_time)) }}
                                                            {{isset($orders_details->booking_time_zone)?App\model\User::timezonedetails($orders_details->booking_time_zone):""}} </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Additional Remarks</th>
                                                        <td>{{ isset($orders_details->remark) ? $orders_details->remark : '----' }}</td>
                                                    </tr>
                                                    @if(isset($orders_details) && in_array($orders_details->status, [4,5,10]))
                                                        <tr>
                                                            <th>Cancel By</th>
                                                            <td>{{ isset($orders_details) ? ucwords($orders_details->cancel_by) : '-----' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Cancel Reason</th>
                                                            <td>{{ isset($orders_details) ? $orders_details->cancel_reason : '-----' }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>Total Amount</th>
                                                        <td class="">
                                                            <span class="currency"></span> {{ ($orders_details->total_pay == 0)? "0" : $orders_details->total_pay }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Type</th>
                                                        <td>
                                                            @if($orders_details->payment_type == 1)
                                                                Cash
                                                            @elseif($orders_details->payment_type == 2)
                                                                Card
                                                            @elseif($orders_details->payment_type == 3)
                                                                Wallet
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
{{--                                                        <td>{{ ($orders_details->payment_type == 1) ? "Cash" : ($orders_details->payment_type == 2)? "Card" : ($orders_details->payment_type == 3)? "Wallet" : "----" }}</td>--}}
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Status</th>
                                                        <td>{{ ($orders_details->payment_status  == 1)? "Completed" : "Pending"}}</td>
                                                    </tr>
{{--                                                    <tr>--}}
{{--                                                        <th>Order Type</th>--}}
{{--                                                        <td>{{ ($orders_details->order_type == 1)? "Schedule Order" : "Book Now" }}</td>--}}
{{--                                                    </tr>--}}
                                                    <tr>
                                                        <th>Order Status</th>
                                                        <td><span id="order-status">{{ $orders_status }}</span></td>
                                                    </tr>
                                                </table>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Package Details</h5>
                                            </div>
                                            <table class="table">
                                                <tr>
                                                    <th>Category Name</th>
                                                    <th>Package Name</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total Cost</th>
                                                </tr>
                                                @if(isset($orders_details))
                                                    @if(isset($package_list))
                                                        @foreach($package_list as $key => $package)
                                                            <tr>
                                                                <td>
                                                                    {{ $package->sub_category_name }}
                                                                </td>
                                                                <td>
                                                                    {{ $package->package_name }}
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ $package->price_for_one }}
                                                                </td>
                                                                <td class="">
                                                                    {{ $package->num_of_items }}
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ round($package->price_for_one * $package->num_of_items,2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    <tr>
                                                        <td colspan="3"></td>
                                                        <td>
                                                            Item Total
                                                        </td>
                                                        <td class="">
                                                            <span class="currency"></span> {{ $orders_details->total_item_cost }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3"></td>
                                                        <td>
                                                            Promocode Discount
                                                        </td>
                                                        <td>
                                                            <span class="currency"> {{ $orders_details->promo_code_discount != null ?  $orders_details->promo_code_discount : "0.00" }}</span>
                                                        </td>
                                                    </tr>
                                                        <tr>
                                                            <td colspan="3"></td>
                                                            <td>
                                                                Refferal Discount
                                                            </td>
                                                            <td>
                                                                <span class="currency"> {{ $orders_details->refer_discount != null ?  $orders_details->refer_discount : "0.00" }}</span>
                                                            </td>
                                                        </tr>
                                                    <tr>
                                                        <td colspan="3"></td>
                                                        <td>
                                                            Tax
                                                        </td>
                                                        <td class="">
                                                            <span class="currency"></span> {{ $orders_details->tax }}
                                                        </td>
                                                    </tr>
                                                        <tr>
                                                            <td colspan="3"></td>
                                                            <td>
                                                                Tip
                                                            </td>
                                                            <td class="currency">
                                                                {{ ($orders_details->tip > 0)?$orders_details->tip:0 }}
                                                            </td>
                                                        </tr>
                                                    <tr>
                                                        <td colspan="3"></td>
                                                        <td>
                                                            <h3>Total Pay</h3>
                                                        </td>
                                                        <td>
                                                            <h3 class=""><span
                                                                        class="currency"></span> {{ $orders_details->total_pay }}
                                                            </h3>
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
                            url: '{{ route("get:admin:other_service_user_refund_amount_settle") }}',
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
    </script>

    <script>
            $(document).on('click', '.order_cancel', function (e) {
                e.preventDefault();
                var id = $(this).attr('orderid');
                var update_status = $(this).attr('updatestatus');
                var txt, title, status, url;
                status = "Cancel";

                @if(Illuminate\Support\Facades\Auth::guard("on_demand")->check())
                    url = '{{ route("get:provider:update_other_service_order_status") }}';
                @else
                    url = '{{ route("get:admin:update_other_service_order_status") }}';
                @endif

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
                                        data: {id: id, update_status: update_status, reason: inputValue},
                                        success: function (result) {
                                            if (result.success == true) {
                                                $(".order_cancel").css({"display": "none"});
                                                $(".order_completed").css({"display": "none"});

                                                $("#order-status").empty();
                                                $("#order-status").text("Cancelled");
                                                swal("success!", "You " + status + " order reason is: " + inputValue, "success");
                                            } else {
                                                swal("Warning", result.message, "warning");
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

            $(document).on('click', '.order_completed', function (e) {
                e.preventDefault();
                var id = $(this).attr('orderid');
                var update_status = $(this).attr('updatestatus');
                var txt, title, status, url;
                status = "Completed";

                @if(Illuminate\Support\Facades\Auth::guard("on_demand")->check())
                    url = '{{ route("get:provider:update_other_service_order_status") }}';
                @else
                    url = '{{ route("get:admin:update_other_service_order_status") }}';
                @endif

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
                            $.ajax({
                                type: 'get',
                                url: url,
                                data: {id: id, update_status: update_status},
                                success: function (result) {
                                    if (result.success == true) {
                                        $(".order_cancel").css({"display": "none"});
                                        $(".order_completed").css({"display": "none"});

                                        $("#order-status").empty();
                                        $("#order-status").text("Completed");
                                        swal("Success", "Order status update successfully", "success");

                                    } else {
                                        // swal("Cancelled", "Order Status Not Updated Something Wrong!", "error");
                                        swal("Warning", result.message, "warning");
                                        console.log(result);
                                    }
                                }
                            })
                        } else {
                            swal("Cancelled", "Order Status Not Updated", "error");
                        }
                    });
            });
        </script>

@endsection

