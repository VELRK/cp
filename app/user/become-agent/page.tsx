'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getCities, updateProfile } from '@/lib/frontendApi';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import confetti from 'canvas-confetti';
import {
  ArrowLeft,
  Briefcase,
  ShieldCheck,
  Building2,
  Award,
  CheckCircle2,
  AlertCircle,
  Upload,
  User,
  Phone,
  Mail,
  MapPin,
  FileText,
  Sparkles,
  ArrowRight,
  TrendingUp,
  PlusCircle,
  ExternalLink,
  Lock,
  Clock,
} from 'lucide-react';

interface City {
  id: number;
  name: string;
  state?: string;
}

export default function BecomeAgentPage() {
  const { user, loading: authLoading, refreshUser, setAuthModalOpen } = useAuth();
  const router = useRouter();

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
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [isSuccessUpgraded, setIsSuccessUpgraded] = useState(false);

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
      setErrorMsg('Document must be less than 10 MB.');
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

  const isAlreadyAgent =
    user && (user.role === 'agent' || user.user_type === 'agent');

  const isPendingKyc =
    isSuccessUpgraded ||
    user?.kyc_status === 'pending' ||
    (user && user.is_verified === 0 && !user.kyc_approved);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);

    if (!user) {
      setAuthModalOpen('login');
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
      setErrorMsg('Please confirm your declaration and agree to platform agent standards.');
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
      formData.append('role', 'owner'); // Ensure owner role permissions for adding properties
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
        setIsSuccessUpgraded(true);
        setSuccessMsg(
          res.data.message ||
            'Congratulations! Your profile has been successfully converted to an Agent. KYC submitted for approval.'
        );
        confetti({
          particleCount: 120,
          spread: 80,
          origin: { y: 0.5 },
        });
        await refreshUser();
      } else {
        setErrorMsg(res.data?.message || 'Failed to update profile to agent.');
      }
    } catch (err: any) {
      const msg =
        err?.response?.data?.message ||
        'An error occurred while submitting your agent application. Please verify details.';
      setErrorMsg(msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="nb-become-agent-wrapper py-5 mt-5">
      <div className="container py-3">
        {/* Navigation Breadcrumb */}
        <div className="d-flex align-items-center justify-content-between mb-4">
          <Link
            href="/"
            className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1.5 p-0 hover-translate"
          >
            <ArrowLeft size={16} />
            <span className="fw-medium">Back to Home</span>
          </Link>
          {user && (
            <Link
              href="/user/profile"
              className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-decoration-none"
            >
              My Profile
            </Link>
          )}
        </div>

        {/* Hero Banner with Classic Styling */}
        <div className="classic-agent-hero p-4 p-md-5 rounded-4 mb-5 text-white position-relative overflow-hidden shadow-lg">
          <div className="classic-gold-shimmer position-absolute pointer-events-none" />
          <div className="row align-items-center position-relative z-index-1">
            <div className="col-lg-8">
              <div className="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3 classic-pill-badge">
                <ShieldCheck size={16} className="text-warning" />
                <span className="classic-pill-text text-uppercase fw-bold">
                  Accredited Partner Program
                </span>
              </div>
              <h1 className="classic-hero-title fw-extrabold mb-3">
                Become a Verified Real Estate Agent
              </h1>
              <p className="classic-hero-subtitle text-white-50 mb-4" style={{ maxWidth: '680px' }}>
                Upgrade your customer profile into an accredited Agent account. Expand your real estate
                portfolio, receive high-intent buyer inquiries directly on WhatsApp, and post
                unlimited listings across Coimbatore with zero listing commissions.
              </p>
              <div className="d-flex flex-wrap gap-3">
                <a
                  href="#agent-convert-form"
                  className="btn classic-gold-btn px-4 py-2.5 rounded-pill fw-bold d-inline-flex align-items-center gap-2 shadow"
                >
                  <Briefcase size={18} />
                  <span>{isAlreadyAgent ? 'View Agent Profile' : 'Upgrade to Agent Now'}</span>
                </a>
                <a
                  href="#agent-privileges"
                  className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2"
                >
                  <span>Explore Agent Benefits</span>
                  <ArrowRight size={16} />
                </a>
              </div>
            </div>
            <div className="col-lg-4 d-none d-lg-block text-end">
              <div className="classic-seal-card p-4 rounded-4 d-inline-block text-start border shadow">
                <div className="d-flex align-items-center gap-3 mb-3">
                  <div className="classic-seal-icon rounded-circle d-flex align-items-center justify-content-center">
                    <Award size={32} className="text-warning" />
                  </div>
                  <div>
                    <div className="fw-bold text-white fs-6">Elite Brokerage</div>
                    <div className="small text-warning">Verified Network</div>
                  </div>
                </div>
                <div className="border-top border-secondary pt-3 small text-white-50">
                  <div className="d-flex justify-content-between py-1">
                    <span>Listing Fee</span>
                    <strong className="text-success">₹0 Free</strong>
                  </div>
                  <div className="d-flex justify-content-between py-1">
                    <span>Buyer Leads</span>
                    <strong className="text-warning">Instant WhatsApp</strong>
                  </div>
                  <div className="d-flex justify-content-between py-1">
                    <span>Visibility</span>
                    <strong className="text-white">Top Priority</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* 4 Classic Privilege Cards */}
        <section id="agent-privileges" className="mb-5">
          <div className="text-center mb-4">
            <h2 className="classic-section-heading fw-bold text-dark mb-2">
              Why Partner with Coimbatore Properties?
            </h2>
            <p className="text-muted small" style={{ maxWidth: '600px', margin: '0 auto' }}>
              Built specifically for property agents, consultants, and broker firms operating in
              the Coimbatore region.
            </p>
          </div>

          <div className="row g-3 g-md-4">
            <div className="col-md-6 col-lg-3">
              <div className="classic-benefit-card p-4 rounded-4 h-100 bg-white border shadow-sm hover-elevate">
                <div className="classic-benefit-icon-wrapper rounded-3 mb-3 d-flex align-items-center justify-content-center">
                  <ShieldCheck size={26} className="text-primary" />
                </div>
                <h3 className="h6 fw-bold text-dark mb-2">Verified Agent Badge</h3>
                <p className="text-muted small mb-0">
                  Inspire instant confidence with verified KYC credentials that rank higher on
                  search results.
                </p>
              </div>
            </div>

            <div className="col-md-6 col-lg-3">
              <div className="classic-benefit-card p-4 rounded-4 h-100 bg-white border shadow-sm hover-elevate">
                <div className="classic-benefit-icon-wrapper rounded-3 mb-3 d-flex align-items-center justify-content-center">
                  <Building2 size={26} className="text-primary" />
                </div>
                <h3 className="h6 fw-bold text-dark mb-2">Unlimited Listings</h3>
                <p className="text-muted small mb-0">
                  Post residential flats, plots, commercial shops, and independent houses with
                  comprehensive media tools.
                </p>
              </div>
            </div>

            <div className="col-md-6 col-lg-3">
              <div className="classic-benefit-card p-4 rounded-4 h-100 bg-white border shadow-sm hover-elevate">
                <div className="classic-benefit-icon-wrapper rounded-3 mb-3 d-flex align-items-center justify-content-center">
                  <TrendingUp size={26} className="text-primary" />
                </div>
                <h3 className="h6 fw-bold text-dark mb-2">Direct Buyer Leads</h3>
                <p className="text-muted small mb-0">
                  Direct client inquiries sent straight to your phone and email without middleman
                  filtering.
                </p>
              </div>
            </div>

            <div className="col-md-6 col-lg-3">
              <div className="classic-benefit-card p-4 rounded-4 h-100 bg-white border shadow-sm hover-elevate">
                <div className="classic-benefit-icon-wrapper rounded-3 mb-3 d-flex align-items-center justify-content-center">
                  <Award size={26} className="text-primary" />
                </div>
                <h3 className="h6 fw-bold text-dark mb-2">Zero Listing Fees</h3>
                <p className="text-muted small mb-0">
                  Keep 100% of your brokerage deal profits with transparent, zero hidden listing
                  charges.
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Main Agent Conversion / Overview Section */}
        <section id="agent-convert-form" className="row justify-content-center">
          <div className="col-lg-10">
            {/* If user is not logged in: Prompt with classic login/register card */}
            {!authLoading && !user && (
              <div className="card classic-form-card border-0 shadow rounded-4 p-4 p-md-5 bg-white text-center mb-5">
                <div className="mx-auto mb-3 classic-avatar-circle d-flex align-items-center justify-content-center">
                  <Lock size={32} className="text-primary" />
                </div>
                <h3 className="fw-bold text-dark mb-2">Sign In to Convert Your Profile</h3>
                <p className="text-muted small mb-4" style={{ maxWidth: '520px', margin: '0 auto' }}>
                  To upgrade your existing profile to an Accredited Agent, sign in to your Coimbatore
                  Properties account. If you don&apos;t have an account yet, you can register as an
                  agent in seconds.
                </p>
                <div className="d-flex flex-wrap justify-content-center gap-3">
                  <button
                    type="button"
                    className="btn classic-gold-btn rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 shadow"
                    onClick={() => setAuthModalOpen('login')}
                  >
                    <User size={16} />
                    <span>Sign In to Upgrade</span>
                  </button>
                  <button
                    type="button"
                    className="btn btn-outline-primary rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2"
                    onClick={() => setAuthModalOpen('register')}
                  >
                    <span>Create New Agent Account</span>
                  </button>
                </div>
              </div>
            )}

            {/* If user is ALREADY an agent: Display classic Agent Profile & Status Card */}
            {!authLoading && user && (isAlreadyAgent || isSuccessUpgraded) && (
              <div className="card classic-form-card border-0 shadow rounded-4 p-4 p-md-5 bg-white mb-5">
                {isPendingKyc && (
                  <div className="alert alert-warning border-warning-subtle rounded-3 p-3 mb-4 d-flex align-items-start gap-2.5">
                    <Clock size={20} className="text-warning flex-shrink-0 mt-0.5" />
                    <div>
                      <div className="fw-bold text-dark">KYC Verification In Progress</div>
                      <div className="small text-secondary">
                        Your agent application and KYC documents have been submitted to Coimbatore Properties compliance desk for verification. Property posting privileges will unlock automatically upon admin approval.
                      </div>
                    </div>
                  </div>
                )}
                <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-4 border-bottom mb-4">
                  <div className="d-flex align-items-center gap-3">
                    <div
                      className="rounded-circle overflow-hidden border bg-light flex-shrink-0 d-flex align-items-center justify-content-center"
                      style={{ width: 80, height: 80 }}
                    >
                      {profilePreview ? (
                        <img
                          src={profilePreview}
                          alt={user.name}
                          className="w-100 h-100 object-fit-cover"
                        />
                      ) : (
                        <Briefcase size={36} className="text-primary" />
                      )}
                    </div>
                    <div>
                      <div className="d-flex align-items-center gap-2">
                        <h2 className="h4 fw-bold text-dark m-0">{user.name}</h2>
                        <span className="badge classic-badge-verified d-inline-flex align-items-center gap-1">
                          <CheckCircle2 size={13} />
                          <span>AGENT</span>
                        </span>
                      </div>
                      <div className="text-muted small mt-1">
                        {user.business_name || 'Independent Real Estate Consultant'}
                      </div>
                      <div className="small text-muted d-flex align-items-center gap-2 mt-1">
                        <span>{user.phone}</span> • <span>{user.email}</span>
                      </div>
                    </div>
                  </div>

                  <div className="text-md-end">
                    {isPendingKyc ? (
                      <span className="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5">
                        <Clock size={14} className="text-warning" />
                        <span>KYC Status: PENDING REVIEW</span>
                      </span>
                    ) : (
                      <span className="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                        KYC Status: {user.kyc_status?.toUpperCase() || 'APPROVED'}
                      </span>
                    )}
                  </div>
                </div>

                <div className="row g-3 mb-4">
                  <div className="col-sm-6 col-md-3">
                    <div className="p-3 rounded-3 bg-light border text-center">
                      <div className="text-muted small mb-1">Role Type</div>
                      <div className="fw-bold text-primary">Accredited Agent</div>
                    </div>
                  </div>
                  <div className="col-sm-6 col-md-3">
                    <div className="p-3 rounded-3 bg-light border text-center">
                      <div className="text-muted small mb-1">Partner Status</div>
                      <div className="fw-bold text-success">Verified Partner</div>
                    </div>
                  </div>
                  <div className="col-sm-6 col-md-3">
                    <div className="p-3 rounded-3 bg-light border text-center">
                      <div className="text-muted small mb-1">Operating Region</div>
                      <div className="fw-bold text-primary">Coimbatore & Surroundings</div>
                    </div>
                  </div>
                  <div className="col-sm-6 col-md-3">
                    <div className="p-3 rounded-3 bg-light border text-center">
                      <div className="text-muted small mb-1">Listing Access</div>
                      <div className="fw-bold text-success">Unlimited Listings</div>
                    </div>
                  </div>
                </div>

                <div className="d-flex flex-wrap gap-3 pt-3 border-top">
                  {isPendingKyc ? (
                    <button
                      type="button"
                      className="btn btn-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 opacity-75"
                      disabled
                    >
                      <Clock size={16} />
                      <span>Posting Unlocks Upon KYC Approval</span>
                    </button>
                  ) : (
                    <Link
                      href="/owner/property/add"
                      className="btn classic-gold-btn rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                    >
                      <PlusCircle size={16} />
                      <span>Post New Property</span>
                    </Link>
                  )}
                  <Link
                    href="/owner/listings"
                    className="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                  >
                    <span>Manage My Listings</span>
                  </Link>
                  <Link
                    href="/owner/enquiries"
                    className="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                  >
                    <span>View Received Leads</span>
                  </Link>
                </div>
              </div>
            )}

            {/* If user is customer/tenant: Display Classic Conversion Form */}
            {!authLoading && user && !isAlreadyAgent && !isSuccessUpgraded && (
              <div className="card classic-form-card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white mb-5">
                <div className="classic-form-header mb-4 pb-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                  <div>
                    <div className="d-inline-flex align-items-center gap-1 text-warning fw-bold small text-uppercase mb-1">
                      <Sparkles size={14} />
                      <span>One-Time Professional Upgrade</span>
                    </div>
                    <h2 className="h4 fw-bold text-dark m-0">
                      Convert Customer Profile to Agent
                    </h2>
                  </div>
                  <div className="d-flex align-items-center gap-2">
                    <span className="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill small">
                      Current: {user.role?.toUpperCase()}
                    </span>
                    <ArrowRight size={16} className="text-muted" />
                    <span className="badge classic-badge-gold px-3 py-1.5 rounded-pill small fw-bold">
                      Target: AGENT
                    </span>
                  </div>
                </div>

                {errorMsg && (
                  <div className="alert alert-danger d-flex align-items-center gap-2 small py-2.5 mb-4 rounded-3 border-danger-subtle">
                    <AlertCircle size={18} className="flex-shrink-0" />
                    <span>{errorMsg}</span>
                  </div>
                )}

                {successMsg && (
                  <div className="alert alert-success d-flex align-items-center gap-2 small py-2.5 mb-4 rounded-3 border-success-subtle">
                    <CheckCircle2 size={18} className="flex-shrink-0" />
                    <span>{successMsg}</span>
                  </div>
                )}

                <form onSubmit={handleSubmit}>
                  {/* Step 1: Personal & Contact Information */}
                  <div className="mb-4">
                    <h5 className="classic-section-title fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                      <span className="classic-step-num">1</span>
                      <span>Personal & Contact Credentials</span>
                    </h5>
                    <div className="row g-3">
                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Full Name (as per ID)
                        </label>
                        <div className="input-group">
                          <span className="input-group-text bg-light text-muted border-end-0">
                            <User size={16} />
                          </span>
                          <input
                            type="text"
                            className="form-control border-start-0 ps-0"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            required
                            placeholder="Enter full name"
                          />
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Mobile Number
                        </label>
                        <div className="input-group">
                          <span className="input-group-text bg-light text-muted border-end-0">
                            <Phone size={16} />
                          </span>
                          <input
                            type="tel"
                            className="form-control border-start-0 ps-0"
                            value={phone}
                            onChange={(e) => setPhone(e.target.value)}
                            required
                            placeholder="10-digit mobile"
                          />
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Email Address
                        </label>
                        <div className="input-group">
                          <span className="input-group-text bg-light text-muted border-end-0">
                            <Mail size={16} />
                          </span>
                          <input
                            type="email"
                            className="form-control border-start-0 ps-0"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            placeholder="name@agency.com"
                          />
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Primary Operating City
                        </label>
                        <div className="input-group">
                          <span className="input-group-text bg-light text-muted border-end-0">
                            <MapPin size={16} />
                          </span>
                          <select
                            className="form-select border-start-0 ps-0"
                            value={cityId}
                            onChange={(e) => setCityId(e.target.value)}
                            required
                          >
                            <option value="">Select operating city</option>
                            {cities.map((c) => (
                              <option key={c.id} value={c.id}>
                                {c.name}
                                {c.state ? `, ${c.state}` : ''}
                              </option>
                            ))}
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Step 2: Agency & Real Estate Practice Details */}
                  <div className="mb-4 pt-3 border-top">
                    <h5 className="classic-section-title fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                      <span className="classic-step-num">2</span>
                      <span>Agency & Business Profile</span>
                    </h5>
                    <div className="row g-3">
                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Business / Agency / Consultancy Name *
                        </label>
                        <div className="input-group">
                          <span className="input-group-text bg-light text-muted border-end-0">
                            <Building2 size={16} />
                          </span>
                          <input
                            type="text"
                            className="form-control border-start-0 ps-0"
                            value={businessName}
                            onChange={(e) => setBusinessName(e.target.value)}
                            required
                            placeholder="e.g., Sri Krishna Real Estate Consultancy"
                          />
                        </div>
                        <div className="form-text small">
                          If operating independently, enter your full trade or individual consultant name.
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Specialization Areas
                        </label>
                        <select
                          className="form-select"
                          value={specialization}
                          onChange={(e) => setSpecialization(e.target.value)}
                        >
                          <option value="Residential & Commercial">
                            Residential & Commercial (All-in-one)
                          </option>
                          <option value="Apartments & Gated Communities">
                            Apartments & Gated Communities
                          </option>
                          <option value="Plots, Land & Farmland">
                            Plots, DTCP/RERA Land & Farmland
                          </option>
                          <option value="Commercial & Industrial Leasing">
                            Commercial, Warehouses & Industrial
                          </option>
                          <option value="Luxury Villas & Independent Houses">
                            Luxury Villas & Independent Houses
                          </option>
                        </select>
                      </div>

                      <div className="col-12">
                        <label className="form-label small fw-semibold text-secondary">
                          Website or RERA / Agency URL (optional)
                        </label>
                        <input
                          type="url"
                          className="form-control"
                          value={website}
                          onChange={(e) => setWebsite(e.target.value)}
                          placeholder="https://youragency.com"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Step 3: Identity & KYC Verification */}
                  <div className="mb-4 pt-3 border-top">
                    <h5 className="classic-section-title fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                      <span className="classic-step-num">3</span>
                      <span>Identity & KYC Verification</span>
                    </h5>
                    <div className="row g-3">
                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          12-Digit Aadhaar Number *
                        </label>
                        <input
                          type="text"
                          className="form-control font-monospace"
                          value={formatAadharDisplay(aadharNo)}
                          onChange={(e) =>
                            setAadharNo(e.target.value.replace(/\D/g, '').slice(0, 12))
                          }
                          placeholder="XXXX  XXXX  XXXX"
                          maxLength={14}
                          required
                        />
                        <div className="form-text small">
                          Aadhaar is encrypted and strictly used for verified agent accreditation.
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Aadhaar Document / ID Proof *
                        </label>
                        <input
                          type="file"
                          className="form-control"
                          accept="image/jpeg,image/png,image/webp,application/pdf"
                          onChange={handleAadharFileChange}
                          required={!user.aadhar_file}
                        />
                        <div className="form-text small">
                          {user.aadhar_file && !aadharFile
                            ? 'Document on file. Upload new file to replace.'
                            : 'PDF, JPG, PNG or WebP (Max 10 MB).'}
                        </div>
                      </div>

                      <div className="col-md-6">
                        <label className="form-label small fw-semibold text-secondary">
                          Agent Profile Photo (Executive Portrait)
                        </label>
                        <input
                          type="file"
                          className="form-control"
                          accept="image/jpeg,image/png,image/webp"
                          onChange={handleProfilePicChange}
                        />
                        <div className="form-text small">
                          A professional photo increases buyer response rate by up to 3x.
                        </div>
                      </div>

                      {profilePreview && (
                        <div className="col-md-6 d-flex align-items-center gap-3">
                          <div
                            className="rounded-circle overflow-hidden border shadow-sm flex-shrink-0"
                            style={{ width: 64, height: 64 }}
                          >
                            <img
                              src={profilePreview}
                              alt="Preview"
                              className="w-100 h-100 object-fit-cover"
                            />
                          </div>
                          <span className="small text-muted">
                            Photo preview (will appear on your listings)
                          </span>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Declaration & Checkbox */}
                  <div className="mb-4 pt-3 border-top">
                    <div className="form-check p-3 rounded-3 bg-light border">
                      <input
                        type="checkbox"
                        className="form-check-input ms-0 me-2"
                        id="agentDeclaration"
                        checked={acceptTerms}
                        onChange={(e) => setAcceptTerms(e.target.checked)}
                        required
                      />
                      <label
                        className="form-check-label small text-secondary"
                        htmlFor="agentDeclaration"
                      >
                        <strong className="text-dark">Professional Declaration:</strong> I confirm
                        that I am a practicing real estate consultant / agent. I agree to provide
                        authentic, verified property details, respect client confidentiality, and
                        comply with Coimbatore Properties&apos; professional agent standards.
                      </label>
                    </div>
                  </div>

                  {/* Submit Button */}
                  <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                    <div className="d-flex align-items-center gap-2 text-muted small">
                      <ShieldCheck size={16} className="text-success" />
                      <span>256-Bit SSL Encrypted & Direct Admin Verification</span>
                    </div>

                    <button
                      type="submit"
                      className="btn classic-gold-btn rounded-pill px-5 py-2.5 fw-bold shadow hover-translate d-inline-flex align-items-center gap-2"
                      disabled={submitting}
                    >
                      <Briefcase size={18} />
                      <span>
                        {submitting
                          ? 'Submitting Application...'
                          : 'Submit Agent Application & Upgrade Profile'}
                      </span>
                    </button>
                  </div>
                </form>
              </div>
            )}
          </div>
        </section>
      </div>

      {/* Classic Look Component Styles */}
      <style
        dangerouslySetInnerHTML={{
          __html: `
        .nb-become-agent-wrapper {
          background-color: #f8fafc;
          min-height: 85vh;
        }
        .classic-agent-hero {
          background: linear-gradient(135deg, #071f3f 0%, #0b2c56 50%, #103b70 100%);
          border: 1px solid rgba(212, 175, 55, 0.25);
        }
        .classic-gold-shimmer {
          top: -50%;
          right: -20%;
          width: 500px;
          height: 500px;
          background: radial-gradient(circle, rgba(242, 178, 3, 0.15) 0%, transparent 70%);
          border-radius: 50%;
        }
        .classic-pill-badge {
          background: rgba(242, 178, 3, 0.12);
          border: 1px solid rgba(242, 178, 3, 0.35);
        }
        .classic-pill-text {
          font-size: 0.75rem;
          letter-spacing: 0.08em;
          color: #f2b203;
        }
        .classic-hero-title {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          font-size: 2.25rem;
          line-height: 1.25;
          letter-spacing: -0.02em;
        }
        .classic-gold-btn {
          background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
          color: #0b1a30 !important;
          border: 1px solid #d4af37 !important;
          font-weight: 700 !important;
          transition: all 0.25s ease;
        }
        .classic-gold-btn:hover {
          background: linear-gradient(135deg, #e5be48 0%, #c9961a 100%) !important;
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba(212, 175, 55, 0.35) !important;
        }
        .classic-seal-card {
          background: rgba(7, 31, 63, 0.85);
          backdrop-filter: blur(10px);
          border-color: rgba(212, 175, 55, 0.3) !important;
          width: 280px;
        }
        .classic-seal-icon {
          width: 48px;
          height: 48px;
          background: rgba(242, 178, 3, 0.15);
          border: 1px solid rgba(242, 178, 3, 0.3);
        }
        .classic-benefit-card {
          border-color: rgba(11, 44, 86, 0.1) !important;
          transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .classic-benefit-card:hover {
          transform: translateY(-4px);
          box-shadow: 0 12px 24px rgba(11, 44, 86, 0.08) !important;
          border-color: rgba(212, 175, 55, 0.4) !important;
        }
        .classic-benefit-icon-wrapper {
          width: 48px;
          height: 48px;
          background: #eef3fb;
          border: 1px solid rgba(11, 44, 86, 0.12);
        }
        .classic-form-card {
          border: 1px solid rgba(11, 44, 86, 0.12) !important;
        }
        .classic-step-num {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 26px;
          height: 26px;
          border-radius: 50%;
          background: #0b2c56;
          color: #fff;
          font-size: 0.8rem;
          font-weight: bold;
        }
        .classic-badge-verified {
          background-color: #0b2c56;
          color: #f2b203;
          border: 1px solid rgba(242, 178, 3, 0.4);
          font-size: 0.75rem;
          padding: 0.35rem 0.65rem;
          border-radius: 50rem;
        }
        .classic-badge-gold {
          background-color: #f2b203;
          color: #0b2c56;
          border: 1px solid #d4af37;
        }
        .classic-avatar-circle {
          width: 64px;
          height: 64px;
          border-radius: 50%;
          background: #eef3fb;
          border: 1px solid rgba(11, 44, 86, 0.15);
        }
        .hover-translate {
          transition: transform 0.2s ease;
        }
        .hover-translate:hover {
          transform: translateY(-2px);
        }
      `,
        }}
      />
    </div>
  );
}
