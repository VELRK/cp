'use client';

import React, { useState, useEffect } from 'react';
import { getMe, login as apiLogin, register as apiRegister, logout as apiLogout, sendOtp as apiSendOtp, verifyOtp as apiVerifyOtp, resendOtp as apiResendOtp } from '../lib/frontendApi';
import { getAppHomeUrl } from '../lib/api';
import { AuthContext, type AuthUser } from '../lib/auth-context-store';

export type { AuthUser };

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAuthModalOpen, setAuthModalOpen] = useState<'login' | 'register' | null>(null);

  const refreshUser = async () => {
    try {
      const response = await getMe();
      if (response.data?.success) {
        setUser(response.data.user);
      } else {
        localStorage.removeItem('nb_token');
        setUser(null);
        setToken(null);
      }
    } catch (error) {
      console.error('Error fetching current user:', error);
      localStorage.removeItem('nb_token');
      setUser(null);
      setToken(null);
    }
  };

  useEffect(() => {
    const savedToken = localStorage.getItem('nb_token');
    if (savedToken) {
      setToken(savedToken);
      getMe()
        .then((response) => {
          if (response.data?.success) {
            setUser(response.data.user);
          } else {
            localStorage.removeItem('nb_token');
          }
        })
        .catch(() => {
          localStorage.removeItem('nb_token');
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (loginId: string, passwordStr: string) => {
    const response = await apiLogin(loginId, passwordStr);
    if (response.data?.success) {
      const { token: receivedToken, user: receivedUser } = response.data;
      localStorage.setItem('nb_token', receivedToken);
      setToken(receivedToken);
      setUser(receivedUser);
      setAuthModalOpen(null);
    }
    return response.data;
  };

  const completeOtpSignIn = (receivedToken: string, receivedUser: AuthUser) => {
    localStorage.setItem('nb_token', receivedToken);
    setToken(receivedToken);
    setUser(receivedUser);
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
      localStorage.setItem('nb_token', receivedToken);
      setToken(receivedToken);
      setUser(receivedUser);
      setAuthModalOpen(null);
    }
    return response.data;
  };

  const logout = async () => {
    localStorage.removeItem('nb_token');
    setToken(null);
    setUser(null);
    setAuthModalOpen(null);

    try {
      await apiLogout();
    } catch (e) {
      console.error('Logout request failed', e);
    } finally {
      localStorage.removeItem('nb_token');
      setToken(null);
      setUser(null);
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
