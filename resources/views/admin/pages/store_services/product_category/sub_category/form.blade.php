@extends('admin.layout.super_admin')
@section('title')
    @if(!isset($store_category))
        Add
    @else
        Edit
    @endif
    Store Sub Category
@endsection
@section('page-css')
    <style>
        .toggle input[type="checkbox"] + .button-indecator:before {
            font-size: 25px;
        }
        .discount {
            display: none;
        }

        .image {
            padding-top: 0;
        }
        @if(isset($store_category))
            @if($store_category->category_icon != Null)
            #upload-image-preview {
                background: url({{ asset('assets/images/store-images/category-images/'.$store_category->category_icon) }});
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
        <div class="external-horizontal-nav">
            @include('admin.include.store-horizontal-navbar')
        </div>
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Store Sub Category @if(isset($store_category_details)) of {{ $store_category_details->name }} @endif</h5>
                            <span>@if(!isset($store_category))Add @else Edit @endif Store Sub Category @if(isset($store_category_details)) of {{ $store_category_details->name }} @endif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>@if(!isset($store_category))Add @else Edit @endif Store Sub Category @if(isset($store_category_details)) of {{ $store_category_details->name }} @endif</h5>
                                <a href="{{ route('get:admin:store_sub_category_list',[ 'slug' => $slug, 'category_id' => $store_category_details->id ]) }}" class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post" action="{{route('post:admin:update_store_subcategory',[ 'slug' => $slug ])}}" enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    <input type="hidden" name="category_id" value="{{$store_category_details->id}}">
                                    @if(isset($store_category))
                                        <input type="hidden" name="id" value="{{$store_category->id}}">
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-7">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Category Name:<sup class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="category_name" required id="category_name" placeholder="Category Name" value="{{ (isset($store_category)) ? $store_category->name : old('category_name') }}">
                                                    <span class="error">{{ $errors->first('category_name') }}</span>
                                                </div>
                                            </div>
                                            @if(isset($language_lists))

                                                @foreach($language_lists as $single_lang)
                                                    @php
                                                        $language_name =  isset($single_lang->language_name)?$single_lang->language_name:"";
                                                        $language_code =  isset($single_lang->language_code)?$single_lang->language_code:"";
                                                        $col_name = $language_code."_name";
                                                    @endphp

                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Language Value (in {{$language_name}}):<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="{{$col_name}}" id="{{$col_name}}" value="{{ isset($store_category->$col_name)?$store_category->$col_name:"" }}"  placeholder="Please enter language (in {{$language_name}})" value="" autocomplete="off" required>
                                                            <span class="error">{{ $errors->first($col_name) }}</span>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            @endif

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Category Status:</label>
                                                <div class="col-sm-8">
                                                    <select name="category_status" id="category_status" class="form-control" required>
                                                        @if(isset($store_category) && $store_category->status==0)
                                                            <option value="1">Activate</option>
                                                            <option value="0" selected>Deactivate</option>
                                                        @else
                                                            <option value="1" selected>Activate</option>
                                                            <option value="0">Deactivate</option>
                                                        @endif
                                                    </select>
                                                    <span class="error">{{ $errors->first('category_status') }}</span>
                                                </div>
                                            </div>
                                        </div>
{{--                                        <div class="form-group col-sm-5">--}}
{{--                                            <div class="form-group row">--}}
{{--                                                <label class="col-sm-12 col-form-label image">Category Image:<sup class="error">*</sup></label>--}}
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <div id="upload-image-preview">--}}
{{--                                                        @if(isset($store_category) && $store_category->category_icon != Null)--}}
{{--                                                            <label for="image-upload" id="image-label">Change Image</label>--}}
{{--                                                            <input type="file" id="image-upload" name="category_icon"/>--}}
{{--                                                        @else--}}
{{--                                                            <label for="image-upload" id="image-label">Upload Image</label>--}}
{{--                                                            <input type="file" id="image-upload" name="category_icon" required/>--}}
{{--                                                        @endif--}}
{{--                                                    </div>--}}
{{--                                                    --}}{{--<span class="note">[Note: Upload only png and jpg file dimension between 250*250 to 500*500 & max size 100kb.]</span>--}}
{{--                                                    <span class="error">{{ $errors->first('category_icon') }}</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2"></label>
                                        <div class="col-sm-10">
                                            <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
@endsection

