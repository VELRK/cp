'use client';

import React, { useState, useEffect } from 'react';
import {
  getMe,
  login as apiLogin,
  register as apiRegister,
  logout as apiLogout,
  sendOtp as apiSendOtp,
  verifyOtp as apiVerifyOtp,
  resendOtp as apiResendOtp,
  invalidateApiCache,
} from '../lib/frontendApi';
import { getAppHomeUrl } from '../lib/api';
import { AuthContext, type AuthUser } from '../lib/auth-context-store';

export type { AuthUser };

const persistAuthSession = (token: string, user: AuthUser) => {
  if (typeof window === 'undefined') return;
  try {
    localStorage.setItem('nb_token', token);
    localStorage.setItem('nb_user', JSON.stringify(user));
  } catch (e) {
    console.warn('Could not persist auth session to localStorage', e);
  }
};

const clearAuthSession = () => {
  if (typeof window === 'undefined') return;
  try {
    localStorage.removeItem('nb_token');
    localStorage.removeItem('nb_user');
  } catch (e) {
    console.warn('Could not clear auth session from localStorage', e);
  }
};

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAuthModalOpen, setAuthModalOpen] = useState<'login' | 'register' | null>(null);

  const refreshUser = async () => {
    try {
      invalidateApiCache('/api/nb/me');
      const response = await getMe();
      if (response.data?.success && response.data.user) {
        const savedToken = (typeof window !== 'undefined' ? localStorage.getItem('nb_token') : null) || token || '';
        setUser(response.data.user);
        if (savedToken) {
          persistAuthSession(savedToken, response.data.user);
        }
      } else {
        clearAuthSession();
        setUser(null);
        setToken(null);
      }
    } catch (error) {
      console.error('Error fetching current user:', error);
      clearAuthSession();
      setUser(null);
      setToken(null);
    }
  };

  useEffect(() => {
    const savedToken = typeof window !== 'undefined' ? localStorage.getItem('nb_token') : null;
    const savedUserStr = typeof window !== 'undefined' ? localStorage.getItem('nb_user') : null;

    let parsedUser: AuthUser | null = null;
    if (savedUserStr) {
      try {
        parsedUser = JSON.parse(savedUserStr);
      } catch {
        parsedUser = null;
      }
    }

    if (savedToken && parsedUser) {
      // 1. Instant hydration: restore user in 0ms so logged-in state renders immediately
      setToken(savedToken);
      setUser(parsedUser);
      setLoading(false);

      // 2. Background verification (stale-while-revalidate)
      getMe()
        .then((response) => {
          if (response.data?.success && response.data.user) {
            setUser(response.data.user);
            persistAuthSession(savedToken, response.data.user);
          } else if (response.status === 401 || (response.data && response.data.success === false)) {
            clearAuthSession();
            setUser(null);
            setToken(null);
          }
        })
        .catch((err) => {
          if (err?.response?.status === 401 || err?.response?.status === 403) {
            clearAuthSession();
            setUser(null);
            setToken(null);
          }
        });
    } else if (savedToken) {
      // Token exists without cached user
      setToken(savedToken);
      getMe()
        .then((response) => {
          if (response.data?.success && response.data.user) {
            setUser(response.data.user);
            persistAuthSession(savedToken, response.data.user);
          } else {
            clearAuthSession();
            setUser(null);
            setToken(null);
          }
        })
        .catch(() => {
          clearAuthSession();
          setUser(null);
          setToken(null);
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      // Guest user (not logged in)
      setLoading(false);
    }

    // 3. Cross-tab synchronization
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'nb_token' || e.key === 'nb_user') {
        const currentToken = localStorage.getItem('nb_token');
        const currentUserStr = localStorage.getItem('nb_user');
        if (currentToken && currentUserStr) {
          try {
            setUser(JSON.parse(currentUserStr));
            setToken(currentToken);
            setLoading(false);
          } catch {
            setUser(null);
            setToken(null);
            setLoading(false);
          }
        } else {
          setUser(null);
          setToken(null);
          setLoading(false);
        }
      }
    };
    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, []);

  const login = async (loginId: string, passwordStr: string) => {
    const response = await apiLogin(loginId, passwordStr);
    if (response.data?.success) {
      const { token: receivedToken, user: receivedUser } = response.data;
      persistAuthSession(receivedToken, receivedUser);
      setToken(receivedToken);
      setUser(receivedUser);
      setLoading(false);
      setAuthModalOpen(null);
    }
    return response.data;
  };

  const completeOtpSignIn = (receivedToken: string, receivedUser: AuthUser) => {
    persistAuthSession(receivedToken, receivedUser);
    setToken(receivedToken);
    setUser(receivedUser);
    setLoading(false);
    setAuthModalOpen(null);
  };

  const sendOtp = async (phone: string, countryCode = '+91') => {
    const response = await apiSendOtp(phone, countryCode);
    return response.data;
  };

  const verifyOtp = async (phone: string, otp: string, countryCode = '+91') => {
    const response = await apiVerifyOtp(phone, otp, countryCode);
    if (response.data?.success) {
      const { token: receivedToken, user: receivedUser } = response.data;
      completeOtpSignIn(receivedToken, receivedUser);
    }
    return response.data;
  };

  const resendOtp = async (phone: string, countryCode = '+91') => {
    const response = await apiResendOtp(phone, countryCode);
    return response.data;
  };

  const registerUser = async (formData: FormData) => {
    const response = await apiRegister(formData);
    if (response.data?.success) {
      const { token: receivedToken, user: receivedUser } = response.data;
      if (receivedToken) {
        if (receivedUser) {
          persistAuthSession(receivedToken, receivedUser);
        } else {
          localStorage.setItem('nb_token', receivedToken);
        }
        setToken(receivedToken);
        if (receivedUser) {
          setUser(receivedUser);
          setLoading(false);
        }
      }
      setAuthModalOpen(null);
    }
    return response.data;
  };

  const logout = async () => {
    clearAuthSession();
    setToken(null);
    setUser(null);
    setLoading(false);
    setAuthModalOpen(null);

    try {
      await apiLogout();
    } catch (e) {
      console.error('Logout request failed', e);
    } finally {
      clearAuthSession();
      setToken(null);
      setUser(null);
      setLoading(false);
      setAuthModalOpen(null);
      window.location.href = getAppHomeUrl();
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        loading,
        isAuthModalOpen,
        setAuthModalOpen,
        login,
        sendOtp,
        verifyOtp,
        resendOtp,
        registerUser,
        logout,
        refreshUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
