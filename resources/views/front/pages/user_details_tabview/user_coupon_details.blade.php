<div class="card">
    <h5 class="card-header ">My Coupon Details</h5>
    <div class="card-body addWalletCls p-0">
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="col-12">
                            <div class="col-12 pb-3 couponDetailsTitle font-weight-bold">
                                <span class="pr-3"><i class="fas fa-angle-left"></i></span> {{ isset($get_coupon_details->coupon_name)?ucfirst($get_coupon_details->coupon_name):"" }}
                            </div>
                            <div class="col-12 mx-auto ">
                                <div class="card bg-dark text-white">
                                    @php
                                        $pub_path = 'assets/images/coupon_images/'.$get_coupon_details->coupon_image;
                                        if(file_exists(public_path($pub_path)))
                                        {
                                            $coupon_img_path = asset($pub_path);
                                        }else{
                                            $coupon_img_path = asset('assets/images/coupon_images/no-coupon.jpg');
                                        }
                                    @endphp
                                    <img class="card-img" src="{{ $coupon_img_path }}" alt="Card image" height="170">
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 ">

                                <div class="row">
                                    <div class="col-lg-9 col-md-9 col-sm-12">
                                        <div class="couponDetailsTitle font-weight-bold pb-2">Coupon Details</div>
                                        <div class="lableTitle pb-1">Product Details</div>
                                        <div class="producttitle">{{ isset($get_coupon_details->coupon_brand_name)?ucfirst($get_coupon_details->coupon_brand_name):"" }}</div>
                                    </div>

                                </div>
                                <div class="row pt-2 pb-2">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="lableTitle pb-1">Coupon Code</div>
                                    </div>
                                    <div class="col-lg-12 col-md-12 col-sm-12 ">

                                        @if(isset($get_user_purchase_coupon_list) && count($get_user_purchase_coupon_list) > 0)

                                            @foreach($get_user_purchase_coupon_list as $singel_code)
                                                @php
                                                    if($singel_code->status == "1"){
                                                        $coupon_status = "Used At ".$singel_code->code_used_date;
                                                    }else {
                                                        $curr_date = date("Y-m-d H:i:s");
                                                        if ($singel_code->code_active_date == "0000-00-00 00:00:00" || $singel_code->code_active_date == Null ) {
                                                            $coupon_status = "Not Purchase";
                                                        }else if (strtotime($curr_date) > strtotime($singel_code->code_expire_date)) {
                                                            $coupon_status = "Expired";
                                                        } else {
                                                            $to_date = strtotime($curr_date);
                                                            $from_date = strtotime($singel_code->code_expire_date);
                                                            $day_diff = $from_date -  $to_date;
                                                            $total_days =  ceil($day_diff/(60*60*24));
                                                            $coupon_status =($total_days > 1)?$total_days." Days Remain":$total_days." Day Remain";
                                                        }
                                                    }

                                                @endphp


                                                <div class="row mx-0 py-2 couponcodeBackColor mb-2">
                                                    <div class="col-9">
                                                        <div class="row">
                                                        <div class="col-lg-6 col-md-6 col-sm-12 ">
                                                            <div class="couponcode couponcodeColor font-weight-bold couponCodeName">
                                                                {{ (isset($singel_code->coupon_code) && $singel_code->coupon_code != "")?trim($singel_code->coupon_code):"--" }}</div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                            <div class="couponValidText"> {{ (isset($coupon_status ) && $coupon_status != "")?$coupon_status:"--" }}</div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="col-lg-3 col-md-6 col-sm-12 float-right">
                                                            <div class="couponcode font-weight-bold float-right copybtn"  	data-persistent="false"  data-trigger="click" data-placement="right"  > COPY </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            @endforeach

                                        @endif

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
                                                    <a href="{{ (isset($get_coupon_details->link) )?$get_coupon_details->link:"" }}" target="_blank"> {{ (isset($get_coupon_details->link) )?$get_coupon_details->link:"" }} </a>
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
                                                    <li class="instructionLI">{!! (isset($get_coupon_details->how_to_redeem) )?$get_coupon_details->how_to_redeem:"" !!}</li>
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
                                                    <li class="instructionLI">{!!   (isset($get_coupon_details->terms_condition) )?$get_coupon_details->terms_condition:"" !!}</li>
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
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script type="text/javascript">

    $(document).ready(function (){
        $(document).on("click",".copybtn",function (){

            var cb = $(this).parent().parent().parent().find(".couponCodeName").text();

            var cpyCode = $.trim(cb);

            // creating textarea of html
            var input = document.createElement("textarea");
            //adding p tag text to textarea
            input.value = cpyCode;
            document.body.appendChild(input);
            input.select();
            document.execCommand("Copy");
            // removing textarea after copy
            input.remove();
            /*$(this).tooltip('show');*/

            $(this).tooltip('hide').attr('data-original-title', "Copied!!!").tooltip('show');

            setTimeout(function() {
                $(".copybtn").tooltip('hide');
            }, 1000);

            //
        });
    })
</script>
