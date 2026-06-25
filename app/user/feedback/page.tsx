'use client';

import React, { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../components/AuthContext';
import { getFeedbacks, submitFeedback } from '@/lib/frontendApi';
import { formatApiErrorMessage } from '@/lib/api';
import { ArrowLeft, MessageSquare, Send, CheckCircle2, AlertCircle, Calendar, Paperclip, X } from 'lucide-react';

interface FeedbackItem {
  id: number;
  userId: string;
  title: string;
  description: string | null;
  image: string | null;
  createdAt: string;
  name: string | null;
}

export default function FeedbackPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [feedbacks, setFeedbacks] = useState<FeedbackItem[]>([]);
  const [loadingList, setLoadingList] = useState(true);

  // Form states
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);

  // Status states
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch feedback history
  const fetchFeedbacks = async () => {
    try {
      setLoadingList(true);
      const res = await getFeedbacks();
      if (res.data?.success && Array.isArray(res.data.feedbacks)) {
        setFeedbacks(res.data.feedbacks);
      }
    } catch (err: any) {
      console.error('Error fetching feedbacks:', err);
    } finally {
      setLoadingList(false);
    }
  };

  useEffect(() => {
    if (user) {
      fetchFeedbacks();
    }
  }, [user]);

  // Handle image attachment change
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setErrorMessage('File size must be less than 5MB');
        return;
      }
      setImageFile(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setImagePreview(reader.result as string);
      };
      reader.readAsDataURL(file);
      setErrorMessage(null);
    }
  };

  // Remove attached image
  const removeAttachment = () => {
    setImageFile(null);
    setImagePreview(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  // Form submission
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim()) {
      setErrorMessage('Please provide a subject/title.');
      return;
    }

    try {
      setSubmitting(true);
      setErrorMessage(null);
      setSuccessMessage(null);

      const formData = new FormData();
      formData.append('title', title);
      formData.append('description', description);
      if (imageFile) {
        formData.append('image_file', imageFile);
      }
      formData.append('name', user?.name || '');

      const res = await submitFeedback(formData);

      if (res.data?.success) {
        setSuccessMessage(res.data.message || 'Feedback submitted successfully!');
        setTitle('');
        setDescription('');
        removeAttachment();
        // Refresh list
        fetchFeedbacks();
      } else {
        setErrorMessage(formatApiErrorMessage(res.data, 'Failed to submit feedback.'));
      }
    } catch (err: any) {
      console.error('Error submitting feedback:', err);
      setErrorMessage(formatApiErrorMessage(err.response?.data, 'An error occurred while submitting. Please try again.'));
    } finally {
      setSubmitting(false);
    }
  };

  if (authLoading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  // Dashboard path based on user role
  const getDashboardPath = () => {
    if (user?.role === 'owner') return '/owner/dashboard';
    return '/tenant/dashboard';
  };

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-12 col-xl-11">
          {/* Back Navigation */}
          <Link
            href={getDashboardPath()}
            className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0"
          >
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          {/* Heading */}
          <div className="d-flex align-items-center gap-3 mb-5">
            <div className="bg-primary text-white p-3 rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <MessageSquare size={24} />
            </div>
            <div>
              <h1 className="h3 fw-bold text-dark m-0">Share Your Feedback</h1>
              <p className="text-muted small m-0">
                Help us improve Coimbatore Properties. We appreciate your suggestions, reports, and comments.
              </p>
            </div>
          </div>

          <div className="row g-4">
            {/* Left Column: Form */}
            <div className="col-lg-6">
              <div className="card border-0 shadow-sm bg-white p-4">
                <h2 className="h5 fw-bold mb-4 text-dark border-bottom pb-2">New Feedback</h2>

                {successMessage && (
                  <div className="alert alert-success d-flex align-items-center gap-2 small mb-4 py-2 px-3 border-0 shadow-sm" role="alert">
                    <CheckCircle2 size={16} className="text-success" />
                    <div>{successMessage}</div>
                  </div>
                )}

                {errorMessage && (
                  <div className="alert alert-danger d-flex align-items-center gap-2 small mb-4 py-2 px-3 border-0 shadow-sm" role="alert">
                    <AlertCircle size={16} className="text-danger" />
                    <div>{errorMessage}</div>
                  </div>
                )}

                <form onSubmit={handleSubmit}>
                  {/* Name field (readonly preview) */}
                  <div className="mb-3">
                    <label className="form-label text-muted small fw-bold mb-1">From</label>
                    <input
                      type="text"
                      className="form-control bg-light border-0 small"
                      value={`${user?.name || 'User'} (${user?.email || ''})`}
                      disabled
                    />
                  </div>

                  {/* Subject field */}
                  <div className="mb-3">
                    <label htmlFor="feedback-title" className="form-label text-muted small fw-bold mb-1">
                      Subject / Topic <span className="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      id="feedback-title"
                      className="form-control small border"
                      placeholder="e.g. Bug report, suggestion, testimonial..."
                      value={title}
                      onChange={(e) => setTitle(e.target.value)}
                      required
                    />
                  </div>

                  {/* Details field */}
                  <div className="mb-3">
                    <label htmlFor="feedback-desc" className="form-label text-muted small fw-bold mb-1">
                      Detailed Description
                    </label>
                    <textarea
                      id="feedback-desc"
                      className="form-control small border"
                      rows={5}
                      placeholder="Please explain in detail..."
                      value={description}
                      onChange={(e) => setDescription(e.target.value)}
                    />
                  </div>

                  {/* Optional Image Attachment */}
                  <div className="mb-4">
                    <label className="form-label text-muted small fw-bold mb-1 d-block">
                      Attach Screenshot or Image (Optional)
                    </label>
                    
                    {!imagePreview ? (
                      <button
                        type="button"
                        onClick={() => fileInputRef.current?.click()}
                        className="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 py-2 px-3 rounded-3 border-dashed w-100 justify-content-center text-secondary small"
                        style={{ borderStyle: 'dashed' }}
                      >
                        <Paperclip size={14} />
                        <span>Choose Image File (JPG, PNG, WEBP max 5MB)</span>
                      </button>
                    ) : (
                      <div className="position-relative border rounded-3 p-2 d-flex align-items-center gap-3 bg-light">
                        <img
                          src={imagePreview}
                          alt="Feedback attachment preview"
                          className="rounded"
                          style={{ width: '60px', height: '60px', objectFit: 'cover' }}
                        />
                        <div className="flex-grow-1 overflow-hidden">
                          <p className="text-dark small mb-0 fw-bold truncate">{imageFile?.name}</p>
                          <p className="text-muted small mb-0">{( (imageFile?.size || 0) / 1024 / 1024 ).toFixed(2)} MB</p>
                        </div>
                        <button
                          type="button"
                          onClick={removeAttachment}
                          className="btn btn-light btn-sm border-0 rounded-circle p-1 hover-danger position-absolute top-50 end-0 translate-middle-y me-3"
                          title="Remove attachment"
                        >
                          <X size={16} className="text-muted" />
                        </button>
                      </div>
                    )}

                    <input
                      type="file"
                      ref={fileInputRef}
                      onChange={handleFileChange}
                      accept="image/*"
                      className="d-none"
                    />
                  </div>

                  {/* Submit Button */}
                  <button
                    type="submit"
                    className="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100 py-2.5 rounded-3 fw-bold shadow-sm"
                    disabled={submitting}
                  >
                    {submitting ? (
                      <>
                        <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Submitting Feedback...</span>
                      </>
                    ) : (
                      <>
                        <Send size={16} />
                        <span>Submit Feedback</span>
                      </>
                    )}
                  </button>
                </form>
              </div>
            </div>

            {/* Right Column: History */}
            <div className="col-lg-6">
              <div className="card border-0 shadow-sm bg-white p-4 h-100 d-flex flex-column">
                <h2 className="h5 fw-bold mb-4 text-dark border-bottom pb-2">Feedback History</h2>

                {loadingList ? (
                  <div className="text-center py-5 my-auto">
                    <div className="spinner-border text-primary" role="status">
                      <span className="visually-hidden">Loading history...</span>
                    </div>
                  </div>
                ) : feedbacks.length > 0 ? (
                  <div className="d-flex flex-column gap-3 overflow-auto pr-1" style={{ maxHeight: '550px' }}>
                    {feedbacks.map((item) => (
                      <div key={item.id} className="border rounded-3 p-3 bg-light position-relative">
                        <div className="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                          <h3 className="h6 fw-bold m-0 text-dark">{item.title}</h3>
                          <span className="text-muted small d-flex align-items-center gap-1">
                            <Calendar size={12} />
                            {new Date(item.createdAt).toLocaleDateString('en-IN')}
                          </span>
                        </div>

                        {item.description && (
                          <p className="text-secondary small mb-2 whitespace-pre-wrap">{item.description}</p>
                        )}

                        {item.image && (
                          <div className="mt-2">
                            <a
                              href={`/${item.image}`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="d-inline-block border rounded overflow-hidden"
                            >
                              <img
                                src={`/${item.image}`}
                                alt="Feedback attachment"
                                className="img-fluid"
                                style={{ maxHeight: '100px', maxWidth: '100%', objectFit: 'contain' }}
                              />
                            </a>
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-5 my-auto text-muted">
                    <MessageSquare size={36} className="mx-auto mb-2 opacity-50 text-secondary" />
                    <p className="mb-0 small">No feedback submitted yet.</p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
