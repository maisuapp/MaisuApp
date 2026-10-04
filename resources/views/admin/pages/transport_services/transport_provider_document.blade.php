@extends('admin.layout.super_admin')
@section('title')
    Driver Required Documents
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('/assets/css/widget/widget.css') }}">
    <style>
        .comp-card i {
            width: 35px !important;
            height: 35px !important;
            padding: 8px 0 !important;
        }

        .comp-card:hover i {
            border-radius: 5px !important;
        }

        .comp-card i:hover {
            border-radius: 50% !important;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ other service horizontal navbar ] start -->
        <div class="other-service-horizontal-nav">
{{--            @include('admin.include.transport-horizontal-navbar')--}}
        </div>
        <!-- [ other service horizontal navbar ] end -->
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>Driver Required Documents</h5>
                                    <span>Driver Required Documents List</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            {{--<a href="{{ route('get:transport:transport_provider_list') }}"--}}
                            {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
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
                        <div class="row">
                            <!-- analytic card start -->

                            @if(isset($required_documents_lists) && !$required_documents_lists->isEmpty())
                                @foreach($required_documents_lists as $key => $required_document)
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card comp-card">
                                            <div class="card-body text-center">
                                                <h6 class="m-b-20">{{ ucwords($required_document->name) }}</h6>
                                                @if($provider_documents[$key] == Null)
                                                    <p>{{ $required_document->name }} not found</p>
                                                @else
                                                    @if((isset($provider_documents[$key])) && ($provider_documents[$key]->document_file != Null) && file_exists(public_path('/assets/images/provider-documents/'.$provider_documents[$key]->document_file)))
                                                        @if($provider_documents[$key]->status == 0)
                                                            <p class="provider_document_id_{{ $provider_documents[$key]->id }}">
                                                                Pending</p>
                                                        @elseif($provider_documents[$key]->status == 1)
                                                            <p class="provider_document_id_{{ $provider_documents[$key]->id }}"
                                                               id="approved_id_{{ $provider_documents[$key]->id }}">
                                                                Approved</p>
                                                        @elseif($provider_documents[$key]->status == 2)
                                                            <p class="provider_document_id_{{ $provider_documents[$key]->id }}"
                                                               id="reject_id_{{ $provider_documents[$key]->id }}">
                                                                Rejected</p>
                                                        @endif
                                                    @else
                                                        <p>{{ $required_document->name }} not found</p>
                                                    @endif
                                                @endif
                                                @if((isset($provider_documents[$key])) && ($provider_documents[$key]->document_file != Null) && file_exists(public_path('/assets/images/provider-documents/'.$provider_documents[$key]->document_file)))
                                                    <a href="{{ asset('assets/images/provider-documents/'.$provider_documents[$key]->document_file) }}"
                                                       target="_blank">
                                                        <i class="fa fa-eye bg-c-blue document_view"
                                                           data-toggle="tooltip" data-placement="top" title="View"></i>
                                                    </a>
                                                @else
                                                    <a>
                                                        <i class="fa fa-eye-slash bg-c-blue" data-toggle="tooltip"
                                                           data-placement="top" title="View"></i>
                                                    </a>
                                                @endif
                                                <a class="document_status"
                                                   id="document_approved_id_{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"
                                                   document_approved_id="{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"
                                                   document_status="1">
                                                    <i class="fa fa-thumbs-up bg-c-green" data-toggle="tooltip"
                                                       data-placement="top" title="Approve"></i>
                                                </a>
                                                <a class="document_status"
                                                   id="document_approved_id_{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"
                                                   document_approved_id="{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"
                                                   document_status="2">
                                                    <i class="fa fa-thumbs-down bg-c-red" data-toggle="tooltip"
                                                       data-placement="top" title="Reject"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-xl-3 col-md-6">
                                    <div class="card comp-card">
                                        <div class="card-body text-center">
                                            {{--<h6 class="m-b-20">Required Documents</h6>--}}
                                            <p>Required documents list not found</p>
                                            <a>
                                                <i class="fa fa-eye bg-c-blue"></i>
                                            </a>
                                            <a class="document_status">
                                                <i class="fa fa-thumbs-up bg-c-green"></i>
                                            </a>
                                            <a class="document_status">
                                                <i class="fa fa-thumbs-down bg-c-red"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{--<div class="col-xl-3 col-md-6">--}}
                            {{--<div class="card comp-card">--}}
                            {{--<div class="card-body text-center">--}}
                            {{--<h6 class="m-b-20">Vehicle Insurance</h6>--}}
                            {{--<p>Status</p>--}}
                            {{--<a href="" target="_blank"><i class="fa fa-eye bg-c-blue"></i></a>--}}
                            {{--<a href="" target="_blank"><i class="fa fa-thumbs-up bg-c-green"></i></a>--}}
                            {{--<a href="" target="_blank"><i class="fa fa-thumbs-down bg-c-red"></i></a>--}}
                            {{--</div>--}}
                            {{--</div>--}}
                            {{--</div>--}}
                        </div>
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script type="text/javascript" src="{{ asset('/assets/js/widget/jquery.wavify.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/js/widget/TweenMax.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/assets/js/widget/widget-statistic.js') }}"></script>

    <script>
        $(document).on('click', '.document_status', function (e) {
            e.preventDefault();
            var id = $(this).attr('document_approved_id');
            var status = $(this).attr('document_status');
            var txt, title;
            console.log(id);
            if (status == 1) {
                title = "Approved Document File?";
                txt = "if press yes then approved document file!";
            }
            else if (status == 2) {
                title = "Reject Document File?";
                txt = "if press yes then Reject document file!";
            }
            if (id.trim() != "") {
                swal({
                        title: title,
                        text: txt,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        closeOnConfirm: false,
                        closeOnCancel: false
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            $.ajax({
                                type: 'get',
                                url: '{{ route("get:ajax:admin:update_approved_reject_provider_document") }}',
                                data: {id: id, status: status},
                                success: function (result) {
                                    if (result.success == true) {
                                        var provider_document_id = '.provider_document_id_' + id;
                                        if (result.status['status'] == 1) {
                                            $(provider_document_id).empty();
                                            $(provider_document_id).text("Approved");
                                            swal("Success", "Approved Document File Successfully", "success");
                                        }
                                        else if (result.status['status'] == 2) {
                                            $(provider_document_id).empty();
                                            $(provider_document_id).text("Rejected");
                                            swal("Success", "Reject Document File Successfully", "success");
                                        } else {
                                            $(provider_document_id).empty();
                                            $(provider_document_id).text("Pending");
                                            swal("Success", "Reject Document File Successfully", "success");
                                        }
                                    }else {
                                        swal("Warning", result.message, "warning");
                                    }
                                }
                            })
                        } else {
                            if (status == 1) {
                                swal("Cancelled", "Document File is Approved", "error");
                            }
                            else {
                                swal("Cancelled", "Document File is Reject", "error");
                            }
                        }
                    });
            }
        });
    </script>
@endsection

