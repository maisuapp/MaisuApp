@extends('front.layout.default')
@section('title')
    <title>Not Found</title>
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
        .not-found
        {
            /* height: calc(100vh - 160px); */
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin: 0 auto!important;
            width: 280px;
        }
        .not-found img{
            width: 100%;
            text-align: center;
        }
        .not-found .not-found-head{
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            color: #969696;
            padding: 5px 0;
        }
        .not-found .not-found-content{
            padding: 5px 0;
            text-align: center;
            color: #BFBFBF;
            letter-spacing: 0.5px;
        }
        .footer{
            display: none;
        }
    </style>
@endsection
@section('page-content')<!--content-->
<div class="container-fluid content minheights">
    <nav class="navbar navbar-default filter">
        <span class="rest-count">  {{ $service_category_slug == 'food-delivery' ? 'Restaurants' : 'Store' }}</span>
        <span class="location-name"></span>
        <ul class="nav  cuisine-data">
            <li class="filter-title">FILTER BY :</li>
            <li><span><button onclick="cuisine_close()">{{ $service_category_slug == 'food-delivery' ? 'Cuisine' : 'Category ' }}  <i class="fa fa-caret-down"></i></button></span></li>
            {{--<li>--}}
            {{--<span class="checkbox checkbox-success">--}}
            {{--<input id="Offer" type="checkbox">--}}
            {{--<label for="Offer">Show Offer</label>--}}
            {{--</span>--}}
            {{--</li>--}}
            <li>
                <span class="checkbox-success offer-button">
                    <a href="{{ route('get:search_offer_restaurants_list',[$service_category_slug]) }}" style="color: #0e0e0e;">Show Offer</a>
                </span>
            </li>
        </ul>
    </nav>
    <div id="data" class="col-md-12 col-sm-12 col-xs-12 cuisine-data row">
        @foreach($cuisine_lists as $cuisine_list)
            {{--<div class="col-md-2 checkbox checkbox-success">--}}
            {{--<input id="{{ $cuisine_list->name }}" type="checkbox">--}}
            {{--<label for="{{ $cuisine_list->name }}">{{ $cuisine_list->name }}</label>--}}
            {{--</div>--}}
            <div class="col-md-2 col-sm-3 col-xs-4 cuisine_lists">
                <div>
                    <a class="filter_cuisine_name" id="{{ $cuisine_list->name }}" href="{{ route('get:search_restaurants_list',[$service_category_slug,$location,$cuisine_list->name]) }}">{{ $cuisine_list->name }}</a>
                </div>
            </div>
        @endforeach
        {{--<input type="button" href="#" class="submit" value="Apply Filters">--}}
        {{--<input type="button" href="#" class="cancel" value="Cancel">--}}
        <a class="close" onclick="cuisine_close()"></a>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 row">
        <div class="not-found">
            <img src="{{ asset('/assets/front/images/template-images/no_store_found.png') }}">
            <div class="not-found-head">Oh! There’s not much left</div>
            <div class="not-found-content">We can’t find anything related to your search .Try a different search.</div>
        </div>
    </div>
</div>
<!--end content-->
@endsection

@section('page-js')
<script type="text/javascript">
    function cuisine_close() {
        if ($('#data').hasClass('active')) {
            $("#data").removeClass("active").hide();
        }
        else {
            $('#data').show().addClass('active');
        }
    }

    $(document).ready(function () {
        $("#loadmore").trigger('click');
        $('#data').hide();
    });
</script>
@endsection
