@extends('admin.layout.dispatcher_admin')
@section('title')
    Re-Assign Driver
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
            padding: 4px 30px 4px 20px;
            background-color: transparent;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Re-Assign Driver</h5>
                            <span>Re-assign driver for order</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <form id="main" method="post" action="{{ ((Illuminate\Support\Facades\Auth::guard("admin")->check()) && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1 )? route('post:admin:transport_update_re_assign_driver',[ "slug" => $slug ]) : '#' }}" enctype="multipart/form-data">
                            {{csrf_field() }}
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Re-Assgin Driver</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="form-group col-sm-12">
                                                    <input required type="hidden" name="ride_id" value="{{ isset($ride_id)? $ride_id : 0 }}">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Select Driver:<sup class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <select id="js-example1" name="driver_id" required class="js-example-placeholder-single1 js-states form-control">
                                                                <option disabled selected value=""></option>
                                                                <option disabled>Select Driver</option>
                                                                @if(isset($driver_list))
                                                                    @if(!$driver_list->isEmpty())
                                                                        @foreach($driver_list as $key => $driver_details)
                                                                            <option value="{{ $driver_details->driver_id }}" {{ (((isset($driver)) && ($driver != Null))? (($driver == $driver_details->driver_id)? "selected" : "") : "" ) }}>{{ $driver_details->contact_number." ".ucwords($driver_details->driver_name)  }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </select>
                                                            <span class="error">{{ $errors->first('driver_id') }}</span>
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
                                                <button type="submit" class="btn btn-primary m-b-0">Assign Driver
                                                </button>
                                            </center>
                                        </div>
                                    </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $("#js-example1").select2({
            placeholder: "Select a Driver",
            allowClear: true,
        });

        $('#js-example1').on('select2:selecting', function (e) {
            // console.log('Selecting: ' , e.params.args.data.element.value);
            // console.log('Selecting: ' , e.params.args.data);
            var driver_id = e.params.args.data.element.value;
            console.log(driver_id);
        });
    </script>
    <script>

    </script>
@endsection

