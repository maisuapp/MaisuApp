@extends('admin.layout.store_admin')
@section('title')
    Dashboard
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('/assets/css/widget/widget.css') }}">
    <style>
        .sos-st-card h5:after {
            width: 0;
        }

        .sos-st-card.green h3 span {
            border-color: #2ed8b678;
        }

        .sos-st-card.green h3, .sos-st-card.green h5 {
            color: #2ed8b6;
        }

        .sos-st-card.purple h3 span {
            border-color: #b881e67a;
        }

        .sos-st-card.purple h3, .sos-st-card.purple h5 {
            color: #b881e6;
        }

        .sos-st-card.blue h3 span {
            border-color: #4099ff85;
        }

        .sos-st-card.blue h3, .sos-st-card.blue h5 {
            color: #4099ff;
        }

        .sos-st-card.yellow h3 span {
            border-color: #ffb64d85;
        }

        .sos-st-card.yellow h3, .sos-st-card.yellow h5 {
            color: #FFB64D;
        }

        .sos-st-card.red h3 span {
            border-color: #ff537085;
        }

        .sos-st-card.red h3, .sos-st-card.red h5 {
            color: #FF5370;
        }

        .services .col-xl-3 {
            max-width: 20%;
        }

        .services .card-block {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .services h3 {
            font-size: 10px;
            font-weight: bold;
            /*padding-top: 10px;*/
        }

        .services h5 {
            padding-top: 5px;
            font-size: 16px;
        }

        .services .col-auto {
            padding: 0;
            padding-left: 5px;
        }

        .services .text-right {
            padding: 0 5px;
            padding-left: 7px;
        }

        .services .text-right {
            display: inline-block;
        }

        .sos-st-card h5:after {
            width: 0;
        }

        .latest-update-card .card-block .latest-update-box:after {
            width: 0;
        }

        .latest-update-box p {
            margin-bottom: .5rem;
        }

        .latest-update-box p span.offers {
            font-size: 16px;
            font-weight: 500;
            font-family: "Quicksand", sans-serif;
        }

        .dashboard-box {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 5px 0 rgba(43, 43, 43, 0.1), 0 2px 6px -7px rgba(43, 43, 43, 0.1) !important;
        }

        .dashboard-title {
            padding: 1rem !important;
        }

        .dashboard-title h5 {
            font-size: 16px;
        }

        .order-list td, .table th {
            padding: 0.7rem 0.75rem !important;
        }
    </style>
@endsection
@section('page-content')

    {{--sidebar start--}}
    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-home bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Dashboard</h5>
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

                            <div class="col-xl-12 col-md-12">
                                <div class="card dashboard-box">
                                    <div class="card-block dashboard-title">
                                        <h5>Today Summary</h5>
                                    </div>
                                </div>
                            </div>

                            <!-- product profit start -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card prod-p-card card-red">
                                    <div class="card-body">
                                        <div class="row align-items-center m-b-30">
                                            <div class="col">
                                                <h6 class="m-b-5 text-white">Total Revenue</h6>
                                                <h3 class="m-b-0 f-w-700 text-white "><span class="currency"></span> {{ $total_rev }} </h3>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-money text-c-red f-18"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card prod-p-card card-blue">
                                    <div class="card-body">
                                        <div class="row align-items-center m-b-30">
                                            <div class="col">
                                                <h6 class="m-b-5 text-white">Total Sale</h6>
                                                <h3 class="m-b-0 f-w-700 text-white">{{ $total_sales }}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-database text-c-blue f-18"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card prod-p-card card-green">
                                    <div class="card-body">
                                        <div class="row align-items-center m-b-30">
                                            <div class="col">
                                                <h6 class="m-b-5 text-white">Total Orders</h6>
                                                <h3 class="m-b-0 f-w-700 text-white">{{ $total_sales }}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-dollar text-c-green f-18"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card prod-p-card card-yellow">
                                    <div class="card-body">
                                        <div class="row align-items-center m-b-30">
                                            <div class="col">
                                                <h6 class="m-b-5 text-white">New Users</h6>
                                                <h3 class="m-b-0 f-w-700 text-white">{{ $total_user }}</h3>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-tags text-c-yellow f-18"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- product profit end -->

                        </div>
                        <!-- [ page content ] end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ style Customizer ] start -->
    <div id="styleSelector">
    </div>
    <!-- [ style Customizer ] end -->
    {{--sidebar end--}}

@endsection
@section('page-js')
    <script type="text/javascript" src="{{ asset('assets/js/chart/jquery.flot.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/chart/amcharts.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/chart/serial.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/chart/light.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/chart/custom-dashboard.min.js')}}"></script>

    <script>
        $( document ).ready(function() {
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
        });
    </script>
@endsection

