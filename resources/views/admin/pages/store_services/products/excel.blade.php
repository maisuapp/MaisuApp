@extends('admin.layout.super_admin')
@section('title')
    Store Product Excel
@endsection
@section('page-css')
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
                            <h5> Excel Product</h5>
                            <span> Add Store Product Using Excel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">

                    <div class="page-body">
                        <div class="">
                            <form id="main" method="post"
                                  action="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('post:admin:update_store_product_excel',$slug) : route('post:store-admin:update_store_product_excel')}}"
                                  enctype="multipart/form-data">
                                {{csrf_field() }}
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Import Excel
                                                to @if(isset($service_category)) {{ ucwords(strtolower($service_category->name)) }} @endif
                                                Store Product</h5>
                                            <a href="{{ (Illuminate\Support\Facades\Auth::guard("admin")->check()) ? route('get:admin:store_product_list',[$slug,$store_id]) : route('get:store-admin:store_product_list') }}"
                                               class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                                        </div>
                                        <div class="card-block">
                                            @if(isset($service_category))
                                                <input type="hidden" name="service_cat_id" value="{{$service_category->id}}">
                                            @endif
                                            <input type="hidden" name="store_id" value="{{$store_id}}">

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label image">Select file Upload
                                                    Excel:<sup
                                                        class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" type="file" id="excel-upload"
                                                           name="product_excel_file"
                                                           required/>
                                                    <span class="note">[Note: Upload only .xls, .xslx ]</span>
                                                    <span class="error">{{ $errors->first('product_excel_file') }}</span>
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
@endsection

