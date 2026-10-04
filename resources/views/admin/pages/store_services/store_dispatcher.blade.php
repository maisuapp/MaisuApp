@extends('admin.layout.super_admin')
@section('title')
    Dispatcher
@endsection
@section('page-css')
    <style>
        .rm-pending {
            padding: 0;
        }
        .order-tab {
            border-right: 1px solid #4389e9;
            border-top: 1px solid #4389e9;
            border-bottom: 1px solid #4389e9;
            /*padding: 15px 0;*/
            /*cursor: pointer;*/
        }
        .order-tab a {
            color: #4389e9;
            padding: 15px 0;
            text-align: center;
            width: 100%;
            font-size: 15px;
            font-weight: bold;
            display: inline-block;
        }
        .order-tab:first-child {
            border-left: 1px solid #4389e9;
        }
        .bg-order-tab {
            background-color: #4389e9;
        }
        .bg-order-tab a {
            color: white;
        }
        .order_list {
            padding: 0;
        }
        .order_status span {
            padding-right: 20px;
        }
        .order_right .rm-pending {
            padding: 2px 0;
        }
        .order_right h4 {
            margin: 0;
        }
        .btn-order-status {
            padding: .375rem .75rem;
            margin: 5px;
        }

        /*Time count down*/
        .style {
            width: 100%;
            font-family: sans-serif;
            font-weight: bold;
            border-style: solid;
        }
        .size_sm {
            font-size: 20px;
            border-width: 3px;
            border-radius: 3px;
        }

        /*swal shopper style*/
        .shopper-span {
            font-size: 22px;
            color: #585858;
            font-weight: 400;
        }
        .shopper-span input[type='radio'] {
            height: 20px;
            width: 20px;
            color: #4099ff;
        }
        .shopper-span label {
            vertical-align: middle;
        }
        .sw-title {
            color: gray;
            margin-bottom: 24px;
        }
        .dispatcher-btn {
            width: 100%;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="other-service-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                @if(!isset($service_category))
                                    <i class="feather icon-list bg-c-blue"></i>
                                @endif
                                <div class="d-inline">
                                    @if(isset($service_category))
                                        <h5 style="margin-top: 10px">
                                            <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" height="40" width="40" style="margin-right: 5px">
                                            {{ isset($service_category) ? ucwords(strtolower($service_category->name)) : '' }}
                                            Dispatcher
                                        </h5>
                                    @else
                                        <h5>Dispatcher</h5>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="btn m-b-0 btn-right">
                                <b>Refresh After : </b>&nbsp; <span id="ms_timer"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="form-group col-sm-12 rm-pending">
                            <div class="card">
                                {{--<div class="card-block">--}}
                                <div class="row" style="padding: 0 15px">
                                    {{--<div class="col-lg-12">--}}
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "new") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"new"]) : route('get:store-admin:store_service_dispatcher','new') }}">
                                            New (<span class="new">{{ $pending_orders }}</span>)
                                        </a>
                                    </div>
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "approved") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"approved"]) : route('get:store-admin:store_service_dispatcher','approved')}}">
                                            Approved (<span class="approved">{{ $approved_orders }}</span>)
                                        </a>
                                    </div>
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "processing") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"processing"]) : route('get:store-admin:store_service_dispatcher','processing')}}">
                                            Order Ready (<span class="processing">{{ $processing_orders }}</span>)
                                        </a>
                                    </div>
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "ongoing") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"ongoing"]) : route('get:store-admin:store_service_dispatcher','ongoing')}}">
                                            Ongoing (<span class="ongoing">{{ $ongoing_orders }}</span>)
                                        </a>
                                    </div>
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "completed") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"completed"]) : route('get:store-admin:store_service_dispatcher','completed')}}">
                                            Completed (<span class="completed">{{ $completed_orders }}</span>)
                                        </a>
                                    </div>
                                    <div
                                            class="col-sm-2 order-tab {{ isset($order_slug) && ($order_slug == "cancelled") ? "bg-order-tab" : '' }}">
                                        <a class="render_link"
                                           href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_service_dispatcher',[$slug,"cancelled"])  : route('get:store-admin:store_service_dispatcher','cancelled') }}">
                                            Cancelled (<span class="cancelled">{{ $cancelled_orders }}</span>)
                                        </a>
                                    </div>
                                    {{--</div>--}}
                                </div>
                                {{--</div>--}}
                            </div>
                        </div>
                        @foreach($store_order_list as $store_order)
                            <div class="form-group col-sm-12 rm-pending" id="hide_{{ $store_order->id }}">
                                <div class="card">
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-lg-12 order_list">
                                                <div class="row" style="padding: 0 15px;">
                                                    <div class="col-sm-3">
                                                        <img @if($store_order->store_banner != Null) src="{{ asset('assets/images/store-images/'.$store_order->store_banner) }}" @endif width="100%" height="137">
                                                    </div>
                                                    <div class="col-sm-7 order_right">
                                                        <div class="col-sm-12 rm-pending">
                                                            <h4><b>{{ $store_order->store_name }}
                                                                    @if($store_order->taken_away_type == 2) <span style="color: darkorange; font-size: 14px"> (takeaway order)</span> @endif
                                                                    @if(isset($store_order->order_type) && $store_order->order_type == 1) <span style="color: #ec8f09; font-size: 14px"> (schedule order)</span> @endif
                                                                </b></h4>
                                                        </div>
                                                        <div class="col-sm-12 rm-pending order_status">
                                                            <span><b>Status:</b> {{ $order_slug }}</span>
                                                            <span><b>Order No:</b> #{{ $store_order->order_no }}</span>
                                                            <span style="color: red"><i class="fa fa-clock-o" aria-hidden="true"></i> {{ $store_order->time_elapsed_string($store_order->created_at) }}</span>
                                                        </div>
                                                        <div class="col-sm-12 rm-pending">
                                                            <span><b>Order List:</b> {{ \Illuminate\Support\Str::limit($store_order->order_product_list, $limit = 60, $end = '...') }}</span>
                                                        </div>
                                                        <div class="col-sm-12 rm-pending">
                                                            <span><i class="fas fa-map-marker-alt" style="color: green"></i> {{ $store_order->pickup_address }}</span>
                                                        </div>
                                                        <div class="col-sm-12 rm-pending">
                                                            <span><i class="fas fa-map-marker-alt" style="color: red"></i> {{ $store_order->destination_address }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        @if(isset($order_slug) && ($order_slug == "new"))
                                                            <button orderid="{{$store_order->id}}" updatestatus="2" class="btn btn-success waves-effect waves-light btn-order-status order-approved dispatcher-btn">Approve</button>
                                                        @endif
                                                        @if(isset($order_slug) && ($order_slug == "approved"))
                                                            <button orderid="{{$store_order->id}}" updatestatus="5" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">Order Ready</button>
                                                        @endif
                                                        @if(isset($order_slug) && ($order_slug == "processing"))
                                                            @if($store_order->taken_away_type == 2)
                                                                <button orderid="{{$store_order->id}}" updatestatus="8" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">{{ ($store_order->taken_away_type == 2)? "Ready for Pickup" : "Ongoing" }}</button>
                                                            @else
                                                                @if($store_order->delivery_people_id == Null && $store_order->taken_away_type == 1)
                                                                    <a href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() ? route('get:admin:store_manual_assign_driver',[$slug,$store_order->id]) : route('get:store-admin:store_manual_assign_driver',[$store_order->id])}}" class="btn btn-success waves-effect waves-light btn-order-status render_link dispatcher-btn">Assign Driver</a>
                                                                @else
                                                                    @if((Illuminate\Support\Facades\Auth::guard("admin")->check()) && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                                                                        @if((isset($general_settings)) && $general_settings != Null && isset($store_order))
                                                                            @if(($general_settings->max_driver_reassign > $store_order->is_driver_reassign) && ($store_order->delivery_people_id != Null) && in_array($store_order->order_delivery_status,[1,2,3,5]))
                                                                                <a href="{{ route('get:admin:store_manual_assign_driver',[ "slug" => $slug, "order_id" => $store_order->id, "redirect_type" => "dispatcher"]) }}"
                                                                                   class="btn btn-dark waves-effect waves-light btn-order-status render_link dispatcher-btn">
                                                                                    Re-Assign
                                                                                </a>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                    <button orderid="{{$store_order->id}}" updatestatus="8" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">{{ ($store_order->taken_away_type == 2)? "Ready for Pickup" : "Ongoing" }}</button>
                                                                @endif
                                                            @endif
                                                        @endif

                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            @if(isset($order_slug) && ($order_slug == "ongoing"))
                                                                @if(($store_order->taken_away_type == 2))
                                                                    <button orderid="{{$store_order->id}}" updatestatus="9" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">Order Picked-Up</button>
                                                                @else
                                                                    @if((Illuminate\Support\Facades\Auth::guard("admin")->check()) && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                                                                        @if((isset($general_settings)) && $general_settings != Null && isset($store_order))
                                                                            @if(($general_settings->max_driver_reassign > $store_order->is_driver_reassign) && ($store_order->delivery_people_id != Null) && in_array($store_order->order_delivery_status,[1,2,3,5]))
                                                                                <a href="{{ route('get:admin:store_manual_assign_driver',[ "slug" => $slug, "order_id" => $store_order->id, "redirect_type" => "dispatcher"]) }}"
                                                                                   class="btn btn-dark waves-effect waves-light btn-order-status render_link dispatcher-btn">
                                                                                    Re-Assign
                                                                                </a>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                    <button orderid="{{$store_order->id}}" updatestatus="9" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">Completed</button>
                                                                @endif
                                                            @endif
                                                        @else
                                                            @if(isset($order_slug) && ($order_slug == "ongoing") && ($store_order->taken_away_type == 2))
                                                                <button orderid="{{$store_order->id}}" updatestatus="9" class="btn btn-success waves-effect waves-light btn-order-status update-order-status dispatcher-btn">Order Picked-Up</button>
                                                            @endif
                                                        @endif

                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            <a href="{{ route('get:admin:store_order_details',[$slug,$store_order->id]) }}"
                                                               class="btn btn-warning waves-effect waves-light btn-order-status render_link dispatcher-btn">
                                                                View Details
                                                            </a>
                                                        @else
                                                            <a href="{{ route('get:store-admin:store_order_details',[$store_order->id]) }}"
                                                               class="btn btn-warning waves-effect waves-light btn-order-status render_link dispatcher-btn">
                                                                View Details
                                                            </a>
                                                        @endif
                                                        @if(isset($order_slug) && ($order_slug == "new"))
                                                            <button orderid="{{$store_order->id}}" updatestatus="3"
                                                                    class="btn btn-danger waves-effect waves-light btn-order-status order_reject_cancel dispatcher-btn">
                                                                Reject
                                                            </button>

                                                        @endif
                                                        {{--@if(isset($order_slug) && ($order_slug == "approved"))--}}
                                                        {{--<button orderid="{{$store_order->id}}" updatestatus="4"--}}
                                                        {{--class="btn btn-danger waves-effect waves-light btn-order-status order_reject_cancel">--}}
                                                        {{--Cancel--}}
                                                        {{--</button>--}}
                                                        {{--@endif--}}
                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            @if(isset($order_slug) && ($order_slug == "approved") ||($order_slug == "processing") ||($order_slug == "ongoing") )
                                                                <button orderid="{{$store_order->id}}" updatestatus="4"
                                                                        checkstatus="{{ isset($order_slug)? (($order_slug == "approved")? 2 : (($order_slug == "processing")? 5 : (($order_slug == "ongoing")? 8 : 0) )) : 0 }}"
                                                                        class="btn btn-danger waves-effect waves-light btn-order-status order_reject_cancel dispatcher-btn">
                                                                    Cancel
                                                                </button>
                                                            @endif
                                                        @else
                                                            @if(isset($order_slug) && ($order_slug == "approved"))
                                                                <button orderid="{{$store_order->id}}" updatestatus="4"
                                                                        class="btn btn-danger waves-effect waves-light btn-order-status order_reject_cancel dispatcher-btn">
                                                                    Cancel
                                                                </button>
                                                            @endif
                                                        @endif

                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            @if(isset($order_slug) && ($order_slug == "cancelled"))
                                                                    <p style="text-align: center; margin-bottom: 0; margin-top: 10px;"><strong>Refund Status</strong></p>
                                                                 <span class="btn {{ ($store_order->user_refund_status == 1)? 'btn-outline-success' : 'btn-outline-danger' }} waves-effect waves-light btn-order-status dispatcher-btn">
                                                                    {{ ($store_order->payment_type != 1)? ($store_order->user_refund_status == 1)? "Completed" : "Pending" : "N/A" }}
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-js')

    <script>
        $(document).on('click', '.order_reject_cancel', function (e) {
            e.preventDefault();
            var id = $(this).attr('orderid');
            var update_status = $(this).attr('updatestatus');
            var checkstatus = $(this).attr('checkstatus');
            var txt, title, status, url;
            if (update_status == 3) {
                status = "Reject";
            } else {
                status = "Cancel";
            }
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                url = '{{ route("get:admin:store_order_status_update") }}';
            @else
                url = '{{ route("get:store-admin:store_order_status_update") }}';
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
                                        console.log(result);
                                        if (result.success == true) {
                                            if (update_status == 3) {
                                                $new_count = parseInt($('.new').text());
                                                $cancelled_count = parseInt($('.cancelled').text());
                                                $('.new').text($new_count - 1);
                                                $('.cancelled').text($cancelled_count + 1);
                                            } else {
                                                if (checkstatus == 2) {
                                                    $approved_count = parseInt($('.approved').text());
                                                    $cancelled_count = parseInt($('.cancelled').text());
                                                    $('.approved').text($approved_count - 1);
                                                    $('.cancelled').text($cancelled_count + 1);
                                                } else if (checkstatus == 5) {
                                                    $processing_count = parseInt($('.processing').text());
                                                    $cancelled_count = parseInt($('.cancelled').text());
                                                    $('.processing').text($processing_count - 1);
                                                    $('.cancelled').text($cancelled_count + 1);
                                                } else if (checkstatus == 8) {
                                                    $ongoing_count = parseInt($('.ongoing').text());
                                                    $cancelled_count = parseInt($('.cancelled').text());
                                                    $('.ongoing').text($ongoing_count - 1);
                                                    $('.cancelled').text($cancelled_count + 1);
                                                }

                                            }
//                                    location.reload();
                                            var new_id = "#hide_" + id;
                                            swal("success!", "You " + status + " order reason is: " + inputValue, "success");
                                            $(new_id).hide();
                                        } else {
                                            console.log(result);
                                        }
                                    }
                                })
                            });
                    } else {
                        swal("Cancelled", "Reject Order", "error");
                    }
                });
        });
        $(document).on('click', '.update-order-status', function (e) {
            e.preventDefault();
            var id = $(this).attr('orderid');
            var update_status = $(this).attr('updatestatus');
            var txt, title, status, url;
            if (update_status == 2) {
                status = "Approve";
            } else if (update_status == 5) {
                status = "Processing";
            } else if (update_status == 8) {
                status = "Ongoing";
            } else {
                status = "Completed";
            }
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                url = '{{ route("get:admin:store_order_status_update") }}';
            @else
                url = '{{ route("get:store-admin:store_order_status_update") }}';
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
//                                    location.reload();
                                    if (update_status == 2) {
                                        $new_count = parseInt($('.new').text());
                                        $approved_count = parseInt($('.approved').text());
                                        $('.new').text($new_count - 1);
                                        $('.approved').text($approved_count + 1);
                                    } else if (update_status == 5) {
                                        $approved_count = parseInt($('.approved').text());
                                        $processing_count = parseInt($('.processing').text());
                                        $('.approved').text($approved_count - 1);
                                        $('.processing').text($processing_count + 1);
                                    } else if (update_status == 8) {
                                        $processing_count = parseInt($('.processing').text());
                                        $ongoing_count = parseInt($('.ongoing').text());
                                        $('.processing').text($processing_count - 1);
                                        $('.ongoing').text($ongoing_count + 1);
                                    } else {
                                        $ongoing_count = parseInt($('.ongoing').text());
                                        $completed_count = parseInt($('.completed').text());
                                        $('.ongoing').text($ongoing_count - 1);
                                        $('.completed').text($completed_count + 1);
                                    }
                                    var new_id = "#hide_" + id;
                                    swal("Success", "order status update successfully", "success");
                                    $(new_id).hide();
                                } else {
                                    swal("Warning",result.message, "warning");
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Order Status Not Updated", "error");
                    }
                });
        });

        $(document).on('click', '.order-approved', function (e) {
            e.preventDefault();
            var id = $(this).attr('orderid');
            var update_status = $(this).attr('updatestatus');
            var txt, title, status, url;
            if (update_status == 2) {
                @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                    url = '{{ route("get:admin:store_order_status_update") }}';
                @else
                    url = '{{ route("get:store-admin:store_order_status_update") }}';
                @endif

                title = "Approve Order?";
                txt = "if press yes then approve order!";
                var inviteDevInput = '<form> <span class="shopper-span"> <input type="radio" checked name="shopper" id="radio1" value="0"> <label for="radio1"> Inner Shopper </label></span> <span class="shopper-span"> <input type="radio" name="shopper" id="radio2" value="1"> <label for="radio2"> Outer Shopper</label></span></form>';
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
                        if(isConfirm){
                            $.ajax({
                                type: 'get',
                                url: url,
                                data: {id: id, update_status: update_status},
                                success: function (result) {
                                    if (result.success == true) {
                                        $new_count = parseInt($('.new').text());
                                        $approved_count = parseInt($('.approved').text());
                                        $('.new').text($new_count - 1);
                                        $('.approved').text($approved_count + 1);

                                        var new_id = "#hide_" + id;
                                        swal("Success", "order status update successfully", "success");
                                        $(new_id).hide();
                                    } else {
                                        swal("Warning", "Approve Order", "warning");
                                    }
                                }
                            });
                        }
                    });
                }
            });
    </script>
    <script type="text/javascript" src="{{asset('assets/js/plugin/jquery.countdownTimer.js')}}"
            charset="UTF-8"></script>
    <script>
        $(function () {
            $('#ms_timer').countdowntimer({
                minutes: 0,
                seconds: 20,
                size: "sm",
                timeUp: timeisUp
            });

            function timeisUp() {
                cache_clear();
            }
        });

        function cache_clear() {
            var url = window.location.href;
            history.pushState({
                url: url
            }, url);
            window.location.href = url;
        }

    </script>
@endsection

