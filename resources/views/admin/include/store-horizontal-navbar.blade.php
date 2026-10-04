<style>
    /*.pcoded .pcoded-header{*/
    /*box-shadow: none;*/
    /*}*/
    #menu, #menu ul {
        margin: 0px;
        padding: 0px;
        list-style: none;
    }

    #menu {
        width: 100%;
        /* margin: 60px auto; */
        border: 1px solid #fefefe;
        /*background: #fefefe;*/
        background: #ffffff;
        /* background: linear-gradient(#1ebdbe, #107677); */
        /*border-radius: 6px;*/
        box-shadow: 0px 2px 2px #ccc;
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
        /*background: url(../images/sep.png) no-repeat;*/
        position: relative;
    }

    #menu a {
        float: left;
        padding: 12px 30px;
        color: #666;
        text-decoration: none;
        /* text-transform: uppercase; */
        /* font: bold 12px Arial, Helvetica; */
    }

    #menu a i {
        width: 20px;
        font-size: 15px;
    }

    #menu a:hover {
        color: #42a5f5;
        cursor: pointer;
    }

    #menu ul {
        margin: 20px 0px 0px 0px;
        opacity: 0;
        visibility: hidden;
        position: absolute;
        top: 40px;
        left: 0px;
        z-index: 99;
        /*background: #fefefe;*/
        background: #ffffff;
        /*background: linear-gradient(#1ebdbe, #107677);*/
        /*box-shadow: 0px -1px 0px rgba(255, 255, 255, .3);*/
        box-shadow: 0px 2px 10px #ccc;
        border-radius: 3px;
        transition: all .2s ease-in-out;
    }

    #menu li:hover > ul {
        opacity: 1;
        visibility: visible;
        margin: 0px;
    }

    #menu ul a i {
        width: 17px;
        font-size: 15px;
    }

    #menu ul ul {
        top: 0px;
        /*left: 150px;*/
        position: absolute;
        left: 100%;
        margin: 0px 0px 0px 20px;
        box-shadow: 0px 2px 10px #ccc;
        /*box-shadow: 0px -1px 0px rgba(255, 255, 255, .3);*/
    }

    #menu ul a {
        padding: 10px;
        /*width: 130px;*/
        min-width: 200px;
        display: block;
        white-space: nowrap;
        float: none;
        text-transform: none;
    }

    #menu ul li a:hover i {
        /*background: linear-gradient(#1ebdbe, #107677);*/
        color: #42a5f5;
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
        /*border-bottom: 6px solid #1ebdbe;*/
    }

    #menu ul ul li:first-child > a:after {
        left: -12px;
        top: 50%;
        margin-top: -6px;
        border-top: 6px solid transparent;
        border-right: 6px solid #26354494;
        /*border-right: 6px solid #107677;*/
        border-bottom: 6px solid transparent;
    }

    #menu-trigger { /* hide initially */
        display: none;
    }

    #menu-wrap {
        /*position: fixed;*/
        width: 100%;
        display: block;
        z-index: 1;
    }

    .page-header.card {
        /*margin: 70px 35px 15px;*/
        margin: 30px 35px 15px;
    }

    @media (max-width: 700px) {

        /*#menu-wrap {*/
        /*position: relative;*/
        /*}*/
        #menu-wrap * {
            box-sizing: border-box;
        }

        #menu-trigger {
            display: block;
            height: 40px;
            line-height: 40px;
            cursor: pointer;
            padding: 0px 0px 0px 35px;
            border: 1px solid #fff;
            color: #666;
            font-weight: bold;
            background-color: #fefefe;
            box-shadow: 0px 2px 2px #ccc;
            border-radius: 6px;
        }

        #menu {
            margin: 0px;
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
            margin: 20px 0px 0px 0px;
            opacity: 0;
            visibility: hidden;
            position: absolute;
            top: 38px;
            left: 4%;
            z-index: 99;
            background: #fefefe;
            /*background: linear-gradient(#1ebdbe, #107677);*/
            /*box-shadow: 0px -1px 0px rgba(255, 255, 255, .3);*/
            box-shadow: 0px 2px 10px #ccc;
            border-radius: 3px;
            transition: all .2s ease-in-out;
        }

        #menu ul ul {
            top: 38px;
            /*left: 150px;*/
            position: absolute;
            left: 0;
            margin: 0px 0px 0px 20px;
            box-shadow: 0px 2px 10px #ccc;
            /*box-shadow: 0px -1px 0px rgba(255, 255, 255, .3);*/
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
            font-size: 15px !important;
        }

    }
</style>
<nav id="menu-wrap">
    <ul id="menu">
        <li><a class="render_link" href="{{ route('get:admin:store_service_dashboard',$slug) }}"><i
                        class="fa fa-home"></i>Summary</a></li>
        <li><a class="render_link" href="{{ route('get:admin:store_service_dispatcher',[$slug,"new"]) }}"><i
                        class="fas fa-list-alt"></i>Dispatcher </a></li>
        <li><a class="render_link" href="{{ route('get:admin:store_category_list',$slug) }}"><i
                        class="fas fa-list-alt"></i>Category </a></li>
        <li>
            <a><i class="fas fa-store"></i> Stores</a>
            <ul>
                <li><a
                            {{--class="render_link"--}}
                            href="{{ route('get:admin:add_store',[$slug]) }}"><i class="fa fa-angle-double-right"></i>
                        Add
                        Store</a></li>
                <li><a class="render_link"
                       href="{{ route('get:admin:store_list',[$slug,"approved"]) }}"><i
                                class="fa fa-angle-double-right"></i> Store List</a></li>
                <li><a class="render_link"
                       href="{{ route('get:admin:store_list',[$slug,"un-approved"]) }}"><i
                                class="fa fa-angle-double-right"></i> Un-Approved Store</a></li>
                <li><a class="render_link"
                       href="{{ route('get:admin:store_list',[$slug,"blocked"]) }}"><i
                                class="fa fa-angle-double-right"></i> Blocked Store</a></li>
                <li><a class="render_link"
                       href="{{ route('get:admin:store_list',[$slug,"rejected"]) }}"><i
                                class="fa fa-angle-double-right"></i> Rejected Store</a></li>
            </ul>
        </li>
        <li><a><i class="fas fa-shopping-bag"></i>Orders</a>
            <ul>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"all"]) }}"><i
                                class="fa fa-angle-double-right"></i> All Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"pending"]) }}"><i
                                class="fa fa-angle-double-right"></i> Pending Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"approved"]) }}"><i
                                class="fa fa-angle-double-right"></i> Approved Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"rejected"]) }}"><i
                                class="fa fa-angle-double-right"></i> Rejected Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"ongoing"]) }}"><i
                                class="fa fa-angle-double-right"></i> Ongoing Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"completed"]) }}"><i
                                class="fa fa-angle-double-right"></i> Completed Orders</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_order_list',[$slug,"cancelled"]) }}"><i
                                class="fa fa-angle-double-right"></i> Cancelled Orders</a></li>
            </ul>
        </li>
        <li><a class="render_link" href="{{ route('get:admin:store_earning_report',$slug) }}"><i
                        class="fas fa-list-alt"></i>Earnings Report </a></li>
        <li><a><i class="fa fa-cog"></i> Settings</a>
            <ul>
                <li><a class="render_link" href="{{ route('get:admin:store_required_document_list',$slug) }}"><i
                                class="fa fa-angle-double-right"></i> Required Documents</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store:promocode_list',$slug) }}"><i class="fa fa-angle-double-right"></i> Promocode</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_service_setting',$slug) }}"><i class="fa fa-angle-double-right"></i> Service Settings</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_service_slider_list',$slug) }}"><i class="fa fa-angle-double-right"></i> Service Slider</a></li>
                <li><a class="render_link" href="{{ route('get:admin:store_delivery_charges',$slug) }}"><i class="fa fa-angle-double-right"></i> Delivery Charges </a></li>
{{--                <li><a class="render_link" href="{{ route('get:admin:store_surcharge_setting',$slug) }}"><i class="fa fa-angle-double-right"></i> Surcharges Settings </a></li>--}}
                <li><a class="render_link" href="{{ route('get:admin:add_users',['slug' => $slug]) }}"><i class="fa fa-angle-double-right"></i> Place new order </a></li>
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
