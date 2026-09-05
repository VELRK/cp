'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { getCities, updateProfile } from '@/lib/frontendApi';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import confetti from 'canvas-confetti';
import {
  ShieldCheck,
  Building2,
  CheckCircle2,
  AlertCircle,
  Upload,
  User,
  Phone,
  Mail,
  MapPin,
  Sparkles,
  ArrowRight,
  ArrowLeft,
  Briefcase,
  FileText,
  Lock,
  Check,
  TrendingUp,
  X
} from 'lucide-react';

interface City {
  id: number;
  name: string;
  state?: string;
}

interface AgentKycOnboardingProps {
  onSuccess: () => void;
  onCancel?: () => void;
}

export default function AgentKycOnboarding({ onSuccess, onCancel }: AgentKycOnboardingProps) {
  const { user, refreshUser } = useAuth();

  // Step 1: 'confirm' (Confirmation screen) | Step 2: 'kyc_form' (KYC Form) | Step 3: 'success'
  const [step, setStep] = useState<'confirm' | 'kyc_form' | 'success'>('confirm');

  const [cities, setCities] = useState<City[]>([]);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [cityId, setCityId] = useState('');
  const [businessName, setBusinessName] = useState('');
  const [specialization, setSpecialization] = useState('Residential & Commercial');
  const [website, setWebsite] = useState('');
  const [aadharNo, setAadharNo] = useState('');
  const [aadharFile, setAadharFile] = useState<File | null>(null);
  const [aadharFileName, setAadharFileName] = useState('');
  const [profilePic, setProfilePic] = useState<File | null>(null);
  const [profilePreview, setProfilePreview] = useState<string | null>(null);
  const [acceptTerms, setAcceptTerms] = useState(false);

  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  // Fetch Cities
  useEffect(() => {
    getCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((err) => console.error('Error fetching cities', err));
  }, []);

  // Pre-fill user data
  useEffect(() => {
    if (!user) return;
    setName(user.name || '');
    setEmail(user.email || '');
    setPhone(user.phone || '');
    if (user.city_id) setCityId(String(user.city_id));
    if (user.business_name) setBusinessName(user.business_name);
    if (user.aadhar_no) setAadharNo(user.aadhar_no);
    if (user.website) setWebsite(user.website);
    if (user.profile_pic) {
      setProfilePreview(toFrontendAssetUrl(user.profile_pic));
    }
  }, [user]);

  const handleProfilePicChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) {
      setErrorMsg('Profile photo must be less than 10 MB.');
      return;
    }
    setProfilePic(file);
    setProfilePreview(URL.createObjectURL(file));
    setErrorMsg(null);
  };

  const handleAadharFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) {
      setErrorMsg('Document proof must be less than 10 MB.');
      return;
    }
    setAadharFile(file);
    setAadharFileName(file.name);
    setErrorMsg(null);
  };

  const formatAadharDisplay = (val: string) => {
    const digits = val.replace(/\D/g, '').slice(0, 12);
    const parts = [];
    for (let i = 0; i < digits.length; i += 4) {
      parts.push(digits.slice(i, i + 4));
    }
    return parts.join(' ');
  };

  const handleSubmitKyc = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);

    if (!user) {
      setErrorMsg('Please sign in before submitting KYC.');
      return;
    }

    const cleanAadhar = aadharNo.replace(/\D/g, '');
    if (cleanAadhar.length !== 12) {
      setErrorMsg('Please enter a valid 12-digit Aadhaar number.');
      return;
    }

    if (!businessName.trim() || businessName.trim().length < 2) {
      setErrorMsg('Please enter your Business or Agency name (at least 2 characters).');
      return;
    }

    if (!cityId) {
      setErrorMsg('Please select your primary operating city.');
      return;
    }

    if (!user.aadhar_file && !aadharFile) {
      setErrorMsg('Please upload your Aadhaar document or government ID proof for agent KYC verification.');
      return;
    }

    if (!acceptTerms) {
      setErrorMsg('Please confirm the declaration to proceed.');
      return;
    }

    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('userId', String(user.id));
      formData.append('name', name.trim());
      formData.append('email', email.trim());
      formData.append('phone', phone.trim());
      formData.append('city_id', cityId);
      formData.append('user_type', 'agent');
      formData.append('role', 'owner'); // Ensure owner permissions for adding property
      formData.append('business_name', businessName.trim());
      formData.append('aadhar_no', cleanAadhar);
      formData.append('kyc_submit', '1');

      if (website.trim()) {
        let cleanWebsite = website.trim();
        if (!cleanWebsite.startsWith('http://') && !cleanWebsite.startsWith('https://')) {
          cleanWebsite = 'https://' + cleanWebsite;
        }
        formData.append('website', cleanWebsite);
      }
      if (profilePic) {
        formData.append('profile_image', profilePic);
      }
      if (aadharFile) {
        formData.append('aadhar_file', aadharFile);
      }

      const res = await updateProfile(formData);
      if (res.data?.success) {
        setStep('success');
        confetti({
          particleCount: 100,
          spread: 70,
          origin: { y: 0.6 },
        });
        await refreshUser();
        setTimeout(() => {
          onSuccess();
        }, 2200);
      } else {
        setErrorMsg(res.data?.message || 'Failed to update agent profile. Please verify details.');
      }
    } catch (err: any) {
      const msg =
        err?.response?.data?.message ||
        'An error occurred while submitting KYC. Please try again.';
      setErrorMsg(msg);
    } finally {
      setSubmitting(false);
    }
  };

  // STEP 1: Confirmation Screen
  if (step === 'confirm') {
    return (
      <div className="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in my-3">
        <div
          className="p-4 p-md-5 text-white position-relative"
          style={{
            background: 'linear-gradient(135deg, #071f3f 0%, #0b2c56 100%)',
          }}
        >
          <div className="d-flex align-items-center gap-2 mb-3">
            <span
              className="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5"
              style={{
                background: 'rgba(212, 175, 55, 0.2)',
                border: '1px solid rgba(212, 175, 55, 0.5)',
                color: '#ffd700',
                fontSize: '0.78rem',
              }}
            >
              <ShieldCheck size={14} />
              <span>AGENT VERIFICATION REQUIRED</span>
            </span>
          </div>

          <h2 className="h3 fw-bold text-white mb-2">
            Do you want to become an Agent to post properties?
          </h2>
          <p className="text-white-50 small mb-0" style={{ maxWidth: '650px', lineHeight: '1.6' }}>
            To list and manage property listings on Coimbatore Properties, users need to register as an Agent and complete basic KYC verification.
          </p>
        </div>

        <div className="p-4 p-md-5 bg-white">
          <div className="row g-3 mb-4">
            <div className="col-md-4">
              <div className="p-3 rounded-3 bg-light border h-100">
                <div className="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
                  <div className="p-2 rounded-circle bg-primary-subtle text-primary">
                    <Building2 size={18} />
                  </div>
                  <span>100% Free Listings</span>
                </div>
                <p className="text-muted small mb-0">
                  Post residential, commercial, and plot properties without brokerage fees.
                </p>
              </div>
            </div>

            <div className="col-md-4">
              <div className="p-3 rounded-3 bg-light border h-100">
                <div className="d-flex align-items-center gap-2 mb-2 text-warning fw-bold">
                  <div className="p-2 rounded-circle bg-warning-subtle text-dark">
                    <ShieldCheck size={18} />
                  </div>
                  <span>Verified Partner Status</span>
                </div>
                <p className="text-muted small mb-0">
                  Build credibility with verified agent badge and reach authentic buyers & tenants.
                </p>
              </div>
            </div>

            <div className="col-md-4">
              <div className="p-3 rounded-3 bg-light border h-100">
                <div className="d-flex align-items-center gap-2 mb-2 text-success fw-bold">
                  <div className="p-2 rounded-circle bg-success-subtle text-success">
                    <TrendingUp size={18} />
                  </div>
                  <span>Direct Client Leads</span>
                </div>
                <p className="text-muted small mb-0">
                  Receive instant WhatsApp inquiries and direct calls from interested clients.
                </p>
              </div>
            </div>
          </div>

          <div className="p-3.5 rounded-3 mb-4 d-flex align-items-center gap-3" style={{ background: '#f8f9fc', border: '1px dashed #cbd5e1' }}>
            <div className="p-2 rounded-circle bg-white shadow-sm text-primary flex-shrink-0">
              <FileText size={20} />
            </div>
            <div>
              <div className="fw-bold text-dark small">Quick 1-Minute KYC Requirement</div>
              <div className="text-muted small" style={{ fontSize: '0.8rem' }}>
                You will only need your Business / Agency Name, Operating City, and 12-digit Aadhaar Number with document proof.
              </div>
            </div>
          </div>

          <div className="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 pt-2">
            {onCancel && (
              <button
                type="button"
                className="btn btn-outline-secondary rounded-pill px-4 py-2.5 w-100 w-sm-auto text-decoration-none"
                onClick={onCancel}
              >
                Cancel & Return
              </button>
            )}
            <button
              type="button"
              className="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2 ms-auto w-100 w-sm-auto"
              style={{
                background: 'linear-gradient(135deg, #0b2c56 0%, #174276 100%)',
                border: 'none',
                minWidth: '240px',
              }}
              onClick={() => setStep('kyc_form')}
            >
              <span>Yes, Become an Agent & Fill KYC</span>
              <ArrowRight size={16} />
            </button>
          </div>
        </div>
      </div>
    );
  }

  // STEP 3: Success Screen
  if (step === 'success') {
    return (
      <div className="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in my-3 text-center p-5">
        <div className="py-4">
          <div
            className="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm"
            style={{
              width: '80px',
              height: '80px',
              background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
              color: '#ffffff',
            }}
          >
            <CheckCircle2 size={44} />
          </div>
          <h2 className="h4 fw-bold text-dark mb-2">Agent Profile & KYC Submitted!</h2>
          <p className="text-muted small mb-4" style={{ maxWidth: '500px', margin: '0 auto' }}>
            Congratulations! Your profile has been upgraded to an Agent account and KYC has been submitted. Loading the Post Property form now...
          </p>
          <div className="spinner-border text-primary spinner-border-sm" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  // STEP 2: KYC & Agent Profile Form
  return (
    <div className="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in my-3">
      {/* Form Header */}
      <div
        className="p-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-2"
        style={{
          background: 'linear-gradient(135deg, #071f3f 0%, #0b2c56 100%)',
        }}
      >
        <div>
          <button
            type="button"
            className="btn btn-link text-white-50 p-0 text-decoration-none small d-inline-flex align-items-center gap-1 mb-1"
            onClick={() => setStep('confirm')}
          >
            <ArrowLeft size={14} />
            <span>Back to Confirmation</span>
          </button>
          <h2 className="h4 fw-bold text-white mb-0 d-flex align-items-center gap-2">
            <ShieldCheck size={22} className="text-warning" />
            <span>Agent Profile & KYC Form</span>
          </h2>
        </div>
        <span
          className="badge rounded-pill px-3 py-1.5 fw-bold"
          style={{ background: 'rgba(212, 175, 55, 0.25)', color: '#ffd700', border: '1px solid rgba(212, 175, 55, 0.4)' }}
        >
          POST PROPERTY ELIGIBILITY
        </span>
      </div>

      <div className="p-4 p-md-5 bg-white">
        {errorMsg && (
          <div className="alert alert-danger d-flex align-items-center gap-2 small py-2.5 mb-4 rounded-3 border-danger-subtle">
            <AlertCircle size={18} className="flex-shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmitKyc}>
          {/* Personal Info Grid */}
          <div className="mb-4">
            <h6 className="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <User size={16} className="text-primary" />
              <span>1. Contact Information</span>
            </h6>
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label small fw-semibold text-muted">Full Name</label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><User size={14} /></span>
                  <input
                    type="text"
                    className="form-control"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="Your Name"
                    required
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label small fw-semibold text-muted">Mobile Phone</label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><Phone size={14} /></span>
                  <input
                    type="tel"
                    className="form-control"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="Phone Number"
                    required
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label small fw-semibold text-muted">Email Address</label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><Mail size={14} /></span>
                  <input
                    type="email"
                    className="form-control"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="name@example.com"
                    required
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Agency & KYC Grid */}
          <div className="mb-4">
            <h6 className="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <Building2 size={16} className="text-primary" />
              <span>2. Agency & KYC Verification</span>
            </h6>

            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  Agency / Business Name <span className="text-danger">*</span>
                </label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><Briefcase size={14} /></span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="e.g., Sri Krishna Properties & Consultancy"
                    value={businessName}
                    onChange={(e) => setBusinessName(e.target.value)}
                    required
                  />
                </div>
              </div>

              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  Operating City <span className="text-danger">*</span>
                </label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><MapPin size={14} /></span>
                  <select
                    className="form-select"
                    value={cityId}
                    onChange={(e) => setCityId(e.target.value)}
                    required
                  >
                    <option value="">Select City</option>
                    {cities.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name} {c.state ? `(${c.state})` : ''}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  Specialization
                </label>
                <select
                  className="form-select form-select-sm"
                  value={specialization}
                  onChange={(e) => setSpecialization(e.target.value)}
                >
                  <option value="Residential & Commercial">Residential & Commercial</option>
                  <option value="Residential Apartments & Villas">Residential Apartments & Villas</option>
                  <option value="Commercial & Retail Spaces">Commercial & Retail Spaces</option>
                  <option value="Plots, Land & Agricultural">Plots, Land & Agricultural</option>
                  <option value="Rental & Lease Management">Rental & Lease Management</option>
                </select>
              </div>

              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  Website / Portfolio (Optional)
                </label>
                <input
                  type="url"
                  className="form-control form-control-sm"
                  placeholder="https://myagency.com"
                  value={website}
                  onChange={(e) => setWebsite(e.target.value)}
                />
              </div>

              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  12-Digit Aadhaar Number <span className="text-danger">*</span>
                </label>
                <div className="input-group input-group-sm">
                  <span className="input-group-text bg-light"><Lock size={14} /></span>
                  <input
                    type="text"
                    className="form-control fw-medium"
                    placeholder="XXXX XXXX XXXX"
                    maxLength={14}
                    value={formatAadharDisplay(aadharNo)}
                    onChange={(e) => setAadharNo(e.target.value)}
                    required
                  />
                </div>
                <div className="form-text small" style={{ fontSize: '0.72rem' }}>
                  Used for partner identity verification. Kept strictly confidential.
                </div>
              </div>

              <div className="col-md-6">
                <label className="form-label small fw-semibold text-dark">
                  Aadhaar Document / ID Proof <span className="text-danger">*</span>
                </label>
                <div className="input-group input-group-sm">
                  <input
                    type="file"
                    className="form-control"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    onChange={handleAadharFileChange}
                    id="agentAadharProof"
                  />
                </div>
                {aadharFileName && (
                  <div className="text-success small mt-1 d-flex align-items-center gap-1" style={{ fontSize: '0.75rem' }}>
                    <Check size={13} />
                    <span>Selected: {aadharFileName}</span>
                  </div>
                )}
                {user?.aadhar_file && !aadharFileName && (
                  <div className="text-muted small mt-1" style={{ fontSize: '0.75rem' }}>
                    Existing document on file. Choose file to replace.
                  </div>
                )}
              </div>

              <div className="col-md-12">
                <label className="form-label small fw-semibold text-dark">
                  Agency Logo / Profile Photo (Optional)
                </label>
                <div className="d-flex align-items-center gap-3">
                  {profilePreview && (
                    <img
                      src={profilePreview}
                      alt="Preview"
                      className="rounded-circle border"
                      style={{ width: '48px', height: '48px', objectFit: 'cover' }}
                    />
                  )}
                  <input
                    type="file"
                    className="form-control form-control-sm"
                    accept="image/*"
                    onChange={handleProfilePicChange}
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Declaration Checkbox */}
          <div className="form-check p-3 rounded-3 bg-light border mb-4">
            <input
              className="form-check-input ms-0 me-2"
              type="checkbox"
              id="confirmAgentDeclaration"
              checked={acceptTerms}
              onChange={(e) => setAcceptTerms(e.target.checked)}
              required
            />
            <label className="form-check-label small text-dark" htmlFor="confirmAgentDeclaration">
              <strong>I confirm</strong> that the information and identity proof provided above are accurate and genuine. I agree to operate professionally in accordance with Coimbatore Properties agent guidelines.
            </label>
          </div>

          {/* Action Buttons */}
          <div className="d-flex align-items-center justify-content-between gap-3 pt-2 border-top">
            <button
              type="button"
              className="btn btn-outline-secondary rounded-pill px-4 py-2 small"
              onClick={() => setStep('confirm')}
              disabled={submitting}
            >
              Back
            </button>
            <button
              type="submit"
              className="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
              style={{
                background: 'linear-gradient(135deg, #0b2c56 0%, #174276 100%)',
                border: 'none',
              }}
              disabled={submitting}
            >
              {submitting ? (
                <>
                  <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                  <span>Submitting KYC...</span>
                </>
              ) : (
                <>
                  <span>Submit KYC & Continue to Post Property</span>
                  <ArrowRight size={16} />
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
