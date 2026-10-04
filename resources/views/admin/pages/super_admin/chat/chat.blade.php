@extends('admin.layout.super_admin')
@section('title')
    User Chat
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/widget/widget.css')}}">
    <style>
        .text-time-size{
            font-size: 11px;
        }
        .chat-error-message{
            display: none;
        }
    </style>
@endsection
@section('page-content')
    <div class="pcoded-content">
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
{{--                                    <h5>@if($type == 0) User @elseif($type == 1) Store @elseif($type == 2) Driver @elseif($type == 3) Provider @else @endif</h5>--}}
                                    <h5 id="chat_user_provider_name"></h5>
                                    <span>Chat Details</span>
                                </div>
                            </div>
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
                                <h5>Messages</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-xl-12 col-md-12">
                                        <div class="chat-card">
                                            <div class="" id="chat_list"></div>
                                            <div class="right-icon-control">
                                                <div class="input-group input-group-button">
                                                    <input type="text" class="form-control"
                                                           name="message"
                                                           id="message"
                                                           placeholder="Send message">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary chat"
{{--                                                                sender_id="{{$sender_id}}"--}}
{{--                                                                senderName="Super Admin"--}}
{{--                                                                receiverId="{{$get_user_detail['receiver_id']}}"--}}
{{--                                                                receiverName="{{$get_user_detail['receiver_name']}}"--}}
{{--                                                                userProfile="{{$get_user_detail['profile_picture']}}"--}}
{{--                                                                services="{{$get_user_detail['services']}}"--}}
{{--                                                                userType="{{$get_user_detail['user_type']}}"--}}
                                                                type="button">
                                                            <i class="feather icon-message-circle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <p class="error chat-error-message">Message text field is required</p>
                                            </div>
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
    <script>
        // user id
        var u_id = "{{$id}}";

        // admin reference a_1
        var a_id = "{{$sender_id}}";

        var sender_id = a_id;
        var senderName = "Admin";
        var child_Data = [];
        var child_Key;
        var admin_reference;
        var user_fcm;
        var time_stamp;
        var localDate;

        function chatMessages() {
            // get chat messages
            var childData = '';
            var sender_check = '';
            var chat_ref = "{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/messages/a_1_"+u_id;
            var leadsRef = database.ref(chat_ref);
            leadsRef.on('value', function (snapshot) {
                $("#chat_list").empty();
                var html = '';
                snapshot.forEach(function (childSnapshot) {
                    childData = childSnapshot.val();
                    sender_check = childData['sender_id'];

                    localDate = new Date(childData['date'] + " GMT");

                    if (a_id !== sender_check) {
                        html += '<div class="row m-b-20 received-chat">\n' +
                            // '<div class="col-auto p-r-0">\n' +
                            // '<img \n' +
                            // 'alt="user image"\n' +
                            // 'class="img-radius img-40">\n' +
                            // '</div>\n' +
                            '<div class="col">\n' +
                            '<div class="msg">\n' +
                            '<p class="m-b-0">' + childData['message'] + '</p>\n' +
                            '</div>\n' +
                            '<p class="text-muted m-b-0 text-time-size"><i\n' +
                            'class="fa fa-clock-o m-r-10"></i>' + localDate.toLocaleString() + '</p>\n' +
                            '</div>\n' +
                            '</div>';
                    } else {
                        html += '<div class="row m-b-20 send-chat">\n' +
                            '<div class="col">\n' +
                            '<div class="msg">\n' +
                            '<p class="m-b-0">' + childData['message'] + '</p>\n' +
                            '</div>\n' +
                            '<p class="text-muted m-b-0 text-time-size"><i\n' +
                            'class="fa fa-clock-o m-r-10"></i>' + localDate.toLocaleString() + '\n' +
                            '</p>\n' +
                            '</div>\n' +
                            // '<div class="col-auto p-l-0">\n' +
                            // '<img \n' +
                            // 'alt="user image"\n' +
                            // 'class="img-radius img-40">\n' +
                            // '</div>\n' +
                            '</div>';
                    }
                    document.getElementById("chat_list").innerHTML = html;
                });
            });
        }

        async function userDetails() {
            // get user details
            var get_ref = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/a_1").orderByChild('receiver_id').equalTo(u_id);
            var snapshot = await get_ref.once('value');

            if(snapshot.exists()) {
                snapshot.forEach(function (childSnapshot) {
                    var val = childSnapshot.val();
                    child_Data['child_Key'] = childSnapshot.key;
                    child_Data['receiverId'] = val['receiver_id'];
                    child_Data['receiverName'] = val['receiver_name'];
                    child_Data['userProfile'] = val['profile_picture'];
                    child_Data['services'] = val['services'];
                    child_Data['userType'] = val['user_type'];
                });
            } else {
                child_Data = 'NA';
            }

            // using snap key get admin ref and update admin seen
            $("#chat_user_provider_name").text(child_Data['receiverName']);
            admin_reference = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/a_1/"+child_Data['child_Key']);
            admin_reference.update({
                admin_seen: true,
            });

            console.log('child_Key');
            console.log(child_Data['child_Key']);
            return child_Data;
        }

        $(document).ready(function (){
            chatMessages();
            userDetails();

            // get user fcm tokens
            var fcm = "{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/fcm_token/";
            database.ref(fcm+u_id).once('value').then((snapshot) => {
                user_fcm = snapshot.val().fcm_token;
            });
        });

        $(document).on('click', '.chat', function (e) {
            var time_ref = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/server_timezone/timestamp");
            var admin_rf = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/messages/a_1_" + u_id);
            var user_rf = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/messages/" + u_id + "_a_1");

            var user_msg;
            var user_message = $("#message").val();
            var service_cat_id = "{{ isset($service_category) ? $service_category->id : 0 }}";

            var chat_user_rf = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/" + u_id);
            var chat_user_key;
            var chat_user_rf_update;

            time_ref.set(firebase.database.ServerValue.TIMESTAMP).then(function() {
                time_ref.once("value").then((snapshot) => {
                    time_stamp = snapshot.val();

                    var utc_date = new Date();
                    // var time = utc_date.toUTCString();

                    var month_format = utc_date.getUTCMonth();
                    month_format = month_format + 1;
                    if(month_format < 10) {
                        month_format = '0'+month_format
                    }
                    var seconds_format = utc_date.getUTCSeconds();
                    if(seconds_format < 10) {
                        seconds_format = '0'+seconds_format
                    }
                    var time = utc_date.getUTCFullYear() + "-" + (month_format) + "-" + utc_date.getUTCDate() + " " + utc_date.getUTCHours() + ":" + utc_date.getUTCMinutes() + ":" + (seconds_format);
                    e.preventDefault();

                    if (user_message === "") {
                        user_msg = "";
                        $(".chat-error-message").css({"display": "block"});
                    }
                    else {
                        $(".chat-error-message").css({"display": "none"});
                        user_msg = user_message;
                        var chat_msg = {
                            date: time,
                            message: user_msg,
                            sender_id: sender_id,
                            sender_name: senderName,
                        };

                        // push chat messages
                        admin_rf.child(time_stamp).set(chat_msg);
                        user_rf.child(time_stamp).set(chat_msg);

                        console.log('admin_reference');
                        console.log(admin_reference);
                        // update admin ref details
                        admin_reference.update({
                            admin_seen: true,
                            created_at: time,
                            last_message: user_msg,
                            profile_picture:child_Data['userProfile'],
                            receiver_id:child_Data['receiverId'],
                            receiver_name:child_Data['receiverName'],
                            services:child_Data['services'],
                            user_type:child_Data['userType']
                        });

                        chat_user_rf.orderByChild("receiver_id").equalTo("a_1").once("value").then((snapshot) => {
                            if (snapshot.exists()) {
                                snapshot.forEach(function(childSnapshot) {
                                    chat_user_key = childSnapshot.key; // you will get your key here
                                });
                                chat_user_rf_update = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/" + u_id + "/" + chat_user_key);
                                chat_user_rf_update.update({
                                    created_at: time,
                                    last_message: user_msg,
                                    profile_picture:'',
                                    receiver_id:"a_1",
                                    receiver_name:"Admin",
                                    services:"",
                                });
                            } else {
                                chat_user_rf_update = database.ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/" + u_id);
                                chat_user_rf_update.push({
                                    created_at: time,
                                    last_message: user_msg,
                                    profile_picture:'',
                                    receiver_id:"a_1",
                                    receiver_name:"Admin",
                                    services:"",
                                });
                            }
                        });

                        // send notification to user
                        $.ajax({
                            type: 'get',
                            url: '{{ route("get:admin:chat_message") }}',
                            data: {
                                receiver_id: child_Data['receiverId'],
                                user_message: user_msg,
                                user_fcm:user_fcm,
                            },
                            success: function (result) {
                                console.log(result);
                                if (result.success === true) {
                                    //console.log(result.message)
                                    console.log("T");
                                } else {
                                    console.log("F");
                                    //console.log(result.message);
                                }
                            }
                        });

                        user_message = $("#message").val("");
                    }
                });
            }).catch((error) => {
                console.error("error");
                console.error(error);
            });
        });
    </script>
@endsection
