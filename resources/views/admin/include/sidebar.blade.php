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
            {{--<ul class="pcoded-item pcoded-left-item">
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
                        --}}{{--<span class="pcoded-mtext">On-Demand Services</span>--}}{{--
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
            </ul>--}}
            {{--<div class="pcoded-navigation-label">Customers</div>--}}

            {{--<ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:user_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Customers</span>
                    </a>
                </li>
            </ul>--}}

            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('get:admin:provider_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">Providers</span>--}}
            {{--</a>--}}
            {{--</li>--}}

            {{--</ul>--}}
            {{--<ul class="pcoded-item pcoded-left-item">
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
            </ul>--}}

            {{--<div class="pcoded-navigation-label">General Settings</div>--}}

            {{--<ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:admin:general_setting') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Site Settings</span>
                    </a>
                </li>
            </ul>--}}

            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="@if(Request::segment(2) === 'world-country-list') active pcoded-trigger @endif">--}}
            {{--<a href="{{ route('get:admin:world_country_list') }}" class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--<span class="pcoded-mtext">World Country</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

            {{--<ul class="pcoded-item pcoded-left-item">
                <li class="@if(Request::segment(2) === 'support-page-list') active pcoded-trigger @endif " >
                    <a href="{{ route('get:admin:support_pages') }}" class="waves-effect waves-dark render_link">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Page List</span>
                    </a>
                </li>
            </ul>--}}



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



{{--            <ul class="pcoded-item pcoded-left-item">--}}
{{--                <li class="pcoded-hasmenu">--}}
{{--                    <a href="javascript:void(0)" class="waves-effect waves-dark">--}}
{{--                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
{{--                        <span class="pcoded-mtext">Shopper List</span>--}}
{{--                    </a>--}}
{{--                    <ul class="pcoded-submenu">--}}
{{--                        <li class="">--}}
{{--                            <a href="{{ route('get:admin:add_shopper') }}" class="waves-effect waves-dark">--}}
{{--                                <span class="pcoded-mtext">Add Shopper</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="{{ route('get:admin:shopper_service_list',["approved"]) }}"--}}
{{--                               class="waves-effect waves-dark">--}}
{{--                                <span class="pcoded-mtext">Shopper Providers List</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="{{ route('get:admin:shopper_service_list',["un-approved"]) }}" class="waves-effect waves-dark">--}}
{{--                                <span class="pcoded-mtext">Un-Approved Shopper</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="{{ route('get:admin:shopper_service_list',["blocked"]) }}" class="waves-effect waves-dark">--}}
{{--                                <span class="pcoded-mtext">Blocked Shopper</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="">--}}
{{--                            <a href="{{ route('get:admin:shopper_service_list',["rejected"]) }}" class="waves-effect waves-dark">--}}
{{--                                <span class="pcoded-mtext">Rejected Shopper</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}
{{--            </ul>--}}

{{--            <ul class="pcoded-item pcoded-left-item">--}}
{{--                <li class="">--}}
{{--                    <a href="{{ route('get:admin:shopper_service_list',["approved"]) }}" class="waves-effect waves-dark">--}}
{{--                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
{{--                        <span class="pcoded-mtext">Shopper Services</span>--}}
{{--                    </a>--}}
{{--                </li>--}}
{{--            </ul>--}}
            @foreach($admin_main_menu_list as $single_menu)
                {{--@foreach($single_menu['parent_menu'] as $parent)
                            <h6>{{$parent}}</h6>
                @endforeach--}}
                {{--<h6>{{$single_menu['parent_menu']['name']}}</h6>--}}
                <ul class="pcoded-item pcoded-left-item ">
                    <li class="@if(Request::segment(2) ===$single_menu['parent_menu']['module_name'] ) active pcoded-trigger @endif  {{{(count($single_menu['child_menu']) > 0)?"pcoded-hasmenu":""}}} " >
                        @if(count($single_menu['child_menu']) > 0)
                            <a href="javascript:void(0)" class="waves-effect waves-dark render_link">
                                <span class="pcoded-micon"><i class="fa fa-{{ $single_menu['parent_menu']['image']}} "></i></span>
                                <span class="pcoded-mtext">{{$single_menu['parent_menu']['name']}}</span>
                            </a>
                        @else
                            <a href="{{ route($single_menu['parent_menu']['route_path']) }}" class="waves-effect waves-dark render_link">
                                <span class="pcoded-micon"><i class="fa fa-{{ $single_menu['parent_menu']['image']}} "></i></span>
                                <span class="pcoded-mtext">{{$single_menu['parent_menu']['name']}}</span>
                            </a>
                        @endif
                        {{-- code check submenu--}}
                        @if(count($single_menu['child_menu']) > 0)
                            <ul class="pcoded-submenu">
                                @foreach($single_menu['child_menu'] as $child_menu)
                                    <li class="">
                                        @if(!empty($child_menu["route_path_arr"]))
                                            <a href="{{ route( $child_menu['route_path'],[$child_menu["route_path_arr"]] ) }}" class="waves-effect waves-dark">
                                                <span class="pcoded-mtext">{{$child_menu['name']}} </span>
                                            </a>
                                        @else
                                            <a href="{{ route( $child_menu['route_path']) }}" class="waves-effect waves-dark">
                                                <span class="pcoded-mtext">{{$child_menu['name']}} </span>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        {{-- End code check submenu--}}
                    </li>
                </ul>
            @endforeach
            @if($admin_role == 1)
                <ul class="pcoded-item pcoded-left-item">
                    <li class="">
                        <a href="{{ route('get:admin:sub_admin_list') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="fa fa-users"></i></span>
                            <span class="pcoded-mtext">Sub Admin</span>
                        </a>
                    </li>
                </ul>

                <ul class="pcoded-item pcoded-left-item">
                    <li class="pcoded-hasmenu">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="fa fa-comments"></i></span>
                            <span class="pcoded-mtext">Live Chat <i class="fa fa-circle" style="color: red;position: absolute;top: -2px;right: -13px;font-size: 11px;" id="admin_seen_or_not"></i></span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="">
                                <a href="{{ route('get:admin:chat_user_list',['type_string'=>'user','type'=>0]) }}" class="waves-effect waves-dark">
                                    <span class="pcoded-mtext">Customer Live Chat  &nbsp;<i class="fa fa-circle" style="color: red;position: absolute;top: 3px;right: 29.5%;font-size: 11px;" id="admin_seen_or_not_customer"></i></span>
                                </a>
                            </li>
{{--                            <li class="">--}}
{{--                                <a href="{{ route('get:admin:chat_user_list',['type_string'=>'store','type'=>1]) }}" class="waves-effect waves-dark">--}}
{{--                                    <span class="pcoded-mtext">Store Live Chat  &nbsp;<i class="fa fa-circle" style="color: red;position: absolute;top: 3px;right: 41.5%;font-size: 11px;" id="admin_seen_or_not_store"></i></span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li class="">--}}
{{--                                <a href="{{ route('get:admin:chat_user_list',['type_string'=>'driver','type'=>2]) }}" class="waves-effect waves-dark">--}}
{{--                                    <span class="pcoded-mtext">Driver Live Chat  &nbsp;<i class="fa fa-circle" style="color: red;position: absolute;top: 3px;right: 39.5%;font-size: 11px;" id="admin_seen_or_not_driver"></i></span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
                            <li class="">
                                <a href="{{ route('get:admin:chat_user_list',['type_string'=>'provider','type'=>3]) }}" class="waves-effect waves-dark">
                                    <span class="pcoded-mtext">Provider Live Chat  &nbsp;<i class="fa fa-circle" style="color: red;position: absolute;top: 3px;right: 33.5%;font-size: 11px;" id="admin_seen_or_not_provider"></i></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            @endif
            {{--@foreach($admin_main_menu_list as $single_menu)

                <ul class="pcoded-item pcoded-left-item">
                    <li class="@if(Request::segment(2) === $single_menu->module_name) active pcoded-trigger @endif " >
                        <a href="{{ route($single_menu->route_path) }}" class="waves-effect waves-dark render_link">
                            <span class="pcoded-micon"><i class="feather {{$single_menu->image}} "></i></span>
                            <span class="pcoded-mtext">{{$single_menu->name}}</span>
                        </a>
                    </li>
                </ul>

            @endforeach--}}
        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->

@include('admin.pages.super_admin.chat.script')
