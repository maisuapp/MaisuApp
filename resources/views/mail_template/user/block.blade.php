@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0" style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                <tr>
                    <td class="people-name"
                        style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                        <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $user_name }},</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">Your profile was blocked due to unwanted activities and non-compliance with <strong style="color: darkgoldenrod;">My-Checkout(myCheckout)</strong> Terms and Conditions.</p>
                        {{--<p class="lh top-desc"--}}
                        {{--   style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
                        {{--    Sorry, it looks like your profile has been blocked due to unwanted activities.--}}
                        {{--</p>--}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection

