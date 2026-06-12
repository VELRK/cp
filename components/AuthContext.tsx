'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../lib/api';

interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: 'tenant' | 'customer' | 'owner' | 'admin';
  status: 'pending' | 'approved' | 'rejected';
  city_id: number;
  profile_pic?: string;
  aadhar_no?: string;
  aadhar_file?: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  loading: boolean;
  isAuthModalOpen: 'login' | 'register' | null;
  setAuthModalOpen: (modal: 'login' | 'register' | null) => void;
  login: (login: string, password: string) => Promise<any>;
  registerUser: (formData: FormData) => Promise<any>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAuthModalOpen, setAuthModalOpen] = useState<'login' | 'register' | null>(null);

  const refreshUser = async () => {
    try {
      const response = await api.get('/api/nb/me');
      if (response.data?.success) {
        setUser(response.data.user);
      } else {
        // Token invalid
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
      api.get('/api/nb/me')
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
    const response = await api.post('/api/nb/login', {
      login: loginId,
      password: passwordStr,
    });
    if (response.data?.success) {
      const { token: receivedToken, user: receivedUser } = response.data;
      localStorage.setItem('nb_token', receivedToken);
      setToken(receivedToken);
      setUser(receivedUser);
      setAuthModalOpen(null);
    }
    return response.data;
  };

  const registerUser = async (formData: FormData) => {
    // Note: register API accepts multipart/form-data directly
    const response = await api.post('/api/nb/register', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
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
    try {
      await api.post('/api/nb/logout');
    } catch (e) {
      console.error('Logout request failed', e);
    } finally {
      localStorage.removeItem('nb_token');
      setToken(null);
      setUser(null);
      setAuthModalOpen(null);
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
        registerUser,
        logout,
        refreshUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
