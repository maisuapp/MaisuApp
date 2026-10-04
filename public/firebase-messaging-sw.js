/*
 Give the service worker access to Firebase Messaging.
 Note that you can only use Firebase Messaging here. Other Firebase libraries
 are not available in the service worker.
*/

importScripts('https://www.gstatic.com/firebasejs/8.7.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.7.1/firebase-messaging.js');

// For Firebase JS SDK v7.20.0 and later, measurementId is optional
/*
 Initialize the Firebase app in the service worker by passing in
 your app's Firebase config object.
 https://firebase.google.com/docs/web/setup#config-object
*/

firebase.initializeApp({
    apiKey: "AIzaSyBOkv8tfNQdoMpTXcUBiLlFGuM7w1OzErs",
    authDomain: "maisuapp-91580.firebaseapp.com",
    databaseURL: "https://maisuapp-91580-default-rtdb.firebaseio.com",
    projectId: "maisuapp-91580",
    storageBucket: "maisuapp-91580.appspot.com",
    messagingSenderId: "285766204011",
    appId: "1:285766204011:web:424d0d988f099307834f2e",
    measurementId: "G-JK7S79PS91"
});

/*
 Retrieve an instance of Firebase Messaging so that it can handle background messages.
*/
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        // icon: payload.notification.icon,
    };
    self.registration.showNotification(notificationTitle,
        notificationOptions);
});
