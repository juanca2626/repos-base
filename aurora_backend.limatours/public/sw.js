importScripts('https://www.gstatic.com/firebasejs/5.5.2/firebase-app.js')
importScripts('https://www.gstatic.com/firebasejs/5.5.2/firebase-messaging.js')

firebase.initializeApp({
    projectId: "firebase-limatours",
    messagingSenderId: "541786854376"
})

const messaging = firebase.messaging()

messaging.setBackgroundMessageHandler( playload => {
    const tituloNoti = 'Testing Noti'
    const opcionesNoti = {
        body : payload.data.titulo,
        icon : 'images/icon.png',
        click_action : 'http://google.com'
    }

    return self.registration.showNotification(
      tituloNoti,
      opcionesNoti
    )
} )
