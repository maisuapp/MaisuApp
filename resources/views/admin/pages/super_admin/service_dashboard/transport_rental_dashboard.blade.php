@extends('admin.layout.super_admin')
@section('title')
    {{ ucwords(strtolower($service_category->name)) }}
@endsection
@section('page-css')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
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
            @include('admin.include.transport-rental-horizontal-navbar')
        </div>

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-home bg-c-green"></i>
                        <div class="d-inline">
                            <h5>{{ ucwords(strtolower($service_category->name)) }}</h5>
                            <span>Summary</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card sale-card card-transparent">
                                    <div class="col-md-12 card-main-padding">
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Approved Drivers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $approved_drivers }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-user-tie text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">Active Drivers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $active_drivers }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-shipping-fast text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">UnApproved Drivers</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $un_approved_drivers }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-user-plus text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">Earnings</h6>
                                                            <h3 class="m-b-0 f-w-700 "> <span class="currency"></span> {{ $total_admin_earning }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-money-bill-alt text-c-green f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card sale-card card-transparent">
                                    <div class="card-header">
                                        <h5>Last 7-Days {{ ucwords(strtolower($service_category->name)) }} Statistics</h5>
                                    </div>
                                    <div class="col-md-12 card-main-padding">
                                        <div class="col-md-3 card-padding">
                                            <div class="card prod-p-card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h6 class="m-b-5">Total @if($service_category->category_type == 1) Rides @elseif($service_category->category_type == 5) Delivery @elseif($service_category->category_type == 6) Deals @endif</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $total_ride }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-car text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">Completed @if($service_category->category_type == 1) Rides @elseif($service_category->category_type == 5) Delivery @elseif($service_category->category_type == 6) Deals @endif</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $total_completed_ride }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-check text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">Running @if($service_category->category_type == 1) Rides @elseif($service_category->category_type == 5) Delivery @elseif($service_category->category_type == 6) Deals @endif</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $total_running_ride }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-spinner text-c-green f-18"></i>
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
                                                            <h6 class="m-b-5">Cancelled @if($service_category->category_type == 1) Rides @elseif($service_category->category_type == 5) Delivery @elseif($service_category->category_type == 6) Deals @endif</h6>
                                                            <h3 class="m-b-0 f-w-700">{{ $total_cancelled_ride }}</h3>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-times text-c-green f-18"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page-js')
@endsection

