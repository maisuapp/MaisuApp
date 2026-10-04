@extends('admin.layout.super_admin')
@section('title')
    Shopper Provider List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
        .approve, .reject {
            cursor: pointer;
        }

        @if(isset($status) && $status==2 || isset($status) && $status==3)
            .toggle input[type="checkbox"]:checked + .button-indecator:before {
            color: red;
        }
        @endif
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <div class="other-service-horizontal-nav">
            @include('admin.include.shopper-horizontal-navbar')
        </div>

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Shopper Provider</h5>
                            <span>Shopper Provider List</span>
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
                                <h5>Shopper Provider List</h5>
                                {{--<a href="{{ route('get:admin:service_list') }}"--}}
                                {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Shopper Name</th>
                                            <th>Email</th>
                                            <th>Contact No</th>
                                            <th>Documents</th>
                                            <th>Services</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($shopper_list))
                                            @foreach($shopper_list as $key => $shopper_detail)
                                                <tr id="hide_{{$shopper_detail['id']}}">
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $shopper_detail['name'] }}</td>
                                                    <td>{{ $shopper_detail['email'] }}</td>
                                                    <td>{{ $shopper_detail['contact_number'] }}</td>
                                                    <td class="icon-url-link">
                                                        <a href="{{ route('get:admin:shopper_provider_document', [$shopper_detail['id']]) }}"
                                                           class="render_link">
                                                            <div class="data-table-main icon-list-demo">
                                                                <i class="fa fa-file-text" data-toggle="tooltip"
                                                                   data-placement="top" title="Document List"></i>
                                                            </div>
                                                        </a>
                                                    </td>
                                                    <td class="url-link">
                                                        @if(isset($shopper_services))
                                                            @if(array_key_exists($shopper_detail['id'],$shopper_services))
                                                                {{ implode(', ',$shopper_services[$shopper_detail['id']]) }}
                                                            @else
                                                                ----
                                                            @endif
                                                        @else
                                                            ----
                                                        @endif
                                                    </td>

                                                    <td class="action">
                                                        @if($status == 0)
                                                            <a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumbs-up.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     data-toggle="tooltip" class="approve"
                                                                     shopperid="{{$shopper_detail['id']}}"
                                                                     data-placement="top" title="Approve">
                                                            </a>
                                                            <a class="render_link">
                                                                <img src="{{ asset('/assets/images/template-images/thumb-down.png') }}"
                                                                     style="width:20px; height: 20px;"
                                                                     data-toggle="tooltip"
                                                                     shopperid="{{$shopper_detail['id']}}"
                                                                     data-placement="top" title="Reject" class="reject">
                                                            </a>
                                                        @elseif($status == 1)
                                                            <span class="toggle">
                                                                <label>
                                                                    <input name="manual_assign"
                                                                           class="form-control shopper_status block"
                                                                           type="checkbox"
                                                                           checked
                                                                           shopperid="{{$shopper_detail['id']}}">
                                                                    <span class="button-indecator"
                                                                          data-toggle="tooltip" data-placement="top"
                                                                          title="Active">
                                                                    </span>
                                                                </label>
                                                            </span>
                                                            <a href="{{ route('get:admin:edit_shopper',[$shopper_detail['id']]) }}"
                                                               class="render_link"
                                                            >
                                                                <img
                                                                        src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                        style="width:20px; height: 20px;"
                                                                        data-toggle="tooltip"
                                                                        data-placement="top" title="Edit">
                                                            </a>

                                                        @elseif($status == 2)
                                                            <span class="toggle">
                                                                <label>
                                                                    <input name="manual_assign"
                                                                           class="form-control shopper_status unblock"
                                                                           type="checkbox" checked
                                                                           shopperid="{{$shopper_detail['id']}}">
                                                                    <span class="button-indecator"
                                                                          data-toggle="tooltip" data-placement="top"
                                                                          title="Active">
                                                                    </span>
                                                                </label>
                                                            </span>
                                                        @else
                                                            <span class="toggle">
                                                                <label>
                                                                    <input name="manual_assign"
                                                                           class="form-control shopper_status approve"
                                                                           type="checkbox" checked
                                                                           storeid="{{$shopper_detail['id']}}">
                                                                    <span class="button-indecator"
                                                                          data-toggle="tooltip" data-placement="top"
                                                                          title="Active">
                                                                    </span>
                                                                </label>
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

@endsection
@section('page-js')
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            var table = $('.table').DataTable({
                initComplete: function () {
                    $('div.dataTables_filter').wrap('<form>').parent().attr('autocomplete', 'off')
                }
            });
        });
    </script>

    <script type="text/javascript">
        $(document).on('click', '.block', function (e) {
            e.preventDefault();
            var id = $(this).attr('shopperid');
            swal({
                    title: "Block Shopper?",
                    text: "if press yes then shopper is block!",
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
                            url: '{{ route('get:admin:update_shopper_status') }}',
                            data: {id: id, request_for: 2},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "Shopper block successfully", "success");
                                    $(new_id).hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Shopper status not change", "error");
                    }
                });
        });
        $(document).on('click', '.unblock', function (e) {
            e.preventDefault();
            var id = $(this).attr('shopperid');
            swal({
                    title: "Unblock Shopper?",
                    text: "if press yes then shopper is unblock!",
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
                            url: '{{ route('get:admin:update_shopper_status') }}',
                            data: {id: id, request_for: 1},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "Shopper unblock successfully", "success");
                                    $(new_id).hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Shopper status not change", "error");
                    }
                });
        });
        $(document).on('click', '.approve', function (e) {
            e.preventDefault();
            var id = $(this).attr('shopperid');
            swal({
                    title: "Approve Shopper?",
                    text: "if press yes then shopper is unblock!",
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
                            url: '{{ route('get:admin:update_shopper_status') }}',
                            data: {id: id, request_for: 1},
                            success: function (result) {
                                if (result.success == true) {
                                    var new_id = "#hide_" + id;
                                    swal("Success", "Shopper approve successfully", "success");
                                    $(new_id).hide();
                                } else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Shopper status not change", "error");
                    }
                });
        });
        $(document).on('click', '.reject', function (e) {
            e.preventDefault();
            var id = $(this).attr('shopperid');
            swal({
                    title: "Reject Shopper?",
                    text: "if press yes then shopper is reject!",
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
                                title: "Reject Shopper!",
                                text: "if reject shopper then Write reject reason:",
                                type: "input",
                                showCancelButton: true,
                                closeOnConfirm: false,
                                animation: "slide-from-top",
                                inputPlaceholder: "Reject Reason"
                            },
                            function (inputValue) {
                                if (inputValue === false) return false;
                                if (inputValue === "") {
                                    swal.showInputError("You need to write reject shopper!");
                                    return false
                                }
                                $.ajax({
                                    type: 'get',
                                    url: '{{ route('get:admin:update_shopper_status') }}',
                                    data: {
                                        id: id,
                                        request_for: 3,
                                        reject_reason: inputValue
                                    },
                                    success: function (result) {
                                        if (result.success == true) {
                                            var new_id = "#hide_" + id;
                                            swal("Success", "Shopper reject successfully", "success");
                                            $(new_id).hide();
                                        } else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                    }
                                })

                            });
                    } else {
                        swal("Cancelled", "Shopper status not change", "error");
                    }
                });
        });
    </script>
@endsection
