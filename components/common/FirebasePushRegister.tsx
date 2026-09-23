'use client';

import { useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { registerWebPush } from '@/lib/firebase';

/** Asks for browser notification permission after login and saves the FCM token. */
export default function FirebasePushRegister() {
  const { user, token, loading } = useAuth();

  useEffect(() => {
    if (loading || !user || !token) {
      return;
    }
    registerWebPush();
  }, [user, token, loading]);

  return null;
}
