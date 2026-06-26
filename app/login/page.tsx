'use client';

import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Phone, Lock, ShieldAlert, CheckCircle, ArrowLeft, MessageCircle } from 'lucide-react';
import { getDashboardPathForRole } from '@/lib/dashboardPaths';

type LoginStep = 'phone' | 'otp';
const RESEND_SECONDS = 60;

function normalizePhoneInput(value: string): string {
  return value.replace(/\D/g, '').slice(0, 10);
}

export default function LoginPage() {
  const { user, sendOtp, verifyOtp, resendOtp } = useAuth();
  const router = useRouter();

  const [step, setStep] = useState<LoginStep>('phone');
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
    if (step === 'otp') otpInputRef.current?.focus();
  }, [step]);

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
      setStep('otp');
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
                {step === 'phone' ? 'Sign in with Phone' : 'Verify OTP'}
              </h1>
              <p className="text-muted small">
                {step === 'phone'
                  ? 'We will send a 4-digit OTP to your WhatsApp'
                  : `OTP sent to +91 ${phone}`}
              </p>
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

            {step === 'phone' ? (
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
                  Don&apos;t have an account?{' '}
                  <Link href="/register" className="fw-semibold text-decoration-none text-primary">
                    Register here
                  </Link>
                </p>
              </form>
            ) : (
              <form onSubmit={handleVerifyOtp}>
                <button
                  type="button"
                  className="btn btn-link btn-sm p-0 mb-3 text-decoration-none d-flex align-items-center gap-1"
                  onClick={() => {
                    setStep('phone');
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
          </div>
        </div>
      </div>
    </div>
  );
}
