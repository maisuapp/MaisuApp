@extends('admin.layout.auth')
@section('title')
    Verify Coupon Code
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
            background-color: #00aff0;
            border-color: #00aff0;
        }

        .btn-success:hover, .btn-success:active, .btn-success:focus {
            background-color: #6cc8ff;
            border-color: #6cc8ff;
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
                    <form class="md-float-material form-material" action="{{ route('post:couponcode_verify') }}"
                          method="post">
                        {{ csrf_field() }}
                        <div class="text-center">
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
                                        <h3 class="text-center txt-primary">Coupon Code Verify </h3>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="text" name="coupon_code"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required
                                                   value="{{old('coupon_code') }}">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Coupon Code</label>
                                            <span class="error">{{ $errors->first('coupon_code') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="tel" name="contact_number" id="phone"
                                                   class="form-control {{ $errors->first()!= Null ? "fill" : '' }}"
                                                   required>
                                            <input type="hidden" id="contact_numbers" name="contact_numbers">
                                            <span class="form-bar"></span>
                                            {{--<label class="float-label">Contact Number</label>--}}
                                            <span id="phone_error" class="error">{{ $errors->first('contact_number') }}</span>
                                            <span class="error">{{ $errors->first('full_number') }}</span>
                                            <span class="error">{{ $errors->first('contact_numbers') }}</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button class="btn btn-success btn-md btn-block waves-effect text-center m-b-20">
                                            Verify
                                        </button>
                                    </div>
                                </div>

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
        window.intlTelInput(input, {
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
            initialCountry: "vn",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            // preferredCountries: ['cn', 'jp'],
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
    </script>
@endsection
