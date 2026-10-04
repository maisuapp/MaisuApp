@extends('admin.layout.super_admin')
@section('title')
    Coupon List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style></style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="other-service-horizontal-nav">
            @include('admin.include.coupon-deals-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>Coupon List</h5>
                                    <span>All Coupon List</span>
                                </div>
                            </div>
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
                                <h5>Coupon List</h5>
                                <a href="{{ route('get:admin:coupon_deals:add_coupon',["slug"=> $slug]) }}" class="btn btn-primary m-b-0 btn-right ">Add New Coupon</a>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>Image</th>
                                            <th>Coupon Name</th>
                                            <th>Brand Name</th>
                                            <th>Amount</th>
                                            <th>Total Coupon</th>
                                            <th>Total Available Coupon</th>
                                            <th>Coupon Validity (in days)</th>
                                            <th style="width: 50px;">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($coupon_list))
                                            @foreach($coupon_list as $key => $coupon)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="td-icon"><img id="icon_{{ $coupon->id }}" src="{{ asset('/assets/images/coupon_images/'.$coupon->coupon_image )}}" width="26" height="26"></td>
                                                    <td><a style="color:blue;" href="{{ route('get:admin:coupon_code_list',[$slug,$coupon->id]) }}"> {{ $coupon->coupon_name }}</a></td>
                                                    <td>{{ $coupon->coupon_brand_name }}</td>
                                                    <td class=""><span class="currency"></span>{{ App\model\GeneralSettings::currency_fomate($coupon->coupon_amount) }} </td>
                                                    <td class="">{{ $coupon->total_coupon }}</td>
                                                    <td class="">{{ $coupon->available_coupon }}</td>
                                                    <td class="">{{ $coupon->coupon_validity }}</td>
                                                    <td class="action">
                                                        <span class="toggle">
                                                            <label>
                                                                <input name="status" class="form-control coupon_status coupon_id_{{$coupon->id}}" id="coupon_id_{{$coupon->id}}" coupon_id="{{$coupon->id}}" coupon_status="{{$coupon->status}}" type="checkbox" {{ ("1" == $coupon->status) ? 'checked' : '' }}>
                                                                <span class="button-indecator" data-toggle="tooltip" data-placement="top" id="title_status_{{$coupon->id}}" title="{{ ("1" == $coupon->status) ? 'Active' : 'InActive' }}"></span>
                                                            </label>
                                                        </span>
                                                        <a href="{{ route('get:admin:coupon_deals:edit_coupon',[$slug,$coupon->id]) }}" class="render_link">
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}" style="width:20px; height: 20px;" data-toggle="tooltip" data-placement="top" title="Edit">
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
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}"></script>

    <script type="text/javascript">
        $(document).on('click', '.coupon_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('coupon_id');
            var status = $(this).attr('coupon_status');
            var txt, title;
            if (status == 1) {
                title = "Disable Coupon?";
                txt = "if press yes then disable coupon!";
            } else {
                title = "Enable Coupon?";
                txt = "if press yes then enable coupon!";
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
                            url: '{{ route("get:admin:coupon_deals:update_coupon_status") }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
                                    var coupon_id = '.coupon_id_' + id;
                                    if (result.status == 1) {
                                        $(coupon_id).prop("checked", true);
                                        $(coupon_id).attr("coupon_status", 1);
                                        swal("Success", "Enable coupon Successfully", "success");
                                    } else {
                                        $(coupon_id).prop("checked", false);
                                        $(coupon_id).attr("coupon_status", 0);
                                        swal("Success", "Disable coupon Successfully", "success");
                                    }
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Coupon is Enable", "error");
                        } else {
                            swal("Cancelled", "Coupon is Disable", "error");
                        }
                    }
                });
        });
    </script>
@endsection
