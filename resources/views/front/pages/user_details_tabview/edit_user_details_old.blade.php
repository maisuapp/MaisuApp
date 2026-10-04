{{--user details--}}
<div class="content-container">

    <div class="container-fluid">
        <div class="col-md-12 col-xs-12 user_details">
            <div class="title">
                <h2>Edit Profile</h2>
            </div>

            <div class="edit_user_details">
                <form action="{{ route('post:update_user_profile_details') }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="profile_img">
                        @if(Illuminate\Support\Facades\Auth::guard("user")->user()->avatar != '' && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "google" && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "facebook" )
                            <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/'.Illuminate\Support\Facades\Auth::guard("user")->user()->avatar) }}">
                        @elseif(Illuminate\Support\Facades\Auth::guard("user")->user()->avatar == '' )
                            <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/default.png') }}">
                        @else
                            @if(filter_var(Illuminate\Support\Facades\Auth::guard("user")->user()->avatar, FILTER_VALIDATE_URL) !== false)
                                <img id="upload-preview" src="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->avatar }}">
                            @else
                                <img id="upload-preview" src="{{ asset('/assets/images/profile-images/customer/'.Illuminate\Support\Facades\Auth::guard("user")->user()->avatar) }}">
                            @endif
                        @endif


                        <center>
                            {{--<form id="uploadForm" action="{{ route('post:upload_user_profile_image') }}" method="post">--}}
                            <input id='profile-avtar' name="user_avtar" type='file' accept=".png, .jpg, .jpeg"/>
                            <input id='button-profile-avtar' class="btn btn-default" type='button'
                                   value="New Image"/>
                            <input id='remove-profile-avtar' class="btn btn-default" type='button'
                                   value="Remove"/>
                            {{--</form>--}}
                        </center>
                    </div>
                    <div class="change_details">
                        <div class="form-group">
                            <label for="user_name">User Name:</label>
                            <input type="text" class="form-control" id="user_name" name="name" value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->first_name.' '.Illuminate\Support\Facades\Auth::guard("user")->user()->last_name }}">
                        </div>
                        <div class="form-group">
                            <label for="email_id">Email Id:</label>
                            @if(Illuminate\Support\Facades\Auth::guard("user")->user()->login_type == "google" )
                                <input type="email" disabled class="form-control" id="email_id"
                                       value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->email }}">
                            @elseif( Illuminate\Support\Facades\Auth::guard("user")->user()->login_type == "facebook")
                                <input type="email" disabled class="form-control" id="email_id"
                                       value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->email }}">
                            @else
                                <input type="email" disabled class="form-control" name="email" id="email_id"
                                       value="{{ isset(Illuminate\Support\Facades\Auth::guard("user")->user()->email) ? Illuminate\Support\Facades\Auth::guard("user")->user()->email : old('email') }}">
                            @endif
                            <span class="error" id="email-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="email_id">Contact Details:</label>
                            <input type="text" disabled class="form-control" id="contact_details" name="contact_details"
                                   value="{{ Illuminate\Support\Facades\Auth::guard("user")->user()->country_code.Illuminate\Support\Facades\Auth::guard("user")->user()->contact_number }}">
                        </div>

                        <center>

                            <div class="address-detail">
                                <input type="submit" class="add-address-btn address-btn detail-submit" value="Update">
                            </div>
                        </center>
                    </div>
                </form>
            </div>
        </div>
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

    @if(Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "facebook" && Illuminate\Support\Facades\Auth::guard("user")->user()->login_type != "google")
    $('#email_id').on('focus', function () {
        $('.detail-submit').hide();
    });

    $('#email_id').focusout(function () {

        var email_default = "{{ Illuminate\Support\Facades\Auth::guard("user")->user()->email }}";
        var new_email = $(this).val();

        if (email_default == new_email) {
            $('.detail-submit').show();
        }
        else {
            $('.detail-submit').show();
        }
        errorEmail();

    });

    function errorEmail() {
        var error = $('#email-error').text();
        console.log(error);
        if (error != '') {
            $('.detail-submit').hide();
        }
    }

    $('#email_id').change(function () {
        var email = $(this).val();
        var user_id = "{{Illuminate\Support\Facades\Auth::guard("user")->user()->id}}";

        $.ajax({
            type: 'get',
            data: {email: email, user_id: user_id, check_for: "edit", id: "user"},

            url: '{{ route('post:check_email') }}',
            success: function (response) {
                if (response == 'taken') {
                    $('#email_id').siblings("span").text('Sorry... Email already taken');
                }
                else if (response == 'not_taken') {
                    $('#email_id').siblings("span").text('');
                    $('.detail-submit').show();
                }
            }
        });
    });
    @endif

    document.getElementById('button-profile-avtar').addEventListener('click', openDialog);

    function openDialog() {
        document.getElementById('profile-avtar').click();
    }

    var imgtag = "#upload-preview";

    function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $(imgtag).show();
                $(imgtag).attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#profile-avtar").change(function () {
        readURL(this);
    });

    $('#remove-profile-avtar').on('click', function () {
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
    });
</script>
