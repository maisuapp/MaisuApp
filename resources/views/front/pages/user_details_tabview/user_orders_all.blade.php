<div class="card">
    <div class="card-header d-flex ">
        <div class="col-8 p-0">
            <h5 >Order Histrory</h5>
        </div>
        <div class="col-4 p-0">
            <select class="custom-select custom-select-sm filterType">
                <option value="all" {{ (isset($filter_type) && $filter_type =="all")?"selected":"" }}>All</option>
                <option value="today" {{ (isset($filter_type) && $filter_type =="today")?"selected":"" }}>Today</option>
                <option value="lastweek" {{ (isset($filter_type) && $filter_type =="lastweek")?"selected":"" }}>Last 7 Days</option>
                <option value="lastmonth" {{ (isset($filter_type) && $filter_type =="lastmonth")?"selected":"" }}>Last 30 Days</option>
                <option value="lastyear" {{ (isset($filter_type) && $filter_type =="lastyear")?"selected":"" }}>Last Year</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="row pb-3">
                <div class="col ">
                    <a href="{{ route('get:user_orders_all',["transport","all"]) }}" class="service_type " title="Tranport Service">
                        <label class="btn btn-outline-success btn-block {{ (isset($service_type) && $service_type == "transport" )?"active":""  }}" for="success-outlined">Transport Service</label>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('get:user_orders_all',["delivery","all"]) }}" class="service_type " title="Delivery Service">
                        <label class="btn btn-outline-success btn-block {{ (isset($service_type) && $service_type == "delivery" )?"active":""  }}" for="success-outlined1">Delivery Service</label>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('get:user_orders_all',["on-demand","all"]) }}" class="service_type " title="On-Demand Service">
                        <label class="btn btn-outline-success btn-block {{ (isset($service_type) && $service_type == "on-demand" )?"active":""  }}" for="success-outlined1">On-Demand Service</label>
                    </a>
                </div>
<!--                <div class="col">
                    <a href="{{ route('get:user_orders_all',["coupon","all"]) }}" class="service_type " title="Coupon Service">
                        <label class="btn btn-outline-success btn-block {{ (isset($service_type) && $service_type == "coupon" )?"active":""  }}" for="success-outlined1">Coupon Deals</label>
                    </a>
                </div>-->
        </div>

        <div class="orderHistoryMain">

            @if(isset($get_orders_data) && !empty($get_orders_data))

                @if(count($get_orders_data) > 0)
                    @foreach($get_orders_data  as $singel_order)
                    <div class="orderHistory mb-3" >
                        <div class="statusmainCls {{ $service_type }}">
                            <?php
                            if ($singel_order['status'] == 1 ) {
                                $bg_status_class = "pending";
                                $bg_color = "pending";
                                $status_img = "pending.png";
                            } elseif ($singel_order['status'] == 2) {
                                $bg_status_class = "approved";
                                $status_img = "pending.png";
                                $bg_color = "pending";
                            }elseif (((in_array($singel_order['status'], [3]) && $service_type == "delivery") )) {
                                $bg_status_class = "Rejected";
                                $status_img = "cancel.png";
                                $bg_color = "cancelled";
                            }
                             elseif (((in_array($singel_order['status'], [4, 10]) && $service_type != "on-demand") || (in_array($singel_order['status'], [4, 5, 10]) && $service_type == "on-demand")  )) {
                                $bg_status_class = "cancelled";
                                $status_img = "cancel.png";
                                $bg_color = "cancelled";
                            } elseif ( (in_array($singel_order['status'], [5,6,7]) && $service_type != "on-demand") || (in_array($singel_order['status'], [6,7]) && $service_type == "on-demand") ) {
                                $bg_status_class = "processing";
                                $status_img = "pending.png";
                                $bg_color = "pending";
                            } elseif ($singel_order['status'] == 8) {
                                $bg_status_class = "ongoing";
                                $status_img = "pending.png";
                                $bg_color = "pending";
                            } elseif ($singel_order['status'] == 9) {
                                $bg_status_class = "completed";
                                $status_img = "complete.png";
                                $bg_color = "completed";
                            } else {
                                $bg_status_class = "pending";
                                $bg_color = "pending";
                                $status_img = "pending.png";
                            }
                            ?>
                                <div class="status ml-auto pull-right statusClass {{$bg_color}}">
                                    <img class="pl-3 pb-1" src="{{ asset('/assets/front/img/png/'.$status_img)}} "> <span class="pl-4">{{ucfirst($bg_status_class)}}</span>
                                </div>
                        </div>

                        <div class="col-12 d-flex pb-2">
                            <div class="serviceImg">
                                @if(file_exists(public_path('/assets/images/service-category/'.$singel_order['service_category_icon']) ))
                                    <img src="{{ asset('/assets/images/service-category/'.$singel_order['service_category_icon']) }}" width="20" title="{{ $singel_order['service_category_name'] }}">
                                @else
                                    <img src="{{ asset('/assets/front/img/png/store.png') }}" width="20" title="{{ $singel_order['service_category_name'] }}">
                                @endif
                            </div>
                            <div class="pl-2 paddingtop-2">
                                <span class="serviceTitle"> {{ $singel_order['service_category_name'] }}</span>
                            </div>

                        </div>

                        @if($service_type == "delivery")
                            <div class="col-12 d-flex pb-2">
                                <div class="serviceImg">
                                    <img src="{{ asset('/assets/front/img/png/store.png') }}" width="20">
                                </div>
                                <div class="pl-2 paddingtop-2">
                                    <span class="storeTitle font-weight-bold"><a href="{{ route('get:restaurant_data',[$singel_order['store_id']]) }}" title=" {{$singel_order['name']}} "> {{$singel_order['name']}} </a></span>
                                </div>
                            </div>
                        @endif

                        <div class="col-12 d-flex row borderBtmCls pb-2 font14">
                            <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12  d-flex pr-0 pb-2">
                                <div class="serviceImg">
                                    <img src="{{ asset('/assets/front/img/png/calander.png') }}" width="20">
                                </div>
                                <div class="pl-2">
                                    {{--{{ ($singel_order['created_at'] != "")?date("D d, M, Y",strtotime($singel_order['created_at'])):"--" }}--}}
                                    {{ isset($singel_order['created_at'])?date("D d, M, Y",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($singel_order['created_at']))):"--" }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12  d-flex pr-0 pb-2">
                                <div class="serviceImg">
                                    <img src="{{ asset('/assets/front/img/png/time.png') }}" width="20">
                                </div>
                                <div class="pl-2">
                                    {{--{{ ($singel_order['created_at'] != "")?date("H:i a",strtotime($singel_order['created_at'])):"--" }}--}}
                                    {{ isset($singel_order['created_at'])?date("h:i a",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($singel_order['created_at']))):"--" }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12  d-flex pr-0 pb-2">
                                <div class="orderIdcls text-center font-weight-bold">
                                    @if($service_type == "delivery")
                                        OrderID : <a href="{{ route('get:store_order_details',[$singel_order['id']]) }}" title="View Details">#{{ ($singel_order['order_no'] != "")?$singel_order['order_no']:"--" }}</a>
                                    @elseif($service_type == "transport")
                                        OrderID : <a href="{{ route('get:ride_details',[$singel_order['id']]) }}" title="View Details">#{{ ($singel_order['order_no'] != "")?$singel_order['order_no']:"--" }}</a>
                                    @elseif($service_type == "on-demand")
                                        OrderID : <a href="{{ route('get:provider_order_details',[$singel_order['id']]) }}" title="View Details">#{{ ($singel_order['order_no'] != "")?$singel_order['order_no']:"--" }}</a>
                                    @elseif($service_type == "coupon")
                                        OrderID : <a href="#" title="View Details">#{{ ($singel_order['order_no'] != "")?$singel_order['order_no']:"--" }}</a>
                                    @endif

                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12  d-flex pr-0 pb-2 justify-content-lg-center ">
                                <div class="priceFont text-center font-weight-bold {{$bg_color}}fontColor">
                                    <span class="currency">{{ ($singel_order['total_cost'])?$singel_order['total_cost']:0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex row pb-2 font14 pt-2">
                            @if($service_type != "on-demand")
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12  d-flex pr-0">
                                <div class="serviceImg pt-1">
                                    <img src="{{ asset('/assets/front/img/png/pickuplocation.png') }}" width="12">
                                </div>
                                <div class="pl-2 pt-1">
                                    {{isset($singel_order['pickup_address'])?$singel_order['pickup_address']:"--"}}
                                </div>
                            </div>
                            @endif

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12  d-flex pr-0">
                                <div class="serviceImg pt-1">
                                    <img src="{{ asset('/assets/front/img/png/destination.png') }}" width="12">
                                </div>
                                <div class="pl-2 pt-1">
                                    {{isset($singel_order['delivery_address'])?$singel_order['delivery_address']:"--"}}
                                </div>
                            </div>

                        </div>
                </div>
                @endforeach
                    <div class="float-right">
                        {{   $get_orders_data->links() }}
                    </div>
                @else
                    <div class="col-12 p-0 text-center">
                        <h5 >No record found</h5>
                    </div>
                @endif

            @endif

        </div>

    </div>
</div>
<script type="text/javascript">

    $(document).ready(function (){
        $(document).on("change",".filterType",function (){
           var filterType =$(this).val();
           {{--urlPath = "{{ route('get:user_orders_all',[$service_type,'+filterType+]) }}";--}}
           var urlPath = '{{ route("get:user_orders_all", [$service_type,":filterType"]) }}';
           urlPath = urlPath.replace(':filterType', filterType);
           window.location.href = urlPath;
        });
    })
</script>
