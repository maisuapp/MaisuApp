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
                        {{--<tr><td><p class="lh top-desc"style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">{{ $content }}</p></td></tr>--}}
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: darkgoldenrod;">My-Checkout(myCheckout)</strong>.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your <strong style="color: darkgoldenrod;">{{ $service_name }}</strong> ride request {{ $ride_no }} was completed. We appreciate your trust by booking your ride service thru our application.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Hope that you consider us on your future ride trips and share your ride experience with your friends.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your Way… Anytime… Anywhere…</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Have a great day!</p>
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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your <strong style="color: darkgoldenrod;">{{ $service_name }}</strong> order {{ $ride_no }} was delivered at {{ isset($delivery_address)? $delivery_address : "" }}, by {{ isset($driver_name)? $driver_name : "" }} at {{ isset($order_date_time)? $order_date_time : "" }}.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">We appreciate your trust by booking your delivery service thru our application.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Hope that you consider us on your future delivery orders and share your order experience with your friends.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your Way… Anytime… Anywhere…</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Have a great day!</p>
{{--                                <p class="lh top-desc"--}}
{{--                                   style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
{{--                                    Your {{ $service_name }} order {{$ride_no}} has been deliver--}}
{{--                                    at @if(isset($delivery_address)) {{ $delivery_address }} @endif, by--}}
{{--                                    @if(isset($driver_name)) {{ $driver_name }} @endif--}}
{{--                                    at @if(isset($order_date_time)) {{ $order_date_time }} @endif.--}}
{{--                                </p>--}}
                            </td>
                        </tr>
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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Hi, your {{ $service_name }} service request was completed by {{ $provider_name }} at {{ $order_date_time }}.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">We appreciate your trust by booking your on-demand service thru our application.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Hope that you consider us on your future on-demand service needs and share your experience with your friends.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your Way… Anytime… Anywhere…</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Have a great day!</p>

{{--                                <p class="lh top-desc"--}}
{{--                                   style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
{{--                                    Hi, your {{ $service_name }} service request has been completed--}}
{{--                                    by {{ isset($provider_name)? $provider_name : "" }}--}}
{{--                                    at {{ isset($order_date_time)? $order_date_time : "" }}.--}}
{{--                                    Have a great day!--}}
{{--                                </p>--}}
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection
