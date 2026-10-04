@extends('admin.layout.super_admin')
@section('title')
    On-demand Provider Documents
@endsection
@section('page-css')
    <link rel="stylesheet" href="{{ asset('/assets/css/font/font-awesome-n.min.css') }}">
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
        .document_view ,.document_status{
            cursor: pointer;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <div class="other-service-horizontal-nav">
            @include('admin.include.other-service-horizontal-navbar')
        </div>
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5>On-demand Provider's Documents</h5>
                                    <span>On-demand Provider's Documents List</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <a href="{{ route('get:admin:other_service_provider_list',[$slug,"approved"]) }}"
                               class="btn btn-primary m-b-0 btn-right render_link">Back</a>
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
                            @if(isset($required_documents))
{{--                                @foreach($required_documents as $key => $required_document)--}}
{{--                                    <div class="col-xl-3 col-md-6">--}}
{{--                                        <div class="card comp-card">--}}
{{--                                            <div class="card-body text-center">--}}
{{--                                                <h6 class="m-b-20">{{ ucwords($required_document->name) }}</h6>--}}
{{--                                                @if($required_documents[$key] == Null)--}}
{{--                                                    <p>{{ $required_document->name }} not found</p>--}}
{{--                                                @else--}}
{{--                                                    @if(isset($required_documents[$key]) && $required_documents[$key]->document_file != Null && file_exists(public_path('/assets/images/provider-documents/'.$required_documents[$key]->document_file)))--}}
{{--                                                        @if($required_documents[$key]->status == 0)--}}
{{--                                                            <p class="provider_document_id_{{ $required_documents[$key]->id }}">--}}
{{--                                                                Pending</p>--}}
{{--                                                        @elseif($required_documents[$key]->status == 1)--}}
{{--                                                            <p class="provider_document_id_{{ $required_documents[$key]->id }}"--}}
{{--                                                               id="approved_id_{{ $required_documents[$key]->id }}">--}}
{{--                                                                Approved</p>--}}
{{--                                                        @elseif($required_documents[$key]->status == 2)--}}
{{--                                                            <p class="provider_document_id_{{ $required_documents[$key]->id }}"--}}
{{--                                                               id="reject_id_{{ $required_documents[$key]->id }}">--}}
{{--                                                                Rejected</p>--}}
{{--                                                        @endif--}}
{{--                                                    @else--}}
{{--                                                        <p>{{ $required_document->name }} not found</p>--}}
{{--                                                    @endif--}}
{{--                                                @endif--}}
{{--                                                @if(isset($provider_documents[$key]) && $provider_documents[$key]->document_file != Null && file_exists(public_path('/assets/images/provider-documents/'.$provider_documents[$key]->document_file)))--}}
{{--                                                    <a href="{{ asset('provider-documents/'.$provider_documents[$key]->document_file) }}"--}}
{{--                                                       target="_blank">--}}
{{--                                                        <i class="fa fa-eye bg-c-blue document_view"--}}
{{--                                                           data-toggle="tooltip" data-placement="top" title="View"></i>--}}
{{--                                                    </a>--}}
{{--                                                @else--}}
{{--                                                    <a>--}}
{{--                                                        <i class="fa fa-eye-slash bg-c-blue" data-toggle="tooltip"--}}
{{--                                                           data-placement="top" title="View"></i>--}}
{{--                                                    </a>--}}
{{--                                                @endif--}}
{{--                                                <a class="document_status"--}}
{{--                                                   id="document_approved_id_{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"--}}
{{--                                                   document_approved_id="{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"--}}
{{--                                                   document_status="1">--}}
{{--                                                    <i class="fa fa-thumbs-up bg-c-green" data-toggle="tooltip"--}}
{{--                                                       data-placement="top" title="Approve"></i>--}}
{{--                                                </a>--}}
{{--                                                <a class="document_status"--}}
{{--                                                   id="document_approved_id_{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"--}}
{{--                                                   document_approved_id="{{ isset($provider_documents[$key])? $provider_documents[$key]->id : ''}}"--}}
{{--                                                   document_status="2">--}}
{{--                                                    <i class="fa fa-thumbs-down bg-c-red" data-toggle="tooltip"--}}
{{--                                                       data-placement="top" title="Reject"></i>--}}
{{--                                                </a>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                            @endforeach--}}


                                @foreach($required_documents as $required_document)
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card comp-card">
                                            <div class="card-body text-center">
                                                <h6 class="m-b-20">{{ ucwords($required_document->name) }}</h6>
                                                @if($provider_documents)
                                                    @foreach($provider_documents as $provider_document)
                                                        @if($provider_document->req_document_id === $required_document->id)
                                                            <p class="provider_document_id_{{ $provider_document->req_document_id }}"
                                                               id="approved_id_{{ $provider_document->req_document_id }}">
                                                                @if($provider_document->status == 0)
                                                                    Pending
                                                                @elseif($provider_document->status == 1)
                                                                    Approved
                                                                @else
                                                                    Rejected
                                                                @endif
                                                            </p>
                                                            @if(isset($provider_document->document_file))
                                                                <a href="{{ asset('/assets/images/provider-documents/'.$provider_document->document_file) }}"
                                                                   target="_blank">
                                                                    <i class="fa fa-eye bg-c-blue document_view"
                                                                       data-toggle="tooltip" data-placement="top"
                                                                       title="View"></i>
                                                                </a>
                                                            @else
                                                                <a>
                                                                    <i class="fa fa-eye-slash bg-c-blue" data-toggle="tooltip"
                                                                       data-placement="top" title="View"></i>
                                                                </a>
                                                            @endif
                                                            <a class="document_status"
                                                               id="document_approved_id_{{ isset($provider_document->provider_document_id)? $provider_document->provider_document_id : ''}}"
                                                               document_approved_id="{{ isset($provider_document->provider_document_id)? $provider_document->provider_document_id : ''}}"
                                                               document_status="1">
                                                                <i class="fa fa-thumbs-up bg-c-green" data-toggle="tooltip"
                                                                   data-placement="top" title="Approve"></i>
                                                            </a>
                                                            <a class="document_status"
                                                               id="document_approved_id_{{ isset($provider_document->provider_document_id)? $provider_document->provider_document_id : ''}}"
                                                               document_approved_id="{{ isset($provider_document->provider_document_id)? $provider_document->provider_document_id : ''}}"
                                                               document_status="2">
                                                                <i class="fa fa-thumbs-down bg-c-red" data-toggle="tooltip"
                                                                   data-placement="top" title="Reject"></i>
                                                            </a>
                                                        @else
                                                            <p>
                                                                Document not found!
                                                            </p>
                                                            <a>
                                                                <i class="fa fa-eye-slash" style="background-color: #653996 !important;" data-toggle="tooltip"
                                                                   data-placement="top" title="View"></i>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @empty($provider_documents->first())
                                                    <p>
                                                        Document not found!
                                                    </p>
                                                    <a>
                                                        <i class="fa fa-eye-slash" style="background-color: #653996 !important;" data-toggle="tooltip"
                                                           data-placement="top" title="View"></i>
                                                    </a>
                                                @endempty
                                            </div>
                                        </div>
                                    </div>
                            @endforeach
                        @endif
                        <!-- project-ticket end -->
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
    <script type="text/javascript">
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
                                        console.log(result);
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
