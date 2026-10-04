@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(in_array($service_cat_type, [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30]))
                    @if(isset($auth_type) && $auth_type == 1)
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $admin_name }},</p>
                            </td>
                        </tr>
                        {{--<tr><td><p class="lh top-desc"style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">{{ $content }}</p></td></tr>--}}
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $provider_name }} provider has a new services request for  {{ $service_name }} service from  {{ $user_name }}.</p>
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
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: green;">My-Checkout SERVICE (myService)</strong> app.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">You have a new services request for the {{ $service_name }} service from the {{ $user_name }}.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please provide a courteous and professional service to your customer. Reach out to customer for any questions on their order. Plan on being on time and provide an exemplary service with timely completion of assigned task.</p>
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection

