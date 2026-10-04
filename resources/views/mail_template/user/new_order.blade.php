@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(isset($service_category_type_array))
                    @if(in_array($service_cat_type, [1,2,4]))
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $user_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: darkgoldenrod;">My-Checkout(myCheckout)</strong>.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">Your {{ $service_name }} ride request placed and you can track assigned driver’s geolocation using the application.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">Please be on your pickup location prior to driver’s arrival and check your destination.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">Always be safe and here are some tips to remember.</p>
                                <p class="lh top-desc">Ridesharing Safety Tips:</p>
                                <p class="lh top-desc"><span style="margin-left: 15px;"> -	Check the driver's rating on <strong style="color: darkgoldenrod;">myCheckoutapp</strong></span></p>
                                <p class="lh top-desc"><span style="margin-left: 15px;">  - Verify the identity of the driver and car before entering the vehicle.</span></p>
                                <p class="lh top-desc"><span style="margin-left: 15px;">  - Know your surroundings before, during and after your ride</span></p>
                                <p class="lh top-desc"><span style="margin-left: 15px;">  - Follow along in your own maps app.</span></p>
                                <p class="lh top-desc"><span style="margin-left: 15px;">  - If something doesn’t feel right, don’t get into the vehicle.</span></p>
                                <p class="lh top-desc"><span style="margin-left: 15px;">  - Rate your driver and share your ride experience using <strong style="color: darkgoldenrod;">myCheckoutapp</strong>.</span></p>
                            </td>
                        </tr>
                    @elseif(in_array($service_cat_type, [5,6,7,8,9,10]))
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $user_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: darkgoldenrod;">My-Checkout(myCheckout)</strong>.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your order was sent to the {{ isset($store_name)? $store_name : "" }} store for approval. You will get a notification message once your order is accepted.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">In the meantime, please check the application for other service offerings.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please share and tell your friends to sign up and join our community.</p>
                            </td>
                        </tr>
                    {{--@elseif(in_array($service_cat_type, [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30]))--}}
                    @elseif(in_array($service_cat_type, $service_category_type_array))
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $user_name }},</p>
                            </td>
                        </tr>
                        {{--<tr><td><p class="lh top-desc"style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">{{ $content }}</p></td></tr>--}}
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: darkgoldenrod;">My-Checkout(myCheckout)</strong>.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your {{ $service_name }} service request was accepted by {{ isset($provider_name)? $provider_name : "" }}. You will be able to track your provider’s location status at the time of service.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">In the meantime, please check the application for other service offerings.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please share and tell your friends to sign up and join our community.</p>
                                {{--<p class="lh top-desc"--}}
                                {{--style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
                                {{-- Your {{ $service_name }} service request accepted--}}
                                {{-- by {{ isset($provider_name)? $provider_name : "" }}. The provider will come at--}}
                                {{-- doorstep and provide service at your booking time.--}}
                                {{--</p>--}}
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection
