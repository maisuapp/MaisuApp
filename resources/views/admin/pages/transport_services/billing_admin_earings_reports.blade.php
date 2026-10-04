@extends('admin.layout.account_billing_admin')
@section('title')
    Earnings Report
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">

    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <style>
        /*date timepicker style*/
        .input-group {
            margin-bottom: 0;
        }

        .input-group-append .input-group-text {
            background-color: #2ed8b6;
        }

        /*datatable style*/
        /*table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {*/
        /*background: #55d090;*/
        /*}*/

        /*.page-item.active .page-link {*/
        /*background: #55d090;*/
        /*border-color: #55d090;*/
        /*}*/

        .page-link {
            color: #55d090;
        }

        /*.table th, .table td {*/
        /*white-space: unset;*/
        /*}*/

        /*get date style*/
        .date-wrapper {
            margin: 20px 0;
        }

        ul.set-date {
            list-style-type: none;
            margin: 0;
            padding: 0;
            overflow: hidden;
            /*background-color: #333;*/
        }

        ul.set-date li {
            float: left;
            margin-left: 20px;
            padding-right: 16px;
            border-right: 2px solid #21252a;
        }

        ul.set-date li:last-of-type {
            border-right: none;
        }

        ul.set-date li:first-of-type {
            margin-left: 0;
        }

        ul.set-date li a {
            display: block;
            color: #2a455f !important;
            text-align: center;
            /*padding: 14px 16px;*/
            text-decoration: underline !important;
            /*border: 1px solid green;*/
        }

        /*day selection style*/

        ul.set-date li a:hover {
            color: #07C !important;
            text-decoration: none !important;
            cursor: pointer;
        }

        .datetimepicker-dropdown-bottom-right {
            width: 250px;
        }

        .datetimepicker table {
            width: 100%;
        }

        #to_date, #from_date {
            background: white;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        /*table style*/
        table th, table td {
            padding: 5px !important;
            font-size: 12px;
            /*max-width: 20px !important;*/
            word-wrap: break-word !important;
            white-space: pre-line;
        }

        table .extra {
            /*max-width: 22px !important;*/
        }

        table .extra-2 {
            /*max-width: 24px !important;*/
        }

        table .extra-3 {
            /*max-width: 38px !important;*/
        }

        table .th-checkbox {
            max-width: 14px !important;
            padding: 0;
        }

        /*select driver style*/
        .select2-container {
            width: 100% !important;
            vertical-align: unset;
        }

        .select2-container--default .select2-selection--single {
            height: auto;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            /*padding-top: 1px;*/
            padding: 4px 30px 4px 20px;
            background-color: transparent;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        /*checkbox style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            /*padding-left: 20px;*/
            padding-left: 30px;
            margin-right: 7px;
        }

        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }

        .border-checkbox-section .border-checkbox-group .checklbl {
            height: 0px;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            @if(Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
                <div class="other-service-horizontal-nav">
                    @include('admin.include.transport-horizontal-navbar')
                </div>
            @endif
        @endif

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5> Earnings Report</h5>
                            <span>All Drivers Earnings</span>
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
                        <div class="card">
                            <div class="card-header">
                                <h5> Earnings</h5>
                                {{--<a href="{{ route('get:transport:transport_service_list') }}"--}}
                                {{--class="btn btn-success m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">

                                <form method="post"
                                      action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? route('post:admin:transport_search_earing_report',$slug) : route('post:account:transport_search_earing_report') : '' }}">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-lg-12 date-wrapper">
                                            <div class="form-group">
                                                <ul class="set-date">
                                                    <li><a id="today">Today</a></li>
                                                    <li><a id="yeasterday">Yesterday</a></li>
                                                    <li><a id="this_week">This Week</a></li>
                                                    <li><a id="this_month">This Month</a></li>
                                                    <li><a id="last_month">Last Month</a></li>
                                                    <li><a id="this_year">This Year</a></li>
                                                    <li><a id="last_year">Last Year</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!--from-->
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <div class="input-group date form_datetime">
                                                    <input name="from_date" class="form-control category"
                                                           value="{{isset($from_date)? ($from_date != Null)?  $from_date : old('from_date') : old('from_date') }}"
                                                           placeholder="From Date"
                                                           id="from_date"
                                                           type="text" readonly>
                                                    <span class="input-group-append" id="basic-addon3">
                                                <label class="bg-c-green input-group-text">
                                                    <span class="fa fa-remove remove_from_date "></span>
                                                </label>
                                                </span>
                                                    <span class="input-group-append" id="basic-addon3">
                                                    <label class="bg-c-green input-group-text">
                                                        <span class="fa fa-th"></span>
                                                    </label>
                                                </span>

                                                </div>
                                            </div>
                                        </div>
                                        <!--to-->
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <div class="input-group date to_datetime">
                                                    <input name="to_date" class="form-control category"
                                                           value="{{isset($to_date)? ($to_date != Null)?  $to_date : old('to_date') : old('to_date') }}"
                                                           placeholder="To Date"
                                                           id="to_date"
                                                           type="text" readonly>
                                                    <span class="input-group-append" id="basic-addon3">
                                                <label class="bg-c-green input-group-text">
                                                    <span class="fa fa-remove remove_to_date"></span>
                                                </label>
                                                </span>
                                                    <span class="input-group-append" id="basic-addon3">
                                                    <label class="bg-c-green input-group-text">
                                                        <span class="fa fa-th"></span>
                                                    </label>
                                                </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                {{--<label></label>--}}
                                                <select id="js-example1" name="driver"
                                                        class="js-example-placeholder-single1 js-states form-control">
                                                    <option disabled selected>Select Driver</option>
                                                    @if(isset($driver_list))
                                                        @if(!$driver_list->isEmpty())
                                                            @foreach($driver_list as $key => $driver_details)
                                                                {{ $selected = isset($driver)? ($driver != Null)? ($driver == $driver_details->driver_id)?  "selected" : "" : "" : "" }}
                                                                <option value="{{ $driver_details->driver_id }}" {{ $selected }}>{{ $driver_details->contact_number." ".ucwords($driver_details->name)  }}</option>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                {{--<label></label>--}}
                                                <select id="js-example2" name="user"
                                                        class="js-example-placeholder-single2 js-states form-control">
                                                    <option disabled selected>Select Customer</option>
                                                    @if(isset($user_list))
                                                        @if(!$user_list->isEmpty())
                                                            @foreach($user_list as $key => $user_details)
                                                                {{ $selected = isset($user)? ($user != Null)? ($user == $user_details->id)?  "selected" : "" : "" : "" }}
                                                                <option value="{{ $user_details->id }}" {{ $selected }}>{{ $user_details->contact_number." ".ucwords($user_details->first_name." ".$user_details->last_name) }}</option>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <select name="payment_type" class="form-control select_border gray_text" id="payment_type" onchange="this.className=this.options[this.selectedIndex].className">
                                                    <option class="form-control select_border gray_text" value="">Select Payment Type</option>
                                                    <option value="1" {{ $selected = ( (isset($payment_type) && $payment_type == "1")? "selected" : "" ) }} class="form-control select_border black_text">Cash</option>
                                                    <option value="2" {{ $selected = ( (isset($payment_type) && $payment_type == "2")? "selected" : "" ) }} class="form-control select_border black_text">Card</option>
                                                    <option value="3" {{ $selected = ( (isset($payment_type) && $payment_type == "3")? "selected" : "" ) }} class="form-control select_border black_text">Wallet</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                {{--<label></label>--}}
                                                <select id="city" name="driver_pay_type" class="form-control">
                                                    <option value="2">Select Driver Payment Status</option>
                                                    @if(isset($driver_pay_type) && $driver_pay_type != Null)
                                                        @if($driver_pay_type == 1)
                                                            <option value="1" selected>Settled</option>
                                                            <option value="0">Unsettled</option>
                                                        @elseif($driver_pay_type == 0)
                                                            <option value="1">Settled</option>
                                                            <option value="0" selected>Unsettled</option>
                                                        @else
                                                            <option value="1">Settled</option>
                                                            <option value="0">Unsettled</option>
                                                        @endif
                                                    @else
                                                        <option value="1">Settled</option>
                                                        <option value="0">Unsettled</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <div class="form-group">
                                                {{--<label></label>--}}
                                                <input type="submit" class="btn btn-default"
                                                       value="Search">

                                                <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? route('get:admin:transport_earning_report',$slug) : route('get:account:transport_earning_report') : ''}}"
                                                   class="render_link">
                                                    <input type="reset" class="btn btn-default" value="Clear">
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </form>

                                <form method="post"
                                      action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)? route('post:admin:transport_driver_payment_settled',$slug) : route('post:account:transport_driver_payment_settled') : '' }}">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="dt-responsive table-responsive">

                                                {{--<table id="custm-tool-ele"--}}
                                                {{--id="new-cons"--}}
                                                {{--class="table table-striped table-bordered nowrap"--}}
                                                {{--class="table table-bordered"--}}
                                                {{--style="width:100%; margin-bottom: 0">--}}
                                                <table id="new-cons" style="max-width:100% !important;" class="table table-bordered">
                                                    <thead>
                                                    <tr>
                                                        <th id="th-checkbox"  style="min-width: 30px;">
                                                            <div class="border-checkbox-section">
                                                                <div class="border-checkbox-group border-checkbox-group-primary">
                                                                    <input class="border-checkbox"
                                                                           type="checkbox"
                                                                           id="all_settled">
                                                                    <label class="border-checkbox-label"
                                                                           for="all_settled">
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </th>
                                                        <th>Payment Status</th>
                                                        <th>Ride Date</th>
                                                        <th>Ride Type</th>
                                                        <th>Booking Id</th>
                                                        <th>Service Type</th>
                                                        <th>Customer Name</th>
                                                        <th>Driver Name</th>
                                                        <th>Ride Fare</th>
                                                        <th>Promocode Discount Amount</th>
                                                        <th>Tax</th>
                                                        <th>Tip</th>
                                                        <th>Total</th>
                                                        <th>Ride Status</th>
                                                        <th>Driver Earning</th>
                                                        <th>Site Commission</th>
                                                        <th>Pay to Driver</th>
                                                        <th>Collect from Driver</th>
                                                        <th>Type</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if(isset($payment_reports))
                                                            @foreach($payment_reports as $key => $report_details)
                                                                <tr id="hide_{{ $report_details->id }}">
                                                                    <td><div class="border-checkbox-section"> <div class="border-checkbox-group border-checkbox-group-primary"> <input class="border-checkbox payment_settled" type="checkbox" name="payment_settled[]" id="payment_{{ $report_details->id }}" value="{{ $report_details->id }}"> <label class="border-checkbox-label" for="payment_{{ $report_details->id }}"> </label> </div> </div> </td>
                                                                    <td>{{ ($report_details->driver_pay_settle_status == 1)? "Settled" : "Unsettled" }}</td>
                                                                    <td>{{ Carbon\Carbon::parse($report_details->pickup_datetime)->format('d F Y H:i:s') }}</td>
                                                                    <td>{{ ($report_details->ride_type == 1)? "Schedule" : "Now" }}</td>
                                                                    <td>{{ $report_details->ride_no }}</td>
                                                                    <td>{{ ucwords($report_details->service_category_name) }}</td>
                                                                    <td>{{ ucwords($report_details->user_name) }}</td>
                                                                    <td>{{ ucwords($report_details->driver_name) }}</td>
                                                                    <td class=""><span class="currency"></span> {{ round(($report_details->base_fare + $report_details->total_distance_amount + $report_details->time_fare_amount + $report_details->adjustment_amount ), 2) }} </td>
                                                                    <td class=""><span class="currency"></span>@if(isset($used_promocode_amount[$report_details->ride_no])) {{ $used_promocode_amount[$report_details->ride_no] }} @else 0 @endif </td>
                                                                    <td class=""><span class="currency"></span> {{ $report_details->tax }} </td>
                                                                    <td class=""><span class="currency"></span> {{ $report_details->tip }} </td>
                                                                    <td class=""><span class="currency"></span> {{ $report_details->total_pay }} </td>
                                                                    <td>Completed</td>
                                                                    <td class=""><span class="currency"></span> {{ $report_details->driver_amount }} </td>
                                                                    <td class=""><span class="currency"></span> {{ $report_details->admin_commission }} </td>
                                                                    <td>@if($report_details->payment_type == 2 || $report_details->payment_type == 3) <span class=""><span class="currency"></span> {{ $driver_commission[$report_details->ride_no] }} </span> @else ---- @endif </td>
                                                                    <td>@if($report_details->payment_type == 1) <span class="currency"></span>{{ $collect_payment[$report_details->ride_no] }} @else ---- @endif </td>
                                                                    <td>@if($report_details->payment_type == 1) Cash @else @if($report_details->payment_type == 2) Card @else @if($report_details->payment_type == 3) Wallet @else ---- @endif @endif @endif </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr class="odd"><td valign="top" colspan="18" class="dataTables_empty">No data available in table.</td></tr>
                                                        @endif
                                                    </tbody>
                                                    <tfoot>
                                                    @if(isset($payment_reports) && !$payment_reports->isEmpty())
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Fare:</th>
                                                            <th class=""><span class="currency"></span> @if(isset($total_ride)) {{ $total_ride }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Site Commission:</th>
                                                            <th class="currency">@if(isset($total_site_earning)){{ $total_site_earning }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Driver Earning:</th>
                                                            <th class=""><span class="currency"></span>@if(isset($total_driver_earning)) {{ $total_driver_earning }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Discount:</th>
                                                            <th class=""><span class="currency"></span> @if(isset($total_discount)){{ $total_discount }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Pay to Driver:</th>
                                                            <th class=""><span class="currency"></span>@if(isset($total_pay_driver)) {{ $total_pay_driver }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Collect from Driver:</th>
                                                            <th class=""><span class="currency"></span> @if(isset($total_collect_payment)) {{ $total_collect_payment }} @else 0 @endif </th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="18" style="text-align:right">Total Driver Outstanding Amount:</th>
                                                            <th class=""><span class="currency"></span> @if(isset($total_driver_outstanding_amount)) {{ $total_driver_outstanding_amount }} @else 0 @endif </th>
                                                        </tr>
                                                    @endif
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <div class="text-center">
                                                @if(isset($payment_reports))
                                                    <input type="submit" class="btn btn-default" value="Mark As Settled">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </form>


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
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>

    {{--<script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}" type="text/javascript"></script>--}}
    {{--<script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}" type="text/javascript"></script>--}}
    {{--<script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}" type="text/javascript"></script>--}}
    {{--<script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>--}}

    <script type="text/javascript">
        $(document).ready(function () {
            $('#new-cons').DataTable({
                dom: 'Bfrtip',
                searching: false,
                bPaginate: false,
                buttons: [
                    // 'excel'
                    {
                        extend: 'excel',
                        text: 'Download Excel',
                        footer: true
                    }]
            });
        });
    </script>

    {{--Model Script type detials--}}
    <script src="{{ asset('assets/js/classie.js')}}" type="text/javascript"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $("#js-example1").select2({
            placeholder: "Select a Driver",
            allowClear: true,
        });
        $("#js-example2").select2({
            placeholder: "Select a Customer",
            allowClear: true,
        });
    </script>
    <script>

        $(document).ready(function () {
            // bind event handlers when the page loads.
            $("#all_settled").click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });
        });
    </script>
    <script>
        $("#today").click(function () {
            var current_date = new Date();
            var year = current_date.getFullYear();
            var month = (current_date.getMonth() + 1);
            var date = (current_date.getDate());

//            var get_from_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_from_dt = (date < 10 ? '0' : '') + date + '-' + (month < 10 ? '0' : '') + month + '-' + year;

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_from_dt);

        });

        $("#yesterday").click(function () {

            // var ddd = "Thu Apr 03 2021 17:54:37 GMT+0530 (India Standard Time)";
            // var current_date = new Date(ddd);
            var current_date = new Date();
            current_date.setDate(current_date.getDate() - 1);
            var year = current_date.getFullYear();
            var month = (current_date.getMonth() + 1);
            var date = (current_date.getDate());

//            var get_from_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_from_dt = (date < 10 ? '0' : '') + date + '-' + (month < 10 ? '0' : '') + month + '-' + year;

            console.log(current_date);

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_from_dt);

        });

        $("#this_week").click(function () {
            var curr = new Date; // get current date
            curr.setDate(curr.getDate() - 1);
            var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
            var last = first + 6; // last day is the first day + 6

            var firstday = new Date(curr.setDate(first));
            var firstyear = firstday.getFullYear();
            var firstmonth = (firstday.getMonth() + 1);
            var firstdate = (firstday.getDate());
//            var get_from_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_from_dt = (firstdate < 10 ? '0' : '') + firstdate + '-' + (firstmonth < 10 ? '0' : '') + firstmonth + '-' + firstyear;

            var lastday = new Date(curr.setDate(last));
            var lastyear = lastday.getFullYear();
            var lastmonth = (lastday.getMonth() + 1);
            var lastdate = (lastday.getDate());
//            var get_to_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_to_dt = (lastdate < 10 ? '0' : '') + lastdate + '-' + (lastmonth < 10 ? '0' : '') + lastmonth + '-' + lastyear;

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_to_dt);

        });

        $("#this_month").click(function () {
            var date = new Date();
            var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

            var year = firstDay.getFullYear();
            var month = (firstDay.getMonth() + 1);
            var date = (firstDay.getDate());
//            var get_from_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_from_dt = (date < 10 ? '0' : '') + date + '-' + (month < 10 ? '0' : '') + month + '-' + year;

            var year = lastDay.getFullYear();
            var month = (lastDay.getMonth() + 1);
            var date = (lastDay.getDate());
//            var get_to_dt = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            var get_to_dt = (date < 10 ? '0' : '') + date + '-' + (month < 10 ? '0' : '') + month + '-' + year;

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_to_dt);
        });

        $("#last_month").click(function () {
            var now = new Date();
            var prevMonthLastDate = new Date(now.getFullYear(), now.getMonth(), 0);
            var prevMonthFirstDate = new Date(now.getFullYear() - (now.getMonth() > 0 ? 0 : 1), (now.getMonth() - 1 + 12) % 12, 1);

            var formatDateComponent = function (dateComponent) {
                return (dateComponent < 10 ? '0' : '') + dateComponent;
            };
            var formatDate = function (date) {
//                return date.getFullYear() + '-' + formatDateComponent(date.getMonth() + 1) + '-' + formatDateComponent(date.getDate());
                return formatDateComponent(date.getDate()) + '-' + formatDateComponent(date.getMonth() + 1) + '-' + date.getFullYear();
            };
            $("#from_date").val(formatDate(prevMonthFirstDate));
            $("#to_date").val(formatDate(prevMonthLastDate));
        });

        $("#this_year").click(function () {
            var date = new Date();
//            var get_from_dt = (new Date()).getFullYear() + '-' + "01" + '-' + "01";
//            var get_to_dt = (new Date()).getFullYear() + '-' + "12" + '-' + "31";
            var get_from_dt = "01" + '-' + "01" + '-' + (new Date()).getFullYear();
            var get_to_dt = "31" + '-' + "12" + '-' + (new Date()).getFullYear();

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_to_dt);
        });

        $("#last_year").click(function () {
            var date = new Date();
//            var get_from_dt = ((new Date()).getFullYear() - 1) + '-' + "01" + '-' + "01";
//            var get_to_dt = ((new Date()).getFullYear() - 1) + '-' + "12" + '-' + "31";
            var get_from_dt = "01" + '-' + "01" + '-' + ((new Date()).getFullYear() - 1);
            var get_to_dt = "31" + '-' + "12" + '-' + ((new Date()).getFullYear() - 1);

            $("#from_date").val(get_from_dt);
            $("#to_date").val(get_to_dt);
        });
    </script>

    <script type="text/javascript" src="{{asset('assets/js/bootstrap-datetimepicker.js')}}" charset="UTF-8"></script>
    <script type="text/javascript">
        var param1 = new Date();
        var param2 = param1.getFullYear() + '-' + (param1.getMonth() + 1) + '-' + (param1.getDate() + 4) + ' 23:59';
        $('.form_datetime').datetimepicker({
            // format: "dd-mm-yyyy hh:ii",
            minView: 2,
            format: "yyyy-mm-dd",
            autoclose: true,
            clear: 'Clear selection',
        });
        var param1 = new Date();
        var param2 = param1.getFullYear() + '-' + (param1.getMonth() + 1) + '-' + (param1.getDate() + 4);
        $('.to_datetime').datetimepicker({
            // format: "dd-mm-yyyy hh:ii",
            minView: 2,
            format: "yyyy-mm-dd",
            autoclose: true,
            clear: 'Clear selection',
        });

        $(".remove_from_date").click(function () {
            $('#from_date').val("").datetimepicker("update");
            $('#to_date').val("").datetimepicker("update");
        });
        $(".remove_to_date").click(function () {
            $('#from_date').val("").datetimepicker("update");
            $('#to_date').val("").datetimepicker("update");
        });
    </script>
@endsection

