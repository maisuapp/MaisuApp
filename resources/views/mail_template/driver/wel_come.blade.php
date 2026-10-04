@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                {{--admin--}}
                @if(isset($auth_type) && $auth_type == 1)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Hi Administrator,</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">New driver {{ $driver_name }} signed up. Check all necessary documents before approving (Driver’s License, NBI Clearance, Vehicle Insurance, OR/CR, and Notarized SPA by owner if vehicle is not under the driver’s name).</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Check uploaded documents and contact information for approval.  Any questions, call Driver.</p>
                        </td>
                    </tr>
                @endif
                {{--driver--}}
                @if(isset($auth_type) && $auth_type == 2)
                    <tr>
                        <td class="people-name"
                            style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                            <p style="margin-bottom: 0;margin-top: 20px;">Kamusta {{ $driver_name }},</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat sapag download at paggamitnang <strong style="color: darkblue;">My-Checkout DRIVER (myDriver)</strong>.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Ang iyongaplikasyon ay sinusuri at naghihintaynangaprubalmula kay Admin.</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Ang pagmamaneho ay importantesaatingmgakabayan.  Ang atingresponsabilidad ay magingmagalang, maingatpagmaneho, at bigyanrespeto ang atingmga <strong style="color: darkgoldenrod;">myCheckout</strong> napasahero.  Maramingbigayna features ang <strong style="color: darkblue;">myDriver</strong> at makikitamo real time kung sino ang may kailangannangatingserbisyo. Dahilikaw ay driver puedekangmagingtagahatidnangtao, magdeliver or full courier serbisyo</p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Samantala, sabihan ang iyongkaibigan and pamilyanai-download ang My-Checkout app at gamitin para saiba pang mgaserbisyo.</p>
                            <p class="lh top-desc"><strong>Website: </strong> <a href="www.mycheck-out.com">www.mycheck-out.com</a></p>
                            <p class="lh top-desc"><strong>Facebook: </strong> <a href="www.facebook.com/mycheckoutph">www.facebook.com/mycheckoutph</a></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" ><strong>Instagram: </strong> mycheckoutph</p>

                            <p class="lh top-desc">Download and Sign up from Apple App Store and Google Play Store:</p>
                            <p class="lh top-desc"><span style="margin-left: 15px;"> -<strong style="color: darkgoldenrod;"> My-Checkout (myCheckout)</strong> to use ALL services</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: green;"> My-Checkout SERVICE (myService)</strong> to be an On-Demand Service Provider.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: darkblue;"> My-Checkout DRIVER (myDriver)</strong> to be a Driver, Courier (Delivery or Concierge) and for all Transportation related activities</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: red;"> My-Checkout STORE (myStore)</strong> for Restaurant, Grocery, Liquor Store, Pharmacy, Small to Medium Businesses and Home-Based Businesses.</span></p>
                            <p class="lh top-desc"><span style="margin-left: 15px;">  -<strong style="color: #bb8bfd;"> My-Checkout SHOPPER (myShopper)</strong> to be an external Shopper for STORE related activities.</span></p>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Salamat at palaging mag-ingat…</p>
                            <br>
                            <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;" >Iyongparaan… Anumangoras… Kahitsaan…</p>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endsection
