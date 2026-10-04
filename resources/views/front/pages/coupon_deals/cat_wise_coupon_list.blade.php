<div class="row">
    <div class="col-12">
        <div class="cateborderCls pl-0">
            <div class="catetitleCls selectedCategory">
                {{ isset($first_coupon_category_list->category_name)?ucfirst($first_coupon_category_list->category_name):"" }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="col-12">
                    <div class="row pt-3 mx-auto ">
                        @if(isset($first_cat_all_coupon_lists) && count($first_cat_all_coupon_lists) >0)
                            @foreach($first_cat_all_coupon_lists as $singel_coupon)
                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                    @if(file_exists(public_path('assets/images/coupon_images/'.$singel_coupon->coupon_image)))
                                        <img src="{{ asset('/assets/images/coupon_images/'.$singel_coupon->coupon_image)}}" class="card-img-top" >
                                    @else
                                        <img src="{{ asset('assets/images/coupon_images/no-coupon.jpg') }}"  alt="{{ ucfirst($singel_coupon->coupon_name) }}" class="card-img-top" >
                                    @endif

                                    <div class="card-body cardBodyColor">
                                        <a href="{{ route('get:coupon-details',[$singel_coupon->id]) }}" class="couponDetailLink" target="_new" title="{{ isset($singel_coupon->coupon_name)?ucfirst($singel_coupon->coupon_name):"" }}">
                                            <div class="card-title offertitle mb-1">{{ isset($singel_coupon->coupon_name)?ucfirst($singel_coupon->coupon_name):"" }}</div>
                                        </a>
                                        <p class="card-text offerstorename mb-2">{{ isset($singel_coupon->coupon_brand_name)?ucfirst($singel_coupon->coupon_brand_name):"" }}</p>
                                        <div class="row">
                                            <div class="col-sm-7 ">
                                                <strong class="price pull-left product_price currency">{{ (isset($singel_coupon->coupon_amount) && $singel_coupon->coupon_amount > 0  )?ucfirst($singel_coupon->coupon_amount):0 }}</strong><br>
<!--                                                <div class="offerValidCls">Valid for {{ (isset($singel_coupon->coupon_validity) && $singel_coupon->coupon_validity > 1  )?$singel_coupon->coupon_validity." Days":$singel_coupon->coupon_validity." Day" }}</div>-->
                                            </div>
                                            <div class="col-sm-5">
                                                <div class="{{$singel_coupon->id}}_getButton  getButton {{(isset($singel_coupon->coupon_qty) && $singel_coupon->coupon_qty != Null)?"hide":""}}">
                                                    <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right addCouponCart" data-action="add" id="{{$singel_coupon->id}}">GET</button>
                                                </div>
                                                <div class="{{$singel_coupon->id}}_getPlusMinusButton getPlusMinusButton {{(isset($singel_coupon->coupon_qty) && $singel_coupon->coupon_qty != Null)?"":"hide"}}">
                                                    <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="add" id="{{$singel_coupon->id}}"><i class="fa fa-plus"></i></button>
                                                    <label class="{{$singel_coupon->id}}_totalQty">{{ isset($singel_coupon->coupon_qty)?$singel_coupon->coupon_qty:0 }}</label>
                                                    <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="minus" id="{{$singel_coupon->id}}"><i class="fa fa-minus"></i></button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="no-coupon-avail mx-auto font-weight-bold">No Coupon Found</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

