@extends('admin.layout.super_admin')
@section('title')
    Category List
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
    {{--@if(Illuminate\Support\Facades\Auth::guard("admin")->check())--}}
    <div class="external-horizontal-nav">
        @include('admin.include.transport-horizontal-navbar')
    </div>
    {{--@endif--}}
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
                                    <span>All Category List List</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <button type="button" id="add_rule" count="0" class="btn btn-primary m-b-0 btn-right render_link">Add Rule</button>
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
                        <form id="main" method="post"
                              action="{{ route('post:admin:update_transport_category_list',$slug)}}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}

                            <div class="form-group row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Category List</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="dt-responsive table-responsive">
                                                <table id="new-cons"
                                                       class="table table-striped table-bordered nowrap" style="width:100%">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 5%;">No</th>
                                                        <th>Category Name</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="table_body">
                                                        @if(isset($transport_category_list) && (count($transport_category_list) >= 1))
                                                            @foreach($transport_category_list as $key => $category_list)
                                                                <tr id="delete_product_{{$category_list->id}}">
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td>
                                                                        <input type="text" length="{{ $key + 1 }}" step="0.01" min="0" class="form-control category_name" name="category_name[]" placeholder="category name" value="{{$category_list->category_name}}">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr id="delete_product_1">
                                                                <td>1</td>
                                                                <td>
                                                                    <input type="text" length="1" step="0.01" min="0" class="form-control category_name" name="category_name[]" placeholder="category name" value="">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <button type="submit" class="btn btn-primary m-b-0 pull-right">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

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


            $("#add_rule").on("click",function (e){
                // $("#table_body tr:last").remove();
                var length = $('#table_body tr').length;
                var length1 = parseInt(length) + parseInt(1);

                var new_row = '<tr id="delete_product_' + length1 + '">' +
                    ' <td>' + length1 + '</td> ' +
                    '<td>' +
                    '       <input type="text" length="'+length1+'" step="0.01" min="0" span_type="0" class="form-control category_name" name="category_name[]" placeholder="category name" value="">' +
                    '</td>' +
                    '</tr>';

                $("#table_body").append(new_row);
            });

            // $(document).on("keyup",".close_limit",function (e){
            //     var length = $(this).attr('length');
            //     console.log(length);
            //
            //     var before_length = parseInt(length) - 1;
            //     console.log("before_length " + before_length);
            //     var before_close_val = $(".close_limit_"+ before_length ).val();
            //     console.log("before_close_val " + before_close_val);
            //     if (before_close_val > 0 || length == 1) {
            //
            //         if (length == 1) {
            //             var before_close_val = 0;
            //         }else {
            //             var before_close_val = parseFloat(before_close_val) + parseFloat(0.01) ;
            //         }
            //
            //         $(".start_limit_"+length).val(before_close_val);
            //
            //         var current_close_limit = $(this).val();
            //         if (current_close_limit != 0){
            //             var current_close_limit = parseFloat(current_close_limit) + parseFloat(0.01) ;
            //         }else {
            //             var current_close_limit = 0;
            //         }
            //         console.log("current_close_limit "+ current_close_limit);
            //
            //         var next_length = parseInt(length) + 1;
            //         console.log("next_length " + next_length);
            //         var is_span_type = $(".start_limit_" + next_length).attr("span_type");
            //         if (is_span_type == 1){
            //             $(".start_limit_" + next_length).empty().text(current_close_limit + "");
            //             $(".hidden_start_limit_" + next_length).val(current_close_limit);
            //         } else {
            //             $(".start_limit_" + next_length).val(current_close_limit);
            //         }
            //     } else  {
            //         var length = $(this).val('');
            //         alert("please fill above value first");
            //     }
            // });


    </script>
@endsection
