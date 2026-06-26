'use client';

import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getCities } from '@/lib/frontendApi';
import { getDashboardPathForRole } from '@/lib/dashboardPaths';
import { X, Lock, Mail, User, Phone, CheckCircle, ShieldAlert, ArrowLeft, MessageCircle } from 'lucide-react';

interface City {
  id: number;
  name: string;
  state: string;
}

type LoginStep = 'phone' | 'otp';

const RESEND_SECONDS = 60;

function normalizePhoneInput(value: string): string {
  return value.replace(/\D/g, '').slice(0, 10);
}

const AuthModals: React.FC = () => {
  const router = useRouter();
  const { isAuthModalOpen, setAuthModalOpen, sendOtp, verifyOtp, resendOtp, registerUser } = useAuth();
  const [cities, setCities] = useState<City[]>([]);

  // Login OTP flow
  const [loginStep, setLoginStep] = useState<LoginStep>('phone');
  const [loginPhone, setLoginPhone] = useState('');
  const [loginOtp, setLoginOtp] = useState('');
  const [resendTimer, setResendTimer] = useState(0);
  const otpInputRef = useRef<HTMLInputElement>(null);

  // Register form states
  const [regName, setRegName] = useState('');
  const [regEmail, setRegEmail] = useState('');
  const [regPhone, setRegPhone] = useState('');
  const [regPassword, setRegPassword] = useState('');
  const [regPasswordConfirm, setRegPasswordConfirm] = useState('');
  const [regRole, setRegRole] = useState<'owner' | 'customer' | 'agent'>('customer');
  const [regCityId, setRegCityId] = useState('');
  const [regAadharNo, setRegAadharNo] = useState('');
  const [regAadharFile, setRegAadharFile] = useState<File | null>(null);
  const [regProfilePic, setRegProfilePic] = useState<File | null>(null);
  const [regAcceptTerms, setRegAcceptTerms] = useState(false);

  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const resetLoginFlow = useCallback(() => {
    setLoginStep('phone');
    setLoginPhone('');
    setLoginOtp('');
    setResendTimer(0);
  }, []);

  useEffect(() => {
    if (isAuthModalOpen) {
      setErrorMsg(null);
      setSuccessMsg(null);
      if (isAuthModalOpen === 'login') {
        resetLoginFlow();
      }
      getCities()
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.cities)) {
            setCities(res.data.cities);
          }
        })
        .catch((e) => console.error('Error fetching cities', e));
    }
  }, [isAuthModalOpen, resetLoginFlow]);

  useEffect(() => {
    if (resendTimer <= 0) return;
    const t = setTimeout(() => setResendTimer((s) => s - 1), 1000);
    return () => clearTimeout(t);
  }, [resendTimer]);

  useEffect(() => {
    if (loginStep === 'otp') {
      otpInputRef.current?.focus();
    }
  }, [loginStep]);

  if (!isAuthModalOpen) return null;

  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);

    const phone = normalizePhoneInput(loginPhone);
    if (phone.length !== 10) {
      setErrorMsg('Enter a valid 10-digit mobile number.');
      return;
    }

    setLoading(true);
    try {
      const result = await sendOtp(phone);
      if (!result.success) {
        setErrorMsg(result.message || 'Could not send OTP.');
        return;
      }
      setLoginPhone(phone);
      setLoginStep('otp');
      setLoginOtp('');
      setResendTimer(RESEND_SECONDS);
      setSuccessMsg(
        result.development_mode && result.otp
          ? `OTP sent (dev mode): ${result.otp}`
          : 'OTP sent to your WhatsApp number.'
      );
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Failed to send OTP. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);

    const otp = loginOtp.replace(/\D/g, '').slice(0, 4);
    if (otp.length !== 4) {
      setErrorMsg('Enter the 4-digit OTP.');
      return;
    }

    setLoading(true);
    try {
      const result = await verifyOtp(loginPhone, otp);
      if (!result.success) {
        setErrorMsg(result.message || 'Invalid OTP.');
        return;
      }
      router.push(getDashboardPathForRole(result.user?.role));
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Verification failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleResendOtp = async () => {
    if (resendTimer > 0 || loading) return;
    setErrorMsg(null);
    setSuccessMsg(null);
    setLoading(true);
    try {
      const result = await resendOtp(loginPhone);
      if (!result.success) {
        setErrorMsg(result.message || 'Could not resend OTP.');
        return;
      }
      setResendTimer(RESEND_SECONDS);
      setSuccessMsg(
        result.development_mode && result.otp
          ? `OTP resent (dev mode): ${result.otp}`
          : 'A new OTP has been sent to your WhatsApp.'
      );
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Failed to resend OTP.');
    } finally {
      setLoading(false);
    }
  };

  const handleRegisterSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);

    if (regPassword !== regPasswordConfirm) {
      setErrorMsg('Passwords do not match');
      return;
    }
    if (!regAcceptTerms) {
      setErrorMsg('You must accept the terms and conditions');
      return;
    }

    setLoading(true);
    try {
      const formData = new FormData();
      formData.append('name', regName);
      formData.append('email', regEmail);
      formData.append('phone', regPhone);
      formData.append('password', regPassword);
      formData.append('password_confirm', regPasswordConfirm);
      formData.append('role', regRole);
      formData.append('accept_terms', regAcceptTerms ? '1' : '0');
      if (regCityId) formData.append('city_id', regCityId);
      if (regAadharNo) formData.append('aadhar_no', regAadharNo);
      if (regAadharFile) formData.append('aadhar_file', regAadharFile);
      if (regProfilePic) formData.append('profile_image', regProfilePic);

      const result = await registerUser(formData);
      if (result.success) {
        setSuccessMsg('Registration successful! Waiting for admin approval.');
        setRegName('');
        setRegEmail('');
        setRegPhone('');
        setRegPassword('');
        setRegPasswordConfirm('');
        setRegCityId('');
        setRegAadharNo('');
        setRegAadharFile(null);
        setRegProfilePic(null);
        setRegAcceptTerms(false);
        setTimeout(() => {
          setAuthModalOpen(null);
        }, 3000);
      } else {
        setErrorMsg(result.message || 'Registration failed');
      }
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Registration error. Please check fields.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      className="modal fade show d-block"
      style={{ background: 'rgba(7, 31, 63, 0.4)', backdropFilter: 'blur(8px)', zIndex: 1060 }}
      tabIndex={-1}
      role="dialog"
    >
      <div className="modal-dialog modal-dialog-centered" role="document">
        <div className="modal-content border-0 shadow-lg rounded-4 animate-fade-in">
          <div className="modal-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center p-3">
            <div>
              <h2 className="modal-title h5 fw-bold text-primary mb-0" style={{ color: 'var(--nb-primary)' }}>
                {isAuthModalOpen === 'login'
                  ? loginStep === 'phone'
                    ? 'Sign in with Phone'
                    : 'Verify OTP'
                  : 'Create an Account'}
              </h2>
              {isAuthModalOpen === 'login' && (
                <p className="text-muted small mb-0 mt-1">
                  {loginStep === 'phone'
                    ? 'We will send a 4-digit OTP to your WhatsApp'
                    : `OTP sent to +91 ${loginPhone}`}
                </p>
              )}
            </div>
            <button
              type="button"
              className="btn border-0 p-1 rounded-circle"
              aria-label="Close"
              onClick={() => setAuthModalOpen(null)}
            >
              <X size={20} />
            </button>
          </div>

          <div className="modal-body p-4">
            {errorMsg && (
              <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-3">
                <ShieldAlert size={16} />
                <span>{errorMsg}</span>
              </div>
            )}
            {successMsg && (
              <div className="alert alert-success d-flex align-items-center gap-2 small py-2 mb-3">
                <CheckCircle size={16} />
                <span>{successMsg}</span>
              </div>
            )}

            {isAuthModalOpen === 'login' ? (
              loginStep === 'phone' ? (
                <form onSubmit={handleSendOtp}>
                  <div className="mb-3">
                    <label className="form-label small fw-semibold">Mobile Number</label>
                    <div className="input-group">
                      <span className="input-group-text bg-light border-end-0 fw-semibold small">+91</span>
                      <span className="input-group-text bg-light border-end-0 border-start-0">
                        <Phone size={16} className="text-muted" />
                      </span>
                      <input
                        type="tel"
                        inputMode="numeric"
                        className="form-control border-start-0"
                        placeholder="10-digit mobile number"
                        value={loginPhone}
                        onChange={(e) => setLoginPhone(normalizePhoneInput(e.target.value))}
                        maxLength={10}
                        required
                      />
                    </div>
                    <div className="d-flex align-items-center gap-1 mt-2 text-muted" style={{ fontSize: '0.75rem' }}>
                      <MessageCircle size={14} />
                      <span>OTP will be delivered on WhatsApp</span>
                    </div>
                  </div>

                  <button
                    type="submit"
                    className="btn btn-danger w-100 py-2.5 fw-semibold rounded-pill nb-btn-nav-danger text-dark"
                    disabled={loading || loginPhone.length !== 10}
                  >
                    {loading ? 'Sending OTP...' : 'Send OTP'}
                  </button>

                  <p className="small text-muted text-center mt-3 mb-0">
                    Don&apos;t have an account?{' '}
                    <button
                      type="button"
                      className="btn btn-link p-0 small fw-semibold text-decoration-none"
                      onClick={() => setAuthModalOpen('register')}
                    >
                      Register here
                    </button>
                  </p>
                </form>
              ) : (
                <form onSubmit={handleVerifyOtp}>
                  <button
                    type="button"
                    className="btn btn-link btn-sm p-0 mb-3 text-decoration-none d-flex align-items-center gap-1"
                    onClick={() => {
                      setLoginStep('phone');
                      setLoginOtp('');
                      setErrorMsg(null);
                      setSuccessMsg(null);
                    }}
                  >
                    <ArrowLeft size={14} />
                    Change number
                  </button>

                  <div className="mb-3">
                    <label className="form-label small fw-semibold">Enter 4-digit OTP</label>
                    <div className="input-group">
                      <span className="input-group-text bg-light border-end-0">
                        <Lock size={16} className="text-muted" />
                      </span>
                      <input
                        ref={otpInputRef}
                        type="text"
                        inputMode="numeric"
                        autoComplete="one-time-code"
                        className="form-control border-start-0 text-center fw-bold letter-spacing-wide"
                        placeholder="• • • •"
                        value={loginOtp}
                        onChange={(e) => setLoginOtp(e.target.value.replace(/\D/g, '').slice(0, 4))}
                        maxLength={4}
                        required
                        style={{ letterSpacing: '0.35em', fontSize: '1.25rem' }}
                      />
                    </div>
                  </div>

                  <button
                    type="submit"
                    className="btn btn-danger w-100 py-2.5 fw-semibold rounded-pill nb-btn-nav-danger text-dark mb-3"
                    disabled={loading || loginOtp.length !== 4}
                  >
                    {loading ? 'Verifying...' : 'Verify & Sign In'}
                  </button>

                  <div className="text-center small">
                    {resendTimer > 0 ? (
                      <span className="text-muted">Resend OTP in {resendTimer}s</span>
                    ) : (
                      <button
                        type="button"
                        className="btn btn-link p-0 small fw-semibold text-decoration-none"
                        onClick={handleResendOtp}
                        disabled={loading}
                      >
                        Resend OTP
                      </button>
                    )}
                  </div>
                </form>
              )
            ) : (
              <form onSubmit={handleRegisterSubmit} style={{ maxHeight: '65vh', overflowY: 'auto', paddingRight: '4px' }}>
                <div className="mb-2">
                  <label className="form-label small fw-semibold mb-1">Full Name</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light"><User size={14} /></span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Name"
                      value={regName}
                      onChange={(e) => setRegName(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <div className="mb-2">
                  <label className="form-label small fw-semibold mb-1">Email</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light"><Mail size={14} /></span>
                    <input
                      type="email"
                      className="form-control"
                      placeholder="Email Address"
                      value={regEmail}
                      onChange={(e) => setRegEmail(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <div className="mb-2">
                  <label className="form-label small fw-semibold mb-1">Phone Number</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text bg-light"><Phone size={14} /></span>
                    <input
                      type="tel"
                      className="form-control"
                      placeholder="Phone"
                      value={regPhone}
                      onChange={(e) => setRegPhone(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <div className="mb-2 row g-2">
                  <div className="col-6">
                    <label className="form-label small fw-semibold mb-1">Password</label>
                    <input
                      type="password"
                      className="form-control form-control-sm"
                      placeholder="Password"
                      value={regPassword}
                      onChange={(e) => setRegPassword(e.target.value)}
                      required
                    />
                  </div>
                  <div className="col-6">
                    <label className="form-label small fw-semibold mb-1">Confirm</label>
                    <input
                      type="password"
                      className="form-control form-control-sm"
                      placeholder="Confirm"
                      value={regPasswordConfirm}
                      onChange={(e) => setRegPasswordConfirm(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <div className="mb-2 row g-2">
                  <div className="col-6">
                    <label className="form-label small fw-semibold mb-1">I am a</label>
                    <select
                      className="form-select form-select-sm"
                      value={regRole}
                      onChange={(e) => setRegRole(e.target.value as 'owner' | 'customer' | 'agent')}
                    >
                      <option value="customer">Customer</option>
                      <option value="owner">Owner</option>
                      <option value="agent">Agent</option>
                    </select>
                  </div>
                  <div className="col-6">
                    <label className="form-label small fw-semibold mb-1">City</label>
                    <select
                      className="form-select form-select-sm"
                      value={regCityId}
                      onChange={(e) => setRegCityId(e.target.value)}
                      required
                    >
                      <option value="">Select city</option>
                      {cities.map((city) => (
                        <option key={city.id} value={city.id}>{city.name}</option>
                      ))}
                    </select>
                  </div>
                </div>

                {regRole !== 'customer' && (
                  <div className="mb-2">
                    <label className="form-label small fw-semibold mb-1">Aadhaar Card Number (12 digits)</label>
                    <input
                      type="text"
                      className="form-control form-control-sm"
                      placeholder="12 digit Aadhaar"
                      maxLength={12}
                      value={regAadharNo}
                      onChange={(e) => setRegAadharNo(e.target.value.replace(/\D/g, ''))}
                    />
                  </div>
                )}

                <div className="mb-2 row g-2">
                  {regRole !== 'customer' && (
                    <div className="col-6">
                      <label className="form-label small fw-semibold mb-1">Aadhaar File</label>
                      <input
                        type="file"
                        className="form-control form-control-sm"
                        accept="image/*"
                        onChange={(e) => setRegAadharFile(e.target.files ? e.target.files[0] : null)}
                      />
                    </div>
                  )}
                  <div className={regRole !== 'customer' ? 'col-6' : 'col-12'}>
                    <label className="form-label small fw-semibold mb-1">Profile Photo</label>
                    <input
                      type="file"
                      className="form-control form-control-sm"
                      accept="image/*"
                      onChange={(e) => setRegProfilePic(e.target.files ? e.target.files[0] : null)}
                    />
                  </div>
                </div>

                <div className="mb-3 form-check mt-3">
                  <input
                    type="checkbox"
                    className="form-check-input"
                    id="accept-terms-check"
                    checked={regAcceptTerms}
                    onChange={(e) => setRegAcceptTerms(e.target.checked)}
                    required
                  />
                  <label className="form-check-label small" htmlFor="accept-terms-check">
                    I accept the Terms of Use and Privacy Policy. I confirm I am presenting accurate details.
                  </label>
                </div>

                <button
                  type="submit"
                  className="btn btn-danger w-100 py-2 fw-semibold rounded-pill nb-btn-nav-danger text-dark"
                  disabled={loading}
                >
                  {loading ? 'Registering...' : 'Sign Up Free'}
                </button>

                <p className="small text-muted text-center mt-3 mb-0">
                  Already have an account?{' '}
                  <button
                    type="button"
                    className="btn btn-link p-0 small fw-semibold text-decoration-none"
                    onClick={() => setAuthModalOpen('login')}
                  >
                    Login here
                  </button>
                </p>
              </form>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AuthModals;
