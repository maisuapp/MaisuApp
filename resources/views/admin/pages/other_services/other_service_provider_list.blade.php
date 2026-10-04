@extends('admin.layout.super_admin')
@section('title')
    @if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0)
        Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif
    @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @else Other Service
    @endif Provider List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    @php $category_type = \App\Http\Controllers\OtherServiceController::checkCategoryType($slug); @endphp
    <style>
        .extra-color i {
            color: #653996 !important;
        }

         @if($category_type == 3)

        .md-trigger:hover {
            color: #653996;
            cursor: pointer;
        }

        .url-link a {
            background: #653996;
        }

        .icon-url-link a {
            color: #653996;
        }

        @elseif($category_type == 4)
            .extra-color i {
                color: #653996 !important;
            }

            .md-trigger:hover {
                color: #653996;
                cursor: pointer;
            }

            .url-link a {
                background: #653996;
            }

            .icon-url-link a {
                color: #653996;
            }

        @else
            .extra-color i {
                color: #653996 !important;
            }
        @endif


        .md-trigger img:hover {
            opacity: 0.7;
            cursor: pointer;
        }

        .approve, .reject {
            cursor: pointer;
        }

        @if(isset($status) && $status==2 || isset($status) && $status==3)
            .toggle input[type="checkbox"]:checked + .button-indecator:before {
                color: red;
            }
        @endif
    </style>
    <style>
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
        .md-content > .wrapper {
            padding: 15px 25px 30px 25px;
            margin: 0;
            font-size: 1em;
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

        .btn_model_send {
            /*background: #6f09f5 !important;*/
            min-width: unset !important;
            padding: 5px 18px !important;
        }

        .btn_model_close {
            min-width: unset !important;
            padding: 5px 18px !important;
        }
        .pass{
            color: #f5090a;
        }
        .pass:focus, .pass:hover {
            text-decoration: none;
            color: #4099ff
        }
        .error {
            color: red;
            font-weight: 500;
        }

        .text-model {
            margin-bottom: 10px;
        }
        .approve, .reject {
            cursor: pointer;
        }
        @if(isset($status) && $status==2 || isset($status) && $status==3)
            .toggle input[type="checkbox"]:checked + .button-indecator:before {
            color: red;
        }
        @endif

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
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <div class="external-horizontal-nav">
            @include('admin.include.other-service-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list" style="background-color: #653996"></i>
                        <div class="d-inline">
                            <h5>@if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0)
                                    Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif

                                @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @else
                                    Other
                                @endif Service Providers</h5>
                            <span>All @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @else
                                    Other Service @endif Service Provider List</span>
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
                                <h5>
                                    @if(isset($status) && $status == 1 )
                                        Approved @elseif(isset($status) && $status == 0)
                                        Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif
                                    @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @else
                                        Other
                                    @endif Service Provider List</h5>
                                {{--<a href="{{ route('get:admin:other_service_list') }}"--}}
                                {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Provider Name</th>
                                            {{--<th>City</th>--}}
                                            <th>Ratings</th>
                                            <th>Packages</th>
                                            <th>Orders</th>
                                            <th>Documents</th>
                                            @if(isset($status)&& in_array($status, [1]))
                                            <th>Sponsor Provider</th>
                                            @endif

                                            @if(isset($status)&& in_array($status, [0,2,3]))
                                                <th>Sign-Up Time</th>
                                            @endif
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($providers))
                                            @foreach($providers as $key => $provider)
                                                <tr class="extra-color" id="hide_{{$provider->provider_id}}">
                                                    <td>{{ $key+1 }}</td>
                                                    <td>{{ $provider->name }}</td>
                                                    {{--<td>Georgia</td>--}}
                                                    <td class="icon-url-link">
                                                        <a style="color: #653996" href="{{ route('get:admin:provider_review_list',[$slug,$provider->id]) }}"
                                                           class="render_link">
                                                            <span class="data-table-main icon-list-demo"
                                                                  {{-- data-toggle="tooltip" data-placement="top" --}}
                                                                  title="Rating List">
                                                                <i style="color: #653996" class="fa fa-star"></i> {{ $provider->rating }}
                                                            </span>
                                                        </a>
                                                    </td>
                                                    <td class="url-link">
                                                        <a href="{{ route('get:admin:provider_package_list',[$slug,$provider->id]) }}"
                                                           class="render_link"
                                                           {{-- data-toggle="tooltip" data-placement="top" --}}
                                                           title="Package List">
                                                            {{--<img src="{{ asset('/assets/images/template-images/menu.png') }}">--}}
                                                            view
                                                            @if(isset($package_count))
                                                                @if(array_key_exists($provider->provider_id,$package_count))
                                                                    ({{ $package_count[$provider->provider_id] }})
                                                                @else
                                                                    (0)
                                                                @endif
                                                            @else
                                                                (0)
                                                            @endif
                                                            {{--(5)--}}
                                                        </a>
                                                    </td>
                                                    <td class="icon-url-link">
                                                        <a href="{{ route('get:admin:other_service_provider_order_list',[$slug,$provider->id]) }}"
                                                           class="render_link">
                                                            <div class="data-table-main icon-list-demo">
                                                                <i class="fa fa-shopping-cart" style="color: #653996"
                                                                 {{--  data-toggle="tooltip" data-placement="top" --}}
                                                                 title="Order List"></i>
                                                            </div>
                                                        </a>
                                                    </td>
                                                    <td class="icon-url-link">
                                                        <a href="{{ route('get:admin:provider_document',[$slug,$provider->provider_id]) }}"
                                                           class="render_link">
                                                            <div class="data-table-main icon-list-demo">
                                                                <i class="fa fa-file-text"
                                                                {{--  data-toggle="tooltip" data-placement="top" --}}
                                                                   title="Document List"></i>
                                                            </div>
                                                        </a>
                                                    </td>
                                                    @if(isset($status)&& in_array($status, [1]))
                                                        <td class="icon-url-link">
                                                            <span class="toggle">
                                                                <label>
                                                                    <input name="manual_assign" class="form-control is_sponsor"
                                                                           type="checkbox"
                                                                           id="sponsor_{{$provider->id}}"
                                                                           serid="{{$provider->id}}"
                                                                           providerid="{{$provider->provider_id}}"
                                                                           is_sponsor="{{$provider->is_sponsor}}"
                                                                            {{ $provider->is_sponsor == 1 ? 'checked' : '' }}>
                                                                    <span class="button-indecator" data-toggle="tooltip"
                                                                          data-placement="top" title="Active"></span>
                                                                </label>
                                                            </span>
                                                        </td>

                                                    @endif
                                                    @if(isset($status)&& in_array($status, [0,2,3]))
                                                        <td>{{ date("d-M Y h:i A",strtotime($provider->created_at) )}}</td>
                                                    @endif
                                                    <td class="action">
                                                        {{--<span class="toggle">--}}
                                                        {{--<label>--}}
                                                        {{--<input name="manual_assign"--}}
                                                        {{--class="form-control provider_status"--}}
                                                        {{--provider_id="{{$provider->id}}"--}}
                                                        {{--provider_status="{{$provider->status}}"--}}
                                                        {{--type="checkbox"--}}
                                                        {{--@if($provider->status == 1) checked @endif>--}}
                                                        {{--<span class="button-indecator" data-toggle="tooltip"--}}
                                                        {{--data-placement="top" title="Active"></span>--}}
                                                        {{--</label>--}}
                                                        {{--</span>--}}
                                                        {{--<a class="delete" provider_id="{{$provider->id}}">--}}
                                                        {{--<img src="{{ asset('/assets/images/template-images/remove-1.png') }}"--}}
                                                        {{--style="width:20px; height: 20px;" data-toggle="tooltip"--}}
                                                        {{--data-placement="top" title="Delete">--}}
                                                        {{--</a>--}}
                                                        @if($status == 0)
                                                            <a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumbs-up.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     {{-- data-toggle="tooltip" data-placement="top"--}}
                                                                      class="approve"
                                                                     providerid="{{$provider->provider_id}}"
                                                                      title="Approve">
                                                            </a><a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumb-down.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     providerid="{{$provider->provider_id}}"
                                                                     {{--data-toggle="tooltip" data-placement="top" --}}
                                                                     title="Reject" class="reject">
                                                            </a>
                                                        @elseif($status == 1)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status block"
                                                                   type="checkbox" checked
                                                                   providerid="{{$provider->provider_id}}"><span
                                                                            class="button-indecator"
{{--                                                                            data-toggle="tooltip" data-placement="top"--}}
                                                                            title="Active"></span></label>
                                                                </span>
                                                            <a href="{{ route('get:admin:other_service_edit_provider',[$slug,$provider->id]) }}"
                                                                    {{--class="render_link"--}}
                                                            >
                                                                <img src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     {{--data-toggle="tooltip" data-placement="top" --}}
                                                                      title="Edit">
                                                            </a>
                                                            <span title="Change Password"
                                                                  class="md-trigger change-password text-c-orenge"
                                                                  data-modal="modal-2" data-toggle="tooltip"
                                                                  provider_name="{{$provider->name}}"
                                                                  providerid="{{$provider->provider_id}}"
                                                            >
                                                            <i class="fa fa-key"></i>

                                                            {{--<span title="Change Password"--}}
                                                            {{--class="md-trigger text-c-orenge"--}}
                                                            {{--data-modal="modal-2" data-toggle="tooltip"--}}
                                                            {{--provider_name="{{ $store_detail->provider_name }}"--}}

                                                            {{--store_id="{{ $store_detail->store_id }}"--}}
                                                            {{-->--}}
                                                            {{--<i class="fa fa-key"></i>--}}
                                                            {{--</span>--}}
                                                        @elseif($status == 2)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status unblock"
                                                                   type="checkbox" checked
                                                                   providerid="{{$provider->provider_id}}"><span
                                                                            class="button-indecator"
                                                                    {{--data-toggle="tooltip" data-placement="top" --}}
                                                                            title="Blocked"></span></label>
                                                        </span>
                                                        @else
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status approve"
                                                                   type="checkbox" checked
                                                                   providerid="{{$provider->provider_id}}"><span
                                                                            class="button-indecator"
                                                                    {{--data-toggle="tooltip" data-placement="top" --}}
                                                                            title="Rejected"></span></label>
                                                        </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
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
    <div class="md-modal md-effect-1" id="modal-2">
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
    <script type="text/javascript">
        {{--$(document).on('click', '.delete', function (e) {--}}
        {{--e.preventDefault();--}}
        {{--var id = $(this).attr('provider_id');--}}
        {{--var slug = "{{ $slug }}";--}}
        {{--swal({--}}
        {{--title: "Are you sure?",--}}
        {{--text: "You will not be able to recover this Data!",--}}
        {{--type: "warning",--}}
        {{--showCancelButton: true,--}}
        {{--confirmButtonClass: "btn-danger",--}}
        {{--confirmButtonText: "Yes, delete it!",--}}
        {{--cancelButtonText: "No, cancel!",--}}
        {{--closeOnConfirm: false,--}}
        {{--closeOnCancel: false--}}
        {{--},--}}
        {{--function (isConfirm) {--}}
        {{--if (isConfirm) {--}}
        {{--$.ajax({--}}
        {{--type: 'get',--}}
        {{--                            url: '{{ route('get:admin:other_service_delete_provider') }}',--}}
        {{--data: {id: id, slug: slug},--}}
        {{--success: function (result) {--}}
        {{--if (result.success == true) {--}}
        {{--//                                    location.reload();--}}
        {{--var new_id = '#provider_' + id;--}}
        {{--swal("Success", "provider remove Successfully", "success");--}}
        {{--$(new_id).hide();--}}
        {{--}--}}
        {{--else {--}}
        {{--console.log(result);--}}
        {{--}--}}
        {{--}--}}
        {{--})--}}
        {{--} else {--}}
        {{--swal("Cancelled", "Your Data is safe :)", "error");--}}
        {{--}--}}
        {{--});--}}
        {{--});--}}
        $(document).on('click', '.block', function (e) {
            e.preventDefault();
            var id = $(this).attr('providerid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Block Provider Service?",
                    text: "if press yes then provider service is block!",
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
                            url: '{{ route('get:admin:update_other_service_provider_status') }}',
                            data: {id: id, request_for: 2, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "provider service block successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "provider service status not change", "error");
                    }
                });
        });
        $(document).on('click', '.unblock', function (e) {
            e.preventDefault();
            var id = $(this).attr('providerid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Unblock Provider Service?",
                    text: "if press yes then provider service is unblock!",
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
                            url: '{{ route('get:admin:update_other_service_provider_status') }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "provider service unblock successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "provider service status not change", "error");
                    }
                });
        });
        $(document).on('click', '.approve', function (e) {
            e.preventDefault();
            var id = $(this).attr('providerid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Approve Provider Service?",
                    text: "if press yes then provider service is approved!",
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
                            url: '{{ route('get:admin:update_other_service_provider_status') }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "provider service approve successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "provider service status not change", "error");
                    }
                });
        });
        $(document).on('click', '.reject', function (e) {
            e.preventDefault();
            var id = $(this).attr('providerid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Reject Provider Service?",
                    text: "if press yes then provider service is rejected!",
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
                                title: "Reject Provider Service!",
                                text: "if reject store then Write reject reason:",
                                type: "input",
                                showCancelButton: true,
                                closeOnConfirm: false,
                                animation: "slide-from-top",
                                inputPlaceholder: "Reject Reason"
                            },
                            function (inputValue) {
                                if (inputValue === false) return false;
                                if (inputValue === "") {
                                    swal.showInputError("You need to write reject provider service!");
                                    return false
                                }
                                $.ajax({
                                    type: 'get',
                                    url: '{{ route('get:admin:update_other_service_provider_status') }}',
                                    data: {
                                        id: id,
                                        request_for: 3,
                                        service_cat_id: service_cat_id,
                                        reject_reason: inputValue
                                    },
                                    success: function (result) {
                                        if (result.success == true) {
//                                    location.reload();
                                            var new_id = "#hide_" + id;
                                            swal("Success", "provider service reject successfully", "success");
                                            $(new_id).hide();
                                        }else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                    }
                                })


                            });
                    } else {
                        swal("Cancelled", "provider service status not change", "error");
                    }
                });
        });

        $(document).on('click', '.provider_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('provider_id');
            var status = $(this).attr('provider_status');
            var txt, title, provider_status, slug;
            slug = "{{ $slug }}";
            if (status == 1) {
                title = "Are You Blocked this Provider?";
                txt = "if press yes then Blocked this Provider!";
                provider_status = 2;
            } else {
                title = "Are You Approved this Provider?";
                txt = "if press yes then Approved this Provider!";
                provider_status = 1;
            }
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
                            {{--                            url: '{{ route('get:admin:other_service_change_provider_status') }}',--}}
                            data: {id: id, status: provider_status, slug: slug},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = '#provider_' + id;
                                    if (result.status == 1) {
                                        swal("Success", "Provider Approved Successfully", "success");
                                    } else {
                                        swal("Success", "Provider Blocked Successfully", "success");
                                    }
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Category is Enable", "error");
                        } else {
                            swal("Cancelled", "Category is Disable", "error");
                        }
                    }
                });
        });

        $(document).on('click', '.is_sponsor', function (e) {
            e.preventDefault();
            var id = $(this).attr('providerid');
            var providerid = $(this).attr('serid');
            var status = $(this).attr('is_sponsor');
            var txt, title, provider_sponsor_status, slug;
            slug = "{{ $slug }}";

            if (status == 0) {
                title = "Are You Add this Provider as Sponsor?";
                txt = "if press yes then  Provider add as Sponsor!";
                provider_sponsor_status = 1;
            } else {
                title = "Are You Remove this Provider as Sponsor?";
                txt = "if press yes then Provider remove as Sponsor!";
                provider_sponsor_status = 0;
            }

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
                            url: '{{ route('get:admin:other_service_change_provider_sponsor') }}',
                            data: {id: id,providerid:providerid, status: provider_sponsor_status, slug: slug},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#sponsor_"+providerid;

                                    if (result.status == 1) {
                                        $(new_id).prop("checked", true);
                                        $(new_id).attr("is_sponsor", 1);
                                        swal("Success", "Provider added as sponsor Successfully", "success");
                                    } else {
                                        $(new_id).prop("checked", false);
                                        $(new_id).attr("is_sponsor", 0);
                                        swal("Success", "Provider removed as sponsor Successfully", "success");
                                    }

                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "sponsor is added", "error");
                        } else {
                            swal("Cancelled", "sponsor is removed", "error");
                        }
                    }
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
                var modal = document.querySelector('#modal-2');
                close = modal.querySelector('.md-close');
                classie.add(modal, 'md-show');
            });
            $(document).on("click",".md-close",function (){
                var modal = document.querySelector('#modal-2');
                classie.remove(modal, 'md-show');
                $("#change_password_form")[0].reset();
                $("#send_message").text("");
                $("#send_message").hide();
                $('#fail_message').text("");
                $("#fail_message").show();
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
