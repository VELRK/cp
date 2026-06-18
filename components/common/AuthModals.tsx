'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/components/AuthContext';
import api from '@/lib/api';
import { X, Lock, Mail, User, Phone, CheckCircle, ShieldAlert } from 'lucide-react';

interface City {
  id: number;
  name: string;
  state: string;
}

const AuthModals: React.FC = () => {
  const { isAuthModalOpen, setAuthModalOpen, login, registerUser } = useAuth();
  const [cities, setCities] = useState<City[]>([]);

  // Form states
  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  const [regName, setRegName] = useState('');
  const [regEmail, setRegEmail] = useState('');
  const [regPhone, setRegPhone] = useState('');
  const [regPassword, setRegPassword] = useState('');
  const [regPasswordConfirm, setRegPasswordConfirm] = useState('');
  const [regRole, setRegRole] = useState<'owner' | 'buyer' | 'agent'>('buyer');
  const [regCityId, setRegCityId] = useState('');
  const [regAadharNo, setRegAadharNo] = useState('');
  const [regAadharFile, setRegAadharFile] = useState<File | null>(null);
  const [regProfilePic, setRegProfilePic] = useState<File | null>(null);
  const [regAcceptTerms, setRegAcceptTerms] = useState(false);

  // Status states
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isAuthModalOpen) {
      setErrorMsg(null);
      setSuccessMsg(null);
      // Fetch cities
      api.get('/api/nb/cities')
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.cities)) {
            setCities(res.data.cities);
          }
        })
        .catch((e) => console.error('Error fetching cities', e));
    }
  }, [isAuthModalOpen]);

  if (!isAuthModalOpen) return null;

  const handleLoginSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setLoading(true);
    try {
      const result = await login(loginEmail, loginPassword);
      if (!result.success) {
        setErrorMsg(result.message || 'Invalid credentials');
      } else {
        setLoginEmail('');
        setLoginPassword('');
      }
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Login failed. Please try again.');
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
        // Reset state
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
            <h2 className="modal-title h5 fw-bold text-primary" style={{ color: 'var(--nb-primary)' }}>
              {isAuthModalOpen === 'login' ? 'Login' : 'Create an Account'}
            </h2>
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
              <form onSubmit={handleLoginSubmit}>
                <div className="mb-3">
                  <label className="form-label small fw-semibold">Email or Phone</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light border-end-0">
                      <Mail size={16} className="text-muted" />
                    </span>
                    <input
                      type="text"
                      className="form-control border-start-0"
                      placeholder="Enter email or phone number"
                      value={loginEmail}
                      onChange={(e) => setLoginEmail(e.target.value)}
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
                      placeholder="Enter password"
                      value={loginPassword}
                      onChange={(e) => setLoginPassword(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-danger w-100 py-2.5 fw-semibold rounded-pill nb-btn-nav-danger text-dark"
                  disabled={loading}
                >
                  {loading ? 'Logging in...' : 'Sign In'}
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
                      onChange={(e) => setRegRole(e.target.value as 'owner' | 'buyer' | 'agent')}
                    >
                      <option value="buyer">Buyer / Tenant</option>
                      <option value="owner">Owner</option>
                      <option value="agent">Agent / Broker</option>
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

                {regRole !== 'buyer' && (
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
                  {regRole !== 'buyer' && (
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
                  <div className={regRole !== 'buyer' ? "col-6" : "col-12"}>
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
