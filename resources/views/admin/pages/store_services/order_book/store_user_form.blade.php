@extends('admin.layout.all_admin')
@section('title')
    Place New Order
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <style>
        #phone:focus {
            border-bottom: 2px solid #4099ff
        }
        #phone_error {
            width: 100%;
        }
        #customer_phone {
            position: relative;
            top: 0;
            overflow-x: auto;
            overflow-y: scroll;
            width: 100%;
            max-height: 300px;
            display: none;
        }
        #customer_phone::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.06);
            background-color: #F5F5F5;
        }
        #customer_phone::-webkit-scrollbar {
            height: 2px;
            width: 2px;
            background-color: inset 0 0 6px rgba(0, 0, 0, 0.06);
        }
        #customer_phone::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.48);
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
                                    <img src="{{ asset('assets/images/service-category/'.$service_category->icon_name) }}" class="img-fluid" height="30px" width="30px">&nbsp;&nbsp;Place New Order
                                </h5>
                            @else
                                <h5>Place New Order</h5>
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
                        <form id="main" method="post" action="{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route('post:admin:store_user',[ "slug" => $slug ]) : route('post:dispatcher:store_user',[ "slug" => $slug ]) }}" enctype="multipart/form-data">
                            {{csrf_field() }}
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header"><h5>Customer Details</h5></div>
                                        <div class="card-block">
                                            <div class="form-group row">
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <input id="gender" type="hidden" name="gender" class="form-control" value="0" placeholder="Gender">
                                                            <input id="user_id" type="hidden" name="id" class="form-control" value="{{ old('id') }}" placeholder="User Id">
                                                            <label class="col-form-label">Customer Contact No:<sup class="error">*</sup></label>
                                                            <input type="text" class="form-control" name="contact_number" id="phone" placeholder="Customer Contact Number" value="{{ old('contact_number') }}" required>
                                                            <ul class="customer_contact dropdown-menu" role="listbox" id="customer_phone"></ul>

                                                            <input type="hidden" id="contact_numbers" name="contact_numbers" value="">
                                                            <input type="hidden" id="country_code" name="country_code" value="{{ '+1' }}">
                                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                                            <span class="error">{{ $errors->first('phone') }}</span>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="col-form-label">Customer Full Name:<sup class="error">*</sup></label>
                                                            <input type="text"  class="form-control" name="first_name" required id="customer_first_name" placeholder="Customer Full Name" value="{{ old('first_name') }}">
                                                            @if($errors->first('first_name'))
                                                                <span class="error"> Customer full name is required.</span>
                                                            @endif
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="col-form-label">Customer Email:<sup class="error">*</sup></label>
                                                            <input type="text"  class="form-control" name="email" required id="customer_email" placeholder="Customer Email" value="{{ old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>

                                                        <div class="col-sm-8 my-2" id="password_toggle">
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <label class="col-form-label">Password:<sup class="error">*</sup></label>
                                                                    <input type="text" class="form-control" name="password" id="password" placeholder="Password" value="123456">
                                                                    @if($errors->first('password'))
                                                                        <span class="error"> {{ $errors->first('password') }}</span>
                                                                    @endif
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label class="col-form-label">Confirm Password:<sup class="error">*</sup></label>
                                                                    <input type="text"  class="form-control" name="re_type_password" id="confirm_password" placeholder="Confirm Password" value="123456">
                                                                    @if($errors->first('re_type_password'))
                                                                        <span class="error"> {{ $errors->first('re_type_password') }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <button type="submit" class="btn btn-primary m-b-0">Next</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
            initialCountry: "us",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['cn', 'jp'],
            preferredCountries: ['us'],
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

        var get_back_user_id = $("#user_id").val();
        if(get_back_user_id > 0){
            $("#password_toggle").hide();
        }

        input.addEventListener("countrychange", function() {
            //console.log("country code "+iti.getSelectedCountryData()['dialCode']);
            var country_code = iti.getSelectedCountryData()['dialCode'];
            if(country_code > 0){
                country_code = "+"+country_code;
                document.getElementById("phone_error").innerHTML = '';
            }else{
                country_code = "+1";
                document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
            }
            $("#country_code").val(country_code);
        });
        $("#phone").on('keyup', function (e) {
            var phone = $(this).val();
            if (isNaN(phone)) {
                document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                document.getElementById("contact_numbers").value = "";
            } else {
                document.getElementById("contact_numbers").value = phone;
                document.getElementById("phone_error").innerHTML = '';
            }
            var contact_number = e.target.value;
            var country_code = $("#country_code").val();
            var slug = "{{ $slug }}";
            $("#customer_phone").empty();

            $.ajax({
                url: '{{ Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 ? route("get:admin:store_delivery_search_customer_create_order") : route("get:dispatcher:store_delivery_search_customer_create_order") }}',
                type: "get",
                data: {slug: slug, contact_number: contact_number, country_code: country_code},
                success: function (data) {
                    $("#customer_phone").empty();
                    if (data.success == false) {
                        $("#customer_phone").append('<li><a disabled>Customers Contact Number not found!</a></li>');
                        $("#customer_phone li").click(function () {
                            $(".customer_contact").css("display", "none");
                        });
                        $("#user_id").val("0");
                        $('input[name="user_id"]').attr('value', 0);
                        $("#customer_first_name").attr("readonly", false);
                        // $("#customer_last_name").attr("readonly", false);
                        $("#customer_email").attr("readonly", false);

                        $("#customer_first_name").val("");
                        // $("#customer_last_name").val("");
                        $("#customer_email").val("");

                        // $("#admin_country").empty();
                        // $("#admin_country").attr("readonly", false);
                        // $("#admin_country").attr("disabled", false);

                        // $("#admin_city").empty();
                        // $("#admin_city").attr("readonly", false);
                        // $("#admin_city").append('<option disabled selected value="">Choose Admin City</option>');

                        $("#password").empty();
                        $("#password").attr("required", true);

                        $("#confirm_password").empty();
                        $("#confirm_password").attr("required", true);

                        $("#password_toggle").show();

                        {{--var url = '{{ route("get:admin:get_country_details") }}';--}}
                        //$.ajax({
                        //    type: 'get',
                        //    url: url,
                        //    data: {city_id: 0, type: "html", country_type: 1},
                        //    success: function (result) {
                        //        $("#admin_country").empty();
                        //        $("#admin_country").attr("readonly", false);
                        //        // $("#admin_country").attr("disabled", false);
                        //        if (result.success == true || result.success == "true") {
                        //            $("#admin_country").append(result.data);
                        //        }
                        //    }
                        //});

                    }
                    else {
                        $(".customer_contact").css("display", "block");
                        $.each(data['customers'], function (index, customers) {
                            // $("#customer_phone").empty();
                            $("#customer_phone").append('<li><span class="user_id" style="display: none">' + customers.id + '</span><span class="first_name" style="display: none">' + customers.first_name + '</span><span class="last_name" style="display: none">' + " " +  customers.last_name + '</span><span class="email" style="display: none">' + customers.email + '</span>' +'</span><span class="contact_number" style="display: none">' + customers.contact_number + '</span>' +
                                '<span class="city_id" style="display: none">' + customers.city_id + '</span><span class="city_name" style="display: none">' + customers.city_name + '</span>' +
                                '<a class="dropdown-item"  role="option"><strong> ' + customers.country_code + " " + customers.contact_number + "</strong> - " + customers.first_name + '</a></li>');

                            $("#customer_phone li").click(function () {
                                //var phone = $('a.dropdown-item', this).text();
                                var phone = $('span.contact_number', this).text();
                                $("#phone").val(phone);

                                $("#password_toggle").hide();

                                var user_id = $('span.user_id', this).text();
                                $("#user_id").val(user_id);
                                $('input[name="user_id"]').attr('value', user_id);

                                var first_name = $('span.first_name', this).text();
                                $("#customer_first_name").attr("readonly", true);
                                $("#customer_first_name").val(first_name);

                                // var last_name = $('span.last_name', this).text();
                                // $("#customer_last_name").attr("readonly", true);
                                // $("#customer_last_name").val(last_name);

                                var email = $('span.email', this).text();
                                $("#customer_email").attr("readonly", true);
                                $("#customer_email").val(email);

                                var city_id = $('span.city_id', this).text();
                                var city_name = $('span.city_name', this).text();

                                // $("#admin_city").empty();
                                // $("#admin_city").attr("readonly", true);
                                // $("#admin_city").append('<option disabled value="">Choose Admin City</option><option selected value="'+ city_id +'">' + city_name +'</option>');
                                //
                                //
                                // $("#admin_country").empty();
                                // $("#admin_country").attr("readonly", true);

                                $("#password").empty();
                                $("#password").attr("required", false);

                                $("#confirm_password").empty();
                                $("#confirm_password").attr("required", false);

                                {{--var url = '{{ route("get:admin:get_country_details") }}';--}}
                                //$.ajax({
                                //    type: 'get',
                                //    url: url,
                                //    data: {city_id: city_id, type: "html", country_type: 0},
                                //    success: function (result) {
                                //        if (result.success == true || result.success == "true") {
                                //            $("#admin_country").append(result.data);
                                //        }
                                //    }
                                //});
                                $(".customer_contact").css("display", "none");
                            });
                        });
                    }
                }
            });
        });
    </script>
@endsection
