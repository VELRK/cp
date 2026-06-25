import { createContext } from 'react';

export interface AuthUser {
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

export interface AuthContextType {
  user: AuthUser | null;
  token: string | null;
  loading: boolean;
  isAuthModalOpen: 'login' | 'register' | null;
  setAuthModalOpen: (modal: 'login' | 'register' | null) => void;
  login: (login: string, password: string) => Promise<any>;
  registerUser: (formData: FormData) => Promise<any>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

/** Singleton React context — must live in a shared module to avoid duplicate bundles. */
export const AuthContext = createContext<AuthContextType | undefined>(undefined);
