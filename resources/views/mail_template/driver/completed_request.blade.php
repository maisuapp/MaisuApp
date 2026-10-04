@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(in_array($service_cat_type, [1,2,4]))
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Kamusta {{ $driver_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat sapaggamitnang <strong style="color: darkblue;">My-Checkout DRIVER (myDriver)</strong> app.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Ang {{ $service_name }} ride request {{ isset($ride_no) ? $ride_no : "" }} ay nakumpletonang {{ isset($order_date_time) ? $order_date_time : "" }}. Ang iyongkitanapera ay ililipatsaiyong account.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Paalalasapasaheronamagbigaynangmatapatna review and ratings saatingserbisyo.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat at palaging mag-ingat....</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Iyongparaan.... Anumangoras.... Kahitsaan....</p>
                        </td>
                    </tr>
                @elseif(in_array($service_cat_type, [5,6,7,8,9,10]))
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Kamusta {{ $driver_name }},</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat sapaggamitnang <strong style="color: darkblue;">My-Checkout DRIVER (myDriver)</strong> app.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Ang {{ $service_name }} order {{ isset($ride_no) ? $ride_no : "" }} ay nakumpletona deliver na. Ang iyongkitanapera ay ililipatsaiyong account.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Ibahagimosapamilya at kaibigan.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">I-download ang <strong style="color: darkblue;">myDriver</strong> app kung gusto nilamaging driver</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">I-download ang <strong style="color: darkgoldenrod;">myDriver</strong> app at gamitin para saiba pang mgaserbisyo.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat at palaging mag-ingat....</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Iyongparaan.... Anumangoras.... Kahitsaan....</p>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endsection
