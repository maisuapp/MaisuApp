@extends('admin.layout.super_admin')
@section('title')
    Add Coupons
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/material-datetime-picker.css')}}" type="text/css">
    <style>
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


        .image {
            padding-top: 0;
        }

        @if(isset($coupon_details))
        @if($coupon_details->coupon_image != Null)
        #upload-image-preview {
            background: url({{ asset('assets/images/coupon_images/'.$coupon_details->coupon_image) }});
            width: 100%;
            height: 200px;
            background-size: cover;
        }
        @endif
        @endif
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Add Coupon</h5>
                            <span>Coupon Details</span>
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
                        <div class="card">
                            <div class="card-header">
                                <h5>Add Coupon</h5>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post"
                                      action="{{ route('get:admin:coupon_deals:update_coupon',$slug) }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    @if(isset($coupon_details))
                                        <input type="hidden" name="id" value="{{$coupon_details->id}}">
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-12">
                                            <div class="row">
                                                <div class="form-group col-sm-7">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Category:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="category_id" id="category_id"
                                                                    class="form-control" required>
                                                                <option disabled selected value="">Choose Coupon
                                                                    Category
                                                                </option>
                                                                @if(isset($category_list))
                                                                    @foreach($category_list as $category)
                                                                        <option value="{{ $category->id }}"
                                                                                @if($category->status == 0) disabled
                                                                                @endif
                                                                                @if(isset($coupon_details) && ($coupon_details->category_id == $category->id)) selected @endif>{{$category->category_name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('category_id') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Name:
                                                            <sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="coupon_name"
                                                                   required
                                                                   id="coupon_name" placeholder="Coupon Name"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_name : old('coupon_name') }}">
                                                            <span class="error">{{ $errors->first('coupon_name') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Brand Name:</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control"
                                                                   name="coupon_brand_name"

                                                                   id="coupon_brand_name"
                                                                   placeholder="Coupon Brand Name"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_brand_name : old('coupon_brand_name') }}">
                                                            <span class="error">{{ $errors->first('coupon_brand_name') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Amount:
                                                            <sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="number" class="form-control"
                                                                   name="coupon_amount"
                                                                   required
                                                                   id="coupon_amount" placeholder="Coupon Amount"
                                                                   step="0.01"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_amount : old('coupon_amount') }}">
                                                            <span class="error">{{ $errors->first('coupon_amount') }}</span>
                                                        </div>
                                                    </div>


                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">No. Of Coupon Code:
                                                            <sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="number" class="form-control" name="total_coupon"
                                                                   required
                                                                   step="1" min="1" max="999"
                                                                   {{ (isset($coupon_details)) ? ($coupon_details->total_coupon > 0 ? "readonly" : "") : ""}}
                                                                   id="total_coupon" placeholder="No. Of Coupon Code"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->total_coupon : old('total_coupon') }}">
                                                            <span class="error">{{ $errors->first('total_coupon') }}</span>
                                                        </div>
                                                    </div>


{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-4 col-form-label">Coupon Code:--}}
{{--                                                            <sup class="error">*</sup></label>--}}
{{--                                                        <div class="col-sm-8">--}}
{{--                                                            <input type="text" class="form-control" name="coupon_code"--}}
{{--                                                                   required--}}
{{--                                                                   id="coupon_code" placeholder="Coupon Code"--}}
{{--                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_code : old('coupon_code') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('coupon_code') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Link:
                                                            <sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="link" required
                                                                   id="link" placeholder="Coupon link"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->link : old('link') }}">
                                                            <span class="error">{{ $errors->first('link') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Status:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="status" id="status"
                                                                    class="form-control" required>
                                                                <option disabled selected value="">Choose Coupon
                                                                    Status
                                                                </option>
                                                                <option value="1" {{ (isset($coupon_details)) ? ($coupon_details->status == 1 ? "selected" : "") : ""}}>
                                                                    Active
                                                                </option>
                                                                <option value="0" {{ (isset($coupon_details)) ? ($coupon_details->status == 0 ? "selected" : "") : ""}}>
                                                                    InActive
                                                                </option>
                                                            </select>
                                                            <span class="error">{{ $errors->first('category_id') }}</span>
                                                        </div>
                                                    </div>


                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Coupon Validity After Purchase (in days):
                                                            <sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="number" class="form-control" name="coupon_validity" required
                                                                   step="1" min="1" max="90"
                                                                   {{ (isset($coupon_details)) ? ($coupon_details->coupon_validity > 0 ? "readonly" : "") : ""}}
                                                                   id="coupon_validity" placeholder="Coupon Validity (in days)"
                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_validity : old('coupon_validity') }}">
                                                            <span class="error">{{ $errors->first('coupon_validity') }}</span>
                                                        </div>
                                                    </div>


                                                </div>


                                                <div class="form-group col-sm-5">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label image">Coupon Image:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <div id="upload-image-preview">
                                                                @if(isset($coupon_details) && $coupon_details->coupon_image != Null)
                                                                    <label for="image-upload" id="image-label">Change
                                                                        Image</label>
                                                                    <input type="file" id="image-upload"
                                                                           name="coupon_image"/>
                                                                @else
                                                                    <label for="image-upload" id="image-label">Upload
                                                                        Image</label>
                                                                    <input type="file" id="image-upload"
                                                                           name="coupon_image"
                                                                           required/>
                                                                @endif
                                                            </div>
                                                            {{--<span class="note">[Note: Upload only png and jpg file dimension between 250*250 to 500*500 & max size 100kb.]</span>--}}
                                                            <span class="error">{{ $errors->first('coupon_image') }}</span>
                                                        </div>
                                                    </div>
                                                </div>


{{--                                                <div class="form-group col-sm-4">--}}
{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-12 col-form-label">Active Date--}}
{{--                                                            Time:<sup--}}
{{--                                                                    class="error">*</sup></label>--}}
{{--                                                        <div class="col-sm-12">--}}
{{--                                                            <input type="text" class="form-control active_timepicker"--}}
{{--                                                                   name="active_date" autocomplete="off"--}}
{{--                                                                   required--}}
{{--                                                                   id="active_date"--}}
{{--                                                                   placeholder="Coupon Active Date Time"--}}
{{--                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->active_date : old('active_date') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('active_date') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="form-group col-sm-4">--}}
{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-12 col-form-label">Expiry Date--}}
{{--                                                            Time:<sup--}}
{{--                                                                    class="error">*</sup></label>--}}
{{--                                                        <div class="col-sm-12">--}}
{{--                                                            <input type="text" class="form-control timepicker"--}}
{{--                                                                   name="expiry_date" autocomplete="off"--}}
{{--                                                                   required--}}
{{--                                                                   id="expiry_date"--}}
{{--                                                                   placeholder="Coupon Expiry Date Time"--}}
{{--                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->expiry_date : old('expiry_date') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('expiry_date') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="form-group col-sm-4">--}}
{{--                                                    <div class="form-group row">--}}
{{--                                                        <label class="col-sm-12 col-form-label">Coupon Limit:<sup--}}
{{--                                                                    class="error">*</sup></label>--}}
{{--                                                        <div class="col-sm-12">--}}
{{--                                                            <input type="number" class="form-control"--}}
{{--                                                                   name="coupon_limit" autocomplete="off"--}}
{{--                                                                   required--}}
{{--                                                                   id="active_date"--}}
{{--                                                                   placeholder="Coupon Limit"--}}
{{--                                                                   value="{{ (isset($coupon_details)) ? $coupon_details->coupon_limit : old('coupon_limit') }}">--}}
{{--                                                            <span class="error">{{ $errors->first('coupon_limit') }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Terms & Condition:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                     <textarea id="textarea" name="terms_condition"
                                                               placeholder="Page Description"
                                                               class="form-control">{{ (isset($coupon_details)) ? $coupon_details->terms_condition : old('terms_condition')}}</textarea>
                                                            <span class="error">{{ $errors->first('terms_condition') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">How To Redeem:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                     <textarea id="textarea1" name="how_to_redeem"
                                                               placeholder="Page Description"
                                                               class="form-control">{{ (isset($coupon_details)) ? $coupon_details->how_to_redeem : old('how_to_redeem')}}</textarea>
                                                            <span class="error">{{ $errors->first('how_to_redeem') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
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
    <script src="https://unpkg.com/babel-polyfill@6.2.0/dist/polyfill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rome/2.1.22/rome.standalone.js"></script>
    <script src="{{ asset('assets/js/plugin/material-datetime-picker.js')}}"></script>
    <script type="text/javascript">
        var active_timepicker = new MaterialDatetimePicker({})
            .on('submit', function (d) {
//                output.innerText = d;
                $('.active_timepicker').val(d);
            });

        //        var el = document.querySelector('.active_timepicker');
        //        el.addEventListener('click', function () {
        //            picker.open();
        //        }, false);

        $(document.body).on('click', '.active_timepicker', function () {
            active_timepicker.open();
        });
        //        $(document).ready(function () {
        //            $('.active_timepicker').mdtimepicker(); //Initializes the time picker
        //        });


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
    <script src="https://cdn.tiny.cloud/1/67emx4i7o4nz8oelac30ctlnbvrv72l3lh9jqinbw71q399u/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        /*tinymce.init({
            selector: 'textarea',
            height: "280",
            plugins: [
                'code image spellchecker',
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code',
                'insertdatetime media nonbreaking save table contextmenu directionality',
            ],
            toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print media | preview | code | ltr rtl',
        });*/
        tinymce.init({
            selector: 'textarea',
            plugin: 'a_tinymce_plugin',
            a_plugin_option: true,
            a_configuration_option: 400,
            height: "280",
            plugins: [
                'code image spellchecker',
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code',
                'insertdatetime media nonbreaking save table contextmenu directionality',
            ],
            toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print media | preview | code | ltr rtl',
        });
    </script>
{{--    <script src="https://tinymce.cachefly.net/4.0/tinymce.min.js"></script>--}}
{{--    <script>--}}
{{--        tinymce.init({--}}
{{--            selector: 'textarea',--}}
{{--            height: "280",--}}
{{--            plugins: [--}}
{{--                'code image spellchecker',--}}
{{--                'advlist autolink lists link image charmap print preview hr anchor pagebreak',--}}
{{--                'searchreplace wordcount visualblocks visualchars code',--}}
{{--                'insertdatetime media nonbreaking save table contextmenu directionality',--}}
{{--            ],--}}
{{--            toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print media | preview | code | ltr rtl',--}}
{{--        });--}}
{{--    </script>--}}
@endsection

