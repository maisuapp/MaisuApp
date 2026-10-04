@extends('admin.layout.auth')
@section('title')
    Provider Register
@endsection
@section('page-css')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <style>
        .login-block .auth-box {
            max-width: 650px;
        }
        .error {
            color: red;
        }
        .btn-success {
            background-color: #653996 !important;
            border-color: #653996 !important;
        }

        .btn-success:hover, .btn-success:active, .btn-success:focus {
            background-color: #653996 !important;
            border-color: #653996 !important;
            box-shadow: 0 0 0 0.2rem #65399680 !important;
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
    <section class="login-block">
        <!-- Container-fluid starts -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Authentication card start -->
                    <form class="md-float-material form-material" action="{{ route('post:provider-admin:register') }}"
                          method="post">
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
                                        <h3 class="text-center txt-primary">Sign up As On-demand Provider</h3>
                                    </div>
                                </div>
                                {{--<div class="row m-b-20">--}}
                                {{--<div class="col-md-6">--}}
                                {{--<a href="#!" class="btn btn-facebook m-b-20 btn-block waves-effect waves-light"><i class="fab fa-facebook-f"></i> Login with Facebook</a>--}}
                                {{--</div>--}}
                                {{--<div class="col-md-6">--}}
                                {{--<a href="#!" class="btn btn-google-plus m-b-0 btn-block waves-effect waves-light">--}}
                                {{--<i class="fab fa-google"></i> Sign in with Google</a>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="email" name="email"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required=""
                                                   value="{{old('email') }}">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Email</label>
                                            <span class="error">{{ $errors->first('email') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" name="contact_number" id="phone" required
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   >
                                            <input type="hidden" id="contact_numbers" name="contact_numbers">
                                            <input type="hidden" id="country_code" name="country_code"
                                                   value="+81">
                                            <span class="form-bar"></span>
                                            {{--<label class="float-label">Contact Number</label>--}}
                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                            <span class="error">{{ $errors->first('full_number') }}</span>
                                            <span class="error">{{ $errors->first('contact_numbers') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" name="name"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required=""
                                                   value="{{old('name') }}">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Name</label>
                                            <span class="error">{{ $errors->first('name') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <div class="row"
                                                 style="margin:0;border-bottom: 1px solid #ccc">
                                                <label class="col-sm-4 col-form-label"
                                                       style="padding-left: 0 ; padding-top: 10px">Gender:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <div class="form-radio">

                                                        <div class="row">
                                                            <div class="radio radio-inline"
                                                                 style="padding-left: 0 ; padding-top: 7px">
                                                                <label>
                                                                    <input type="radio" value="1" required
                                                                           {{old('gender') == '1' ? "checked ": "" }}
                                                                           name="gender" {{ (isset($user_details)) ? ($user_details->gender == 1)? "checked ": "" : ""}}>
                                                                    <i class="helper"></i>Male
                                                                </label>
                                                            </div>
                                                            <div class="radio radio-inline"
                                                                 style="padding-left: 0 ; padding-top: 7px">
                                                                <label>
                                                                    <input type="radio" value="2" required
                                                                           {{old('gender') == '2' ? "checked ": "" }}
                                                                           name="gender" {{ (isset($user_details)) ? ($user_details->gender == 2)? "checked ": "" : ""}}>
                                                                    <i class="helper"></i>Female
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="error">{{ $errors->first('gender') }}</span>
                                                <span class="form-bar"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="password" name="password" minlength="6" maxlength="18"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required="" value="{{ old('confirm_password') }}">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Password</label>
                                            <span class="error">{{ $errors->first('password') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="password" name="confirm_password" minlength="6" maxlength="18"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required="" value="{{ old('confirm_password') }}">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Confirm Password</label>
                                            <span class="error">{{ $errors->first('confirm_password') }}</span>
                                        </div>
                                    </div>
                                </div>
                                {{--<div class="row m-t-25 text-left">--}}
                                {{--<div class="col-md-12">--}}
                                {{--<div class="checkbox-fade fade-in-primary">--}}
                                {{--<label>--}}
                                {{--<input type="checkbox" value="">--}}
                                {{--<span class="cr"><i--}}
                                {{--class="cr-icon icofont icofont-ui-check txt-primary"></i></span>--}}
                                {{--<span class="text-inverse">I read and accept <a href="#">Terms &amp; Conditions.</a></span>--}}
                                {{--</label>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="col-md-12">--}}
                                {{--<div class="checkbox-fade fade-in-primary">--}}
                                {{--<label>--}}
                                {{--<input type="checkbox" value="">--}}
                                {{--<span class="cr"><i--}}
                                {{--class="cr-icon icofont icofont-ui-check txt-primary"></i></span>--}}
                                {{--<span class="text-inverse">Send me the <a--}}
                                {{--href="#">Newsletter</a> weekly.</span>--}}
                                {{--</label>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button class="btn btn-success provider-registration btn-md btn-block waves-effect text-center m-b-20">
                                            Sign up now
                                        </button>
                                    </div>
                                </div>
                                <p class="text-inverse text-left">Already have an account?
                                    <a href="{{ route('get:provider-admin:login') }}">
                                        <b style="color: #653996">Sign In </b></a>here!</p>
                            </div>
                        </div>
                    </form>
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
            initialCountry: "jp",
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
                    country_code = "+81";
                    document.getElementById("phone_error").innerHTML = 'Invalid Country Code';
                }
                $("#country_code").val(country_code);
            });
            $("#phone").on('keyup', function (event) {
                console.log(event.value)
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

                if (contact_number == null || contact_number == ""){
                    document.getElementById("phone_error").innerHTML = 'Contact number required.';
                    document.getElementById("contact_numbers").value = "";
                    $('.provider-registration').prop('disabled',true)
                }
                //check code for numeric value
                if (isNaN(contact_number)) {
                    document.getElementById("phone_error").innerHTML = 'Invalid Contact Number';
                    document.getElementById("contact_numbers").value = "";
                    $('.provider-registration').prop('disabled',true)
                } else {
                    document.getElementById("phone_error").innerHTML = '';
                    if (contact_number.length < 11){
                        document.getElementById("contact_numbers").value = "";
                        $('.provider-registration').prop('disabled',true)
                        document.getElementById("phone_error").innerHTML = 'Please lengthen this text till 11 digits';
                        // event.target.setCustomValidity('Please lengthen this text till 11 digits.')
                    } else if (contact_number.length === 11){
                        // event.target.setCustomValidity('')
                        document.getElementById("phone_error").innerHTML = '';
                        document.getElementById("contact_numbers").value = contact_number;
                        $('.provider-registration').prop('disabled',false)
                    } else {
                        document.getElementById("phone_error").innerHTML = 'Only 11 digit allowed';
                        $('.provider-registration').prop('disabled',true)
                        // event.target.setCustomValidity('Only 11 digit allowed')
                    }
                }
            });
        });

        // $('.provider-registration').click(function (event){
        //     console.clear()
        //     document.getElementById("phone_error").innerHTML = '';
        //     let contactNumber = $('#contact_numbers').val();
        //     console.log(contactNumber)
        //     if (contactNumber == null || contactNumber == ""){
        //         event.preventDefault()
        //         // return document.getElementById("phone_error").innerHTML = 'Contact number required';
        //     }
        // })

    </script>
@endsection
