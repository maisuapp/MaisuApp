@php $general_settings = \App\model\GeneralSettings::first() @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MaisuApp</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicons -->
    @php $general_settings = \App\model\GeneralSettings::first() @endphp
    <link rel="icon"
          href="{{ isset($general_settings)? ($general_settings->website_favicon != Null) ? asset('assets/images/website-logo-icon/'.$general_settings->website_favicon) : '' : '' }}"
          type="image/x-icon">
    <link
        href="{{ isset($general_settings)? ($general_settings->website_favicon != Null) ? asset('assets/images/website-logo-icon/'.$general_settings->website_favicon) : '' : '' }}"
        rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,700|Open+Sans:300,300i,400,400i,700,700i"
          rel="stylesheet">

    <!-- Bootstrap CSS File -->
    <link href="{{ asset('assets/front/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Libraries CSS Files -->
    <link href="{{ asset('assets/front/css/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/front/css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/front/css/magnific-popup.css') }}" rel="stylesheet">

    <!-- Main Stylesheet File -->
    <link href="{{ asset('assets/front/css/welcome_page.css') }}" rel="stylesheet">


    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font/font-awesome.min.css')}}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css" crossorigin="anonymous">--}}

    <style>
        #logo img {
            width: 180px;
        }

        .mockup-image {
            height: 500px;
        }

        .wow h1 {
            margin-top: 20px !important;
        }

        .product-screen-1 img, .product-screen-2 img, .product-screen-3 img {
            width: 280px;
        }

        #about .about-img {
            height: auto!important;
            /*display: flex;*/
            /*justify-content: center;*/
            /*align-items: center;*/
        }

        #about .content p {
            margin: 0 0 10px 0;
        }

        .app-store {
            width: 200px;
        }

        .padding-3 {
            padding-top: 3px;
        }

        /*new add*/
        #header #logo h1 {
            margin: -10px 0 0 0;
        }

        /*contact*/
        #contact .contact-about h3 {
            color: #653996;
        }

        #contact .info i {
            color: #653996;
        }

        #contact .social-links a {
            /*color: #1dc8cd;*/
            color: #653996;
            /*border: 1px solid #1dc8cd;*/
            border: 1px solid #653996;
        }

        #contact .social-links a:hover {
            /*background: #1dc8cd;*/
            background: #653996;
            color: #fff;
        }

        /*back to top*/
        .back-to-top {
            /*background: linear-gradient(45deg, #1de099, #1dc8cd);*/
            background: linear-gradient(45deg, #653996, #653996)
        }

        .back-to-top:focus {
            /*background: linear-gradient(45deg, #1de099, #1dc8cd);*/
            background: linear-gradient(45deg, #653996, #653996)
        }

        .back-to-top:hover {
            /*background: #1dc8cd;*/
            background: #18A88B;
        }

        {{--/*new*/--}}
        {{--/*header*/--}}
        {{--#header #logo h1 {--}}
        {{--    margin: -12px 0 0 0;--}}
        {{--    /*margin: -4px 0 0 0;*/--}}
        {{--}--}}
{{--        #header.header-fixed {--}}
{{--            background: linear-gradient(45deg, #41e28e, #29c272);--}}
{{--        }--}}
             /*intro*/
        #intro {
            {{--background: linear-gradient(45deg, rgba(255, 252, 235, 0.8), rgba(255, 215, 0, 0.40)), url({{ asset('/assets/front/img/intro-bg.jpg') }}) center top no-repeat;--}}
            {{--background-size: cover;--}}
            {{--position: relative;--}}
            background: linear-gradient(180deg, #653996, #653996de), url({{ asset('/assets/front/img/slider_image_2.png') }}) center top no-repeat;
            background-size: cover;
            position: relative;
        }

        #intro .btn-get-started:hover {
            color: #653996;
            background: #fff;
        }

        /*call to action*/
        {{--#call-to-action {--}}
        {{--    overflow: hidden;--}}
        {{--    background: url({{asset('/assets/front/img/call-action-bg.png')}}) fixed center center;--}}
        {{--background: linear-gradient(rgba(30, 130, 152, 0.7), rgba(5, 70, 111, 0.5)), url(../img/call-to-action-bg.jpg) fixed center center;--}}
        {{--    background-size: cover;--}}
        {{--    padding: 80px 0;--}}
        {{--}--}}
        #call-to-action {
            overflow: hidden;
{{--            background: linear-gradient(rgba(255, 215, 0, 0.65), rgba(249, 230, 123, 0.2)), url({{asset('/assets/front/img/call-to-action-bg.jpg')}}) fixed center center;--}}
            background: linear-gradient(#653996, #653996de), url({{asset('/assets/front/img/slider_image_2.png')}}) fixed center center;
            background-size: cover;
            padding: 80px 0;
        }

        #call-to-action .cta-btn:hover {
            background: #ffffff;
            color: #653996;
            border: 2px solid #ffffff;
        }

        /*section header divider*/
        .section-header .section-divider {
            background: linear-gradient(0deg, #fff5c0 0%, #653996 100%)
        }

        #about .about-img img {
            /*margin-top: -18px;*/
        }

        #about-mirror {
            padding: 0;
            overflow: hidden;
        }

        #about-mirror .about-img {
            /*height: 450px;*/
            overflow: hidden;
        }

        #about-mirror .about-img img {
            margin-top: -45px;
            max-width: 100%;
        }

        .contact-about img {
            width: 80%;
            margin-left: -7px;
        }

        .text-justify {
            text-align: justify;
        }

        /*copyright*/
        .copyright {
            text-align: center !important;
            width: 100%;
        }

        .copyright a {
            padding: 0;
            color: #653996;
            font-size: 12px;
        }

        .copyright a:active, .copyright a:focus, .copyright a:hover {
            color: #844600e6;
        }

        .copyright a::after {
            content: "|";
            padding-left: 5px;
        }

        .copyright a:last-of-type::after {
            content: "";

        }
        #mobile-nav-toggle i {
            color: #653996;
        }

        .img-fluid {
            max-width: 50%;
            /*margin-left: -30px;*/
            margin-left: -20px;
        }

        @media (max-width: 768px) {
            #features .features-img img {
                position: relative;
            }
        }
    </style>
</head>

<body>

<!--========================== Header ============================-->
<header id="header" style="background-color:#fff;">
    <div class="container">
        <div id="logo" class="pull-left">
            <h1>
                <a href="#intro" class="scrollto">
                    <img {{--class="img-fluid"--}}
                         src="{{ isset($general_settings)? ($general_settings->website_logo != Null) ? asset('assets/images/website-logo-icon/'.$general_settings->website_logo) : asset('assets/images/website-logo-icon/logo.png') : asset('assets/images/website-logo-icon/logo.png') }}"
                         alt="FOX-JEK 2022"/>
                </a>
            </h1>
        </div>
        <nav id="nav-menu-container">
            <ul class="nav-menu">
                <li class="menu-active"><a href="#intro">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#advanced-features">Apps</a></li>
                <li><a href="#more-features">More Features</a></li>
                <li><a href="#contact">Contact Us</a></li>
                {{--                <li><a href="http://b.link/my-checkout_wiki">Help</a></li>--}}
                {{--                <li><a href="{{ route('get:store-admin:login') }}">myStore Login</a></li>--}}
                {{--                <li><a href="{{ route('get:provider-admin:login') }}">myService Login</a></li>--}}
            </ul>
        </nav>
    </div>
</header>

<!--========================== Intro Section ============================-->
<section id="intro">
    <div class="intro-text">
        <h2>Welcome to MaisuApp</h2>
        <p>We offer excellent service to excellent customers!</p>
        <a href="#about" class="btn-get-started scrollto">Get Started</a>
    </div>
    <div class="product-screens">
        <div class="product-screen-1 wow fadeInUp" data-wow-delay="0.4s" data-wow-duration="0.6s">
            <img src="{{ asset('assets/front/img/main_slider_mockup_1.png') }}" alt="">
        </div>
        <div class="product-screen-2 wow fadeInUp" data-wow-delay="0.2s" data-wow-duration="0.6s">
            <img src="{{ asset('assets/front/img/main_slider_mockup_2.png') }}" alt="">
        </div>
        <div class="product-screen-3 wow fadeInUp" data-wow-duration="0.6s">
            <img src="{{ asset('assets/front/img/main_slider_mockup_3.png') }}" alt="">
        </div>
    </div>
</section>

<main id="main">

    <!--========================== About Us Section ============================-->
    <section id="about" class="section-bg">
        <div class="container">
            <div class="section-header">
                <h3 class="section-title">About Us</h3>
                <span class="section-divider"></span>
                <p class="section-description"></p>
            </div>
            <div class="row">
                <div class="col-lg-6 about-img wow fadeInLeft">
                    {{--<embed src="https://www.youtube.com/embed/puEpokdJZp0" allowfullscreen="true" width="425"  style="width: 95%" height="344">--}}
                    {{--                    <embed--}}
                    {{--                        src="{{ isset($general_settings)? (isset($general_settings->about_us_youtube_link) && $general_settings->about_us_youtube_link != Null)? $general_settings->about_us_youtube_link."?autoplay=1" : "" : "" }}"--}}
                    {{--                        autoplay="1" allowfullscreen="true" style="width: 95%" height="344">--}}
                    {{--                    <iframe width="95%" height="344" frameborder="0" src="https://www.youtube.com/embed/puEpokdJZp0?playlist=puEpokdJZp0&loop=1&autoplay=1"></iframe>--}}
                    {{--                    <iframe width="95%" height="344" frameborder="0" src="https://www.youtube.com/embed/puEpokdJZp0?&autoplay=1"></iframe>--}}
                    <img src="{{ asset('assets/front/img/handyman_vector.png') }}" alt="">
                </div>
                <div class="col-lg-6 content wow fadeInRight text-justify" style="margin-top: 20px">
                    <p>We are in the Tokyo, Japan, all in one service provider platform that allows providers to list the services they want to offer to customers. A customer can book on-demand services like a tutor, beautician, baby care, pet care, pest control, electrician and more.</p>
                    <p>MaisuApp, you can keep your service online anytime and reach more customers to earn more with a service request. Also, the MaisuApp says goodbye to holding multiple individual On-Demand Apps on your phone.</p>
                </div>
            </div>
        </div>
    </section>

    <!--========================== Product Featuress Section ============================-->
    <section id="features">
        <div class="container">
            <div class="row">
                {{--                <div class="col-lg-8 offset-lg-4">--}}
                <div class="col-lg-12 offset-lg-12">
                    <div class="section-header wow fadeIn" data-wow-duration="1s">
                        <h3 class="section-title">Product Features</h3>
                        <span class="section-divider"></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 features-img">
                    <img src="{{ asset('assets/front/img/product_features_2.png') }}" alt="" class="wow fadeInLeft">
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="col-lg-12 col-md-12">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 box wow fadeInLeft">
                                <div class="icon"><img
                                        src="{{ asset('assets/front/img/60_60/schedule_service.png') }}"></div>
                                <h4 class="title"><a>Schedule Service</a></h4>
                                <p class="description">Users have a choice to schedule services request at their
                                    convenience. They can book service now or later.</p>
                            </div>
                            <div class="col-lg-6 col-md-6 box wow fadeInRight" data-wow-delay="0.1s">
                                <div class="icon"><img
                                        src="{{ asset('assets/front/img/60_60/live_tracking.png') }}">
                                </div>
                                <h4 class="title"><a>Live Tracking</a></h4>
                                <p class="description">After placing a service request, users can track their request
                                    within the app with an advanced GPS System.</p>
                            </div>
                            <div class="col-lg-6 col-md-6 box wow fadeInLeft" data-wow-delay="0.2s">
                                <div class="icon"><img src="{{ asset('assets/front/img/60_60/manage_request.png') }}"></div>
                                <h4 class="title"><a>Manage request</a></h4>
                                <p class="description">A service provider can manage new service requests with
                                    accept/reject option based on their availability.</p>
                            </div>
                            <div class="col-lg-6 col-md-6 box wow fadeInRight" data-wow-delay="0.3s">
                                <div class="icon"><img
                                        src="{{ asset('assets/front/img/60_60/flexible_payment.png') }}"></div>
                                <h4 class="title"><a>Secure Payment</a></h4>
                                <p class="description">To pay for service requests, users have secure payment options
                                    like cash, credit card, and in-app wallet.</p>
                            </div>
{{--                            <div class="col-lg-6 col-md-6 box wow fadeInRight" data-wow-delay="0.3s">--}}
{{--                                <div class="icon"><img--}}
{{--                                        src="{{ asset('assets/front/img/60_60/Multiple_Driver_Search_Algorithm.png') }}">--}}
{{--                                </div>--}}
{{--                                <h4 class="title"><a>Multiple Driver Search Algorithm</a></h4>--}}
{{--                                <p class="description">The app script provides multiple driver search algorithm option--}}
{{--                                    like competitive & nearest first.</p>--}}
{{--                            </div>--}}
{{--                            <div class="col-lg-6 col-md-6 box wow fadeInRight" data-wow-delay="0.3s">--}}
{{--                                <div class="icon"><img src="{{ asset('assets/front/img/60_60/Schedule_Booking.png') }}">--}}
{{--                                </div>--}}
{{--                                <h4 class="title"><a>Schedule Booking</a></h4>--}}
{{--                                <p class="description">If a user is regularly used ride/delivery/services; they have the--}}
{{--                                    option to schedule booking at a set time.</p>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- #features -->

    <!--========================== Product Features Section ============================-->
    <section id="about-mirror">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 about-img wow fadeInLeft text-center">
                    <img src="{{ asset('assets/front/img/about-us.png') }}" alt="">
                </div>
            </div>
        </div>
    </section>

    <!--========================== Product Advanced Featuress Section ============================-->
    <section id="advanced-features">
        <div class="features-row section-bg">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <img class="advanced-feature-img-right wow fadeInRight mockup-image"
                             src="{{ asset('assets/front/img/user_mockup_3.png') }}" alt="">
                        <div class="wow fadeInLeft text-justify">
                            <h1>
                                <center>User App</center>
                            </h1>
                            <p>The MaisuApp allows you to find the provider services fast and easy way across Tokyo,
                                Japan. You can book provider services such as tutor, beautician, car wash, plumber,
                                electrician, gardening, pest control gardening, pet care, baby care, plumber and
                                more.</p>
                            <p>You can register into an app by providing basic information like name, email, and contact
                                details. Also, you can sign up through a social account. You can see the nearest
                                available providers list with details like service charges, feedback and profile
                                picture, and book the services. Also, you have the option to manage your profile, change
                                your password, invite friends, and manage bank account details. You can use the promo
                                code to get the discount from the services request.</p>
{{--                            <p>You have the option to log in through a social account or register into an application--}}
{{--                                with information like name, email, contact details, and more. Also, you can manage--}}
{{--                                profile information, view history, invite a friend and manage bank details. You can use--}}
{{--                                a promo code to get a discount on the total amount.</p>--}}
                            <center>
                                <a href="https://play.google.com/store/apps/details?id=com.maisu.user" target="_blank">
                                    <img class="app-store padding-3" src="{{ asset('assets/front/img/get-it-on-google-play.png') }}">
                                </a>
                                <a href="https://apps.apple.com/jp/app/maisuapp/id6453467342" target="_blank">
                                    <img class="app-store" src="{{ asset('assets/front/img/get-it-on-app-store.png') }}">
                                </a>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="features-row">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <img class="advanced-feature-img-left wow fadeInLeft mockup-image"
                             src="{{ asset('assets/front/img/provider_mockup_3.png') }}" alt="">
                        <div class="wow fadeInRight text-justify">
                            <h1>
                                <center>Provider App</center>
                            </h1>
                            <p>Want to make more money with your skill and professional services? The MaisuApp is here
                                to boost your earnings at your convenient time. With our trusted platform, you can offer
                                and get online service requests like a tutor, beautician, car wash, electrician, home
                                cleaning, laundry, pest control, gardening, pet care, baby care, plumber and more.</p>
                            <p>You can log in to the app through a social account or with basic details. After login,
                                you must have additional services & packages which you want to offer and complete the
                                profile information. You can manage service requests with a single tap by the
                                accepting/reject option. You can view the earnings report with all details. Also, you
                                can manage profile information, feedback and other details.</p>
                            <center>
                                <a href="https://play.google.com/store/apps/details?id=com.maisu.provider" target="_blank">
                                    <img class="app-store padding-3" src="{{ asset('assets/front/img/get-it-on-google-play.png') }}">
                                </a>
                                <a href="https://apps.apple.com/jp/app/maisuapp/id6453467342" target="_blank">
                                    <img class="app-store" src="{{ asset('assets/front/img/get-it-on-app-store.png') }}">
                                </a>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--        <div class="features-row section-bg">--}}
        {{--            <div class="container">--}}
        {{--                <div class="row">--}}
        {{--                    <div class="col-12">--}}
        {{--                        <img class="advanced-feature-img-right wow fadeInRight mockup-image"--}}
        {{--                             src="{{ asset('assets/front/img/provider_mockup.png?v=0.1') }}" alt="">--}}
        {{--                        <div class="wow fadeInLeft text-justify">--}}
        {{--                            <h1>--}}
        {{--                                <center>myService App - For Service Providers</center>--}}
        {{--                            </h1>--}}
        {{--                            <p>Through myService App, service providers can boost earnings and reach potential clients--}}
        {{--                                by listing their on-demand services.Using our trusted platform, you can access more than--}}
        {{--                                30+ services such as concierge service, photographer, dog walking, home cleaning, AC--}}
        {{--                                repair, beauty, tutors, kids, laundry, electrician, plumbers, massage, and more</p>--}}
        {{--                            <p>All service providers are vetted to ensure that our platform only consists of a pool--}}
        {{--                                filled with trusted and talented service providers.</p>--}}
        {{--                            <p>Join myCheckout community. Download and register as a service provider.</p>--}}
        {{--                            <center>--}}
        {{--                                <a href="{{ isset($general_settings)? (isset($general_settings->provider_playstore_link) && $general_settings->provider_playstore_link != Null)? $general_settings->provider_playstore_link : "" : "" }}"--}}
        {{--                                   target="_blank"><img class="app-store padding-3"--}}
        {{--                                                        src="{{ asset('assets/front/img/get-it-on-google-play.png') }}"></a>--}}
        {{--                                <a href="{{ isset($general_settings)? (isset($general_settings->provider_appstore_link) && $general_settings->provider_appstore_link != Null)? $general_settings->provider_appstore_link : "" : "" }}"--}}
        {{--                                   target="_blank"><img class="app-store"--}}
        {{--                                                        src="{{ asset('assets/front/img/get-it-on-app-store.png') }}"></a>--}}
        {{--                            </center>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
    </section><!-- #advanced-features -->

    <!--========================== Call To Action Section ============================-->
    <section id="call-to-action">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 text-center text-lg-left">
                    <h3 class="cta-title">Call To Action</h3>
                    <p class="cta-text"> Whether you have a question or query about our services feel free to contact
                        us!</p>
                </div>
                <div class="col-lg-3 cta-btn-container text-center">
                    <a class="cta-btn align-middle" href="#contact">Call To Action</a>
                </div>
            </div>
        </div>
    </section><!-- #call-to-action -->

    <!--========================== More Features Section ============================-->
    <section id="more-features">
        <div class="container">
            <div class="section-header">
                <h3 class="section-title">More Features</h3>
                <span class="section-divider"></span>
                <p class="section-description"></p>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="box wow fadeInLeft">
                        <div class="icon">
                            <img src="{{ asset('assets/front/img/60_60/social_login.png') }}"
                                 style="margin-top: 10px">
                        </div>
                        <h4 class="title"><a href="">Social Login</a></h4>
                        <p class="description">App users have the option to login through their social accounts sites
                            like Facebook, Google or Gmail.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="box wow fadeInRight">
                        <div class="icon">
                            <img src="{{ asset('assets/front/img/60_60/live_chat_2.png') }}"
                                 style="margin-top: 10px">
                        </div>
                        <h4 class="title"><a href="">Live Chat</a></h4>
                        <p class="description">After accepting service requests, the user & service provider have the
                            option to chat with each other within the app.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="box wow fadeInLeft">
                        <div class="icon">
                            <img src="{{ asset('assets/front/img/60_60/earning_report_2.png') }}"
                                 style="margin-top: 20px">
                        </div>
                        <h4 class="title"><a href="">Earning Report</a></h4>
                        <p class="description">A service provider can see earning reports with all details of completed,
                            cancel, running and pending requests.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="box wow fadeInRight">
                        <div class="icon">
                            <img src="{{ asset('assets/front/img/60_60/promo_code.png') }}"
                                 style="margin-top: 20px">
                        </div>
                        <h4 class="title"><a href="">Promo Code</a></h4>
                        <p class="description">Users can use promo codes to get a discount on their services request.
                            Promo code offer by admin.</p>
                    </div>
                </div>
{{--                <div class="col-lg-6">--}}
{{--                    <div class="box wow fadeInRight">--}}
{{--                        <div class="icon">--}}
{{--                            <img src="{{ asset('assets/front/img/60_60/Promo_codeOLD.png') }}"--}}
{{--                                 style="margin-top: 20px">--}}
{{--                        </div>--}}
{{--                        <h4 class="title"><a href="">Promo code</a></h4>--}}
{{--                        <p class="description">A user uses a promo code and get a discount on the total amount in all--}}
{{--                            available services.</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-lg-6">--}}
{{--                    <div class="box wow fadeInRight">--}}
{{--                        <div class="icon">--}}
{{--                            <img src="{{ asset('assets/front/img/60_60/Gods_View.png') }}"--}}
{{--                                 style="margin-top: 20px">--}}
{{--                        </div>--}}
{{--                        <h4 class="title"><a href="">God's View</a></h4>--}}
{{--                        <p class="description">With the god's view feature, the admin can show all available driver's on--}}
{{--                            the map with thier status.</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
    </section><!-- #more-features -->

    <!--========================== Contact Section ============================-->
    <section id="contact">
        <div class="container">
            <div class="row wow fadeInUp">
                <div class="col-lg-4 col-md-4">
                    <div class="contact-about">
                        <img class="img-fluid"
                             src="{{ asset('assets/front/img/web_icon_2.png') }}"
                             alt="My-Checkout"/>
                        {{--                        <h3>My-Checkout</h3>--}}
                        <p>Want to know more? Feel free to contact us!</p>
                        @if(isset($general_settings))
                            <div class="social-links">
                                <a href="{{ ((isset(request()->get('general_settings')->twitter_link)) && (request()->get('general_settings')->twitter_link == null)) ? "#" : request()->get('general_settings')->twitter_link }}"
                                   class="twitter" target="_blank"><i class="fab fa-twitter"></i></a>
                                <a href="{{ ((isset(request()->get('general_settings')->facebook_link)) && (request()->get('general_settings')->twitter_link == null)) ? "#" : request()->get('general_settings')->twitter_link }}"
                                   class="facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <a href="{{ ((isset(request()->get('general_settings')->instagram_link)) && (request()->get('general_settings')->twitter_link == null)) ? "#" : request()->get('general_settings')->twitter_link }}"
                                   class="instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                                <a href="{{ ((isset(request()->get('general_settings')->linkedin_link)) && (request()->get('general_settings')->twitter_link == null)) ? "#" : request()->get('general_settings')->twitter_link }}"
                                   class="linkedin" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="info">
                        {{--                        @if(isset($general_settings))--}}
                        {{--                            @if($general_settings->address != Null)--}}
                        <div>
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
{{--                            <i class="ion-ios-location-outline"></i>--}}
                            <p style="margin-bottom: 0; padding-bottom: 10px">Tokyo,Japan</p>
                        </div>
                        {{--                            @endif--}}
                        {{--                            @if($general_settings->email != Null)--}}
                        <div>
                            <i class="fa fa-envelope" aria-hidden="true"></i>{{--                            <i class="ion-ios-email-outline"></i>--}}
                            <p style="margin-bottom: 0; padding-bottom: 20px">maisuapp@gmail.com</p>
                        </div>
                        {{--                            @endif--}}
                        {{--                            @if($general_settings->contact_no != Null)--}}
                        <div>
                            <i class="fa fa-phone" aria-hidden="true"></i>
{{--                            <i class="ion-ios-telephone-outline"></i>--}}
                            <p style="margin-bottom: 0; padding-bottom: 20px"></p>
                        </div>
                        {{--                            @endif--}}
                        {{--                        @endif--}}
                    </div>
                </div>
                <div class="col-lg-5 col-md-8">
                    <div class="form">
                        <img src="{{ asset('assets/front/img/map_2.png') }}">
                    </div>
                </div>
            </div>
        </div>
    </section><!-- #contact -->
</main>

<!--========================== Footer ============================-->
<footer id="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-lg-right text-center">
                <div class="row">
                    <div class="col-xl-12 col-sm-12 col-md-12 col-lg-12 text-lg-right text-center">
{{--                        <div class="copyright">--}}
{{--                            <span style="float: left;">--}}
{{--                                <a href="{{ route('get:terms-and-conditions') }}"--}}
{{--                                   target="_blank">Terms and Conditions</a>--}}
{{--                                <a href="{{ route('get:privacy-policy') }}" target="_blank">Privacy-Policy</a>--}}
{{--                                <a href="{{ route('get:disclaimer') }}" target="_blank">Disclaimer</a>--}}
{{--                                <a href="{{ route('get:faq') }}" target="_blank">FAQ</a>--}}
{{--                            </span>--}}
{{--                            --}}{{--                            <span style="float: right">--}}
{{--                            --}}{{--                                @if(isset($general_settings))--}}
{{--                            --}}{{--                                    @if($general_settings->copy_right != Null)--}}
{{--                            --}}{{--                                        {{ $general_settings->copy_right }}--}}
{{--                            --}}{{--                                    @endif--}}
{{--                            --}}{{--                                @endif--}}
{{--                            --}}{{--                            </span>--}}
{{--                        </div>--}}

                        <div class="copyright">
                            <span style="float: right">
{{--                                @if(isset($general_settings))--}}
{{--                                    @if($general_settings->copy_right != Null)--}}
{{--                                        {{ $general_settings->copy_right }}--}}
{{--                                    @endif--}}
{{--                                @endif--}}
                                    Copyright @ 2023 MaisuApp. All Rights Reserved
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</footer><!-- #footer -->

<a href="#" class="back-to-top" style="background-color: #653996"><i class="fas fa-chevron-up"></i></a>

<!-- JavaScript Libraries -->
<script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/front/js/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/front/js/easing.min.js') }}"></script>
<script src="{{ asset('assets/front/js/wow.min.js') }}"></script>
<script src="{{ asset('assets/front/js/hoverIntent.js') }}"></script>
<script src="{{ asset('assets/front/js/superfish.min.js') }}"></script>
<script src="{{ asset('assets/front/js/magnific-popup.min.js') }}"></script>

<!-- Contact Form JavaScript File -->
<script src="{{ asset('assets/front/js/contactform.js') }}"></script>

<!-- Template Main Javascript File -->
<script src="{{ asset('assets/front/js/main.js') }}"></script>

</body>
</html>
