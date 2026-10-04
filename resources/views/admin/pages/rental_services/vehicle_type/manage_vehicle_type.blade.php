@extends('admin.layout.super_admin')
@section('title')
    Vehicle Type List - @if(isset($service_category))  {{ ucwords(strtolower($service_category->name)) }} @endif
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
        /*data table pagination style*/
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

        /* Status style*/
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
        {{--<div class="page-header card">--}}
        {{--<div class="row align-items-end">--}}
        {{--<div class="col-lg-12">--}}
        {{--@include('admin.include.other-service-horizontal-navbar')--}}
        {{--</div>--}}
        {{--</div>--}}
        {{--</div>--}}

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-green"></i>
                        <div class="d-inline">
                            <h5>Vehicle Type</h5>
                            <span>All Vehicle Type List
                                @if(isset($service_category) && $service_category->name != Null) of {{ ucwords(strtolower($service_category->name)) }} @endif
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
                                <h5>Vehicle Type List @if(isset($service_category) && $service_category->name != Null) of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>

                                <a href="{{ isset($service_category) ? route('get:admin:add_transport_rental_vehicle_type',['slug' => $service_category->slug]) : '' }}"
                                   class="btn btn-success m-b-0 btn-right render_link">Add Vehicle Type</a>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Vehicle Icon</th>
                                            <th>Vehicle Name</th>
                                            <th>Cost For Km</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($vehicle_type_lists))
                                            @foreach($vehicle_type_lists as $key => $vehicle_type)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="td-icon">
                                                        <img src="{{ asset('/assets/images/service-category/transport-service-type/'.$vehicle_type->icon_name)}}">
                                                    </td>
                                                    <td>{{ ucwords($vehicle_type->name) }}</td>
                                                    <td class="currency"> {{ $vehicle_type->cost_for_km }}</td>
                                                    <td>
                                                        <div class="toggle">
                                                            <label>
                                                                <input name="required_document"
                                                                       class="form-control vehicle_type"
                                                                       id="vehicle_type_id_{{$vehicle_type->id}}"
                                                                       vehicle_type_id="{{$vehicle_type->id}}"
                                                                       vehicle_type_status="{{$vehicle_type->status}}"
                                                                       type="checkbox" {{ ("1" == $vehicle_type->status) ? 'checked' : '' }}>
                                                                {{--<input name="vehicle_type_list"--}}
                                                                {{--class="form-control vehicle_type_status"--}}
                                                                {{--vehicle_type_id="{{ $vehicle_type_list->id }}"--}}
                                                                {{--type="checkbox" {{ $checked = ("1" == $vehicle_type_list->status) ? 'checked' : '' }} >--}}
                                                                <span class="button-indecator" data-toggle="tooltip" data-placement="top" title="{{ ("1" == $vehicle_type->status) ? 'Active' : 'InActive' }}"></span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td class="action">
                                                        <a href="{{ route('get:admin:edit_transport_rental_vehicle_type',['slug' => $slug, 'id' => $vehicle_type->id]) }}"
                                                           class="render_link edit">
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                 style="width:20px; height: 20px;" data-toggle="tooltip" data-placement="top" title="Edit">
                                                        </a>
                                                        <a class="delete" vehicleid="{{ $vehicle_type->id }}">
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
    <script type="text/javascript">
        var table = $('#new-cons').DataTable();
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var id = $(this).attr('vehicleid');
            var RemovetableRow = table.row($(this).parents('tr'));
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this vehicle type!",
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
                            url: '{{ route('get:admin:delete_transport_rental_vehicle_type') }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == 0) {
                                    // swal("Warning", "You can not delete this vehicle type", "success");
                                    swal("Warning", "You can't delete the vehicle type because this vehicle type is already allocated to driver.", "warning");
                                }
                                if (result.success == 11) {
                                    swal("Warning", "You can not delete this vehicle type", "warning");
                                    // swal("Warning", "You can't delete the vehicle type because this vehicle type is already allocated to driver.", "warning");
                                }
                                if (result.success == true) {
                                    RemovetableRow.remove().draw();
                                    swal("Success", "Vehicle Type Delete Successfully", "success");
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Your Data is safe :)", "error");
                    }
                });
        });
    </script>
    <script>
        $(document).on('click', '.vehicle_type', function (e) {
            e.preventDefault();
            var id = $(this).attr('vehicle_type_id');
            var status = $(this).attr('vehicle_type_status');
            var txt, title;
            if (status == 1) {
                title = "Disable vehicle type?";
                txt = "if press yes then disable vehicle type!";
            }
            else {
                title = "Enable vehicle type?";
                txt = "if press yes then enable vehicle type!";
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
                            url: '{{ route("get:ajax:admin:update_transport_rental_vehicle_type_status") }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
                                    var vehicle_type_id_ = '#vehicle_type_id_' + id;
                                    if (result.status == 1) {
                                        $(vehicle_type_id_).prop("checked", true);
                                        $(vehicle_type_id_).attr("vehicle_type_status", 1);
                                        swal("Success", "Enable Vehicle Type Successfully", "success");
                                    }
                                    else {
                                        $(vehicle_type_id_).prop("checked", false);
                                        $(vehicle_type_id_).attr("vehicle_type_status", 0);
                                        swal("Success", "Disable Vehicle Type Successfully", "success");
                                    }
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Vehicle type is Enable", "error");
                        }
                        else {
                            swal("Cancelled", "Vehicle type is Disable", "error");
                        }
                    }
                });
        });
    </script>
@endsection

