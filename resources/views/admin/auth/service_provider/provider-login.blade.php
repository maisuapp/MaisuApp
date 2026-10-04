@extends('admin.layout.auth')
@section('title')
    Provider Admin Login
@endsection
@section('page-css')
    <style>
        .btn-success {
            background-color: #653996 !important;
            border-color: #653996 !important;
        }

        .btn-success:hover, .btn-success:active, .btn-success:focus {
            background-color: #653996 !important;
            border-color: #653996 !important;
            box-shadow: 0 0 0 0.2rem #65399690 !important;
        }
        #contact_number {
            width: 85%;
            height: 38px;
            padding: 15px;
        }

        .form-material #contact_number:focus {
            /*border-color: transparent;*/
            border-bottom: 1px solid #4099ff;
            outline: none;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        .iti__flag-container {
            z-index: 9;
        }
        .error {
            color: red;
        }
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/demo.css')}}">
    <style>
        #phone:focus {
            border-bottom: 2px solid #00aff0
        }
    </style>
@endsection
@section('page-content')
    {{--<input type="tel" name="email" id="phone">--}}

    <section class="login-block">
        <!-- Container-fluid starts -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Authentication card start -->
                    <form class="md-float-material form-material" method="post"
                          action="{{ route('post:provider-admin:login') }}">
                        {{ csrf_field() }}
                        <div class="text-center webLogo">
                            @php $map_key = \App\model\GeneralSettings::first() @endphp
                            @if(isset($map_key) && $map_key->website_logo != Null)
                                <img src="{{ asset('assets/images/website-logo-icon/'.$map_key->website_logo)}}"
                                     alt="{{$map_key->website_logo}}">
                            @endif
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center txt-primary">Provider Admin</h3>
                                        <h3 class="text-center txt-primary">Sign In</h3>
                                    </div>
                                </div>
                                {{--<div class="row m-b-20">--}}
                                {{--<div class="col-md-6">--}}
                                {{--<a href="{{ url('/store-admin/auth/facebook') }}" class="btn btn-facebook m-b-20 btn-block"><i class="icofont icofont-social-facebook"></i> Facebook</a>--}}
                                {{--<button class="btn btn-facebook m-b-20 btn-block"><i class="icofont icofont-social-facebook"></i>facebook</button>--}}
                                {{--</div>--}}
                                {{--<div class="col-md-6">--}}
                                {{--<a href="{{ url('/store-admin/auth/google') }}" class="btn btn-google-plus m-b-20 btn-block"><i class="icofont icofont-social-google"></i> Google</a>--}}
                                {{--<button class="btn btn-google-plus m-b-20 btn-block">--}}
                                {{--<i class="icofont icofont-social-google"></i>Google--}}
                                {{--</button>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                {{--<p class="text-muted text-center p-b-5">Sign in with your regular account</p>--}}
{{--                                <div class="form-group form-primary">--}}
{{--                                    <input type="text" name="email" id="phone"--}}
{{--                                    class="form-control {{ $errors->first()!= Null ? "fill" : 'fill' }}" required="" autofocus>--}}
{{--                                    <span class="form-bar"></span>--}}
{{--                                    <label class="float-label">Email</label>--}}
{{--                                    <span class="error">{{ $errors->first('email') }}</span>--}}
{{--                                </div>--}}
                                <div class="form-group form-primary">
                                    <div class="input-group">
                                        <input type="text" name="contact_number" style="width: 100%;" minlength="11"
                                               class="form-control" required="" id="contact_number"
                                               maxlength="11" value="{{old('contact_number') }}"
                                               placeholder="Contact Number" >
                                    </div>
                                    <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                    <input type="hidden" id="country_code" name="country_code" value="" >
                                </div>
                                <div class="form-group form-primary">
                                    <input type="password" name="password" id="password"
                                           class="form-control {{ $errors->first()!= Null ? "fill" : '' }}" required="">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Password</label>
                                    <span class="error">{{ $errors->first('password') }}</span>
                                </div>
                                {{--<div class="row m-t-25 text-left">--}}
                                {{--<div class="col-12">--}}
                                {{--<div class="checkbox-fade fade-in-primary">--}}
                                {{--<label>--}}
                                {{--<input type="checkbox" value="">--}}
                                {{--<span class="cr"><i--}}
                                {{--class="cr-icon icofont icofont-ui-check txt-primary"></i></span>--}}
                                {{--<span class="text-inverse">Remember me</span>--}}
                                {{--</label>--}}
                                {{--</div>--}}
                                {{--<div class="forgot-phone text-right float-right">--}}
                                {{--<a href="auth-reset-password.html" class="text-right f-w-600"> Forgot--}}
                                {{--Password?</a>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit"
                                                class="btn btn-success btn-md btn-block waves-effect text-center m-b-20">
                                            LOGIN
                                        </button>

                                    </div>
                                </div>
                                <p class="text-inverse text-left">Don't have an account?
                                    <a href="{{ route('get:provider-admin:register') }}"><b style="color: #653996">Register here </b></a>for free!</p>
                            </div>
                        </div>
                    </form>
                    <!-- end of form -->
                </div>
                <!-- Authentication card end -->
            </div>
            <!-- end of col-sm-12 -->
        </div>
        <!-- end of row -->
        </div>
        <!-- end of container-fluid -->
    </section>
@endsection
@section('page-js')
    <script type="text/javascript" src="{{ asset('assets/js/country-code/intlTelInput.min.js')}}"></script>
    <script>
        var input = document.querySelector("#contact_number");
        var iti = window.intlTelInput(input, {
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
            initialCountry: "jp",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['us'],
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
        var country_code = iti.getSelectedCountryData()['dialCode']
        if(country_code > 0){
            country_code = "+"+country_code;
            document.getElementById("phone_error").innerHTML = '';
        }else{
            country_code = "+81";
            document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
        }
        $("#country_code").val(country_code);
        input.addEventListener("countrychange", function() {
            //console.log(iti.getSelectedCountryData()['dialCode']);
            var country_code = iti.getSelectedCountryData()['dialCode']
            if(country_code > 0){
                country_code = "+"+country_code;
                document.getElementById("phone_error").innerHTML = '';
            }else{
                country_code = "+81";
                document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
            }
            $("#country_code").val(country_code);
        });
        $("#contact_number").on('keyup', function (event) {
            var contact_number = $(this).val();
            // var n = contact_number.indexOf("0", 0);
            // // var n = contact_number.charAt(contact_number);
            // if (n == 0) {
            //     document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
            //     document.getElementById("contact_numbers").value = "";
            // } else {
            //     document.getElementById("contact_numbers").value = contact_number;
            //     document.getElementById("phone_error").innerHTML = '';
            //     console.log(contact_number);
            // }

            //check code for numeric value
            if (isNaN(contact_number)) {
                document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                document.getElementById("contact_numbers").value = "";
            } else {
                document.getElementById("contact_numbers").value = contact_number;
                document.getElementById("phone_error").innerHTML = '';
            }
        });
    </script>
@endsection

