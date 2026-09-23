import { initializeApp, getApps, type FirebaseApp, type FirebaseOptions } from 'firebase/app';
import { getMessaging, getToken, onMessage, isSupported, type Messaging } from 'firebase/messaging';
import api from './api';
import { getAppBasePath } from './appBasePath';
import { API_PATHS } from './frontendApi';

type FirebaseWebConfig = FirebaseOptions & { vapidKey?: string };

let app: FirebaseApp | null = null;
let messaging: Messaging | null = null;
let registering = false;
let configPromise: Promise<FirebaseWebConfig | null> | null = null;

function appBasePath(): string {
  return getAppBasePath();
}

async function loadFirebaseWebConfig(): Promise<FirebaseWebConfig | null> {
  if (!configPromise) {
    configPromise = api
      .get(API_PATHS.firebaseConfig)
      .then((res) => {
        const body = res.data || {};
        if (!body.success || !body.enabled || !body.config?.apiKey || !body.config?.appId) {
          return null;
        }
        return {
          ...body.config,
          vapidKey: body.vapidKey || '',
        } as FirebaseWebConfig;
      })
      .catch(() => null);
  }
  return configPromise;
}

function getFirebaseApp(options: FirebaseOptions): FirebaseApp {
  if (app) return app;
  app = getApps().length ? getApps()[0] : initializeApp(options);
  return app;
}

export async function registerWebPush(): Promise<string | null> {
  if (typeof window === 'undefined' || registering) {
    return null;
  }
  if (!('Notification' in window) || !('serviceWorker' in navigator)) {
    return null;
  }
  const supported = await isSupported().catch(() => false);
  if (!supported) {
    return null;
  }
  registering = true;
  try {
    const cfg = await loadFirebaseWebConfig();
    if (!cfg?.apiKey || !cfg.appId) {
      return null;
    }
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
      return null;
    }
    const firebaseApp = getFirebaseApp(cfg);
    messaging = getMessaging(firebaseApp);
    const base = appBasePath();
    const swUrl = `${base}/firebase-messaging-sw.js`;
    const registration = await navigator.serviceWorker.register(swUrl, { scope: `${base}/` || '/' });
    const vapidKey = cfg.vapidKey || '';
    const tokenOpts: { vapidKey?: string; serviceWorkerRegistration: ServiceWorkerRegistration } = {
      serviceWorkerRegistration: registration,
    };
    if (vapidKey) {
      tokenOpts.vapidKey = vapidKey;
    }
    const token = await getToken(messaging, tokenOpts);
    if (!token) {
      return null;
    }
    await api.post(API_PATHS.fcmToken, { fcm_token: token, platform: 'web' });
    onMessage(messaging, (payload) => {
      const n = payload.notification;
      if (!n?.title || Notification.permission !== 'granted') {
        return;
      }
      try {
        new Notification(n.title, {
          body: n.body || '',
          icon: `${base}/assets/img/nb-placeholder-property.svg`,
        });
      } catch {
        /* ignore */
      }
    });
    return token;
  } catch (e) {
    console.warn('Web push registration skipped', e);
    return null;
  } finally {
    registering = false;
  }
}
