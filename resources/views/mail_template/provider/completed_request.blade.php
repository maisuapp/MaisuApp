@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(in_array($service_cat_type, [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30]))
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $provider_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: green;">My-Checkout SERVICE (myService)</strong> app.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your service for {{ $service_name }} service was completed at {{ isset($order_date_time)? $order_date_time : "" }}. The money will be transferred to your account.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please rate your customer and also request that you be rated for the job performed pertaining to this order.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please share and tell your friends to sign up and join our community.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Your Way.... Anytime.... Anywhere....</p>

                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endsection
