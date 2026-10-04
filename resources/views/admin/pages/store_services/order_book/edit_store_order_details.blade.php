@extends('admin.layout.all_admin')
@section('title')
    Edit Order Details
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/mdtimepicker.min.css')}}" type="text/css">
    <style>
        .form-control:disabled, .form-control[readonly] {
            background-color: #ffffff;
        }
        .input-group-append .input-group-text {
            background-color: #2ed8b6;
        }
        /* store Type Select Radio Button Style*/
        input[type="radio"] {
            display: none;
        }
        input[type="radio"] + .label {
            position: relative;
            padding-left: 25px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: all .2s ease-in-out;
            margin-bottom: 10px;
        }
        input[type="radio"] + .label:before, input[type="radio"] > .label:after {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            width: 20px;
            height: 20px;
            text-align: center;
            color: black;
            cursor: pointer;
            border-radius: 50%;
            transition: all .3s ease;
        }
        input[type="radio"] + .label:before {
            box-shadow: 0 0 0 0 #CBDCFF, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
        }
        input[type="radio"] + .label:hover {
            color: #4099ff;
        }
        input[type="radio"] + .label:hover:before {
            animation-duration: .5s;
            animation-name: change-size;
            animation-iteration-count: infinite;
            animation-direction: alternate;
            box-shadow: inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
        }
        input[type="radio"]:checked + .label:hover {
            color: #333333;
            cursor: default;
        }
        input[type="radio"]:checked + .label:before {
            animation-duration: .2s;
            animation-name: select-radio;
            animation-iteration-count: 1;
            animation-direction: Normal;
            box-shadow: inset 0 0 0 1px #4099ff, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #4099ff;
        }
        @keyframes change-size {
            from {
                box-shadow: 0 0 0 0 #4099ff, inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            to {
                box-shadow: 0 0 0 1px #4099ff, inset 0 0 0 1px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
        }
        @keyframes select-radio {
            0% {
                box-shadow: 0 0 0 0 #CBDCFF, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #4099ff, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            90% {
                box-shadow: 0 0 0 10px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #4099ff, inset 0 0 0 2px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
            100% {
                box-shadow: 0 0 0 12px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #4099ff, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #4099ff;
            }
        }
        @media screen and (max-width: 576px) {
            input[type="radio"] + .label {
                margin-left: 48%;
                display: block;
            }
            .input-top-bottom-margin {
                margin: 15px 0;
            }
        }
        /*checkbox style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 30px;
            margin-right: 7px;
        }
        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }
        .border-checkbox-section .border-checkbox-group .checklbl {
            height: 0px;
        }
        /*image upload style*/
        .image {
            padding-top: 0;
        }
        #image-preview-1 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 160px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        #image-preview-2 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        #image-preview-1 input, #image-preview-2 input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }
        #image-preview-1 label, #image-preview-2 label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer !important;
            background-color: #000000d1;
            color: white;
            width: 200px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-transform: uppercase;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            text-align: center;
        }
        .res-banner-label {
            margin-top: -20px;
            margin-bottom: 15px;
        }
        @if(isset($store_details) && $store_details->store_banner != Null)
            #image-preview-1 {
            background: url({{ asset('/assets/images/store-images/'.$store_details->store_banner) }}) no-repeat;
            background-size: cover;
            background-position: center;
        }
        @endif
        @if(isset($store_details) && $store_details->profile_image != Null)
            #image-preview-2 {
            background: url({{ asset('/assets/images/profile-images/provider/'.$store_details->profile_image) }}) no-repeat;
            background-size: cover;
            background-position: center;
        }
        @endif
        @if(isset($store_details) && $store_details->profile_image != Null && filter_var($store_details->profile_image, FILTER_VALIDATE_URL) == true)
            #image-preview-2 {
            background: url({{ $store_details->profile_image}}) no-repeat;
            background-size: cover;
            background-position: center;
        }
        @endif
        .input-controls {
            margin-top: 10px;
            border: 1px solid transparent;
            border-radius: 2px 0 0 2px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            height: 32px;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
        #searchInput {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            margin-left: 12px;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 50%;
        }
        #searchInput:focus {
            border-color: #4d90fe;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <div class="external-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Order Details</h5>
                            <span>Edit Order Details</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <div class="card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive ride-detail-table">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <div class="ride-detail-table-header">
                                                        <h5>Product Details</h5>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <a href="{{ route('get:admin:add_store_order_product',[$slug,$order_details->id]) }}"
                                                       class="btn btn-primary m-b-0 btn-right render_link">Add Product</a>
                                                </div>
                                            </div>
                                            <table class="table table-bordered">
                                                <tr style="border-top: 1px solid #dee2e6">
                                                    <th>Category Name</th>
                                                    <th>Product Name</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total Cost</th>
                                                    <th>Action</th>
                                                </tr>
                                                @if(isset($product_details))
                                                    @foreach($product_details as $key => $products)
                                                        @foreach($products as $no => $product)
                                                            <tr>
                                                                @if($no == 0)
                                                                    <td rowspan="{{count($products)}}">
                                                                        {{ $key }}
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    {{ $product->product_name }}
                                                                    @if(isset($product_option_list))
                                                                        @if(array_key_exists($product->id,$product_option_list))
                                                                            @if( $product_option_list[$product->id] != Null)
                                                                                ({{ $product_option_list[$product->id] }})
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ $product->price_for_one }}
                                                                </td>
                                                                <td>
                                                                    {{ $product->num_of_items }}
                                                                </td>
                                                                <td class="">
                                                                    <span class="currency"></span> {{ round(($product->price_for_one * $product->num_of_items),2) }}
                                                                </td>
                                                                    <td>
                                                                        <a href="{{ route('get:admin:delete_store_order_product',[$slug,$product->id]) }}" productid="{{$product->id}}">
                                                                            <img src="{{ asset('/assets/images/template-images/remove-1.png') }}"
                                                                                 style="width:20px; height: 20px;"
                                                                                 data-toggle="tooltip"
                                                                                 data-placement="top" title="Delete">
                                                                        </a>
                                                                    </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Item Total
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->total_item_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Discount
                                                    </td>
                                                    <td>
                                                        <span class=""><span class="currency"></span> {{ $order_details->discount_cost }} </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Tax
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->tax_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Delivery Charge
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->delivery_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        Packaging Charge
                                                    </td>
                                                    <td class="">
                                                        <span class="currency"></span> {{ $order_details->packaging_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="border-right: 1px solid #ffffff"></td>
                                                    <td>
                                                        <h3>Total Pay</h3>
                                                    </td>
                                                    <td>
                                                        <h3 class=""><span
                                                                class="currency"></span> {{ $order_details->total_pay }}
                                                        </h3>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script>
        $(document).ready(function ($) {
            //Use this inside your document ready jQuery
//            $(window).on('popstate', function () {
//                location.reload(true);
//            });
        });
    </script>
    @include('admin.pages.store_services.store.script')
    <script type="text/javascript" src="{{ asset('assets/js/country-code/intlTelInput.min.js')}}"></script>
    <script type="text/javascript">
        var input = document.querySelector("#phone");
        var iti  = window.intlTelInput(input, {
            // allowDropdown: false,
            // autoHideDialCode: false,
            // autoPlaceholder: "off",
            // dropdownContainer: document.body,
            // excludeCountries: ["us"],
            // formatOnDisplay: false,
            // geoIpLookup: function(callback) {
            //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //     var countryCode = (resp && resp.country) ? resp.country : "";
            //     callback(countryCode);
            //   });
            // },
            hiddenInput: "full_number",
            initialCountry: "us",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['ph'],
            separateDialCode: true,
            // initialCountry: "auto",
            // geoIpLookup: function(success, failure) {
            //     $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //         var countryCode = (resp && resp.country) ? resp.country : "";
            //         success(countryCode);
            //     });
            // },
            utilsScript: "{{ asset('assets/js/country-code/utils.js')}}",
        });

        $(document).ready(function () {
            input.addEventListener("countrychange", function () {
                console.log(iti.getSelectedCountryData()['dialCode'])
                var country_code = iti.getSelectedCountryData()['dialCode']
                if (country_code > 0) {
                    country_code = "+" + country_code;
                    document.getElementById("phone_error").innerHTML = '';
                } else {
                    country_code = "+675";
                    document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
                }
                $("#country_code").val(country_code);
            });
            $("#phone").on('keyup', function (event) {
                var contact_number = $(this).val();
                var n = contact_number.indexOf("0", 0);
                // var n = contact_number.charAt(contact_number);
                if (n == 0) {
                    document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                    document.getElementById("contact_numbers").value = "";
                } else {
                    document.getElementById("contact_numbers").value = contact_number;
                    document.getElementById("phone_error").innerHTML = '';
                    console.log(contact_number);
                }
            });
        });
    </script>
@endsection

