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

  useEffect(() => {
    if (!loading && !user) {
      router.push('/login');
    }
  }, [user, loading, router]);

  if (loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  const isOwner = user.role === 'owner';
  const isApproved = user.status === 'approved';

  // If user is not an approved owner, show warning page
  if (!isOwner || !isApproved) {
    return (
      <div className="container py-5 mt-5">
        <div className="row justify-content-center">
          <div className="col-md-8 col-lg-6">
            <div className="card border-0 shadow-lg text-center p-5 rounded-4 bg-white" style={{ border: '1px solid rgba(0,0,0,0.05)' }}>
              {!isOwner ? (
                <div className="mb-4">
                  <div className="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-danger-subtle text-danger animate-pulse" style={{ width: '70px', height: '70px' }}>
                    <ShieldAlert size={36} />
                  </div>
                  <h2 className="h4 fw-bold text-dark mt-4 mb-3">Owner Account Required</h2>
                  <p className="text-muted small px-3">
                    You are currently logged in with a <strong className="text-danger text-capitalize">{user.role}</strong> account. Only accounts registered as an Owner can post property ads.
                  </p>
                  <p className="text-muted small px-3 mb-4">
                    Please log out and sign in using your Owner credentials, or reach out to support to change your account type.
                  </p>
                </div>
              ) : (
                <div className="mb-4">
                  <div className="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning" style={{ width: '70px', height: '70px' }}>
                    <Clock size={36} />
                  </div>
                  <h2 className="h4 fw-bold text-dark mt-4 mb-3">Approval Pending</h2>
                  <p className="text-muted small px-3">
                    Your Owner account status is currently <strong className="text-warning text-capitalize">{user.status}</strong>.
                  </p>
                  <p className="text-muted small px-3 mb-4">
                    To maintain quality listings in Coimbatore, our moderation team verifies all owner accounts. We appreciate your patience; you can list properties immediately once approved.
                  </p>
                </div>
              )}

              <div className="d-flex flex-column gap-2">
                <Link href="/" className="btn btn-danger text-dark fw-bold rounded-pill py-2.5">
                  Go to Homepage
                </Link>
                <button
                  type="button"
                  onClick={() => router.push(user.role === 'owner' ? '/owner/dashboard' : '/tenant/dashboard')}
                  className="btn btn-link text-decoration-none text-secondary small fw-semibold"
                >
                  Go to Dashboard
                </button>
              </div>
            </div>
          </div>
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
