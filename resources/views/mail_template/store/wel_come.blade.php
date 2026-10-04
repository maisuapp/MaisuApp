@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(isset($auth_type) && $auth_type == 1)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $admin_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">New store {{ $store_name }} signed up. Please check all necessary information and documents provided before approval</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Check uploaded documents and contact information for approval. Any questions, call Store.</p>
                        </td>
                    </tr>
                @endif
                @if(isset($auth_type) && $auth_type == 2)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $store_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thank you for signing up!</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please complete your registration by going to <a href="https://my-checkout.com/store-admin/login">https://my-checkout.com/store-admin/login</a>
                                Enter Service Type, Store Details, Upload Required Documents and add your Product Lists.
                                Upon completion of entries, your <strong style="color: red;">myStore</strong> application will be reviewed for approval.
                            </p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;"><strong style="color: red;">My-Checkout STORE (myStore)</strong> is a multi-service platform that provides an all-in-one solution to consumers.  We are committed to be your valued partner to deliver a turnkey solution with measurable results in growing your business.</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> - We CONNECT BUSINESSES with their customer base and help acquire new ones, resulting in dramatic increase in their top-line growth revenues.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> - We build INNOVATIVE AND FULLY INTEGRATED SOLUTIONS to help increase our clients’ brand and services loyalty with their target audience.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> - We are RESULT ORIENTED and we have a proven formula to maximize your profits and ensure that your business stand out from the rest.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> - We have many YEARS OF EXPERIENCE hailing from various backgrounds. You will only work with experienced individuals and have an attentive support staff to manage your account.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> - We have COMPETITIVE AND FAIR PRICING. There are no surprise bills. That’s how we would like to be treated, and that is how our clients are treated.</span></p>

                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >Please tell your friends and family to download our apps and together let’s grow our community.</p>

                            <p class="lh top-desc"><strong>Website: </strong> <a href="www.mycheck-out.com">www.mycheck-out.com</a></p>
                            <p class="lh top-desc"><strong>Facebook: </strong> <a href="www.facebook.com/mycheckoutph">www.facebook.com/mycheckoutph</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong>Instagram: </strong> mycheckoutph</p>
                            <p class="lh top-desc">Download and Sign up from Apple App Store and Google Play Store:</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> -<strong style="color: darkgoldenrod;"> My-Checkout (myCheckout)</strong> to use ALL services</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: green;"> My-Checkout SERVICE (myService)</strong> to be an On-Demand Service Provider.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: darkblue;"> My-Checkout DRIVER (myDriver)</strong> to be a Driver, Courier (Delivery or Concierge) and for all Transportation related activities.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: red;"> My-Checkout STORE (myStore)</strong> for Restaurant, Grocery, Liquor Store, Pharmacy, Small to Medium Businesses and Home-Based Businesses.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: #bb8bfd;"> My-Checkout SHOPPER (myShopper)</strong> to be an external Shopper for STORE related activities.</span></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">For further inquiries regarding your application, email us at <a target="_blank" href="mailto:sales@whitelabelfox.com">sales@whitelabelfox.com</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">For possible extended partnership, email us at  <a target="_blank" href="mailto:partners@foxjek.com">partners@foxjek.com</a></p>
                            <br>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >Your Way… Anytime… Anywhere…</p>
                        </td>
                    </tr>
                @endif
                {{--                            <tr>--}}
                {{--                                <td>--}}
                {{--                                    <p class="lh top-desc"--}}
                {{--                                       style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">{{ $content_more }}</p>--}}
                {{--                                </td>--}}
                {{--                            </tr>--}}
            </table>
        </td>
    </tr>
@endsection

