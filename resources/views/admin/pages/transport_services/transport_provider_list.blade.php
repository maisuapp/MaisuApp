@extends('admin.layout.super_admin')
@section('title')
    @if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0) Un-Approved @elseif(isset($status) && $status == 2) Blocked @elseif(isset($status) && $status == 3) Rejected @endif Drivers List
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

        .page-link {
            color: #55d090;
        }

        /*datatable td link*/
        .document a, .ride a, .ratings a {
            color: #55d090;
            font-weight: bold;
            font-size: 14px;
        }

        .document i, .ride i, .ratings i {
            font-size: 18px;
        }

        .ratings i {
            font-size: 16px;
        }

        .icon-list-demo i {
            height: auto;
            line-height: 10px;
            border: none;
            margin-right: 5px;
            color: #55d090;
        }

        /* Status style*/
        .toggle {
            display: inline-block;
        }

        .toggle input[type="checkbox"]:checked + .button-indecator:before {
            color: #55d090;
        }

        .toggle input[type="checkbox"] + .button-indecator:before {
            color: #55d090;
        }

        /* Vehicle type styles for the modal */
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
            border: 2px solid #2ed8b6;
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
        #cover-spin   {
            position:fixed;
            width:100%;
            left:0;right:0;top:0;bottom:0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index:9999;
            /*display:none;*/
        }
        #cover-spin::after {
            content:'';
            display:block;
            position:absolute;
            left:48%;
            top:40%;
            width:50px;
            height:50px;
            border-style:solid;
            border-color:black;
            border-top-color:transparent;
            border-width: 4px;
            border-radius:50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }
        @-webkit-keyframes spin {
            from {-webkit-transform:rotate(0deg);}
            to {-webkit-transform:rotate(360deg);}
        }

        @keyframes spin {
            from {transform:rotate(0deg);}
            to {transform:rotate(360deg);}
        }
        @if((isset($status) && $status==2) || (isset($status) && $status==3))
            .toggle input[type="checkbox"]:checked + .button-indecator:before {
            color: red;
        }
        @endif
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">

        <div class="other-service-horizontal-nav">
{{--            @include('admin.include.transport-horizontal-navbar')--}}
        </div>

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5> @if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0)
                                    Un-Approved @elseif(isset($status) && $status == 2)
                                    Blocked @elseif(isset($status) && $status == 3) Rejected @endif Drivers List</h5>
                            <span>All Drivers List</span>
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
                                <h5> Drivers List</h5>
                                {{--<a href="{{ route('get:transport:transport_service_list') }}"--}}
                                {{--class="btn btn-success m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="drivers" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Vehicle Type</th>
                                            <th>Service Type</th>
                                            <th>Driver Name</th>
                                            <th>Email</th>
                                            <th>Contact No.</th>
                                            <th>Rating</th>
                                            @if((isset($status) && $status == 1) || (isset($status) && $status == 2))
                                            <th>Trip</th>
                                            @endif
                                            <th>Documents</th>
                                            @if(isset($status)&& in_array($status, [0,2,3]))
                                                <th>Sign-Up Time</th>
                                            @endif
                                            <th>App Version</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {{--@if(isset($provider_lists))
                                            @foreach($provider_lists as $key => $provider_list)
                                                <tr id="hide_{{$provider_list->provider_service_id}}">
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="td-icon">
                                                        <span title="Vehicle Details" class="md-trigger"
                                                              data-modal="modal-1" data-toggle="tooltip"
                                                              model_type="0"
                                                              driver_name="{{ ucwords($provider_list->driver_name) }}"
                                                              vehicle_type_name="{{ ucwords($provider_list->vehicle_type_name) }}"
                                                              vehicle_company="{{ ucwords($provider_list->model_name." ".$provider_list->vehicle_company) }}"
                                                              plat_no="{{ $provider_list->plat_no }}"
                                                              model_year="{{ $provider_list->model_year }}"
                                                              color="{{ ($provider_list->color != Null)? ucwords($provider_list->color) : "----" }}"
                                                              @if(isset($service_category) && $service_category->id == 2)
                                                                  handicap="{{ ($provider_list->handicap == 0)? 'No' : 'Yes' }}"
                                                                  child_seat="{{ ($provider_list->child_seat == 0)? 'No' : 'Yes' }}"
                                                              @endif
                                                              url="{{ route('get:admin:edit_transport_provider_vehicle_details',[$slug, $provider_list->driver_details_id ]) }}"
                                                              vehicle_id="{{ $provider_list->driver_details_id }}">
                                                            @if($provider_list->type_icon != Null)
                                                                <img id="vehicle_type_icon_{{ $provider_list->driver_details_id }}"
                                                                     src="{{ asset('/assets/images/service-category/transport-service-type/'.$provider_list->type_icon )}}"
                                                                     width="26" height="26">
                                                            @else
                                                                ----
                                                            @endif
                                                        </span>
                                                    </td>
--}}{{--                                                    <td>{{ $provider_list->handicap }}</td>--}}{{--

                                                    <td class="td-icon">
                                                        <span title="Vehicle Details" class="md-trigger"
                                                              data-modal="modal-2" data-toggle="tooltip"
                                                              model_type="1"
                                                              driver_name="{{ ucwords($provider_list->driver_name) }}"

                                                              total_request="{{ ($provider_list->total_request == Null)? '0' : $provider_list->total_request }}"
                                                              total_completed="{{ ($provider_list->total_completed == Null)? '0' : $provider_list->total_completed }}"
                                                              total_cancelled="{{ ($provider_list->total_cancelled == Null)? '0' : $provider_list->total_cancelled }}"
                                                        >
                                                            {{ ucwords($provider_list->driver_name) }}
                                                        </span>
                                                    </td>

                                                    <td>{{ $provider_list->email }}</td>
                                                    <td>{{ $provider_list->contact_number }}</td>
                                                    @if(isset($slug) && $slug == "courier-service")
                                                    @else
                                                        <td class="ratings">
                                                            <a href="{{ route('get:admin:transport_provider_review_list',['service_cat_slug' => $slug, 'driver_id' => $provider_list->driver_details_id ]) }}"
                                                               class="render_link"
                                                               --}}{{--                                                               data-toggle="tooltip" data-placement="top"--}}{{--
                                                               title="Rating List">
                                                    <span class="icon-list-demo">
                                                        <i class="fa fa-star"></i>{{ ($provider_list->rating == Null)? '0' : $provider_list->rating }}
                                                    </span>
                                                            </a>
                                                        </td>
                                                    @endif
                                                    @if((isset($status) && $status == 1) || (isset($status) && $status == 2))
                                                        <td class="ride">
                                                            <a href="{{ route('get:admin:transport_provider_ride_list',['service_cat_slug' => $slug, 'driver_id' => $provider_list->driver_details_id ]) }}"
                                                               class="render_link"
                                                               title="{{ (isset($slug) && $slug == "courier-service")? 'Delivery' : 'Ride' }} List">
                                                    <span class="icon-list-demo"> @if(isset($slug) && $slug == "courier-service")
                                                            <i class="fa fa-truck"></i>
                                                        @elseif(isset($slug) && $slug == "bike-ride")
                                                            <i class="fa fa-motorcycle"></i>
                                                        @else
                                                            <i class="fa fa-car"></i>
                                                        @endif
                                                        </span>
                                                            </a>
                                                        </td>
                                                    @endif
                                                    <td class="document">
                                                        <a href="{{ route('get:admin:transport_provider_document',['service_cat_slug' => $slug, 'provider_service_id' => $provider_list->provider_service_id]) }}"
                                                           class="render_link" data-toggle="tooltip"
                                                           data-placement="top" title="Document List">
                                                    <span class="icon-list-demo">
                                                        <i class="fa fa-file-text"></i>
                                                    </span>
                                                        </a>
                                                    </td>
                                                    @if(isset($status)&& in_array($status, [0,2,3]))
                                                        <td>{{ date("d-M Y h:i A",strtotime($provider_list->created_at) )}}</td>
                                                    @endif
                                                    <td class="action">
                                                        @if($status == 0)
                                                            <a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumbs-up.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     data-toggle="tooltip" class="approve"
                                                                     provider_service_id="{{$provider_list->provider_service_id}}"
                                                                     data-placement="top" title="Approve">
                                                            </a>
                                                            <a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumb-down.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     data-toggle="tooltip" class="reject"
                                                                     provider_service_id="{{$provider_list->provider_service_id}}"
                                                                     data-placement="top" title="Reject">
                                                            </a>
                                                        @elseif($status == 1)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status block"
                                                                   type="checkbox" checked
                                                                   provider_service_id="{{$provider_list->provider_service_id}}"><span
                                                                            class="button-indecator"
                                                                            data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Active"></span></label>
                                                        </span>
                                                        @elseif($status == 2)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status unblock"
                                                                   type="checkbox" checked
                                                                   provider_service_id="{{$provider_list->provider_service_id}}"><span
                                                                            class="button-indecator"
                                                                            data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Active"></span></label>
                                                        </span>
                                                        @else
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status approve"
                                                                   type="checkbox" checked
                                                                   provider_service_id="{{$provider_list->provider_service_id}}"><span
                                                                            class="button-indecator"
                                                                            data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Active"></span></label>
                                                        </span>
                                                        @endif

                                                        <a href="{{ route('get:admin:edit_transport_provider',[$slug, $provider_list->driver_details_id]) }}"
                                                           class="render_link edit">
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                 style="width:20px; height: 20px;"
                                                                 data-toggle="tooltip"
                                                                 data-placement="top" title="Edit">
                                                        </a>
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

    <div class="md-modal md-effect-1" id="modal-1">
        <div class="md-content">
            <h3 class="bg-c-green" id="driver_name_type">Nixon</h3>
            <div>
                <div>
                    <img id="vehicle_icon" src="">
                </div>
                <ul>
                    <li><strong>Vehicle Type : </strong> <span id="vehicle_name"></span></li>
                    <li><strong>Model Name : </strong> <span id="model_name"></span></li>
                    <li><strong>License Plate : </strong> <span id="license_name"></span></li>
                    <li><strong>Vehicle Year : </strong> <span id="vehicle_year"></span></li>
                    <li><strong>Vehicle Color : </strong> <span id="vehicle_color"></span></li>
                    @if(isset($service_category) && $service_category->id == 2)
                        <li><strong>Handicap : </strong> <span id="vehicle_handicap"></span></li>
                        <li><strong>Child Seat : </strong> <span id="vehicle_child_seat"></span></li>
                    @endif
                </ul>

                <a href="" id="driver_vehicle_details">
                    <button type="button" class="btn btn-success waves-effect">Edit</button>
                </a>
                <button type="button" class="btn btn-success waves-effect md-close">Close</button>
            </div>

        </div>
    </div>


    <div class="md-modal md-effect-1" id="modal-2">
        <div class="md-content">
            <h3 class="bg-c-green" id="driver_name">Nixon</h3>
            <div>
                <ul>
                    <li><strong>Total Request : </strong> <span id="total_request"></span></li>
                    <li><strong>Total Completed : </strong> <span id="total_completed"></span></li>
                    <li><strong>Total Rejected : </strong> <span id="total_rejected"></span></li>
                </ul>

                <button type="button" class="btn btn-success waves-effect md-close">Close</button>
            </div>

        </div>
    </div>
    <div class="md-modal md-effect-1" id="modal-3">
        <div class="md-content">
            <h3 class="bg-c-blue">Change Password</h3>
            <div class="wrapper">
                <div id="cover-spin" style="display: none"></div>
                <form method="get" id="change_password_form">
                    <p id="send_message" class="text-success font-weight-bold"></p>
                    <input type="hidden" class="form-control" name="provider_id" id="provider_id" placeholder="Provider id" value="">
                    <div class="form-group">
                        <label class="col-form-label">Password:</label>
                        <input type="password" name="password" class="form-control border-r-top-left-right" required id="password" value="{{ old('password') }}" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">Confirm Password:</label>
                        <input type="password" name="confirm_password" class="form-control border-r-top-left-right" required id="confirm_password" value="{{ old('forgot_confirm_password') }}" placeholder="Confirm Password">
                    </div>

                    <div class="form-group">
                        <p id="fail_message" class="text-danger"></p>
                    </div>
                    <button type="submit" class="btn btn-primary btn_model_send">Submit</button>
                    <button type="button" class="btn btn-login btn_model_close md-close">Close</button>

                </form>
            </div>
        </div>
    </div>
    <div class="md-overlay"></div>
@endsection
@section('page-js')
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>
    <script>
        function init() {

            var overlay = document.querySelector('.md-overlay');
            [].slice.call(document.querySelectorAll('.md-trigger-vehicle')).forEach(function (el, i) {
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
                    var model_type = $(this).attr('model_type');
                    if (model_type == 0) {
                        $('#model_type_driver').css("display", "none");
                        $('#model_type_vehicle').css("display", "block");
                        var vehicle_id = $(this).attr('vehicle_id');
                        var img_path = $('#vehicle_type_icon_' + vehicle_id).attr('src');
                        if (img_path == '') {
                            $("#vehicle_icon").attr("src");
                        } else {
                            $('#vehicle_icon').attr('src', img_path);
                        }
                        var vehicle_type_name = $(this).attr('vehicle_type_name');
                        var vehicle_company = $(this).attr('vehicle_company');
                        var plat_no = $(this).attr('plat_no');
                        var model_year = $(this).attr('model_year');
                        var color = $(this).attr('color');
                        var url = $(this).attr('url');
                        var driver_name = $(this).attr('driver_name');
                        $('#driver_name_type').text(driver_name);
                        $('#vehicle_name').text(vehicle_type_name);
                        $('#model_name').text(vehicle_company);
                        $('#license_name').text(plat_no);
                        $('#vehicle_year').text(model_year);
                        $('#vehicle_color').text(color);
                        $('#driver_vehicle_details').attr('href', url);
                        @if(isset($service_category) && $service_category->id == 2)
                        var get_vehicle_handicap = $(this).attr('handicap');
                        console.log(get_vehicle_handicap);
                        $('#vehicle_handicap').text(get_vehicle_handicap);
                        var get_vehicle_child_seat = $(this).attr('child_seat');
                        $('#vehicle_child_seat').text(get_vehicle_child_seat);
                        @endif
                    }
                    if (model_type == 1) {
                        $('#model_type_vehicle').css("display", "none");
                        $('#model_type_driver').css("display", "block");
                        var total_request = $(this).attr('total_request');
                        var total_completed = $(this).attr('total_completed');
                        var total_cancelled = $(this).attr('total_cancelled');
                        var driver_name = $(this).attr('driver_name');
                        $('#driver_name').text(driver_name);
                        $('#total_request').text(total_request);
                        $('#total_completed').text(total_completed);
                        $('#total_rejected').text(total_cancelled);
                    }

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

        $(document).ready(function () {
            var table = $('#drivers').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: {
                    url: "{{route('get:admin:transport_service_provider_list_new')}}",
                    data: {
                        status : "{{ $status }}",
                        service_category_id : "{{ $service_category_id }}",
                    }
                },
                columns: [
                    {data: 'no'},
                    {data: 'vehicle_type'},
                    {data: 'service_cat_name', 'sortable': false},
                    {data: 'driver_name'},
                    {data: 'email'},
                    {data: 'contact_number'},
                    {data: 'rating'},
                    @if( (isset($status) && $status == 1) ||  (isset($status) && $status == 2))
                        {data: 'rides'},
                    @endif
                    {data: 'documents'},
                    @if(isset($status)&& in_array($status, [0,2,3]))
                        {data: 'sign_up_time'},
                    @endif
                    {data: 'provider_app_version' ,'sortable': false},
                    {data: 'actions', 'sortable': false},
                ],
                drawCallback: function () {
                    //Make your callback here.
                    init();
                },
            });
        });
    </script>

    {{--Model Script type detials--}}
    <script src="{{ asset('assets/js/classie.js')}}" type="text/javascript"></script>


    <script>
        $(document).ready(function (){

            $(document).on("click",".change-password",function (){
                var overlay = document.querySelector('.md-overlay');
                var data_modal = $(this).attr('data-modal');
                var providerid = $(this).attr('providerid');
                $("#provider_id").val(providerid);
                var modal = document.querySelector('#modal-3');
                close = modal.querySelector('.md-close');
                classie.add(modal, 'md-show');
            });
            $(document).on("click",".md-close",function (){
                var modal = document.querySelector('#modal-3');
                classie.remove(modal, 'md-show');
                $("#change_password_form")[0].reset();
                $("#send_message").text("");
                $("#send_message").hide();
                $('#fail_message').text("");
                $("#fail_message").show();
            });
        });
    </script>

    <script>
        $(document).on('click', '.block', function (e) {
            // e.preventDefault();
            let select_element = $(this);
            console.log(select_element);
            var id = $(this).attr('provider_service_id');
            var service_cat_id = $(this).attr('service_cat_id');
{{--            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}--}}
            swal({
                    title: "Block Driver?",
                    text: "if press yes then driver is block!",
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
                            url: '{{ route("get:admin:transport_update_provider_status") }}',
                            data: {id: id, request_for: 2, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "driver block successfully", "success");
                                    $(new_id).hide();
                                    //alert(select_element);
                                    $('.block_element_'+id).closest('.parent').hide();
                                    $('.block_element_'+id).closest('.child').hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "driver status not change", "error");
                    }
                });
        });
        $(document).on('click', '.unblock', function (e) {
            // e.preventDefault();
            var id = $(this).attr('provider_service_id');
            var service_cat_id = $(this).attr('service_cat_id');
{{--            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}--}}
            swal({
                    title: "Unblock Driver?",
                    text: "if press yes then driver is unblock!",
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
                            url: '{{ route("get:admin:transport_update_provider_status") }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "driver unblock successfully", "success");
                                    $(new_id).hide();
                                    $('.unblock_element_'+id).closest('.parent').hide();
                                    $('.unblock_element_'+id).closest('.child').hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Driver status not change", "error");
                    }
                });
        });
        $(document).on('click', '.approve', function (e) {
            // e.preventDefault();
            var id = $(this).attr('provider_service_id');
            var service_cat_id = $(this).attr('service_cat_id');
{{--            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}--}}
            swal({
                    title: "Approve Driver?",
                    text: "if press yes then driver is unblock!",
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
                            url: '{{ route("get:admin:transport_update_provider_status") }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "driver approve successfully", "success");
                                    $(new_id).hide();
                                    $('.approve_element_'+id).closest('.parent').hide();
                                    $('.approve_element_'+id).closest('.child').hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "driver status not change", "error");
                    }
                });
        });
        $(document).on('click', '.reject', function (e) {
            // e.preventDefault();
            var id = $(this).attr('provider_service_id');
            var service_cat_id = $(this).attr('service_cat_id');
{{--            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}--}}
            swal({
                    title: "Reject Driver?",
                    text: "if press yes then driver is unblock!",
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
                                title: "Reject Driver!",
                                text: "if reject driver then Write reject reason:",
                                type: "input",
                                showCancelButton: true,
                                closeOnConfirm: false,
                                animation: "slide-from-top",
                                inputPlaceholder: "Reject Reason"
                            },
                            function (inputValue) {
                                if (inputValue === false) return false;
                                if (inputValue === "") {
                                    swal.showInputError("You need to write reject driver reason!");
                                    return false
                                }
                                $.ajax({
                                    type: 'get',
                                    url: '{{ route("get:admin:transport_update_provider_status") }}',
                                    data: {
                                        id: id,
                                        request_for: 3,
                                        service_cat_id: service_cat_id,
                                        rejected_reason: inputValue
                                    },
                                    success: function (result) {
                                        if (result.success == true) {
                                            var new_id = "#hide_" + id;
                                            swal("Success", "driver reject successfully", "success");
                                            $(new_id).hide();
                                            $('.reject_element_'+id).closest('.parent').hide();
                                            $('.reject_element_'+id).closest('.child').hide();
                                        } else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                    }
                                })
                            });
                    } else {
                        swal("Cancelled", "driver status not change", "error");
                    }
                });
        });
    </script>
    <script rel="stylesheet" src="{{ asset('assets/js/validation/jquery.validate.js')}}"></script>
    <script type="text/javascript">
        $("#change_password_form").validate({
            rules: {
                password: {
                    required : true,
                    minlength:6,
                    maxlength:16,
                },
                confirm_password: {
                    required : true,
                    equalTo : "#password"
                },
            },
            messages: {
                password: {
                    required :"Password field is required",
                },
                confirm_password: {
                    required :"Confirm Password field is required",
                    equalTo : "Password and confirm password not match!"
                },
            },
            submitHandler: function(form) {
                var form_data = $("#change_password_form").serialize();
                $.ajax({
                    type: 'get',
                    url: '{{ route('get:admin:provider_change_password') }}',
                    data: form_data,
                    async:false,
                    cache:false,
                    success: function (result) {
                        $("#cover-spin").css('display',"block");
                        setTimeout(function (){
                            if (result.success == true) {
                                $('#fail_message').text("");
                                $("#fail_message").hide();

                                $("#send_message").text("");
                                $("#send_message").show();
                                $('#send_message').text(result.message);

                                $('#password').val("");
                                $('#confirm_password').val("");

                                $("#cover-spin").css('display',"none");
                                // location.reload();
                            } else {
                                $("#send_message").text("");
                                $("#send_message").hide();

                                $('#fail_message').text("");
                                $("#fail_message").show();
                                $('#fail_message').text(result.message);
                                $("#cover-spin").css('display',"none");
                            }
                        },900);
                    }
                });
            }
        });
    </script>
@endsection

