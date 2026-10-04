@extends('admin.layout.super_admin')
@section('title')
    Shopper Services
@endsection
@section('page-css')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/assets/css/widget/widget.css') }}">
    <style>
        .card-padding {
            padding: 15px 10px;
            float: left;
        }

        .card-body{
            padding: 1.25rem;
        }
        .card-transparent {
            background-color: transparent;
            border: none;
            box-shadow: none;
            margin: -15px;
        }

        .card-main-padding {
            padding: 8px;
        }

        .card-padding .col, .card-padding .col-auto {
            padding: 0 10px;
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="external-horizontal-nav">
            @include('admin.include.shopper-horizontal-navbar')
        </div>
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-home bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Shopper Services</h5>
                            <span>Summary</span>
                        </div>
                    </div>
                </div>
                {{--<div class="col-lg-4">--}}
                {{--<div class="page-header-breadcrumb">--}}
                {{--<ul class=" breadcrumb breadcrumb-title">--}}
                {{--<li class="breadcrumb-item">--}}
                {{--<a href=""><i class="feather icon-home"></i> Dashboard</a>--}}
                {{--</li>--}}
                {{--</ul>--}}
                {{--</div>--}}
                {{--</div>--}}
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <!-- [ page content ] start -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card sale-card card-transparent">
                                    {{--<div class="card-header">--}}
                                    {{--<h5>Deals Analytics</h5>--}}
                                    {{--</div>--}}
                                    {{--<div class="card-block">--}}
                                    <div class="col-md-12 card-main-padding">
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Approved Shoppers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($approved_store) ? $approved_store : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-store text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Live Shoppers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($live_store) ? $live_store : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-shipping-fast text-c-blue f-18"></i>
                                                            {{--<i class="fas fa-business-time text-c-blue f-18"></i>--}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Pending Shoppers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($pending_store) ? $pending_store : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-user-plus text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Earnings
                                                                {{--Last 7-Days--}}
                                                            </h6>
                                                            <h3 class="m-b-0 f-w-700 "><span class="currency"></span> {{ isset($admin_earning) ? $admin_earning : 0 }} </h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-money-bill-alt text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--</div>--}}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card sale-card card-transparent">
                                    <div class="card-header">
                                        <h5>Last 7-Days Statistics</h5>
                                    </div>
                                    {{--<div class="card-block">--}}
                                    <div class="col-md-12 card-main-padding">
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Total Orders</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($total_orders) ? $total_orders : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-shopping-bag text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Completed Orders</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($completed_orders) ? $completed_orders : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-check text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Running Orders</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($running_orders) ? $running_orders : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-spinner text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Cancelled Orders</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ isset($cancelled_orders) ? $cancelled_orders : 0 }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-times text-c-blue f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--</div>--}}
                                </div>
                            </div>
                        </div>
                        <!-- [ page content ] end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-js')
    {{--<script type="text/javascript" src="{{ asset('assets/js/chart/jquery.flot.js')}}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('assets/js/chart/amcharts.js')}}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('assets/js/chart/serial.js')}}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('assets/js/chart/light.js')}}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('assets/js/chart/custom-dashboard.min.js')}}"></script>--}}
@endsection
