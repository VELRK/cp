'use client';

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../../components/AuthContext';
import PropertyForm from '../../../../components/PropertyForm';
import Link from 'next/link';
import { ArrowLeft, ShieldAlert, Clock } from 'lucide-react';

export default function AddPropertyPage() {
  const { user, loading } = useAuth();
  const router = useRouter();

  // We do not force redirect immediately so users can see the Step 0 landing page.
  // Gating is handled inline inside the PropertyForm or after user logs in.

  if (loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }



  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link href="/owner/dashboard" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          <h1 className="h3 fw-bold text-dark mb-4">List New Property</h1>
          <PropertyForm />
        </div>
      </div>
    </div>
  );
}
