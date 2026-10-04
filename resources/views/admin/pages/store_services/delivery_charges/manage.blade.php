@extends('admin.layout.super_admin')
@section('title')
    Delivery Charges List
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
        <div class="external-horizontal-nav">
            @include('admin.include.store-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>Delivery Charges</h5>
                                    <span>All Delivery Charges List</span>
                                </div>
                            </div>
                        </div>
                        {{--<div class="col-lg-4">--}}
                        {{--    <button type="button" id="add_rule" count="0" class="btn btn-black m-b-0 btn-right render_link">Add Rule</button>--}}
                        {{--</div>--}}
                    </div>
                </div>
            </div>
        </div>
        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <form id="main" method="post" action="{{ route('get:admin:update_store_delivery_charges',$slug)}}" enctype="multipart/form-data">
                            {{csrf_field() }}

                            <div class="form-group row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Delivery Charges List</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="dt-responsive table-responsive">
                                                <table id="new-cons" class="table table-striped table-bordered nowrap" style="width:100%">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Start Limit</th>
                                                        <th>Close Limit</th>
                                                        <th>Charges</th>
                                                        {{--<th>Action</th>--}}
                                                    </tr>
                                                    </thead>
                                                    <tbody id="table_body">
                                                    @if(isset($delivery_charges))
                                                        @foreach($delivery_charges as $key => $charge)
                                                            <tr id="delete_product_{{$charge->id}}">
                                                                <td >{{ $key + 1 }}</td>
                                                                @if($charge->close_limit == 0)
                                                                    <td colspan="2">
                                                                        <input type="hidden" class="form-control hidden_start_limit hidden_start_limit_{{ $key + 1 }}" name="start_limit[]" value="{{$charge->start_limit}}" readonly>
                                                                        <input type="hidden" name="close_limit[]" value="0">
                                                                        Above <span length="{{ $key + 1 }}" span_type="1" class="start_limit start_limit_{{ $key + 1 }}">{{ $charge->start_limit." KM" }}</span></td>
                                                                @else
                                                                    <td>
                                                                        {{ $charge->start_limit." KM" }}
                                                                        <input type="hidden" step="0.01" min="0" length="{{ $key + 1 }}" span_type="0"  class="form-control start_limit start_limit_{{ $key + 1 }}" name="start_limit[]" placeholder="start_limit" value="{{$charge->start_limit}}" readonly>
                                                                    </td>
                                                                    <td>
                                                                        {{ $charge->close_limit." KM" }}
                                                                        <input type="hidden" length="{{ $key + 1 }}" step="0.01" min="0" class="form-control close_limit close_limit_{{ $key + 1 }}" name="close_limit[]" placeholder="close_limit" value="{{$charge->close_limit}}">
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    <input type="number" step="0.01" min="0" class="form-control" name="charges[]" placeholder="Charges" value="{{$charge->charges}}">
                                                                </td>
                                                                {{--<td>--}}
                                                                {{--    @if( (count($delivery_charges) - 1) !=  $key)--}}
                                                                {{--        <a class="delete" delivery_charge_id="{{$charge->id}}">--}}
                                                                {{--            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}"--}}
                                                                {{--                 style="width:20px; height: 20px;" data-toggle="tooltip"--}}
                                                                {{--                 data-placement="top" title="Delete">--}}
                                                                {{--        </a>--}}
                                                                {{--    @endif--}}
                                                                {{--</td>--}}
                                                            </tr>
                                                        @endforeach
                                                    @else

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
            $("#table_body tr:last").remove();
            var length = $('#table_body tr').length;
            var length1 = parseInt(length) + parseInt(1);
            var length2 = parseInt(length1) + parseInt(1) ;

            var close_val = $(".close_limit_"+length).val();
            if (close_val != 0){
                var nest_start_val = parseFloat(close_val) + parseFloat(0.01) ;
            }else {
                var nest_start_val = 0;
            }
            console.log(close_val);
            console.log(nest_start_val);

            var new_row = '<tr id="delete_product_' + length1 + '">' +
                ' <td>' + length1 + '</td> ' +
                '<td>' +
                '       <input type="number" length="'+length1+'" step="0.01" min="0" span_type="0" class="form-control start_limit start_limit_'+length1+'" name="start_limit[]" placeholder="start_limit" value="'+nest_start_val+'" readonly>' +
                '</td>' +
                '<td>' +
                '       <input type="number" length="'+length1+'" step="0.01" min="0" class="form-control close_limit close_limit_'+length1+'" name="close_limit[]" placeholder="close_limit" value="">' +
                '</td>' +
                '<td> ' +
                '       <input type="number" step="0.01" min="0" class="form-control" name="charges[]"  placeholder="Charges" value="">' +
                '</td>' +
                '</tr>' +
                '<tr id="delete_product_' + length2 + '"> ' +
                '<td>' + length2 + '</td>' +
                '<td colspan="2">' +
                '<input type="hidden" class="form-control hidden_start_limit hidden_start_limit_' + length2 + '" name="start_limit[]" value="" readonly> ' +
                '<input type="hidden" name="close_limit[]" value="0">' +
                ' Above <span length="'+length2+'" span_type="1" class="start_limit start_limit_'+length2+'"></span></td> ' +
                '<td> <input type="number" step="0.01" min="0" class="form-control" name="charges[]"  placeholder="Charges" value="0"> </td>' +
                ' </tr>';

            $("#table_body").append(new_row);
        });

        $(document).on("keyup",".close_limit",function (e){
            var length = $(this).attr('length');
            console.log(length);
            var before_length = parseInt(length) - 1;
            console.log("before_length " + before_length);
            var before_close_val = $(".close_limit_"+ before_length ).val();
            console.log("before_close_val " + before_close_val);
            if (before_close_val > 0 || length == 1) {
                if (length == 1) {
                    var before_close_val = 0;
                }else {
                    var before_close_val = parseFloat(before_close_val) + parseFloat(0.01) ;
                }

                $(".start_limit_"+length).val(before_close_val);
                var current_close_limit = $(this).val();
                if (current_close_limit != 0){
                    var current_close_limit = parseFloat(current_close_limit) + parseFloat(0.01) ;
                }else {
                    var current_close_limit = 0;
                }
                console.log("current_close_limit "+ current_close_limit);

                var next_length = parseInt(length) + 1;
                console.log("next_length " + next_length);
                var is_span_type = $(".start_limit_" + next_length).attr("span_type");
                if (is_span_type == 1){
                    $(".start_limit_" + next_length).empty().text(current_close_limit + " KM");
                    $(".hidden_start_limit_" + next_length).val(current_close_limit);
                } else {
                    $(".start_limit_" + next_length).val(current_close_limit);
                }
            } else  {
                var length = $(this).val('');
                alert("please fill above value first");
            }
        });

        $(document).on("change",".close_limit",function (e){
            var length = $(this).attr('length');
            console.log(length);
            var before_length = parseInt(length) - 1;
            console.log("before_length " + before_length);
            var before_close_val = $(".close_limit_"+ before_length ).val();
            console.log("before_close_val " + before_close_val);
            if (before_close_val > 0 || length == 1) {
                if (length == 1) {
                    var before_close_val = 0;
                }else {
                    var before_close_val = parseFloat(before_close_val) + parseFloat(0.01) ;
                }

                $(".start_limit_"+length).val(before_close_val);
                var current_close_limit = $(this).val();
                if (current_close_limit != 0){
                    var current_close_limit = parseFloat(current_close_limit) + parseFloat(0.01) ;
                } else {
                    var current_close_limit = 0;
                }
                console.log("current_close_limit "+ current_close_limit);

                if (before_close_val >= current_close_limit){
                    alert("please set proper value");
                }
                var next_length = parseInt(length) + 1;
                console.log("next_length " + next_length);
                var is_span_type = $(".start_limit_" + next_length).attr("span_type");
                if (is_span_type == 1){
                    $(".start_limit_" + next_length).empty().text(current_close_limit + " KM");
                    $(".hidden_start_limit_" + next_length).val(current_close_limit);
                } else {
                    $(".start_limit_" + next_length).val(current_close_limit);
                }
            } else  {
                var length = $(this).val('');
                alert("please fill above value first");
            }
        });

        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var delivery_charge_id = $(this).attr('delivery_charge_id');
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this delivery charge range!",
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
                            url: '{{ route('get:admin:delete_store_delivery_charges') }}',
                            data: {
                                delivery_charge_id : delivery_charge_id
                            },
                            success: function (result) {
                                if (result.success == true) {
                                    swal({
                                        title: "Success!",
                                        text: result.message,
                                        type: "success",
                                        confirmButtonText: "View",
                                    }, function() {
                                        console.log(result);
                                        location.reload();
                                    });
                                }
                                else {
                                    swal("Warning", result.message, "warning");
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Delivery charge range detail is safe :)", "error");
                    }
                });
        });
    </script>
@endsection
