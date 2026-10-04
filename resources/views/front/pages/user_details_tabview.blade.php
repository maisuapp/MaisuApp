@extends('front.layout.default')
@section('title')
    <title>{{ (isset($title_code) )? $title_code : __('messages.31') }}</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Restaurant">
    <meta name="keywords" content="Restaurant">
@endsection

@section('page-css')
    <style>
        @media screen and (max-width: 1023px) {
            .tab{
                display: none;
            }

        }
        button:hover{
            cursor: pointer;
        }
    </style>
@endsection

@section('page-content')

    <div class="container-fluid innerPages">
        <div class="container removePadding minHeight p-4" >
                <div class="row" >

                    <div class="tab  col-lg-3 col-md-10 col-sm-12 col-xs-12 p-0">
                        <ul class="sidebar-navigation">
                            <li class=""></li>
                            <li>

                                <a class="tablinks" href="{{ route('get:user_profile') }}" id="edit_profile" title="Edit Profile">
                                    <i class="fa fa-user" aria-hidden="true"></i> Profile
                                </a>
                            </li>

                            @if(Illuminate\Support\Facades\Auth::guard("user")->check() && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "facebook" && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "google")
                                <li>
                                    <a class="tablinks" href="{{ route('get:user_change_password') }}" id="change_password" title="Change Password">
                                        <i class="fa fa-pencil-alt" aria-hidden="true"></i> Change Password
                                    </a>
                                </li>
                            @endif

<!--                            <li>
                                <a class="tablinks" href="{{ route('get:user_orders') }}" id="orders" title="Delivery Orders">
                                    <i class="fa fa-list" aria-hidden="true"></i> Delivery Orders
                                </a>
                            </li>-->

                            <li>
                                <a class="tablinks" href="{{ route('get:user_orders_all',["transport","all"]) }}" id="ride_orders_all" title="All Orders">
                                    <i class="fa fa-shopping-basket" aria-hidden="true"></i> All Orders
                                </a>
                            </li>
<!--                            <li>
                                <a class="tablinks" href="{{ route('get:user_offers') }}" id="offers" title="Offers">
                                    <i class="fa fa-cog" aria-hidden="true"></i> Offers
                                </a>
                            </li>-->
                            <li>
                                <a class="tablinks" href="{{ route('get:user_wallet') }}" id="wallet" title="wallet">
                                    <i class="fa fa-wallet" aria-hidden="true"></i> Manage Wallet
                                </a>
                            </li>
                            <li>
                                <a class="tablinks" href="{{ route('get:user_payments') }}" id="payments" title="Manage Cards">
                                    <i class="fa fa-credit-card" aria-hidden="true"></i> Manage Cards
                                </a>
                            </li>
                            <li>
                                <a class="tablinks" href="{{ route('get:user_address') }}" id="addresses" title="Addresses">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i> Addresses
                                </a>
                            </li>
                            <li>
                                <a class="tablinks" href="{{ route('get:my_coupons') }}" id="my-coupons" title="My Coupons">
                                    <i class="fa fa-tag " aria-hidden="true"></i> My Coupons
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class=" col-lg-9  col-md-10 col-sm-12 col-xs-12 pt-4 pb-4  pr-0" >
                        @if($user_open_tab == "edit_profile")
                            @include('front.pages.user_details_tabview.edit_user_details')
                        @elseif($user_open_tab == "change_password")
                            @include('front.pages.user_details_tabview.user_change_password')
                       {{-- @elseif($user_open_tab == "orders")
                            @include('front.pages.user_details_tabview.user_orders')--}}
                        @elseif($user_open_tab == "ride_orders_all")
                            @include('front.pages.user_details_tabview.user_orders_all')
                        @elseif($user_open_tab == "offers")
                            {{--@include('front.pages.user_details_tabview.user_offers')--}}
                        @elseif($user_open_tab == "payments")
                            @include('front.pages.user_details_tabview.user_payments')
                        @elseif($user_open_tab == "wallet")
                            @include('front.pages.user_details_tabview.user_wallets')
                        @elseif($user_open_tab == "addresses")
                            @include('front.pages.user_details_tabview.user_address_list')
                        @elseif($user_open_tab == "my-coupons")
                            @include('front.pages.user_details_tabview.user_coupons')
                        @elseif($user_open_tab == "my-coupon-details")
                            @include('front.pages.user_details_tabview.user_coupon_details')
                        @elseif($user_open_tab == "order-detail")
                            @include('front.pages.user_details_tabview.user_order_detail')
                        @elseif($user_open_tab == "ride-detail")
                            @include('front.pages.user_details_tabview.user_ride_detail')
                        @elseif($user_open_tab == "courier-detail")
                            @include('front.pages.user_details_tabview.user_courier_detail')
                        @elseif($user_open_tab == "provider-detail")
                            @include('front.pages.user_details_tabview.user_provider_detail')
                        @else
                        @endif
                    </div>

            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script>
        $(document).ready(function () {
            var id = "{{ $user_open_tab }}";
            $('.tablinks').removeClass("active");
            $('#' + id).addClass("active");
        });
    </script>
@endsection

