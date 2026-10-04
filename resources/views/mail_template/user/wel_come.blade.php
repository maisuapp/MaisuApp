@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px 30px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(isset($auth_type) && $auth_type == 1)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $admin_name }},</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">New user {{ $user_name }} signed up. Please check all necessary information.</p>
                        </td>
                    </tr>
                @endif
                @if(isset($auth_type) && $auth_type == 2)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $user_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for downloading My-Checkout and hope that you enjoy use of our multi- service all in one solution.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">Please tell your friends and family to download our appsand together let’s grow our community.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;"><strong style="color: red;">Delivery (myStore)</strong></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >My-Checkout delivery service <strong style="color: red;">(myStore)</strong> provides fast, often same-day delivery, within a particular area. The platform offers a wide range of options, such as food and grocery, or all kinds of packages and merchandise.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;" ><strong style="color: green;">On-Demand (myService)</strong></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >My-Checkout on-demand service <strong style="color: green;">(myService)</strong> will connect you with service professionals. The platform bridges the gap between in-home services and instant online services. Our on-demand home services include personal shopper, doctor on-call, personal care services, tutor, home maintenance and many more. We also have Virtual service consultation features.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong style="color: darkblue;">Transportation (myDriver)</strong></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >My-Checkout transportation service <strong style="color: darkblue;">(myDriver)</strong> provides immediate assistance for a person to travel anywhere via car or motor bike. The platform provides ride-share transportation from one place to another to obtain services or carry out life's activities.</p>
                            <p class="lh top-desc"><strong>Website: </strong> <a href="www.mycheck-out.com">www.mycheck-out.com</a></p>
                            <p class="lh top-desc"><strong>Facebook: </strong> <a href="www.facebook.com/mycheckoutph">www.facebook.com/mycheckoutph</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong>Instagram: </strong> mycheckoutph</p>
                            <p class="lh top-desc">Download the and Sign up from Apple App Store and Google Play Store:</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> -<strong style="color: darkgoldenrod;"> My-Checkout (myCheckout)</strong> to use ALL services</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: green;"> My-Checkout SERVICE (myService)</strong> to be an On-Demand Service Provider.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: darkblue;"> My-Checkout DRIVER (myDriver)</strong> to be a Driver, Courier (Delivery or Concierge) and for all Transportation related activities</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: red;"> My-Checkout STORE (myStore)</strong> for Restaurant, Grocery, Liquor Store, Pharmacy, Small to Medium Businesses and Home-Based Businesses.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: #bb8bfd;"> My-Checkout SHOPPER (myShopper)</strong> to be an external Shopper for STORE related activities.</span></p>
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

