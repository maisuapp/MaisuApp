<div class="card">
    <h5 class="card-header ">My Coupon Lists</h5>
    <div class="card-body addWalletCls">
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row pt-3 mx-auto ">

                            @if(isset($get_user_purchase_coupons) && count($get_user_purchase_coupons) > 0)

                                @foreach($get_user_purchase_coupons as $singel_puchase_coupon)
                                    <div class="card col-md-4 col-sm-12  border-0   pb-4  defaultWidth singleCard" >
                                        <div class="boxshadow h-100 cardBodyColor ">
                                            @php
                                                    $coupon_name = (isset($singel_puchase_coupon->coupon_name) && $singel_puchase_coupon->coupon_name !="")?$singel_puchase_coupon->coupon_name:"--";
                                                    $coupon_id = (isset($singel_puchase_coupon->coupon_id) && $singel_puchase_coupon->coupon_id !="")?$singel_puchase_coupon->coupon_id:0;
                                                    $coupon_brand_name = (isset($singel_puchase_coupon->coupon_brand_name) && $singel_puchase_coupon->coupon_brand_name !="")?$singel_puchase_coupon->coupon_brand_name:"--";

                                                    if(file_exists(public_path('assets/images/coupon_images/'.$singel_puchase_coupon->coupon_image)))
                                                    {
                                                        $coupon_img_path = asset('/assets/images/coupon_images/'.$singel_puchase_coupon->coupon_image );
                                                    }
                                                    else
                                                    {
                                                        $coupon_img_path = asset('assets/images/coupon_images/no-coupon.jpg');
                                                    }

                                                    /*$coupon_img = \App\Classes\NotificationClass::getWebCompanyWiseImage("coupon_image", $singel_puchase_coupon->coupon_image);*/
                                                    $coupon_validity  = (isset($singel_puchase_coupon->coupon_validity) && $singel_puchase_coupon->coupon_validity !="")?$singel_puchase_coupon->coupon_validity:0;


                                            @endphp
                                            <img src="{{ $coupon_img_path }}" class="img-fluid card-img-top" alt="...">
                                            <div class="card-body ">
                                                <div class="card-title offertitle mb-1">
                                                    <a href="{{ route('get:my_coupon_details',[$coupon_id]) }}" class ="couponTitleLink" title=" {{ $coupon_name }}"> {{ $coupon_name }}  </a>
                                                </div>
                                                <p class="card-text offerstorename mb-2">{{ $coupon_brand_name }}</p>
<!--                                                <div class="row">
                                                    <div class="col-sm-12 ">
                                                        <div class="offerValidCls">Valid for {{ $coupon_validity }} Day</div>
                                                    </div>
                                                </div>-->
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else

                            <div class="card col-md-12 col-sm-12  border-0   pb-4  defaultWidth singleCard text-center font-weight-bold " >
                                No Coupon Founds
                            </div>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
