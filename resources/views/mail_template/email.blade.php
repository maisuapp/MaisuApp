@extends('mail_template.layout.mail')
@section('page-content')
    {{--<p class="lh top-desc"
       style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">
        Welcome to the Kipper app! Now, you can obtain 30+ on-demand services
        including transportation, delivery services and other on-demand handyman
        services including pet care, plumber, beauty, and more services.</p>--}}
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                <tr>
                    <td class="people-name" style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                        <p style="margin-bottom: 0;margin-top: 20px;">Ugogo App verification Code is : {{ $opt }}</p>
                    </td>
                </tr>


            </table>
        </td>
    </tr>
@endsection

