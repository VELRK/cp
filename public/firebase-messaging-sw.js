/* eslint-disable no-undef */
importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js');

function firebaseConfigUrl() {
  const path = self.location.pathname || '';
  const base = path.replace(/\/firebase-messaging-sw\.js$/i, '');
  return (base || '') + '/api/nb/firebase-config';
}

function showPushNotification(payload) {
  const n = (payload && payload.notification) || {};
  const title = n.title || 'Coimbatore Properties';
  const options = {
    body: n.body || '',
    icon: 'assets/img/nb-placeholder-property.svg',
    data: (payload && payload.data) || {},
  };
  return self.registration.showNotification(title, options);
}

fetch(firebaseConfigUrl())
  .then(function (res) {
    return res.json();
  })
  .then(function (body) {
    const cfg = (body && body.config) || {};
    if (!cfg.apiKey || !cfg.appId) {
      return;
    }
    if (!firebase.apps.length) {
      firebase.initializeApp({
        apiKey: cfg.apiKey,
        authDomain: cfg.authDomain,
        projectId: cfg.projectId,
        storageBucket: cfg.storageBucket,
        messagingSenderId: cfg.messagingSenderId,
        appId: cfg.appId,
        measurementId: cfg.measurementId,
      });
    }
    const messaging = firebase.messaging();
    messaging.onBackgroundMessage(showPushNotification);
  })
  .catch(function () {
    /* config not saved yet */
  });
