'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import { getCities, updateProfile } from '@/lib/frontendApi';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import {
  ArrowLeft,
  User,
  Mail,
  Phone,
  MapPin,
  Image as ImageIcon,
  ShieldAlert,
  CheckCircle2,
  Save,
} from 'lucide-react';

interface City {
  id: number;
  name: string;
  state?: string;
}

export default function UserProfilePage() {
  const { user, loading: authLoading, refreshUser } = useAuth();
  const router = useRouter();

  const [cities, setCities] = useState<City[]>([]);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [cityId, setCityId] = useState('');
  const [aadharNo, setAadharNo] = useState('');
  const [profilePic, setProfilePic] = useState<File | null>(null);
  const [profilePreview, setProfilePreview] = useState<string | null>(null);
  const [aadharFile, setAadharFile] = useState<File | null>(null);

  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login?redirect=/user/profile');
    }
  }, [user, authLoading, router]);

  useEffect(() => {
    getCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((err) => console.error('Error loading cities', err));
  }, []);

  useEffect(() => {
    if (!user) return;
    setName(user.name || '');
    setEmail(user.email || '');
    setPhone(user.phone || '');
    setCityId(user.city_id ? String(user.city_id) : '');
    setAadharNo(user.aadhar_no || '');
    setProfilePreview(user.profile_pic ? toFrontendAssetUrl(user.profile_pic) : null);
  }, [user]);

  const handleProfilePicChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      setErrorMsg('Profile photo must be under 5 MB.');
      return;
    }
    setProfilePic(file);
    setProfilePreview(URL.createObjectURL(file));
    setErrorMsg(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) return;

    setErrorMsg(null);
    setSuccessMsg(null);
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append('userId', String(user.id));
      formData.append('name', name.trim());
      formData.append('email', email.trim());
      formData.append('phone', phone.trim());
      if (cityId) formData.append('city_id', cityId);
      if (aadharNo.trim()) formData.append('aadhar_no', aadharNo.trim());
      if (profilePic) formData.append('profile_image', profilePic);
      if (aadharFile) formData.append('aadhar_file', aadharFile);

      const res = await updateProfile(formData);
      if (res.data?.success) {
        setSuccessMsg(res.data.message || 'Profile updated successfully.');
        setProfilePic(null);
        setAadharFile(null);
        const updatedUser = res.data.user;
        if (updatedUser?.profile_pic) {
          setProfilePreview(toFrontendAssetUrl(updatedUser.profile_pic));
        }
        await refreshUser();
      } else {
        setErrorMsg(res.data?.message || 'Could not update profile.');
      }
    } catch (err: unknown) {
      const message =
        err &&
        typeof err === 'object' &&
        'response' in err &&
        err.response &&
        typeof err.response === 'object' &&
        'data' in err.response &&
        err.response.data &&
        typeof err.response.data === 'object' &&
        'message' in err.response.data
          ? String((err.response.data as { message?: string }).message)
          : 'Failed to update profile. Please try again.';
      setErrorMsg(message);
    } finally {
      setLoading(false);
    }
  };

  if (authLoading || !user) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading profile...</span>
        </div>
      </div>
    );
  }

  const roleLabel =
    user.role === 'owner' ? 'Property Owner' : user.role === 'tenant' ? 'Tenant' : user.role;

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-8">
          <Link
            href="/"
            className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0"
          >
            <ArrowLeft size={14} />
            <span>Back to Home</span>
          </Link>

          <div className="d-flex align-items-center gap-2 mb-4">
            <User size={24} className="text-primary" />
            <h1 className="h3 fw-bold text-dark m-0">Manage Profile</h1>
          </div>

          <div className="card border-0 shadow-sm rounded-3 p-4 bg-white">
            <div className="d-flex flex-wrap align-items-center gap-3 mb-4 pb-3 border-bottom">
              <div
                className="rounded-circle overflow-hidden border bg-light flex-shrink-0 d-flex align-items-center justify-content-center"
                style={{ width: 72, height: 72 }}
              >
                {profilePreview ? (
                  <img
                    src={profilePreview}
                    alt={name || 'Profile'}
                    className="w-100 h-100 object-fit-cover"
                  />
                ) : (
                  <User size={32} className="text-muted" />
                )}
              </div>
              <div>
                <div className="fw-bold text-dark">{user.name}</div>
                <div className="small text-muted">{user.email}</div>
                <span className="badge bg-primary-subtle text-primary border border-primary-subtle mt-1">
                  {roleLabel}
                </span>
                {user.status !== 'approved' && (
                  <span className="badge bg-warning-subtle text-warning border border-warning-subtle ms-1 mt-1">
                    {user.status === 'pending' ? 'Pending approval' : 'Not approved'}
                  </span>
                )}
              </div>
            </div>

            {errorMsg && (
              <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-3 rounded-3">
                <ShieldAlert size={16} />
                <span>{errorMsg}</span>
              </div>
            )}
            {successMsg && (
              <div className="alert alert-success d-flex align-items-center gap-2 small py-2 mb-3 rounded-3">
                <CheckCircle2 size={16} />
                <span>{successMsg}</span>
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label small fw-semibold">
                    <User size={14} className="me-1" />
                    Full Name
                  </label>
                  <input
                    type="text"
                    className="form-control"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                    minLength={2}
                  />
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold">
                    <Mail size={14} className="me-1" />
                    Email
                  </label>
                  <input
                    type="email"
                    className="form-control"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold">
                    <Phone size={14} className="me-1" />
                    Phone
                  </label>
                  <input
                    type="tel"
                    className="form-control"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    required
                  />
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold">
                    <MapPin size={14} className="me-1" />
                    City
                  </label>
                  <select
                    className="form-select"
                    value={cityId}
                    onChange={(e) => setCityId(e.target.value)}
                  >
                    <option value="">Select city</option>
                    {cities.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                        {c.state ? `, ${c.state}` : ''}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="col-md-6">
                  <label className="form-label small fw-semibold">
                    <ImageIcon size={14} className="me-1" />
                    Profile Photo
                  </label>
                  <input
                    type="file"
                    className="form-control"
                    accept="image/jpeg,image/png,image/webp"
                    onChange={handleProfilePicChange}
                  />
                  <div className="form-text">JPG, PNG or WebP. Max 5 MB.</div>
                </div>

                {user.role === 'owner' && (
                  <>
                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">Aadhaar Number (optional)</label>
                      <input
                        type="text"
                        className="form-control"
                        value={aadharNo}
                        onChange={(e) => setAadharNo(e.target.value.replace(/\D/g, '').slice(0, 12))}
                        placeholder="12-digit Aadhaar"
                        maxLength={12}
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">Aadhaar Document (optional)</label>
                      <input
                        type="file"
                        className="form-control"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        onChange={(e) => setAadharFile(e.target.files?.[0] || null)}
                      />
                    </div>
                  </>
                )}
              </div>

              <div className="mt-4 pt-3 border-top">
                <button
                  type="submit"
                  className="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2"
                  disabled={loading}
                >
                  <Save size={16} />
                  <span>{loading ? 'Saving...' : 'Save Changes'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
