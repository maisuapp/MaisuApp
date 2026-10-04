@extends('front.layout.default')
@section('title')
    <title>Checkout</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Store">
    <meta name="keywords" content="Store">
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/front/css/step-by-step.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/smart_cart.css?v=0.2') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/checkout.css?v=0.3') }}">
@endsection
@section('page-content')
        <div class="container-fluid removePadding minheights">
             <form action="{{ route('post:user_order') }}" name="place-order" id="place-order" method="post">
                <div class="sticky row">
                    @if( (isset($empty_cart)) && $empty_cart == 1)
                        <div class="col-md-12 col-sm-12 col-xs-12 order_detail">
                            @include('front.pages.checkout.empty_checkout')
                        </div>
                    @else
                            <div class="col-md-8 col-sm-7 col-xs-12 order_detail">
        <!--                    @include('front.pages.checkout.checkout_delivery_type')-->
                                @if($service_cat_id == $medical_service)
                                    @include('front.pages.checkout.checkout_medical_prescription')
                                @endif
                                @include('front.pages.checkout.checkout_delivery_address')
                                @include('front.pages.checkout.checkout_payment')

                            </div>
                            <div class="col-md-4 col-sm-5 col-xs-12 ">
<!--                                <form action="{{ route('post:user_order') }}" name="place-order" id="place-order" method="post">-->
                                    @include('front.pages.checkout.checkout_cart')
<!--                                </form>-->
                            </div>
                    @endif
                 </div>
            </form>
    </div>
@endsection
@section('page-js')
    <script src="{{ asset('assets/front/js/theia-sticky-sidebar.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.order_detail, .cart')
                .theiaStickySidebar({
                    additionalMarginTop: 30
                });

            //code for check radio type for delivery service
            $('#radioBtn a').on('click', function(){
                var sel = $(this).data('title');
                var tog = $(this).data('toggle');
                $('#'+tog).prop('value', sel);
                var del_ser_val =  $("#delivery_service").val();
                $('a[data-toggle="'+tog+'"]').not('[data-title="'+sel+'"]').removeClass('active').addClass('notActive');
                $('a[data-toggle="'+tog+'"][data-title="'+sel+'"]').removeClass('notActive').addClass('active');
                hideshowAddress(del_ser_val);
            });

            var delivery_service_val =$("#delivery_service").val();
            if(delivery_service_val > 0)
            {
                hideshowAddress(delivery_service_val);
            }
            function hideshowAddress(delivery_service_val){
                    if(delivery_service_val == 2){
                        $(".mainAddressSection").css('display',"none");
                        $(".payment-content").css('display',"block");
                    }else{
                        $(".mainAddressSection").css('display',"block");
                        $(".payment-content").css('display',"none");
                    }
                    $("#user_taken_type").val(delivery_service_val);

                    $.ajax({
                        type: 'get',
                        url: '{{ route('get:delivery_cost') }}',
                        data: {
                            store_id: 0,
                            address_id: 0,
                            user_taken_type: "y",
                        },
                        success: function (data) {
                            if (data.success == true) {
                                $('#form_address_delivery').val();
                                var cart_element = $(".product-cart-list");
                                cart_element.empty();
                                cart_element.append(data.cart_item_data);
                                $(".badge").empty();
                                $(".badge").append(data.cart_item);

                                $("#all-store-to-pay-grand-total").empty();
                                $("#all-store-to-pay-grand-total").append(data.all_store_total_pay);

                            } else {
                                console.log(data);
                                location.reload();
                            }
                        }
                    });
            }
        });
    </script>
@endsection
