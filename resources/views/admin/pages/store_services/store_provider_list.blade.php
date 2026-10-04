@extends('admin.layout.super_admin')
@section('title')
    @if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0)
        Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif
    @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }}
    @endif Store Provider List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
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
            z-index:999;
            display:none;
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
        <div class="other-service-horizontal-nav">
            @include('admin.include.store-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>@if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0) Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif
                                @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif Store Provider</h5>
                                <span>All @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif Store Provider List</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>@if(isset($status) && $status == 1 ) Approved @elseif(isset($status) && $status == 0) Un-Approved @elseif(isset($status) && $status == 2) Blocked @endif
                                    @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif Store Provider List</h5>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Store Name</th>
                                            <th>Ratings</th>
                                            <th>Products</th>
                                            @if($general_settings->is_private_driver_module == 1)
                                                <th>Private Driver</th>
                                            @endif
                                            <th>Orders</th>
                                            <th>Documents</th>
                                            @if(isset($status)&& in_array($status, [0,2,3]))
                                                <th>Sign-Up Time</th>
                                                {{--@elseif(isset($status)&& $status == 2)--}}
                                                {{--    <th>Blocked Time</th>--}}
                                                {{--@elseif(isset($status)&& $status == 3)--}}
                                                {{--    <th>Rejected Time</th>--}}
                                            @endif
                                            <th>App Version</th>
                                            <th>Actions</th>
                                            {{--<th>Provider Name</th>--}}
                                            {{--<th>Email</th>--}}
                                            {{--<th>Contact No</th>--}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($store_details))
                                            @foreach($store_details as $key => $store_detail)
                                                <tr id="hide_{{$store_detail->store_id}}">
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        <span title="Provider Details" class="md-trigger"
                                                              data-modal="modal-1" data-toggle="tooltip"
                                                              provider_name="{{ $store_detail->provider_name }}"
                                                              email="{{ App\model\User::Email2Stars($store_detail->provider_email) }}"
                                                              contact_no="{{ App\model\User::ContactNumber2Stars($store_detail->contact_number) }}"
                                                              city="Georgia"
                                                              image="path">
                                                            <b>{{ $store_detail->store_name }}</b>
                                                        </span>
                                                    </td>
                                                    <td class="icon-url-link">
                                                        <a href="{{ route('get:admin:store_review_list', [$slug,$store_detail->store_id]) }}" class="render_link">
                                                            <span class="data-table-main icon-list-demo"><i class="fa fa-star" data-toggle="tooltip" data-placement="top" title="Rating List"></i>{{ $store_detail->rating }}</span>
                                                        </a>
                                                    </td>

                                                    <td class="url-link">
                                                        <a href="{{ route('get:admin:store_product_list', [$slug,$store_detail->store_id]) }}"
                                                           class="render_link" data-toggle="tooltip"
                                                           data-placement="top"
                                                           title="Product List">
                                                            {{--<img src="{{ asset('/assets/images/template-images/menu.png') }}">--}}
                                                            view
                                                            @if(isset($products_count))
                                                                @if(array_key_exists($store_detail->store_id,$products_count))
                                                                    ({{ $products_count[$store_detail->store_id] }})
                                                                @else
                                                                    (0)
                                                                @endif
                                                            @else
                                                                (0)
                                                            @endif
                                                        </a>
                                                    </td>
                                                    @if($general_settings->is_private_driver_module == 1)
                                                        <td class="icon-url-link">
                                                            <a href="{{ route('get:admin:store_driver_list', [$slug,$store_detail->store_id]) }}"
                                                               class="render_link">
                                                                <span class="data-table-main icon-list-demo">
                                                                     <i class="fa fa-users" title="Driver Lists"></i>
                                                                    @if(isset($store_driver_count))
                                                                        @if(array_key_exists($store_detail->store_id,$store_driver_count))
                                                                            ({{ $store_driver_count[$store_detail->store_id] }})
                                                                        @else
                                                                            (0)
                                                                        @endif
                                                                    @else
                                                                        (0)
                                                                    @endif
                                                                </span>
                                                            </a>
                                                        </td>
                                                    @endif
                                                    <td class="icon-url-link">
                                                        {{--<a href="{{ route('get:admin:store_wise_order_list',[$slug,str_replace(' ','-',strtolower(trim($store_detail->store_name))),$store_detail->store_id]) }}"--}}
                                                        <a href="{{ route('get:admin:store_wise_order_list',[$slug,$store_detail->store_id]) }}"
                                                           class="render_link">
                                                            <div class="data-table-main icon-list-demo">
                                                                <i class="fa fa-shopping-cart" data-toggle="tooltip"
                                                                   data-placement="top" title="Order List"></i>
                                                            </div>
                                                        </a>
                                                    </td>
                                                    <td class="icon-url-link">
                                                        <a href="{{ route('get:admin:store_provider_document', [$slug,$store_detail->store_id]) }}"
                                                           class="render_link">
                                                            <div class="data-table-main icon-list-demo">
                                                                <i class="fa fa-file-text" data-toggle="tooltip"
                                                                   data-placement="top" title="Document List"></i>
                                                            </div>
                                                        </a>
                                                    </td>
                                                    @if(isset($status) && in_array($status, [0,2,3]))
                                                        <td>{{ date("d-M Y h:i A",strtotime($store_detail->created_at) )}}</td>
                                                    @endif
                                                    <td>{{ ($store_detail->store_app_version !=Null)?$store_detail->store_app_version:0 }}</td>
                                                    <td class="action">
                                                        @if($status == 0)
                                                            <a class="render_link">
                                                                <img
                                                                    src="{{ asset('/assets/images/template-images/thumbs-up.png') }}"
                                                                    style="width:20px; height: 20px;"
                                                                    data-toggle="tooltip" class="approve"
                                                                    storeid="{{$store_detail->store_id}}"
                                                                    data-placement="top" title="Approve">
                                                            </a><a class="render_link">
                                                                <img
                                                                    src="{{ asset('/assets/images/template-images/thumb-down.png') }}"
                                                                    style="width:20px; height: 20px;"
                                                                    data-toggle="tooltip"
                                                                    storeid="{{$store_detail->store_id}}"
                                                                    data-placement="top" title="Reject" class="reject">
                                                            </a>
                                                        @elseif($status == 1)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status block"
                                                                   type="checkbox" checked
                                                                   storeid="{{$store_detail->store_id}}"><span
                                                                        class="button-indecator"
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="Active"></span></label>
                                                        </span>
                                                            <a href="{{ route('get:admin:edit_store',[$slug,$store_detail->store_id]) }}"
                                                                {{--class="render_link"--}}
                                                            >
                                                                <img
                                                                    src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                    style="width:20px; height: 20px;"
                                                                    data-toggle="tooltip"
                                                                    data-placement="top" title="Edit">
                                                            </a>

                                                            <span title="Change Password"
                                                                  class="md-trigger text-c-orenge"
                                                                  data-modal="modal-2" data-toggle="tooltip"
                                                                  provider_name="{{ $store_detail->provider_name }}"

                                                                  store_id="{{ $store_detail->store_id }}"
                                                            >
                                                            <i class="fa fa-key"></i>
                                                        </span>
                                                        @elseif($status == 2)
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status unblock"
                                                                   type="checkbox" checked
                                                                   storeid="{{$store_detail->store_id}}"><span
                                                                        class="button-indecator"
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="Active"></span></label>
                                                        </span>
                                                        @else
                                                            <span class="toggle"><label>
                                                            <input name="manual_assign"
                                                                   class="form-control store_status approve"
                                                                   type="checkbox" checked
                                                                   storeid="{{$store_detail->store_id}}"><span
                                                                        class="button-indecator"
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="Active"></span></label>
                                                        </span>
                                                        @endif

                                                        {{--<a class="delete">--}}
                                                        {{--<img src="{{ asset('/assets/images/template-images/remove-1.png') }}"--}}
                                                        {{--style="width:20px; height: 20px;" data-toggle="tooltip"--}}
                                                        {{--data-placement="top" title="Delete">--}}
                                                        {{--</a>--}}
                                                    </td>
                                                    {{--<td>Nixon</td>--}}
                                                    {{--<td>nixon@gmail.com</td>--}}
                                                    {{--<td>+1234-567-890</td>--}}
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


    <div class="md-modal md-effect-1" id="modal-1">
        <div class="md-content">
            <h3 class="bg-c-blue" id="driver_name">Provider Details</h3>
            <div class="wrapper">
                <p class="text-model"><strong>Provider Name : </strong> <span id="provider_name"></span></p>
                <p class="text-model"><strong>Email : </strong> <span id="email"></span></p>
                <p class="text-model"><strong>Contact No : </strong> <span id="contact_no"></span></p>
                <button type="button" class="btn btn-primary waves-effect md-close">Close</button>
            </div>
        </div>
    </div>

    <div class="md-modal md-effect-1" id="modal-2">
        <div class="md-content">

            <h3 class="bg-c-blue">Change Password</h3>
            <div class="wrapper">
                <div id="cover-spin"></div>
                <form method="get" id="change_password_form">
                    <p id="send_message" class="text-success"></p>
                    <input type="hidden" class="form-control" name="store_id" id="store_id" placeholder="store id" value="">
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
        $(document).ready(function () {
            var table = $('.table').DataTable({
                initComplete: function () {
                    $('div.dataTables_filter').wrap('<form>').parent().attr('autocomplete', 'off')
                }
            });
        });
    </script>

    {{--Model Script type detials--}}
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

                        $(".error").empty();
                        $("#send_message").empty();
                        $("#fail_message").empty();
                        $("#password").val("");
                        $("#confirm_password").val("");

                        var store_id = $(this).attr('store_id');
                        var provider_name = $(this).attr('provider_name');
                        var email = $(this).attr('email');
                        var contact_no = $(this).attr('contact_no');
                        var city = $(this).attr('city');
                        var image = $(this).attr('image');

                        // var url = $(this).attr('url');
                        $('#provider_name').text(provider_name);
                        $('#email').text(email);
                        $('#contact_no').text(contact_no);
                        // $('#city').text(city);
                        $('#image').text(image);
                        $('#store_id').val(store_id);

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

    <script rel="stylesheet" src="{{ asset('assets/js/validation/jquery.validate.js')}}"></script>
    <script type="text/javascript">
        $("#change_password_form").validate({
            rules: {
                password: {
                    required : true,
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
                    url: '{{ route('get:admin:store_change_password') }}',
                    data: form_data,
                    success: function (result) {
                        $("#cover-spin").css('display',"block");
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
                    }
                });
            }
        });

        $(document).on('click', '.block', function (e) {
            e.preventDefault();
            var id = $(this).attr('storeid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Block Store?",
                    text: "if press yes then store is block!",
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
                            url: '{{ route('get:admin:update_store_status') }}',
                            data: {id: id, request_for: 2, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "store block successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "store status not change", "error");
                    }
                });
        });
        $(document).on('click', '.unblock', function (e) {
            e.preventDefault();
            var id = $(this).attr('storeid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Unblock Store?",
                    text: "if press yes then store is unblock!",
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
                            url: '{{ route('get:admin:update_store_status') }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "store unblock successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "store status not change", "error");
                    }
                });
        });
        $(document).on('click', '.approve', function (e) {
            e.preventDefault();
            var id = $(this).attr('storeid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Approve Store?",
                    text: "if press yes then store is unblock!",
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
                            url: '{{ route('get:admin:update_store_status') }}',
                            data: {id: id, request_for: 1, service_cat_id: service_cat_id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#hide_" + id;
                                    swal("Success", "store approve successfully", "success");
                                    $(new_id).hide();
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "store status not change", "error");
                    }
                });
        });
        $(document).on('click', '.reject', function (e) {
            e.preventDefault();
            var id = $(this).attr('storeid');
            var service_cat_id = {{ isset($service_category) ? $service_category->id : 0 }}
            swal({
                    title: "Reject Store?",
                    text: "if press yes then store is unblock!",
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
                                title: "Reject Store!",
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
                                    swal.showInputError("You need to write reject order!");
                                    return false
                                }
                                $.ajax({
                                    type: 'get',
                                    url: '{{ route('get:admin:update_store_status') }}',
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
                                            swal("Success", "store reject successfully", "success");
                                            $(new_id).hide();
                                        }else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                    }
                                })


                            });
                    } else {
                        swal("Cancelled", "store status not change", "error");
                    }
                });
        });
    </script>
@endsection
