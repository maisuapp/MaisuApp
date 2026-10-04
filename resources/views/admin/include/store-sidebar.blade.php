<!-- [ navigation menu ] start -->
<nav class="pcoded-navbar" id="render-navbar">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="@if(Request::segment(2) === 'dashboard') active pcoded-trigger @endif">
                    <a href="{{ route('get:store-admin:dashboard') }}"
                       class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-home" style="font-size: 16px"></i></span>
                        <span class="pcoded-mtext">Dashboard</span>
                    </a>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:store-admin:store_service_dispatcher','new') }}"
                       class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-clipboard-list" style="font-size: 18px"></i></span>
                        <span class="pcoded-mtext">Dispatcher</span>
                    </a>
                </li>
            </ul>

            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:store-admin:edit_store') }}"
                       class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-store"></i></span>
                        <span class="pcoded-mtext">Store Details</span>
                    </a>
                </li>
            </ul>
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a href="{{ route('get:store-admin:store_product_list') }}"
                       class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-store"></i></span>
                        <span class="pcoded-mtext">Product List</span>
                    </a>
                </li>
            </ul>

            {{--<div class="pcoded-navigation-label">Customers</div>--}}
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a
                            href="{{ route('get:store-admin:store_provider_document') }}"
                            class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-id-card"></i></span>
                        <span class="pcoded-mtext">Document</span>
                    </a>
                </li>

            </ul>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fas fa-shopping-basket"></i></span>
                        <span class="pcoded-mtext">Order List</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["pending"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Pending Order List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["approved"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Approved Order List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["rejected"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Rejected Order List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["ongoing"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Ongoing Order List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["completed"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Completed Order List</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('get:store-admin:store_order_list',["cancelled"]) }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Cancelled Order List</span>
                            </a>
                        </li>
                        {{--<li class="">--}}
                        {{--<a href="{{ route('get:admin:provider_list',["provider-services"]) }}" class="waves-effect waves-dark">--}}
                        {{--<span class="pcoded-mtext">Other Service Providers List</span>--}}
                        {{--</a>--}}
                        {{--</li>--}}
                    </ul>
                </li>
            </ul>
            {{--<div class="pcoded-navigation-label">General Settings</div>--}}

            @if(Illuminate\Support\Facades\Auth::guard() == 'store')
                @if(Illuminate\Support\Facades\Auth::guard('store')->user()->login_type == "email" )
                    <ul class="pcoded-item pcoded-left-item">
                        <li class="">
                            <a href="{{ route('get:store-admin:change_password') }}"
                               class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fas fa-key"></i></span>
                                <span class="pcoded-mtext">Change Password</span>
                            </a>
                        </li>
                    </ul>
                @endif
            @endif
            <ul class="pcoded-item pcoded-left-item">
                <li class="">
                    <a
                            href="{{ route('get:store-admin:store_earning_report') }}"
                            class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="far fa-money-bill-alt"></i></span>
                        <span class="pcoded-mtext">Earning Report</span>
                    </a>
                </li>
            </ul>
            {{--<ul class="pcoded-item pcoded-left-item">--}}
            {{--<li class="">--}}
            {{--<a href="{{ route('store:logout',[ 'store' ]) }}"--}}
            {{--class="waves-effect waves-dark">--}}
            {{--<span class="pcoded-micon"><i class="fas fa-sign-out-alt"></i></span>--}}
            {{--<span class="pcoded-mtext">Logout</span>--}}
            {{--</a>--}}
            {{--</li>--}}
            {{--</ul>--}}

        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->
