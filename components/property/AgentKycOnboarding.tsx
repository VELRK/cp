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
  FileCheck2,
  ChevronRight,
  Shield,
  HelpCircle,
} from 'lucide-react';

interface City {
  id: number;
  name: string;
  state?: string;
}

interface AgentKycOnboardingProps {
  onSuccess: () => void;
  onCancel?: () => void;
  initialStep?: 'kyc_form';
}

export default function AgentKycOnboarding({
  onSuccess,
  onCancel,
  initialStep = 'kyc_form',
}: AgentKycOnboardingProps) {
  const { user, refreshUser } = useAuth();

  // Step 1: 'kyc_form' (KYC Form) | Step 2: 'success'
  const [step, setStep] = useState<'kyc_form' | 'success'>(initialStep);

  // Sub-steps for kyc_form: 1 = Agency Info, 2 = Aadhaar & Docs, 3 = Review & Declaration
  const [formSubStep, setFormSubStep] = useState<1 | 2 | 3>(1);

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

  const validateSubStep1 = (): boolean => {
    setErrorMsg(null);
    if (!name.trim()) {
      setErrorMsg('Please enter your full name.');
      return false;
    }
    if (!phone.trim()) {
      setErrorMsg('Please enter your contact phone number.');
      return false;
    }
    if (!businessName.trim() || businessName.trim().length < 2) {
      setErrorMsg('Please enter your Business or Agency name (at least 2 characters).');
      return false;
    }
    if (!cityId) {
      setErrorMsg('Please select your primary operating city.');
      return false;
    }
    return true;
  };

  const validateSubStep2 = (): boolean => {
    setErrorMsg(null);
    const cleanAadhar = aadharNo.replace(/\D/g, '');
    if (cleanAadhar.length !== 12) {
      setErrorMsg('Please enter a valid 12-digit Aadhaar number.');
      return false;
    }
    if (!user?.aadhar_file && !aadharFile) {
      setErrorMsg('Please upload your Aadhaar document or government ID proof for agent verification.');
      return false;
    }
    return true;
  };

  const handleSubmitKyc = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    setErrorMsg(null);

    if (!user) {
      setErrorMsg('Please sign in before submitting KYC.');
      return;
    }

    if (!validateSubStep1()) {
      setFormSubStep(1);
      return;
    }

    if (!validateSubStep2()) {
      setFormSubStep(2);
      return;
    }

    if (!acceptTerms) {
      setErrorMsg('Please confirm the compliance declaration to proceed.');
      return;
    }

    const cleanAadhar = aadharNo.replace(/\D/g, '');

    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('userId', String(user.id));
      formData.append('name', name.trim());
      formData.append('email', email.trim());
      formData.append('phone', phone.trim());
      formData.append('city_id', cityId);
      formData.append('user_type', 'agent');
      formData.append('role', 'owner'); // Ensure owner permissions for adding properties
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
          particleCount: 120,
          spread: 80,
          origin: { y: 0.55 },
        });
        await refreshUser();
        setTimeout(() => {
          onSuccess();
        }, 2000);
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

  const selectedCityName =
    cities.find((c) => String(c.id) === String(cityId))?.name || 'Selected City';

  const maskedAadhaar = aadharNo
    ? `XXXX XXXX ${aadharNo.replace(/\D/g, '').slice(-4) || 'XXXX'}`
    : 'XXXX XXXX XXXX';



  // ==========================================
  // STAGE 3: SUCCESS & UNLOCK CELEBRATION
  // ==========================================
  if (step === 'success') {
    return (
      <div className="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in my-3 text-center p-5">
        <div className="py-4">
          <div
            className="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-lg"
            style={{
              width: '86px',
              height: '86px',
              background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
              color: '#ffffff',
            }}
          >
            <CheckCircle2 size={48} />
          </div>
          <h2 className="h3 fw-bold text-dark mb-2">Agent Conversion & KYC Submitted!</h2>
          <p className="text-muted small mb-4" style={{ maxWidth: '540px', margin: '0 auto', lineHeight: '1.6' }}>
            Congratulations! Your account has been converted to an{' '}
            <strong className="text-primary">Agent Account</strong> and your KYC documents have been submitted for verification.
            Redirecting to verification status...
          </p>
          <div className="spinner-border text-primary spinner-border-sm" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  // ==========================================
  // STAGE 2: KYC VERIFICATION STEPS (GUIDED WIZARD)
  // ==========================================
  return (
    <div className="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in my-3">
      {/* Header */}
      <div
        className="p-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-2"
        style={{
          background: 'linear-gradient(135deg, #071f3f 0%, #0b2c56 100%)',
        }}
      >
        <div>
          {formSubStep > 1 ? (
            <button
              type="button"
              className="btn btn-link text-white-50 p-0 text-decoration-none small d-inline-flex align-items-center gap-1 mb-1 hover-white"
              onClick={() => setFormSubStep((s) => (s - 1) as any)}
            >
              <ArrowLeft size={14} />
              <span>Back to Previous Step</span>
            </button>
          ) : onCancel ? (
            <button
              type="button"
              className="btn btn-link text-white-50 p-0 text-decoration-none small d-inline-flex align-items-center gap-1 mb-1 hover-white"
              onClick={onCancel}
            >
              <ArrowLeft size={14} />
              <span>Cancel & Return</span>
            </button>
          ) : null}
          <h2 className="h4 fw-bold text-white mb-0 d-flex align-items-center gap-2">
            <ShieldCheck size={22} className="text-warning" />
            <span>Agent KYC Verification</span>
          </h2>
          <div className="text-white-50 small mt-0.5" style={{ fontSize: '0.8rem' }}>
            Complete your quick KYC details to convert your account to Agent and post properties.
          </div>
        </div>

        <div className="d-flex align-items-center gap-2">
          <span
            className="badge rounded-pill px-3 py-1.5 fw-bold"
            style={{
              background: 'rgba(212, 175, 55, 0.25)',
              color: '#ffd700',
              border: '1px solid rgba(212, 175, 55, 0.4)',
            }}
          >
            STEP {formSubStep} OF 3
          </span>
        </div>
      </div>

      {/* Progress Timeline Stepper */}
      <div className="bg-light px-4 py-3 border-bottom">
        <div className="row g-2 text-center align-items-center">
          {/* Step 1 indicator */}
          <div className="col-4">
            <div
              className="d-flex align-items-center justify-content-center gap-2 p-2 rounded-3"
              style={{
                background: formSubStep === 1 ? '#0b2c56' : '#ecfdf5',
                color: formSubStep === 1 ? '#ffffff' : '#059669',
                fontWeight: 600,
                fontSize: '0.82rem',
                border: formSubStep === 1 ? 'none' : '1px solid #a7f3d0',
                transition: 'all 0.25s ease',
              }}
            >
              {formSubStep > 1 ? <CheckCircle2 size={16} /> : <Building2 size={16} />}
              <span className="d-none d-sm-inline">1. Agency Details</span>
              <span className="d-sm-none">1. Agency</span>
            </div>
          </div>

          {/* Step 2 indicator */}
          <div className="col-4">
            <div
              className="d-flex align-items-center justify-content-center gap-2 p-2 rounded-3"
              style={{
                background:
                  formSubStep === 2 ? '#0b2c56' : formSubStep > 2 ? '#ecfdf5' : '#ffffff',
                color:
                  formSubStep === 2 ? '#ffffff' : formSubStep > 2 ? '#059669' : '#64748b',
                fontWeight: 600,
                fontSize: '0.82rem',
                border: formSubStep === 2 ? 'none' : '1px solid #e2e8f0',
                transition: 'all 0.25s ease',
              }}
            >
              {formSubStep > 2 ? <CheckCircle2 size={16} /> : <Lock size={16} />}
              <span className="d-none d-sm-inline">2. Aadhaar & Docs</span>
              <span className="d-sm-none">2. KYC</span>
            </div>
          </div>

          {/* Step 3 indicator */}
          <div className="col-4">
            <div
              className="d-flex align-items-center justify-content-center gap-2 p-2 rounded-3"
              style={{
                background: formSubStep === 3 ? '#0b2c56' : '#ffffff',
                color: formSubStep === 3 ? '#ffffff' : '#64748b',
                fontWeight: 600,
                fontSize: '0.82rem',
                border: formSubStep === 3 ? 'none' : '1px solid #e2e8f0',
                transition: 'all 0.25s ease',
              }}
            >
              <FileCheck2 size={16} />
              <span className="d-none d-sm-inline">3. Declaration & Unlock</span>
              <span className="d-sm-none">3. Unlock</span>
            </div>
          </div>
        </div>
      </div>

      {/* Form Content */}
      <div className="p-4 p-md-5 bg-white">
        {errorMsg && (
          <div className="alert alert-danger d-flex align-items-center gap-2 small py-2.5 mb-4 rounded-3 border-danger-subtle">
            <AlertCircle size={18} className="flex-shrink-0 text-danger" />
            <span>{errorMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmitKyc}>
          {/* SUB-STEP 1: AGENCY & CONTACT INFO */}
          {formSubStep === 1 && (
            <div className="animate-fade-in">
              <div className="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <Building2 size={18} className="text-primary" />
                  <span>Step 1: Agency & Operating Information</span>
                </h5>
                <span className="text-muted small">Fields marked with * are required</span>
              </div>

              {/* Personal Contact row */}
              <div className="row g-3 mb-4">
                <div className="col-md-4">
                  <label className="form-label small fw-semibold text-muted">Full Name *</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light">
                      <User size={14} />
                    </span>
                    <input
                      type="text"
                      className="form-control"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="Your Full Name"
                      required
                    />
                  </div>
                </div>

                <div className="col-md-4">
                  <label className="form-label small fw-semibold text-muted">Mobile Phone *</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light">
                      <Phone size={14} />
                    </span>
                    <input
                      type="tel"
                      className="form-control"
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="10-digit Phone"
                      required
                    />
                  </div>
                </div>

                <div className="col-md-4">
                  <label className="form-label small fw-semibold text-muted">Email Address</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light">
                      <Mail size={14} />
                    </span>
                    <input
                      type="email"
                      className="form-control"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="name@example.com"
                    />
                  </div>
                </div>
              </div>

              {/* Agency Credentials */}
              <div className="row g-3 mb-4">
                <div className="col-md-6">
                  <label className="form-label small fw-semibold text-dark">
                    Agency / Business Name <span className="text-danger">*</span>
                  </label>
                  <div className="input-group">
                    <span className="input-group-text bg-light">
                      <Briefcase size={15} />
                    </span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g., Sri Krishna Real Estate & Consultancy"
                      value={businessName}
                      onChange={(e) => setBusinessName(e.target.value)}
                      required
                    />
                  </div>
                  <div className="form-text small" style={{ fontSize: '0.74rem' }}>
                    This name will be displayed as the authorized agent on all your property listings.
                  </div>
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold text-dark">
                    Primary Operating City <span className="text-danger">*</span>
                  </label>
                  <div className="input-group">
                    <span className="input-group-text bg-light">
                      <MapPin size={15} />
                    </span>
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
                    Property Specialization
                  </label>
                  <select
                    className="form-select"
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
                    Agency Website / Portfolio (Optional)
                  </label>
                  <input
                    type="url"
                    className="form-control"
                    placeholder="https://myagency.com"
                    value={website}
                    onChange={(e) => setWebsite(e.target.value)}
                  />
                </div>
              </div>

              {/* Sub-step 1 Footer */}
              <div className="d-flex align-items-center justify-content-between pt-3 border-top">
                {onCancel ? (
                  <button
                    type="button"
                    className="btn btn-outline-secondary rounded-pill px-4 py-2"
                    onClick={onCancel}
                  >
                    Cancel
                  </button>
                ) : (
                  <div />
                )}
                <button
                  type="button"
                  className="btn btn-primary rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                  style={{ background: 'linear-gradient(135deg, #0b2c56 0%, #153e75 100%)', border: 'none' }}
                  onClick={() => {
                    if (validateSubStep1()) {
                      setFormSubStep(2);
                    }
                  }}
                >
                  <span>Continue to Step 2: Aadhaar & KYC Docs</span>
                  <ArrowRight size={16} />
                </button>
              </div>
            </div>
          )}

          {/* SUB-STEP 2: AADHAAR & DOCUMENT PROOF */}
          {formSubStep === 2 && (
            <div className="animate-fade-in">
              <div className="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <Lock size={18} className="text-primary" />
                  <span>Step 2: Aadhaar Identity & Document Proof</span>
                </h5>
                <span className="text-muted small">Encrypted & Confidential</span>
              </div>

              <div className="row g-4 mb-4">
                <div className="col-md-6">
                  <label className="form-label small fw-semibold text-dark">
                    12-Digit Aadhaar Number <span className="text-danger">*</span>
                  </label>
                  <div className="input-group">
                    <span className="input-group-text bg-light">
                      <Lock size={15} />
                    </span>
                    <input
                      type="text"
                      className="form-control fw-bold font-monospace"
                      placeholder="XXXX XXXX XXXX"
                      maxLength={14}
                      value={formatAadharDisplay(aadharNo)}
                      onChange={(e) => setAadharNo(e.target.value)}
                      required
                      style={{ letterSpacing: '1px', fontSize: '1rem' }}
                    />
                  </div>
                  <div className="form-text small" style={{ fontSize: '0.74rem' }}>
                    Enter the 12-digit number from your Aadhaar Card. Used exclusively for agent identity verification.
                  </div>
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold text-dark">
                    Aadhaar Document / ID Proof File <span className="text-danger">*</span>
                  </label>
                  <input
                    type="file"
                    className="form-control"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    onChange={handleAadharFileChange}
                    id="agentAadharProof"
                  />
                  {aadharFileName && (
                    <div className="text-success small mt-1.5 d-flex align-items-center gap-1.5 fw-semibold" style={{ fontSize: '0.78rem' }}>
                      <Check size={14} />
                      <span>Document attached: {aadharFileName}</span>
                    </div>
                  )}
                  {user?.aadhar_file && !aadharFileName && (
                    <div className="text-muted small mt-1.5" style={{ fontSize: '0.75rem' }}>
                      Existing document on file. Choose file only if you want to replace it.
                    </div>
                  )}
                  <div className="form-text small" style={{ fontSize: '0.72rem' }}>
                    Accepted formats: PDF, JPG, PNG, WEBP (Max 10 MB).
                  </div>
                </div>

                <div className="col-md-12">
                  <label className="form-label small fw-semibold text-dark">
                    Agency Logo or Profile Photo (Optional)
                  </label>
                  <div className="d-flex align-items-center gap-3">
                    {profilePreview && (
                      <img
                        src={profilePreview}
                        alt="Logo preview"
                        className="rounded-circle border shadow-sm"
                        style={{ width: '52px', height: '52px', objectFit: 'cover' }}
                      />
                    )}
                    <input
                      type="file"
                      className="form-control"
                      accept="image/*"
                      onChange={handleProfilePicChange}
                    />
                  </div>
                  <div className="form-text small" style={{ fontSize: '0.74rem' }}>
                    Showcases your business brand or personal consultant picture on listings.
                  </div>
                </div>
              </div>

              {/* Sub-step 2 Footer */}
              <div className="d-flex align-items-center justify-content-between pt-3 border-top">
                <button
                  type="button"
                  className="btn btn-outline-secondary rounded-pill px-4 py-2"
                  onClick={() => setFormSubStep(1)}
                >
                  Back: Agency Details
                </button>
                <button
                  type="button"
                  className="btn btn-primary rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                  style={{ background: 'linear-gradient(135deg, #0b2c56 0%, #153e75 100%)', border: 'none' }}
                  onClick={() => {
                    if (validateSubStep2()) {
                      setFormSubStep(3);
                    }
                  }}
                >
                  <span>Review & Complete Verification</span>
                  <ArrowRight size={16} />
                </button>
              </div>
            </div>
          )}

          {/* SUB-STEP 3: REVIEW, DECLARATION & SUBMISSION */}
          {formSubStep === 3 && (
            <div className="animate-fade-in">
              <div className="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <FileCheck2 size={18} className="text-primary" />
                  <span>Step 3: Review Details & Compliance Declaration</span>
                </h5>
                <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                  Final Step
                </span>
              </div>

              {/* Review summary cards */}
              <div className="p-3.5 rounded-3 bg-light border mb-4">
                <div className="small fw-bold text-uppercase text-muted mb-2.5" style={{ letterSpacing: '0.5px' }}>
                  Verification Application Summary
                </div>
                <div className="row g-3">
                  <div className="col-sm-6">
                    <div className="text-muted small">Agency / Business Name</div>
                    <div className="fw-bold text-dark" style={{ fontSize: '0.95rem' }}>{businessName}</div>
                  </div>
                  <div className="col-sm-6">
                    <div className="text-muted small">Operating City</div>
                    <div className="fw-bold text-dark" style={{ fontSize: '0.95rem' }}>{selectedCityName}</div>
                  </div>
                  <div className="col-sm-6">
                    <div className="text-muted small">Contact Person & Phone</div>
                    <div className="fw-bold text-dark" style={{ fontSize: '0.95rem' }}>{name} ({phone})</div>
                  </div>
                  <div className="col-sm-6">
                    <div className="text-muted small">Aadhaar Card on File</div>
                    <div className="fw-bold text-dark font-monospace" style={{ fontSize: '0.95rem' }}>{maskedAadhaar}</div>
                  </div>
                </div>
              </div>

              {/* Declaration Checkbox */}
              <div
                className="form-check p-3 rounded-3 border mb-4"
                style={{ background: '#f8fafc', borderColor: '#cbd5e1' }}
              >
                <input
                  className="form-check-input ms-0 me-2.5"
                  type="checkbox"
                  id="confirmAgentDeclarationFinal"
                  checked={acceptTerms}
                  onChange={(e) => setAcceptTerms(e.target.checked)}
                  required
                  style={{ width: '18px', height: '18px' }}
                />
                <label className="form-check-label small text-dark" htmlFor="confirmAgentDeclarationFinal" style={{ lineHeight: '1.5' }}>
                  <strong>I solemnly declare</strong> that the information, business details, and identity documents provided above are genuine and accurate.
                  I agree to convert my account to an accredited Agent partner and abide by Coimbatore Properties agent guidelines.
                </label>
              </div>

              {/* Sub-step 3 Footer */}
              <div className="d-flex align-items-center justify-content-between pt-3 border-top">
                <button
                  type="button"
                  className="btn btn-outline-secondary rounded-pill px-4 py-2"
                  onClick={() => setFormSubStep(2)}
                  disabled={submitting}
                >
                  Back: Documents
                </button>

                <button
                  type="button"
                  onClick={() => handleSubmitKyc()}
                  className="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 text-white"
                  style={{
                    background: 'linear-gradient(135deg, #0b2c56 0%, #153e75 100%)',
                    border: 'none',
                    minWidth: '260px',
                  }}
                  disabled={submitting || !acceptTerms}
                >
                  {submitting ? (
                    <>
                      <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                      <span>Verifying & Converting Account...</span>
                    </>
                  ) : (
                    <>
                      <span>Submit KYC & Convert to Agent</span>
                      <ArrowRight size={16} />
                    </>
                  )}
                </button>
              </div>
            </div>
          )}
        </form>
      </div>
    </div>
  );
}
