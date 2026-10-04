@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($promocode_details))Add @else Edit @endif Promocode
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/material-datetime-picker.css')}}" type="text/css">
    <style>
        .form-control:disabled, .form-control[readonly] {
            background-color: #ffffff;
        }

        .toggle input[type="checkbox"] + .button-indecator:before {
            font-size: 25px;
        }

        /*.discount {*/
        /*display: none;*/
        /*}*/

        .image {
            padding-top: 0;
        }

        @if(isset($product_details))
        @if($product_details->image != Null)
        #upload-image-preview {
            background: url({{ asset('assets/images/store-images/product-images/'.$product_details->image) }});
            width: 100%;
            height: 200px;
            background-size: cover;
        }

        @endif
        @endif

        .c-datepicker {
            width: 600px;
            min-height: 370px;
        }

        .c-datepicker__header {
            float: left;
            width: 50%;
        }

        .c-datepicker__calendar {
            width: 50%;
            float: left;
        }

        .c-datepicker__date {
            float: right;
            padding-left: 300px;

        }

        .c-datepicker__back {
            margin-left: 300px;
        }

        .c-datepicker__header-date {
            height: 340px;
        }

        .c-datepicker__toggle--right {
            right: 0;
            left: 200px;
        }

        .c-datepicker__header-day {
            height: 40px;
            line-height: 40px;
            font-size: 16px;
        }

        .c-datepicker__header-date span {
            margin: 10px;
        }

        .c-datepicker__toggle {
            top: 290px;
        }

        .c-datepicker__toggle--left {
            left: 50px;
        }

        .c-datepicker__clock {
            float: right;
            width: 50%;
        }

        .c-datepicker__clock::before {
            left: 30px;
        }

        .c-datepicker__clock .c-datepicker__clock__num, .c-datepicker__clock-hands {
            top: 95%;
        }

        .c-datepicker .c-datepicker--open {
            top: 55% !important;
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="external-horizontal-nav">
                @if(isset($service_category) && in_array($service_category->category_type,[1,5]) && !in_array($service_category->id,[31,32]))
                    @include('admin.include.transport-horizontal-navbar')
                @elseif(isset($service_category) && in_array($service_category->category_type,[1,5]) && in_array($service_category->id,[31,32]) )
                    @include('admin.include.transport-rental-horizontal-navbar')
                @elseif(isset($service_category) && in_array($service_category->category_type,[2]))
                    @include('admin.include.store-horizontal-navbar')
                @else
                    @include('admin.include.other-service-horizontal-navbar')
                @endif
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>@if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif  Promocode</h5>
                            <span>@if(!isset($product_details))Add @else Edit @endif @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif  Promocode</span>
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
                        <div class="row">
                            <form id="main" method="post" action="{{
                                                ( isset($service_category) && in_array($service_category->category_type,[1,5]) ?
                                                        ( !(in_array($service_category->id,[31,32]))? route('post:admin:transport:update_promocode',$slug):route('post:admin:transport-rental:update_promocode',$slug) ) : (isset($service_category) && in_array($service_category->category_type,[2])? route('post:admin:store:update_promocode',$slug) :  route('post:admin:other:update_promocode',$slug) ))  }}" enctype="multipart/form-data">
                                {{csrf_field() }}
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($promocode_details))Add @else Edit @endif @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif  Promocode</h5>
                                            @if(isset($service_category) && in_array($service_category->category_type,[1,5]) && !in_array($service_category->id,[31,32]))
                                                <a href="{{ route('get:admin:transport:promocode_list',$slug) }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                            @elseif(isset($service_category) && in_array($service_category->category_type,[1,5]) && in_array($service_category->id,[31,32]) )
                                                    <a href="{{ route('get:admin:transport-rental:promocode_list',$slug) }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                            @elseif(isset($service_category) && in_array($service_category->category_type,[2]))
                                                <a href="{{ route('get:admin:store:promocode_list',$slug) }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                            @else
                                                <a href="{{ route('get:admin:other:promocode_list',$slug) }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                            @endif
                                        </div>
                                        <div class="card-block">
                                            @if(isset($service_category))
                                                <input type="hidden" name="service_cat_id" value="{{$service_category->id}}">
                                            @endif
                                            @if(isset($promocode_details))
                                                <input type="hidden" name="id" value="{{$promocode_details->id}}">
                                            @endif
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Code Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="code_name" required id="name" placeholder="Promo Code Name" value="{{ (isset($promocode_details)) ? $promocode_details->promo_code : old('code_name') }}">
                                                            <span class="error">{{ $errors->first('code_name') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Expiry Date Time:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control timepicker" name="expiry_date_time" autocomplete="off" required id="name" placeholder="Promo Code Expiry Date Time" value="{{ (isset($promocode_details)) ? $promocode_details->expiry_date_time : old('expiry_date_time') }}">
                                                            <span class="error">{{ $errors->first('expiry_date_time') }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Discount Type:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <select name="discount_type" id="discount_type" class="form-control" required>
                                                                <option value="" selected disabled>Choose Product Discount Type</option>
                                                                <option value="1"  @if(isset($promocode_details) && $promocode_details->discount_type == 1) selected @endif> Discount in Amount</option>
                                                                <option value="2" @if(isset($promocode_details) && $promocode_details->discount_type == 2) selected @endif> Discount in Percentage</option>
                                                            </select>
                                                            <span class="error">{{ $errors->first('discount_type') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Discount Amount:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="discount_amount" required
                                                                   id="discount_amount"
                                                                   placeholder="Discount Amount"
                                                                   step="0.01"
                                                                   value="{{ (isset($promocode_details) && $promocode_details->discount_type != 0) ? $promocode_details->discount_amount : old('discount_amount') }}">
                                                            <span class="error">{{ $errors->first('discount_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Min Order Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="min_order_amount"
                                                                   id="min_order_amount"
                                                                   placeholder="Min Order Amount"
                                                                   step="0.01"
                                                                   value="{{ isset($promocode_details) ? ($promocode_details->min_order_amount != 0)? $promocode_details->min_order_amount : "" : old('min_order_amount') }}">
                                                            <span class="error">{{ $errors->first('min_order_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Max Discount Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="max_discount_amount"
                                                                   id="max_discount_amount"
                                                                   placeholder="Max Discount Amount"
                                                                   step="0.01"
                                                                   value="{{ isset($promocode_details) ? ($promocode_details->max_discount_amount != 0)? $promocode_details->max_discount_amount : "" : old('max_discount_amount') }}">
                                                            <span class="error">{{ $errors->first('max_discount_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Coupon Limit:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="coupon_limit"
                                                                   id="coupon_limit"
                                                                   placeholder="Coupon Limit"
                                                                   step="0.01"
                                                                   value="{{ isset($promocode_details) ? ($promocode_details->coupon_limit != 0 ? $promocode_details->coupon_limit : "" ) : old('coupon_limit') }}">
                                                            <span class="error">{{ $errors->first('coupon_limit') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Usage Limit For One User:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="usage_limit" required
                                                                   id="usage_limit"
                                                                   placeholder="Usage Limit For Onr User"
                                                                   step="0.01"
                                                                   value="{{ isset($promocode_details) ? ($promocode_details->usage_limit != 0 ? $promocode_details->usage_limit : "") : old('usage_limit') }}">
                                                            <span class="error">{{ $errors->first('usage_limit') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Promo Code Description:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <textarea name="description" id="description" class="form-control" required placeholder="Promo Code Description">{{ isset($promocode_details) ? $promocode_details->description : old('description') }}</textarea>
                                                            <span class="error">{{ $errors->first('description') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <center>
                                            <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                        </center>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page-js')

    <script src="https://unpkg.com/babel-polyfill@6.2.0/dist/polyfill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rome/2.1.22/rome.standalone.js"></script>
    <script src="{{ asset('assets/js/plugin/material-datetime-picker.js')}}"></script>
    <script type="text/javascript">
        var picker = new MaterialDatetimePicker({})
            .on('submit', function (d) {
//                output.innerText = d;
                $('.timepicker').val(d);
            });

        //        var el = document.querySelector('.timepicker');
        //        el.addEventListener('click', function () {
        //            picker.open();
        //        }, false);

        $(document.body).on('click', '.timepicker', function () {
            picker.open();
        });
        //        $(document).ready(function () {
        //            $('.timepicker').mdtimepicker(); //Initializes the time picker
        //        });
    </script>
    <script src="{{ asset('assets/js/upload_image.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.uploadPreview({
                input_field: "#image-upload",   // Default: .image-upload
                preview_box: "#upload-image-preview",  // Default: .image-preview
                label_field: "#image-label",    // Default: .image-label
                label_default: "Choose Image",   // Default: Choose File
                label_selected: "Change Image",  // Default: Change File
                no_label: false                 // Default: false
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#discount').on('click', function () {
                var discount_value = $(this).val();
                if (discount_value == 1) {
                    $(this).val(0);
                    $(this).removeAttr("checked");
                    $('.discount').hide();
                    $('#discount_amount').removeAttr("required");
                    $('#discount_type').removeAttr("required");
                }
                else {
                    $(this).attr("checked", "checked");
                    $(this).val(1);
                    $('.discount').show();
                    $('#discount_amount').attr("required", "required");
                    $('#discount_type').attr("required", "required");
                }
            });
            $('#food_type').on('click', function () {
                var food_type = $(this).val();
                if (food_type == 1) {
                    $(this).val(2);
                }
                else {
                    $(this).val(1);
                }
            });
        });
    </script>

@endsection

