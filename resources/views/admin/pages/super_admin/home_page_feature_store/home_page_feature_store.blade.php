@extends('admin.layout.super_admin')
@section('title')
    @if(isset($home_page_feature_store)) Edit @else Add @endif Feature Store List
@endsection
@section('page-css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <style>
        input[type="radio"] {
            display: none;
        }

        input[type="radio"] + .label {
            position: relative;
            /*margin-left: 43%;*/
            /*display: block;*/
            padding-left: 25px;
            margin-right: 10px;
            cursor: pointer;
            /*line-height: 16px;*/
            color: black;
            font-size: 14px;
            transition: all .2s ease-in-out;
            margin-bottom: 10px;
        }

        input[type="radio"] + .label:before, input[type="radio"] > .label:after {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            width: 20px;
            height: 20px;
            text-align: center;
            color: black;
            cursor: pointer;
            border-radius: 50%;
            transition: all .3s ease;
        }

        input[type="radio"] + .label:before {
            /*box-shadow: inset 0 0 0 1px #666565, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;*/
            box-shadow: 0 0 0 0 #91DEAC, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
        }

        input[type="radio"] + .label:hover {
            color: #44BB6E;
        }

        input[type="radio"] + .label:hover:before {
            animation-duration: .5s;
            animation-name: change-size;
            animation-iteration-count: infinite;
            animation-direction: alternate;
            box-shadow: inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
        }

        input[type="radio"]:checked + .label:hover {
            color: #333333;
            cursor: default;
        }

        input[type="radio"]:checked + .label:before {
            animation-duration: .2s;
            animation-name: select-radio;
            animation-iteration-count: 1;
            animation-direction: Normal;
            box-shadow: inset 0 0 0 1px #44BB6E, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #44BB6E;

        }

        @keyframes change-size {
            from {
                box-shadow: 0 0 0 0 #44BB6E, inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            to {
                box-shadow: 0 0 0 1px #44BB6E, inset 0 0 0 1px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
        }

        @keyframes select-radio {
            0% {
                box-shadow: 0 0 0 0 #91DEAC, inset 0 0 0 2px #FFFFFF, inset 0 0 0 3px #44BB6E, inset 0 0 0 16px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            90% {
                box-shadow: 0 0 0 10px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #44BB6E, inset 0 0 0 2px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
            100% {
                box-shadow: 0 0 0 12px #E8FFF0, inset 0 0 0 0 #FFFFFF, inset 0 0 0 1px #44BB6E, inset 0 0 0 3px #FFFFFF, inset 0 0 0 16px #44BB6E;
            }
        }

        @media screen and (max-width: 576px) {
            input[type="radio"] + .label {
                margin-left: 48%;
                display: block;
            }
        }

        #upload-image-preview {
            height: 160px;
        }

        @if(isset($home_page_feature_store))
        #upload-image-preview {
            background: url({{ asset('/assets/images/home-banner/'.$home_page_feature_store->banner_image) }}) no-repeat;
            background-size: contain;
            background-position: center;
        }
        @endif


        /*start select style*/
        .select2-container {
            width: 100% !important;
            vertical-align: unset;
        }
        .select2-container--default .select2-selection--single {
            height: auto;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            /*padding-top: 1px;*/
            padding: 4px 30px 4px 20px;
            background-color: transparent;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
        /*end select style*/
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        {{--<div class="external-horizontal-nav">
            @include('admin.include.store-horizontal-navbar')
        </div>--}}
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Home Page Feature Store</h5>
                            <span>@if(isset($home_page_feature_store)) Edit @else Add @endif Feature Store</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <a href="{{ route('get:admin:home_page_feature_store_list') }}"
                       class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                </div>
            </div>
        </div>


        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <form id="main" method="post" action="{{ route('post:admin:add_home_page_feature_store') }}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}

                            <div class="card">
                                <div class="card-header">
                                    <h5>@if(isset($home_page_feature_store)) Edit @else Add @endif Feature Store</h5>
                                </div>
                                <div class="card-block">

                                    @if(isset($home_page_feature_store))
                                        <input type="hidden" name="id" value="{{$home_page_feature_store->id}}">
                                    @endif

                                    <div class="row">
                                        <div class="form-group col-sm-12">

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label image">Select Service:<sup
                                                        class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <select id="services" name="service"
                                                            class="js-example-placeholder-single1 js-states form-control" required>
                                                        <option disabled selected value=""></option>
                                                        <option disabled>Select a Service Category</option>
                                                        @if(isset($service_category))
                                                            @if(!$service_category->isEmpty())
                                                                @foreach($service_category as $key => $category)
                                                                    {{ $selected = isset($home_page_feature_store) ? ($home_page_feature_store->service_cat_id == $category->id)?  "selected" : "" : "" }}
                                                                    <option value="{{ $category->id }}" {{ $selected }}>{{ ucwords($category->name)  }}</option>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    </select>
                                                    <span class="error">{{ $errors->first('services') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label image">Store List:<sup
                                                        class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <select id="store" name="store"
                                                            class="js-example-placeholder-single1 js-states form-control" required>
                                                        <option disabled selected value=""></option>
                                                        <option disabled>Select a store List</option>

                                                    </select>
                                                    <span class="error">{{ $errors->first('services') }}</span>
                                                </div>
                                            </div>



                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
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

@endsection
@section('page-js')
    <script type="text/javascript" src="{{ asset('assets/js/upload_image.js')}}"></script>
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

            let service_Category = $('#services').val();
            let selected_store = "{{ isset($home_page_feature_store->store_id) ? $home_page_feature_store->store_id : 0 }}";
            LoadStores(service_Category,selected_store);
        });
            $('#services').change(function(){
                let service_Category = $(this).val();
                let selected_store = "{{ isset($home_page_feature_store->store_id) ? $home_page_feature_store->store_id : 0 }}";
                LoadStores(service_Category,selected_store);
            });

            function LoadStores(service_Category,selected_store){
                if(!service_Category){
                    throw new Error("Something went badly wrong!");
                }
                $.ajax({
                    type: "POST",
                    url: "{{ route('get:admin:ajax_load_store') }}",
                    async : false,
                    cache : false,
                    data: {
                        service_Category: service_Category,
                        selected_store: selected_store,
                        _token: "{{ csrf_token() }}",
                        id: "{{ isset($home_page_feature_store) ? $home_page_feature_store->store_id : 0 }}"
                    },
                    success:function(response){
                        let $select = $('#store');
                        if(response.success === true){
                            $select.find('option').remove().end().append('<option disabled selected value="">Store List</option>');
                            $.each(response.data, function (key, value) {
                                if(parseInt(selected_store) === parseInt(value.store_id)){
                                    $select.append('<option value=' + value.store_id + ' selected>' + value.store_name + '</option>');
                                } else{
                                    $select.append('<option value=' + value.store_id + '>' + value.store_name + '</option>');
                                }

                            });
                        }
                    }
                });
            }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $("#services").select2({
            placeholder: "Select a Service Category",
            allowClear: true,
        });
        $("#store").select2({
            placeholder: "Select a Store",
            allowClear: true,
        });
    </script>
@endsection

