<div class="address-detail col-sm-12">
    <style>
        .empty_cart {
            /*border: 1px dashed #e9e9eb;*/
            /*height: 200px;*/
            height: auto;
            float: left;
            margin: 2.5%;
            padding: 20px;
            width: 95%;
            text-align: center;
            background-color: #fff;
        }
        .empty_cart_btn {
            /*background-color: #60B246;*/
            font-weight: 600;
            text-align: center;
            font-size: 18px;
            color: var(--sitecolor);
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-lg-offset-3 col-lg-6 col-md-offset-3 col-md-6 col-sm-12 col-xs-12">
            <div class="empty_cart">
                <img src="{{ asset('/assets/front/images/template-images/empty_cart.png') }}" width="50%">
                <h3>{{ __('messages.219') }}</h3>
                <div style="font-size: 14px; margin-bottom: 10px;">{{ __('messages.218') }}</div>
                <p><a class="empty_cart_btn" href="{{ route('get:homepage') }}">{{ __('messages.217') }}</a></p>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
