@extends('admin.layout.super_admin')
@section('title')
    Drivers Review List
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
        }

        .icon-list-demo i {
            height: auto;
            line-height: 10px;
            border: none;
            margin-right: 5px;
            color: #55d090;
        }
        .icon-list-demo i:hover {
            color: #9bd8b3;
        }
        .action a{
            margin: 0;
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
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ other service horizontal navbar ] start -->
        <div class="other-service-horizontal-nav">
            @include('admin.include.transport-rental-horizontal-navbar')
        </div>
        <!-- [ other service horizontal navbar ] end -->
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5>@if(!isset($driver_details)) Drivers @endif Review List
                                @if(isset($driver_details) && $driver_details != Null)
                                    of {{ ucwords(strtolower($driver_details->driver_name)) }}
                                @endif
                                </h5>
                            <span>All Review List @if(isset($service_category) && $service_category->name != Null) of {{ ucwords(strtolower($service_category->name)) }} @endif
                                Drivers </span>
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
                                <h5>@if(!isset($driver_details)) Drivers @endif Review List @if(isset($service_category) && $service_category->name != Null) of {{ ucwords(strtolower($service_category->name)) }} @endif
                                    @if(isset($driver_details) && $driver_details != Null)
                                        - {{ ucwords(strtolower($driver_details->driver_name)) }}
                                    @endif
                                </h5>

                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Ride No.</th>
                                            <th>Customer Name</th>
                                            <th>Driver Name</th>
                                            <th>Date & Time</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($provider_reviews))
                                            @foreach($provider_reviews as $key => $provider_review)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $provider_review->ride_no }}</td>
                                                    <td>{{ ucwords(strtolower($provider_review->first_name ." ". $provider_review->last_name)) }}</td>
                                                    <td>{{ ucwords(strtolower($provider_review->name)) }}</td>
                                                    <td>{{ $provider_review->pickup_datetime }}</td>
                                                    <td class="ratings">
                                                    <span class="icon-list-demo">
                                                        <i class="fa fa-star"></i>{{ $provider_review->rating }}
                                                    </span>
                                                    </td>
                                                    <td>
                                                        {{ ($provider_review->comment != Null)? $provider_review->comment : "----" }}
                                                    </td>
                                                    <td class="action">
                                                        {{--<div class="toggle">--}}
                                                            {{--<label>--}}
                                                                {{--<input name="manual_assign" class="form-control"--}}
                                                                       {{--type="checkbox" {{ ($provider_review->status == 1)? 'checked' : '' }}>--}}
                                                                {{--<span class="button-indecator"></span>--}}
                                                            {{--</label>--}}
                                                        {{--</div>--}}

                                                        <div class="toggle">
                                                            <label>
                                                                <input name="review"
                                                                       class="form-control review"
                                                                       id="review_id_{{$provider_review->id}}"
                                                                       review_id="{{$provider_review->id}}"
                                                                       review_status="{{$provider_review->status}}"
                                                                       type="checkbox" {{ ("1" == $provider_review->status) ? 'checked' : '' }}>
                                                                <span class="button-indecator" data-toggle="tooltip" data-placement="top" title="{{ ("1" == $provider_review->status) ? 'on' : 'off' }}"></span>
                                                            </label>
                                                        </div>
                                                        <a class="delete" reviewid="{{ $provider_review->id }}">
                                                            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}"
                                                                 style="width:20px; height: 20px;" data-toggle="tooltip" data-placement="top" title="Delete">
                                                        </a>
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
        $(document).on('click', '.review', function (e) {
            e.preventDefault();
            var id = $(this).attr('review_id');
            var status = $(this).attr('review_status');
            var txt, title;
            if (status == 1) {
                title = "Disable review?";
                txt = "if press yes then disable review!";
            }
            else {
                title = "Enable review?";
                txt = "if press yes then enable review!";
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
                            url: '{{ route("get:ajax:admin:update_transport_rental_provider_ride_review_status") }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
                                    var review_id = '#review_id_' + id;
                                    if (result.status == 1) {
                                        $(review_id).prop("checked", true);
                                        $(review_id).attr("review_status", 1);
                                        swal("Success", "Enable Review Successfully", "success");
                                    }
                                    else {
                                        $(review_id).prop("checked", false);
                                        $(review_id).attr("review_status", 0);
                                        swal("Success", "Disable Review Successfully", "success");
                                    }
                                }else {
                                    swal("Warning", result.message, "warning");
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Review is Enable", "error");
                        }
                        else {
                            swal("Cancelled", "Review is Disable", "error");
                        }
                    }
                });
        });
    </script>

    <script type="text/javascript">
        var table = $('#new-cons').DataTable();
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var id = $(this).attr('reviewid');
            var RemovetableRow = table.row($(this).parents('tr'));
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this review!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: 'get',
                            url: '{{ route('get:admin:delete_transport_rental_provider_ride_review') }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
                                    RemovetableRow.remove().draw();
                                    swal("Success", "review Delete Successfully", "success");
                                }else {
                                    swal("Warning", result.message, "warning");
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Your Data is safe :)", "error");
                    }
                });
        });
                                </script>
@endsection

