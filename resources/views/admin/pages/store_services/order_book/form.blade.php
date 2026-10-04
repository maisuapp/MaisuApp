@extends('admin.layout.all_admin')
@section('title')
    Create Order
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }

        /*Select style*/
        .select2-container {
            width: 100% !important;
            vertical-align: unset;
        }
        .select2-container--default .select2-selection--single {
            height: auto;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            /*padding-top: 1px;*/
            padding: 4px 30px 4px 20px;
            background-color: transparent;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
        .cart-table td, .cart-table th{
            border-top: none;
            border-bottom: 1px solid #dee2e6;
        }
        .cart-table tr:last-of-type td, .cart-table tr:last-of-type th,
        .cart-footer-table td, .cart-footer-table th {
            border-top: none;
            border-bottom: none;
        }

        /*cart style*/
        .store_hide_show {
            display: none;
        }
        .store_list .store_wrapper{
            border-bottom: 1px solid gray;
            padding-bottom: 15px;
            cursor: unset;
        }
        .storeDetails {
            font-size: 14px
        }
        .storeActive {
            color: #565b61;
        }
        .storeInActive {
            color: #a7a7a7;
        }
        .storeDetails .store-attribute {
            font-weight: 600;

        }

        .cart_minus_plus {
            padding: 0 4px 0 5px;
        }
        .font_sz_12 {
            font-size: 12px;
        }
        .font_sz_14 {
            font-size: 14px;
        }
        .list_product_name {
            font-size: 14px;
        }
        .list_product_amount_sec{
            margin-top: 10px;
        }
        .list_product_amount {
            font-size: 15px;
        }
        .list_product_amount_minus{
            padding: 0 4px 0 5px;
            margin-right: 5px;
        }
        .list_product_amount_plus{
            padding: 0 4px 0 5px;
            margin-left: 5px;
        }
        .list_product_discount{
            font-weight: 100;
            color: gray;
        }

        /*toppings model style*/
        .model_close{
            position: fixed;
            right: 30px;
            top: 20px;
            padding: 0;
            margin : 0;
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
            color: #000;
        }
        .radio_label {
            line-height: 1.6 !important;
            padding-left: 1.8rem !important;
            margin-bottom: 0;
        }
        .radio .helper::after, .radio .helper::before{
            width: 1.2rem;
            height: 1.2rem;
            top: 0;
        }
        .product_add_ons{
            padding-bottom: 5px;
            border-bottom: 1px solid #827b7b26;
        }
        /*toppings style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 20px;
            margin-right: 7px;
        }
        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }
        .addmodal{
            top: auto !important;
        }
        .product_browse {
            padding: 5px 0;
            color: #007bff;
            font-size: 15px;
            font-weight: 600;
        }
        .product_browse:hover {
            text-decoration: underline;
            cursor: pointer;
        }
        #delete_address {
            border-color: #ff163d;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)
            <div class="other-service-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif

        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title mx-3">
                        @if(!isset($service_category)) <i class="feather icon-list bg-c-green"></i> @endif
                        <div class="d-inline">
                            @if(isset($service_category))
                                <h5>
                                    <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" class="img-fluid" height="30px" width="30px">&nbsp;&nbsp;Create Order
                                </h5>
                            @else
                                <h5>Create Order</h5>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">

                        <form id="main" method="post" action="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:checkout_order',[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) : route('get:dispatcher:checkout_order',[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) }}" enctype="multipart/form-data">
                            {{csrf_field() }}

                            <input type="hidden" name="store_limit_count" id="store_limit_count" value="">
                            {{--User details--}}
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Customer details</h5>
                                            <a href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:add_users",['slug' => $slug]) : route("get:dispatcher:add_users",['slug' => $slug]) }}" class="btn btn-primary m-b-0 btn-right render_link">Back</a>
                                        </div>
                                        <div class="card-block">
                                            <div class="form-group row">
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">

                                                        <div class="col-sm-4">
                                                            <input id="user_id" type="hidden" name="id" class="form-control" value="{{ (isset($user_details)) ? $user_details->id : old('id') }}" placeholder="User Id">
                                                            <label class="col-form-label">Customer Contact No:<sup class="error">*</sup></label>
                                                            <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="contact_number" id="phone" placeholder="Customer Contact Number" value="{{ (isset($user_details)) ? $user_details->country_code."".App\model\User::ContactNumber2Stars($user_details->contact_number) : old('contact_number') }}" required>
                                                            <input type="hidden" id="contact_numbers" name="contact_numbers" value="{{ (isset($user_details)) ? $user_details->contact_number : '' }}">
                                                            <input type="hidden" id="country_code" name="country_code" value="{{ (isset($user_details)) ? $user_details->country_code : '+1' }}">
                                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                                            <span class="error error_user">{{ $errors->first('phone') }}</span>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="col-form-label">Customer Full Name:<sup class="error">*</sup></label>
                                                            <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="first_name" required id="customer_first_name" placeholder="Customer Full Name" value="{{ (isset($user_details))? $user_details->first_name : old('first_name') }}">
                                                            @if($errors->first('first_name'))
                                                                <span class="error"> Customer full name is required.</span>
                                                            @endif
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="col-form-label">Customer Email:<sup class="error">*</sup></label>
                                                            <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="email" required id="customer_email" placeholder="Customer Email" value="{{ (isset($user_details))? $user_details->email : old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="row" id="select_address">
                                                                <div class="col-sm-10" id="change_col_class">
                                                                    <label class="col-form-label">Select Address:<sup class="error">*</sup></label>
                                                                    <select class="form-control user_address" name="user_address" id="user_address" required>
                                                                        <option disabled selected value="">Choose Address</option>
                                                                        @if(isset($address_list))
                                                                            @foreach($address_list as $address)
                                                                                <option value="{{ $address->id }}" {{ ( (isset($user_address_id)) && $user_address_id == $address->id)? "selected" : "" }}>{{$address->address}} </option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                    @if($errors->first('user_address'))
                                                                        <span class="error">Customer Address is required.</span>
                                                                    @endif
                                                                    <span class="error error_user_address"></span>
                                                                </div>
                                                                <div class="col-sm-2 p-t-30">
                                                                    <div class="btn btn-outline-primary form-control" type="select" id="add_address" total_address="@if((isset($address_list))){{count($address_list)}}@endif">Add Address</div>
                                                                </div>
                                                                <div class="col-sm-2 p-t-30" style="display: none;" id="hide_show_edit_address">
                                                                    <div class="btn btn-outline-primary form-control" type="select" id="edit_address" is_edit="" total_address="@if((isset($address_list))){{count($address_list)}}@endif">Edit Address</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12">
                                                            <div class="row" id="new_address" style="display: none">
                                                                <div class="col-sm-3" id="addressSearch">
                                                                    <label class="col-form-label">Search Address. :<sup class="error">*</sup></label>
                                                                    <input type="text" class="form-control search_txt" name="search_address" id ="search_address" placeholder="Search Address" value="{{ old('flat_no') }}"  autocomplete="off">
                                                                    <input type="hidden" class="form-control" name="address_lat" id ="address_lat" placeholder="Search Address Lat" value="{{ old('address_lat') }}">
                                                                    <input type="hidden" class="form-control" name="address_long" id ="address_long" placeholder="Search Address Long" value="{{ old('address_long') }}">
                                                                    @if($errors->first('search_address'))
                                                                        <span class="error"> Search Address is required.</span>
                                                                    @endif
                                                                    <span class="error error_search_address"></span>
                                                                </div>
                                                                <div class="col-sm-3" id="flat_number">
                                                                    <label class="col-form-label">Flat No. :<sup class="error">*</sup></label>
                                                                    <input type="text" class="form-control" name="flat_no" id="flat_no" placeholder="Flat No" value="{{ old('flat_no') }}" />
                                                                    @if($errors->first('flat_no'))
                                                                        <span class="error"> Flat No is required.</span>
                                                                    @endif
                                                                    <span class="error error_flat_no"></span>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <label class="col-form-label">Landmark :<sup class="error">*</sup></label>
                                                                    <input type="text" class="form-control" name="landmark" id="landmark" placeholder="Landmark" value="{{ old('landmark') }}" />
                                                                    @if($errors->first('landmark'))
                                                                        <span class="error"> Landmark is required.</span>
                                                                    @endif
                                                                    <span class="error error_landmark"></span>
                                                                </div>
                                                                <div class="col-sm-2 p-t-30">
                                                                    <div class="btn btn-outline-primary form-control" type="update" id="update_address">Save Address</div>
                                                                </div>
                                                                <div class="col-sm-2 p-t-30" id="choose_address_show">
                                                                    <div class="btn btn-outline-secondary form-control" type="choose" id="choose_address">Choose Address</div>
                                                                </div>
                                                                <div class="col-sm-2 p-t-30" style="display: none;" id="delete_address_show">
                                                                    <div class="btn btn-outline-danger form-control" type="choose" id="delete_address">Delete Address</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--Create Order--}}
                            <div class="row">
                                <div class="form-group col-sm-8">
                                    <div class="card">
                                        <div class="card-header"><h5 data-toggle="modal" data-target="#large-Modal" >Create Order</h5></div>
                                        <div class="card-block">

                                            <div class="row">
                                                <div class="col-sm-4">

                                                    <div class="row">
                                                        {{--<div class="col-sm-12">--}}
                                                        {{--    <div class="form-group">--}}
                                                        {{--        <label class="col-form-label">Select City</label>--}}
                                                        {{--        <select id="select_city" name="city" class="js-example-placeholder-single1 js-states form-control">--}}
                                                        {{--            <option disabled selected value=""></option>--}}
                                                        {{--            <option disabled>Select a City</option>--}}
                                                        {{--            @if(isset($admin_city))--}}
                                                        {{--                @foreach($admin_city as $key => $city_details)--}}
                                                        {{--                    <option value="{{ $city_details->id }}">{{ ($city_details->name) }}</option>--}}
                                                        {{--                @endforeach--}}
                                                        {{--            @endif--}}
                                                        {{--        </select>--}}
                                                        {{--        <input id="hidden_city_id" type="hidden" name="hidden_city_id" class="form-control" value="" placeholder="Hidden City Id">--}}
                                                        {{--    </div>--}}
                                                        {{--</div>--}}

                                                        <div class="col-sm-12">
                                                            <label class="col-form-label">Select Store :<sup class="error">*</sup></label>
                                                            <div class="store_list">

                                                                <div id="store_list"></div>

                                                                <div class="store_wrapper m-t-15" style="border-bottom: none;">
                                                                    <div class="form-group" style="margin-bottom: 10px">
                                                                        <div class="form-group" style="margin-bottom: 0">
                                                                            <div class="btn btn-dark btn-sm" id="add_store" store_count="0">Select Store</div>
                                                                        </div>
                                                                        <span class="error error_add_store"></span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{--Product Search--}}
                                                <div class="col-sm-8">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group">
                                                                <label class="col-form-label">Product Search</label>
                                                                <input type="text" id="product_search" name="city" class="form-control" placeholder="Product search">
                                                                <span class="error product_search_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-12">
                                                            <div id="store_category">
                                                                <div class="accordion-block">
                                                                    <div id="accordion" role="tablist" aria-multiselectable="true"></div>
                                                                </div>
                                                            </div>
                                                            <div id="store_product" style="display: none;">
                                                                <div class="table-responsive">
                                                                    <table class="table m-b-0 without-header cart-table">
                                                                        <tbody id="search_store_product"></tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{--Cart--}}
                                <div class="form-group col-sm-4">
                                    <div class="card table-card">
                                        <div class="card-header">
                                            <h5>Cart <label class="label label-inverse" id="total_quantity">0</label></h5>
                                        </div>
                                        <div id="cart_details"></div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="modal fade" id="large-Modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="model_product_id" style="margin: auto !important;">
            </div>
        </div>
    </div>
@endsection
@section('page-js')
    <script>
        var allow_store_limit = 1;
    </script>
    {{--map--}}
    @php $map_key = \App\model\GeneralSettings::query()->first() @endphp
    <script src="//maps.googleapis.com/maps/api/js?key={{ isset($map_key)? ($map_key->map_key != Null)? $map_key->map_key : 0 : 0 }}&libraries=places"></script>
    <script>
        var defaultBounds = new google.maps.LatLngBounds( new google.maps.LatLng(-33.8902, 151.1759), new google.maps.LatLng(-33.8474, 151.2631));
        var input = document.getElementById('search_address');
        var options = {
            bounds: defaultBounds,
            types: ['establishment']
        };
        autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            $("#address_lat").val(place.geometry.location.lat());
            $("#address_long").val(place.geometry.location.lng());
        });
    </script>

    {{--country code--}}
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
            // initialCountry: "auto",
            initialCountry: "pt",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['cn', 'jp'],
            preferredCountries: ['pt'],
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
        input.addEventListener("countrychange", function() {
            var country_code = iti.getSelectedCountryData()['dialCode'];
            if(country_code > 0){
                country_code = "+"+country_code;
                document.getElementById("phone_error").innerHTML = '';
            }else{
                country_code = "+675";
                document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
            }
            $("#country_code").val(country_code);
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        //store active or deactive
        $(document).on('click', '.storeDetails', function (e) {
            $(".storeDetails").removeClass("storeActive");
            $(".storeDetails").addClass("storeInActive");
            $(this).removeClass("storeInActive");
            $(this).addClass("storeActive");
        });

        //select and add addresses
        $(document).ready(function () {
            var is_address_selected = $("#user_address").val();
            var selected_add = {{$user_address_id}};

            if(is_address_selected > 0 && is_address_selected == selected_add) {
                // give option for edit and delete address
                addressEditDeleteOption(is_address_selected);
            }

            $(document).on('click', '#add_address', function (e) {
                $("#search_address").val('');
                $("#address_lat").val('');
                $("#address_long").val('');
                $("#flat_no").val('');
                $("#landmark").val('');
                $('.error').text("");
                $('#edit_address').attr("is_edit",0);

                var total_address = $(this).attr("total_address");
                if (total_address >= 5){
                    $(".error_user_address").show();
                    $('.error_user_address').text("User only add five address, you can select any one of given option!");
                } else {
                    $(".error_user_address").hide();
                    $('.error_user_address').text("");

                    $("#select_address").hide();
                    $("#new_address").show();
                    $("#delete_address_show").hide();
                    $("#choose_address_show").show();
                }
            });

            $(document).on('click', '#choose_address', function (e) {
                $(".error_user_address").hide();
                $('.error_user_address').text("");

                $("#select_address").show();
                $("#new_address").hide();
            });

            $(document).on('change', '#user_address', function (e) {
                $(".error_user_address").hide();
                $('.error_user_address').text("");
                var address_id = $(this).val();

                // give option for edit and delete address
                addressEditDeleteOption(address_id);

                selectAddress(address_id);
            });

            $(document).on('click', '#edit_address', function (e) {
                $(".error_user_address").hide();
                $('.error').text("");

                var edit_address_id = $("#user_address").val();

                $.ajax({
                    type: 'get',
                    url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:edit_user_address") : route("get:dispatcher:edit_user_address") }}',
                    data: {
                        edit_address_id: edit_address_id,
                    },
                    success: function (result) {
                        if (result.success == true || result.success == "true") {
                            const lat_long = result.address_data['lat_long'].split(",");
                            $("#search_address").val(result.address_data['address']);
                            $("#address_lat").val(lat_long[0]);
                            $("#address_long").val(lat_long[1]);
                            $("#flat_no").val(result.address_data['flat_no']);
                            $("#landmark").val(result.address_data['landmark']);
                            $('#edit_address').attr("is_edit",1);

                            $("#select_address").hide();
                            $("#new_address").show();
                            $("#choose_address_show").hide();
                            $("#delete_address_show").show();

                        } else {
                            $('#edit_address').attr("is_edit",0);
                            swal({title: "Warning", text: result.message, type: "warning"}, function () {
                            });
                        }
                    }
                })
            });

            $(document).on('click', '#delete_address', function (e) {
                e.preventDefault();

                $(".error_user_address").hide();
                $('.error').text("");

                var edit_address_id = $("#user_address").val();
                var user_id = $("#user_id").val();

                swal({
                        title: "Delete Address?",
                        text: "if press yes then address is delete!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        closeOnConfirm: false,
                        closeOnCancel: false
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            $.ajax({
                                type: 'get',
                                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:delete_user_address") : route("get:dispatcher:delete_user_address") }}',
                                data: {
                                    edit_address_id: edit_address_id,
                                    user_id: user_id,
                                },
                                success: function (result) {
                                    if (result.success == true || result.success == "true") {
                                        $("#select_address").show();
                                        $("#new_address").hide();

                                        $("#user_address").empty();
                                        $("#user_address").append(result.data);

                                        $("#add_address").attr("total_address",result.address_count);
                                        $('#edit_address').attr("total_address",result.address_count);

                                        selectAddress('');
                                        addressEditDeleteOption('');

                                        swal("Success", "User address deleted successfully", "success");
                                    } else {
                                        swal({ title: "Warning", text: result.message, type: "warning" }, function () {});
                                    }
                                }
                            })
                        } else {
                            swal("Cancelled", "Address not deleted", "error");
                        }
                    });
            });

            $(document).on('click', '#update_address', function (e) {
                e.preventDefault();
                var country_id = $(this).val();

                var user_id = $("#user_id").val();
                var search_address = $("#search_address").val();
                var address_lat = $("#address_lat").val();
                var address_long = $("#address_long").val();
                var flat_no = $("#flat_no").val();
                var landmark = $("#landmark").val();
                var flagData = 0;

                // for edit
                var is_edit = $('#edit_address').attr("is_edit");
                var edit_address_id = '';
                if(is_edit == 1) {
                    edit_address_id = $("#user_address").val();
                }

                if (user_id == null || user_id === ""){
                    flagData = 1;
                    $('.error_user').show();
                    $('.error_user').text("Please select user");
                }
                else if (search_address == null || search_address === ""){
                    flagData = 1;
                    $('.error_search_address').show();
                    $('.error_search_address').text("Address field is required");
                }
                else if (flat_no == null || flat_no === ""){
                    flagData = 1;
                    $('.error_flat_no').show();
                    $('.error_flat_no').text("Flat number is required");
                }
                else if (landmark == null || landmark === ""){
                    flagData = 1;
                    $('.error_landmark').show();
                    $('.error_landmark').text("Landmark number is required");
                }
                else if (address_lat == null || address_lat === "" || address_long == null || address_long === ""){
                    flagData = 1;
                    $('.error_search_address').show();
                    $('.error_search_address').text("Please select address with autocomplete");
                }
                else {
                    $('.error_user').hide();
                    $('.error_user').text("");

                    $('.error_search_address').hide();
                    $('.error_search_address').text("");

                    $('.error_flat_no').hide();
                    $('.error_flat_no').text("");

                    $('.error_landmark').hide();
                    $('.error_landmark').text("");
                }
                if (flagData == 0){
                    swal({
                            title: "Save Address?",
                            text: "if press yes then address is save!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                    type: 'get',
                                    url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:add_user_address") : route("get:dispatcher:add_user_address") }}',
                                    data: {
                                        user_id: user_id,
                                        search_address: search_address,
                                        address_lat: address_lat,
                                        address_long: address_long,
                                        flat_no: flat_no,
                                        landmark: landmark,
                                        edit_address_id: edit_address_id,
                                        type: "html"
                                    },
                                    success: function (result) {
                                        if (result.success == true || result.success == "true") {
                                            $("#select_address").show();
                                            $("#new_address").hide();

                                            $("#user_address").empty();
                                            $("#user_address").append(result.data);

                                            $("#add_address").attr("total_address",result.address_count);
                                            $('#edit_address').attr("total_address",result.address_count);

                                            selectAddress('');
                                            addressEditDeleteOption('');

                                            swal("Success", "User address saved successfully", "success");
                                        } else {
                                            swal({ title: "Warning", text: result.message, type: "warning" }, function () {});
                                        }
                                    }
                                })
                            } else {
                                swal("Cancelled", "Address not saved", "error");
                            }
                        });
                }
            });

            function addressEditDeleteOption(address_id) {
                if(address_id > 0 && address_id != '') {
                    $("#hide_show_edit_address").show();
                    $("#change_col_class").removeClass('col-sm-10');
                    $("#change_col_class").addClass('col-sm-8');
                } else {
                    $("#hide_show_edit_address").hide();
                    $("#change_col_class").removeClass('col-sm-8');
                    $("#change_col_class").addClass('col-sm-10');
                }
            }

            function selectAddress(address_id) {
                var get_url = '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:checkout_order",[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) : route("get:dispatcher:checkout_order",[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) }}';
                var new_url = get_url + "/"+address_id;

                $("#main").attr("action",new_url);

                var browser_url = '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:place_order",[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) : route("get:dispatcher:place_order",[ "slug" => $slug, "user_id" => (isset($user_details)) ? $user_details->id : 0 ]) }}';

                var browser_url = browser_url + "/"+address_id;
                window.history.pushState("", "", browser_url);
            }

            var get_back_address_id = '{{ isset($user_address_id) ? $user_address_id : 0 }}';
            if(get_back_address_id  > 0){
                selectAddress(get_back_address_id );
            }
        });


        // $("#select_city").select2({
        //     placeholder: "Select a City",
        //     allowClear: true,
        // }).on("change", function (e) {
        //     var hidden_city_id = $("#hidden_city_id").val();
        //     var city_id = $(this).val();
        //     if (hidden_city_id.trim() == "" || hidden_city_id.trim() == null) {
        //     } else {
        //         if (hidden_city_id != city_id){
        //             removeCart(city_id,0);
        //         }
        //     }
        // });
        function removeCart(city_id, store_id){
            var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:remove_cart_details') : route('get:dispatcher:remove_cart_details') }}',
                data: {
                    user_id: user_id,
                    slug: '{{ $slug }}',
                    city_id: city_id,
                    store_id: store_id,
                },
                success: function (result) {
                    if (store_id>0){
                        get_cart_details(0,0);
                    } else {
                        get_cart_details(1,1);
                    }
                }
            });
        }

        //add new store
        $(document).on('click', '#add_store', function (e) {
            var city_id = $("#select_city").val();
            var user_address = $("#user_address").val();
            var store_count = $(this).attr("store_count");
            if (store_count < allow_store_limit){
                var margin_top = store_count > 0 ? "m-t-15" : "";
                var set_store_count = parseInt(store_count)+ 1;
                if (user_address == null || user_address === "" || user_address == 0){
                    $('.error_add_store').show();
                    $('.error_add_store').text("Please select Address");
                } else {
                    //if (city_id == null || city_id === ""){
                    //    $('.error_add_store').show();
                    //    $('.error_add_store').text("Please select city");
                    //} else {
                    $('.error_add_store').hide();
                    $('.error_add_store').text("");
                    if (store_count == 1) {
                        $(".storeDetails_" + store_count).removeClass("storeActive");
                        $(".storeDetails_" + store_count).addClass("storeInActive");
                    } else if (store_count == 2) {
                        $(".storeDetails_1").removeClass("storeActive");
                        $(".storeDetails_1").addClass("storeInActive");
                        $(".storeDetails_" + store_count).removeClass("storeActive");
                        $(".storeDetails_" + store_count).addClass("storeInActive");
                    }

                    $("#store_list").append('<div class="store_wrapper ' + margin_top + '">\n' +
                        '    <div class="form-group m-b-10 store_select" store_details_count="' + set_store_count + '">\n' +
                        '        <select id="select_store_' + set_store_count + '" store_id="" name="store" class="js-example-placeholder-single1 js-states form-control store_id store_id_' + set_store_count + '" store_details_count="' + set_store_count + '">\n' +
                        '            <option disabled selected value="">Select a Store</option>\n' +
                        '        </select>\n' +
                        '    </div>\n' +
                        '    <div class="storeDetails storeDetails_' + set_store_count + ' storeActive" store_details_count="' + set_store_count + '">\n' +
                        '        <div><span class="store-attribute">Store Name : </span> <span id="store_name_' + set_store_count + '">----</span></div>\n' +
                        '        <div><span class="store-attribute">Email : </span> <span id="store_email_' + set_store_count + '">----</span></div>\n' +
                        '        <div><span class="store-attribute">Contect Number : </span> <span id="store_contect_number_' + set_store_count + '">----</span></div>\n' +
                        '        <div><span class="store-attribute">Opening Hours : </span> <span id="store_open_hour_' + set_store_count + '">----</span></div>\n' +
                        '        <div><span>Today : </span> <span id="store_today_time_' + set_store_count + '">----</span></div>\n' +
                        '        <div style="text-align:end;"><span store_id="0" id="product_browse_' + set_store_count + '" class="product_browse m-t-5">Browse Product</span></div>' +
                        '    </div>\n' +
                        '</div>');
                    $(this).attr("store_count", set_store_count);
                    $(".storeDetails" + set_store_count).addClass("storeActive");

                    changeStoreList(set_store_count, 0);
                    //}
                    if (store_count == allow_store_limit - 1) {
                        $(this).css({"cursor": "not-allowed"});
                        $("#add_store").hide();
                    }
                }
            }
            else {
                $(this).css({"cursor":"not-allowed"});
                $("#add_store").hide();
            }
        });

        function changeStoreList(set_store_count, selected_option) {
            var city_id = 0;
            //var city_id = $("#select_city").val();
            var user_address_id = $("#user_address").val();
            if(user_address_id != null && user_address_id != 0){
                var ignore_store_ids = $(".store_id").map(function () {
                    return $(this).val();
                }).get();

                $.ajax({
                    type: 'GET',
                    url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:get_store_list") : route("get:dispatcher:get_store_list") }}',
                    data: {
                        user_address_id: user_address_id,
                        ignore_store_ids: ignore_store_ids,
                        selected_option: selected_option,
                        slug: '{{ $slug }}',
                        type: "html"
                    }
                }).then(function (result) {
                    var studentSelect = $('.store_id_' + set_store_count);
                    if (result.success == true) {
                        var store_list = result['store_list'];
                        studentSelect.empty().append('<option disabled selected value="">Select a Store</option>');
                        var option = [];
                        $.each(store_list, function (index, store_details) {
                            if (store_details.id == selected_option) {
                                studentSelect.append('<option value="' + store_details.id + '" store_name="' + store_details.name + '" store_email="' + store_details.email + '" store_contect_number="' + store_details.contact_number + '" store_open_hour="' + store_details.store_open_hour + '" store_today_time="' + store_details.store_today_time + '" selected>' + store_details.name + '</option>')
                            } else {
                                studentSelect.append('<option value="' + store_details.id + '" store_name="' + store_details.name + '" store_email="' + store_details.email + '" store_contect_number="' + store_details.contact_number + '" store_open_hour="' + store_details.store_open_hour + '" store_today_time="' + store_details.store_today_time + '">' + store_details.name + '</option>')
                            }
                        });
                    } else {
                        studentSelect.empty().append('<option disabled selected value="">Store Not found</option>');
                    }
                });
            }
        }

        $(document).on('change', '.store_id', function (e) {
            var old_store_id = $(this).attr('store_id');

            var get_store_count = $(this).attr('store_details_count');
            var store_id = $(this).find('option:selected').attr("value");
            var store_name = $(this).find('option:selected').attr("store_name");
            var store_email = $(this).find('option:selected').attr("store_email");
            var store_contect_number = $(this).find('option:selected').attr("store_contect_number");
            var store_open_hour = $(this).find('option:selected').attr("store_open_hour");
            var store_today_time = $(this).find('option:selected').attr("store_today_time");

            $("#store_name_"+get_store_count).text(store_name);
            $("#store_email_"+get_store_count).text(store_email);
            $("#store_contect_number_"+get_store_count).text(store_contect_number);
            $("#store_open_hour_"+get_store_count).text(store_open_hour);
            $("#store_today_time_"+get_store_count).text(store_today_time);
            $("#product_browse_"+get_store_count).attr('store_id',store_id);

            $(".storeDetails").removeClass("storeActive");
            $(".storeDetails").addClass("storeInActive");
            $(this).parent().parent().find(".storeDetails").removeClass("storeInActive");
            $(this).parent().parent().find(".storeDetails").addClass("storeActive");

            if (old_store_id.trim() == "" || old_store_id.trim() == null) {
            } else {
                if (old_store_id != store_id) {
                    //var city_id = $("#select_city").val();
                    var city_id = 0;
                    removeCart(city_id, old_store_id);
                }
            }
            $(this).attr('store_id',store_id);
            $("#store_limit_count").val(store_id);
            setProduct(store_id,0,"");
        });

        $(document).on('click', '.product_browse', function (e) {
            var store_id = $(this).attr('store_id');
            setProduct(store_id,0,"");
        });

        // $(document).on('click', '.store_id', function (e) {
        $(document).on('focus', '.store_id', function (e) {
            var selected_option = $(this).find('option:selected').attr("value");
            var set_store_count = $(this).attr("store_details_count");
            changeStoreList(set_store_count, selected_option);
        });

        $(document).on('click', '.view_more', function (e) {
            var store_id = $(this).attr('store_id');
            var get_view_more = $(this).attr('view_more');
            setProduct(store_id,get_view_more,"");
        });

        $(document).on('keyup', '#product_search', function (e) {
            if ($(".storeActive")[0]){
                var store_details_count = $(".storeActive").attr("store_details_count");
                var store_id = $("#select_store_"+store_details_count).val();
                if (store_id == null){
                    $(".product_search_error").text("please select a store");
                } else {
                    $(".product_search_error").text("");
                    var search_product = $(this).val();
                    setProduct(store_id,0,search_product)
                }

            } else {
                var store_details_count = 0;
                $(".product_search_error").text("please select a store");
            }
        });

        function setProduct(store_id,get_view_more,search_product) {
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:get_store_product_list') : route('get:dispatcher:get_store_product_list') }}',
                data: {
                    user_id: '{{ (isset($user_details)) ? $user_details->id : 0 }}',
                    store_id: store_id,
                    slug: '{{ $slug }}',
                    view_more: get_view_more,
                    search_product: search_product
                },
                success: function (result) {
                    if (result.success == true){
                        if (result.check_search_product > 0){
                            $("#store_category").css({"display": "none"});
                            $("#store_product").css({"display": "block"});
                            $("#search_store_product").empty().append(result.category_wise_product_list);
                            if (!(result.category_wise_product_list.length > 0)){
                                $(".product_search_error").text("Product not found!");
                            }
                        } else {
                            $("#store_product").css({"display": "none"});
                            $("#store_category").css({"display": "block"});
                            if (result.view_more > 0){
                                $('.remove_view_more_'+result.view_more).remove();
                                $(".accordion_table_body_"+ result.view_more).append(result.category_wise_product_list);
                            } else {
                                $("#accordion").empty().append(result.category_wise_product_list);
                            }
                        }
                    } else {
                        alert(result.message);
                    }
                }
            });
        }

        $(document).on('click', '.add_to_cart', function (e) {
            $("#large-Modal").removeClass("addmodal");
            $("#model_product_id").css({ 'width' : '100%'});
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            var is_toppings = $(this).attr('is_toppings');
            var is_iwill_choose = $(this).attr('is_iwill_choose');
            var is_repeat = $(this).attr('is_repeat');
            if (is_iwill_choose > 0) {
                $("#large-Modal").addClass("addmodal");
                // $("#large-Modal").css({"top":"auto !important"})
                $("#model_product_id").css({ 'width' : '50%'});
                $("#model_product_id").css({ 'margin' :'auto !important'});

                $("#model_product_id").empty();
                $("#model_product_id").append('<div class="modal-body" style="display: block; text-align: center;">\n' +
                    '                    <span class="btn btn-outline-success waves-effect waves-light mrgnLeftClass i_will_choose " store_id="'+store_id+'" product_id="'+product_id+'"> I`LL CHOOSE </span>\n' +
                    '                    <span class="btn btn-success waves-effect waves-light finalpriceAmtTxt mrgnRightClass repeat_last" store_id="'+store_id+'" product_id="'+product_id+'"> REPEAT LAST</span>\n' +
                    '                </div>');
            } else {
                if (is_toppings > 0){
                    getProductDetails(store_id,product_id);
                } else {
                    var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
                    var quantity = 1;
                    if (is_repeat > 0) {
                        var cart_id = $(this).attr('cart_id');
                    }else {
                        var cart_id = 0;
                    }
                    add_product_cart(cart_id,user_id,store_id,product_id,quantity,is_repeat,is_toppings)
                }
            }
        });

        $(document).on('click', '.i_will_choose', function (e) {
            $("#model_product_id").empty();
            $("#large-Modal").removeClass("addmodal");
            $("#model_product_id").css({ 'width' : '100%'});

            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            getProductDetails(store_id,product_id);
        });

        $(document).on('click', '.repeat_last, .plus_product', function () {
            var cart_id = $(this).attr('cart_id');
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            $("#large-Modal").modal("hide");
            $('.close').trigger('click');
            var quantity = 1;
            var is_toppings = 0;
            var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
            add_product_cart(cart_id,user_id,store_id,product_id,quantity,1,is_toppings)
        });

        $(document).on('click', '.remove_product', function () {
            var cart_id = $(this).attr('cart_id');
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            $("#large-Modal").modal("hide");
            $('.close').trigger('click');
            var quantity = 0;
            var is_toppings = 0;
            var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
            add_product_cart(cart_id,user_id,store_id,product_id,quantity,1,is_toppings)
        });

        function getProductDetails(store_id,product_id) {
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:get_store_product_details') : route('get:dispatcher:get_store_product_details') }}',
                data: {
                    user_id: '{{ (isset($user_details)) ? $user_details->id : 0 }}',
                    store_id: store_id,
                    slug: '{{ $slug }}',
                    product_id: product_id
                },
                success: function (result) {
                    if (result.success == true) {
                        $("#model_product_id").empty().append(result.product_model_detials);
                    } else {
                        alert(result.message);
                    }
                }
            });
        }

        $(document).on('click', '.product_size, .product_option, .product_topping', function (e) {
            add_cart_count();
        });

        function add_cart_count() {
            var product_size_amount = $(".product_size:checked").attr("product_size_amount");
            var product_option_amount = 0;
            $('.product_option:checked').each(function (i) {
                product_option_amount += Number($(this).attr("product_option_amount"));
            });
            var product_toppings_amount = 0;
            $('.product_topping:checkbox:checked').each(function (i) {
                product_toppings_amount += Number($(this).attr("product_topping_amount"));
            });
            var total_product_amount = parseFloat(product_size_amount) + parseFloat(product_option_amount) + parseFloat(product_toppings_amount);
            $(".total_product_amount").text(total_product_amount);
        }

        $(document).on('click', '#add_product_cart', function (e) {
            var user_id = $(this).attr('user_id');
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            var quantity = 1;
            var is_toppings = 1;
            var cart_id = 0;
            add_product_cart(cart_id,user_id,store_id,product_id,quantity,0,is_toppings)
        });

        function add_product_cart(cart_id,user_id,store_id,product_id,quantity,repeat,is_toppings) {

            var product_option_ids = "";
            var product_toppings_ids = "";
            if (repeat > 0){
                var product_size_id = "";
            } else {
                var product_size_id = $(".product_size:checked").val();
                var op_comma = "";
                $('.product_option:checked').each(function (i) {
                    if (i != 0) {
                        op_comma = ",";
                    }
                    product_option_ids = product_option_ids + op_comma + $(this).val();
                });
                var top_comma = "";
                $('.product_topping:checkbox:checked').each(function (i) {
                    if (i != 0) {
                        top_comma = ",";
                    }
                    product_toppings_ids = product_toppings_ids + top_comma + $(this).val();
                });
            }
            //var product_size_id = $(".product_size:checked").val();
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:add_cart_store_product') : route('get:dispatcher:add_cart_store_product') }}',
                data: {
                    user_id: user_id,
                    store_id: store_id,
                    slug: '{{ $slug }}',
                    product_id: product_id,
                    quantity: quantity,
                    cart_id: cart_id,
                    repeat: repeat,
                    is_toppings: is_toppings,
                    product_size_id: product_size_id,
                    product_option_ids: product_option_ids,
                    product_toppings_ids: product_toppings_ids,
                },
                success: function (result) {
                    if (result.success == true) {
                        $('.add_to_cart_'+product_id).attr('cart_id',result.user_order_cart['id']);
                        if (is_toppings == 1){
                            $('.add_to_cart_'+product_id).attr('is_iwill_choose',1);
                        }
                        $('.add_to_cart_'+product_id).attr('is_repeat',1);
                        $('.close').trigger('click');
                        get_cart_details(0,0);
                    } else {
                        alert(result.message);
                    }
                }
            });
        }

        function get_cart_details(get_type,store_id) {
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:get_cart_details') : route('get:dispatcher:get_cart_details') }}',
                data: {
                    user_id: '{{ (isset($user_details)) ? $user_details->id : 0 }}',
                    get_type: get_type,
                    is_checkout : 0,
                    slug: '{{ $slug }}',
                },
                success: function (result) {
                    if (result.success == true) {
                        $("#cart_details").empty().append(result.cart_details);
                        $("#total_quantity").empty().append(result.total_quantity);
                        if(get_type == 1){
                            $("#add_store").attr("store_count",result.set_store_count);
                            if (!store_id >0) {
                                $("#store_list").empty().append(result.store_list);
                                if (result.store_list  != ""){
                                    var gstore_id = $(".store_id ").find('option:selected').attr("value");
                                    $("#store_limit_count").val(gstore_id);
                                    $("#add_store").hide();
                                }
                                if (result.city_id > 0){
                                    $("#select_city").select2().select2('val',Math.round(result.city_id).toFixed());
                                    $("#hidden_city_id").val(result.city_id);
                                }
                            }
                        }
                    }
                }
            });
        }

        window.onload=function() {
            get_cart_details(1,0);
        };

        function remove_selectStoreBtn(){
            var get_back_store_id = $("#store_limit_count").val();
            if (get_back_store_id > 0) {
                $("#add_store").hide();
            }
        }
        remove_selectStoreBtn();
    </script>

@endsection
