@extends('admin.layout.super_admin')
@section('title')
    Coupon Code List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
        .action a {
            /*margin: 0;*/
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ other service horizontal navbar ] start -->
        <div class="other-service-horizontal-nav">
        </div>
        <div class="external-horizontal-nav">
            @include('admin.include.coupon-deals-horizontal-navbar')
        </div>
        <!-- [ other service horizontal navbar ] end -->
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>Coupon Code List</h5>
                                </div>
                            </div>
                        </div>
                        {{--<div class="col-lg-4">--}}
                        {{--<a href="{{ route('get:admin:user_list') }}"--}}
                        {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
                        {{--</div>--}}
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
                                <h5>Coupon Code List</h5>
                                <a href="{{--{{ route('get:admin:add_user') }}--}}"
                                   class="btn btn-primary m-b-0 btn-right render_link addCouponCode" couponid="{{$coupon_id}}">
                                    Add Coupon Code </a>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>User Name</th>
                                            <th>Coupon Code</th>
                                            <th>Purchase Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {{--@if(isset($coupon_code_list))
                                            @foreach($coupon_code_list as $key => $coupon_code)
                                                <tr id="delete_coupon_code_{{$coupon_code['id']}}">
                                                    <td>{{ $key + 1 }}</td>
                                                    @if($coupon_code['user_id'] > 0)
                                                    <td>{{ $coupon_code['first_name'] . " " . $coupon_code['last_name'] }}</td>
                                                    @else
                                                    <td>{{ "--" }}</td>
                                                    @endif

                                                    <td>{{ $coupon_code['coupon_code'] }}</td>
                                                    <td>{{ ($coupon_code['code_active_date'] > 0)? $coupon_code['code_active_date']:"--"}}</td>
                                                    <td>{{ $coupon_code['coupon_status']}}</td>
                                                    <td>
                                                        <a class="delete" couponcodeid="{{ ($coupon_code['user_id'] > 0)?0:$coupon_code['id'] }}">
                                                            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}"
                                                                 style="width:20px; height: 20px;" data-toggle="tooltip"
                                                                 data-placement="top" title="Delete"  >
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

@endsection
@section('page-js')
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}"></script>
<!--    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}"></script>-->

    {{--User Delete Script--}}
    <script type="text/javascript">
        $(document).ready(function () {
            var table = $('#new-cons').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: "{{route('get:admin:coupon_code_list_new',[$slug,$coupon_id])}}",
                columns: [
                    {data: 'id' },
                    {data: 'first_name'},
                    {data: 'coupon_code'},
                    {data: 'code_active_date'},
                    {data: 'coupon_status'},
                    {data: 'action', 'sortable': false},
                ],
                "order": [[ 0, "desc" ]]

            });

            $(document).on('click', '.delete', function (e) {
                e.preventDefault();
                var id = $(this).attr('couponcodeid');
                if(id > 0){
                    // var RemovetableRow = table.row($(this).parents('tr'));
                    swal({
                            title: "Are you sure?",
                            text: "You will not be able to recover this data!",
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
                                    url: '{{ route('get:admin:delete_coupon_code') }}',
                                    data: {id: id},
                                    success: function (result) {
                                        if (result.success == true) {
                                            var new_id = "#delete_coupon_code_" + id;
                                            swal("Success", "coupon code remove successfully", "success");
                                            $(new_id).hide();
                                        }else {
                                            swal("Warning", result.message, "warning");
                                            console.log(result);
                                        }
                                        // else{
                                        //     swal("Cancelled", "something went to wrong", "error");
                                        // }
                                    }
                                })
                            } else {
                                swal("Cancelled", "Your Data is safe :)", "error");
                            }
                        });

                }else{
                    swal("Cancelled", "Sorry can not delete coupon code. already assing to user :)", "error");
                }

            });

            $(document).on('click', '.addCouponCode', function (e) {
                e.preventDefault();
                var coupon_id = $(this).attr('couponid');
                var txt, title, status, url;


                url = '{{ route("get:admin:add_coupon_code") }}';

                title = "Are you sure to add coupon code?";
                txt = "if press yes then add coupon code!";
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
                            swal({
                                    title: "Add Coupon Code!",
                                    text: "",
                                    type: "input",
                                    showCancelButton: true,
                                    closeOnConfirm: false,
                                    animation: "slide-from-top",
                                    inputPlaceholder: "No Of Coupon"
                                },
                                function (inputValue) {
                                    if (inputValue === false) return false;
                                    if (inputValue === "") {
                                        swal.showInputError("You need to enter no of coupon code "  );
                                        return false
                                    }
                                    $.ajax({
                                        type: 'get',
                                        url: url,
                                        data: {coupon_id: coupon_id,  total_coupon: inputValue},
                                        success: function (result) {
                                            console.log(result);
                                            if (result.success == true) {
                                                location.reload();
                                            }else {
                                                swal("Warning", result.message, "warning");
                                                console.log(result);
                                            }
                                            // else {
                                            //     swal("Cancelled", result.message, "error");
                                            // }
                                        }
                                    })
                                });
                        } else {
                            swal("Cancelled", "Cancel to add coupon code", "error");
                        }
                    });
            });
        });
    </script>
@endsection

