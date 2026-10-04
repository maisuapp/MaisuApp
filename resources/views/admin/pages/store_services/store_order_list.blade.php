@extends('admin.layout.super_admin')
@section('title')
    @if(isset($store_details)) {{$store_details->name}} @else Store @endif Order List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                <div class="external-horizontal-nav">
                    @include('admin.include.store-horizontal-navbar')
                </div>
        @endif
    @endif
    <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>@if(isset($store_details)) {{$store_details->name}} @else @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif
                                Store @endif
                                Order List</h5>
                            <span>All @if(isset($service_category)) Store @endif Order List</span>
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
                            <div class="card-header">
                                <h5>Store Order List</h5>
                                {{--<a href="{{ route('get:admin:store_provider_list') }}"--}}
                                {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">

                                        @if(isset($store_details))
                                            <thead>
                                            <tr>
                                                @if(isset($store_details))
                                                    <th>No</th>
                                                    <th>Customer Name</th>
                                                    <th>Order List</th>
                                                    <th>Total Cost</th>
                                                    <th>Order Status</th>
                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <th>Payment Status</th>
                                                        <th>Refund Status</th>
                                                    @endif
                                                    <th>Details</th>
                                                @else
                                                    <th>No</th>
                                                    <th>Customer Name</th>
                                                    <th>Store Name</th>
                                                    <th>Total Cost</th>
                                                    <th>Order Status</th>
                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <th>Payment Status</th>
                                                        <th>Refund Status</th>
                                                    @endif
                                                    <th>Details</th>
                                                @endif
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(isset($order_list))
                                                @foreach($order_list as $key => $order)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ $order->user_name }}</td>
                                                        <td style="font-size: 12px">{{ \Illuminate\Support\Str::limit($order->order_product_list, $limit = 60, $end = '...') }}</td>
                                                        <td class="">
                                                            {{--<i class="fa fa-usd"></i>--}}
                                                            <span class="currency"></span> {{ $order->total_pay }} </td>
                                                        <td class="order-status">
                                                            <?php
                                                            if ($order->status == 1) {
                                                                $bg_status_class = "pending";
                                                            } elseif ($order->status == 2) {
                                                                $bg_status_class = "approved";
                                                            } elseif (in_array($order->status, [3, 4, 10])) {
                                                                $bg_status_class = "cancelled";
                                                            } elseif (in_array($order->status, [5, 6, 7])) {
                                                                $bg_status_class = "processing";
                                                            } elseif ($order->status == 8) {
                                                                $bg_status_class = "ongoing";
                                                            } elseif ($order->status == 9) {
                                                                $bg_status_class = "completed";
                                                            } else {
                                                                $bg_status_class = "";
                                                            }
                                                            ?>
                                                            <span class="{{$bg_status_class}}">
                                                                {{ str_replace('-',' ',ucwords(trim($order_status[$order->status]))) }}
                                                            </span>
                                                        </td>
                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            <td class="order-status">
                                                                @if($order->payment_status == 1)
                                                                    <span class="completed">Paid</span>
                                                                @else
                                                                    <span class="pending">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td class="order-status">
                                                                @if($order->status == 3 || $order->status == 5)
                                                                    @if($order->payment_type == 1 )
                                                                        <span class="approved">N/A</span>
                                                                    @else
                                                                        @if($order->user_refund_status == 1)
                                                                            <span class="completed">Completed</span>
                                                                        @else
                                                                            <span class="pending">Pending</span>
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    <span class="approved">N/A</span>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td class="order-status">
                                                            <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? route('get:admin:store_order_details',[$slug,$order->id]) : route('get:account:store_order_details',[$order->id]) : route('get:store-admin:store_order_details',[$order->id]) }}"
                                                               class="render_link">view details</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        @else
                                            <thead>
                                            <tr>
                                                @if(isset($store_details))
                                                    <th>No</th>
                                                    <th>Customer Name</th>
                                                    <th>Order List</th>
                                                    <th>Total Cost</th>
                                                    <th>Order Status</th>
                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <th>Payment Status</th>
                                                        <th>Refund Status</th>
                                                    @endif
                                                    <th>Details</th>
                                                @else
                                                    <th>No</th>
                                                    <th>Customer Name</th>
                                                    <th>Store Name</th>
                                                    <th>Total Cost</th>
                                                    <th>Order Status</th>
                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <th>Payment Status</th>
                                                        <th>Refund Status</th>
                                                    @endif
                                                    {{--                                                        @if(isset($cancel_status_type) && $cancel_status_type ==1)--}}
                                                    {{--                                                            <th>Refund Status</th>--}}
                                                    {{--                                                        @endif--}}
                                                    <th>Details</th>
                                                @endif
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(isset($order_list))
                                                @foreach($order_list as $key => $order)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ $order->user_name }}</td>
                                                        <td>{{ $order->store_name }}</td>
                                                        <td class=""><span
                                                                    class="currency"></span> {{ $order->total_pay }}
                                                        </td>
                                                        <td class="order-status">
                                                            <?php
                                                            if ($order->status == 1) {
                                                                $bg_status_class = "pending";
                                                            } elseif ($order->status == 2) {
                                                                $bg_status_class = "approved";
                                                            } elseif (in_array($order->status, [3, 4, 10])) {
                                                                $bg_status_class = "cancelled";
                                                            } elseif (in_array($order->status, [5, 6, 7])) {
                                                                $bg_status_class = "processing";
                                                            } elseif ($order->status == 8) {
                                                                $bg_status_class = "ongoing";
                                                            } elseif ($order->status == 9) {
                                                                $bg_status_class = "completed";
                                                            } else {
                                                                $bg_status_class = "";
                                                            }
                                                            ?>
                                                            <span class="{{$bg_status_class}}">
                                                                    {{ str_replace('-',' ',ucwords(trim($order_status[$order->status]))) }}
                                                                </span>
                                                        </td>
                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            <td class="order-status">
                                                                @if($order->payment_status == 1)
                                                                    <span class="completed">Paid</span>
                                                                @else
                                                                    <span class="pending">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td class="order-status">
                                                                @if($order->status == 3 || $order->status == 4)
                                                                    @if($order->payment_type == 1 )
                                                                        <span class="approved">N/A</span>
                                                                    @else
                                                                        @if($order->user_refund_status == 1)
                                                                            <span class="completed">Completed</span>
                                                                        @else
                                                                            <span class="pending">Pending</span>
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    <span class="approved">N/A</span>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        {{--                                                            @if(isset($cancel_status_type) && $cancel_status_type == 1)--}}
                                                        {{--                                                                @if($order->status == 4)--}}
                                                        {{--                                                                    <td class="order-status"> <span class="{{ ($order->user_refund_status == 1)? "completed" : "pending" }}"> {{ ($order->user_refund_status == 1)? "Completed" : "Pending" }}</span></td>--}}
                                                        {{--                                                                @else--}}
                                                        {{--                                                                    <td>----</td>--}}
                                                        {{--                                                                @endif--}}
                                                        {{--                                                            @endif--}}
                                                        <td class="order-status">
                                                            <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)?  route('get:admin:store_order_details',[$slug,$order->id]) :  route('get:account:store_order_details',[$order->id]) : route('get:store-admin:store_order_details',[$order->id]) }}"
                                                               class="render_link">view details</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        @endif

                                    </table>
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
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>
@endsection
