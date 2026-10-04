@extends('admin.layout.super_admin')
@section('title')
    Order Details
@endsection
@section('page-css')
@endsection
@section('page-content')

    <div class="pcoded-content">
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
        @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <!-- [ other service horizontal navbar ] start -->
                <div class="other-service-horizontal-nav">
                    @include('admin.include.store-horizontal-navbar')
                </div>
                <!-- [ other service horizontal navbar ] end -->
        @endif
    @endif
    <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        @if(!isset($service_category))
                            <i class="feather icon-list bg-c-green"></i>
                        @endif
                        <div class="d-inline">
                            @if(isset($service_category))
                                <h5 style="margin-top: 10px">
                                    <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}"
                                         height="40" width="40" style="margin-right: 5px">
                                    {{ isset($service_category) ? ucwords(strtolower($service_category->name)) : '' }}
                                    Order Details
                                </h5>
                            @else
                                <h5>
                                    Order Details
                                </h5>
                                <span>Full Order Details</span>
                            @endif
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
                                            <table class="table">
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
                                                    <th>Pickup Address</th>
                                                    <td>{{ isset($store_details) ? $store_details->address : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Delivery Address</th>
                                                    <td>{{ isset($order_details) ? $order_details->delivery_address : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Phone No</th>
                                                    <td>{{ isset($user_details) ? $user_details->contact_number : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Order Date</th>
                                                    <td>{{ isset($order_details) ? $order_details->created_at : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Order Type</th>
                                                    <td>{{ isset($order_details) && $order_details->order_type == 1 ? 'Schedule Order': 'Book Now' }}</td>
                                                </tr>
                                                @if(isset($order_details) && $order_details->order_type == 1)
                                                    <tr>
                                                        <th>Schedule Order Date</th>
                                                        <td>{{ isset($order_details) ? $order_details->order_date_time : '' }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <th>Total Amount</th>
                                                    <td class="currency"> {{ isset($order_details) ? $order_details->total_pay : '' }}</td>
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
                                                    <th>Order Status</th>
                                                    <th>Time</th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        {{ ucwords($order_status[$order_details->status]) }}
                                                    </td>
                                                    <td>01-01-2019 05:30:00</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="ride-detail-table-header">
                                                <h5>Product Details</h5>
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
                                                                <td>
                                                                    {{ $product->product_name }}
                                                                </td>
                                                                <td class="currency">
                                                                      {{ $product->price_for_one }}
                                                                </td>
                                                                <td>
                                                                    {{ $product->num_of_items }}
                                                                </td>
                                                                <td class="currency">
                                                                     {{ round(($product->price_for_one * $product->num_of_items),2) }}
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
                                                    <td class="currency">
                                                         {{ $order_details->total_item_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Discount
                                                    </td>
                                                    <td>
                                                        - <span class="currency"> {{ $order_details->discount_cost }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Tax
                                                    </td>
                                                    <td class="currency">
                                                         {{ $order_details->tax_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Delivery Charge
                                                    </td>
                                                    <td class="currency">
                                                         {{ $order_details->delivery_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Packaging Charge
                                                    </td>
                                                    <td class="currency">
                                                         {{ $order_details->packaging_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        <h3>Total Pay</h3>
                                                    </td>
                                                    <td>
                                                        <h3 class="currency"> {{ $order_details->total_pay }}</h3>
                                                    </td>
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
@endsection
@section('page-js')

@endsection
