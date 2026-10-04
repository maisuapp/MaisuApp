@extends('admin.layout.store_admin')
@section('title')
    @if(!isset($store_details))
        Add
    @else
        Edit
    @endif
    Store Details
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/mdtimepicker.min.css')}}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.steps.css')}}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/assets/css/widget/widget.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/css/file-upload/jquery.filer.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/css/file-upload/jquery.filer-dragdropbox-theme.css') }}">
    <style>
        .wizard > .content {
            background: #ffffff;
        }

        .pcoded[theme-layout="vertical"][vertical-placement="left"][vertical-nav-type="expanded"][vertical-effect="shrink"] .pcoded-content {
            margin-left: 0;
        }

        .form-control:disabled, .form-control[readonly] {
            background: transparent;
        }

        .wizard > .content > .body {
            position: relative;
            height: auto;
            width: 100%;
        }

        .wizard > .content {
            min-height: 230px;
        }

        .wizard > .content > .body input {
            border: 1px solid #ccc !important;
        }

        .image {
            padding-top: 0;
        }

        #image-preview-1 {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 220px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #image-preview-2 {
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
        .wizard > .content > .body label.error{
            margin-left: 0px;
        }
        @if(isset($required_document))
        @foreach($required_document as $documents)
        @foreach($documents as $document)

        #document-preview-{{$document->id}}             {
            border: 1px solid #9e9e9e;
            width: 100%;
            height: 220px;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            color: #ecf0f1;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #document-preview-{{$document->id}} input, #document-preview-{{$document->id}} input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }

        #document-preview-{{$document->id}} label, #document-preview-{{$document->id}} label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer !important;
            background-color: #000000d1;
            color: white;
            width: 200px;
            height: 50px;
            font-size: 16px;
            line-height: 50px;
            text-transform: uppercase;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            text-align: center;
        }

        @endforeach
        @endforeach
        @endif

        .radio, .radio label {
            cursor: pointer;
        }

        .wizard > .content > .body input[type="checkbox"] {
            display: none;
        }

        .border-checkbox-section {
            margin: 0;
        }

        .every_d_opn_time, .every_d_cls_time {
            margin-top: 5px;
        }

        .border-checkbox-section .border-checkbox-group .border-checkbox-label:before {
            top: 11px;
        }

        .border-checkbox-section .border-checkbox-group .border-checkbox-label:after {
            top: 21px;
        }

        .comp-card i {
            width: 35px !important;
            height: 35px !important;
            padding: 8px 0 !important;
        }

        .comp-card:hover i {
            border-radius: 5px !important;
        }

        .comp-card i:hover {
            border-radius: 50% !important;
        }

        .document_view, .document_status {
            cursor: pointer;
        }

        .doc_service_cat {
            display: none;
        }

        .input-controls {
            margin-top: 10px;
            border: 1px solid transparent;
            border-radius: 2px 0 0 2px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            height: 32px;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        #searchInput {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            margin-left: 12px;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 50%;
        }

        #searchInput:focus {
            border-color: #4d90fe;
        }
    </style>

@endsection
@section('page-content')
    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="fas fa-store bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Store Registration Details</h5>
                            <span>New Store Details</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <!-- Form wizard with validation card start -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Required Store Registration Details</h5>
                                        <span>Add class of <code>.form-control</code> with <code>&lt;input&gt;</code> tag</span>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div id="wizard">
                                                    <section>
                                                        <form class="wizard-form" id="example-advanced-form"
                                                              action="{{ route('post:store-admin:service-register') }}"
                                                              method="post" enctype="multipart/form-data">
                                                            {{csrf_field() }}
                                                            <input type="hidden" name="provider_id" id="provider_id"
                                                                   value="{{ Illuminate\Support\Facades\Auth::guard()->user()->id }}">
                                                            <h3> Contact Person Details </h3>
                                                            <fieldset>
                                                                <div class="form-group row">
                                                                    <div class="col-md-3">
                                                                        <label for="user_name" class="block">Contact
                                                                            Person Name
                                                                            <span class="error">*</span></label>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <input id="user_name" name="user_name"
                                                                               type="text" required
                                                                               value="{{ Illuminate\Support\Facades\Auth::guard()->user()->first_name }}"
                                                                               class="required form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-md-3">
                                                                        <label for="email" class="block">Email Address
                                                                            <span class="error">*</span></label>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <input id="email" name="email" type="email"
                                                                               @if(Illuminate\Support\Facades\Auth::guard()->user()->email != Null) readonly
                                                                               @endif
                                                                               value="{{ Illuminate\Support\Facades\Auth::guard()->user()->email }}"
                                                                               class="required form-control">
                                                                        <span class="error"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-md-3">
                                                                        <label for="contact_number" class="block">Contact
                                                                            Number
                                                                            <span class="error">*</span></label>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <input id="contact_number" name="contact_number"
                                                                               @if(Illuminate\Support\Facades\Auth::guard()->user()->contact_number != Null) readonly
                                                                               @endif
                                                                               value="{{ Illuminate\Support\Facades\Auth::guard()->user()->country_code.Illuminate\Support\Facades\Auth::guard()->user()->contact_number }}"
                                                                               type="text"
                                                                               class="form-control required">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-md-3">
                                                                        <label for="gender" class="block">
                                                                            Gender <span class="error">*</span></label>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <div class="@if(isset($store_details)) col-sm-6 @else col-sm-8 @endif">
                                                                            <div class="form-radio">
                                                                                <div class="radio radio-inline">
                                                                                    <label>
                                                                                        <input type="radio"
                                                                                               name="gender"
                                                                                               class="form-control required"
                                                                                               value="1"
                                                                                               @if(Illuminate\Support\Facades\Auth::guard()->user()->gender == 1) checked="checked"@endif>
                                                                                        <i class="helper"></i>Male
                                                                                    </label>
                                                                                </div>
                                                                                <div class="radio radio-inline">
                                                                                    <label>
                                                                                        <input type="radio"
                                                                                               name="gender" value="2"
                                                                                               class="form-control required"
                                                                                               @if(Illuminate\Support\Facades\Auth::guard()->user()->gender == 2) checked="checked" @endif >
                                                                                        <i class="helper"></i>Female
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </fieldset>
                                                            <h3> Select Service </h3>
                                                            <fieldset>
                                                                <div class="form-group row">
                                                                    <div class="col-md-2">
                                                                        <label for="confirm-2" class="block">
                                                                            Service <span
                                                                                    style="color: red">*</span></label>
                                                                    </div>
                                                                    <div class="col-md-10">
                                                                        <div class="@if(isset($store_details)) col-sm-6 @else col-sm-8 @endif">
                                                                            <div class="form-radio">
                                                                                @if(isset($service_category))
                                                                                    @foreach($service_category as $service)
                                                                                        <div class="radio radio-inline">
                                                                                            <label>
                                                                                                <div>
                                                                                                    <input type="radio"
                                                                                                           name="service_category"
                                                                                                           value="{{ $service->id }}"
                                                                                                           class="form-control required service_category"
                                                                                                           service_category_id="{{ $service->id }}">
                                                                                                    <i class="helper"></i>{{ $service->name }}
                                                                                                </div>
                                                                                            </label>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </fieldset>
                                                            <h3> Store Details </h3>
                                                            <fieldset>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="store_name">Store Name<span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <input id="store_name" name="store_name"
                                                                                       type="text"
                                                                                       placeholder="Enter Store Name"
                                                                                       class="form-control required">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="delivery_radius"
                                                                                       class="block">Delivery
                                                                                    Radius<span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <select type="text"
                                                                                        class="form-control required"
                                                                                        name="delivery_radius"
                                                                                        required id="delivery_radius">
                                                                                    <option disabled selected>Select
                                                                                        Service
                                                                                        Radius
                                                                                    </option>
                                                                                    <option value="0.5">0.5 KM</option>
                                                                                    <option value="1">1 KM</option>
                                                                                    <option value="5">5 KM</option>
                                                                                    <option value="10">10 KM</option>
                                                                                    <option value="15">15 KM</option>
                                                                                    <option value="20">20 KM</option>
                                                                                    <option value="25">25 KM</option>
                                                                                    <option value="30">30 KM</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="eta_delivery_time"
                                                                                       class="block">Estimated Delivery
                                                                                    Time
                                                                                    <span style="color: red">*</span>
                                                                                </label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <select type="text"
                                                                                        class="form-control required"
                                                                                        name="eta_delivery_time"
                                                                                        id="eta_delivery_time">
                                                                                    <option disabled selected>
                                                                                        Select Estimated Delivery Time
                                                                                    </option>
                                                                                    <option value="10">10 Min</option>
                                                                                    <option value="15">15 Min</option>
                                                                                    <option value="20">20 Min</option>
                                                                                    <option value="30">30 Min</option>
                                                                                    <option value="45">45 Min</option>
                                                                                    <option value="60">60 Min</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-12">
                                                                                        <label for="order_min_amount">Order
                                                                                            Min
                                                                                            Amount</label>
                                                                                    </div>
                                                                                    <div class="col-md-12">
                                                                                        <input id="order_min_amount"
                                                                                               name="order_min_amount"
                                                                                               type="number" step="0.01"
                                                                                               placeholder="Enter Order Min Amount"
                                                                                               class="form-control">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-12">
                                                                                        <label for="packaging_charges">packging Charges </label>
                                                                                    </div>
                                                                                    <div class="col-md-12">
                                                                                        <input id="packaging_charges"
                                                                                               name="packaging_charges"
                                                                                               placeholder="Enter Packaging Charges"
                                                                                               type="number" step="0.01"
                                                                                               class="form-control">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="description">Sort
                                                                                    Description:</label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <textarea id="description"
                                                                                          name="description"
                                                                                          placeholder="$1,100 for two people (approx.) Exclusive Charges May Apply... Cash and Cards accepted"
                                                                                          class="form-control"></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="store_address">Store
                                                                                    Address: <span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <input id="address"
                                                                                       name="store_address"
                                                                                       type="text" required readonly
                                                                                       placeholder="Select Address on Map"
                                                                                       class="form-control required">
                                                                                <input type="hidden" name="address_lat"
                                                                                       id="lat"
                                                                                       class="form-control required">
                                                                                <input type="hidden" name="address_long"
                                                                                       id="lng"
                                                                                       class="form-control required">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="store_address">Store
                                                                                    Timings: <span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                @include('admin.pages.store_services.store.date_time')
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label for="store_name">Store
                                                                                    Banner<span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div id="image-preview-1">
                                                                                    <label for="image-upload-1"
                                                                                           id="image-label">Upload
                                                                                        Image</label>
                                                                                    <input type="file"
                                                                                           id="image-upload-1"
                                                                                           name="store_banner"
                                                                                           required
                                                                                           class="form-control required"
                                                                                    />
                                                                                </div>
                                                                                <span class="note">[Note: Image dimensions between 250*250 to 512*512]</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <div class="col-md-12">
                                                                                <label>Select Your Store Address<span
                                                                                            style="color: red">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <input id="searchInput" name="store_address"
                                                                                       class="input-controls"
                                                                                       type="text" placeholder="Enter a location">
                                                                                <div class="map" id="map"
                                                                                     style="width: 100%; height: 300px;"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </fieldset>
                                                            <h3> Documents </h3>
                                                            <fieldset>
                                                                @if(isset($required_document))
                                                                    @foreach($required_document as $key => $documents)
                                                                        <div class="row doc_service_cat"
                                                                             id="service_cat_id_{{$key}}">
                                                                            <div class="row">
                                                                                @foreach($documents as $document)
                                                                                    <div class="col-xl-3 col-md-6">
                                                                                        <div class="card comp-card">
                                                                                            <div class="card-body text-center">
                                                                                                <h6 class="m-b-20">{{ $document->name }}</h6>
                                                                                                <div id="document-preview-{{$document->id}}">
                                                                                                    <label for="document-upload-{{$document->id}}"
                                                                                                           id="document-label-{{$document->id}}">Upload
                                                                                                        Document</label>
                                                                                                    <input type="file"
                                                                                                           id="document-upload-{{$document->id}}"
                                                                                                           name="documents[{{$document->id}}]"
                                                                                                           class="form-control documents_{{$key}} unless_doc"
                                                                                                    />
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </fieldset>
                                                            <input type="submit" id="form-submit" style="display: none">
                                                        </form>
                                                    </section>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page body end -->
                </div>
            </div>
        </div>
    </div>
    <!-- Main-body end -->


@endsection
@section('page-js')

    {{--@include('admin.pages.store_services.store.script')--}}
    <script rel="stylesheet" src="{{ asset('assets/js/jquery.steps.js')}}"></script>
    {{--    <script rel="stylesheet" src="{{ asset('assets/js/waves.min.js')}}"></script>--}}
    <script rel="stylesheet" src="{{ asset('assets/js/form-wizard.js?v=0.1')}}"></script>
    <script rel="stylesheet" src="{{ asset('assets/js/validation/jquery.validate.js')}}"></script>
    <script rel="stylesheet" src="{{ asset('assets/js/file-upload/dropzone-amd-module.min.js')}}"></script>
    @include('admin.pages.store_services.store.script')
    <script type="text/javascript">
        $(document).ready(function () {
            $(".check-male").click(function () {
                $("#male").prop("checked", true);
            });
        });
        @if(isset($required_document))
        @foreach($required_document as $documents)
        @foreach($documents as $document)
        $(document).ready(function () {
            $.uploadPreview({
                input_field: "#document-upload-{{$document->id}}", // Default: .image-upload
                preview_box: "#document-preview-{{$document->id}}", // Default: .image-preview
                label_field: "#document-label-{{$document->id}}", // Default: .image-label
                label_default: "Upload Document", // Default: Choose File
                label_selected: "Upload Document", // Default: Change File
                no_label: false // Default: false
            });
        });
        @endforeach
        @endforeach
        @endif

        //        $(document).ready(function () {
        //            $('#finish').click(function () {
        //                $('#form-submit').click();
        //            });
        //        });
        $(document).on('click', 'a[href="#finish"]', function () {
            $('#form-submit').click();
        });

        $(document).on('change', '.service_category', function () {
            var id = $(this).attr('service_category_id');
            console.log(id);
            $('.doc_service_cat').hide();
            $('#service_cat_id_' + id).show();
            $('.unless_doc').prop('required',false);
            $('.documents_' + id).prop('required',true);
        });

        $(document.body).on('change', '#email', function () {
            var email = $(this).val();
            var id = $('#provider_id').val();
            $.ajax({
                type: 'get',
                data: {email: email, id: id},

                url: '{{ route('get:store-admin:check-unique-email') }}',
                success: function (response) {
                    if (response == 'taken') {
                        var cls = '#email';
                        $(cls).siblings("span").text('Sorry... Email already taken');
                        $(cls).val('');
                    } else if (response == 'not_taken') {
                        $('#email').siblings("span").text('');
                    }
                }
            });
        });

        $(document.body).on('change', '#contact_number', function () {
            var contact_number = $(this).val();
            var id = $('#provider_id').val();
            $.ajax({
                type: 'get',
                data: {contact_number: contact_number, id: id},

                url: '{{ route('get:store-admin:check-unique-contact-number') }}',
                success: function (response) {
                    if (response == 'taken') {
                        var cls = '#contact_number';
                        $(cls).siblings("span").text('Sorry... contact number already taken');
                        $(cls).val('');
                    } else if (response == 'not_taken') {
                        $('#contact_number').siblings("span").text('');
                    }
                }
            });
        });
    </script>

@endsection

