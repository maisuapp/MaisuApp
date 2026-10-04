@extends('mail_template.layout.mail')
@section('page-content')
    <tr class="white" style="background-color: #ffffff;">
        <td>
            <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                   style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                @if(in_array($service_cat_type, [5,6,7,8,9,10]))
                    @if(isset($auth_type) && $auth_type == 1)
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $admin_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">{{ $store_name }} store have a new order request from {{ $user_name }} with the following order</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                                       style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">
                                    @if(isset($product_details))
                                        <tr>
                                            <td><p class="lh top-desc" style="margin-bottom: 3px;">Product Name</p></td>
                                            <td><p class="lh top-desc" style="margin-bottom: 3px;">Amount</p></td>
                                        </tr>
                                        @foreach($product_details as $key => $products)
                                            @foreach($products as $no => $product)
                                                <tr>
                                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">{{ $product->product_name }}</p></td>
{{--                                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">{{ $product->product_name }} {{ " (".$product->num_of_items." * ".$product->price_for_one.")" }}</p></td>--}}
                                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">$ {{ round(($product->price_for_one * $product->num_of_items),2) }}</p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    <tr>
                                        <td><p class="lh top-desc" style="margin-bottom: 5px;">Totol Pay</p></td>
                                        <td><p class="lh top-desc" style="margin-bottom: 5px;">$ {{ $total_pay }}</p></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    @if(isset($auth_type) && $auth_type == 2)
                        <tr>
                            <td class="people-name"
                                style="font-size: 26px;font-weight: 600;letter-spacing: 2px;">
                                <p style="margin-bottom: 0;margin-top: 20px;">Hi {{ $store_name }},</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Thanks for using <strong style="color: red;">My-Checkout STORE (myStore)</strong> app.</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc"
                                   style="margin-bottom: 0;line-height: 25px;width: 100%;padding-bottom: 20px;">
                                    You have a new order request from {{ $user_name }}.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h3 style="text-align: center">
                                    Order Details
                                </h3>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="sub-container sign" cellpadding="0" cellspacing="0" border="0"
                                       style="width: 100%;padding: 10px;color: #40404b;font-family:calibri; ;font-size:15px;letter-spacing: 1px; height: auto;font-weight: 500;">

                                    @if(isset($product_details))
                                        <tr>
                                            <td><p class="lh top-desc" style="margin-bottom: 3px;">Product Name</p></td>
                                            <td><p class="lh top-desc" style="margin-bottom: 3px;">Amount</p></td>
                                        </tr>
                                        @foreach($product_details as $key => $products)
                                            @foreach($products as $no => $product)
                                                <tr>
                                                    {{--                                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">{{ $product->product_name }} {{ " (".$product->num_of_items." * ".$product->price_for_one.")" }}</p>--}}
                                                    <td><p class="lh top-desc"
                                                           style="margin-bottom: 3px;">{{ $product->product_name }}</p>
                                                    </td>
                                                    <td><p class="lh top-desc" style="margin-bottom: 3px;">
                                                            $ {{ round(($product->price_for_one * $product->num_of_items),2) }}</p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    <tr>
                                        <td><p class="lh top-desc" style="margin-bottom: 5px;">Totol Pay</p>
                                        </td>
                                        <td><p class="lh top-desc" style="margin-bottom: 5px;">$ {{ $total_pay }}</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please
                                    process timely and reach out to customer for any questions on their order. Plan on
                                    booking delivery earlier so that it is available for pickup and quickly delivered to
                                    the customer</p>
                                <p class="lh top-desc" style="margin-bottom: 0;line-height: 25px;width: 100%;">Please
                                    remind to rate your customer and also request that you be rated for the job
                                    performed pertaining to this order.</p>
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
@endsection
