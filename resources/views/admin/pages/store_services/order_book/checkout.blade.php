@extends('admin.layout.all_admin')
@section('title')
    Checkout Order
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <style>
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
        .cart-row td{
            padding: 1px 0;
            font-size: 14px;
        }
        .cart-table td, .cart-table th{
            border-top: none;
            /*border-bottom: 1px solid #dee2e6;*/
        }
        .cart-table tr:last-of-type td, .cart-table tr:last-of-type th,
        .cart-footer-table td, .cart-footer-table th {
            border-top: none;
            border-bottom: none;
        }
    </style>
    <style>
        .store_list .store_wrapper{
            border-bottom: 1px solid gray;
            padding-bottom: 15px;
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

        .cart_icon{
            margin-bottom: 0;
            line-height: unset;
        }

        .cart_minus_plus {
            padding: 0 4px 0 5px;
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
                    <div class="page-header-title">
                        @if(!isset($service_category)) <i class="feather icon-list bg-c-green"></i> @endif
                        <div class="d-inline">
                            @if(isset($service_category))
                                <h5>
                                    <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" height="30" width="30">&nbsp;&nbsp;Checkout Order
                                </h5>
                            @else
                                <h5>Checkout Order</h5>
                                {{--<span>Book Order</span>--}}
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
                        <form id="main" method="post" action="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('any:admin:create_place_order',['slug' => $slug]) : route('any:dispatcher:create_place_order',['slug' => $slug]) }}" enctype="multipart/form-data">
                            {{csrf_field() }}

                            <div class="row">
                                {{--User details--}}
                                <div class="form-group col-sm-6">
                                    <div class="row">
                                        <div class="form-group col-sm-12">
                                            <div class="card">
                                                <div class="card-header"><h5>Customer details</h5></div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="form-group col-sm-12">
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <input id="user_id" type="hidden" name="user_id" class="form-control" value="{{ (isset($user_details)) ? $user_details->id : old('user_id') }}" placeholder="User Id">

                                                                    <label class="col-form-label">Customer Contact No:<sup class="error">*</sup></label>
                                                                    <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="contact_number" id="phone" placeholder="Customer Contact Number" value="{{ (isset($user_details)) ? $user_details->country_code."".App\model\User::ContactNumber2Stars($user_details->contact_number) : old('contact_number') }}" required>
                                                                    <ul class="customer_contact dropdown-menu" role="listbox" id="customer_phone" style="top: 59px; left: 15px; display: none;"></ul>

                                                                    <input type="hidden" id="contact_numbers" name="contact_numbers" value="{{ (isset($user_details)) ? $user_details->contact_number : '' }}">
                                                                    <input type="hidden" id="country_code" name="country_code" value="{{ (isset($user_details)) ? $user_details->country_code : '+675' }}">
                                                                    <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                                                    <span class="error error_user">{{ $errors->first('phone') }}</span>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="col-form-label">Customer Full Name:<sup class="error">*</sup></label>
                                                                    <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="first_name" required id="customer_first_name" placeholder="Customer Full Name" value="{{ (isset($user_details))? $user_details->first_name : old('first_name') }}">
                                                                    @if($errors->first('first_name'))
                                                                        <span class="error"> Customer full name is required.</span>
                                                                    @endif
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <label class="col-form-label">Customer Email:<sup class="error">*</sup></label>
                                                                    <input type="text" {{ (isset($user_details)) ? "readonly" : "" }} class="form-control" name="email" required id="customer_email" placeholder="Customer Email" value="{{ (isset($user_details))? $user_details->email : old('email') }}">
                                                                    <span class="error">{{ $errors->first('email') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <div class="card">
                                                <div class="card-header"><h5>Address details</h5></div>
                                                <div class="card-block">
                                                    <div class="form-group row">
                                                        <div class="form-group col-sm-12">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="row" id="new_address">

                                                                        <div class="col-sm-12" id="addressSearch">
                                                                            <label class="col-form-label">Address. :<sup class="error">*</sup></label>
                                                                            <input type="text" class="form-control" name="search_address" id="search_address" placeholder="Search Address" value="@if( isset($address_list)) {{ $address_list->address }} @endif" required readonly>
                                                                            <input type="hidden" class="form-control" name="address_id" id="address_id" placeholder="Search Address ID" value="@if( isset($address_list)) {{ $address_list->id }} @endif">
                                                                            @if($errors->first('address_id'))
                                                                                <span class="error"> Address field is required.</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-sm-6" id="flat_number">
                                                                            <label class="col-form-label">Flat No. :<sup class="error">*</sup></label>
                                                                            <input type="text" class="form-control" name="flat_no" id="flat_no" placeholder="Flat No" value="@if( isset($address_list)) {{ $address_list->flat_no }} @endif" required readonly/>
                                                                            @if($errors->first('flat_no'))
                                                                                <span class="error"> Flat No is required.</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <label class="col-form-label">Landmark :<sup class="error">*</sup></label>
                                                                            <input type="text" class="form-control" name="landmark" id="landmark" placeholder="Landmark" value="@if( isset($address_list)) {{ $address_list->landmark }} @endif" required readonly/>
                                                                            @if($errors->first('landmark'))
                                                                                <span class="error"> Landmark is required.</span>
                                                                            @endif
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
                                </div>

                                {{--Create Order--}}
                                {{--Cart--}}
                                <div class="form-group col-sm-6">
                                    <div class="card table-card">
                                        <div class="card-header">
                                            <h5>Cart <label class="label label-inverse" id="total_quantity">0</label></h5>
                                            @if(isset($user_address_id) && $user_address_id > 0)
                                                <a href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:place_order",["slug" => $slug, "user_id" => $user_details->id, "address" => $user_address_id]) : route("get:dispatcher:place_order",["slug" => $slug, "user_id" => $user_details->id, "address" => $user_address_id]) }}" class="btn btn-primary m-b-0 btn-right render_link">Back</a>
                                            @else
                                                <a href="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:place_order",["slug" => $slug, "user_id" => $user_details->id]) : route("get:dispatcher:place_order",["slug" => $slug, "user_id" => $user_details->id]) }}" class="btn btn-primary m-b-0 btn-right render_link">Back</a>
                                            @endif
                                        </div>
                                        <div id="cart_details">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page-js')

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
            console.log("country code "+iti.getSelectedCountryData()['dialCode']);
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
        //select and add addresses
        $(document).ready(function (){
            $(document).on('click', '#add_address', function (e) {
                var total_address = $(this).attr("total_address");
                if (total_address >= 5){
                    $(".error_user_address").show();
                    $('.error_user_address').text("User only add five address, you select any one of given option!");
                }
                else {
                    $(".error_user_address").hide();
                    $('.error_user_address').text("");

                    $("#select_address").hide();
                    $("#new_address").show();
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
                            title: "Add Address?",
                            text: "if press yes then address is store!",
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
                                        type:"html"
                                    },
                                    success: function (result) {
                                        if(result.success == true || result.success == "true" ){

                                            $("#select_address").show();
                                            $("#new_address").hide();

                                            $("#user_address").empty();
                                            $("#user_address").append(result.data);
                                        } else {
                                            swal({ title: "Warning", text: result.message, type: "warning" }, function() {});
                                        }
                                    }
                                })
                            } else {
                                swal("Cancelled", "Address not added", "error");
                            }
                        });
                }
            });
        });

        //$("#select_city").select2({
        //    placeholder: "Select a City",
        //    allowClear: true,
        //}).on("change", function (e) {
        //    //}).on("select2:select", function (e) {
        //    var str = $(this).val();
        //    console.log(str);
        //    // DOSelectAjaxProd(e.val, str);
        //});

        $("#select_city").select2({
            placeholder: "Select a City",
            allowClear: true,
        }).on("change", function (e) {
            var city_id = $(this).val();
            var slug = "{{ $slug }}";
            console.log(city_id);
            if(city_id > 0) {
                var  url = '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:get_store_list") : route("get:dispatcher:get_store_list") }}';
                $.ajax({
                    type: 'get',
                    url: url,
                    data: {city_id: city_id ,type:"html"},
                    success: function (result) {
                        if(result.success == true || result.success == "true" ){
                            $("#admin_city").empty();
                            $("#admin_city").append(result.data);
                        }
                    }
                })
            }else{
                alert("Please select proper country")
            }
        });

        $(document).on('click', '.plus_product', function () {
            var cart_id = $(this).attr('cart_id');
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            var quantity = 1;
            var is_toppings = 0;
            var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
            add_product_cart(cart_id,user_id,store_id,product_id,quantity,1,is_toppings)
        });

        $(document).on('click', '.remove_product', function () {
            var cart_id = $(this).attr('cart_id');
            var store_id = $(this).attr('store_id');
            var product_id = $(this).attr('product_id');
            var quantity = 0;
            var is_toppings = 0;
            var user_id = '{{ (isset($user_details)) ? $user_details->id : 0 }}';
            add_product_cart(cart_id,user_id,store_id,product_id,quantity,1,is_toppings)
        });

        function add_product_cart(cart_id,user_id,store_id,product_id,quantity,repeat,is_toppings) {

            var product_option_ids = "";
            var product_toppings_ids = "";
            var product_size_id = "";
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
                    console.log(result);
                    if (result.success == true) {
                        get_cart_details(0);
                    } else {
                        alert(result.message);
                    }
                }
            });
        }

        function get_cart_details(get_type) {
            var address_id = '{{ (isset($address_list)) ? $address_list->id : 0 }}';
            console.log(address_id);
            $.ajax({
                type: 'get',
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('get:admin:get_store_checkout_cart_details') :route('get:dispatcher:get_store_checkout_cart_details') }}',
                data: {
                    user_id: '{{ (isset($user_details)) ? $user_details->id : 0 }}',
                    address_id: '{{ (isset($address_list)) ? $address_list->id : 0 }}',
                    get_type: get_type,
                    is_checkout : 1,
                    slug: '{{ $slug }}',
                },
                success: function (result) {
                    console.log(result);
                    if (result.success == true) {
                        $("#cart_details").empty().append(result.cart_details);
                        $("#total_quantity").empty().append(result.total_quantity);
                        if(get_type == 1){
                            $("#store_list").empty().append(result.store_list);
                            if (result.city_id > 0){
                                $("#select_city").select2().select2('val',Math.round(result.city_id).toFixed());
                            }
                        }
                    }
                }
            });
        }
        window.onload=function() { get_cart_details(1) };



    </script>

@endsection
