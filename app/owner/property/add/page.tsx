'use client';

import React, { useEffect, useState, Suspense } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import PropertyForm from '@/components/property/PropertyForm';
import AgentKycOnboarding from '@/components/property/AgentKycOnboarding';
import Link from 'next/link';
import {
  ArrowLeft,
  ShieldCheck,
  Clock,
  CheckCircle2,
  AlertCircle,
  RefreshCw,
  Lock,
  ShieldAlert,
  ArrowRight,
  Building2,
  Phone,
  FileText,
  HelpCircle,
  AlertTriangle,
  Sparkles,
} from 'lucide-react';

function AddPropertyContent() {
  const { user, loading: authLoading, refreshUser } = useAuth();
  const router = useRouter();

  const [justSubmittedKyc, setJustSubmittedKyc] = useState(false);
  const [isReapplying, setIsReapplying] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);

  useEffect(() => {
    if (!authLoading && !user) {
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

  if (!user) {
    return null;
  }

  const isAgent =
    user.role === 'agent' ||
    user.user_type === 'agent';

  // KYC is rejected only if explicitly rejected by admin with a reason
  const isKycRejected =
    user.kyc_status === 'rejected' ||
    (Boolean(user.kyc_rejection_reason) && user.kyc_status !== 'approved');

  // KYC is approved ONLY if admin approved the KYC (or user is admin)
  const isKycApproved =
    !isKycRejected &&
    (user.role === 'admin' ||
      (user.kyc_status === 'approved' && (user.role === 'agent' || user.user_type === 'agent' || user.role === 'owner')));

  // KYC is pending if submitted and waiting for admin review (not allowed to post property yet)
  const isKycPending =
    !isKycApproved &&
    !isKycRejected &&
    (user.kyc_status === 'pending' ||
      justSubmittedKyc ||
      (isAgent && Boolean(user.aadhar_no) && user.kyc_status !== 'approved'));

  const handleCheckStatus = async () => {
    try {
      setIsRefreshing(true);
      await refreshUser();
    } finally {
      setIsRefreshing(false);
    }
  };

  const maskedAadhaar = user.aadhar_no
    ? `XXXX XXXX ${user.aadhar_no.replace(/\D/g, '').slice(-4) || 'XXXX'}`
    : 'XXXX XXXX XXXX';

  return (
    <div className="container py-5 mt-4">
      <div className="row justify-content-center">
        <div className="col-lg-10 col-xl-9">
          {/* Top Breadcrumb Navigation */}
          <div className="d-flex align-items-center justify-content-between mb-4">
            <Link
              href="/"
              className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1.5 p-0"
            >
              <ArrowLeft size={15} />
              <span className="fw-medium">Back to Home</span>
            </Link>

            {(isKycApproved || isAgent) && (
              <Link
                href="/owner/listings"
                className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-decoration-none fw-medium"
              >
                My Properties
              </Link>
            )}
          </div>

          {/* CASE 1: ONBOARDING KYC FORM */}
          {(!isKycApproved && !isKycPending && !isKycRejected) || isReapplying ? (
            <AgentKycOnboarding
              initialStep="kyc_form"
              onSuccess={() => {
                setJustSubmittedKyc(true);
                setIsReapplying(false);
                refreshUser();
              }}
              onCancel={() => {
                if (isReapplying) {
                  setIsReapplying(false);
                } else {
                  router.push('/');
                }
              }}
            />
          ) : isKycRejected ? (
            /* CASE 2: REJECTED STATE WITH PROMINENT REASON & ACTION */
            <div className="card border-0 shadow-lg rounded-4 overflow-hidden my-3 animate-fade-in">
              {/* Card Header */}
              <div
                className="p-4 p-md-5 position-relative border-bottom"
                style={{
                  background: 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 40%, #ffffff 100%)',
                  borderTop: '4px solid #e11d48',
                }}
              >
                <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <span
                    className="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5"
                    style={{
                      background: '#ffe4e6',
                      color: '#be123c',
                      border: '1px solid #fecdd3',
                      fontSize: '0.75rem',
                      letterSpacing: '0.5px',
                    }}
                  >
                    <ShieldAlert size={14} className="text-danger" />
                    <span>ACTION REQUIRED • KYC REJECTED</span>
                  </span>

                  <span className="small text-muted fw-semibold">
                    Agent Compliance Desk
                  </span>
                </div>

                <div className="d-flex align-items-start gap-3">
                  <div
                    className="p-3 rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0"
                    style={{
                      background: '#ffe4e6',
                      color: '#e11d48',
                      width: '52px',
                      height: '52px',
                    }}
                  >
                    <AlertCircle size={26} />
                  </div>
                  <div>
                    <h1
                      className="h3 fw-bold mb-2"
                      style={{ color: '#0f172a', letterSpacing: '-0.3px' }}
                    >
                      KYC Verification Not Approved
                    </h1>
                    <p className="text-muted small mb-0" style={{ maxWidth: '640px', lineHeight: '1.6' }}>
                      Your agent verification application was reviewed by our compliance team.
                      Please review the compliance note below, correct your information or document proof, and apply again.
                    </p>
                  </div>
                </div>
              </div>

              {/* Card Body */}
              <div className="p-4 p-md-5 bg-white">
                {/* Highlighted Compliance Feedback Box */}
                <div
                  className="p-4 rounded-3 mb-4 position-relative overflow-hidden"
                  style={{
                    background: '#fffdfd',
                    border: '1.5px solid #fecdd3',
                    borderLeft: '5px solid #e11d48',
                    boxShadow: '0 4px 16px rgba(225, 29, 72, 0.05)',
                  }}
                >
                  <div className="d-flex align-items-center gap-2 mb-2">
                    <span
                      className="text-uppercase fw-bold small"
                      style={{
                        color: '#be123c',
                        fontSize: '0.75rem',
                        letterSpacing: '0.8px',
                      }}
                    >
                      COMPLIANCE DESK REJECTION REASON
                    </span>
                  </div>

                  <div
                    className="fw-bold mb-3"
                    style={{
                      fontSize: '1.08rem',
                      color: '#0f172a',
                      lineHeight: '1.5',
                    }}
                  >
                    “{user.kyc_rejection_reason ||
                      'Document clarity or identity details did not meet compliance verification standards.'}”
                  </div>

                  <div
                    className="p-3 rounded-2 small d-flex align-items-center gap-2"
                    style={{
                      background: '#f8fafc',
                      border: '1px solid #e2e8f0',
                      color: '#475569',
                    }}
                  >
                    <span className="fw-bold text-primary">💡 How to fix:</span>
                    <span>
                      Tap <strong>Update Details & Apply Again</strong> below to update your documents or business information and re-submit.
                    </span>
                  </div>
                </div>

                {/* Submitted Details Snapshot Cards */}
                <div className="mb-4">
                  <div className="small fw-bold text-uppercase text-muted mb-2" style={{ letterSpacing: '0.6px' }}>
                    Current Credentials on File
                  </div>
                  <div className="row g-3">
                    <div className="col-md-6">
                      <div className="p-3 rounded-3 bg-light border h-100">
                        <div className="d-flex align-items-center gap-2 mb-1">
                          <Building2 size={16} className="text-secondary" />
                          <span className="small text-muted">Agency / Business Name</span>
                        </div>
                        <div className="fw-bold text-dark" style={{ fontSize: '0.95rem' }}>
                          {user.business_name || 'Sri kumar properties'}
                        </div>
                      </div>
                    </div>
                    <div className="col-md-6">
                      <div className="p-3 rounded-3 bg-light border h-100">
                        <div className="d-flex align-items-center gap-2 mb-1">
                          <FileText size={16} className="text-secondary" />
                          <span className="small text-muted">Aadhaar Number on File</span>
                        </div>
                        <div className="fw-bold text-dark" style={{ fontSize: '0.95rem' }}>
                          {maskedAadhaar}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Actions Footer */}
                <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                  <div className="d-flex flex-wrap gap-2.5">
                    <button
                      type="button"
                      onClick={() => setIsReapplying(true)}
                      className="btn rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                      style={{
                        background: 'linear-gradient(135deg, #0b2c56 0%, #071f3f 100%)',
                        letterSpacing: '0.2px',
                      }}
                    >
                      <span>Update Details & Apply Again</span>
                      <ArrowRight size={16} />
                    </button>
                    <Link
                      href="/"
                      className="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold"
                    >
                      Back to Home
                    </Link>
                  </div>

                  <div className="small text-muted d-flex align-items-center gap-1.5">
                    <HelpCircle size={15} />
                    <span>Need help? Contact support@coimbatoreproperties.com</span>
                  </div>
                </div>
              </div>
            </div>
          ) : isKycPending ? (
            /* CASE 3: KYC IN PROGRESS / PENDING REVIEW */
            <div className="card border-0 shadow-lg rounded-4 overflow-hidden my-3 animate-fade-in">
              {/* Header Banner */}
              <div
                className="p-4 p-md-5 position-relative border-bottom"
                style={{
                  background: 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 40%, #ffffff 100%)',
                  borderTop: '4px solid #f59e0b',
                }}
              >
                <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <span
                    className="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5"
                    style={{
                      background: '#fef3c7',
                      color: '#92400e',
                      border: '1px solid #fde68a',
                      fontSize: '0.75rem',
                      letterSpacing: '0.5px',
                    }}
                  >
                    <Clock size={14} className="text-warning" />
                    <span>UNDER REVIEW • KYC IN PROGRESS</span>
                  </span>

                  <span className="small text-muted fw-semibold">
                    CBE Compliance Desk
                  </span>
                </div>

                <div className="d-flex align-items-start gap-3">
                  <div
                    className="p-3 rounded-circle d-none d-sm-flex align-items-center justify-content-center flex-shrink-0"
                    style={{
                      background: '#fef3c7',
                      color: '#d97706',
                      width: '52px',
                      height: '52px',
                    }}
                  >
                    <Clock size={26} />
                  </div>
                  <div>
                    <h1
                      className="h3 fw-bold mb-2"
                      style={{ color: '#0f172a', letterSpacing: '-0.3px' }}
                    >
                      KYC Verification In Progress
                    </h1>
                    <p className="text-muted small mb-0" style={{ maxWidth: '640px', lineHeight: '1.6' }}>
                      Your agent identity credentials and Aadhaar proof have been submitted and are under compliance verification.
                      Property posting will unlock automatically once approved by our compliance team.
                    </p>
                  </div>
                </div>
              </div>

              {/* Body */}
              <div className="p-4 p-md-5 bg-white">
                <div className="row g-3 mb-4">
                  <div className="col-md-6">
                    <div className="p-3 rounded-3 bg-light border h-100">
                      <div className="d-flex align-items-center gap-2 mb-1">
                        <Building2 size={16} className="text-secondary" />
                        <span className="small text-muted">Business / Agency Name</span>
                      </div>
                      <div className="fw-bold text-dark">{user.business_name || 'Registered Agency'}</div>
                    </div>
                  </div>
                  <div className="col-md-6">
                    <div className="p-3 rounded-3 bg-light border h-100">
                      <div className="d-flex align-items-center gap-2 mb-1">
                        <FileText size={16} className="text-secondary" />
                        <span className="small text-muted">Aadhaar Card on File</span>
                      </div>
                      <div className="fw-bold text-dark font-monospace">{maskedAadhaar}</div>
                    </div>
                  </div>
                </div>

                <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                  <div className="d-flex flex-wrap gap-2.5">
                    <button
                      type="button"
                      onClick={handleCheckStatus}
                      disabled={isRefreshing}
                      className="btn btn-primary rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                      style={{
                        background: 'linear-gradient(135deg, #0b2c56 0%, #174276 100%)',
                      }}
                    >
                      <RefreshCw size={16} className={isRefreshing ? 'animate-spin' : ''} />
                      <span>{isRefreshing ? 'Checking Status...' : 'Check Verification Status'}</span>
                    </button>
                    <Link
                      href="/"
                      className="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold"
                    >
                      Back to Home
                    </Link>
                  </div>

                  <span className="small text-muted">
                    Verification typically completes in 2–4 hours.
                  </span>
                </div>
              </div>
            </div>
          ) : (
            /* CASE 4: KYC COMPLETED & APPROVED — SHOW STANDARD PROPERTY FORM */
            <div className="animate-fade-in">

              <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                  <h1 className="h3 fw-bold text-dark mb-1">List New Property</h1>
                  <p className="text-muted small mb-0">
                    Add your property details — cities and property types load dynamically.
                  </p>
                </div>
                <div className="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1.5 small fw-bold">
                  <ShieldCheck size={16} className="text-success" />
                  <span>Verified Agent Account</span>
                </div>
              </div>

              <PropertyForm ownerMode />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function AddPropertyPage() {
  return (
    <Suspense
      fallback={
        <div className="text-center py-5 my-5">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      }
    >
      <AddPropertyContent />
    </Suspense>
  );
}
