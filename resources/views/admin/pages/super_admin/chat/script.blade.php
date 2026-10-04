<script>
    @if(Illuminate\Support\Facades\Auth::guard("admin")->check() && Illuminate\Support\Facades\Auth::guard("admin")->user()->roles == 1)

    const database  = firebase.database();

    // firebase.auth().fetchsigninmethodsforemail("admin@maisuapp.com")
    //     .then((signInMethods) => {
    //         if (signInMethods.length) {
    //             // The email already exists in the Auth database. You can check the
    //             // sign-in methods associated with it by checking signInMethods array.
    //             // Show the option to sign in with that sign-in method.
    //
    //             firebase.auth().signinwithemailandpassword("admin@maisuapp.com", "123456")
    //                 .then((userCredential) => {
    //                     // Signed in
    //                     var user = userCredential.user;
    //                     console.log("sign in");
    //                     // ...
    //                 })
    //                 .catch((error) => {
    //                     var errorCode = error.code;
    //                     var errorMessage = error.message;
    //                     console.log("sign in error");
    //                     console.log(errorCode);
    //                     console.log(errorMessage);
    //                 });
    //         } else {
    //             // User does not exist. Ask user to sign up.
    //             firebase.auth().createuserwithemailandpassword("admin@maisuapp.com", "123456")
    //                 .then((userCredential) => {
    //                     // Signed in
    //                     var user = userCredential.user;
    //                     // ...
    //                 })
    //                 .catch((error) => {
    //                     var errorCode = error.code;
    //                     var errorMessage = error.message;
    //                     // ..
    //                 });
    //
    //         }
    //     })
    //     .catch((error) => {
    //         console.log(error);
    //         // Some error occurred.
    //     });

    // firebase.auth().createUserWithEmailAndPassword("admin@maisuapp.com", "123456")
    //     .then((userCredential) => {
    //         // Signed in
    //         var user = userCredential.user;
    //         // ...
    //     })
    //     .catch((error) => {
    //         var errorCode = error.code;
    //         var errorMessage = error.message;
    //         // ..
    //     });

    firebase.auth().signInWithEmailAndPassword("admin@maisuapp.com", "123456")
        .then((userCredential) => {
            // Signed in
            var user = userCredential.user;
            console.log("sign in");
            // ...
        })
        .catch((error) => {
            var errorCode = error.code;
            var errorMessage = error.message;
            console.log("sign in error");
            console.log(errorCode);
            console.log(errorMessage);
        });

    firebase.auth().onAuthStateChanged((user) => {
        if (user) {
            // User is signed in, see docs for a list of available properties
            // https://firebase.google.com/docs/reference/js/firebase.User
            var uid = user.uid;
            // ...
        } else {
            // User is signed out
            // ...
        }
    });

    var connectedRef = firebase.database().ref(".info/connected");
    connectedRef.on("value", (snap) => {
        if (snap.val()) {
            database.ref("{{ isset($chat_replace_domain)? "/".$chat_replace_domain : "" }}/admin_status").set({
                admin_connected: true,
            });
            console.log(" connected");
        } else {
            database.ref("{{ isset($chat_replace_domain)? "/".$chat_replace_domain : "" }}/admin_status").set({
                admin_connected: false,
            });
            console.log("not connected");
        }
    });

    var db_ref = "{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/admin_status/admin_connected";
    var onDisconnectref = firebase.database().ref(db_ref);
    onDisconnectref.onDisconnect().set(false);

    firebase.database().ref("{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/fcm_token/a_1/").onDisconnect().set({
        fcm_token: "",
    });

    // window.addEventListener('beforeunload', function (e) {
    //     connectedRef = firebase.database().ref(".info/connected");
    //     connectedRef.on("value", (snap) => {
    //         if (snap.val()) {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: true,
    //             });
    //             console.log(" connected");
    //         } else {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: false,
    //             });
    //             console.log("not connected");
    //         }
    //     });
    // });
    // window.addEventListener('onfocus', function (e) {
    //     connectedRef = firebase.database().ref(".info/connected");
    //     connectedRef.on("value", (snap) => {
    //         if (snap.val()) {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: true,
    //             });
    //             console.log(" connected");
    //         } else {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: false,
    //             });
    //             console.log("not connected");
    //         }
    //     });
    // });
    // window.addEventListener('onblur', function (e) {
    //     connectedRef = firebase.database().ref(".info/connected");
    //     connectedRef.on("value", (snap) => {
    //         if (snap.val()) {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: true,
    //             });
    //             console.log(" connected");
    //         } else {
    //             database.ref("chat/admin_status").set({
    //                 admin_connected: false,
    //             });
    //             console.log("not connected");
    //         }
    //     });
    // });
    // document.addEventListener('visibilitychange', function (e) {
    //     if (document.hidden) {
    //         connectedRef = firebase.database().ref(".info/connected");
    //         connectedRef.on("value", (snap) => {
    //             if (snap.val()) {
    //                 database.ref("chat/admin_status").set({
    //                     admin_connected: true,
    //                 });
    //                 console.log(" connected");
    //             } else {
    //                 database.ref("chat/admin_status").set({
    //                     admin_connected: false,
    //                 });
    //                 console.log("not connected");
    //             }
    //         });
    //     }
    // });

    // check if admin is online or offline
    var starCountRef = firebase.database().ref('{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/users/a_1').orderByChild('admin_seen').equalTo(false);
    starCountRef.on('value', (snapshot) => {
        try {
            $("#admin_seen_or_not").hide();
            $("#admin_seen_or_not_customer").hide();
            $("#admin_seen_or_not_store").hide();
            $("#admin_seen_or_not_driver").hide();
            $("#admin_seen_or_not_provider").hide();

            $(".admin_unread").css("background-color", "transparent");

            var c_count = 0;
            var s_count = 0;
            var d_count = 0;
            var p_count = 0;
            snapshot.forEach(childSnapshot => {
                var childData = childSnapshot.val();
                if(childData['user_type'] === 0) {
                    if(!childData['admin_seen']) {
                        c_count = c_count + 1;
                        $("#admin_seen_or_not_customer").show();
                        $(".admin_seen_bgcolor_"+childData['receiver_id']).css("background-color", "aliceblue");
                    }
                    // else {
                    //     throw "Break"
                    // }
                }
                else if(childData['user_type'] === 1) {
                    if(!childData['admin_seen']) {
                        s_count = s_count + 1;
                        $("#admin_seen_or_not_store").show();
                        $(".admin_seen_bgcolor_"+childData['receiver_id']).css("background-color", "aliceblue");
                    }
                    // else {
                    //     throw "Break"
                    // }
                }
                else if(childData['user_type'] === 2) {
                    if(!childData['admin_seen']) {
                        d_count = d_count + 1;
                        $("#admin_seen_or_not_driver").show();
                        $(".admin_seen_bgcolor_"+childData['receiver_id']).css("background-color", "aliceblue");
                    }
                    // else {
                    //     throw "Break"
                    // }
                }
                else if(childData['user_type'] === 3) {
                    if(!childData['admin_seen']) {
                        p_count = p_count + 1;
                        $("#admin_seen_or_not_provider").show();
                        $(".admin_seen_bgcolor_"+childData['receiver_id']).css("background-color", "aliceblue");
                    }
                    // else {
                    //     throw "Break"
                    // }
                } else {
                    throw "Break"
                }
            });
            if(c_count > 0 || s_count > 0 || d_count > 0 || p_count > 0) {
                $("#admin_seen_or_not").show();
            }
        }
        catch(e) {
            // console.log("Ended");
        }
    });

    {{--$('<audio id="chatAudio"><source src="{{ asset('assets/notify/notify.ogg') }}" type="audio/ogg"><source src="{{ asset('assets/notify/notify.mp3') }}" type="audio/mpeg"><source src="{{ asset('assets/notify/notify.wav') }}" type="audio/wav"></audio>').appendTo('body');--}}

    const messaging = firebase.messaging();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var url = "{{ asset('firebase-messaging-sw.js') }}";
            navigator.serviceWorker.register(url)
                .then(function (registration) {
                    // messaging.useServiceWorker(registration);

                    // Request for permission
                    messaging.requestPermission()
                        .then(function () {
                            console.log('Notification permission granted.');
                            // TODO(developer): Retrieve an Instance ID token for use with FCM.
                            // web push certificate
                            messaging.getToken({vapidKey: 'BPRRYKl8M5C4cLwSrZknZHfepmqikqyvIj7QL7MHF4M57fGRzQwq-Osdb6VGevP19mPf5fmDatqn9FrNSiZEOh8'})
                                .then(function (currentToken) {
                                    if (currentToken) {
                                        //console.log('Token: ' + currentToken);
                                        sendTokenToServer(currentToken);
                                        storeToken(currentToken);
                                    } else {
                                        console.log('No Instance ID token available. Request permission to generate one.');
                                        setTokenSentToServer(false);
                                    }
                                })
                                .catch(function (err) {
                                    console.log('An error occurred while retrieving token. ', err);
                                    setTokenSentToServer(false);
                                });
                        })
                        .catch(function (err) {
                            console.log('Unable to get permission to notify.', err);
                        });
                });
        });
    }
    // Handle incoming messages

    messaging.onMessage(function (payload) {
        console.log("Notification received: ",payload);
        // console.log("Notification received: ", payload);
        // if (payload.data.is_chat_notification != true){
        var notify;
        notify = new Notification(payload.data.title, {
            'body': payload.data.desc,
            // 'tag': "s"
        });

        notify.onclick = function () {
            var url = '{{ route("get:admin:chat_user_message_list", [":string",":type",":id"]) }}';

            url = url.replace(':type', payload.data.user_type).replace(':id', payload.data.user_id);

            if(payload.data.user_type === 1 || payload.data.user_type === '1') {
                url = url.replace(':string', 'store');
            } else if(payload.data.user_type === 2 || payload.data.user_type === '2') {
                url = url.replace(':string', 'driver');
            } else if(payload.data.user_type === 3 || payload.data.user_type === '3') {
                url = url.replace(':string', 'provider');
            } else {
                url = url.replace(':string', 'user');
            }

            window.location = url;
            // alert(this.url);
        };

        //$('#chatAudio').play();
        // }
        // toastr["info"](payload.data.desc, payload.data.desc);
    });


    // Callback fired if Instance ID token is updated.
    messaging.onTokenRefresh(function () {
        messaging.getToken()
            .then(function (refreshedToken) {
                console.log('Token refreshed.');
                // Indicate that the new Instance ID token has not yet been sent
                // to the app server.
                setTokenSentToServer(false);
                // Send Instance ID token to app server.
                sendTokenToServer(refreshedToken);
                storeToken(refreshedToken);
            })
            .catch(function (err) {
                console.log('Unable to retrieve refreshed token ', err);
            });
    });

    // Send the Instance ID token your application server, so that it can:
    // - send messages back to this app
    // - subscribe/unsubscribe the token from topics
    function sendTokenToServer(currentToken) {
        if (!isTokenSentToServer()) {
            console.log('Sending token to server...');
            // TODO(developer): Send the current token to your server.
            setTokenSentToServer(true);
        } else {
            console.log('Token already sent to server so won\'t send it again ' +
                'unless it changes');
        }
    }

    function isTokenSentToServer() {
        return window.localStorage.getItem('sentToServer') == 1;
    }

    function setTokenSentToServer(sent) {
        window.localStorage.setItem('sentToServer', sent ? 1 : 0);
    }

    function storeToken(currentToken) {
        $.ajax({
            type: 'get',
            url: '{{ route("get:admin:update_web_token") }}',
            data: {
                web_token: currentToken
            },
            success: function (result) {
                if (result.success == true) {
                    var admin_fcm = "{{ isset($chat_replace_domain)? '/'.$chat_replace_domain : "" }}/fcm_token/a_1/";
                    database.ref(admin_fcm).set({
                        fcm_token: currentToken,
                    });
                } else {
                    // console.log(result.message)
                }
            }
        })
    }
    @endif
</script>
