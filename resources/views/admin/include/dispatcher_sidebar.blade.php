<!-- [ navigation menu ] start -->
<nav class="pcoded-navbar" id="render-navbar">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>

            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--    <li class="@if(Request::segment(2) === 'dashboard') active pcoded-trigger @endif">--}}
            {{--        <a href="{{ route('get:dispatcher:manual_ride_booking') }}" class="waves-effect waves-dark">--}}
            {{--            <span class="pcoded-micon"><i class="feather icon-home"></i></span>--}}
            {{--                <span class="pcoded-mtext">Dashboard</span>--}}
            {{--        </a>--}}
            {{--    </li>--}}
            {{--</ul>--}}


            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:dispatcher:ride_list') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Scheduled Rides List</span>
                    </a>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Delivery Dispatcher</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @php $service_category_list = \App\model\ServiceCategory::where('category_type',2)->where('status',1)->get() @endphp
                        @foreach($service_category_list as $key => $service_category)
                            <li class="">
                                <a href="{{ route('get:dispatcher:store_service_dispatcher',[$service_category->slug ,"new"]) }}"
                                   class="waves-effect waves-dark">
                                    <span class="pcoded-mtext">{{ ucwords(strtolower($service_category->name)) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:dispatcher:transport_provider_location') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">My Bantay</span>
                    </a>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Setting</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="{{ route('get:dispatcher:profile') }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Profile</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:dispatcher:change_password') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Change Password</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->
