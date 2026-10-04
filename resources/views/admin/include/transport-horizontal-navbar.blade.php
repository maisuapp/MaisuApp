<style>
    #menu, #menu ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    #menu {
        width: 100%;
        border: 1px solid #fefefe;
        background: #ffffff;
        box-shadow: 0 2px 2px #ccc;
    }
    #menu:before, #menu:after {
        content: "";
        display: block;
    }
    #menu:after {
        clear: both;
    }
    #menu li {
        float: left;
        position: relative;
    }
    #menu a {
        float: left;
        padding: 12px 30px;
        color: #666;
        text-decoration: none;
    }
    #menu a i {
        width: 20px;
        font-size: 15px;
    }
    #menu a:hover {
        color: #2ed8b6;
        cursor: pointer;
    }
    #menu ul {
        margin: 20px 0 0 0;
        opacity: 0;
        visibility: hidden;
        position: absolute;
        top: 40px;
        left: 0;
        z-index: 99;
        background: #ffffff;
        box-shadow: 0 2px 10px #ccc;
        border-radius: 3px;
        transition: all .2s ease-in-out;
    }
    #menu li:hover > ul {
        opacity: 1;
        visibility: visible;
        margin: 0;
    }
    #menu ul a i {
        width: 17px;
        font-size: 15px;
    }
    #menu ul ul {
        top: 0;
        position: absolute;
        left: 100%;
        margin: 0 0 0 20px;
        box-shadow: 0 2px 10px #ccc;
    }
    #menu ul a {
        padding: 10px;
        min-width: 200px;
        display: block;
        white-space: nowrap;
        float: none;
        text-transform: none;
    }
    #menu ul li a:hover i {
        color: #2ed8b6;
        position: relative;
        transition: 0.5s;
        transform: translateX(8px);
    }
    #menu ul li {
        display: block;
        position: relative;
    }
    #menu ul li:first-child > a:after {
        content: '';
        position: absolute;
        left: 40px;
        top: -6px;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #26354494;
    }
    #menu ul ul li:first-child > a:after {
        left: -12px;
        top: 50%;
        margin-top: -6px;
        border-top: 6px solid transparent;
        border-right: 6px solid #26354494;
        border-bottom: 6px solid transparent;
    }
    #menu-trigger {
        display: none;
    }
    #menu-wrap {
        width: 100%;
        display: block;
        z-index: 1;
    }
    .page-header.card {
        margin: 35px 35px 10px;
    }
    @media (max-width: 700px) {
        #menu-wrap * {
            box-sizing: border-box;
        }
        #menu-trigger {
            display: block;
            height: 40px;
            line-height: 40px;
            cursor: pointer;
            padding: 0 0 0 35px;
            border: 1px solid #fff;
            color: #666;
            font-weight: bold;
            background-color: #fefefe;
            box-shadow: 0 2px 2px #ccc;
            border-radius: 6px;
        }
        #menu {
            margin: 0;
            padding: 10px;
            position: absolute;
            top: 40px;
            width: 100%;
            z-index: 1;
            display: none;
            box-shadow: none;
        }
        #menu:after {
            content: '';
            position: absolute;
            left: 25px;
            top: -8px;
        }
        #menu ul {
            width: 92%;
            margin: 20px 0 0 0;
            opacity: 0;
            visibility: hidden;
            position: absolute;
            top: 38px;
            left: 4%;
            z-index: 99;
            background: #fefefe;
            box-shadow: 0 2px 10px #ccc;
            border-radius: 3px;
            transition: all .2s ease-in-out;
        }
        #menu ul ul {
            top: 38px;
            position: absolute;
            left: 0;
            margin: 0 0 0 20px;
            box-shadow: 0 2px 10px #ccc;
        }
        #menu ul ul li:first-child > a:after {
            content: '';
            position: absolute;
            left: 40px;
            top: -6px;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #26354494;
        }
        #menu li, #menu a {
            float: none;
            position: relative;
            display: block;
        }
    }
</style>
<nav id="menu-wrap">
    <ul id="menu">
        @if(isset($service_category) && in_array($service_category->id,[31,32]))
            <li><a class="render_link" href="{{ route('get:admin:rental:transport_service_dashboard',['slug' => $slug]) }}"><i class="fa fa-home"></i> Summary</a></li>
        @else
            <li><a class="render_link" href="{{ route('get:admin:transport_service_dashboard',['slug' => $slug]) }}"><i class="fa fa-home"></i> Summary</a></li>
        @endif
{{--        <li><a><i class="fa fa-user"></i> Drivers</a>--}}
{{--            <ul>--}}
{{--                <li><a class="render_link" href="{{ route('get:admin:add_transport_provider',['slug' => $slug ]) }}"><i class="fa fa-angle-double-right"></i> Add Driver</a></li>--}}
{{--                <li><a class="render_link" href="{{ route('get:admin:transport_service_provider_list',[ "slug" => $slug, "status" => "approved"]) }}"><i class="fa fa-angle-double-right"></i> Approved Drivers</a></li>--}}
{{--                <li><a class="render_link" href="{{ route('get:admin:transport_service_provider_list',[ "slug" => $slug, "status" => "un-approved"]) }}"><i class="fa fa-angle-double-right"></i> Un-Approved Drivers</a></li>--}}
{{--                <li><a class="render_link" href="{{ route('get:admin:transport_service_provider_list',[ "slug" => $slug, "status" => "blocked"]) }}"><i class="fa fa-angle-double-right"></i> Blocked Drivers</a></li>--}}
{{--                <li><a class="render_link" href="{{ route('get:admin:transport_service_provider_list',[ "slug" => $slug, "status" => "rejected"]) }}"><i class="fa fa-angle-double-right"></i> Reject Drivers</a></li>--}}
{{--            </ul>--}}
{{--        </li>--}}
        @if(isset($slug) && $slug == "courier-service")
            <li><a><i class="fa fa-truck"></i> Services</a>
                <ul>
                    <li><a class="render_link" href="{{ route('get:admin:transport_courier_service_list',[ "slug" => $slug, "status" => "all"]) }}"><i class="fa fa-angle-double-right"></i> All Services</a></li>
                    <li><a class="render_link" href="{{ route('get:admin:transport_courier_service_list',[ "slug" => $slug, "status" => "pending"]) }}"><i class="fa fa-angle-double-right"></i> Pending Services</a></li>
                    <li><a class="render_link" href="{{ route('get:admin:transport_courier_service_list',[ "slug" => $slug, "status" => "cancelled"]) }}"><i class="fa fa-angle-double-right"></i> Cancelled Services</a></li>
                </ul>
            </li>
            <li><a class="render_link" href="{{ route('get:admin:transport_provider_ride_review_list',[ 'slug' => $slug]) }}"><i class="fa fa-star"></i> Reviews</a></li>
            <li><a class="render_link" href="{{ route('get:admin:transport_earning_report',[ 'slug' => $slug]) }}"><i class="fa fa-money"></i> Earnings Reports</a></li>
        @else
            <li><a> @if(isset($slug) && $slug == "bike-ride") <i class="fa fa-motorcycle"></i> @else <i class="fa fa-car"></i> @endif Rides</a>
                <ul>
                    @if(isset($service_category) && in_array($service_category->id,[31,32]))
                        <li><a class="render_link" href="{{ route('get:admin:rental:transport_ride_list',[$slug, "all"]) }}"><i class="fa fa-angle-double-right"></i> All Rides</a></li>
<!--                        <li><a class="render_link" href="{{ route('get:admin:rental:transport_ride_list',[$slug, "pending"]) }}"><i class="fa fa-angle-double-right"></i> Pending Rides</a></li>-->
                        <li><a class="render_link" href="{{ route('get:admin:rental:transport_ride_list',[$slug, "approved"]) }}"><i class="fa fa-angle-double-right"></i> Approved Rides</a></li>
                        <li><a class="render_link" href="{{ route('get:admin:rental:transport_ride_list',[$slug, "cancelled"]) }}"><i class="fa fa-angle-double-right"></i> Cancelled Rides</a></li>
                    @else
                        <li><a class="render_link" href="{{ route('get:admin:transport_ride_list',[$slug, "all"]) }}"><i class="fa fa-angle-double-right"></i> All Rides</a></li>
                        <li><a class="render_link" href="{{ route('get:admin:transport_ride_list',[$slug, "scheduled"]) }}"><i class="fa fa-angle-double-right"></i> Scheduled Rides</a></li>
<!--                        <li><a class="render_link" href="{{ route('get:admin:transport_ride_list',[$slug, "pending"]) }}"><i class="fa fa-angle-double-right"></i> Pending Rides</a></li>-->
                        <li><a class="render_link" href="{{ route('get:admin:transport_ride_list',[$slug, "cancelled"]) }}"><i class="fa fa-angle-double-right"></i> Cancelled Rides</a></li>
                    @endif
                </ul>
            </li>
            @if(isset($service_category) && !in_array($service_category->id,[31,32]))
                <li><a class="render_link" href="{{ route('get:admin:transport_manual_ride_booking',['slug' => $slug]) }}"><i class="fa fa-edit"></i> Manual Ride Booking</a></li>
            @endif
            <li><a class="render_link" href="{{ route('get:admin:transport_provider_ride_review_list',['slug' => $slug]) }}"><i class="fa fa-star"></i> Reviews</a></li>
            @if(isset($service_category) && !in_array($service_category->id,[31,32]))
                <li><a class="render_link" href="{{ route('get:admin:transport_earning_report',[$slug]) }}"><i class="fa fa-money"></i> Earnings Reports</a></li>
            @else
                <li><a class="render_link" href="{{ route('get:admin:transport_rental_earning_report',[$slug]) }}"><i class="fa fa-money"></i> Earnings Reports</a></li>
            @endif
        @endif
        <li><a><i class="fa fa-cog"></i> Settings</a>
            <ul>
                <li><a class="render_link" href="{{ route('get:admin:transport_provider_location',[$slug]) }}"><i class="fa fa-angle-double-right"></i> God's View</a></li>
                <li><a class="render_link" href="{{ route('get:admin:transport_vehicle_type',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Vehicle Type</a></li>
                <li><a class="render_link" href="{{ route('get:admin:transport:promocode_list',$slug) }}"><i class="fa fa-angle-double-right"></i> Promocode</a></li>
                <li><a class="render_link" href="{{ route('get:admin:transport_required_document_list',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Required Documents</a></li>
                @if(isset($service_category) && in_array($service_category->id,[4]))
                    <li><a class="render_link" href="{{ route('get:admin:transport_home_page_details',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Home Page Detail</a></li>
                    <li><a class="render_link" href="{{ route('get:admin:transport_weight_list',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Weight List</a></li>
                    <li><a class="render_link" href="{{ route('get:admin:transport_category_list',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Category List</a></li>
                @endif
                <li><a class="render_link" href="{{ route('get:admin:transport_service_setting',[$slug]) }}"><i class="fa fa-angle-double-right"></i> Service Setting</a></li>
            </ul>
        </li>
    </ul>
</nav>
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
    $('#menu-wrap').prepend('<div id="menu-trigger">Menu</div>');
    $('#menu-trigger').on('click', function () {
        $('#menu').slideToggle();
    });
</script>
