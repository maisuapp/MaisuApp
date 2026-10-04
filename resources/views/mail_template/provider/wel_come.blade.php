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
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">New provider {{ isset($provider_name)? $provider_name : "" }} has registered.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Check the provided details before approving.</p>
                        </td>
                    </tr>
                    {{--<tr>--}}
                    {{--<td>--}}
                    {{--<h3>--}}
                    {{--Below are the details.--}}
                    {{--</h3>--}}
                    {{--</td>--}}
                    {{--</tr>--}}
                    <tr>
                        <td>
                            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                                <tr>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;">Services : {{ isset($provider_service_list)? implode(", ",$provider_service_list) : "" }}</p></td>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;"></p></td>
                                </tr>
                                <tr>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;">Email : {{ isset($provider_email)? $provider_email : "" }}</p></td>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;"></p></td>
                                </tr>
                                <tr>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;">Phone : {{ isset($provider_contact_number)? $provider_contact_number : "" }}</p></td>
                                    <td><p class="lh top-desc" style="margin-bottom: 5px;"></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Check uploaded documents and contact information for approval. Any questions, call Provider.</p>
                        </td>
                    </tr>
                @endif
                @if(isset($auth_type) && $auth_type == 2)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $provider_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thank you for signing up!</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please continue using <strong style="color: green;">My-Checkout SERVICE (myService)</strong> and complete profile information particularly HOME LOCATION, SERVICE RADIUS, ADD SERVICES and PACKAGES offered so that your application will be visible for review.Upon completion of entries, your application will be processed pending approval.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;"><strong style="color: green;">My-Checkout SERVICE (myService)</strong> will connect you with consumers. The platform bridges the gap between in-home services and instant online services.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please tell your friends and family to download our apps and together let’s grow our community.</p>
                            <p class="lh top-desc"><strong>Website: </strong> <a href="www.mycheck-out.com">www.mycheck-out.com</a></p>
                            <p class="lh top-desc"><strong>Facebook: </strong> <a href="www.facebook.com/mycheckoutph">www.facebook.com/mycheckoutph</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong>Instagram: </strong> mycheckoutph</p>

                            <p class="lh top-desc">Download and Sign up from Apple App Store and Google Play Store:</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> -<strong style="color: darkgoldenrod;"> My-Checkout (myCheckout)</strong> to use ALL services.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: green;"> My-Checkout SERVICE (myService)</strong> to be an On-Demand Service Provider.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: darkblue;"> My-Checkout DRIVER (myDriver)</strong> to be a Driver, Courier (Delivery or Concierge) and for all Transportation related activities.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: red;"> My-Checkout STORE (myStore)</strong> for Restaurant, Grocery, Liquor Store, Pharmacy, Small to Medium Businesses and Home-Based Businesses.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: #bb8bfd;"> My-Checkout SHOPPER (myShopper)</strong> to be an external Shopper for STORE related activities.</span></p>
                            <br>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >Your Way… Anytime… Anywhere…</p>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endsection
