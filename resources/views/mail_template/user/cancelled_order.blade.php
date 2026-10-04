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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We regret to inform you that, your ride request was rejected or cancelled by ({{ $cancel_by }} ) due to multiple reasons such as distance, locationand others.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We apologize for any inconvenience but please try to book your ride again.</p>
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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We regret to inform you that {{ $cancel_by }} or admin were unable to approve your {{ $service_name }} order request due to multiple reasons such as distance, location, item(s) unavailable, backlog and others.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We apologize for any inconvenience but please try to book an order again.</p>
                            </td>
                        </tr>
{{--                    @elseif(in_array($service_cat_type, [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30]))--}}
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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We regret to inform you that {{ $provider_name }} canceled your {{ $service_name }} service request due to multiple reasons such as distance, location, availability, backlog and others.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">We apologize for any inconvenience but please try to book another service in the future.</p>
                                {{--<p class="lh top-desc"--}}
                                {{--   style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
                                {{--    {{ $cancel_by }} has cancelled your {{ $service_name }} service request.--}}
                                {{--</p>--}}
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection
