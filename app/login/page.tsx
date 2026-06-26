'use client';

import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Phone, Lock, ShieldAlert, CheckCircle, ArrowLeft, MessageCircle, Mail } from 'lucide-react';
import { getDashboardPathForRole } from '@/lib/dashboardPaths';

type LoginMode = 'email' | 'otp';
type OtpStep = 'phone' | 'verify';
const RESEND_SECONDS = 60;

function normalizePhoneInput(value: string): string {
  return value.replace(/\D/g, '').slice(0, 10);
}

export default function LoginPage() {
  const { user, login, sendOtp, verifyOtp, resendOtp } = useAuth();
  const router = useRouter();

  const [mode, setMode] = useState<LoginMode>('email');
  const [otpStep, setOtpStep] = useState<OtpStep>('phone');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [phone, setPhone] = useState('');
  const [otp, setOtp] = useState('');
  const [resendTimer, setResendTimer] = useState(0);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const otpInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (user) {
      router.push(getDashboardPathForRole(user.role));
    }
  }, [user, router]);

  useEffect(() => {
    if (resendTimer <= 0) return;
    const t = setTimeout(() => setResendTimer((s) => s - 1), 1000);
    return () => clearTimeout(t);
  }, [resendTimer]);

  useEffect(() => {
    if (mode === 'otp' && otpStep === 'verify') otpInputRef.current?.focus();
  }, [mode, otpStep]);

  const switchMode = (next: LoginMode) => {
    setMode(next);
    setOtpStep('phone');
    setErrorMsg(null);
    setSuccessMsg(null);
    setOtp('');
  };

  const handleEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);
    const trimmedEmail = email.trim();
    if (!trimmedEmail || !password) {
      setErrorMsg('Email and password are required.');
      return;
    }
    setLoading(true);
    try {
      const result = await login(trimmedEmail, password);
      if (!result?.success) {
        setErrorMsg(result?.message || 'Invalid email or password.');
        if (result?.use_otp) setMode('otp');
        return;
      }
      router.push(getDashboardPathForRole(result.user?.role));
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Login failed.');
    } finally {
      setLoading(false);
    }
  };

  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);
    const normalized = normalizePhoneInput(phone);
    if (normalized.length !== 10) {
      setErrorMsg('Enter a valid 10-digit mobile number.');
      return;
    }
    setLoading(true);
    try {
      const result = await sendOtp(normalized);
      if (!result.success) {
        setErrorMsg(result.message || 'Could not send OTP.');
        return;
      }
      setPhone(normalized);
      setOtpStep('verify');
      setOtp('');
      setResendTimer(RESEND_SECONDS);
      setSuccessMsg(
        result.development_mode && result.otp
          ? `OTP sent (dev mode): ${result.otp}`
          : 'OTP sent to your WhatsApp number.'
      );
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Failed to send OTP.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    const code = otp.replace(/\D/g, '').slice(0, 4);
    if (code.length !== 4) {
      setErrorMsg('Enter the 4-digit OTP.');
      return;
    }
    setLoading(true);
    try {
      const result = await verifyOtp(phone, code);
      if (!result.success) {
        setErrorMsg(result.message || 'Invalid OTP.');
        return;
      }
      router.push(getDashboardPathForRole(result.user?.role));
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Verification failed.');
    } finally {
      setLoading(false);
    }
  };

  const handleResend = async () => {
    if (resendTimer > 0 || loading) return;
    setLoading(true);
    setErrorMsg(null);
    try {
      const result = await resendOtp(phone);
      if (!result.success) {
        setErrorMsg(result.message || 'Could not resend OTP.');
        return;
      }
      setResendTimer(RESEND_SECONDS);
      setSuccessMsg('A new OTP has been sent to your WhatsApp.');
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Failed to resend OTP.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-md-5">
          <div className="card border-0 shadow-lg rounded-3 p-4 bg-white mt-5">
            <div className="text-center mb-4">
              <h1 className="h3 fw-extrabold text-primary mb-1" style={{ color: 'var(--nb-primary)' }}>
                Sign In
              </h1>
              <p className="text-muted small mb-3">
                {mode === 'email'
                  ? 'Use your registered email and password'
                  : otpStep === 'phone'
                    ? 'We will send a 4-digit OTP to your WhatsApp'
                    : `OTP sent to +91 ${phone}`}
              </p>

              <div className="d-flex rounded-pill bg-light p-1 gap-1">
                <button
                  type="button"
                  className={`btn btn-sm flex-fill rounded-pill fw-semibold ${mode === 'email' ? 'btn-danger text-dark' : 'btn-light border-0 text-muted'}`}
                  onClick={() => switchMode('email')}
                >
                  Email & Password
                </button>
                <button
                  type="button"
                  className={`btn btn-sm flex-fill rounded-pill fw-semibold ${mode === 'otp' ? 'btn-danger text-dark' : 'btn-light border-0 text-muted'}`}
                  onClick={() => switchMode('otp')}
                >
                  Phone OTP
                </button>
              </div>
            </div>

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

            {mode === 'email' ? (
              <form onSubmit={handleEmailLogin}>
                <div className="mb-3">
                  <label className="form-label small fw-semibold">Email Address</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light border-end-0">
                      <Mail size={16} className="text-muted" />
                    </span>
                    <input
                      type="email"
                      className="form-control border-start-0"
                      placeholder="you@example.com"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      autoComplete="email"
                      required
                    />
                  </div>
                </div>

                <div className="mb-4">
                  <label className="form-label small fw-semibold">Password</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light border-end-0">
                      <Lock size={16} className="text-muted" />
                    </span>
                    <input
                      type="password"
                      className="form-control border-start-0"
                      placeholder="Your password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      autoComplete="current-password"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-danger w-100 py-2 fw-semibold rounded-pill text-dark"
                  disabled={loading || !email.trim() || !password}
                >
                  {loading ? 'Signing in...' : 'Sign In'}
                </button>

                <p className="small text-muted text-center mt-3 mb-0">
                  Prefer OTP?{' '}
                  <button type="button" className="btn btn-link p-0 small fw-semibold text-decoration-none" onClick={() => switchMode('otp')}>
                    Sign in with phone
                  </button>
                </p>
              </form>
            ) : otpStep === 'phone' ? (
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
                      value={phone}
                      onChange={(e) => setPhone(normalizePhoneInput(e.target.value))}
                      maxLength={10}
                      required
                    />
                  </div>
                  <div className="d-flex align-items-center gap-1 mt-2 text-muted" style={{ fontSize: '0.75rem' }}>
                    <MessageCircle size={14} />
                    <span>OTP delivered on WhatsApp</span>
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-danger w-100 py-2 fw-semibold rounded-pill text-dark"
                  disabled={loading || phone.length !== 10}
                >
                  {loading ? 'Sending OTP...' : 'Send OTP'}
                </button>

                <p className="small text-muted text-center mt-3 mb-0">
                  Have email login?{' '}
                  <button type="button" className="btn btn-link p-0 small fw-semibold text-decoration-none" onClick={() => switchMode('email')}>
                    Use email & password
                  </button>
                </p>
              </form>
            ) : (
              <form onSubmit={handleVerifyOtp}>
                <button
                  type="button"
                  className="btn btn-link btn-sm p-0 mb-3 text-decoration-none d-flex align-items-center gap-1"
                  onClick={() => {
                    setOtpStep('phone');
                    setOtp('');
                    setErrorMsg(null);
                    setSuccessMsg(null);
                  }}
                >
                  <ArrowLeft size={14} />
                  Change number
                </button>

                <div className="mb-4">
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
                      className="form-control border-start-0 text-center fw-bold"
                      placeholder="• • • •"
                      value={otp}
                      onChange={(e) => setOtp(e.target.value.replace(/\D/g, '').slice(0, 4))}
                      maxLength={4}
                      required
                      style={{ letterSpacing: '0.35em', fontSize: '1.25rem' }}
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-danger w-100 py-2 fw-semibold rounded-pill text-dark mb-3"
                  disabled={loading || otp.length !== 4}
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
                      onClick={handleResend}
                      disabled={loading}
                    >
                      Resend OTP
                    </button>
                  )}
                </div>
              </form>
            )}

            <p className="small text-muted text-center mt-4 mb-0">
              Don&apos;t have an account?{' '}
              <Link href="/register" className="fw-semibold text-decoration-none text-primary">
                Register here
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
