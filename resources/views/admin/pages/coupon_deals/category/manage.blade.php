@extends('admin.layout.super_admin')
@section('title')
    Category List
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
                                    <h5>Category List</h5>
                                    <span>All Category List @if(isset($service_category) && $service_category->name != Null)
                                            of {{ ucwords(strtolower($service_category->name)) }} @endif
                                    </span>
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
                                <h5>Category List @if(isset($service_category) && $service_category->name != Null)
                                        of {{ ucwords(strtolower($service_category->name)) }} @endif</h5>
                                <a href="{{ route('get:admin:coupon_deals_add_category',$slug) }}"
                                   {{--render_link--}}
                                   class="btn btn-primary m-b-0 btn-right ">Add New Category</a>

                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>Category Name</th>
                                            <th style="width: 50px;">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($categories))
                                            @foreach($categories as $key => $category)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $category->category_name }}</td>
                                                    <td class="action">
                                                        <span class="toggle">
                                                        <label>
                                                            <input name="status" class="form-control category_status"
                                                                   id="category_id_{{$category->id}}"
                                                                   category_id="{{$category->id}}"
                                                                   category_status="{{$category->status}}"
                                                                   type="checkbox" {{ ("1" == $category->status) ? 'checked' : '' }}>
                                                            <span class="button-indecator" data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  id="title_status_{{$category->id}}"
                                                                  title="{{ ("1" == $category->status) ? 'Active' : 'InActive' }}"></span>
                                                        </label>
                                                        </span>
                                                        <a
                                                                href="{{ route('get:admin:coupon_deals_edit_category',[$slug,$category->id]) }}"
                                                                {{--class="render_link"--}}
                                                        >
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                 style="width:20px; height: 20px;"
                                                                 data-toggle="tooltip"
                                                                 data-placement="top" title="Edit">
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
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}"></script>

    {{--User Delete Script--}}
    <script type="text/javascript">
        $(document).on('click', '.category_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('category_id');
            var status = $(this).attr('category_status');
            var txt, title;
            if (status == 1) {
                title = "Disable Category?";
                txt = "if press yes then disable category!";
            } else {
                title = "Enable Category?";
                txt = "if press yes then enable category!";
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
                            url: '{{ route("get:admin:coupon_deals_category_change_status") }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
                                    var category_id_ = '#category_id_' + id;
                                    if (result.status == 1) {
                                        $(category_id_).prop("checked", true);
                                        $(category_id_).attr("category_status", 1);
                                        swal("Success", "Enable category Successfully", "success");
                                    } else {
                                        $(category_id_).prop("checked", false);
                                        $(category_id_).attr("category_status", 0);
                                        swal("Success", "Disable category Successfully", "success");
                                    }
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
    </script>
@endsection

