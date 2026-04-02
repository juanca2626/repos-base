importScripts('https://www.gstatic.com/firebasejs/5.5.2/firebase-app.js')
importScripts('https://www.gstatic.com/firebasejs/5.5.2/firebase-messaging.js')

firebase.initializeApp({
    projectId: "firebase-limatours",
    messagingSenderId: "541786854376"
})

const messaging = firebase.messaging()

messaging.setBackgroundMessageHandler(payload => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
});
