<div class="row">
    <div class="col-12">
        <div class="cateborderCls pl-0">
            <div class="catetitleCls selectedCategory">
                {{ isset($first_cat_all_coupon_lists->category_name)?ucfirst($first_cat_all_coupon_lists->category_name):"" }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="col-12 p-3 ">
                    <div class="col-12 pb-3 couponDetailsTitle font-weight-bold">
                        <span class="pr-3"><i class="fas fa-angle-left"></i></span> {{ isset($first_cat_all_coupon_lists->coupon_name)?ucfirst($first_cat_all_coupon_lists->coupon_name):"" }}
                    </div>
                    <div class="col-12 mx-auto ">
                        <div class="card bg-dark text-white">
                            @if(file_exists(public_path('assets/images/coupon_images/'.$first_cat_all_coupon_lists->coupon_image)))
                                <img src="{{ asset('/assets/images/coupon_images/'.$first_cat_all_coupon_lists->coupon_image)}}" class="card-img" alt="Card image" height="270" >
                            @else
                                <img src="{{ asset('assets/images/coupon_images/no-coupon.jpg') }}"  alt="{{ ucfirst($first_cat_all_coupon_lists->coupon_name) }}" class="card-img"  alt="Card image" height="270">
                            @endif
                        </div>
                    </div>
                    <div class="col-12 pt-3 pb-3 ">

                        <div class="row">
                            <div class="col-lg-9 col-md-9 col-sm-12">
                                <div class="couponDetailsTitle font-weight-bold pb-2">Coupon Details</div>
                                <div class="lableTitle pb-1">Product Details</div>
                                <div class="producttitle">{{ isset($first_cat_all_coupon_lists->coupon_brand_name)?ucfirst($first_cat_all_coupon_lists->coupon_brand_name):"" }}</div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12 text-center my-auto">
                                <div class="couponDetailsTitle font-weight-bold currency">{{ (isset($first_cat_all_coupon_lists->coupon_amount) && $first_cat_all_coupon_lists->coupon_amount > 0  )?ucfirst($first_cat_all_coupon_lists->coupon_amount):0 }}</div>
<!--                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm " >GET</button>-->
                                <div class="{{$first_cat_all_coupon_lists->id}}_getButton  getButton {{(isset($first_cat_all_coupon_lists->coupon_qty) && $first_cat_all_coupon_lists->coupon_qty != Null)?"hide":""}}">
                                    <button class="sc-add-to-cart btn btn-success cartButton btn-sm addCouponCart" data-action="add" id="{{$first_cat_all_coupon_lists->id}}">GET</button>
                                </div>
                                <div class="{{$first_cat_all_coupon_lists->id}}_getPlusMinusButton getPlusMinusButton {{(isset($first_cat_all_coupon_lists->coupon_qty) && $first_cat_all_coupon_lists->coupon_qty != Null)?"":"hide"}}">
                                    <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="add" id="{{$first_cat_all_coupon_lists->id}}"><i class="fa fa-plus"></i></button>
                                    <label class="{{$first_cat_all_coupon_lists->id}}_totalQty">{{ isset($first_cat_all_coupon_lists->coupon_qty)?$first_cat_all_coupon_lists->coupon_qty:0 }}</label>
                                    <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth addCouponCart" data-action="minus" id="{{$first_cat_all_coupon_lists->id}}"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="row pt-2 pb-2">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="lableTitle pb-1">Coupon Code</div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 ">
                                <div class="row mx-0 py-2 couponcodeBackColor">
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="couponcode couponcodeColor font-weight-bold">#########</div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
<!--                                        <div class="couponValidText">Valid for {{ (isset($first_cat_all_coupon_lists->coupon_validity) && $first_cat_all_coupon_lists->coupon_validity > 1  )?$first_cat_all_coupon_lists->coupon_validity." Days":$first_cat_all_coupon_lists->coupon_validity." Day" }}</div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row pt-2 pb-2">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="lableTitle pb-1">Link</div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 ">
                                <div class="row mx-0 py-2 ">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="couponcode couponcodeColor font-weight-bold">
                                            <a href="{{ (isset($first_cat_all_coupon_lists->link) )?$first_cat_all_coupon_lists->link:"" }}" target="_blank"> {{ (isset($first_cat_all_coupon_lists->link) )?$first_cat_all_coupon_lists->link:"" }} </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row pt-2 pb-2 d-block couponInstruction">
                            <div class="panel-group" id="accordion">
                                <div class="panel panel-default border-0 accordionColor mb-0">
                                    <div class="panel-heading border-0 pb-0">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                                How to Redeem <span class="pull-right"><i class="fas fa-angle-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse in show">
                                        <ol class="instructionOL">
                                            <li class="instructionLI">{!! (isset($first_cat_all_coupon_lists->how_to_redeem) )?$first_cat_all_coupon_lists->how_to_redeem:"" !!}</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row pt-2 pb-2 d-block couponInstruction">
                            <div class="panel-group" id="accordion1">
                                <div class="panel panel-default border-0 accordionColor mb-0">
                                    <div class="panel-heading border-0 pb-0">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion1" href="#collapseTwo">
                                                Term and Conditions of Use (Coupons) <span class="pull-right"><i class="fas fa-angle-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="panel-collapse collapse in">
                                        <ol class="instructionOL">
                                            <li class="instructionLI">{!!   (isset($first_cat_all_coupon_lists->terms_condition) )?$first_cat_all_coupon_lists->terms_condition:"" !!}</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
