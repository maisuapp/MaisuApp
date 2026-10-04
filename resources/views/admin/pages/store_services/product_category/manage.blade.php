@extends('admin.layout.super_admin')
@section('title')
    Store Product Category
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
@endsection
@section('page-content')

    <div class="pcoded-content">
        <div class="external-horizontal-nav">
            @include('admin.include.store-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-list bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Store Product Category</h5>
                            <span>All Store Product Category List</span>
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
                                <h5>Store Product Category List</h5>
                                <a href="{{ route('get:admin:add_store_category',$slug) }}" class="btn btn-primary m-b-0 btn-right render_link">Add Category</a>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Category Name</th>
                                            {{--<th>Category ID</th>--}}
                                            @if($is_sub_cat_flow == 1)
                                            <th>Sub Category</th>
                                            @else
                                                <th>CategoryID</th>
                                            @endif
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($store_categories))
                                            @foreach($store_categories as $key => $store_category)
                                                <tr id="delete_cat_{{$store_category->id}}">
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $store_category->name }}</td>
                                                    {{--<td>{{ $store_category->id }}</td>--}}
                                                    @if($is_sub_cat_flow == 1)
                                                        <td class="url-link">
                                                            <a href="{{ route('get:admin:store_sub_category_list', [ 'slug' => $slug, 'category_id' => $store_category->id ]) }}" class="render_link" title="Product List">
                                                                {{--<img src="{{ asset('/assets/images/template-images/menu.png') }}">--}}
                                                                view
                                                                @if(isset($category_count))
                                                                    @if(array_key_exists($store_category->id,$category_count))
                                                                        ({{ $category_count[$store_category->id] }})
                                                                    @else
                                                                        (0)
                                                                    @endif
                                                                @else
                                                                    (0)
                                                                @endif
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td class="url-link">

                                                                @if(isset($category_count))
                                                                    @if(array_key_exists($store_category->id,$category_count))
                                                                        {{ $category_count[$store_category->id] }}
                                                                    @else
                                                                        (0)
                                                                    @endif
                                                                @else
                                                                    (0)
                                                                @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                <span class="toggle">
                                                    <label>
                                                        <input name="manual_assign"
                                                               class="form-control sub_cat_status"
                                                               id="sub_cat_{{$store_category->id}}"
                                                               sub_cat_id="{{$store_category->id}}"
                                                               sub_cat_status="{{$store_category->status}}"
                                                               type="checkbox" {{ ("1" == $store_category->status) ? 'checked' : '' }}>
                                                        <span class="button-indecator" title="Active"></span>
                                                    </label>
                                                </span></td>
                                                    <td class="action">
                                                        <a href="{{ route('get:admin:edit_store_category',[$slug,$store_category->slug]) }}"
                                                           class="render_link">
                                                            <img src="{{ asset('/assets/images/template-images/writing-1.png') }}" style="width:20px; height: 20px;" title="Edit">
                                                        </a>
                                                        <a class="delete" categoryid="{{$store_category->id}}">
                                                            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}" style="width:20px; height: 20px;" title="Delete">
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
    <script type="text/javascript">
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var id = $(this).attr('categoryid');
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
                            url: '{{ route('get:admin:delete_store_category') }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = "#delete_cat_" + id;
                                    swal("Success", "category remove successfully", "success");
                                    $(new_id).hide();
                                }
                                else {
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
        $(document).on('click', '.sub_cat_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('sub_cat_id');
            var status = $(this).attr('sub_cat_status');
            var txt, title;
            if (status == 1) {
                title = "Disable Category?";
                txt = "if press yes then disable this category and not display in user panel!";
            }
            else {
                title = "Enable Category?";
                txt = "if press yes then enable this category and display in user panel!";
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
                            url: '{{ route('get:admin:store_category_change_status') }}',
                            data: {id: id},
                            success: function (result) {
                                if (result.success == true) {
//                                    location.reload();
                                    var new_id = '#sub_cat_' + id;
                                    if (result.status == 1) {
                                        $(new_id).prop("checked", true);
                                        $(new_id).attr("sub_cat_status", 1);
                                        swal("Success", "Enable Product Category Successfully", "success");
                                    }
                                    else {
                                        $(new_id).prop("checked", false);
                                        $(new_id).attr("sub_cat_status", 0);
                                        swal("Success", "Disable Product Category Successfully", "success");
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
                        }
                        else {
                            swal("Cancelled", "Category is Disable", "error");
                        }
                    }
                });
        });
    </script>
@endsection

