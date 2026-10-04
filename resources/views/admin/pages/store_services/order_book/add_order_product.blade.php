@extends('admin.layout.all_admin')
@section('title')
    Add Product Order
@endsection
@section('page-css')
    <style>
        .md-perspective,
        .md-perspective body {
            height: 100%;
            overflow: hidden;
        }

        .md-perspective body {
            background: #222;
            -webkit-perspective: 600px;
            -moz-perspective: 600px;
            perspective: 600px;
        }

        .md-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            /*width: 50%;*/
            width: 30%;
            max-width: 630px;
            min-width: 300px;
            height: auto;
            z-index: 2000;
            visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            backface-visibility: hidden;
            -webkit-transform: translateX(-50%) translateY(-50%);
            -moz-transform: translateX(-50%) translateY(-50%);
            -ms-transform: translateX(-50%) translateY(-50%);
            transform: translateX(-50%) translateY(-50%);
        }

        .md-show {
            visibility: visible;
        }

        .md-overlay {
            position: fixed;
            width: 100%;
            height: 100%;
            visibility: hidden;
            top: 0;
            left: 0;
            z-index: 1000;
            opacity: 0;
            background: rgba(55, 58, 60, 0.65);
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .md-show ~ .md-overlay {
            opacity: 1;
            visibility: visible;
        }

        /* Content styles */
        .md-content {
            color: #666666;
            background: #fff;
            position: relative;
            border-radius: 3px;
            margin: 0 auto;
        }

        .md-content h3 {
            color: #fff;
            margin: 0;
            /*padding: 0.4em;*/
            padding: 0.6em 0.4em 0.6em 1em;
            text-align: left;
            font-weight: 400;
            font-size: 1.5em;
            opacity: 0.8;
            border-radius: 3px 3px 0 0;
        }

        .md-content > div {
            padding: 15px 25px 30px 25px;
            margin: 0;
            font-size: 1em;
            /*font-weight: 300;*/
            /*font-size: 1.15em;*/
        }

        .md-content > div > div {
            width: 40%;
            margin: 0 auto;
            padding: 10px 0;
            justify-content: space-around;
            display: flex;
        }

        .md-content > div > div > img {
            border-radius: 50%;
            padding: 4px;
            border: 2px solid #4e99fe;
        }

        .md-content > div ul {
            margin: 0;
            padding: 0 0 30px 0;
        }

        .md-content > div ul li {
            padding: 5px 0;
        }

        /* Individual modal styles with animations/transitions */
        .md-effect-1 .md-content {
            -webkit-transform: scale(0.7);
            -moz-transform: scale(0.7);
            -ms-transform: scale(0.7);
            transform: scale(0.7);
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .md-show.md-effect-1 .md-content {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            transform: scale(1);
            opacity: 1;
        }

        .md-trigger:hover {
            color: #64b0f2;
            cursor: pointer;
        }

        .md-trigger img:hover {
            opacity: 0.7;
            cursor: pointer;
        }

        .approve, .reject {
            cursor: pointer;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <div class="other-service-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        @if(!isset($service_category))
                            <i class="feather icon-list bg-c-green"></i>
                        @endif
                        <div class="d-inline">
                            @if(isset($service_category))
                                <h5 style="margin-top: 10px">
                                    <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" height="40" width="40" style="margin-right: 5px">Add Product
                                </h5>
                            @else
                                <h5>Add Product</h5>
                                <span>Add Product in Order</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <form id="main" method="post" action="{{  route('post:admin:store_order_product',$slug) }}" enctype="multipart/form-data">
                            {{csrf_field() }}
                            <div class="form-group col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add Product Order</h5>
                                        {{--<a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? isset($product_details) ? route('get:admin:store_product_list',[$slug,$product_details->store_id]) : route('get:admin:store_product_list',[$slug,$store_id]) : route('get:store-admin:store_product_list') }}"--}}
                                        {{--class="btn btn-primary m-b-0 btn-right render_link"> Back</a>--}}
                                    </div>
                                    <div class="card-block">
                                        @if(isset($order_details))
                                            <input type="hidden" name="order_id" value="{{$order_details->id}}">
                                        @endif
                                        <div class="row">
                                            <div class="form-group col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Product Cuisines:<sup class="error">*</sup></label>
                                                    <div class="col-sm-8">
                                                        <select name="product_category" id="product_category" class="form-control" store_id="{{ isset($store_details)? $store_details->id : 0 }}" required>
                                                            <option disabled selected value="">Choose Product Cuisines</option>
                                                            @if(isset($product_categories))
                                                                @foreach($product_categories as $product_category)
                                                                    <option value="{{ $product_category->id }}" @if($product_category->status == 0) disabled @endif >{{$product_category->name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <span class="error">{{ $errors->first('product_category') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Product :<sup class="error">*</sup></label>
                                                    <div class="col-sm-8">
                                                        <select name="product_id" id="product_id" class="form-control" data-store-id="{{ isset($store_details)? $store_details->id : 0 }}" required>
                                                            <option disabled selected value="">Choose Product</option>
                                                            @if(isset($store_product_details))
                                                                @foreach($store_product_details as $product_details)
                                                                    <option value="{{ $product_details->id }}"
                                                                            @if($product_details->status == 0) disabled
                                                                            @endif
                                                                    >{{$product_details->name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <span class="error">{{ $errors->first('product_id') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Product Quantity:<sup class="error">*</sup></label>
                                                    <div class="col-sm-8">
                                                        <input type="hidden" class="form-control" name="product_quantity"
                                                               required id="product_quantity" placeholder="Product quantity" value="1">
                                                        <div>
                                                            <span id="minus"><i class="fa fa-minus-square" style="font-size: 1.5em; padding-right: 5px;margin: 0 5px; cursor: pointer;"></i></span>
                                                            <span id="quantity" style="vertical-align: top;">1</span>
                                                            <span id="plus"><i class="fa fa-plus-square" style="font-size: 1.5em; padding-left: 5px;margin:0 5px; cursor: pointer;"></i></span>
                                                        </div>
                                                        <span class="error">{{ $errors->first('product_quantity') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row" id="container-size">
                                                    <label class="col-sm-4 col-form-label">Product Size:<sup class="error">*</sup></label>
                                                    <div class="col-sm-8" id="size"></div>
                                                </div>

                                                <div class="form-group row" id="container-options">
                                                    <label class="col-sm-4 col-form-label">Product Options:<sup class="error">*</sup></label>
                                                    <div class="col-sm-8" id="options"></div>
                                                </div>

                                                <div class="form-group row" id="container-toppings">
                                                    <label class="col-sm-4 col-form-label">Product Toppings:<sup class="error">*</sup></label>
                                                    <div class="col-sm-8" id="toppings"></div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <center>
                                        <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                    </center>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>



@endsection
@section('page-js')
    {{--Model Script type detials--}}
    <script src="{{ asset('assets/js/classie.js')}}" type="text/javascript"></script>
    <script>
        $(document).ready(function(){
            $('#container-toppings').hide();
            $('#container-options').hide();
            $('#container-size').hide();
        });
        $('#product_id').change(function(e){
            let product_id = $(this).val();

            let store_id = $('#product_id').attr('data-store-id');
            console.log(store_id);
            @if(isset($order_details) && $order_details->service_cat_id == 5)
                $.ajax({
                type: 'get',
                url: '{{ route('get:admin:get_order_product_toppings') }}',
                data: { store_id: store_id, product_id: product_id },
                success: function (result) {
                    if (result.success == true) {
                        $("#size").empty();
                        $("#options").empty();
                        $("#toppings").empty();
                        $.each(result.size, function (index, size) {
                            console.log(index);
                            if(index == 0){
                                $("#size").append('<div class="radio"><label><input type="radio" checked="checked" name="size" value="'+size.id+'" ">'+ size.name +'</label></div>');
                            } else {
                                $("#size").append('<div class="radio"><label><input type="radio" name="size" value="'+size.id+'">'+ size.name +'</label></div>');
                            }
                        });
                        $.each(result.options, function (index, options) {
                            $("#options").append('<div class="checkbox"><label><input type="checkbox" name="options[]" value="'+options.id+'"> '+ options.name +'</label></div>');
                        });
                        $.each(result.toppings, function (index, toppings) {
                            $("#toppings").append('<div class="checkbox"><label><input type="checkbox" name="toppings[]" value="'+toppings.id+'"> '+ toppings.name +'</label></div>');
                        });
                        if(result.size_count > 0){
                            $('#container-size').show();
                        } else{
                            $('#container-size').hide();
                        }
                        if(result.options_count > 0){
                            $('#container-options').show();
                        } else{
                            $('#container-options').hide();
                        }
                        if(result.toppings_count > 0){
                            $('#container-toppings').show();
                        } else{
                            $('#container-toppings').hide();
                        }
                    } else {
                        $("#size").empty();
                        $("#options").empty();
                        $("#toppings").empty();
                        $('#container-toppings').hide();
                        $('#container-options').hide();
                        $('#container-size').hide();
                    }
                }
            });
            @endif
        });
    </script>
    <script>
        $("#product_category").on('change', function (e) {
            var category_id = e.target.value;
            var store_id = $('#product_category').attr('store_id');
            $.ajax({
                type: 'get',
                url: '{{ route('get:admin:get_store_order_product') }}',
                data: {
                    store_id: store_id,
                    category_id: category_id
                },
                success: function (result) {
                    if (result.success == true) {
                        $("#product_id").empty().append('<option disabled selected value="">Choose Product</option>');
                        $.each(result.product, function (index, product) {
                            var disabled = "";
                            if (product.status == 0) {
                                var disabled = "disabled";
                            }
                            $("#product_id").append('<option value="' + product.id + '" "' + disabled + '">' + product.name + '</option>');
                        });
                    } else {
                        $('#product_id').empty().append('<option disabled selected value="">Product Not Found</option>');
                    }
                }
            })
        });

        $(function () { // you can wrap it here with in document ready block
            var qty = $("#quantity").text();
            $("#plus").click(function () {
                var count = qty++;
                document.getElementById("quantity").innerHTML = count;
                document.getElementById("product_quantity").value = count;
                //$(this).html(+this.textContent + 1);
            });
            $("#minus").click(function () {
                if (qty == 1){
                    qty = 1;
                    document.getElementById("quantity").innerHTML = qty;
                    document.getElementById("product_quantity").value = qty;
                }else {
                   var count = qty--;
                    document.getElementById("quantity").innerHTML = count;
                    document.getElementById("product_quantity").value = count;
                }
                //$(this).html(+this.textContent + 1);
            });
        });
    </script>
@endsection

