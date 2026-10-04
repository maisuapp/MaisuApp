@extends('admin.layout.super_admin')
@section('title')
    Order Details
@endsection
@section('page-css')
    <style>
        .md-perspective,
        .md-perspective body {
            height: 100%;
            overflow: hidden;
        }

        .md-perspective body {
            background: #222;
            -webkit-perspective: 600px;
            -moz-perspective: 600px;
            perspective: 600px;
        }

        .md-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            /*width: 50%;*/
            width: 30%;
            max-width: 630px;
            min-width: 300px;
            height: auto;
            z-index: 2000;
            visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            backface-visibility: hidden;
            -webkit-transform: translateX(-50%) translateY(-50%);
            -moz-transform: translateX(-50%) translateY(-50%);
            -ms-transform: translateX(-50%) translateY(-50%);
            transform: translateX(-50%) translateY(-50%);
        }

        .md-show {
            visibility: visible;
        }

        .md-overlay {
            position: fixed;
            width: 100%;
            height: 100%;
            visibility: hidden;
            top: 0;
            left: 0;
            z-index: 1000;
            opacity: 0;
            background: rgba(55, 58, 60, 0.65);
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .md-show ~ .md-overlay {
            opacity: 1;
            visibility: visible;
        }

        /* Content styles */
        .md-content {
            color: #666666;
            background: #fff;
            position: relative;
            border-radius: 3px;
            margin: 0 auto;
        }

        .md-content h3 {
            color: #fff;
            margin: 0;
            /*padding: 0.4em;*/
            padding: 0.6em 0.4em 0.6em 1em;
            text-align: left;
            font-weight: 400;
            font-size: 1.5em;
            opacity: 0.8;
            border-radius: 3px 3px 0 0;
        }

        .md-content > div {
            padding: 15px 25px 30px 25px;
            margin: 0;
            font-size: 1em;
            /*font-weight: 300;*/
            /*font-size: 1.15em;*/
        }

        .md-content > div > div {
            width: 40%;
            margin: 0 auto;
            padding: 10px 0;
            justify-content: space-around;
            display: flex;
        }

        .md-content > div > div > img {
            border-radius: 50%;
            padding: 4px;
            border: 2px solid #4e99fe;
        }

        .md-content > div ul {
            margin: 0;
            padding: 0 0 30px 0;
        }

        .md-content > div ul li {
            padding: 5px 0;
        }

        /* Individual modal styles with animations/transitions */
        .md-effect-1 .md-content {
            -webkit-transform: scale(0.7);
            -moz-transform: scale(0.7);
            -ms-transform: scale(0.7);
            transform: scale(0.7);
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .md-show.md-effect-1 .md-content {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            transform: scale(1);
            opacity: 1;
        }

        .md-trigger:hover {
            color: #64b0f2;
            cursor: pointer;
        }

        .md-trigger img:hover {
            opacity: 0.7;
            cursor: pointer;
        }

        .approve, .reject {
            cursor: pointer;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                <div class="other-service-horizontal-nav">
                    @include('admin.include.store-horizontal-navbar')
                </div>
            @endif
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                @if(!isset($service_category))
                                    <i class="feather icon-list bg-c-green"></i>
                                @endif
                                <div class="d-inline">
                                    @if(isset($service_category))
                                        <h5 style="margin-top: 10px">
                                            <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" height="40" width="40" style="margin-right: 5px">
                                            {{ isset($service_category) ? ucwords(strtolower($service_category->name)) : '' }}
                                            Order Details
                                        </h5>
                                    @else
                                        <h5>Order Details</h5>
                                        <span>Full Order Details</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            {{--<div style="position: absolute; right: 40px; top:90px; z-index: 1">--}}
                            <button value="print" onclick="PrintDiv();" style="padding:10px 19px" class="btn btn-primary btn-out-dotted waves-effect waves-light btn-square btn-right  m-b-0">
                                <i class="fas fa-print" data-toggle="tooltip" data-placement="top" title="Print" style="margin-right: 0"></i>
                            </button>
                            {{--</div>--}}
                            @if((Illuminate\Support\Facades\Auth::guard("admin")->check()) && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                                @if((isset($general_settings)) && $general_settings != Null && isset($order_details))
                                    @if(($general_settings->max_driver_reassign > $order_details->is_driver_reassign) && ($order_details->delivery_people_id != Null) && in_array($order_details->order_delivery_status,[1,2,3,5]))
                                        <a href="{{ route('get:admin:store_manual_assign_driver',[ "slug" => $slug, "order_id" => $order_details->id, "redirect_type" => "detail"]) }}" class="btn btn-dark waves-effect waves-light  btn-right m-b-0 m-r-10">
                                            Re-Assign
                                        </a>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content" id="divToPrint">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">

                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                            @if($order_details->status == 3 || $order_details->status == 4)
                                @if($order_details->payment_type == 2 || $order_details->payment_type == 3)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 style="font-size: 18px; margin-left: 10px;margin-top: 30px">Refund
                                                Details</h5>
                                            @if($order_details->user_refund_status == 0)
                                                <a href="#"
                                                   class="btn btn-success m-b-0 btn-right refund_settle"
                                                   order_id="{{$order_details->id}}">Refund Order</a>
                                            @endif
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="table-responsive ride-detail-table"
                                                         style="padding: 0 5px">
                                                        <table class="table">
                                                            <tr>
                                                                <th style="width: 15%">Order Amount:</th>
                                                                <td class="currency"> {{ isset($order_details) ? $order_details->total_pay : '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="width: 15%">Refund Status:</th>
                                                                <td class="order-status">
                                                                    @if(isset($order_details) && ($order_details->user_refund_status == 1))
                                                                        <span class="completed">completed</span>
                                                                    @else
                                                                        <span class="cancelled">Pending</span>
                                                                    @endif
                                                                    {{--{{ isset($order_details) ? ($order_details->user_refund_status == 1 ? "completed" : "pending") : '-----' }}--}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th style="width: 15%">Cancel Charge:</th>
                                                                <td class="currency"> {{ isset($order_details) ? number_format($order_details->cancel_charge,2) : '0.00' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th style="width: 15%">Refund Amount:</th>
                                                                <td class="currency"> {{ isset($order_details) ? number_format($order_details->refund_amount,2) : '0.00' }}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h5>Order Details</h5>
                                {{--<a --}}{{--href="{{ route('get:transport:transport_provider_ride_list') }}"--}}
                                {{--class="btn btn-success m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            {{--<div class="ride-detail-table-header">--}}
                                            {{--<h5>Ride Detail</h5>--}}
                                            {{--</div>--}}
                                            <table class="table tbl-details">
                                                <tr>
                                                    <th>Order ID.</th>
                                                    <td>{{ isset($order_details) ? "#".$order_details->order_no : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Store Name</th>
                                                    <td>{{ isset($order_details) ? $order_details->store_name : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <td>{{ isset($order_details) ? $order_details->user_name : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Customer Phone No</th>
                                                    <td>{{ isset($user_details) ? App\model\User::ContactNumber2Stars($user_details->contact_number) : '-----' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Pickup Address</th>
                                                    <td>{{ isset($store_details) ? $store_details->address : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Delivery Address</th>
                                                    <td>{{ isset($order_details) ? $order_details->delivery_address : '' }}
                                                    </td>
                                                </tr>

                                                @if(isset($order_details) && $order_details->shopper_type == 1)
                                                    @if(isset($shopper_details) )
                                                        <tr>
                                                            <th>Shopper Name</th>
                                                            <td>
                                                                <b>{{ ($shopper_details->shopper_name != Null)? $shopper_details->shopper_name : "----" }}</b>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Shopper Phone No</th>
                                                            <td>{{ ($shopper_details->shopper_contact_number != Null)? $shopper_details->shopper_contact_number : "----" }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                                @if(isset($order_details) && !in_array($order_details->status, [3,4,10]))
                                                    <tr>
                                                        <th>Delivery Person Name</th>
                                                        <td>
                                                            {{--{{ isset($driver_details) ? ucwords($driver_details->name) : '' }}--}}
                                                            @if(isset($driver_details))
                                                                <span title="Delivery Person Details" class="md-trigger"
                                                                      data-modal="modal-1" data-toggle="tooltip"
                                                                      provider_name="{{ ($driver_details->driver_name)? $driver_details->driver_name : '----' }}"
                                                                      contact_no="{{ ($driver_details->contact_number)? App\model\User::ContactNumber2Stars($driver_details->contact_number) : '----'}}"
                                                                      image="{{ asset('assets/images/profile-images/provider/'.$driver_details->avatar) }}"
                                                                      model_name="{{ $driver_details->model_name." ".$driver_details->vehicle_company }}"
                                                                      plat_no="{{ ($driver_details->plat_no)? $driver_details->model_year : '----' }}"
                                                                      model_year="{{ ($driver_details->model_year)? $driver_details->model_year : '----' }}"
                                                                      vehicle_color="{{ ($driver_details->vehicle_color)? $driver_details->vehicle_color : '----' }}"
                                                                      vehicle_type="{{ ($driver_details->vehicle_type_name)? $driver_details->vehicle_type_name : '----' }}"
                                                                >
                                                            <b>{{ $driver_details->driver_name }}</b>
                                                        </span>
                                                            @else
                                                                ----
                                                            @endif
                                                        </td>
                                                    </tr>
{{--                                                    <tr>--}}
{{--                                                        <th>Delivery Person Phone No</th>--}}
{{--                                                        <td>{{ isset($driver_details) ? $driver_details->contact_number : '-----' }}</td>--}}
{{--                                                    </tr>--}}
                                                @endif
                                                <tr>
                                                    <th>Additional Remarks</th>
                                                    <td>{{ isset($order_details) && $order_details->additional_request != Null ? $order_details->additional_reques : '----' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Order Date</th>
                                                    <td>{{ isset($order_details) ? $order_details->created_at : '' }}</td>
                                                </tr>
                                                @if(isset($order_details) && $order_details->order_type == 1 )
                                                    <tr>
                                                        <th>Schedule Order Date</th>
                                                        <td>{{ isset($order_details) ? $order_details->order_date_time : '' }}</td>
                                                    </tr>
                                                @endif
                                                @if(isset($order_details) && in_array($order_details->status, [3,4,10]))
                                                    <tr>
                                                        <th>Cancel By</th>
                                                        <td>{{ isset($order_details) ? ucwords($order_details->cancel_by) : '-----' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cancel Reason</th>
                                                        <td>{{ isset($order_details) ? $order_details->cancel_reason : '-----' }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <th>Total Amount</th>
                                                    <td class=""><span
                                                                class="currency"></span> {{ isset($order_details) ? $order_details->total_pay : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Type</th>
                                                    <td>{{ isset($order_details) ? ($order_details->payment_type == 1 ? "cash" : ($order_details->payment_type == 2 ? "card" : ($order_details->payment_type == 3 ? "wallet" : ''))) : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Status</th>
                                                    <td>{{ isset($order_details) ? ($order_details->payment_status == 1 ? "complete" : "pending") : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Order Type</th>
                                                    <td>{{ isset($order_details) && $order_details->order_type == 1 ? 'Schedule Order': 'Book Now' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Order Status</th>
                                                    <td>{{ isset($orders_status) ? $orders_status : '----' }}</td>
                                                </tr>
                                                {{--                                                <tr>--}}
                                                {{--                                                    <th>Order Status</th>--}}
                                                {{--                                                    <th>Time</th>--}}
                                                {{--                                                </tr>--}}
                                                {{--                                                <tr>--}}
                                                {{--                                                    <td>Pending</td>--}}
                                                {{--                                                    <td>01-01-2019 05:30:00</td>--}}
                                                {{--                                                </tr>--}}
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <div class="ride-detail-table-header">
                                                        <h5>Product Details</h5>
                                                    </div>
                                                </div>
                                                {{--@if(Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)--}}
                                                {{--    @if(isset($order_details) && $order_details->status < 3)--}}
                                                {{--        <div class="col-lg-4">--}}
                                                {{--            <a href="{{ route('get:admin:edit_store_order_details',[$slug,$order_details->id]) }}" class="btn btn-primary m-b-0 btn-right render_link">Edit Product</a>--}}
                                                {{--        </div>--}}
                                                {{--    @endif--}}
                                                {{--@endif--}}
                                            </div>
                                            <table class="table table-bordered">
                                                <tr style="border-top: 1px solid #dee2e6">
                                                    {{--<th>Product Image</th>--}}
                                                    <th>Category Name</th>
                                                    <th>Product Name</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total Cost</th>
                                                </tr>
                                                @if(isset($product_details))
                                                    @foreach($product_details as $key => $products)

                                                        @foreach($products as $no => $product)
                                                            <tr>
                                                                {{--<td>--}}
                                                                {{--<img height="100" width="100"--}}
                                                                {{--src="{{ asset('assets/images/store-images/product-images/14.jpeg') }}">--}}
                                                                {{--</td>--}}
                                                                @if($no == 0)
                                                                    <td rowspan="{{count($products)}}">
                                                                        {{ $key }}
                                                                    </td>
                                                                @endif
                                                                {{--<td>
                                                                    {{ $product->product_name }}
                                                                </td>--}}
                                                                <td>
                                                                    {{ $product->product_name }}
                                                                    <b><sub>@if(isset($product_option_list))
                                                                        @if(array_key_exists($product->id,$product_option_list))
                                                                            @if( $product_option_list[$product->id] != Null)
                                                                                ({{ $product_option_list[$product->id] }})
                                                                            @endif
                                                                        @endif
                                                                        @endif</sub></b>
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ $product->price_for_one }}
                                                                </td>
                                                                <td>
                                                                    {{ $product->num_of_items }}
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ round(($product->price_for_one * $product->num_of_items),2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Item Total
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->total_item_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Discount
                                                    </td>
                                                    <td>
                                                        <span class=""><span class="currency"></span> {{ $order_details->discount_cost }} </span>
                                                    </td>
                                                </tr>
                                                @if((isset($order_details->discount_amount) && $order_details->discount_amount >0  ))
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        PromoCode ({{ $order_details->promocode_name }})
                                                    </td>
                                                    <td>
                                                        <span class=""> -<span class="currency"></span> {{ (isset($order_details->discount_amount) && $order_details->discount_amount >0  )?$order_details->discount_amount:0 }} </span>
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Tax
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->tax_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Delivery Charge
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->delivery_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Packaging Charge
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->packaging_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td><h3>Total Pay</h3></td>
                                                    <td><h3 class=""><span class="currency"></span> {{ $order_details->total_pay }}</h3></td>
                                                </tr>
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

    <div class="md-modal md-effect-1" id="modal-1">
        <div class="md-content">
            <h3 class="bg-c-blue" id="driver_name">Delivery Person Details</h3>
            <div>
                <div style="width: 25%; height: 100px">
                    <img id="image" src="" style="width: 100%; height: auto">
                </div>
                <ul>
                    <li><strong>Name : </strong> <span id="provider_name"></span></li>
                    <li><strong>Contact No : </strong> <span id="contact_no"></span></li>
                    <li><strong>Rating : </strong> <span id="contact_no"></span></li>
                    <li><strong>Vehicle Type : </strong> <span id="vehicle_type"></span></li>
                    <li><strong>Vehicle Model Name : </strong> <span id="model_name"></span></li>
                    <li><strong>Vehicle Plat No: </strong> <span id="plat_no"></span></li>
                    <li><strong>Vehicle Model Year: </strong> <span id="model_year"></span></li>
                    <li><strong>Vehicle Color: </strong> <span id="vehicle_color"></span></li>
                </ul>
                {{--<a href="" id="driver_vehicle_details"><button type="button" class="btn btn-success waves-effect">Edit</button></a>--}}
                <button type="button" class="btn btn-primary waves-effect md-close">Close</button>
            </div>
        </div>
    </div>
    <div class="md-overlay"></div>
@endsection
@section('page-js')
    <script type="text/javascript">
        function PrintDiv() {
            var divToPrint = document.getElementById('divToPrint');
            var popupWin = window.open('', '_blank', 'width=500,height=300');
            popupWin.document.open();
            popupWin.document.write('<html><link rel="stylesheet" type="text/css" href="{{ asset("assets/css/store-order-print.css")}}"><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            popupWin.document.close();
        }

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
                            url: '{{ route("get:admin:user_refund_amount_settle") }}',
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

    <script src="{{ asset('assets/js/classie.js')}}" type="text/javascript"></script>
    <script>
        var ModalEffects = (function () {
            function init() {
                var overlay = document.querySelector('.md-overlay');
                [].slice.call(document.querySelectorAll('.md-trigger')).forEach(function (el, i) {
                    var modal = document.querySelector('#' + el.getAttribute('data-modal')),
                        close = modal.querySelector('.md-close');

                    function removeModal(hasPerspective) {
                        classie.remove(modal, 'md-show');
                        if (hasPerspective) {
                            classie.remove(document.documentElement, 'md-perspective');
                        }
                    }

                    function removeModalHandler() {
                        removeModal(classie.has(el, 'md-setperspective'));
                    }

                    el.addEventListener('click', function (ev) {
                        var provider_name = $(this).attr('provider_name');
                        var contact_no = $(this).attr('contact_no');
                        var image = $(this).attr('image');
                        var vehicle_type = $(this).attr('vehicle_type');
                        var model_name = $(this).attr('model_name');
                        var plat_no = $(this).attr('plat_no');
                        var model_year = $(this).attr('model_year');
                        var vehicle_color = $(this).attr('vehicle_color');

                        // var url = $(this).attr('url');
                        $('#provider_name').text(provider_name);
                        $('#contact_no').text(contact_no);
                        $('#image').attr('src', image);
                        $('#vehicle_type').text(vehicle_type);
                        $('#model_name').text(model_name);
                        $('#plat_no').text(plat_no);
                        $('#model_year').text(model_year);
                        $('#vehicle_color').text(vehicle_color);

                        // $('#driver_vehicle_details').attr('href', url);
                        classie.add(modal, 'md-show');
                        overlay.removeEventListener('click', removeModalHandler);
                        overlay.addEventListener('click', removeModalHandler);
                        if (classie.has(el, 'md-setperspective')) {
                            setTimeout(function () {
                                classie.add(document.documentElement, 'md-perspective');
                            }, 25);
                        }
                    });
                    close.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        removeModalHandler();
                    });
                });
            }

            init();
        })();
    </script>
@endsection

