@extends('admin.layout.store_admin')
@section('title')
    Account Verification Pending
@endsection
@section('page-css')
    @if(Illuminate\Support\Facades\Auth::guard('store')->user()->status == 3)
    <style>
        .pcoded-content{
            margin-left: 0 !important;
        }
    </style>
    @endif
    @if(Illuminate\Support\Facades\Auth::guard('store')->user()->verified_at == Null)
        <style>
            .pcoded-content{
                margin-left: 0 !important;
            }
        </style>
    @endif
    <style>
        .otp {
            width: 35px;
            display: inline-block;
        }
        .otp::-webkit-outer-spin-button,
        .otp::-webkit-inner-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            margin: 0;
        }
    </style>
@endsection
@section('page-content')
    {{--sidebar start--}}
    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="page-header-title">

                        <form id="main" method="post"
                              action="{{ route('post:store-admin:account_verification_approval')}}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            <center>
                                <div class="alert alert-success background-success"
                                     style="width: 65%;margin-bottom: 1rem">
                                    We have sent verification OTP to your register contact number. Please send OTP and
                                    verify your
                                    account.
                                </div>

                            </center>
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <center>
                                        <input type="hidden" class="form-control " name="store_id"
                                               required id="store_name"
                                               placeholder="*" maxlength="1"
                                               value="{{ Illuminate\Support\Facades\Auth::guard('store')->user()->id }}">
                                        <input type="number" class="form-control otp" name="otp_1" required id="store_name" autofocus placeholder="*" maxlength="1"
                                               value="1" autocomplete="off"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  onKeyPress="if(this.value.length==1) return false;">
                                        <input type="number" class="form-control otp" name="otp_2" required id="store_name" placeholder="*" maxlength="1"
                                               value="2" autocomplete="off"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  onKeyPress="if(this.value.length==1) return false;">
                                        <input type="number" class="form-control otp" name="otp_3" required id="store_name" placeholder="*" maxlength="1"
                                               value="3" autocomplete="off"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  onKeyPress="if(this.value.length==1) return false;">
                                        <input type="number" class="form-control otp" name="otp_4" required id="store_name" placeholder="*" maxlength="1"
                                               value="4" autocomplete="off"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  onKeyPress="if(this.value.length==1) return false;">
                                    </center>
                                </div>
                            </div>
                            <center>
                                <button type="submit" class="btn btn-primary btn-round waves-effect waves-light">
                                    Verification OTP
                                </button>
                                <a href="{{ route('get:store-admin:resend_verification_link') }}"
                                   class="btn btn-primary btn-round waves-effect waves-light">Resend Verification OTP</a>
                            </center>
                        </form>

{{--                        <div class="alert alert-success background-success">--}}
{{--                            We have sent verification link to your email address. Please click the link and verify your--}}
{{--                            account.--}}
{{--                        </div>--}}
{{--                        <div class="alert alert-success background-success">--}}
{{--                            If you have not received the email, click on the below link to resend the verification email--}}
{{--                            Resend Verification Email--}}
{{--                        </div>--}}
{{--                        <center>--}}
{{--                            <a href="{{ route('get:store-admin:resend_verification_link') }}"--}}
{{--                               class="btn btn-primary btn-round waves-effect waves-light">Resend Verification Link</a>--}}
{{--                        </center>--}}
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->
    </div>
    <!-- [ style Customizer ] start -->
    <div id="styleSelector">
    </div>
    <!-- [ style Customizer ] end -->
    {{--sidebar end--}}

@endsection
@section('page-js')

@endsection

