@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(in_array($service_cat_type, [1,2,4]))
                    {{--1=admin, 2=driver--}}
                    @if(isset($auth_type) && $auth_type == 1)
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $admin_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $driver_name }} has a new {{ $service_name }} ride request from {{ $user_name }}</p>

                                @if(isset($pickup_address))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $pickup_address }}</p>@endif
                                @if(isset($destination_address))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $destination_address }}</p>@endif
                                @if(isset($order_date_time))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $order_date_time }}</p>@endif

{{--                                @if(isset($pickup_address))--}}
{{--                                    <p class="lh top-desc"--}}
{{--                                       style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 10px;">--}}
{{--                                        Pickup Address : {{ $pickup_address }}--}}
{{--                                    </p>--}}
{{--                                @endif--}}
{{--                                @if(isset($destination_address))--}}
{{--                                    <p class="lh top-desc"--}}
{{--                                       style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 10px;">--}}
{{--                                        Destination Address--}}
{{--                                        : {{ $destination_address }}--}}
{{--                                    </p>--}}
{{--                                @endif--}}
{{--                                @if(isset($order_date_time))--}}
{{--                                    <p class="lh top-desc"--}}
{{--                                       style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">--}}
{{--                                        Pickup Date-Time : {{ $order_date_time }}--}}
{{--                                    </p>--}}
{{--                                @endif--}}
                            </td>
                        </tr>
                    @endif
                    @if(isset($auth_type) && $auth_type == 2)
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Kamusta {{ $driver_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat sapaggamitnang <strong style="color: darkblue;">My-Checkout DRIVER (myDriver)</strong> app.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">May bagokang {{ $service_name }} ride request mula kay  {{ isset($user_name) ? $user_name : "" }}.</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Eto ang detalye</p>
                                @if(isset($pickup_address))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $pickup_address }}</p>@endif
                                @if(isset($destination_address))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $destination_address }}</p>@endif
                                @if(isset($order_date_time))<p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $order_date_time }}</p>@endif
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Mag-ingatsapagmamaneho and galangin ang atingpasahero para bigyan ka nangmagandang review and ratings.</p>
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection
