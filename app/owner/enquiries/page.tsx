'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getOwnerEnquiries } from '@/lib/frontendApi';
import {
  ArrowLeft,
  MessageSquare,
  Calendar,
  Mail,
  Phone,
  MapPin,
  Tag,
  ExternalLink,
  User,
  Hash,
} from 'lucide-react';

interface Enquiry {
  id: number;
  tenant_id: number;
  property_id: number;
  property_title: string;
  property_slug?: string | null;
  city_name?: string | null;
  locality?: string | null;
  price?: number | string | null;
  listing_type?: string | null;
  property_type?: string | null;
  tenant_name: string;
  tenant_email?: string | null;
  tenant_phone?: string | null;
  email: string;
  phone: string;
  message: string;
  status: string;
  created_at: string;
  updated_at?: string | null;
}

function statusBadgeClass(status: string): string {
  switch (status) {
    case 'new':
      return 'bg-info text-dark';
    case 'read':
      return 'bg-primary';
    case 'responded':
      return 'bg-success';
    case 'closed':
      return 'bg-secondary';
    default:
      return 'bg-secondary';
  }
}

function formatPrice(price: Enquiry['price'], listingType?: string | null): string {
  const n = Number(price);
  if (!Number.isFinite(n) || n <= 0) return '—';
  const formatted = new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0,
  }).format(n);
  if (listingType === 'rent') return `${formatted} / month`;
  return formatted;
}

function formatListingType(value?: string | null): string {
  if (!value) return '—';
  return value.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatDateTime(value?: string | null): string {
  if (!value) return '—';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' });
}

export default function OwnerEnquiriesPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [enquiries, setEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && (!user || (user.role !== 'owner' && user.role !== 'agent'))) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  useEffect(() => {
    if (!user) return;
    setLoading(true);
    setError(null);
    getOwnerEnquiries()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.enquiries)) {
          setEnquiries(res.data.enquiries);
        } else {
          setError(res.data?.message || 'Could not load enquiries.');
        }
      })
      .catch((err) => {
        console.error('Error loading enquiries', err);
        setError('Could not load enquiries. Please try again.');
      })
      .finally(() => setLoading(false));
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading enquiries...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link
            href="/owner/dashboard"
            className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0"
          >
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          <div className="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
              <h1 className="h3 fw-bold text-dark mb-2">Enquiries on Your Properties</h1>
              <p className="text-muted small mb-0">
                All buyer/tenant enquiries with property details and contact information submitted on each request.
              </p>
            </div>
            <span className="badge bg-light text-dark border px-3 py-2 rounded-pill">
              {enquiries.length} total
            </span>
          </div>

          {error && (
            <div className="alert alert-danger border-0 rounded-3 mb-4" role="alert">
              {error}
            </div>
          )}

          <div className="d-flex flex-column gap-3">
            {enquiries.map((e) => {
              const propertyHref = e.property_slug ? `/property/${e.property_slug}/` : null;
              const locationParts = [e.locality, e.city_name].filter(Boolean);

              return (
                <div key={e.id} className="card border-0 shadow-sm bg-white overflow-hidden">
                  <div className="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                    <div className="d-flex align-items-center gap-2 flex-wrap">
                      <span className="badge bg-light text-dark border font-monospace">
                        <Hash size={12} className="me-1" />
                        {e.id}
                      </span>
                      <span className={`badge rounded-pill py-2 px-3 ${statusBadgeClass(e.status)}`}>
                        {e.status}
                      </span>
                    </div>
                    <span className="text-muted small d-inline-flex align-items-center gap-1">
                      <Calendar size={13} />
                      {formatDateTime(e.created_at)}
                    </span>
                  </div>

                  <div className="card-body p-4">
                    <div className="row g-4">
                      <div className="col-lg-6">
                        <h2 className="h6 fw-bold text-uppercase text-muted small mb-3">Property</h2>
                        <div className="d-flex align-items-start gap-2 mb-2">
                          <MessageSquare size={16} className="text-primary mt-1 flex-shrink-0" />
                          <div>
                            {propertyHref ? (
                              <Link
                                href={propertyHref}
                                className="fw-bold text-decoration-none text-primary d-inline-flex align-items-center gap-1"
                                target="_blank"
                                rel="noopener noreferrer"
                              >
                                {e.property_title}
                                <ExternalLink size={14} />
                              </Link>
                            ) : (
                              <span className="fw-bold text-dark">{e.property_title}</span>
                            )}
                            <div className="text-muted small mt-1">Listing ID: {e.property_id}</div>
                          </div>
                        </div>
                        <ul className="list-unstyled small mb-0 text-secondary">
                          {locationParts.length > 0 && (
                            <li className="d-flex align-items-start gap-2 mb-2">
                              <MapPin size={14} className="mt-1 flex-shrink-0" />
                              <span>{locationParts.join(', ')}</span>
                            </li>
                          )}
                          <li className="d-flex align-items-start gap-2 mb-2">
                            <Tag size={14} className="mt-1 flex-shrink-0" />
                            <span>
                              {formatListingType(e.listing_type)}
                              {e.property_type ? ` · ${formatListingType(e.property_type)}` : ''}
                            </span>
                          </li>
                          <li className="fw-semibold text-dark">
                            {formatPrice(e.price, e.listing_type)}
                          </li>
                        </ul>
                      </div>

                      <div className="col-lg-6">
                        <h2 className="h6 fw-bold text-uppercase text-muted small mb-3">Enquirer</h2>
                        <div className="d-flex align-items-center gap-2 mb-3">
                          <User size={16} className="text-primary" />
                          <span className="fw-semibold">{e.tenant_name || 'Guest'}</span>
                          <span className="text-muted small">(User #{e.tenant_id})</span>
                        </div>
                        <ul className="list-unstyled small mb-0">
                          <li className="d-flex align-items-center gap-2 mb-2">
                            <Mail size={14} className="text-muted flex-shrink-0" />
                            <a href={`mailto:${e.email}`} className="text-decoration-none">
                              {e.email}
                            </a>
                            <span className="text-muted">(on enquiry)</span>
                          </li>
                          <li className="d-flex align-items-center gap-2 mb-2">
                            <Phone size={14} className="text-muted flex-shrink-0" />
                            <a href={`tel:${e.phone}`} className="text-decoration-none">
                              {e.phone}
                            </a>
                            <span className="text-muted">(on enquiry)</span>
                          </li>
                          {(e.tenant_email || e.tenant_phone) && (
                            <li className="text-muted mt-2 pt-2 border-top">
                              Account:
                              {e.tenant_email ? (
                                <span className="ms-1">
                                  <a href={`mailto:${e.tenant_email}`} className="text-decoration-none">
                                    {e.tenant_email}
                                  </a>
                                </span>
                              ) : null}
                              {e.tenant_email && e.tenant_phone ? ' · ' : null}
                              {e.tenant_phone ? (
                                <a href={`tel:${e.tenant_phone}`} className="text-decoration-none">
                                  {e.tenant_phone}
                                </a>
                              ) : null}
                            </li>
                          )}
                        </ul>
                      </div>
                    </div>

                    <div className="mt-4 pt-3 border-top">
                      <h2 className="h6 fw-bold text-uppercase text-muted small mb-2">Message</h2>
                      <div
                        className="text-secondary small p-3 bg-light rounded-3 mb-0"
                        style={{ whiteSpace: 'pre-line' }}
                      >
                        {e.message || '—'}
                      </div>
                    </div>

                    {e.updated_at && e.updated_at !== e.created_at && (
                      <p className="text-muted small mb-0 mt-3">
                        Last updated: {formatDateTime(e.updated_at)}
                      </p>
                    )}
                  </div>
                </div>
              );
            })}

            {enquiries.length === 0 && !error && (
              <div className="text-center py-5 border bg-white rounded-3 shadow-sm text-muted">
                <MessageSquare size={36} className="mx-auto mb-2 opacity-50" />
                <p className="mb-0">No enquiries received yet on your properties.</p>
                <Link href="/owner/listings" className="btn btn-sm btn-outline-primary rounded-pill px-4 mt-3">
                  View your listings
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
