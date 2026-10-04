@extends('front.layout.default')
@section('title')
    <title>Restaurant List</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Restaurant">
    <meta name="keywords" content="Restaurant">
@endsection
@section('page-css')
    <style>
        .cuisine-data li span button {
            background: none;
            outline: none;
            border: none;
        }

        .location-name {
            display: none;
        }

        .offer-button {
            padding: 0;
        }

        .offer-button a {
            text-decoration: none;
            color: black;
            font-size: 15px;
        }

        .res_listing {
            height: 350px;
            float: left;
            padding: 10px 0;
        }

        .heart {
            /*background-color: #fa4a5b;*/
            width: 30px;
            height: 30px;
            color: #fff;
            position: absolute;
            top: 22px;
            right: 13px;
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

        @media screen and (min-width: 786px) {
            .vendor-list a:hover .heart {
                top: 21px;
                right: 12px;
            }
        }
    </style>
@endsection
@section('page-content')<!--content-->
<div class="container-fluid content" style="min-height: 520px;">
    <nav class="navbar navbar-default filter">
        <span class="rest-count">{{ $count }} {{ $service_category_slug == 'food-delivery' ? 'Restaurants' : 'Store' }}</span>
        <span class="location-name">{{ $location }}</span>
        <ul class="nav cuisine-data">
            <li class="filter-title">FILTER BY :</li>
            <li>
                <span class="checkbox-success offer-button">
                    <a href="{{ route('get:search_restaurants_list',[$service_category_slug,"all"]) }}">
                        @if($service_category_slug == "food-delivery")All Resto @else All Stores @endif
                    </a>
                </span>
            </li>
            <li><span><button onclick="cuisine_close()">{{ $service_category_slug == 'food-delivery' ? 'Cuisine' : 'Category ' }} <i class="fa fa-caret-down"></i></button></span></li>
            <li>
                <span class="checkbox-success offer-button">
                    <a href="{{ route('get:search_offer_restaurants_list',[$service_category_slug]) }}">Show Offer</a>
                </span>
            </li>
        </ul>
    </nav>
    <div id="data" class="row cuisine-data " style="display: none">
        @foreach($cuisine_lists as $cuisine_list)
            {{--<div class="col-md-2 checkbox checkbox-success">--}}
            {{--<input id="{{ $cuisine_list->name }}" type="checkbox">--}}
            {{--<label for="{{ $cuisine_list->name }}">{{ $cuisine_list->name }}</label>--}}
            {{--</div>--}}
            <div class="col-md-2 col-sm-3 col-xs-4 cuisine_lists">
                <div>
                    <a class="filter_cuisine_name" id="{{ $cuisine_list->name }}"
                       href="{{ route('get:search_restaurants_list',[$service_category_slug,$location,strtolower(str_replace(' ','-',$cuisine_list->name))]) }}">{{ $cuisine_list->name }}</a>
                </div>
            </div>
        @endforeach
        {{--<input type="button" href="#" class="submit" value="Apply Filters">--}}
        {{--<input type="button" href="#" class="cancel" value="Cancel">--}}
        <a class="close" onclick="cuisine_close()"></a>
    </div>

    <div class="container store-container-lists">

            <div class="row">
                @if($restaurants)
                    @foreach($restaurants as $key=>$restaurant_data)

                        <div class="col-lg-4 col-md-6 col-sm-6 store-main-box">
                            <div class="card service-card card-height" >
                                @if(file_exists(public_path('assets/images/store-images/'.$restaurant_data['image'])))
                                    {{--                                    <div class="" style="width: 100%; height: 180px; background: red url({{ asset('assets/images/store-images/'.$restaurant_data['image']) }}) no-repeat center; margin: auto; overflow: hidden;"></div>--}}
                                    <img src="{{ asset('assets/images/store-images/'.$restaurant_data['image']) }}" class="card-img-top store-image"  alt="{{ $restaurant_data['name'] }}">
                                @else
                                    {{--                                    <div class="" style="width: 100%; height: 180px; background: red url({{ asset('assets/images/store-images/1.png') }}) no-repeat center; margin: auto; overflow: hidden;"></div>--}}
                                    <img src="{{ asset('assets/images/store-images/no_store_img.png') }}" class="card-img-top store-image"  alt="{{ $restaurant_data['name'] }}">
                                @endif
                                <div class="card-body store-detail-body">
                                    <a href="{{ route('get:restaurant_data',[$restaurant_data->id]) }}" target="_blank">
                                        <h5 class="card-title store-name">{{ ($restaurant_data['name']) }}</h5>
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            @if($cuisines[$key])
                                                @foreach($cuisines[$key] as $keys=>$cuisines_all)
{{--                                                    @foreach($cuisines_all as $keys=>$cuisine)--}}

{{--                                                        @if($cuisine['store_id'] == $restaurant_data['id'])--}}
                                                            @if($keys<3)
                                                                {{ $cuisines_all['name'] }},
                                                            @elseif($keys==3)
                                                                , More..
                                                            @endif
{{--                                                        @endif--}}

{{--                                                    @endforeach--}}
                                                @endforeach
                                            @endif
                                        </h6>

                                        <div class="about">
                                            <span class="rate"><i class="fa fa-star"></i> {{ $restaurant_data['ratings'] }} </span>
                                            <p class="time" >
                                                @if($res_time[$key] == "close") Closed @else {{  $res_time[$key] }} @endif
                                            </p>
{{--<!--                                            <p class="contact"><i class="fa fa-phone icon-color"></i> {{ App\model\User::ContactNumber2Stars($restaurant_data['contact_details']) }}</p>-->--}}
                                        </div>


                                        @if( $restaurant_data['offer_min_amount'] != 0 && $restaurant_data['offer_amount'] != 0)
                                            <div class="discount">
                                                @if($restaurant_data['offer_type'] == 1)
                                                    <p class="discount-string">
                                                        <img src="{{asset('assets/front/img/icons/discount-24.png')}}" alt="{{ ucfirst(strtolower($restaurant_data['name'])) }}"> {{ "$ ".$restaurant_data['offer_amount'] }}
                                                        off on orders above $ {{ $restaurant_data['offer_min_amount'] }}
                                                    </p>
                                                @else
                                                    <p class="discount-string">
                                                        <img src="{{asset('assets/front/img/icons/discount-24.png')}}" alt="{{ ucfirst(strtolower($restaurant_data['name'])) }}"> {{ $restaurant_data['offer_amount'] . '%' }}
                                                        off on orders above $ {{ $restaurant_data['offer_min_amount'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        @elseif($restaurant_data['offer_amount'] != 0 && $restaurant_data['offer_min_amount'] == 0)
                                            <div class="discount">
                                                @if($restaurant_data['offer_type'] == 1)
                                                    <p class="discount-string">
                                                        <img src="{{asset('assets/front/img/icons/discount-24.png')}}" alt="{{ ucfirst(strtolower($restaurant_data['name'])) }}"> {{ "$ ".$restaurant_data['offer_amount'] }}
                                                        off on all orders
                                                    </p>
                                                @else
                                                    <p class="discount-string">
                                                        <img src="{{asset('assets/front/img/icons/discount-24.png')}}" alt="{{ ucfirst(strtolower($restaurant_data['name'])) }}"> {{ $restaurant_data['offer_amount'].'%' }}
                                                        off on all orders
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>

                    @endforeach
                @endif
            </div>

    </div>
</div>
<!--end content-->

@endsection

@section('page-js')
    <script type="text/javascript">
        //        $(function () {
        //            $(".food-list").slice(0, 6).show();
        //            $("#loadMore").on('click', function (e) {
        //                e.preventDefault();
        //                $(".food-list:hidden").slice(0, 4).show();
        //                if ($(".food-list:hidden").length == 0) {
        //                    $("#load").fadeOut('slow');
        //                }
        //            });
        //        });

        function cuisine_close() {
            if ($('#data').hasClass('active')) {
                $("#data").removeClass("active").hide();
            } else {
                $('#data').show().addClass('active');
            }
        }

        $(document).ready(function () {
            $("#loadmore").trigger('click');
            $('#data').hide();

            @if($restaurant_data->offer_min_amount == 0)
            $(".scroll").addClass("offers");
            @endif
        });

    </script>

    {{--$('.filter_cuisine_name').click(function () {--}}
    {{--var filter_cuisine = ($(this).attr('id'));--}}
    {{--var location = $('.location-name').text();--}}
    {{--console.log(filter_cuisine);--}}
    {{--console.log(location);--}}
    {{--$.ajax({--}}
    {{--type: 'get',--}}
    {{--headers: {--}}
    {{--'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}
    {{--},--}}
    {{--data: {filter_cuisine: "filter_cuisine", location: "location"},--}}
    {{--url: '{{ route('get:filter-cuisine') }}',--}}
    {{--success: function (data) {--}}
    {{--console.log(data);--}}
    {{--}--}}
    {{--});--}}
    {{--});--}}
    {{----}}

    {{--var canLoad = true;--}}

    {{--$(window).scroll(function () {--}}
    {{--var hT = $(".load-more").offset().top,--}}
    {{--hH = $(".load-more").outerHeight(),--}}
    {{--wH = $(window).height(),--}}
    {{--wS = $(this).scrollTop();--}}
    {{--if (wS > (hT + hH - wH) && canLoad) {--}}
    {{--canLoad = false;--}}
    {{--$(".load-more").trigger("click");--}}
    {{--}--}}
    {{--});--}}
    {{--$('.scroll').jscroll();--}}

    {{--var offset = $('.scroll').length;--}}

    {{--$('.scrolls').endlessScroll({--}}
    {{--fireOnce: false,--}}
    {{--fireDelay: false,--}}
    {{--loader: '',--}}
    {{--insertAfter: '.scroll:last',--}}
    {{--content: function(i) {--}}
    {{--return '<li>' + (i + offset) + '</li>';--}}
    {{--}--}}
    {{--});--}}

@endsection

