<!-- [ navigation menu ] start -->
<nav class="pcoded-navbar" id="render-navbar">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="@if(Request::segment(2) === 'dashboard') active pcoded-trigger @endif">
                    <a href="{{ route('get:admin:dashboard') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Dashboard</span>
                    </a>
                </li>
                {{--<li class="">--}}
                {{--<a href="{{ route('get:admin:service_category_list') }}" class="waves-effect waves-dark">--}}
                {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
                {{--<span class="pcoded-mtext">Services</span>--}}
                {{--</a>--}}
                {{--</li>--}}
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:sub_admin_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Sub Admin</span>
                    </a>
                </li>
            </ul>

            {{--<div class="pcoded-navigation-label">Transport Service</div>--}}
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:transport:transport_service_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Transport</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:transport:vehicle_type') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Vehicle Type</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

            {{--<div class="pcoded-navigation-label">Store Service</div>--}}
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:transport_service_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Transport Services</span>
                    </a>
                </li>
            </ul>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:store_service_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Delivery Services</span>
                    </a>
                </li>
            </ul>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:other_service_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        {{--<span class="pcoded-mtext">On-Demand Services</span>--}}
                        <span class="pcoded-mtext">Provider Services</span>
                    </a>
                </li>
            </ul>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:coupon_deals_service_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Coupon Deals</span>
                    </a>
                </li>
            </ul>
            {{--<div class="pcoded-navigation-label">Customers</div>--}}
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:user_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Customers</span>
                    </a>
                </li>

            </ul>
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:admin:provider_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Providers</span>--}}
            {{--</a>--}}
            {{--</li>--}}

            {{--</ul>--}}
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Providers</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="{{ route('get:admin:delivery_person_list') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Delivery Person List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:admin:provider_list',["transport"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Transprort Providers List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:admin:provider_list',["store"]) }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Store Providers List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:admin:provider_list',["provider-services"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Other Service Providers List</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:un_register_provider_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Un Register Provider List</span>
                    </a>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="@if(Request::segment(2) === 'push-notification') active pcoded-trigger @endif">
                    <a href="{{ route('get:admin:push_notification') }}" class="waves-effect waves-dark render_link">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Push Notification</span>
                    </a>
                </li>
            </ul>


            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:world_currency_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">World Currency</span>
                    </a>
                </li>
            </ul>

            {{--<div class="pcoded-navigation-label">General Settings</div>--}}
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:general_setting') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Site Settings</span>
                    </a>
                </li>
            </ul>
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="@if(Request::segment(2) === 'world-country-list') active pcoded-trigger @endif">--}}
            {{--<a href="{{ route('get:admin:world_country_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">World Country</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

            <ul class="pcoded-item pcoded-left-item">
                <li class="@if(Request::segment(2) === 'support-page-list') active pcoded-trigger @endif">
                    <a href="{{ route('get:admin:support_pages') }}" class="waves-effect waves-dark render_link">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Page List</span>
                    </a>
                </li>
            </ul>

            {{--            <ul class="pcoded-item pcoded-left-item">--}}
            {{--                <li class="pcoded-hasmenu">--}}
            {{--                    <a href="javascript:void(0)" class="waves-effect waves-dark">--}}
            {{--                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--                        <span class="pcoded-mtext">Pages</span>--}}
            {{--                    </a>--}}
            {{--                    <ul class="pcoded-submenu">--}}
            {{--                        <li class="">--}}
            {{--                            <a href="{{ route('get:admin:about-us') }}"--}}
            {{--                               class="waves-effect waves-dark">--}}
            {{--                                <span class="pcoded-mtext">About Us</span>--}}
            {{--                            </a>--}}
            {{--                        </li>--}}
            {{--                        <li class="">--}}
            {{--                            <a href="{{ route('get:admin:faq') }}"--}}
            {{--                               class="waves-effect waves-dark">--}}
            {{--                                <span class="pcoded-mtext">Faq</span>--}}
            {{--                            </a>--}}
            {{--                        </li>--}}
            {{--                        <li class="">--}}
            {{--                            <a href="{{ route('get:admin:contact-us') }}"--}}
            {{--                               class="waves-effect waves-dark">--}}
            {{--                                <span class="pcoded-mtext">Contact Us</span>--}}
            {{--                            </a>--}}
            {{--                        </li>--}}
            {{--                    </ul>--}}
            {{--                </li>--}}
            {{--            </ul>--}}

            {{--<div class="pcoded-navigation-label">Required Documents</div>--}}
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:admin:required_documents_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Required Documents</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

            {{--<div class="pcoded-navigation-label">Other Services</div>--}}
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:admin:other_service_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Service List</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:admin:other_service_sub_category_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Service Sub Category</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->
