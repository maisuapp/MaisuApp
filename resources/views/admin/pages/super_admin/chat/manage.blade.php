@extends('admin.layout.super_admin')
@section('title')
    @if($type == 0) Users @elseif($type == 1) Stores @elseif($type == 2) Drivers @elseif($type == 3) Providers @else @endif List
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/widget/widget.css')}}">
    <style>
        .chat-box {
            border-bottom: 1px solid #D2D2D2;
            border-radius: 2px 2px 0 0;
            width: 98%;
            margin: 0 10px;
        }

        .admin_unread {
            background: aliceblue;
        }
        .chat-box-ui {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .show-profile {
            background-color: #4099FF;
            margin-right: 10px;
            padding: 5px 10px;
            border-radius: 3px;
            border: 1px solid #4099FF;
        }
        .show-profile > i {
            color: #FFFFFF;
        }
        .delete {
            background-color: #e03d38;
            padding: 5px 10px;
            border-radius: 3px;
            border: 1px solid #e03d38;
            margin-right: 10px;
        }
        .delete > i {
            color: #FFFFFF;
        }
        /*.chat-box:last-of-type {*/
        /*     border-bottom: none;*/
        /*}*/

    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5 id="breadcrumb-h5">Chat Conversation @if($type == 0) Users @elseif($type == 1) Stores @elseif($type == 2) Drivers @elseif($type == 3) Providers @else @endif List</h5>
                                    <span>All @if($type == 0) Users @elseif($type == 1) Stores @elseif($type == 2) Drivers @elseif($type == 3) Providers @else @endif List</span>
                                </div>
                            </div>
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
                                <h5>@if($type == 0) Users @elseif($type == 1) Stores @elseif($type == 2) Drivers @elseif($type == 3) Providers @else @endif List</h5>
                            </div>
                            <div class="card-block" id="chat-users-list">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-js')
    <script>
        function deleteMessage(self) {
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        var messageId = self.getAttribute('data-id');
                        var receiver_id = self.getAttribute('data-receiver-id');

                        database.ref('{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/messages/').child('a_1_'+receiver_id).remove().then(() => {
                            // console.log("Delete Chats Success Fully");
                        }).catch((error) => {
                            console.error("Error removing messages: ", error);
                        });

                        database.ref('{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/a_1/').child(messageId).remove().then(() => {
                            // console.log("Delete Success Fully");
                            swal("Success", "Your Data is deleted successfully :)", "success");
                        }).catch((error) => {
                            console.error("Error removing : ", error);
                        });
                        /*location.reload();*/
                    } else {
                        swal("Cancelled", "Your Data is safe :)", "error");
                    }
                });
        }

        $(document).ready(function (){
            let default_img = "{{asset('assets/images/profile-images/customer/default.png')}}";
            var chat_link = "{{ route('get:admin:chat_user_message_list',["string"=>$type_string,"type"=>$type,":snapshot_key"]) }}";
            var temp_chat_link = '';
            {{--var customer_edit_link = "{{route("get:admin:edit_user",preg_replace("/[^0-9]/", "", ":receiver_id"))}}";--}}
            {{--var provider_edit_link = "{{route('get:admin:other_service_edit_provider',['slug'=>"on-demand",'id'=>preg_replace("/[^0-9]/", "", ":receiver_id"),'status'=>1])}}";--}}
            let reference = "{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/a_1";
            var data = database.ref(reference).orderByChild("user_type").equalTo({{$type}});
            var childData = '';
            var childKey = '';


            data.on('value', function (snapshot) {
                $("#chat-users-list").empty();
                var html = '';
                snapshot.forEach(function (childSnapshot) {
                    childData = childSnapshot.val();
                    // console.log("child data",childData);
                    childKey = childSnapshot.key;
                    temp_chat_link = chat_link.replace(":snapshot_key",childData['receiver_id']);
                    // customer_edit_link = customer_edit_link.replace(":receiver_id",childData['receiver_id']);
                    // provider_edit_link = provider_edit_link.replace(":receiver_id",childData['receiver_id']);

                    html += '<div class="admin_seen_bgcolor_'+childData['receiver_id']+' '+(childData['admin_seen'] === false ?"admin_unread":"")+'">'+
                        '<div class="chat-box-ui chat-box">'+
                        '<div class="p-2">'+
                        '<img src="'+((childData['profile_picture'] !== undefined && childData['profile_picture'] !== '') ? childData['profile_picture'] : default_img)+'" alt="user image" height="40" width="40" class="img-radius img-40 align-top m-r-15">'+
                        '<div class="d-inline-block">'+
                        '<a href="'+temp_chat_link+'">'+
                        '<h6>'+childData['receiver_name']+'</h6>'+
                        '</a>'+
                        '<p class="text-muted m-b-0">'+(childData['last_message'].length > 100 ? childData['last_message'].substring(0, 100) : childData['last_message'])+'</p>'+
                        '</div>'+
                        '</div>'+
                        '<div class="d-flex flex-row justify-content-between align-items-center align-content-center">'+
                        // '<a href="'+(childData['user_type'] === "Customer" ? customer_edit_link : provider_edit_link)+'" class="show-profile">'+
                        //     '<i class="fa fa-user"></i>'+
                        // '</a>'+
                        '<a class="delete" data-id="'+childKey+'" data-receiver-id="'+childData['receiver_id']+'" onclick="deleteMessage(this);">'+
                        '<i class="fa fa-trash"></i>'+
                        '</a>'+
                        '</div>'+
                        '</div>'+
                        '</div>';

                    document.getElementById("chat-users-list").innerHTML = html;
                });
            });
        });
    </script>
@endsection
