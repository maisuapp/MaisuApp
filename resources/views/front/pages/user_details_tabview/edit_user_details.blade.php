<div class="card">
    <h5 class="card-header ">My Profile </h5>
    <div class="card-body">
        <form action="{{ route('post:update_user_profile_details') }}" name="edit_profile_data" id="edit_profile_data" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-row">
                <div class="form-group mx-auto ">
                    <div class="profile-img-container">
                        @if( Illuminate\Support\Facades\Auth::guard("user")->user()->avatar != '' && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "google" && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "facebook" )

                            @if(file_exists(public_path('/assets/images/profile-images/customer/'.Illuminate\Support\Facades\Auth::guard("user")->user()->avatar)))
                                <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/'.Illuminate\Support\Facades\Auth::guard("user")->user()->avatar) }}" height="100" width="100" class="img-thumbnail rounded-circle img-responsive">
                            @else
                                <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/user.png') }}" height="100" width="100" class="img-thumbnail rounded-circle img-responsive">
                            @endif

                        @else( Illuminate\Support\Facades\Auth::guard("user")->user()->avatar == '' )
                            <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/user.png') }}" height="100" width="100" class="img-thumbnail rounded-circle img-responsive">
                        @endif
                        <input class="filebox"   name="user_avtar" type='file' accept=".png, .jpg, .jpeg">
                        <i class="fa fa-upload"></i>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label class="card-label" for="firstname">First Name</label>
                    <input type="text" class="form-control" id="name" name="name"  value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->first_name}}">
                </div>
<!--                <div class="form-group col-md-6">
                    <label class="card-label"for="lastname">Last Name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname"  value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->last_name }}">
                </div>-->
            </div>
             <div class="form-row">
                <div class="form-group col-12">
                    <label class="card-label" for="email">Email</label>
                    @if(Illuminate\Support\Facades\Auth::guard("user")->user()->login_type == "google" )
                        <input type="email" disabled class="form-control" id="email" name="email" placeholder=""
                               value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->email }}">
                    @elseif( Illuminate\Support\Facades\Auth::guard("user")->user()->login_type == "facebook")
                        <input type="email" disabled class="form-control" id="email" name="email" placeholder=""
                               value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->email }}">
                    @else
                        <input type="email" class="form-control" id="email" name="email" placeholder=""
                               value="{{ isset(Illuminate\Support\Facades\Auth::guard("user")->user()->email) ? Illuminate\Support\Facades\Auth::guard("user")->user()->email : old('email') }}">
                    @endif
                </div>
            </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="card-label" for="contact_num">Contact Number</label>
                        <input type="text" class="form-control" id="contact_details" name="contact_details"
                               value="{{Illuminate\Support\Facades\Auth::guard("user")->user()->country_code.Illuminate\Support\Facades\Auth::guard("user")->user()->contact_number }}">
                        <input type="hidden" id="contact_numbers" name="contact_numbers" class="contact_numbers" value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->contact_number }}">
                        <input type="hidden" id="country_code" name="country_code" class="country_code" value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->country_code }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="card-label" for="emergency_contact_number">Emergency Contact Number</label>
                        <input type="text" class="form-control" id="emergency_contact_number" name="emergency_contact_number" placeholder=""
                               value="{{Illuminate\Support\Facades\Auth::guard("user")->user()->emergency_contact }}"
                        >
                    </div>
            </div>
            <div class="form-row">
                <div class="form-group mx-auto col-md-6 ">
                    <button type="submit" class="btn btn-block btn-outline-secondary btnColor  ">Update</button>

                </div>
            </div>
        </form>
    </div>
</div>
<script>
   var input = document.querySelector("#contact_details");
    var iti = window.intlTelInput(input, {
        // allowDropdown: false,
        // autoHideDialCode: false,
        // autoPlaceholder: "off",
        // dropdownContainer: document.body,
        // excludeCountries: ["us"],
        formatOnDisplay: false,
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
   $(document).ready(function () {
       input.addEventListener("countrychange", function() {
           console.log(iti.getSelectedCountryData()['dialCode']);
           var country_code = iti.getSelectedCountryData()['dialCode']
           if(country_code > 0){
               country_code = "+"+country_code;
           }else{
               country_code = "+1";
           }
           console.log(country_code);
           $(".country_code").val(country_code);
       });
       $("#contact_details").on('keyup', function (event) {
           var contact_number = $(this).val();
           console.log(contact_number);
           if (isNaN(contact_number)) {
               // document.getElementById("contact_numbers").value = "";
               contact_number = '';
               $(".contact_numbers").val(contact_number);
           } else {
               // document.getElementById("contact_numbers").value = contact_number;
               $(".contact_numbers").val(contact_number);
           }
       });
   });

    var input_ = document.querySelector("#emergency_contact_number");
    var iti_ = window.intlTelInput(input_, {
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
        hiddenInput: "full_number_emergency",
        initialCountry: "us",
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




    /*$('#remove-profile-avtar').on('click', function () {
        $.ajax({
            url: "{{ route('post:delete_user_profile_image') }}",
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            ache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.success == true) {
                    location.reload();
                }
                else {
                    location.reload();
                }
            }
        });
    });*/
</script>
<script src="{{ asset('assets/js/validation/jquery.validate.js')}}" type="text/javascript"></script>
<script>
    $(document).ready(function (){
        jQuery.validator.addMethod("emailfull", function(value, element) {
            return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
        }, "Please enter valid email address!");


        $("#edit_profile_data").validate({
            rules: {
                name: {
                    required : true,
                },
                lastname: {
                    required : true,
                },
                email: {
                    required : true,
                    emailfull : true,
                },
                contact_details: {
                    required : true,
                    number : true,
                },
                emergency_contact_number: {
                    number : true,
                },


            },
            messages: {
                name: {
                    required :"Please enter first name",
                },
                lastname: {
                    required :"Please enter last name",
                },
                email: {
                    required :"Please enter email",
                    emailfull :"Please enter valid email address!",
                },
                contact_details: {
                    required :"Please enter contact number",
                    number :"Please enter valid number",
                },
                emergency_contact_number: {
                    number :"please enter valid number",
                },

            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "contact_details" || element.attr("name") == "emergency_contact_number"  ) {
                    error.insertAfter( element.parent("div .iti"));
                } else {
                     error.insertAfter(element);
                }

            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
