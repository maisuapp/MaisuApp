@extends('admin.layout.super_admin')
@section('title')
    @if(isset($store_details)) {{ $store_details->name }} @endif Product List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
    <style>
        .document a {
            color: #4099ff;
            font-weight: bold;
            /*font-size: 15px;*/
        }

        .product a {
            background: #4099ff;
            color: white;
            padding: 2px 5px;
            border-radius: 25px;
        }

        .icon-list-demo i {
            height: auto;
            line-height: 10px;
            border: none;
            margin-right: 5px;
            color: #4099ff;
        }

    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="external-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>
                                        @if(isset($store_details)) {{ $store_details->name }} @endif
                                        Store Products</h5>
                                    <span>All Store Product List</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            {{--<a href="{{ route('get:admin:store_provider_list') }}"--}}
                            {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
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
                                <h5>@if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif
                                    Store Product List</h5>
                                <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:add_store_product',[$slug,$store_details->id]) : route('get:store-admin:add_store_product') }}"
                                   class="btn btn-primary m-b-0 btn-right">Add Product</a>
                                @if(isset($store_details))
                                    <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:add_store_product_excel',[$slug,$store_details->id]) : route('get:store-admin:add_store_product_excel',[$store_details->id]) }}"
                                       class="btn btn-primary m-b-0 btn-right" style="margin-left: 20px; margin-right: 10px;">Excel</a>
                                @endif
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            {{--<th>Icon</th>--}}
                                            <th>Product Name</th>
                                            <th>Category Name</th>
                                            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                <th>Recommended </th>
                                            @endif
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($store_products))
                                            @foreach($store_products as $key => $store_product)
                                                <tr id="delete_product_{{$store_product->id}}">
                                                    <td>{{ $key+1 }}</td>
                                                    <td>{{ $store_product->name }}</td>
                                                    <td>{{ $store_product->category_name }}</td>
                                                    @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                                        <td>
                                                            <span class="toggle">
                                                                <label>
                                                                    <input name="manual_assign" class="form-control product_recommended"
                                                                           type="checkbox"
                                                                           id="product_{{$store_product->id}}"
                                                                           product_id="{{$store_product->id}}"
                                                                           product_recommended="{{$store_product->is_recommended}}"
                                                                            {{ $store_product->is_recommended == 1 ? 'checked' : '' }}>
                                                                    <span class="button-indecator" data-toggle="tooltip"
                                                                          data-placement="top" title="Active"></span>
                                                                </label>
                                                            </span>
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <span class="toggle">
                                                            <label>
                                                                <input name="manual_assign" class="form-control product_status"
                                                                       type="checkbox"
                                                                       id="product_status_{{$store_product->id}}"
                                                                       product_id="{{$store_product->id}}"
                                                                       product_status="{{$store_product->status}}"
                                                                        {{ $store_product->status == 1 ? 'checked' : '' }}>
                                                                <span class="button-indecator" data-toggle="tooltip"
                                                                      data-placement="top" title="Active"></span>
                                                            </label>
                                                        </span>
                                                    </td>
                                                    <td class="action">
                                                        <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:edit_store_product',[$slug,$store_product->id]) : route('get:store-admin:edit_store_product',[$store_product->id]) }}"
                                                           class="render_link">
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}"
                                                                 style="width:20px; height: 20px;"
                                                                 data-toggle="tooltip"
                                                                 data-placement="top" title="Edit">
                                                        </a>
                                                        <a class="delete" productid="{{$store_product->id}}">
                                                            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}"
                                                                 style="width:20px; height: 20px;"
                                                                 data-toggle="tooltip"
                                                                 data-placement="top" title="Delete">
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
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var id = $(this).attr('productid');
            var url;
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                url = '{{ route('get:admin:delete_store_product') }}';
            @else
                url = '{{ route('get:store-admin:delete_store_product') }}';
            @endif
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this Data!",
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
                            url: url,
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#delete_product_" + id;
                                    swal("Success", "product remove successfully", "success");
                                    $(new_id).hide();
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
        $(document).on('click', '.product_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('product_id');
            var status = $(this).attr('product_status');
            var txt, title, url;
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                url = '{{ route('get:admin:store_product_change_status') }}';
            @else
                url = '{{ route('get:store-admin:store_product_change_status') }}';
            @endif
            if (status == 1) {
                title = "Disable Product?";
                txt = "if press yes then disable this product and not display in user panel!";
            } else {
                title = "Enable Product?";
                txt = "if press yes then enable this product and display in user panel!";
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
                            url: url,
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = '#product_status_' + id;

                                    if (result.status == 1) {
                                        $(new_id).prop("checked", true);
                                        $(new_id).attr("product_status", 1);
                                        swal("Success", "Enable Product Successfully", "success");
                                    } else {
                                        $(new_id).prop("checked", false);
                                        $(new_id).attr("product_status", 0);
                                        swal("Success", "Disable Product Successfully", "success");
                                    }
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Product is Enable", "error");
                        } else {
                            swal("Cancelled", "Product is Disable", "error");
                        }
                    }
                });
        });

        $(document).on('click', '.product_recommended', function (e) {
            e.preventDefault();
            var id = $(this).attr('product_id');
            var status = $(this).attr('product_recommended');
            var txt, title, url;
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                url = '{{ route('get:admin:store_product_change_recommended_status') }}';
            @else
                url = '{{ route('get:store-admin:store_product_change_recommended_status') }}';
            @endif
            if (status == 1) {
                title = "Remove Product as Recommended?";
                txt = "if press yes then  product Remove as Recommended Product!";
            } else {
                title = "Add Product  as Recommended?";
                txt = "if press yes then product add as Recommended Product!";
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
                            url: url,
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = '#product_' + id;
                                    if (result.status == 1) {
                                        $(new_id).prop("checked", true);
                                        $(new_id).attr("product_recommended", 1);
                                        swal("Success", "Product Added as Recommended Successfully", "success");
                                    } else {
                                        $(new_id).prop("checked", false);
                                        $(new_id).attr("product_recommended", 0);
                                        swal("Success", "Product Removed as Recommended Successfully", "success");
                                    }
                                }else {
                                    swal("Warning", result.message, "warning");
                                    console.log(result);
                                }
                            }
                        })
                    } else {
                        if (status == 1) {
                            swal("Cancelled", "Product Added as Recommended Successfully", "error");
                        } else {
                            swal("Cancelled", "Product Removed as Recommended Successfully", "error");
                        }
                    }
                });
        });
    </script>
@endsection
