'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { getCities } from '@/lib/frontendApi';
import { ShieldAlert, CheckCircle, CheckCircle2, User, Phone } from 'lucide-react';

interface City {
  id: number;
  name: string;
  state: string;
}

export default function RegisterPage() {
  const { user, registerUser } = useAuth();
  const router = useRouter();

  const [cities, setCities] = useState<City[]>([]);
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [role, setRole] = useState<'tenant' | 'owner' | 'agent'>('tenant');
  const [cityId, setCityId] = useState('');
  const [profilePic, setProfilePic] = useState<File | null>(null);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (user) {
      if (user.role === 'owner') {
        router.push('/owner/dashboard');
      } else if (user.role === 'tenant') {
        router.push('/tenant/dashboard');
      } else if (user.role === 'agent') {
        router.push('/agent/dashboard');
      } else {
        router.push('/');
      }
    }
  }, [user, router]);

  useEffect(() => {
    getCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((err) => console.error('Error fetching cities', err));
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setSuccessMsg(null);

    const normalizedPhone = phone.replace(/\D/g, '').slice(-10);
    if (normalizedPhone.length !== 10) {
      setErrorMsg('Enter a valid 10-digit mobile number.');
      return;
    }
    if (!acceptTerms) {
      setErrorMsg('You must agree to the Terms of Use.');
      return;
    }

    setLoading(true);
    try {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('phone', normalizedPhone);
      formData.append('role', role);
      formData.append('accept_terms', acceptTerms ? '1' : '0');
      if (cityId) formData.append('city_id', cityId);
      if (profilePic) formData.append('profile_image', profilePic);

      const result = await registerUser(formData);
      if (result.success) {
        setSuccessMsg('Registration successful! You can now sign in with OTP on your phone.');
        setTimeout(() => {
          router.push('/login');
        }, 3000);
      } else {
        setErrorMsg(result.message || 'Registration failed.');
      }
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'A network error occurred. Please verify your details.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center align-items-stretch g-4">
        <div className="col-lg-5 d-none d-lg-block">
          <div className="nb-post-landing-left h-100 rounded-3 d-flex flex-column justify-content-center w-100">
            <h1 className="nb-post-landing-title">
              Sell or Rent Property<br />
              <span>online faster</span> with CP
            </h1>
            <ul className="nb-post-landing-list">
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Advertise for FREE</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get unlimited enquiries</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get shortlisted buyers and tenants</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Sign in anytime with phone OTP</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="col-lg-6">
          <div className="card border-0 shadow-lg rounded-3 p-4 bg-white">
            <div className="text-center mb-4">
              <h1 className="h3 fw-extrabold text-primary mb-1" style={{ color: '#0b2c56' }}>
                Register Free Account
              </h1>
              <p className="text-muted small">Name + phone only — sign in later with WhatsApp OTP</p>
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

            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label className="form-label small fw-semibold">Full Name</label>
                <div className="input-group">
                  <span className="input-group-text bg-light"><User size={16} /></span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Enter full name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                  />
                </div>
              </div>

              <div className="mb-3">
                <label className="form-label small fw-semibold">Phone Number</label>
                <div className="input-group">
                  <span className="input-group-text bg-light"><Phone size={16} /></span>
                  <input
                    type="tel"
                    className="form-control"
                    placeholder="10-digit mobile number"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, '').slice(0, 10))}
                    maxLength={10}
                    required
                  />
                </div>
              </div>

              <div className="row g-3 mb-3">
                <div className="col-md-6">
                  <label className="form-label small fw-semibold">Account Role</label>
                  <select
                    className="form-select"
                    value={role}
                    onChange={(e) => setRole(e.target.value as 'tenant' | 'owner' | 'agent')}
                  >
                    <option value="tenant">Tenant / Buyer</option>
                    <option value="owner">Property Owner</option>
                    <option value="agent">Agent</option>
                  </select>
                </div>
                <div className="col-md-6">
                  <label className="form-label small fw-semibold">Your City</label>
                  <select
                    className="form-select"
                    value={cityId}
                    onChange={(e) => setCityId(e.target.value)}
                    required
                  >
                    <option value="">Select City</option>
                    {cities.map((c) => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="mb-4">
                <label className="form-label small fw-semibold">Profile Picture (optional)</label>
                <input
                  type="file"
                  className="form-control form-control-sm"
                  accept="image/*"
                  onChange={(e) => setProfilePic(e.target.files ? e.target.files[0] : null)}
                />
              </div>

              <div className="form-check mb-4">
                <input
                  className="form-check-input"
                  type="checkbox"
                  id="regAcceptCheck"
                  checked={acceptTerms}
                  onChange={(e) => setAcceptTerms(e.target.checked)}
                  required
                />
                <label className="form-check-label small text-muted" htmlFor="regAcceptCheck">
                  I accept the Terms of Use and Privacy Policy of Coimbatore Properties NoBroker.
                </label>
              </div>

              <button
                type="submit"
                className="btn btn-danger w-100 py-2 fw-semibold rounded-pill text-dark"
                disabled={loading}
              >
                {loading ? 'Creating Account...' : 'Register Now'}
              </button>

              <p className="small text-muted text-center mt-3 mb-0">
                Already have an account?{' '}
                <Link href="/login" className="fw-semibold text-decoration-none text-primary">
                  Sign In with OTP
                </Link>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
