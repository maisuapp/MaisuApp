<div class="card ">
    <div class="card-header font-weight-bold">
        Coupon Lists
    </div>
    <div class="card-body cartMainPart cardScrollHeight pt-2" id="style-3">
        @php
            $total_all_coupon_total = 0;
        @endphp
        @if(isset($get_user_coupon_cart) && count($get_user_coupon_cart))
            @foreach($get_user_coupon_cart as $singel_coupon_cart)
                @php
                        $total_coupon_total = 0;
                        $total_coupon_total         =  ($singel_coupon_cart->coupon_qty * $singel_coupon_cart->coupon_amount );
                        $total_all_coupon_total     =  $total_all_coupon_total +$total_coupon_total ;
                @endphp
                <div class="singelCouponList pb-2 pt-2 border-bottom-default">
                    <div class="row">
                        <div class="col-9">
                            <a href="{{ route('get:coupon-details',[$singel_coupon_cart->coupon_id]) }}" class="couponDetailLink" target="_new">
                                <div class="card-title offertitle mb-1"> {{ isset($singel_coupon_cart->coupon_name)?ucwords($singel_coupon_cart->coupon_name):"" }}</div>
                            </a>
                        </div>
                        <div class="col-3 ">
                            <button class="btn pull-right btn-circle btn-circle-sm m-1 btnHeigthWidth removeCouponCard" data-couponId="{{$singel_coupon_cart->coupon_id}}" title="Delete"><i class="fa fa-close"></i></button>
                        </div>
                    </div>
                    <p class="card-text offerstorename mb-2">{{ isset($singel_coupon_cart->coupon_brand_name)?$singel_coupon_cart->coupon_brand_name:"" }}</p>
                    <div class="row">
                        <div class="col-sm-5">

                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="add" id="{{$singel_coupon_cart->coupon_id}}"><i class="fa fa-plus"></i></button>
                            <label class="{{$singel_coupon_cart->coupon_id}}_totalQty">{{ isset($singel_coupon_cart->coupon_qty)?$singel_coupon_cart->coupon_qty:0 }}</label>
                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="minus" id="{{$singel_coupon_cart->coupon_id}}"><i class="fa fa-minus"></i></button>

                        </div>
                        <div class="col-sm-7 float-right">
                            <strong class="price pull-right product_price singleCouponPrice currency">{{ round($total_coupon_total,2) }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="singelCouponList pb-2 border-bottom-default emptycouponCart text-center mx-auto p-5 font-weight-bold">
                Empty Coupon Cart
            </div>
        @endif
    </div>

    @if($total_all_coupon_total > 0)
        <div class="card-footer text-muted text-center">
            <a href="javascript:void(0)" class="btn btn-primary btn-block btnColor btncouponcart " data-toggle="modal" data-target="#paymentModalPopup">Proceed To Pay <span class="currency">{{ round($total_all_coupon_total,2) }}</span></a>
        </div>
    @endif
</div>
