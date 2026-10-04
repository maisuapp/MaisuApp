@extends('front.layout.default')
@section('title')
    <title> {{ isset($restaurant->name)?$restaurant->name." - ":"" }} Restaurant</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Restaurant">
    <meta name="keywords" content="Restaurant">
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/front/css/smart_cart.css?v=0.1') }}">
    <style>
        pre {
            width: inherit;
            overflow-x: hidden;
            border: none;
            background-color: transparent;
            padding: 0 10px 0 0;
            font-size: 14px;
            /*font-family: Arial;*/
            white-space: pre-line;
            word-break: keep-all;
        }

        .hotel-info .info .inline label, .hotel-info .info .inline p {
            width: auto;
            display: inline-block;
        }



        .hotel-info .title .res-type {
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 19px;
            @if($restaurant->res_type == "1")
            border: 1px solid #4CAF50;
            color: #4CAF50;
            @else
            border: 1px solid #c0392c;
            color: #c0392c;
            @endif
            float: left;
        }

        .hotel-info .title .res-type::before {
            content: ' ';
            display: block;
            position: relative;
            @if($restaurant->res_type == "veg")
              background: url('{{asset('assets/front/images/icons/pure-veg.png')}}') no-repeat;
            @else
              background: url('{{asset('assets/front/images/icons/non-veg.png')}}') no-repeat;
            @endif
              background-size: contain;
            height: 25px;
            width: 25px;
            top: 1px;
            left: 0;
            float: left;
            padding-right: 28px;
        }

        .list-group {
            margin-bottom: 0;
            border-bottom: 1px solid gainsboro;
        }

        .list-group-item, .list-group:last-child {
            border: none;
        }

        .sc-cart-summary-subtotal {
            text-align: left;
        }

        .sc-cart-toolbar {
            margin-top: 0;
        }

        .sc-cart-remove {
            font-size: 17px;
            top: 5px;
        }


        @media screen and (max-width: 786px) {
            .hotel-info .info label, .hotel-info .info p {
                width: auto;
                display: inline-block;
            }
        }

        .heart {
            /*background-color: #fa4a5b;*/
            width: 30px;
            height: 30px;
            color: #fff;
            position: absolute;
            top: 0px;
            right: 6px;
            text-align: center;
            padding: 3px;
            font-size: 20px;
            cursor: pointer;
        }

        .heart::after {
            content: "";
            position: absolute;
            bottom: -9px;
            width: 0;
            height: 0;
            right: 0;
            border-right: 9px solid transparent;
            /*border-top: 9px solid #d22b3b;*/
        }

        .favourited {
            background-color: #FFA000;
        }

        .favourited::after {
            border-top: 9px solid #d68702;
        }

        .like {
            background-color: #fa4a5b;
        }

        .like::after {
            border-top: 9px solid #d22b3b;
        }

        .favourited .fa-heart {
            color: #d22b3b;
        }

        .product-cart-list {
            max-height: 250px;
            overflow-y: auto;
        }

        .closed {
            color: #d22b3b;
            font-weight: 600;
        }

        /* new check box design */
        /* The container */


        /* Hide the browser's default radio button */
        .container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        /* Create a custom radio button */
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #eee;
            border-radius: 50%;
        }

        /* On mouse-over, add a grey background color */
        .container:hover input ~ .checkmark {
            background-color: #ccc;
        }

        /* When the radio button is checked, add a blue background */
        .container input:checked ~ .checkmark {
            background-color: #2196F3;
        }

        /* Create the indicator (the dot/circle - hidden when not checked) */
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the indicator (dot/circle) when checked */
        .container input:checked ~ .checkmark:after {
            display: block;
        }

        /* Style the indicator (dot/circle) */
        .container .checkmark:after {
            top: 9px;
            left: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
        }
        .categoryListsLink:hover{
            background-color: #f5f5f5 !important;
        }
        .productCateTitleSection{
            color:#333333 ;
            /*border-bottom: 1px solid #333333;*/
            border-bottom: 1px solid #ddd;
            margin: 10px;
            padding-bottom: 10px;
        }
        @media (max-width: 1200px){
            .col-md-9 {
                width: 100%;
            }
        }
        @media screen and (max-width: 1024px) {
            .theiaStickySidebar{
                padding: 0;
            }
        }
        @media(max-width: 1440px) and (min-width: 750px) {
            .vendor-list .food-list .bottom .item {
                padding-bottom: 40px;
            }
        }
        .sc-cart-heading{
            border-bottom: 1px solid #ddd;
        }
        .category{
            width: fit-content !important;
            float: right !important;
            /*position: fixed !important;*/
        }
    </style>

@endsection
@section('page-content')
    <!--content-->
    <div class="container-fluid content main-store-box">
        <div class="hotel-info">
            <div class="row">
                <div class="col-md-3 col-sm-3 col-xs-12 res-img">
                    {{--@if (Illuminate\Support\Facades\Auth::guard("user")->check() && $check_fev_restaurant > 0)
                        <span class="heart favourited" id="user-fav"><i class="fa fa-heart" aria-hidden="true"></i></span>
                    @else
                        <span class="heart like" id="user-fav"><i class="fa fa-heart" aria-hidden="true"></i></span>
                    @endif--}}
                    @if(file_exists(public_path('assets/images/store-images/'.$restaurant->image)))
                        <img src="{{ asset('/assets/images/store-images/'.$restaurant->image)}}">
                    @else
                        <img src="{{ asset('assets/images/store-images/no_store_img.png') }}"  alt="{{ $restaurant->name }}">
                    @endif
                </div>
                <div class="col-md-9 col-sm-9 col-xs-12 res-data">
                    <div class="row">
                        <div class="title">
                            <h1>{{ $restaurant->name }}</h1>
    {{--                        @if($restaurant->res_type == "veg")--}}
    {{--                            <span class="res-type">Pure Veg</span>--}}
    {{--                        @endif--}}
    {{--                        @if($restaurant->res_type == "non-veg")--}}
    {{--                            <span class="res-type">Non Veg</span>--}}
    {{--                        @endif--}}
                            @if($restaurant->rating != 0)
                                <span class="rate"><i class="fa fa-star"></i> {{ $restaurant->rating }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-12 info ">
                        <div class="row">
                            <div class="col-sm-3 info">
                                <div>
                                    <label>
                                        Opening Hours:
                                        @if($res_time != "Close")
                                            <span class="highlight">
                                                Open
                                            </span>
                                        @else
                                            ---
                                        @endif
                                    </label>
                                    <p>
                                        @if($res_time != "Close")
                                            {{ $res_time }}
                                        @else
                                            <span class="closed">
                                                @if(isset($service_categories->id) && $service_categories->id == 5 )
                                                    Restaurant Closed
                                                @else
                                                    Store Closed
                                                @endif

                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label>
                                        @if(isset($service_categories->id) && $service_categories->id == 5 )
                                            Cuisines :
                                        @else
                                            Cateogry :
                                        @endif
                                    </label>
                                    <p>
                                        @foreach($cuisines as  $key => $cuisine)
                                            {{ $loop->first ? '' : ', ' }}
                                            {{ $cuisine->cuisine_name }}
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-5 info">
                                @if($restaurant->order_min_amount != Null)
                                    <div class="inline min-amount">
                                        <label>Order Min Amount :</label>
                                        <p class="inr-min">{{ "$".$restaurant->order_min_amount }}</p>
                                    </div>
                                @endif
                                @if($restaurant->offer_min_amount != 0 && $restaurant->offer_amount != 0)
                                    <div class="inline">
                                        <label>Offer :</label>
                                        <p>
                                            @if($restaurant->offer_type == 1)
                                                <span class="highlight">{{ "$".$restaurant->offer_amount }} discount on min Order <span
                                                            class="inr">{{ "$".$restaurant->offer_min_amount }}</span>
                                            </span>
                                            @else
                                                <span class="highlight">{{ $restaurant->offer_amount." %" }} discount on min Order <span
                                                            class="inr">{{ "$".$restaurant->offer_min_amount }}</span>
                                            </span>
                                            @endif
                                        </p>
                                    </div>
                                @elseif($restaurant->offer_amount != 0 && $restaurant->offer_min_amount == 0)
                                    <div class="inline">
                                        <label>Offer :</label>
                                        <p>
                                            @if($restaurant->offer_type == 1)
                                                <span class="highlight">{{ "$ ".$restaurant->offer_amount }} off on all orders
                                            </span>
                                            @else
                                                <span class="highlight">{{ $restaurant->offer_amount." %" }} off on all orders
                                            </span>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                                <div>
        {{--                            <label>Average Cost :</label>--}}
        {{--                            <pre>{{ $restaurant->description }}</pre>--}}
                                </div>
                            </div>
                            <div class="col-sm-4 info">
                                <div class="inline">
                                    <label>Phone number : </label>
                                    <p><span class="highlight">{{ \App\model\User::ContactNumber2Stars($restaurant->country_code).\App\model\User::ContactNumber2Stars($restaurant->contact_number) }}</span></p>
                                </div>
                                <div class="inline">
                                    <label>Delivery time : </label>
                                    <p><span class="highlight">{{ $restaurant->eta_delivery_time }} mins</span></p>
                                </div>
                                <div>
                                    <label>Address :</label>
                                    <p>{{ $restaurant->address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>



        <div class="sticky col-12" >
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 categoryLists">
                    <div class=" category" >
                        <!-- Cart submit form -->
    {{--                    <div class="float-right" style="float: right !important;">--}}
                            <div id="smartcart" class="  sc-cart sc-theme-default" >
                                <div class=" sc-cart-heading text-right"> <h4>Category Lists</h4></div>

                                <div class=" sc-cart-toolbar">

                                    <div class="sc-cart-summary" >
                                        {{--<div class="sc-cart-summary-subtotal"><span>Subtotal</span> <span
                                                class="sc-cart-subtotal" id="item-total">0</span>
                                            <p>Extra Charges may apply</p></div>--}}
                                        <div class="list-group categoryLists" id="myScrollspy">
                                            <ul class="nav nav-pills nav-stacked">
                                            @foreach($cuisines as $key => $sinlge_cuisine)
                                                <li>
                                                    <a href="#{{preg_replace('/[^a-zA-Z0-9_\-]/', '',str_replace(" ","",$sinlge_cuisine->cuisine_name))}}"  class="list-group-item categoryListsLink productScroll"  >{{$sinlge_cuisine->cuisine_name}}</a>
                                                </li>
                                                {{--<a href="javascript:void(0)" class="list-group-item">Second item</a>
                                                <a href="javascript:void(0)" class="list-group-item">Third item</a>--}}
                                            @endforeach
                                            </ul>
                                        </div>

                                    </div>

                                </div>
                            </div>
    {{--                    </div>--}}

                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 hotel-items">
                    <div class="vendor-list theiaStickySidebar restaurant-data row">
                        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}" id="restaurant_id">
                        <input type="hidden" name="cart_store_id" value="{{ $cart_store_id }}" id="cart_store_id">
                        @foreach($product_with_cousine_lists as $key=>$single_product_with_cousine_list )
                            <div id="{{preg_replace('/[^a-zA-Z0-9_\-]/', '',str_replace(" ","",$single_product_with_cousine_list['cuisine_name']))}}"
                                 class="col-lg-12 col-md-12 col-sm-12 col-xs-12 "
                                 @if($key != 0) style="margin-top: 50px !important;" @endif >
                                <h4  class="productCateTitleSection ">{{ $single_product_with_cousine_list['cuisine_name']}}</h4>
                            </div>
                            @foreach($single_product_with_cousine_list['product'] as $key => $product_list)
                                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 food-list ">
                                    <span class="sc-product-item thumbnail product-{{ $key+1 }} {{ $product_list->id }}">
                                        <input type="hidden" name="product_id" value="{{ $product_list->id }}"
                                               class="product_id">
                                        <div class="top">

                                            @if(file_exists(public_path('assets/images/store-images/product-images/'.$product_list->image)))
                                                <img src="{{ asset('/assets/images/store-images/product-images/'.$product_list->image)}}" name="{{ $product_list->name }}" alt="{{ $product_list->name }}"  class="lazy">
                                            @else
                                                <img src="{{ asset('/assets/images/store-images/product-images/no-product.png')}}" name="{{ $product_list->name }}"  alt="{{ $product_list->name }}" class="lazy">
                                            @endif
                                        </div>
                                        <div class="bottom caption">
                                            {{--@if($product_list->food_type == 1)
                                                <div class="veg" data-name="veg-nonveg">
                                                    <div class="info">
                                                        <span data-name="veg-nonveg" class="veg_nonveg">veg</span>
                                                        <h4 data-name="product_name" class="product_name pro_name">{{$product_list->name}}</h4>
                                                        <h5 data-name="product_desc">{{ $product_list->cuisine_name }}</h5>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="non-veg" data-name="veg-nonveg">
                                                    <div class="info">
                                                        <span data-name="veg-nonveg" class="veg_nonveg">non-veg</span>
                                                        <h4 data-name="product_name" class="product_name pro_name">{{$product_list->name}}</h4>
                                                        <h5 data-name="product_desc">{{ $product_list->cuisine_name }}</h5>
                                                    </div>
                                                </div>
                                            @endif--}}

                                            <div class="{{ ($product_list->food_type == 1)?"veg":(($product_list->food_type == 2)?"non-veg":"") }}" data-name="veg-nonveg">
                                                <div class="info">
                                                    <span data-name="veg-nonveg" class="veg_nonveg">{{ ($product_list->food_type == 1)?"veg":(($product_list->food_type == 2)?"non-veg":"") }}</span>
                                                    <h4 data-name="product_name" class="product_name pro_name" title="{{$product_list->name}}">{{$product_list->name}}</h4>
                                                    <h5 data-name="product_desc">{{ $product_list->cuisine_name }}</h5>
                                                </div>
                                            </div>

                                            <div class="item">
                                                <div class="row">
                                                    <div class="col-sm-6 ">
                                                        @if($product_list->discount_amount == Null)
                                                            <strong class="price pull-left product_price currency">{{ $product_list->amount }}</strong>
                                                            <input name="product_price" value="{{ $product_list->amount }}"
                                                                   type="hidden"/>
                                                        @else
                                                            <strong class="pull-left "> <del class="price currency">{{ $product_list->amount  }}</del> </strong>
                                                            <strong class="price pull-left product_price currency">{{ App\model\StoreProductDetails::getDiscountPrice($product_list->amount,$product_list->discount_amount,$product_list->discount_type) }}</strong>
                                                            <input name="product_price"
                                                                   value="{{ App\model\StoreProductDetails::getDiscountPrice($product_list->amount,$product_list->discount_amount,$product_list->discount_type) }}"
                                                                   type="hidden"/>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input name="product_slug" value="{{ $product_list->slug }}" type="hidden"/>
                                                        <button class="sc-add-to-cart btn btn-success btn-sm pull-right"
                                                                id="{{ $product_list->id }}">Add to cart</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </div>

                            @endforeach
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 cart">
                    <div class="theiaStickySidebar">
                        <div id="smartcart" class="panel panel-default sc-cart sc-theme-default">
                            <div class="panel-heading sc-cart-heading">{{ __('messages.133') }} <span class="sc-cart-count badge">0</span></div>
                            <div class="product-cart-list">
                                <input type="hidden" id="cart_item_list" name="cart_list[]">
                            </div>
                            <div class="panel-footer sc-cart-toolbar">
                                <div class="sc-cart-summary">
                                    <div class="sc-cart-summary-subtotal fontBold">
                                        <span>{{ __('messages.135') }}</span> <span class="sc-cart-subtotal currency" id="all-item-total">0</span>
                                        <p>{{ __('messages.136') }}</p></div>
                                </div>
                                <div class="sc-cart-toolbar">
                                    <a href="{{ route('get:checkout') }}"><button class="btn sc-cart-checkout" type="button">{{ __('messages.134') }}</button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!--end content-->

@endsection

@section('page-js')
    <script src="{{ asset('assets/front/js/theia-sticky-sidebar.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $('#user-fav').on('click', function () {
            @if (Illuminate\Support\Facades\Auth::guard("user")->check())
            var str1 = $(this).attr('class');
            var str2 = "like";
            var str3 = "favourited";
            var type;
            if (str1.indexOf(str2) != -1) {
                type = "add";
            }
            if (str1.indexOf(str3) != -1) {
                type = "remove";
            }
            var restaurant_id = $('#restaurant_id').val();
            var form = new FormData();
            form.append('restaurant_id', restaurant_id);
            form.append('type', type);
            $.ajax({
                url: '{{ route('post:fevourite_restaurant') }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.success == true) {
                        if (data.type == "add") {
                            $('#user-fav').removeClass('like');
                            $('#user-fav').addClass('favourited');
                        }
                        if (data.type == "remove") {
                            $('#user-fav').removeClass('favourited');
                            $('#user-fav').addClass('like');
                        }
                    } else {
                        console.log(data);
                    }
                }
            });
            @else
            // swal("", "login required to add this store in your favorite list", "error");
            // Notify("login required to add this store in your favorite list");
            // alert('login required to add this store in your favorite list');
            @endif
        });

        $(document).ready(function () {
            $('.categoryLists,.hotel-items, .cart')
                .theiaStickySidebar({
                    additionalMarginTop: 30
                });
            // $('.categoryLists,.hotel-items, .cart')
            //     .theiaStickySidebar({
            //         additionalMarginTop: 30
            //     });
            $(".productScroll").on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('html, body').animate({
                    scrollTop: ($(target).offset().top)
                }, 2000);
            });
        });

        $('.sc-add-to-cart').on("click", function () {
            var id = $(this).attr('id');
            authcheck(id);
        });

        //check auth user or not
        function authcheck(id) {
            @if (Illuminate\Support\Facades\Auth::guard("user")->check())
                @if (Illuminate\Support\Facades\Auth::guard("user")->user()->verified_at == Null)
                    url = '{{ route('get:otp-verify') }}';
                    window.location.href = url;
                @else
                    closed(id);
                @endif
            @else
            $('.btn-log-in').trigger("click");
            @endif
        }
        //check restaurant close
        function closed(id) {

            var res_time = "{{ $res_time }}";
            if (res_time == "Closed") {
                alert('{{$restaurant->name}} restaurant closed!').preventDefault().click();
            } else {
                // openModalWithToppings(id);
                var cart_store_id = $("#cart_store_id").val();
                var restaurant_id = $("#restaurant_id").val();
                if(cart_store_id == restaurant_id || cart_store_id == 0 ){
                    openModalWithToppings(id);
                }else{
                    openCartRemovePopup(id);
                }
            }
        }
        @if($cart_item > 0 && $cart_item_data !="" )
        $(".badge").empty();
        $(".badge").append({{$cart_item}});
        var cart_element = $(".product-cart-list");
        cart_element.empty();
        cart_element.append({!! $cart_item_data !!});
        $("#all-item-total").empty();
        $("#all-item-total").append({{ isset($all_store_total_pay)?$all_store_total_pay:0 }});
        @endif
        var messages_130 = "{{ __('messages.130') }}";
        var messages_131 = "{{ __('messages.131') }}";

        function openCartRemovePopup(id,msg=null,remove_cart=null){
            title =  "Remove Cart Item ?";
            txt =  msg != null ? msg : "if press yes then Remove Cart Item!";
            swal({
                    html:true,
                    title: title,
                    text: txt,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function (isConfirm) {
                    if (isConfirm) {
                        if(remove_cart!=null && remove_cart==1) {
                            $.ajax({
                                url: "{{ route('post:temp_cart_delete') }}",
                                type: 'post',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {"temp_cart_id": id},
                                async: false,
                                cache: false,
                                success: function (data) {
                                    if (data.success == true) {
                                        url = '{{ route('get:checkout') }}';
                                        window.location.href = url;
                                    } else {
                                        location.reload();
                                    }
                                }
                            });
                        } else {
                            openModalWithToppings(id);
                            /*swal("success!", "Cart is updated", "success");*/
                        }

                    }
                });
        }

        @if (Session::has('cart_error'))
            openCartRemovePopup("{{ Session::get('temp_cart_id') }}","{!!  Session::get('cart_error')  !!}",1);
        @endif

    </script>
    <script src="{{ asset('assets/front/js/custom-cart.js?v=0.16') }}" type="text/javascript"></script>
@endsection
