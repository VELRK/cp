'use client';

import { useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { getDashboardPathForRole } from '@/lib/dashboardPaths';

export default function LoginPage() {
  const { user, setAuthModalOpen } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (user) {
      router.replace(getDashboardPathForRole(user.role));
    } else {
      router.replace('/');
      setAuthModalOpen('login');
    }
  }, [user, router, setAuthModalOpen]);

  return (
    <div className="d-flex align-items-center justify-content-center min-vh-100">
      <div className="spinner-border text-primary" role="status">
        <span className="visually-hidden">Redirecting to login...</span>
      </div>
    </div>
  );
}
