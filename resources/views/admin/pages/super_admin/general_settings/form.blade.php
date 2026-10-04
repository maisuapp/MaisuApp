@extends('admin.layout.super_admin')
@section('title')
    Site Setting
@endsection
@section('page-css')
    <style>
        .image {
            padding-top: 0;
        }

        #image-preview-1, #image-preview-2 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #image-preview-1 input, #image-preview-2 input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }

        #image-preview-1 label, #image-preview-2 label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer !important;
            background-color: #000000d1;
            color: white;
            width: 200px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-transform: uppercase;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            text-align: center;
        }

        .res-banner-label {
            margin-top: -20px;
            margin-bottom: 15px;
        }

        @if(isset($general_settings) && $general_settings->website_logo != Null)
            #image-preview-1 {
            background: url({{ asset('/assets/images/website-logo-icon/'.$general_settings->website_logo) }}) no-repeat;
            width: 100%;
            height: 200px;
            background-size: 350px 115px;
            /*background-size: cover;*/
            background-position: center;
        }

        @endif
@if(isset($general_settings) && $general_settings->website_favicon != Null)
#image-preview-2 {
            background: url({{ asset('/assets/images/website-logo-icon/'.$general_settings->website_favicon) }}) no-repeat;
            width: 100%;
            height: 200px;
            /*background-size: cover;*/
            background-size: 100px 100px;
            /*background-attachment: fixed;*/
            background-position: center;
        }

        @endif

        .form-group {
            margin-bottom: 10px;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Site Setting</h5>
                            <span>@if(!isset($general_settings))Add @else Edit @endif Site Setting</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <form id="main" method="post" action="{{route('post:admin:update_general_setting')}}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            @if(isset($general_settings))
                                <input type="hidden" name="id" value="{{$general_settings->id}}">
                            @endif

                            <div class="card">
                                <div class="card-header">
                                    <h5>@if(!isset($general_settings))Add @else Edit @endif Site Setting</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row">
                                        <div class="form-group col-sm-12">
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Website Name:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control" name="website_name" required
                                                           id="website_name" placeholder="Website Name"
                                                           value="{{ (isset($general_settings)) ? $general_settings->website_name : old('website_name') }}">
                                                    <span class="error">{{ $errors->first('website_name') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6">
                                                    <div class="row">
                                                        <label class="col-sm-12 col-form-label image">Website
                                                            Logo:</label>
                                                        <div class="col-sm-12">
                                                            <div id="image-preview-1">
                                                                @if(isset($general_settings))
                                                                    <label for="image-upload-1" id="image-label">Change
                                                                        Logo</label>
                                                                    {{--<img id="pre-img-res" src="{{ asset('restaurant/'.$restaurant->image) }}">--}}
                                                                @else
                                                                    <label for="image-upload-1" id="image-label">Upload
                                                                        Logo</label>
                                                                @endif
                                                                <input type="file" id="image-upload-1"
                                                                       name="website_logo"/>
                                                            </div>
                                                            <span class="note">[Note: Upload only png file dimension between 300*300 to 500*500 & max size 100kb.]</span>
                                                            <span
                                                                    class="error">{{ $errors->first('website_logo') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="row">
                                                        <label class="col-sm-12 col-form-label image">Website
                                                            Favicon:</label>
                                                        <div class="col-sm-12">
                                                            <div id="image-preview-2">
                                                                @if(isset($general_settings))
                                                                    <label for="image-upload-2" id="image-label">Change
                                                                        Icon</label>
                                                                    {{--<img id="pre-img-res" src="{{ asset('restaurant/'.$restaurant->image) }}">--}}
                                                                @else
                                                                    <label for="image-upload-2" id="image-label">Upload
                                                                        Icon</label>
                                                                @endif
                                                                <input type="file" id="image-upload-2"
                                                                       name="website_favicon"/>
                                                            </div>
                                                            <span class="note">[Note: Upload only ico file dimension max 50*50 & max size 100kb.]</span>
                                                            <span
                                                                    class="error">{{ $errors->first('website_favicon') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Address:</label>
                                                <div class="col-sm-12">
                                                    <textarea name="address" id="address"
                                                              class="form-control"
                                                              placeholder="address">{{ (isset($general_settings)) ? $general_settings->address : old('address') }}</textarea>
                                                    <span class="error">{{ $errors->first('address') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Admin Receive Email:</label>
                                                        <div class="col-sm-12">
                                                            <input type="email" class="form-control" name="send_receive_email"
                                                                   id="send_receive_email" placeholder="Admin Receive Email"
                                                                   value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->send_receive_email) : old('send_receive_email') }}">
                                                            <span class="error">{{ $errors->first('send_receive_email') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Email:</label>
                                                        <div class="col-sm-12">
                                                            <input type="email" class="form-control" name="email"
                                                                   id="email" placeholder="Email"
                                                                   value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->email) : old('email') }}">
                                                            <span class="error">{{ $errors->first('email') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Contact No:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="contact_no"
                                                                   id="contact_no" placeholder="Contact No"
                                                                   value="{{ (isset($general_settings)) ? App\model\User::ContactNumber2Stars($general_settings->contact_no) : old('contact_no') }}">
                                                            <span class="error">{{ $errors->first('contact_no') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Copy Right
                                                            Content:</label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="copy_right"
                                                                   id="copy_right" placeholder="Copy Right Content"
                                                                   value="{{ (isset($general_settings)) ? $general_settings->copy_right : old('copy_right') }}">
                                                            <span class="error">{{ $errors->first('copy_right') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5>Social Link</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row">
                                        <div class="form-group col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Facebook Link:</label>
                                                        <div class="col-sm-12">
                                                            <input type="url" class="form-control" name="facebook_link"
                                                                   id="facebook_link" placeholder="Facebook Link"
                                                                   value="{{ (isset($general_settings)) ? $general_settings->facebook_link : old('facebook_link') }}">
                                                            <span class="error">{{ $errors->first('facebook_link') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Twitter Link:</label>
                                                        <div class="col-sm-12">
                                                            <input type="url" class="form-control" name="twitter_link"
                                                                   id="twitter_link" placeholder="Twitter Link"
                                                                   value="{{ (isset($general_settings)) ? $general_settings->twitter_link : old('twitter_link') }}">
                                                            <span class="error">{{ $errors->first('twitter_link') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Linkedin Link:</label>
                                                        <div class="col-sm-12">
                                                            <input type="url" class="form-control" name="linkedin_link"
                                                                   id="linkedin_link" placeholder="Linkedin Link"
                                                                   value="{{ (isset($general_settings)) ? $general_settings->linkedin_link : old('linkedin_link') }}">
                                                            <span class="error">{{ $errors->first('linkedin_link') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Instagram Link:</label>
                                                        <div class="col-sm-12">
                                                            <input type="url" class="form-control" name="instagram_link"
                                                                   id="instagram_link" placeholder="Instagram Link"
                                                                   value="{{ (isset($general_settings)) ? $general_settings->instagram_link : old('instagram_link') }}">
                                                            <span class="error">{{ $errors->first('instagram_link') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


{{--                            <div class="card">--}}
{{--                                <div class="card-header">--}}
{{--                                    <h5>Map And Firebase Key</h5>--}}
{{--                                </div>--}}
{{--                                <div class="card-block">--}}
{{--                                    <div class="form-group row">--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Map Key:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control" name="map_key"--}}
{{--                                                           id="map_key" placeholder="Map Key"--}}
{{--                                                           value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->map_key) : old('map_key') }}">--}}
{{--                                                    <span class="error">{{ $errors->first('map_key') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">FCM User Topic--}}
{{--                                                    Name:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="fcm_user_topic_name"--}}
{{--                                                           id="fcm_user_topic_name"--}}
{{--                                                           placeholder="FCM User Topic Name"--}}
{{--                                                           value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->fcm_user_topic_name) : old('fcm_user_topic_name') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('fcm_user_topic_name') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                        </div>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">FCM Server Key:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="fcm_server_key"--}}
{{--                                                           id="fcm_server_key" placeholder="FCM Server Key"--}}
{{--                                                           value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->fcm_server_key) : old('fcm_server_key') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('fcm_server_key') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">FCM Provider Topic--}}
{{--                                                    Name:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="fcm_provider_topic_name"--}}
{{--                                                           id="fcm_provider_topic_name"--}}
{{--                                                           placeholder="FCM Provider Topic Name"--}}
{{--                                                           value="{{ (isset($general_settings)) ? App\model\User::Email2Stars($general_settings->fcm_provider_topic_name) : old('fcm_provider_topic_name') }}">--}}
{{--                                                    <span--}}
{{--                                                        class="error">{{ $errors->first('fcm_provider_topic_name') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="card">
                                <div class="card-header">
                                    <h5>App-Store and Play-Store Link</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">User Playstore
                                                    Link:</label>
                                                <div class="col-sm-12">
                                                    <input type="url" class="form-control"
                                                           name="user_playstore_link"
                                                           id="user_playstore_link"
                                                           placeholder="User Playstore Link"
                                                           value="{{ (isset($general_settings)) ? $general_settings->user_playstore_link : old('user_playstore_link') }}">
                                                    <span
                                                            class="error">{{ $errors->first('user_playstore_link') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">User Appstore
                                                    Link:</label>
                                                <div class="col-sm-12">
                                                    <input type="url" class="form-control"
                                                           name="user_appstore_link"
                                                           id="user_appstore_link"
                                                           placeholder="User Appstore Link"
                                                           value="{{ (isset($general_settings)) ? $general_settings->user_appstore_link : old('user_appstore_link') }}">
                                                    <span
                                                            class="error">{{ $errors->first('user_appstore_link') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Provider Playstore
                                                    Link:</label>
                                                <div class="col-sm-12">
                                                    <input type="url" class="form-control"
                                                           name="provider_playstore_link"
                                                           id="provider_playstore_link"
                                                           placeholder="Provider Playstore Link"
                                                           value="{{ (isset($general_settings)) ? $general_settings->provider_playstore_link : old('provider_playstore_link') }}">
                                                    <span
                                                            class="error">{{ $errors->first('provider_playstore_link') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Provider Appstore
                                                    Link:</label>
                                                <div class="col-sm-12">
                                                    <input type="url" class="form-control"
                                                           name="provider_appstore_link"
                                                           id="provider_appstore_link"
                                                           placeholder="Provider Appstore Link"
                                                           value="{{ (isset($general_settings)) ? $general_settings->provider_appstore_link : old('provider_appstore_link') }}">
                                                    <span
                                                            class="error">{{ $errors->first('provider_appstore_link') }}</span>
                                                </div>
                                            </div>
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Shopper Playstore Link:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="url" class="form-control"--}}
{{--                                                           name="shopper_playstore_link"--}}
{{--                                                           id="shopper_playstore_link"--}}
{{--                                                           placeholder="Shopper Playstore Link"--}}
{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->shopper_playstore_link : old('shopper_playstore_link') }}">--}}
{{--                                                    <span class="error">{{ $errors->first('shopper_playstore_link') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5>User Refer Discount</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Used User
                                                    Discount:</label>
                                                <div class="col-sm-12">
                                                    <input type="number" class="form-control"
                                                           name="used_user_discount" step="0.01"
                                                           min="0"
                                                           id="used_user_discount"
                                                           placeholder="Used User Discount"
                                                           value="{{ (isset($general_settings)) ? $general_settings->used_user_discount : old('used_user_discount') }}">
                                                    <span
                                                            class="error">{{ $errors->first('used_user_discount') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Refer User
                                                    Discount:</label>
                                                <div class="col-sm-12">
                                                    <input type="number" class="form-control"
                                                           min="0"
                                                           name="refer_user_discount" step="0.01"
                                                           id="refer_user_discount"
                                                           placeholder="Refer User Discount"
                                                           value="{{ (isset($general_settings)) ? $general_settings->refer_user_discount : old('refer_user_discount') }}">
                                                    <span
                                                            class="error">{{ $errors->first('refer_user_discount') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Used User Discount
                                                    Type:</label>
                                                <div class="col-sm-12">
                                                    <select type="text" class="form-control"
                                                            name="used_user_discount_type"
                                                            id="used_user_discount_type"
                                                            placeholder="Used User Discount Type"
                                                            value="{{ (isset($general_settings)) ? $general_settings->used_user_discount_type : old('used_user_discount_type') }}">
                                                        <option value="">Select Used User Discount Type</option>
                                                        <option
                                                                value="1" {{ (isset($general_settings)) ? ($general_settings->used_user_discount_type == 1 ? "selected" : "") : "" }}>
                                                            Amount
                                                        </option>
                                                        <option
                                                                value="2" {{ (isset($general_settings)) ? ($general_settings->used_user_discount_type == 2 ? "selected" : "") : "" }}>
                                                            Percentage
                                                        </option>
                                                    </select>

                                                    <span
                                                            class="error">{{ $errors->first('used_user_discount_type') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-form-label">Refer User Discount
                                                    Type:</label>
                                                <div class="col-sm-12">
                                                    <select type="text" class="form-control"
                                                            name="refer_user_discount_type"
                                                            id="refer_user_discount_type"
                                                            placeholder="Refer User Discount Type">
                                                        <option value="">Select Refer User Discount Type
                                                        </option>
                                                        <option
                                                                value="1" {{ (isset($general_settings)) ? ($general_settings->refer_user_discount_type == 1 ? "selected" : "") : "" }}>
                                                            Amount
                                                        </option>
                                                        <option
                                                                value="2" {{ (isset($general_settings)) ? ($general_settings->refer_user_discount_type == 2 ? "selected" : "") : "" }}>
                                                            Percentage
                                                        </option>
                                                    </select>

                                                    <span
                                                            class="error">{{ $errors->first('refer_user_discount_type') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

{{--                            <div class="card">--}}
{{--                                <div class="card-header">--}}
{{--                                    <h5>Paypal Keys</h5>--}}
{{--                                </div>--}}
{{--                                <div class="card-block">--}}
{{--                                    <div class="form-group row">--}}
{{--                                        <div class="col-sm-12">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Paypal--}}
{{--                                                    Environment:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="paypal_sandbox"--}}
{{--                                                           id="paypal_sandbox"--}}
{{--                                                           placeholder="Paypal Environment"--}}
{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->paypal_sandbox : old('paypal_sandbox') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('paypal_sandbox') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            --}}{{--                                            <div class="form-group row">--}}
{{--                                            --}}{{--                                                <label class="col-sm-12 col-form-label">Paypal Merchant--}}
{{--                                            --}}{{--                                                    Id:</label>--}}
{{--                                            --}}{{--                                                <div class="col-sm-12">--}}
{{--                                            --}}{{--                                                    <input type="text" class="form-control"--}}
{{--                                            --}}{{--                                                           name="paypal_merchant_id"--}}
{{--                                            --}}{{--                                                           id="paypal_merchant_id"--}}
{{--                                            --}}{{--                                                           placeholder="Paypal Merchant Id"--}}
{{--                                            --}}{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->paypal_merchant_id : old('paypal_merchant_id') }}">--}}
{{--                                            --}}{{--                                                    <span--}}
{{--                                            --}}{{--                                                            class="error">{{ $errors->first('paypal_merchant_id') }}</span>--}}
{{--                                            --}}{{--                                                </div>--}}
{{--                                            --}}{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-sm-12">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <div class="col-sm-6">--}}
{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-12 col-form-label">Client ID:</label>--}}
{{--                                                        <div class="col-sm-12">--}}
{{--                                                            <input type="text" class="form-control" name="paypal_client_id"--}}
{{--                                                                   id="paypal_client_id" placeholder="Paypal Client ID"--}}
{{--                                                                   value="{{ (isset($general_settings)) ? $general_settings->paypal_client_id : old('paypal_client_id') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('paypal_client_id') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-sm-6">--}}
{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-12 col-form-label">Client Secret Key:</label>--}}
{{--                                                        <div class="col-sm-12">--}}
{{--                                                            <input type="text" class="form-control" name="paypal_client_secret_key"--}}
{{--                                                                   id="paypal_client_secret_key" placeholder="Paypal Client Secret Key"--}}
{{--                                                                   value="{{ (isset($general_settings)) ? $general_settings->paypal_client_secret_key : old('paypal_client_secret_key') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('paypal_client_secret_key') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="card">--}}
{{--                                <div class="card-header">--}}
{{--                                    <h5>Twilio Keys</h5>--}}
{{--                                </div>--}}
{{--                                <div class="card-block">--}}
{{--                                    <div class="form-group row">--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Twilio Service--}}
{{--                                                    Key:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="twilio_service_key"--}}
{{--                                                           id="twilio_service_key"--}}
{{--                                                           placeholder="Twilio Service Key"--}}
{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->twilio_service_key : old('twilio_service_key') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('twilio_service_key') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Twilio Auth--}}
{{--                                                    Token:</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="twilio_auth_token"--}}
{{--                                                           id="twilio_auth_token"--}}
{{--                                                           placeholder="Twilio Auth Token"--}}
{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->twilio_auth_token : old('twilio_auth_token') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('twilio_auth_token') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label">Twilio Verify Service--}}
{{--                                                    :</label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <input type="text" class="form-control"--}}
{{--                                                           name="twilio_verify_service_key"--}}
{{--                                                           id="twilio_verify_service_key"--}}
{{--                                                           placeholder="Twilio Verify Service Key"--}}
{{--                                                           value="{{ (isset($general_settings)) ? $general_settings->twilio_verify_service_key : old('twilio_verify_service_key') }}">--}}
{{--                                                    <span--}}
{{--                                                            class="error">{{ $errors->first('twilio_verify_service_key') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="card">
                                <div class="card-header">
                                    <h5>On-Demand Start Service Time</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group row">
                                        {{--                                        <label class="col-sm-12 col-form-label">Start Service--}}
                                        {{--                                            Time:</label>--}}
                                        <div class="col-sm-12">
                                            Provider can start the service before <input type="number" class="form-control"
                                                                                         name="on_demand_start_service_time" required
                                                                                         id="on_demand_start_service_time"
                                                                                         placeholder="Time"
                                                                                         value="{{ (isset($general_settings)) ? $general_settings->on_demand_start_service_time : old('on_demand_start_service_time') }}"
                                                                                         style="width: 30%; display: inline"> minutes
                                            of requested time.
                                            <span
                                                class="error">{{ $errors->first('on_demand_start_service_time') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-block">
                                    <div class="form-group row">
                                        <div class="col-sm-10">
                                            <button type="submit" class="btn btn-primary m-b-0">Save</button>
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
    <script src="{{ asset('assets/js/upload_image.js') }}"></script>
    <script type="text/javascript">
        // $(document).ready(function () {
        //     $.uploadPreview({
        //         input_field: "#image-upload",   // Default: .image-upload
        //         preview_box: "#upload-image-preview",  // Default: .image-preview
        //         label_field: "#image-label",    // Default: .image-label
        //         label_default: "Choose Image",   // Default: Choose File
        //         label_selected: "Change Image",  // Default: Change File
        //         no_label: false                 // Default: false
        //     });
        // });


        $(document).ready(function () {
            $.uploadPreview({
                input_field: "#image-upload-1", // Default: .image-upload
                preview_box: "#image-preview-1", // Default: .image-preview
                label_field: "#image-label-1", // Default: .image-label
                label_default: "Choose Image", // Default: Choose File
                label_selected: "Change Image", // Default: Change File
                no_label: false // Default: false
            });
            $.uploadPreview({
                input_field: "#image-upload-2", // Default: .image-upload
                preview_box: "#image-preview-2", // Default: .image-preview
                label_field: "#image-label-2", // Default: .image-label
                label_default: "Choose Image", // Default: Choose File
                label_selected: "Change Image", // Default: Change File
                no_label: false // Default: false
            });
        });
    </script>
    {{--<script type="text/javascript">--}}
    {{--$(document).ready(function () {--}}
    {{--$('#discount').on('click', function () {--}}
    {{--var discount_value = $(this).val();--}}
    {{--if (discount_value == 1) {--}}
    {{--$(this).val(0);--}}
    {{--$(this).removeAttr("checked");--}}
    {{--$('.discount').hide();--}}
    {{--$('#discount_amount').removeAttr("required");--}}
    {{--$('#discount_type').removeAttr("required");--}}
    {{--}--}}
    {{--else {--}}
    {{--$(this).attr("checked", "checked");--}}
    {{--$(this).val(1);--}}
    {{--$('.discount').show();--}}
    {{--$('#discount_amount').attr("required", "required");--}}
    {{--$('#discount_type').attr("required", "required");--}}
    {{--}--}}
    {{--});--}}
    {{--$('#food_type').on('click', function () {--}}
    {{--var food_type = $(this).val();--}}
    {{--if (food_type == 1) {--}}
    {{--$(this).val(2);--}}
    {{--}--}}
    {{--else {--}}
    {{--$(this).val(1);--}}
    {{--}--}}
    {{--});--}}
    {{--});--}}
    {{--</script>--}}
@endsection

