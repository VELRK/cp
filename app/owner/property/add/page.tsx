'use client';

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import PropertyForm from '@/components/property/PropertyForm';
import Link from 'next/link';
import { ArrowLeft } from 'lucide-react';

export default function AddPropertyPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!authLoading && (!user || user.role !== 'owner')) {
      router.push('/login?redirect=/owner/property/add');
    }
  }, [user, authLoading, router]);

  if (authLoading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  if (!user || user.role !== 'owner') {
    return null;
  }

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link
            href="/owner/dashboard"
            className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0"
          >
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          <h1 className="h3 fw-bold text-dark mb-1">List New Property</h1>
          <p className="text-muted small mb-4">
            Add your property details — cities and property types load from the admin panel.
          </p>

          <PropertyForm ownerMode />
        </div>
      </div>
    </div>
  );
}
