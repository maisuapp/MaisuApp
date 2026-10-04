@extends('admin.layout.super_admin')
@section('title')
    Push Notification
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
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
                            <h5>Push Notification</h5>
                            <span>Push Notification</span>
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
                            <div class="form-group col-sm-12">
                                <form id="main" method="post"
                                      action="{{ route('post:admin:update_push_notification') }}"
                                      enctype="multipart/form-data">
                                    {{csrf_field() }}
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Send Notification</h5>
                                            {{--<a href="{{ route('get:admin:user_list') }}"--}}
                                            {{--class="btn btn-primary m-b-0 btn-right render_link"> Back</a>--}}
                                        </div>
                                        <div class="card-block">

                                            <div class="row">
                                                <div class="form-group col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Notification Type:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <select type="text" class="form-control"
                                                                    name="notification_type"
                                                                    required id="notification_type">
                                                                <option disabled selected>Select Notification Type
                                                                </option>
                                                                <option value="1">All Users, Providers</option>
                                                                <option value="2">All Users</option>
                                                                <option value="5">All Providers</option>

                                                            </select>
                                                            <span class="error">{{ $errors->first('notification_type') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Title:<sup
                                                                class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <textarea class="form-control" name="title"
                                                                      id="title" required
                                                                      placeholder="Title">{{ (isset($store_details)) ? $store_details->title : old('title') }}</textarea>
                                                            <span class="error">{{ $errors->first('title') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-4 col-form-label">Message:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-8">
                                                            <textarea class="form-control" name="message"
                                                                      id="message" required
                                                                      placeholder="Message">{{ (isset($store_details)) ? $store_details->message : old('message') }}</textarea>
                                                            <span class="error">{{ $errors->first('message') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <center>
                                                <button type="submit" id="submitPushNotify" class="btn btn-primary m-b-0">Send</button>
                                            </center>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="form-group col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Notification List</h5>
                                    </div>
                                    <div class="card-block">

                                        <div class="dt-responsive table-responsive">
                                            <table id="new-cons" class="table table-striped table-bordered nowrap"
                                                   style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Notification Type</th>
                                                    <th>Title</th>
                                                    <th>Message</th>
                                                    <th>Send Time</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @if(isset($push_notification))
                                                    @foreach($push_notification as $key => $notification_details)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            {{--                                                    <td>{{ $key + 1 }}</td>--}}
                                                            <td>{{ $notification_details->notification_type }}</td>
                                                            <td>{{ ($notification_details->title != "")?$notification_details->title:"--" }}</td>
                                                            <td>{{ $notification_details->message }}</td>
                                                            <td>
                                                                <?php  $today = date('Y-m-d H:i:s'); ?>
                                                                @if((($notification_details->created_at->diffInSeconds($today)) / 1) <= 60 )
                                                                    {{ (($notification_details->created_at->diffInSeconds($today)) / 1) }}
                                                                    Seconds ago
                                                                @elseif((($notification_details->created_at->diffInMinutes($today)) / 1) <= 60 )
                                                                    {{ ($notification_details->created_at->diffInMinutes($today)) / 1 }}
                                                                    Minute Ago
                                                                @elseif((($notification_details->created_at->diffInHours($today)) / 1) < 24 )
                                                                    {{ ($notification_details->created_at->diffInHours($today)) / 1 }}
                                                                    Hour Ago
                                                                @elseif((($notification_details->created_at->diffInDays($today)) / 1) <= 7 )
                                                                    {{ ($notification_details->created_at->diffInDays($today)) / 1 }}
                                                                    Days Ago
                                                                @elseif((($notification_details->created_at->diffInWeeks($today)) / 1) < 4 )
                                                                    {{ ($notification_details->created_at->diffInWeeks($today)) / 1 }}
                                                                    Week Ago
                                                                @elseif((($notification_details->created_at->diffInMonths($today)) / 1) <= 12 )
                                                                    {{ ($notification_details->created_at->diffInMonths($today)) / 1 }}
                                                                    Month Ago
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
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
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}"></script>

    <script>
        $(document).ready(function () {
            let user_spamming = 0
            console.log(user_spamming)
            $('#submitPushNotify').on("click", function (e) {
                user_spamming += 1;
                if (user_spamming > 1) {
                    e.preventDefault()
                }
            });
        });
    </script>
@endsection

