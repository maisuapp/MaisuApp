@extends('admin.layout.dispatcher_admin')
@section('title')
    Manual Assign Shopper
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugin/mdtimepicker.min.css')}}" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <style>
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

        /*.form-control:disabled, .form-control[readonly] {*/
        /*background-color: #ffffff;*/
        /*}*/
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        {{--@if(Illuminate\Support\Facades\Auth::guard("admin")->check())--}}
        {{--<div class="external-horizontal-nav">--}}
        {{--@include('admin.include.store-horizontal-navbar')--}}
        {{--</div>--}}
        {{--@endif--}}
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Manual Assign Shopper</h5>
                            <span>Manual assign shopper for order</span>
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
                        <form id="main" method="post"
                              action="{{ Illuminate\Support\Facades\Auth::guard("admin")->check()? (Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 2)? route('post:dispatcher:update_manual_assign_shopper',$slug) : route('post:admin:update_manual_assign_shopper',$slug) :route('post:store-admin:update_manual_assign_shopper') }}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Assgin Shopper</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-12">
                                                    <input required type="hidden" name="order_id"
                                                           value="{{ isset($order_id)? $order_id : 0 }}">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Select Shopper:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <select id="js-example1" name="shopper_id" required
                                                                    class="js-example-placeholder-single1 js-states form-control">
                                                                <option disabled selected value=""></option>
                                                                <option disabled>Select Shopper</option>
                                                                @if(isset($shopper_list))
                                                                    {{--@if(!$shopper_list->isEmpty())--}}
                                                                    @if($shopper_list != Null)
                                                                        @foreach($shopper_list as $key => $shopper_details)
                                                                            {{ $selected = isset($driver)? ($driver != Null)? ($driver == $shopper_details['shopper_id'])?  "selected" : "" : "" : "" }}
                                                                            <option value="{{ $shopper_details['shopper_id'] }}" {{ $selected }}>{{ $shopper_details['contact_number']." ".ucwords($shopper_details['shopper_name'])  }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('shopper_id') }}</span>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <center>
                                                <button type="submit" class="btn btn-primary m-b-0">Assign Shopper
                                                </button>
                                            </center>
                                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $("#js-example1").select2({
            placeholder: "Select a Shopper",
            allowClear: true,
        });

        $('#js-example1').on('select2:selecting', function (e) {
            // console.log('Selecting: ' , e.params.args.data.element.value);
            // console.log('Selecting: ' , e.params.args.data);
            var shopper_id = e.params.args.data.element.value;
            console.log(shopper_id);
        });
    </script>
@endsection

