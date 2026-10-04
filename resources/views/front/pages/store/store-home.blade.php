@php $general_settings = \App\model\GeneralSettings::first() @endphp
@extends('front.layout.default')
@section('title')
    <title>Delivery Services</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Service">
    <meta name="keywords" content="Service">
@endsection
@section('page-css')


    <style>

        #header {
            background: white;
            padding: 16px 0;
            height: 80px;
            border-bottom: solid 1px #ddd;
        }

        #header.header-fixed {
            background: rgba(255, 255, 255, 0.8);
        }

        .nav-menu a {
            color: black;
        }

        #header #logo h1 {
            margin: -10px 0 0 0;
        }

        .btn-policy{
            padding: 0 !important;
            background: #bc1c91 !important;
            color: #000000 !important;
            text-align: center !important;
            /*padding-left: 20% !important;*/
        }
        .btn-policy:active, .btn-policy:focus, .btn-policy:hover {
            background: #FFF !important;
            color: #bc1c91 !important;
        }
        .btn-policy::after {
            content: "";
            padding-left: 0 !important;
        }
        /*contact*/
        #contact .contact-about h3 {
            color: #bc1c91;
        }

        #contact .info i {
            color: #bc1c91;
        }

        /*#contact .social-links a {
            color: #363636;
            border: 1px solid #363636;
        }

        #contact .social-links a:hover {
            background: #363636;
            color: #fff;
        }*/
        #intro {
            background: url({{ asset('/assets/front/img/png/Slider-BG.png') }}) center top no-repeat;
            background-size: cover;
            position: relative;
            height: 650px;
            /*height: 80vh;*/
            width: 100%;
        }
    </style>
    <style>
        /* Dropdown Button */
        .dropbtn {
            background-color: unset;
            border: none;
            text-decoration: none;
            display: inline-block;
            color: #700c0c;
            font-family: "Montserrat", sans-serif;
            font-weight: 400;
            font-size: 14px;
            outline: none;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown button:focus {
            outline: none;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            /*background-color: #f1f1f1;*/
            /*min-width: 160px;*/
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            /*padding: 12px 16px;*/
            padding: 9px 16px;
            text-decoration: none;
            display: block;
            background-color: #f1f1f1;
            margin-top: 2px;
        }

    </style>
@endsection
<!--==========================
  Header
============================-->

@section('page-content')

<!--==========================
  Intro Section
============================-->
<section id="intro" class="store-intro">
    <div class="intro-text">
        <h3 class="text-white"> Order From Favourite <span class="change_store_lable">{{ isset($service_cat_id) ? ($service_cat_id->id == 5 ? 'Restaurants' : 'Stores') : 'Stores' }}</span> Near You</h3>
        <div class="col-md-9 col-lg-9 col-sm-9" >
            <form action="{{ route('get:search') }}" method="get">
                <div class="col-md-12 col-lg-12 col-sm-12" >
                    <div class="row">
                        <div class="col-12 col-sm pr-sm-0 search-paddind-cls">
                            <input type="text" name="search" id="stuff" value="" autocomplete="off" placeholder="Search by store name" class="form-control search searchtxt rmv-border-radius">
                        </div>
                        <div class="col-12 col-sm-auto pr-sm-0 pl-sm-0 search-paddind-cls">
                            <select class="service-select-option form-control rmv-border-radius delivery_service" name="service_category_id" id="service_category_id">
                                <option value="5" {{ isset($service_cat_id) ? ($service_cat_id->id == 5 ? 'selected' : '') : '' }}>Food Delivery</option>
                                <option value="6" {{ isset($service_cat_id) ? ($service_cat_id->id == 6 ? 'selected' : '') : '' }}>Grocery Delivery</option>
                                <option value="7" {{ isset($service_cat_id) ? ($service_cat_id->id == 7 ? 'selected' : '') : '' }}>Liquor Delivery</option>
                                <option value="8" {{ isset($service_cat_id) ? ($service_cat_id->id == 8 ? 'selected' : '') : '' }}>Flower Delivery</option>
                                <option value="9" {{ isset($service_cat_id) ? ($service_cat_id->id == 9 ? 'selected' : '') : '' }}>Water Delivery</option>
                                <option value="10" {{ isset($service_cat_id) ? ($service_cat_id->id == 10 ? 'selected' : '') : '' }}>Medicine Delivery</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-auto pl-sm-0 search-paddind-cls">
                            <input type="submit" name="submit" value="Search" class="btn search-btn rmv-border-radius">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<main id="main">
    <!--==========================
      About Us Section
    ============================-->

    <!--==========================
      Product Featuress Section
    ============================-->
    <section id="features">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 ">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 box wow fadeInLeft">
                            <div class="icon"><img src="{{ asset('assets/front/img/png/Choose-your-meal.png?v=0.1') }}"></div>
                            <h4 class="title"><a>Place Order</a></h4>
                            <p class="description">Register and search your favourite restaurant and place your order.</p>
                        </div>
                        <div class="col-lg-4 col-md-4 box wow fadeInUp" data-wow-delay="0.1s">
                            <div class="icon"><img src="{{ asset('assets/front/img/png/Restaurant-prepares-your-order.png?v=0.1') }}"></div>
                            <h4 class="title"><a>Prepares a meal</a></h4>
                            <p class="description">Restaurant receives your order request instantly  and prepares your meal.</p>
                        </div>
                        <div class="col-lg-4 col-md-4 box wow fadeInRight" data-wow-delay="0.2s">
                            <div class="icon"><img src="{{ asset('assets/front/img/png/Receive-your-order.png?v=0.1') }}"></div>
                            <h4 class="title"><a>Receive Order</a></h4>
                            <p class="description">You can track your order to your doorstep. Makes sure to give feedback.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--==========================
      Product Advanced Featuress Section
    ============================-->
    <section id="advanced-features">
        <div class="features-row section-bg">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 img-mobile">
                        <div class="row">
                            <div class="wow fadeInLeft col-md-6 col-sm-12">
                                <h1 class="float-left "><center>Download Fox-jek App</center></h1>
                                <p class="inline-block">As a user, you can download Fox-jek user app to get a portion of the meal whenever you want and enjoy a meal at a location. You can register or sign up into app by providing the basic information like name, email, contact details. Also, you can use a social account site like Facebook, Google to sign up.</p>
                                <p>After login, you can search for your preferred meal from your faviorite restaurant. You can view a list of the store with their information such as store name, offer details, rating and more. Also, you have an option to filter the restaurant by their timing, rating, cuisine and more. </p>
                                <p>After selecting your favorite restaurant, you can view food items, select the food item and place the food order. Also, you have an option to track your order request within the app. Download the app now enjoy a meal!</p>
                                <center class="float-left">
                                    <a href="{{ isset($general_settings)? (isset($general_settings->user_playstore_link) && $general_settings->user_playstore_link != Null)? $general_settings->user_playstore_link : "" : "" }}" target="_blank">
                                        <img class="app-store padding-3" src="{{ asset('assets/front/img/png/google-play-badge-black.png') }}">
                                    </a>
                                    <a href="{{ isset($general_settings)? (isset($general_settings->user_appstore_link) && $general_settings->user_appstore_link != Null)? $general_settings->user_appstore_link : "" : "" }}" target="_blank">
                                        <img class="app-store" src="{{ asset('assets/front/img/png/app-store-badge-black.png') }}">
                                    </a>
                                </center>
                            </div>
                            <div class=" col-md-6 col-sm-12 text-center">
                                <img class="advanced-feature-img-right wow fadeInRight mockup-image"
                                     src="{{ asset('assets/front/img/png/Mockup.png?v=0.2') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="features-row">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 img-mobile">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <img class="advanced-feature-img-left wow fadeInLeft mockup-image"
                                     src="{{ asset('assets/front/img/png/Mockup2.png?v=0.1') }}" alt="">
                            </div>
                            <div class="wow fadeInRight col-md-6 col-sm-12 w-100">
                                <h1 class="float-left"><center>Become a Partner</center></h1>
                                <p class="inline-block">Download the Fox-jek restaurant app and expand your restaurant business with the online platform. You can register into an app and provide the information about a restaurant.</p>
                                <p>You have an option to add store information using the store panel such as store name, timing, delivery radius, product details, etc.</p>
                                <p>After an admin approves your store request, you will get a real-time order request. You have an option to accept or decline the order request. Also, you can manage restaurant details with some few click.</p>
                                <center>
<!--                                    <ul class="actions btn-action">
                                        <li><a href="https://whitelabelfox.com/get-a-free-quote/" target="_blank" title="Call To Action" class="btn action-btn ">Call To Action</a></li>
                                    </ul>-->
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!--==========================
      Contact Section
    ============================-->
    @include('front.layout.footer')


</main>

<!--==========================
  Footer
============================-->
<!--========================== Footer ============================-->
<!-- #footer -->
@endsection
@section('page-js')
<script type="text/javascript">
    if (window.location.hash && window.location.hash == '#_=_') {
        window.location = '';
    }
</script>
<script>
    $(document).ready(function () {
        $('.user').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true   // 100% fit in a container
        });

        /*var defaultBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(-33.8902, 151.1759),
            new google.maps.LatLng(-33.8474, 151.2631));

        var input = document.getElementById('stuff');
        var options = {
            bounds: defaultBounds,
            types: ['establishment']
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);*/


        //code change store title based in dropdown change
        $(document).on("change",".delivery_service",function (){
            var service_type= $(".delivery_service").val();
            if(service_type == 5  || service_type == "5"){
                $(".change_store_lable").empty();
                $(".change_store_lable").text("Restaurants");
            }else{
                $(".change_store_lable").empty();
                $(".change_store_lable").text("Stores");
            }
        })
    });

    var modal = document.getElementById('user');

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
</script>

@endsection
