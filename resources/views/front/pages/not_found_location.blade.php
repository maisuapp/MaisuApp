@extends('front.layout.default')
@section('title')
    <title>Home</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Restaurant">
    <meta name="keywords" content="Restaurant">
@endsection
@section('page-css')
    <style>
        .content{
            padding-top: 5%;
            padding-bottom: 0;
        }
        @media screen and (max-width: 786px) {
            .content{
                padding-top: 2%;
            }
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
<div class="container-fluid content">

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="not-found">
            <img src="{{ asset('/assets/front/images/template-images/logo.gif') }}">
            <div class="not-found-head">Wish we were here!</div>
            <div class="not-found-content">We don’t deliver here yet. Why don’t you try a different location?</div>

        </div>
    </div>
</div>
<!--end content-->
@endsection

@section('page-js')

@endsection