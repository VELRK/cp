'use client';

import React, { useEffect, useState } from 'react';
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
} from 'lucide-react';

export default function AddPropertyPage() {
  const { user, loading: authLoading, refreshUser } = useAuth();
  const router = useRouter();
  const [hasCompletedKyc, setHasCompletedKyc] = useState(false);
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
    user.user_type === 'agent' ||
    Boolean(user.business_name) ||
    Boolean(user.aadhar_no);

  const isKycRejected =
    user.kyc_status === 'rejected' ||
    (Boolean(user.kyc_rejection_reason) && user.kyc_status !== 'approved');

  const isKycApproved =
    !isKycRejected &&
    (user.kyc_status === 'approved' ||
      user.kyc_approved === true ||
      (!isAgent && user.role === 'owner'));

  const isKycPending =
    !isKycApproved &&
    !isKycRejected &&
    (hasCompletedKyc ||
      user.kyc_status === 'pending' ||
      user.status === 'pending' ||
      Boolean(user.aadhar_no && (user.aadhar_file || user.business_name)));

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

            {isKycApproved && (user.role === 'owner' || user.role === 'agent') && (
              <Link
                href="/owner/listings"
                className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-decoration-none fw-medium"
              >
                My Properties
              </Link>
            )}
          </div>

          {/* CASE 1: ONBOARDING / RE-APPLYING FORM */}
          {(!isKycApproved && !isKycPending && !isKycRejected) || isReapplying ? (
            <AgentKycOnboarding
              onSuccess={() => {
                setHasCompletedKyc(true);
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
            /* CASE 2: LUXURY REJECTED STATE WITH PROMINENT REASON & ACTION */
            <div className="card border-0 shadow-lg rounded-4 overflow-hidden my-3">
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
                      Tap <strong>Update Details & Apply Again</strong> below to update your website URL (or leave it empty if inactive), check your phone number, and re-submit.
                    </span>
                  </div>
                </div>

                {/* Stepper Timeline */}
                <div className="mb-4 pb-3 border-bottom">
                  <div className="small fw-bold text-uppercase text-muted mb-3" style={{ letterSpacing: '0.6px' }}>
                    Verification Status
                  </div>
                  <div className="row g-2 text-center">
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-success-subtle text-success border border-success-subtle mb-1.5">
                        <CheckCircle2 size={18} className="mx-auto" />
                      </div>
                      <div className="small fw-bold text-dark">Account</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Active</div>
                    </div>
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-success-subtle text-success border border-success-subtle mb-1.5">
                        <CheckCircle2 size={18} className="mx-auto" />
                      </div>
                      <div className="small fw-bold text-dark">KYC Form</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Submitted</div>
                    </div>
                    <div className="col-3">
                      <div
                        className="p-2.5 rounded-3 mb-1.5"
                        style={{
                          background: '#ffe4e6',
                          color: '#e11d48',
                          border: '1.5px solid #fecdd3',
                        }}
                      >
                        <AlertTriangle size={18} className="mx-auto" />
                      </div>
                      <div className="small fw-bold text-danger">Review Result</div>
                      <div className="text-danger fw-bold" style={{ fontSize: '0.72rem' }}>Action Needed</div>
                    </div>
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-light text-muted border mb-1.5">
                        <Lock size={18} className="mx-auto text-muted" />
                      </div>
                      <div className="small fw-bold text-muted">Certified</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Locked</div>
                    </div>
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
                    <span>Need help? Contact support@superfinelabels.in</span>
                  </div>
                </div>
              </div>
            </div>
          ) : isKycPending ? (
            /* CASE 3: KYC IN PROGRESS / PENDING REVIEW (STRICTLY BLOCK PROPERTY FORM) */
            <div className="card border-0 shadow-lg rounded-4 overflow-hidden my-3">
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
                      Your Aadhaar identity and agency credentials have been submitted and are currently undergoing
                      official compliance verification. Property posting will unlock automatically once approved.
                    </p>
                  </div>
                </div>
              </div>

              {/* Body */}
              <div className="p-4 p-md-5 bg-white">
                {/* Stepper Timeline */}
                <div className="mb-4 pb-3 border-bottom">
                  <div className="small fw-bold text-uppercase text-muted mb-3" style={{ letterSpacing: '0.6px' }}>
                    Verification Process
                  </div>
                  <div className="row g-2 text-center">
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-success-subtle text-success border border-success-subtle mb-1.5">
                        <CheckCircle2 size={18} className="mx-auto" />
                      </div>
                      <div className="small fw-bold text-dark">Registered</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Completed</div>
                    </div>
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-success-subtle text-success border border-success-subtle mb-1.5">
                        <CheckCircle2 size={18} className="mx-auto" />
                      </div>
                      <div className="small fw-bold text-dark">KYC Details</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Submitted</div>
                    </div>
                    <div className="col-3">
                      <div
                        className="p-2.5 rounded-3 mb-1.5"
                        style={{
                          background: '#fef3c7',
                          color: '#b45309',
                          border: '1.5px solid #fde68a',
                        }}
                      >
                        <Clock size={18} className="mx-auto text-warning" />
                      </div>
                      <div className="small fw-bold text-primary">Compliance Review</div>
                      <div className="text-warning fw-bold" style={{ fontSize: '0.72rem' }}>In Progress</div>
                    </div>
                    <div className="col-3">
                      <div className="p-2.5 rounded-3 bg-light text-muted border mb-1.5">
                        <Lock size={18} className="mx-auto text-muted" />
                      </div>
                      <div className="small fw-bold text-muted">Certified Agent</div>
                      <div className="text-muted" style={{ fontSize: '0.72rem' }}>Pending</div>
                    </div>
                  </div>
                </div>

                {/* Details Snapshot */}
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
                      <div className="fw-bold text-dark">{maskedAadhaar}</div>
                    </div>
                  </div>
                </div>

                {/* Callout Notice */}
                <div
                  className="alert border-0 rounded-3 d-flex align-items-start gap-3 mb-4 p-3.5"
                  style={{
                    background: '#fffbeb',
                    border: '1px solid #fde68a',
                  }}
                >
                  <Clock size={20} className="text-warning flex-shrink-0 mt-0.5" />
                  <div className="small">
                    <div className="fw-bold text-dark mb-1">Your application is currently being verified.</div>
                    <div className="text-muted" style={{ lineHeight: '1.5' }}>
                      Our compliance desk verifies submitted credentials to maintain genuine real estate listings across Coimbatore.
                      Once approved, property listing unlocks automatically with full agent privileges.
                    </div>
                  </div>
                </div>

                {/* Actions */}
                <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                  <div className="d-flex flex-wrap gap-2.5">
                    <button
                      type="button"
                      onClick={handleCheckStatus}
                      disabled={isRefreshing}
                      className="btn rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                      style={{
                        background: 'linear-gradient(135deg, #0b2c56 0%, #071f3f 100%)',
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
            /* CASE 4: KYC APPROVED — SHOW STANDARD PROPERTY FORM */
            <>
              <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                  <h1 className="h3 fw-bold text-dark mb-1">List New Property</h1>
                  <p className="text-muted small mb-0">
                    Add your property details — cities and property types load from the admin panel.
                  </p>
                </div>
                <div className="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1.5 small fw-bold">
                  <ShieldCheck size={16} className="text-success" />
                  <span>Verified Agent Account</span>
                </div>
              </div>

              <PropertyForm ownerMode />
            </>
          )}
        </div>
      </div>
    </div>
  );
}
