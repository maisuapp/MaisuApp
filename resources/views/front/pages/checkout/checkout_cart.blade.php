<div class="theiaStickySidebar col-md-12 col-sm-12 cart">
    <!-- Cart submit form -->
<!--    <form action="{{ route('post:user_order') }}" name="place-order" method="post">-->
        {{ csrf_field() }}
        <div id="smartcart" class="panel panel-default sc-cart sc-theme-default">
            <div class="panel-heading sc-cart-heading">{{ __('messages.133') }} <span class="sc-cart-count badge">0</span></div>
            <div class="product-cart-list">
                <input type="hidden" id="cart_item_list" name="cart_list[]">
            </div>
            <div class="panel-footer sc-cart-toolbar">
                <div class="sc-cart-summary">
                    <div class="sc-cart-summary-subtotal fontBold">
                        <span>{{ __('messages.148') }}</span> <span class="sc-cart-subtotal currency" id="all-store-to-pay-grand-total">0</span>
                    </div>
                </div>

                {{--<a class="coupon active" onclick="$('#coupon-sidebar').asidebar('open')">--}}
                {{--    <div class="row">--}}
                {{--        <div class="coupon-left pull-left">--}}
                {{--            Have a coupon code?--}}
                {{--        </div>--}}
                {{--        <div class="coupon-right pull-right">--}}
                {{--            Apply Coupon--}}
                {{--        </div>--}}
                {{--    </div>--}}
                {{--</a>--}}

                <a href="{{ route('get:restaurant_data', $restaurant_details->id) }}"> <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ __('messages.143') }}</a>
                <div class="sc-cart-toolbar">
                    <input type="hidden" name="delivery_service" id="delivery_service" value="1">
                    <input type="hidden" id="form_total_pay" name="form_total_pay">
                    <input type="hidden" id="form_address_delivery" name="form_address_delivery" value="">
                    <input type="hidden" id="form_payment_type" name="form_payment_type">
                    <input type="hidden" id="card_id" name="form_card_id">
                    <input type="hidden" id="payment_method_type" name="form_payment_method_type">
                    <input type="hidden" id="user_taken_type" name="user_taken_type">
                    <span class="error"></span>
                    <button type="submit" class="btn sc-cart-checkout" id="form_submit" value="{{ __('messages.149') }}">{{ __('messages.149') }}</button>
                </div>
            </div>
        </div>
<!--    </form>-->
</div>
<script>
    var messages_130 = "{{ __('messages.130') }}";
    var messages_131 = "{{ __('messages.131') }}";
</script>
<script src="{{ asset('assets/front/js/custom-cart.js?v=0.16') }}" type="text/javascript"></script>
<script>
    $('#to-pay-grand-total').on('DOMSubtreeModified', function () {
        var total = $(this).text();
        $('#form_total_pay').val(total);
    });

    @if($cart_item > 0 && $cart_item_data !="" && $cart_item_amount > 0)
    $(".badge").empty();
    $(".badge").append({{$cart_item}});
    var cart_element = $(".product-cart-list");
    cart_element.empty();
    cart_element.append({!! $cart_item_data !!});

    $("#all-store-to-pay-grand-total").empty();
    $("#all-store-to-pay-grand-total").append({{$all_store_total_pay}});
    @endif
</script>
