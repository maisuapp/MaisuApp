@extends('admin.layout.super_admin')
@section('title')
    Add Country
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
                            <h5>Add Country</h5>
                            <span>Country Details</span>
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
                                <h5>Add Country</h5>
                                <a href="{{ route('get:admin:world_country_list') }}"
                                   class="btn btn-primary m-b-0 btn-right render_link"> Back</a>
                            </div>
                            <div class="card-block">
                                <form id="main" method="post" action="{{ route('post:admin:update_country_city') }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    @if(isset($service_category))
                                        <input type="hidden" name="id" value="{{$service_category->id}}">
                                    @endif
                                    <div class="row">
                                        <div class="form-group col-sm-8">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Country Name:
                                                    <sup class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="name" required
                                                           id="name" placeholder="Country Name"
                                                           value="{{ (isset($service_category)) ? $service_category->country_name : old('country_name') }}">
                                                    <span class="error">{{ $errors->first('country_name') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Country Code:<sup
                                                            class="error">*</sup></label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" name="name" required
                                                           id="name" placeholder="Country Code"
                                                           value="{{ (isset($service_category)) ? $service_category->country_code : old('country_code') }}">
                                                    <span class="error">{{ $errors->first('country_code') }}</span>
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

