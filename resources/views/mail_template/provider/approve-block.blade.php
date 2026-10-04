@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                {{--                            @if(in_array($service_cat_type, [1,2,4]))--}}
                <tr>
                    <td class="people-name"
                        style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                        <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $provider_name }},</p>
                    </td>
                </tr>
                {{--<tr><td><p class="lh top-desc"style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">{{ $content }}</p></td></tr>--}}
                @if( (isset($status)) && $status  == 1)
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: green;">My-Checkout SERVICE (myService)</strong> app.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Congratulations! Your account was approved and welcome to our Community!</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">We are thrilled that you can start using the platform and generate On-Demand Service requests from various customers.  We recognize that there are several solutions out on the market and appreciate that you trust us to be your valued e-commerce provider.  We at <strong style="color: green;">myService</strong> will provide you with the technical venue to grow your home/personal business in this challenging time.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">We wish you good health and safety.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please share and tell your friends to sign up and join our community.</p>
                            <p class="lh top-desc"><strong>Website: </strong> <a href="www.mycheck-out.com">www.mycheck-out.com</a></p>
                            <p class="lh top-desc"><strong>Facebook: </strong> <a href="www.facebook.com/mycheckoutph">www.facebook.com/mycheckoutph</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong>Instagram: </strong> mycheckoutph</p>
                            <p class="lh top-desc">Download and Sign up from Apple App Store and Google Play Store:</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> -<strong style="color: darkgoldenrod;"> My-Checkout (myCheckout)</strong> to use ALL services</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: green;"> My-Checkout SERVICE (myService)</strong> to be an On-Demand Service Provider.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: darkblue;"> My-Checkout DRIVER (myDriver)</strong> to be a Driver, Courier (Delivery or Concierge) and for all Transportation related activities.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: red;"> My-Checkout STORE (myStore)</strong> for Restaurant, Grocery, Liquor Store, Pharmacy, Small to Medium Businesses and Home-Based Businesses.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: #bb8bfd;"> My-Checkout SHOPPER (myShopper)</strong> to be an external Shopper for STORE related activities.</span></p>
                            <br>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >Your Way.... Anytime.... Anywhere....</p>
                        </td>
                    </tr>
                @elseif((isset($status)) && $status  == 2)
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your profile was blocked due to unwanted activities and non-compliance with Thanks for using <strong style="color: green;">My-Checkout SERVICE (myService)</strong> app Terms and Conditions.</p>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endsection
