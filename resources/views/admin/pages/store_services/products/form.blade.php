@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($product_details))Add @else Edit @endif Store Product
@endsection
@section('page-css')
    <style>
        .toggle input[type="checkbox"] + .button-indecator:before {
            font-size: 25px;
        }
        .image {
            padding-top: 0;
        }

        /* Absolute Center Spinner */
        .loading {
            /*display: none;*/
            position: fixed;
            z-index: 999;
            /*overflow: show;*/
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 50px;
        }

        /* Transparent Overlay */
        .loading:before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.5);
        }

        /* :not(:required) hides these rules from IE9 and below */
        .loading:not(:required) {
            /* hide "loading..." text */
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
        }
        .loading:not(:required):after {
            content: '';
            display: block;
            font-size: 10px;
            width: 50px;
            height: 50px;
            margin-top: -0.5em;
            border: 15px solid rgba(33, 150, 243, 1.0);
            border-radius: 100%;
            border-bottom-color: transparent;
            -webkit-animation: spinner 1s linear 0s infinite;
            animation: spinner 1s linear 0s infinite;
        }

        /* Animation */
        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @-moz-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @-o-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
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
    </style>
    @if(isset($service_category) && $service_category->id == 5)
        <style>
            .more-add-size, .more-add-options, .more-add-toppings, .more-add-category {
                /*border-bottom: 1px solid gainsboro;*/
                margin: 10px 0;
                padding: 10px 0;
                display: inline-block;
                width: 100%;
            }

            .add_size_button, .add_options_button, .add_toppings_button, .add_category_button {
                margin-left: 15px;
                /*background-color: #1b6d85;*/
                background-color: #4bb1b1;
            }

            .remove_field {
                outline: none;
                cursor: pointer;
                margin-top: 36px;
                float: right;
                /*margin-right: 10px;*/
                padding: 3px 10px;
            }

            .remove_field > .fa {
                margin-right: 0px !important;
                color: white;
            }

            .add_size_button .fas, .add_options_button .fas, .add_toppings_button .fas, .add_category_button .fas {
                margin-right: 0px !important;
                color: white;
            }

            .more-add-size, .more-add-options, .more-add-toppings, .more-add-category {
                padding: 0 15px;
            }

            .more-add-size .form-group, .more-add-options .form-group, .more-add-toppings .form-group, .more-add-category .form-group {
                margin-bottom: 0px
            }

            .d_size, .d_options, .d_toppings, .d_category {
                margin-top: -15px;
                margin-bottom: 15px;
            }

            .div-size, .div-options, .div-toppings, .div-category {
                padding: 0 1.25rem !important;
            }
        </style>
    @endif
@endsection
@section('page-content')

    <div class="pcoded-content">
        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
            <div class="external-horizontal-nav">
                @include('admin.include.store-horizontal-navbar')
            </div>
        @endif
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>@if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif Store Product</h5>
                            <span>@if(!isset($product_details)) Add @else Edit @endif
                                @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif Store Product</span>
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
                        <div class="loading">Loading;</div>
                        <form id="main" method="post" action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:update_store_product',$slug) : route('get:store-admin:update_store_product')}}" enctype="multipart/form-data">
                            {{csrf_field() }}
                            <div class="row">

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@if(!isset($product_details))Add @else Edit @endif
                                                @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif
                                                Store Product</h5>
                                            <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? isset($product_details) ? route('get:admin:store_product_list',[$slug,$product_details->store_id]) : route('get:admin:store_product_list',[$slug,$store_id]) : route('get:store-admin:store_product_list') }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                        </div>
                                        <div class="card-block">
                                            @if(isset($service_category))
                                                <input type="hidden" name="service_cat_id" value="{{$service_category->id}}">
                                            @endif
                                            @if(isset($product_details))
                                                <input type="hidden" name="store_id" value="{{$product_details->store_id}}">
                                                <input type="hidden" name="id" value="{{$product_details->id}}">
                                            @else
                                                <input type="hidden" name="store_id" value="{{$store_id}}">
                                            @endif
                                            <div class="row">
                                                <div class="form-group col-sm-7">
                                                    <div class="form-group row" @if((isset($is_sub_cat_flow)) && $is_sub_cat_flow != 1 ) style="display: none" @endif>
                                                        <label class="col-sm-4 col-form-label">Product Category:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="parent_category_id" id="parent_category_id" class="form-control" @if((isset($is_sub_cat_flow)) && $is_sub_cat_flow == 1 )  required @endif >
                                                                <option disabled selected value="">Choose Product Category</option>
                                                                @if(isset($product_categories))
                                                                    @foreach($product_categories as $product_category)
                                                                        <option value="{{ $product_category->id }}"
                                                                                @if($product_category->status == 0) disabled @endif
                                                                                @if(($product_category->is_selected == 1)) selected @endif>{{$product_category->name ." (Id:- ". $product_category->id ." )" }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('parent_category_id') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Product @if((isset($is_sub_cat_flow)) && $is_sub_cat_flow == 1 ) Sub @endif Category:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select name="product_category" id="category" class="form-control" required>
                                                                <option disabled selected value="">Choose Product @if((isset($is_sub_cat_flow)) && $is_sub_cat_flow == 1 ) Sub @endif Category</option>
{{--                                                                @if(isset($product_sub_categories))--}}
{{--                                                                    @foreach($product_sub_categories as $product_subcategory)--}}
{{--                                                                        <option value="{{ $product_subcategory->id }}"--}}
{{--                                                                                @if($product_subcategory->status == 0) disabled @endif--}}
{{--                                                                                @if(isset($product_details) && ($product_details->product_cate_id == $product_subcategory->id)) selected @endif>{{$product_subcategory->name ." (Id:- ". $product_subcategory->id ." )" }}</option>--}}
{{--                                                                    @endforeach--}}
{{--                                                                @endif--}}
                                                            </select>
                                                            <span class="error">{{ $errors->first('product_category') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Product Name:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="product_name" required id="name" placeholder="Product Name" value="{{ (isset($product_details)) ? $product_details->name : old('product_name') }}">
                                                            <span class="error">{{ $errors->first('product_name') }}</span>
                                                        </div>
                                                    </div>
                                                    @if(isset($service_category) && $service_category->id == 5)
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">Food Type</label>
                                                            <span class="toggle col-sm-2 col-form-label food_type">
                                                                <label>
                                                                    <input name="food_type" class="form-control" type="checkbox" id="food_type" @if(isset($product_details) && ($product_details->food_type == 1)) value="1" checked @endif>
                                                                    <span class="button-indecator"></span>
                                                                </label>
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Product Amount:<sup class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="number" class="form-control" name="product_amount" required id="amount" placeholder="Amount Value" step="0.01" value="{{ (isset($product_details)) ? $product_details->amount : old('product_amount') }}">
                                                            <span class="error">{{ $errors->first('product_amount') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Product Description:</label>
                                                        <div class="col-sm-8">
                                                            <textarea name="description" id="description" class="form-control" placeholder="Product Short Description">{{ (isset($product_details)) ? $product_details->description : old('description') }}</textarea>
                                                            <span class="error">{{ $errors->first('description') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Product Status:</label>
                                                        <div class="col-sm-8">
                                                            <select name="status" id="status" class="form-control" required>
                                                                @if(isset($product_details) && $product_details->status==0)
                                                                    <option value="1">Activate</option>
                                                                    <option value="0" selected>Deactivate</option>
                                                                @else
                                                                    <option value="1" selected>Activate</option>
                                                                    <option value="0">Deactivate</option>
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('status') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-5">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label image">Product Image:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <div id="upload-image-preview">
                                                                @if(isset($product_details) && $product_details->image != Null)
                                                                    <label for="image-upload" id="image-label">Change
                                                                        Image</label>
                                                                    <input type="file" id="image-upload"
                                                                           name="product_image"/>
                                                                @else
                                                                    <label for="image-upload" id="image-label">Upload
                                                                        Image</label>
                                                                    <input type="file" id="image-upload"
                                                                           name="product_image"
                                                                           required/>
                                                                @endif
                                                            </div>
                                                            <span class="note">[Note: Upload only png and jpg file dimension between 250*250 to 500*500 & max size 100kb.]</span>
                                                            <span class="error">{{ $errors->first('icon') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                @if(isset($service_category) && $service_category->id == 5)
                                    {{--category--}}
                                    <div class="form-group col-sm-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <h5>Product Category (Options / Toppings)</h5>
                                                    </div>
                                                    <div class="col-sm-6" style="text-align: right">
                                                        <div class="clearfix"></div>
                                                        <a class="btn btn-info add_category_button"><i
                                                                class="fas fa-plus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-block div-category">
                                                <div class="row d_category">

                                                    @if(isset($customize_category))
                                                        @foreach($customize_category as $key => $category)
                                                            <div class="more-add-category">
                                                                <div class="row">
                                                                    <input type="hidden" value="{{ $category->id }}"
                                                                           required
                                                                           name="customize_category[{{ $key+1 }}][id]">
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Name
                                                                                :</label>
                                                                            <div class="col-sm-12">
                                                                                <input type="text" required
                                                                                       class="form-control category_name"
                                                                                       name="customize_category[{{$key+1}}][name]"
                                                                                       placeholder="Name"
                                                                                       value="{{ $category->name }}"
                                                                                       step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Category
                                                                                Type:</label>
                                                                            <div class="col-sm-12">
                                                                                <select class="form-control select_customize_category"
                                                                                        required
                                                                                        name="customize_category[{{$key+1}}][type]">
                                                                                    <option value="1" {{ $category->selection_type == 1 ? "selected" : "" }}>
                                                                                        Single Choice
                                                                                    </option>
                                                                                    <option value="2" {{ $category->selection_type == 2 ? "selected" : "" }}>
                                                                                        Multi Choice
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    {{--<div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Max Selection(For Multi Choice)
                                                                                :</label>
                                                                            <div class="col-sm-12">
                                                                                <input type="number" required
                                                                                       class="form-control max_selection"
                                                                                       name="customize_category[{{$key+1}}][max_selection]"
                                                                                       placeholder="Max Selection in Numeric"
                                                                                       value="{{ ($category->max_selection>0?$category->max_selection:0) }}"
                                                                                       step="1">
                                                                            </div>
                                                                        </div>
                                                                    </div>--}}
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row">
                                                                            <a class="remove_field btn btn-danger"
                                                                               id="remove_cat_{{ $category->id }}"
                                                                               category_id="{{ $category->id }}">
                                                                                <i class="fa fa-times"
                                                                                   aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--size--}}
                                    <div class="form-group col-sm-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <h5>Product size</h5>
                                                    </div>
                                                    <div class="col-sm-6" style="text-align: right">
                                                        <div class="clearfix"></div>
                                                        <a class="btn btn-info add_size_button"><i
                                                                class="fas fa-plus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-block div-size">
                                                <div class="row d_size">
                                                    @if(isset($customize_size))
                                                        @foreach($customize_size as $key => $size)
                                                            <div class="more-add-size">
                                                                <div class="row">
                                                                    <input type="hidden" value="{{ $size->id }}"
                                                                           required
                                                                           name="size[{{ $key+1 }}][id]">
                                                                    <div class="form-group col-sm-5">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Name
                                                                                :</label>
                                                                            <div class="col-sm-12"><input type="text"
                                                                                                          required
                                                                                                          class="form-control size_name"
                                                                                                          name="size[{{$key+1}}][name]"
                                                                                                          placeholder="Name"
                                                                                                          value="{{ $size->name }}"
                                                                                                          step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-5">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Total
                                                                                Amount:</label>
                                                                            <div class="col-sm-12"><input type="number"
                                                                                                          required
                                                                                                          class="form-control"
                                                                                                          name="size[{{$key+1}}][amount]"
                                                                                                          placeholder="Total Amount"
                                                                                                          value="{{ $size->amount }}"
                                                                                                          step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-2">
                                                                        <div class="form-group row"><a
                                                                                id="remove_size_{{ $size->id }}"
                                                                                size_id="{{ $size->id }}"
                                                                                class="remove_field btn btn-danger"><i
                                                                                    class="fa fa-times"
                                                                                    aria-hidden="true"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--option--}}
                                    <div class="form-group col-sm-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <h5>Product Options</h5>
                                                    </div>
                                                    <div class="col-sm-6" style="text-align: right">
                                                        <div class="clearfix"></div>
                                                        <a class="btn btn-info add_options_button"><i
                                                                class="fas fa-plus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-block div-options">
                                                <div class="row d_options">
                                                    @if(isset($customize_options))
                                                        @foreach($customize_options as $key => $options)
                                                            <div class="more-add-options">
                                                                <div class="row">
                                                                    <input type="hidden" value="{{ $options->id }}"
                                                                           required
                                                                           name="options[{{ $key+1 }}][id]">
                                                                    <div class="form-group col-sm-4">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Category:</label>
                                                                            <div class="col-sm-12"><select
                                                                                    select_type="1"
                                                                                    class="form-control select_category"
                                                                                    id="select_options_category_{{ $key +1 }}"
                                                                                    name="options[{{ $key +1 }}][category]"
                                                                                    required>
                                                                                    <option selected disabled> Select
                                                                                        Category
                                                                                    </option>
                                                                                    @if(isset($customize_category))
                                                                                        @foreach($customize_category as $category)
                                                                                            <option value="{{ $category->name }}" {{ $options->category_id == $category->id ? "selected" : "" }}>{{ $category->name }}</option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                </select></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Name
                                                                                :</label>
                                                                            <div class="col-sm-12"><input type="text"
                                                                                                          required
                                                                                                          class="form-control"
                                                                                                          name="options[{{ $key+1 }}][name]"
                                                                                                          value="{{ $options->name }}"
                                                                                                          placeholder="Name">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Total
                                                                                Amount:</label>
                                                                            <div class="col-sm-12"><input type="number"
                                                                                                          required
                                                                                                          class="form-control"
                                                                                                          name="options[{{ $key+1 }}][amount]"
                                                                                                          placeholder="Total Amount"
                                                                                                          value="{{ $options->amount }}"
                                                                                                          step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-2">
                                                                        <div class="form-group row"><a
                                                                                id="remove_options_{{ $options->id }}"
                                                                                options_id="{{ $options->id }}"
                                                                                class="remove_field btn btn-danger"><i
                                                                                    class="fa fa-times"
                                                                                    aria-hidden="true"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--toppings--}}
                                    <div class="form-group col-sm-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <h5>Product Toppings</h5>
                                                    </div>
                                                    <div class="col-sm-6" style="text-align: right">
                                                        <div class="clearfix"></div>
                                                        <a class="btn btn-info add_toppings_button"><i
                                                                class="fas fa-plus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-block div-toppings">
                                                <div class="row d_toppings">
                                                    @if(isset($customize_toppings))
                                                        @foreach($customize_toppings as $key => $toppings)
                                                            <div class="more-add-toppings">
                                                                <div class="row">
                                                                    <input type="hidden" value="{{ $toppings->id }}"
                                                                           required
                                                                           name="toppings[{{ $key+1 }}][id]">
                                                                    <div class="form-group col-sm-4">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Category:</label>
                                                                            <div class="col-sm-12"><select
                                                                                    select_type="2"
                                                                                    class="form-control select_category"
                                                                                    id="select_toppings_category_{{ $key+1 }}"
                                                                                    name="toppings[{{ $key+1 }}][category]"
                                                                                    required>
                                                                                    <option selected disabled> Select
                                                                                        Category
                                                                                    </option>
                                                                                    @if(isset($customize_category))
                                                                                        @foreach($customize_category as $category)
                                                                                            <option value="{{ $category->name }}" {{ $toppings->category_id == $category->id ? "selected" : "" }}>{{ $category->name }}</option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                </select></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Name:</label>
                                                                            <div class="col-sm-12"><input type="text"
                                                                                                          required
                                                                                                          class="form-control"
                                                                                                          name="toppings[{{ $key+1 }}][name]"
                                                                                                          value="{{ $toppings->name }}"
                                                                                                          placeholder="Name">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-3">
                                                                        <div class="form-group row"><label
                                                                                class="col-sm-12 col-form-label">Total
                                                                                Amount:</label>
                                                                            <div class="col-sm-12"><input type="number"
                                                                                                          required
                                                                                                          class="form-control"
                                                                                                          name="toppings[{{ $key+1 }}][amount]"
                                                                                                          placeholder="Total Amount"
                                                                                                          value="{{ $toppings->amount }}"
                                                                                                          step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-sm-2">
                                                                        <div class="form-group row"><a
                                                                                id="remove_toppings_{{ $toppings->id }}"
                                                                                toppings_id="{{ $toppings->id }}"
                                                                                class="remove_field btn btn-danger"><i
                                                                                    class="fa fa-times"
                                                                                    aria-hidden="true"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endif

                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Product Discount Details</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Discount Type:</label>
                                                        <div class="col-sm-12">
                                                            <select name="discount_type" id="discount_type"
                                                                    class="form-control">
                                                                <option value="" selected>Choose Product Discount Type
                                                                </option>
                                                                <option value="1"
                                                                        @if(isset($product_details) && $product_details->discount_type == 1) selected @endif>
                                                                    Discount in Amount
                                                                <option value="2"
                                                                        @if(isset($product_details) && $product_details->discount_type == 2) selected @endif>
                                                                    Discount in Percentage
                                                                </option>
                                                            </select>
                                                            <span
                                                                    class="error">{{ $errors->first('discount_type') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Discount Amount:</label>
                                                        <div class="col-sm-12">
                                                            <input type="number" class="form-control"
                                                                   name="discount_amount"
                                                                   id="discount_amount" placeholder="Discount Amount"
                                                                   step="0.01"
                                                                   value="{{ (isset($product_details) && $product_details->discount_type != 0) ? $product_details->discount_amount : old('discount_amount') }}">
                                                            <span
                                                                    class="error">{{ $errors->first('discount_amount') }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="col-sm-12">
                                        <center>
                                            <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                        </center>
                                    </div>
                                </div>

                            </div>
                        </form>
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
            let service_category = @if(isset($service_category)) {{ $service_category->id }} @else 0 @endif;
            let parent_category_id =  $('#parent_category_id').val();
            LoadSubCategory(service_category,parent_category_id);
        });

        $('#parent_category_id').change(function(){
            let service_category = @if(isset($service_category)) {{ $service_category->id }} @else 0 @endif;
            let parent_category_id = $(this).val();
            LoadSubCategory(service_category,parent_category_id);
        });

        function LoadSubCategory(service_category,parent_category_id){
            let product_cate_id = "{{ isset($product_details->product_cate_id)? $product_details->product_cate_id : 0 }}";
            $(".loading").show();
            if(!service_category){
                $(".loading").hide();
                throw new Error("Something went badly wrong!");
            }
            @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                var url = "{{ route('get:admin:ajax:load_store_product_subcategory') }}";
            @else
                var url = "{{ route('get:store-admin:ajax:load_store_product_subcategory') }}";
            @endif
            $.ajax({
                type: "POST",
                url: url,
                async : false,
                cache : false,
                data: {
                    _token: "{{ csrf_token() }}",
                    service_category: service_category,
                    parent_category_id: parent_category_id,
                },
                success:function(response){
                    setTimeout(function (){
                        let $select = $('#category');
                        if(response.success === true){
                            var sub = "{{ ((isset($is_sub_cat_flow)) && $is_sub_cat_flow == 1 )? "Sub" : "" }}";
                            $select.find('option').remove().end().append('<option disabled selected value="">Choose Product '+ sub +' Category</option>');
                            $.each(response.product_sub_categories, function (key, value) {
                                console.log(value);
                                if(parseInt(product_cate_id) === parseInt(value.id)){
                                    $select.append('<option value=' + value.id + ' selected>' + value.name + '</option>');
                                } else{
                                    $select.append('<option value=' + value.id + '>' + value.name + '</option>');
                                }
                            });
                        }
                        $(".loading").hide();
                    },1000)
                },
            });
        }

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
                } else {
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
                } else {
                    $(this).val(1);
                }
            });
        });
    </script>
    @if(isset($service_category) && $service_category->id == 5)
        <script type="text/javascript">
            $(document).ready(function () {
                var max_fields = 100; //maximum input boxes allowed
                var wrapper_1 = $(".d_size"); //Fields wrapper
                var wrapper_2 = $(".d_options"); //Fields wrapper
                var wrapper_3 = $(".d_toppings"); //Fields wrapper
                var wrapper_4 = $(".d_category"); //Fields wrapper
                var add_button_1 = $(".add_size_button"); //Add button ID
                var add_button_2 = $(".add_options_button"); //Add button ID
                var add_button_3 = $(".add_toppings_button"); //Add button ID
                var add_button_4 = $(".add_category_button"); //Add button ID

                var x = $('.more-add-size').length;
                var y = $('.more-add-options').length;
                var z = $('.more-add-toppings').length;
                var w = $('.more-add-category').length;

                $(add_button_1).click(function (e) { //on add input button click
                    addMoreField1(e);
                });
                $(add_button_2).click(function (e) { //on add input button click
                    addMoreField2(e);
                });
                $(add_button_3).click(function (e) { //on add input button click
                    addMoreField3(e);
                });
                $(add_button_4).click(function (e) { //on add input button click
                    addMoreField4(e);
                });

                function addMoreField1(e) {
                    e.preventDefault();
                    if (x < max_fields) { //max input box allowed
                        x++; //text box increment
                        $(wrapper_1).append('<div class="more-add-size"><div class="row">' +
                            '<input type="hidden" required value="0" name="size[' + x + '][id]"><div class="form-group col-sm-5"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Name :</label> <div class="col-sm-12"> <input required type="text" class="form-control size_name" name="size[' + x + '][name]" placeholder="Name" step="0.01" > </div> </div> </div> ' +
                            '<div class="form-group col-sm-5"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Total Amount:</label> <div class="col-sm-12"> <input required type="number" class="form-control" name="size[' + x + '][amount]" placeholder="Total Amount" step="0.01"> </div> </div> </div>' +
                            '<div class="form-group col-sm-2"> <div class="form-group row"> <a class="remove_field btn btn-danger" id="remove_size_' + x + '" size_id="0"><i class="fa fa-times" aria-hidden="true"></i></a> </div></div>' +
                            '</div></div>');
                    }
                }

                function addMoreField2(e) {
                    e.preventDefault();
                    if (y < max_fields) { //max input box allowed
                        y++; //text box increment
                        $(wrapper_2).append('<div class="more-add-options"><div class="row">' +
                            '<input required type="hidden" value="0" name="options[' + y + '][id]"><div class="form-group col-sm-4"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Category:</label> <div class="col-sm-12"> <select select_type="1" class="form-control select_category" id="select_options_category_' + y + '" name="options[' + y + '][category]" required> <option value="" selected disabled> Select Category </option></select> </div> </div> </div> ' +
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Name :</label> <div class="col-sm-12"> <input required type="text" class="form-control" name="options[' + y + '][name]" placeholder="Name" > </div> </div> </div> ' +
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Total Amount:</label> <div class="col-sm-12"> <input required type="number" class="form-control" name="options[' + y + '][amount]" placeholder="Total Amount" step="0.01"> </div> </div> </div>' +
                            '<div class="form-group col-sm-2"> <div class="form-group row"> <a class="remove_field btn btn-danger" id="remove_options_' + y + '" options_id="0"><i class="fa fa-times" aria-hidden="true"></i></a> </div></div>' +
                            '</div></div>');
                    }
                }

                function addMoreField3(e) {
                    e.preventDefault();
                    if (z < max_fields) { //max input box allowed
                        z++; //text box increment
                        $(wrapper_3).append('<div class="more-add-toppings"><div class="row">' +
                            '<input required type="hidden" value="0" name="toppings[' + z + '][id]"><div class="form-group col-sm-4"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Category:</label> <div class="col-sm-12"> <select select_type="2" class="form-control select_category" id="select_toppings_category_' + z + '" name="toppings[' + z + '][category]" required> <option value="" selected disabled> Select Category </option></select> </div> </div> </div> ' +
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Name:</label> <div class="col-sm-12"> <input required type="text" class="form-control" name="toppings[' + z + '][name]" placeholder="Name"> </div> </div> </div> ' +
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Total Amount:</label> <div class="col-sm-12"> <input required type="number" class="form-control" name="toppings[' + z + '][amount]" placeholder="Total Amount" step="0.01" > </div> </div> </div>' +
                            '<div class="form-group col-sm-2"> <div class="form-group row"> <a class="remove_field btn btn-danger" id="remove_toppings_' + z + '" toppings_id="0"><i class="fa fa-times" aria-hidden="true"></i></a> </div></div>' +
                            '</div></div>');
                    }
                }

                function addMoreField4(e) {
                    e.preventDefault();
                    if (w < max_fields) { //max input box allowed
                        w++; //text box increment
                        $(wrapper_4).append('<div class="more-add-category"><div class="row">' +
                            '<input required type="hidden" value="0" name="customize_category[' + w + '][id]"><div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Name :</label> <div class="col-sm-12"> <input required type="text" class="form-control category_name" name="customize_category[' + w + '][name]" placeholder="Name" step="0.01"> </div> </div> </div> ' +
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <label class="col-sm-12 col-form-label">Category Type:</label> <div class="col-sm-12"> <select required class="form-control select_customize_category" name="customize_category[' + w + '][type]" placeholder="Category Type"> <option value="1"> Single Choice </option> <option value="2"> Multi Choice </option> </select> </div> </div> </div>' +
                            ''+
                            '<div class="form-group col-sm-3"> <div class="form-group row"> <a class="remove_field btn btn-danger" id="remove_cat_' + w + '" category_id="0"><i class="fa fa-times" aria-hidden="true"></i></a> </div></div>' +
                            '</div></div>');
                    }
                }

                var Category_values;

                //edit data set
                    @if(isset($customize_category))
                var get_value = [];
                @foreach($customize_category as $category)
                get_value.push({
                    name: "{{ $category->name }}",
                    category_type: "{{ $category->selection_type }}",
                });
                @endforeach
                    Category_values = get_value;
                @endif
                function changeCategory(e) {
                    e.preventDefault();
                    var get_value = [];
                    $('.category_name').each(function () {
                        console.log(this);
                        var category_type = $(this).closest('.more-add-category').find('select').val();
                        get_value.push({
                            name: this.value,
                            category_type: category_type
                        });
                    });
                    Category_values = get_value;
                }

                $(wrapper_1).on("click", ".remove_field", function (e) { //user click on remove text
                    e.preventDefault();
                    var id = $(this).attr('size_id');
                    var remove_id = $(this).attr('id');
                    swal({
                            title: "Are you sure?",
                            text: "You will not be able to recover this Data!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes, delete it!",
                            cancelButtonText: "No, cancel!",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                if (id > 0) {
                                    $.ajax({
                                        type: 'get',
                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                        url: '{{ route('get:admin:remove_product_customize_size') }}',
                                        @else
                                        url: '{{ route('get:store-admin:remove_product_customize_size') }}',
                                        @endif
                                        data: {id: id},
                                        success: function (result) {
                                            if (result.success == true) {
                                                //                                    location.reload();
                                                $("#" + remove_id).closest('.more-add-size').remove();
                                                x--;
                                                if (x < 0) {
                                                    addMoreField1(e);
                                                }
                                                swal("Success", "Size remove successfully", "success");
                                            } else {
                                            }
                                        }
                                    })
                                }
                                if (id == 0) {
                                    $("#" + remove_id).closest('.more-add-size').remove();
                                    x--;
                                    if (x < 0) {
                                        addMoreField1(e);
                                    }
                                    swal("Success", "Size remove successfully", "success");
                                }
                            } else {
                                swal("Cancelled", "Your Data is safe :)", "error");
                            }
                        });
                });
                $(wrapper_2).on("click", ".remove_field", function (e) { //user click on remove text
                    e.preventDefault();
                    var id = $(this).attr('options_id');
                    var remove_id = $(this).attr('id');
                    swal({
                            title: "Are you sure?",
                            text: "You will not be able to recover this Data!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes, delete it!",
                            cancelButtonText: "No, cancel!",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                if (id > 0) {
                                    $.ajax({
                                        type: 'get',
                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                        url: '{{ route('get:admin:remove_product_customize_options') }}',
                                        @else
                                        url: '{{ route('get:store-admin:remove_product_customize_options') }}',
                                        @endif
                                        data: {id: id},
                                        success: function (result) {
                                            if (result.success == true) {
                                                //                                    location.reload();
                                                $("#" + remove_id).closest('.more-add-options').remove();
                                                y--;
                                                if (y < 0) {
                                                    addMoreField2(e);
                                                }
                                                swal("Success", "Options remove successfully", "success");
                                            } else {
                                            }
                                        }
                                    })
                                }
                                if (id == 0) {
                                    $("#" + remove_id).closest('.more-add-options').remove();
                                    y--;
                                    if (y < 0) {
                                        addMoreField2(e);
                                    }
                                    swal("Success", "Options remove successfully", "success");
                                }
                            } else {
                                swal("Cancelled", "Your Data is safe :)", "error");
                            }
                        });

                });
                $(wrapper_3).on("click", ".remove_field", function (e) { //user click on remove text
                    e.preventDefault();
                    // $(this).parent('div').remove();
                    var id = $(this).attr('toppings_id');
                    var remove_id = $(this).attr('id');
                    swal({
                            title: "Are you sure?",
                            text: "You will not be able to recover this Data!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes, delete it!",
                            cancelButtonText: "No, cancel!",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                if (id > 0) {
                                    $.ajax({
                                        type: 'get',
                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                        url: '{{ route('get:admin:remove_product_customize_toppings') }}',
                                        @else
                                        url: '{{ route('get:store-admin:remove_product_customize_toppings') }}',
                                        @endif
                                        data: {id: id},
                                        success: function (result) {
                                            if (result.success == true) {
                                                //                                    location.reload();
                                                $("#" + remove_id).closest('.more-add-toppings').remove();
                                                z--;
                                                if (z < 0) {
                                                    addMoreField3(e);
                                                }
                                                swal("Success", "Category remove successfully", "success");
                                            } else {
                                            }
                                        }
                                    })
                                }
                                if (id == 0) {
                                    $("#" + remove_id).closest('.more-add-toppings').remove();
                                    z--;
                                    if (z < 0) {
                                        addMoreField3(e);
                                    }
                                    swal("Success", "Toppings remove successfully", "success");
                                }
                            } else {
                                swal("Cancelled", "Your Data is safe :)", "error");
                            }
                        });

                });
                $(wrapper_4).on("click", ".remove_field", function (e) { //user click on remove text
                    e.preventDefault();
                    // $(this).parent('div').remove();
                    var id = $(this).attr('category_id');
                    var remove_id = $(this).attr('id');
                    var customize_category_name = $("#" + remove_id).closest('.more-add-category').find('input[type=text]').val();
                    var more_add_options = $(wrapper_2).html();
                    var more_add_toppings = $(wrapper_3).html();
                    swal({
                            title: "Are you sure?",
                            text: "You will not be able to recover this Data!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes, delete it!",
                            cancelButtonText: "No, cancel!",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                if (id > 0) {
                                    $.ajax({
                                        type: 'get',
                                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                                        url: '{{ route('get:admin:remove_product_customize_category') }}',
                                        @else
                                        url: '{{ route('get:store-admin:remove_product_customize_category') }}',
                                        @endif
                                        data: {id: id},
                                        success: function (result) {
                                            if (result.success == true) {
                                                //                                    location.reload();
                                                getOptionForDelete(customize_category_name, more_add_options);
                                                getToppingsForDelete(customize_category_name, more_add_toppings);
                                                $("#" + remove_id).closest('.more-add-category').remove();
                                                changeCategory(e);
                                                w--;
                                                if (w < 0) {
                                                    // addMoreField4(e);
                                                }
                                                swal("Success", "Category remove successfully", "success");
                                            } else {
                                            }
                                        }
                                    })
                                }
                                if (id == 0) {
                                    getOptionForDelete(customize_category_name, more_add_options);
                                    getToppingsForDelete(customize_category_name, more_add_toppings);
                                    $("#" + remove_id).closest('.more-add-category').remove();
                                    changeCategory(e);
                                    w--;
                                    if (w < 0) {
                                        addMoreField4(e);
                                    }
                                    swal("Success", "Category remove successfully", "success");
                                }
                            } else {
                                swal("Cancelled", "Your Data is safe :)", "error");
                            }
                        });
                });


                $(document).on('change', '.category_name,.select_customize_category', function (e) {
                    e.preventDefault();
                    changeCategory(e);
                });

                $(document).on('focus', '.select_category', function (e) {
                    changeCategory(e);
                    $selected = ($(this).find("option:selected").text());
                    $selection_type = ($(this).attr('select_type'));
                    $(this).find('option').remove();
                    var id = $(this).attr('id');
                    $('#' + id).append('<option value="" selected disabled> Select Category </option>');
                    $(Category_values).each(function () {
                        if ($selection_type == this.category_type) {
                            console.log(this.name);
                            // console.log(this.category_type);
                            var check;
                            if ($selected == this.name) {
                                check = true;
                            } else {
                                check = false;
                            }
                            $('#' + id).append(new Option(this.name, this.name, check, check));
                        }
                    });
                });

                function deleteOption(id, remove_option) {
                    $.ajax({
                        type: 'get',
                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                        url: '{{--{{ route('get:admin:remove_product_customize_options') }}--}}',
                        @else
                        url: '{{--{{ route('get:store-admin:remove_product_customize_options') }}--}}',
                        @endif
                        data: {id: id},
                        success: function (result) {
                            if (result.success == true) {
                                // location.reload();
                                $("#" + remove_option).closest('.more-add-options').remove();
                                y--;
                                if (y < 0) {
                                    addMoreField2(e);
                                }
                            } else {
                                return 0;
                            }
                        }
                    });
                }

                function getOptionForDelete(customize_category_name, more_add_options) {
                    $(more_add_options).each(function (index, obj_options) {
                        var input_hidden_option_id = $(obj_options).find('input[type=hidden]').val();
                        var selected_option_value = $(obj_options).find('.select_category').find("option:selected").val();

                        if (customize_category_name == selected_option_value) {
                            var remove_option = $(obj_options).find('.remove_field').attr('id');
                            if (input_hidden_option_id > 0) {
                                deleteOption(input_hidden_option_id, remove_option);
                            } else {
                                $("#" + remove_option).closest('.more-add-options').remove();
                            }
                        }
                    }, customize_category_name);
                }

                function deleteToppings(id, remove_toppings) {
                    $.ajax({
                        type: 'get',
                        @if(Illuminate\Support\Facades\Auth::guard("admin")->check())
                        url: '{{--{{ route('get:admin:remove_product_customize_toppings') }}--}}',
                        @else
                        url: '{{--{{ route('get:store-admin:remove_product_customize_toppings') }}--}}',
                        @endif
                        data: {id: id},
                        success: function (result) {
                            if (result.success == true) {
                                $("#" + remove_toppings).closest('.more-add-toppings').remove();
                                z--;
                                if (z < 0) {
                                    addMoreField3(e);
                                }
                            } else {
                                return 0;
                            }
                        }
                    });
                }

                function getToppingsForDelete(customize_category_name, more_add_toppings) {
                    $(more_add_toppings).each(function (index, obj_toppings) {
                        var input_hidden_toppings_id = $(obj_toppings).find('input[type=hidden]').val();
                        var selected_toppings_value = $(obj_toppings).find('.select_category').find("option:selected").val();

                        if (customize_category_name == selected_toppings_value) {
                            var remove_toppings = $(obj_toppings).find('.remove_field').attr('id');
                            if (input_hidden_toppings_id > 0) {
                                deleteToppings(input_hidden_toppings_id, remove_toppings);
                            } else {
                                $("#" + remove_toppings).closest('.more-add-toppings').remove();
                            }
                        }
                    }, customize_category_name);
                }
            });
        </script>
    @endif
@endsection

