@extends('admin.layout.super_admin')
@section('title')
    Add Category
@endsection
@section('page-css')
    <style>

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
                            <h5>Add Category</h5>
                            <span>Category Details</span>
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
                                <h5>Add Category</h5>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post"
                                      action="{{ route('get:admin:coupon_deals_update_category',$slug) }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    @if(isset($category))
                                        <input type="hidden" name="id" value="{{$category->id}}">
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-8">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Category Name:
                                                    <sup class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="category_name" required
                                                           id="name" placeholder="Category Name"
                                                           value="{{ (isset($category)) ? $category->category_name : old('category_name') }}">
                                                    <span class="error">{{ $errors->first('category_name') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label"></label>
                                                <div class="col-sm-8">
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
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
@endsection

