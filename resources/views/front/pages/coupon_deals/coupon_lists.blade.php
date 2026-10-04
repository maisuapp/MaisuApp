@extends('front.layout.default')
@section('title')
    <title>
        @if(isset($type) && $type != "coupon_details")
            Coupon Deals
        @else
            Coupon Details
        @endif
    </title>
@endsection
@section('meta-tags')
    <meta name="description" content="Coupon Deals">
    <meta name="keywords" content="Coupon Deals">
@endsection
@section('page-css')


@endsection

@section('page-content')
    <section id="main" class="CouponListMainSection">
        <div class="container-fluid  minheights mainSection">
                <div class="row couponheader">
                    <div class="col-12">
                        <div class="container">
                            <div class="row ">
                                <div class="col-12 px-md-5 couponheaderTitle">
                                    <div class="p-3">
                                        <span class="pr-3"><a href="{{ route('get:coupon-list') }}" title="Return to Coupon Deal"> <i class="fas fa-arrow-left"></i></span></a>
                                            @if(isset($type) && $type != "coupon_details")
                                                Coupon Deals
                                            @else
                                                Coupon Details
                                            @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sticky row md-5 pt-4">
                    <!-- category lists -->
                    <div  class="col-lg-3 col-md-3 col-sm-12 col-xs-12 couponLists  " >
                        <div class="row d-block">
                            <div class="float-right">
                                <div class="col-12">
                                    <div class="cateborderCls">
                                        <div class="catetitleCls">
                                              Category Lists
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12" id="catList">
                                    <ul class="nav nav-pills flex-column " id="myTab" role="tablist">
                                        @if(isset($coupon_category_lists) && (count($coupon_category_lists) > 0))
                                            @foreach($coupon_category_lists as $singel_coupon_cat_lists)
                                                <li class="nav-item">
                                                    <a class="nav-link couponListScroll {{ ($singel_coupon_cat_lists->id == $first_coupon_category_list->id)?"active":"" }}" id="{{$singel_coupon_cat_lists->id}}" data-toggle="tab" href="#{{$singel_coupon_cat_lists->id}}" role="tab" aria-controls="{{$singel_coupon_cat_lists->id}}" aria-selected="false" title="{{ucfirst($singel_coupon_cat_lists->category_name)}}">{{ucfirst($singel_coupon_cat_lists->category_name)}}</a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- category coupon lists -->
                        <div  class="col-lg-6 col-md-6 col-sm-12 col-xs-12  " >
                            <div id="cover-spin-new" style="height:auto;display: none;" ></div>
                            <div class="couponContentPart">
                                @if(isset($type) && $type == "coupon_details")

                                    @if(isset($coupon_detail_html) && $coupon_detail_html !="")
                                        {!!  $coupon_detail_html !!}
                                    @else
                                        <div class="no-coupon-avail mx-auto font-weight-bold">No Coupon Found</div>
                                    @endif

                                @else
                                    @if(isset($cat_wise_coupon_lists_html) && $cat_wise_coupon_lists_html !="")
                                        {!!  $cat_wise_coupon_lists_html !!}
                                    @else
                                        <div class="no-coupon-avail mx-auto font-weight-bold">No Coupon Found</div>
                                    @endif
                                @endif
                            </div>
                        </div>

                    <!-- cart lists -->
                    <div  class="col-lg-3 col-md-3 col-sm-12 col-xs-12 pt-4 pb-4 " >
                        <div class="couponCartSection">
                            @if(isset($coupon_cart) && $coupon_cart !="")
                                {!!  $coupon_cart !!}
                            @endif
                        </div>
                    </div>

                </div>
        </div>
    </section>



    <!-- Modal -->
    <div class="modal fade" id="paymentModalPopup" tabindex="-1" role="dialog" aria-labelledby="paymentModalPopup" aria-hidden="true">
        <div class="modal-dialog <!--modal-dialog-centered-->" role="document">

            <form action="{{ route('post:place-order-coupon') }}" method="post" name="placeordercoupon" id="placeordercoupon">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Coupon Payment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="cc-selector-2">
                            <input id="visa2" class="payment_type_cls" checked="checked"  type="radio" name="payment_type" value="3" />
                            <label class="drinkcard-cc " for="visa2"><i class="fas fa-wallet"></i> Wallet</label>
                            <input id="visa" class="payment_type_cls" type="radio" name="payment_type" value="2"  />
                            <label class="drinkcard-cc " for="visa"><i class="fas fa-credit-card"></i> Card</label>
                        </div>

                        <div class="cc-selector-2 pt-3 hide" id="card_lists">
                            <label class="font-weight-bold cardtextBorderBottom">Select Card</label><br/>
                            @if(isset($card_list) && count($card_list) > 0)
                                <div class="col-md-12">
                                    <div class="row">
                                        @foreach($card_list as $key=>$single_card_list)
                                            <div class="col-md-6">
                                                <input id="card{{$single_card_list->id}}" {{ ($key == 0)?'checked="checked"':"" }}   type="radio" name="card_id" value="{{$single_card_list->id}}" />
                                                <label class="drinkcard-cc " for="card{{$single_card_list->id}}"><i class="fas fa-credit-card"></i> xx-{{ substr($single_card_list->card_number,-4) }}</label>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btnColor btncouponcart purchaseCouponBtn">Pay Now</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@endsection

@section('page-js')
    <script src="{{ asset('assets/front/js/theia-sticky-sidebar.min.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        $('.couponLists')
            .theiaStickySidebar({
                additionalMarginTop: 30
            });

        $('.purchaseCouponBtn').click(function () {
            $('.purchaseCouponBtn').attr('disabled', true);
            $("#placeordercoupon").submit();
        });

        //code set card list hide show
        $(document).on("click",".payment_type_cls",function (){
                var payment_type =$(this).val();
                if(payment_type == 2){
                    if($("#card_lists").hasClass("hide")){
                        $("#card_lists").removeClass("hide");
                        $("#card_lists").addClass("show");
                    }else{
                        $("#card_lists").addClass("show");
                    }
                }else{
                    if($("#card_lists").hasClass("show")){
                        $("#card_lists").removeClass("show");
                        $("#card_lists").addClass("hide");
                    }else{
                        $("#card_lists").addClass("hide");
                    }
                }
        })

        $(document).on("click",".couponListScroll",function (){
            // $("#cover-spin-new").css("display","block");
            $("#cover-spin-new").show();
            var cat_id = $(this).attr('id');
            $.ajax({
                type:'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async :false,
                cache: false,
                /*contentType: "application/json",*/
                /* dataType: "json",*/
                /*processData: false,*/
                url:'{{ route('post:singel-cat-coupons') }}',
                data: {
                        'cat_id':cat_id
                },
                success:function(data){
                    setTimeout(function () {
                        if (data.success) {
                            $(".couponContentPart").empty();
                            $(".couponContentPart").append(data.htmlData);
                        }
                        // $("#cover-spin-new").css("display","none");
                        $("#cover-spin-new").hide();
                    },600);
                }
            });
        });

        //add to coupon to card
        $(document).on("click",".addCouponCart",function (e){
            e.preventDefault(0);

            @if (Illuminate\Support\Facades\Auth::guard("user")->check())
                //add to cart in coupon
                var coupon_id = $(this).attr('id');
                var action = $(this).data('action');
                var thisvar = $(this);
                if(coupon_id > 0)
                {
                    // $("#cover-spin-new").show();
                    $("#cover-spin-new").css("display", "block");
                    $.ajax({

                        type :"POST",
                        async :false,
                        cache: false,
                        url:'{{ route('post:add-coupon-cart') }}',
                        data: {
                            'coupon_id':coupon_id,
                            'action':action,
                            _token : "{{csrf_token()}}"
                        },

                        success:function(data){

                            setTimeout(function (){
                                if(data.success)
                                {
                                    var user_coupon_cart_qty =  data.user_coupon_cart_qty;
                                    if(user_coupon_cart_qty > 0){
                                        /*$(thisvar).closest("div.getButton").addClass("hide");
                                        $(thisvar).closest('div').next("div.getPlusMinusButton").removeClass("hide");*/

                                        $("."+coupon_id+"_getButton").addClass("hide");
                                        $("."+coupon_id+"_getPlusMinusButton").removeClass("hide");
                                    }else{
                                        /*$(thisvar).closest('div').parent().find('div.getButton').removeClass("hide");
                                        $(thisvar).closest("div.getPlusMinusButton").addClass("hide");*/

                                        $("."+coupon_id+"_getButton").removeClass("hide");
                                        $("."+coupon_id+"_getPlusMinusButton").addClass("hide");
                                    }

                                    $(".couponCartSection").empty();
                                    $(".couponCartSection").append(data.cartHtmlData);
                                    $("."+coupon_id+'_totalQty').empty();
                                    $("."+coupon_id+'_totalQty').append(user_coupon_cart_qty);
                                }else{
                                    errormesssageDisplay(data.errmessage,"danger");
                                }
                                // $("#cover-spin-new").hide();
                                $("#cover-spin-new").css("display", "none");

                            },600);


                        }
                    });
                }

            @else
                //code for chcekuser login
                $('.btn-log-in').trigger("click");
            @endif
        });

        //remove from card
        $(document).on("click",".removeCouponCard",function (e){

            e.preventDefault(0);

            @if (Illuminate\Support\Facades\Auth::guard("user")->check())
            //delete to cart in coupon
            let coupon_id = $(this).data("couponid");
            var action = "delete";

                if(coupon_id > 0)
                {
                    // $("#cover-spin-new").css("display","block");
                    $("#cover-spin-new").show();
                    $.ajax({

                        type :"POST",
                        async :false,
                        cache: false,
                        url:'{{ route('post:add-coupon-cart') }}',
                        data: {
                            'coupon_id':coupon_id,
                            'action':action,
                            _token : "{{csrf_token()}}"
                        },
                        success:function(data){

                            setTimeout(function () {

                                if (data.success) {
                                    var user_coupon_cart_qty = data.user_coupon_cart_qty;
                                    if (user_coupon_cart_qty > 0) {

                                        $("." + coupon_id + "_getButton").addClass("hide");
                                        $("." + coupon_id + "_getPlusMinusButton").removeClass("hide");
                                    } else {
                                        $("." + coupon_id + "_getButton").removeClass("hide");
                                        $("." + coupon_id + "_getPlusMinusButton").addClass("hide");
                                    }

                                    $(".couponCartSection").empty();
                                    $(".couponCartSection").append(data.cartHtmlData);
                                    $("." + coupon_id + '_totalQty').empty();
                                    $("." + coupon_id + '_totalQty').append(user_coupon_cart_qty);

                                } else {
                                    errormesssageDisplay(data.errmessage, "danger");
                                }
                                $("#cover-spin-new").hide();

                            },600);
                        }
                    });
                }
            @else
                //code for chcekuser login
                $('.btn-log-in').trigger("click");
            @endif

        });
    });
</script>
@endsection
