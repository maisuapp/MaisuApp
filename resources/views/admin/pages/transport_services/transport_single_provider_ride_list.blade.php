@extends('admin.layout.super_admin')
@section('title')
    @if(isset($status) && $status == 'scheduled' )
        @if(isset($slug) && $slug == "courier-service") Services @else Scheduled Ride @endif
    @elseif(isset($status) && $status == 'all')
        @if(isset($slug) && $slug == "courier-service") All Services @else All Ride @endif
    @elseif(isset($status) && $status == "pending")
        @if(isset($slug) && $slug == "courier-service")  Pending Services @else Pending Ride @endif
    @elseif(isset($status) && $status == 4)
        Cancelled Ride
    @endif
    List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
        /*datatable style*/
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            background: #55d090;
        }

        .page-item.active .page-link {
            background: #55d090;
            border-color: #55d090;
        }

        /*datatable td link*/
        .ride-status a {
            color: #55d090;
            font-weight: bold;
            font-size: 14px;
        }

        .ride-status i {
            display: inline-block;
            font-size: 20px;
        }

        .icon-list-demo i {
            height: auto;
            line-height: 10px;
            border: none;
            margin-right: 5px;
            color: #55d090;
            width: unset;
        }

        /*.ride-status i {*/
        /*display: inline-block;*/
        /*font-size: 15px;*/
        /*}*/

        .page-link {
            color: #55d090;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
        @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <!-- [ other service horizontal navbar ] start -->
                <div class="other-service-horizontal-nav">
{{--                    @include('admin.include.transport-horizontal-navbar')--}}
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
                            <h5>@if(isset($status) && $status == 'scheduled' )
                                    @if(isset($slug) && $slug == "courier-service") Scheduled Deliveries @else Scheduled
                                    Ride @endif
                                @elseif(isset($status) && $status == 'all')
                                    @if(isset($slug) && $slug == "courier-service") All Deliveries @else All Ride @endif
                                @elseif(isset($status) && $status == "pending")
                                    @if(isset($slug) && $slug == "courier-service") Pending Deliveries @else Pending
                                    Ride @endif
                                @elseif(isset($status) && $status == 4)
                                    @if(isset($slug) && $slug == "courier-service") Cancelled Deliveries @else Cancelled
                                    Ride @endif
                                @endif
                                List
                                @if(isset($driver_details) && $driver_details != Null)
                                    of {{ ucwords(strtolower($driver_details->driver_name)) }}
                                @endif
                            </h5>
                            <span>All
                                @if(isset($status) && $status == 'scheduled' )
                                    @if(isset($slug) && $slug == "courier-service")  Scheduled Deliveries @else
                                        Scheduled
                                        Ride @endif
                                @elseif(isset($status) && $status == 'all')
                                    @if(isset($slug) && $slug == "courier-service")  Deliveries @else  Ride @endif
                                @elseif(isset($status) && $status == "pending")
                                    @if(isset($slug) && $slug == "courier-service")  Pending Deliveries @else
                                        Pending
                                        Ride @endif
                                @elseif(isset($status) && $status == 4)
                                    @if(isset($slug) && $slug == "courier-service")  Cancelled Deliveries @else
                                        Cancelled
                                        Ride @endif
                                @endif
                                List
                                @if(isset($service_category) && $service_category->name != Null)
                                    of {{ ucwords(strtolower($service_category->name)) }} @endif
                            </span>
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
                                <h5>@if(isset($status) && $status == 'scheduled' )
                                        @if(isset($slug) && $slug == "courier-service")  Scheduled Deliveries @else
                                            Scheduled Ride @endif
                                    @elseif(isset($status) && $status == 'all')
                                        @if(isset($slug) && $slug == "courier-service")  All Deliveries @else All
                                        Ride @endif
                                    @elseif(isset($status) && $status == "pending")
                                        @if(isset($slug) && $slug == "courier-service")  Pending Deliveries @else
                                            Pending
                                            Ride @endif
                                    @elseif(isset($status) && $status == 4)
                                        @if(isset($slug) && $slug == "courier-service")  Cancelled Deliveries @else
                                            Cancelled Ride @endif
                                    @endif
                                    List @if(isset($service_category) && $service_category->name != Null)
                                        of {{ ucwords(strtolower($service_category->name)) }} @endif
                                    @if(isset($driver_details) && $driver_details != Null)
                                        - {{ ucwords(strtolower($driver_details->driver_name)) }}
                                    @endif
                                </h5>
                                {{--<a href="{{ URL::previous() }}"--}}
                                {{--                                        href="{{ route('get:transport:transport_provider_list') }}"--}}
                                {{--class="btn btn-success m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="rides" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>@if(isset($slug) && $slug == "courier-service") Delivery No. @else
                                                    Ride
                                                    No. @endif</th>
                                            <th>@if(isset($slug) && $slug == "courier-service") Recipient Name @else
                                                    Customer Name @endif</th>
                                            <th>Driver Name</th>
                                            <th>Vehicle Type</th>
                                            <th>Total Cost</th>
                                            <th>Status</th>
                                            <th>Details</th>
                                            <th>@if(isset($slug) && $slug == "courier-service") Pick /
                                                Delivery @else
                                                    Pick / Drop @endif Address
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                       {{-- @if(isset($ride_lists))
                                            @foreach($ride_lists as $key => $ride_list)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $ride_list['ride_no'] }}</td>
                                                    <td>{{ ($ride_list['user_name'] != Null) ? ucwords($ride_list['user_name']) : "----" }}</td>
                                                    <td>{{ ($ride_list['driver_name'] != Null) ? ucwords($ride_list['driver_name']) : "----" }}</td>
                                                    <td>{{ ($ride_list['vehicle_name'] != Null) ? ucwords($ride_list['vehicle_name']) : "----" }}</td>

                                                    <td> <span class="currency"></span> {{ $ride_list['total_pay'] }} </td>
                                                    <td class="ride-status">
                                                       --}}{{-- @if($ride_list['status'] == 0)
                                                            @php $ride_status = "pending"; @endphp
                                                        @elseif($ride_list['status'] == 1 || $ride_list['status'] == 2)
                                                            @php $ride_status = "approved"; @endphp
                                                        @elseif($ride_list['status'] == 5)
                                                            @php $ride_status = "running"; @endphp
                                                        @elseif($ride_list['status'] == 9)
                                                            @php $ride_status = "completed"; @endphp
                                                        @elseif($ride_list['status'] == 4)
                                                            @php $ride_status = "cancelled"; @endphp
                                                        @endif--}}{{--

                                                        @if($ride_list['status'] == 0)
                                                            @php $ride_status = "pending"; @endphp
                                                        @elseif($ride_list['status'] == 1 || $ride_list['status'] == 2 || $ride_list['status'] == 3)
                                                            @php $ride_status = "approved"; @endphp
                                                            --}}{{--@elseif($ride_list->status == 3)--}}{{--
                                                            --}}{{--@php $ride_status = "arrived"; @endphp--}}{{--
                                                        @elseif($ride_list['status'] == 4)
                                                            @php $ride_status = "cancelled"; @endphp
                                                        @elseif($ride_list['status'] == 5 || $ride_list['status'] == 6 || $ride_list['status'] == 7 || $ride_list['status'] == 8)
                                                            @php $ride_status = "running"; @endphp
                                                            --}}{{--@elseif($ride_list->status == 6)--}}{{--
                                                            --}}{{--@php $ride_status = "drop"; @endphp--}}{{--
                                                            --}}{{--@elseif($ride_list->status == 7)--}}{{--
                                                            --}}{{--@php $ride_status = "payment"; @endphp--}}{{--
                                                            --}}{{--@elseif($ride_list->status == 8)--}}{{--
                                                            --}}{{--@php $ride_status = "rating"; @endphp--}}{{--
                                                        @elseif($ride_list['status'] == 9)
                                                            @php $ride_status = "completed"; @endphp
                                                        @elseif($ride_list['status'] == 10)
                                                            @php $ride_status = "failed"; @endphp
                                                        @endif


                                                        <span class="{{ $ride_status }}">{{ $ride_status }}</span>
                                                    </td>
                                                    <td class="ride-status">
                                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                                                                @if(in_array($ride_list['service_cat_id'], $transport_services))
                                                                    <a href="{{ route('get:admin:transport_ride_details',[ $slug, $ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Ride Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @elseif(in_array($ride_list['service_cat_id'], $courier_service))
                                                                    <a href="{{ route('get:admin:transport_ride_details',[ $slug, $ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Delivery Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @elseif(in_array($ride_list['service_cat_id'], $delivery_service))
                                                                    <a href="{{ route('get:admin:transport_delivery_details',[ $slug, $ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Order Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @endif
                                                            @elseif(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2)
                                                                @if(in_array($ride_list['service_cat_id'], $transport_services))
                                                                    <a href="{{ route('get:dispatcher:ride_details',[$ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Ride Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @endif
                                                            @elseif(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 3)
                                                                @if(in_array($ride_list['service_cat_id'], $transport_services))
                                                                    <a href="{{ route('get:account:ride_details',[$ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Ride Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @elseif(in_array($ride_list['service_cat_id'], $courier_service))
                                                                    <a href="{{ route('get:account:ride_details',[$ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Delivery Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @elseif(in_array($ride_list['service_cat_id'], $delivery_service))
                                                                    <a href="{{ route('get:account:delivery_details',[$ride_list['id']]) }}"
                                                                       class="render_link">
                                                                        <span class="icon-list-demo">
                                                                            <i class="fa fa-info-circle text-c-green"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Order Details"></i>
                                                                        </span>
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        @endif

                                                        --}}{{--<a href="{{ route('get:admin:transport_ride_details',['slug' => $slug,'id' => $ride_list->id ]) }}"--}}{{--
                                                        --}}{{--class="render_link"><i class="fa fa-info-circle text-c-green"></i></a>--}}{{--
                                                    </td>
                                                    <td>
                                                        <i class="fa fa-map-marker text-success"></i> {{ $ride_list['pickup_address'] }}
                                                        <br>
                                                        <i class="fa fa-map-marker text-danger"></i> {{ $ride_list['destination_address'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif--}}
                                        </tbody>
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
{{--    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>--}}
    <script>
        $(document).ready(function () {
            var table = $('#rides').DataTable({
                processing: true,
                language : {
                    loadingRecords : '&nbsp;',
                    {{--processing: "<img src='{{ asset('assets/images/website-logo-icon/loader.gif') }}' />",--}}
                    processing: "<img src='{{ asset('/assets/images/website-logo-icon/loader.gif')}}' style='width: 50px; height: 50px;' />",
                    // processing: "<img src='http://127.0.0.1:8000/assets/images/website-logo-icon/loader.gif'  style='width: 10px; height: 10px;'/>",
                    // processing: "<img src='http://127.0.0.1:8000/assets/images/website-logo-icon/loader.gif' />"
                },
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: {
                    url: "{{route('get:admin:single_provider_ride_list_new')}}",
                    data: {
                        driver_id : "{{ $driver_id }}",
                        slug: "{{ $slug }}"
                    }
                },
                columns: [
                    {data: 'no'},
                    {data: 'ride_no'},
                    {data: 'customer_name'},
                    {data: 'driver_name'},
                    {data: 'vehicle_type'},
                    {data: 'total_cost'},
                    {data: 'status'},
                    {data: 'details', 'sortable': false},
                    {data: 'address', 'sortable': false},
                ]
            });
        });
    </script>
@endsection
