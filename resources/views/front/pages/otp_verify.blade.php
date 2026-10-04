@extends('front.layout.default')
@section('title')
    <title>Otp Verify</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Restaurant">
    <meta name="keywords" content="Restaurant">
@endsection
@section('page-css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/country-code/intlTelInput.css')}}">
<style>
    #phone:focus {
        border-bottom: 2px solid #00aff0
    }
    #phone {
        padding-left: 75px !important;
    }
    body {
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: #eee8e3;
    }
    #contact{
        margin-top: 500px;
     }
    #opt .resp-tab-item {
        width: 100% !important;
    }
</style>
@endsection
<!--user login register start-->
@section('page-content')
<div id="opt" class="modal">

    <div class="user">
        <span onclick="return logoutUser()" class="close" title="Close">&times;</span>
        <div class="col-md-6 col-md-offset-3 loginform">
            <ul class="resp-tabs-list">
                <li class="resp-tab-item optMainTitle" id="btn-login-reload-1"><span>OTP Verification</span></li>
            </ul>
            <div class="resp-tabs-container">
                <div class="tab-1 resp-tab-content" id="btn-login-reload-2">
                    <div class="login-top">
                        <form method="POST" name="otpVerify"
                              enctype="multipart/form-data" id="otpVerify">
                            {{ csrf_field() }}
                            <input type="hidden" class="form-control " name="user_id"
                                    id="user_id"
                                   value="{{ Illuminate\Support\Facades\Auth::guard('user')->user()->id }}">


                            <div class="col-sm-6 row">
                                <div class="form-group row">
                                    <label for="store_signup_name" class="col-sm-12 col-form-label">Enter OTP : <span class="red-star">*</span></label>
                                    <div class="col-md-12">
                                        <input type="text" name="otp" class="form-control" placeholder="Enter 4 Digit Otp" value="" autocomplete="off" required/>
                                    </div>
                                </div>
                            </div>

                            <div class="login-bottom">
                                <ul>
                                    {{--<li>--}}
                                    {{--<input type="checkbox" id="brand" value="">--}}
                                    {{--<label for="brand"><span></span> Remember me?</label>--}}
                                    {{--</li>--}}
                                    <li style="margin-bottom: 2%;">
                                        <a href="javascript:void(0)" class="resendOtp" >Resend OTP</a>
                                    </li>
                                </ul>
                                <div class="clear"></div>
                            </div>
                            <input type="submit" id="btn_verify_otp" value="Verify">
                        </form>
                        <div class="clearfix"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
@endsection
<!--user login register end-->
@section('page-js')
<script>
    $(document).ready(function() {
        $("#opt").css("display","block");

        $(document).on('click', '#btn_verify_otp', function (e) {
            e.preventDefault();

            // form.append('_token ', token);

            var form = $("#otpVerify");
            // console.log(form.serialize());
            frmdata = form.serialize();

            $.ajax({
                url: '{{ route('post:otp-verify') }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: frmdata,
                cache: false,
                async:false,
                /*contentType: false,
                processData: false,*/
                success: function (data) {
                    console.log(data);
                    if (data.success == true) {
                        url = '{{ url()->previous() }}';
                        window.location.href = url;
                        $('.bootstrap-growl').remove();
                        /*$.bootstrapGrowl(data.message, // Messages
                            { // options
                                type: "success", // info, success, warning and danger
                                ele: "body", // parent container
                                offset: {
                                    from: "top",
                                    amount: 20
                                },
                                align: "right", // right, left or center
                                width: 300,
                                delay: 5000,
                                allow_dismiss: true, // add a close button to the message
                                stackup_spacing: 10
                            });*/
                    }
                    else {
//                    $('#signupform .error').text(data.error);
                        $(".bootstrap-growl .close").click();
                        $('.bootstrap-growl').remove();
                        $.bootstrapGrowl(data.message, // Messages
                            { // options
                                type: "danger", // info, success, warning and danger
                                ele: "body", // parent container
                                offset: {
                                    from: "top",
                                    amount: 20
                                },
                                align: "right", // right, left or center
                                width: 300,
                                delay: 5000000000,
                                allow_dismiss: true, // add a close button to the message
                                stackup_spacing: 10
                            });
                    }
                }
            });

        });

        //resend opt code

        $(document).on('click', '.resendOtp', function (e) {
            e.preventDefault();

            var form = $("#otpVerify");
            // console.log(form.serialize());
            frmdata = form.serialize();

            $.ajax({
                url: '{{ route('post:resend-otp') }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: frmdata,
                cache: false,
                async:false,
                /*contentType: false,
                processData: false,*/
                success: function (data) {
                    console.log(data);
                    if (data.success == true) {

                        $(".bootstrap-growl .close").click();
                        $('.bootstrap-growl').remove();
                        $.bootstrapGrowl(data.message, // Messages
                            { // options
                                type: "success", // info, success, warning and danger
                                ele: "body", // parent container
                                offset: {
                                    from: "top",
                                    amount: 20
                                },
                                align: "right", // right, left or center
                                width: 300,
                                delay: 5000,
                                allow_dismiss: true, // add a close button to the message
                                stackup_spacing: 10
                            });
                    }
                    else {
//                    $('#signupform .error').text(data.error);

                        $(".bootstrap-growl .close").click();
                        $('.bootstrap-growl').remove();
                        $.bootstrapGrowl(data.message, // Messages
                            { // options
                                type: "danger", // info, success, warning and danger
                                ele: "body", // parent container
                                offset: {
                                    from: "top",
                                    amount: 20
                                },
                                align: "right", // right, left or center
                                width: 300,
                                delay: 5000,
                                allow_dismiss: true, // add a close button to the message
                                stackup_spacing: 10
                            });
                    }
                }
            });

        });
    });
    function logoutUser() {
        url = '{{ route('user:logout',[ 'user' ]) }}';
        window.location.href = url;
    }
</script>
@endsection
