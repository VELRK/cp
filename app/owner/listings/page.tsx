'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getOwnerListings } from '@/lib/frontendApi';
import { ArrowLeft, Plus, Eye, Edit, Home, Grid, MapPin, Layers, CheckCircle, Clock, BarChart2, CalendarCheck } from 'lucide-react';

interface Listing {
  id: number;
  title: string;
  slug: string;
  is_active: number;
  city_name: string;
  price: number;
  views: number;
  property_type: string;
  listing_type: string;
  image_urls?: string[];
  images?: string[];
  thumbnail_url?: string | null;
  location_image_url?: string;
}

export default function OwnerListingsPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [listings, setListings] = useState<Listing[]>([]);
  const [loading, setLoading] = useState(true);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && (!user || user.role !== 'owner')) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch listings
  useEffect(() => {
    if (user) {
      getOwnerListings()
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.listings)) {
            setListings(res.data.listings);
          }
        })
        .catch((err) => console.error('Error loading listings', err))
        .finally(() => setLoading(false));
    }
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading properties...</span>
        </div>
      </div>
    );
  }

  const totalListings = listings.length;
  const activeListings = listings.filter(p => Number(p.is_active) === 1).length;
  const pendingListings = totalListings - activeListings;
  const totalViews = listings.reduce((sum, p) => sum + (p.views || 0), 0);

  const formatPrice = (price: number) => {
    if (price >= 10000000) {
      return `₹${(price / 10000000).toFixed(2)} Cr`;
    } else if (price >= 100000) {
      return `₹${(price / 100000).toFixed(2)} Lakh`;
    }
    return `₹${price.toLocaleString('en-IN')}`;
  };

  return (
    <>
      <style>{pageStyles}</style>
      <div className="container py-5 mt-5">
        <div className="row justify-content-center">
          <div className="col-lg-11">
            
            {/* Back link */}
            <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
              <Link href="/owner/dashboard" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 p-0 owner-back-btn">
                <ArrowLeft size={14} />
                <span>Back to Dashboard</span>
              </Link>
              <Link href="/owner/site-visits" className="btn btn-sm btn-outline-secondary rounded-pill d-inline-flex align-items-center gap-1">
                <CalendarCheck size={14} />
                <span>Site Visits</span>
              </Link>
            </div>

            {/* Header Section */}
            <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
              <div>
                <h1 className="h2 fw-bold text-dark m-0 owner-page-title">My Properties</h1>
                <p className="text-muted small m-0 mt-1">Manage and track performance of your property listings</p>
              </div>
              <Link href="/owner/property/add" className="btn btn-danger rounded-pill px-4 py-2 text-dark fw-bold d-inline-flex align-items-center gap-2 shadow-sm add-property-btn">
                <Plus size={16} />
                <span>Post New Property</span>
              </Link>
            </div>

            {/* Stats Bar */}
            <div className="row g-3 mb-4">
              <div className="col-md-3 col-6">
                <div className="stats-card p-3 rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
                  <div className="stats-icon bg-primary-subtle text-primary p-2.5 rounded-3">
                    <Home size={20} />
                  </div>
                  <div>
                    <div className="stats-label text-muted">Total Properties</div>
                    <div className="stats-val fw-bold text-dark">{totalListings}</div>
                  </div>
                </div>
              </div>
              <div className="col-md-3 col-6">
                <div className="stats-card p-3 rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
                  <div className="stats-icon bg-success-subtle text-success p-2.5 rounded-3">
                    <CheckCircle size={20} />
                  </div>
                  <div>
                    <div className="stats-label text-muted">Active Listings</div>
                    <div className="stats-val fw-bold text-dark">{activeListings}</div>
                  </div>
                </div>
              </div>
              <div className="col-md-3 col-6">
                <div className="stats-card p-3 rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
                  <div className="stats-icon bg-warning-subtle text-warning p-2.5 rounded-3">
                    <Clock size={20} />
                  </div>
                  <div>
                    <div className="stats-label text-muted">Pending Approval</div>
                    <div className="stats-val fw-bold text-dark">{pendingListings}</div>
                  </div>
                </div>
              </div>
              <div className="col-md-3 col-6">
                <div className="stats-card p-3 rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
                  <div className="stats-icon bg-info-subtle text-info p-2.5 rounded-3">
                    <BarChart2 size={20} />
                  </div>
                  <div>
                    <div className="stats-label text-muted">Accumulated Views</div>
                    <div className="stats-val fw-bold text-dark">{totalViews}</div>
                  </div>
                </div>
              </div>
            </div>

            {/* Listings Container */}
            <div className="card border-0 shadow-sm bg-white p-4 rounded-3">
              <h2 className="h5 fw-bold text-dark mb-4 border-bottom pb-2">Properties List</h2>
              
              {listings.length === 0 ? (
                <div className="text-center py-5 text-muted d-flex flex-column align-items-center justify-content-center">
                  <div className="bg-light p-4 rounded-circle mb-3">
                    <Home size={48} className="text-muted" />
                  </div>
                  <h4 className="fw-semibold text-dark">No Properties Posted</h4>
                  <p className="small text-muted mb-4">You have not listed any properties on our platform yet.</p>
                  <Link href="/owner/property/add" className="btn btn-outline-primary rounded-pill px-4">
                    Post Your First Property
                  </Link>
                </div>
              ) : (
                <div className="property-list-wrapper d-flex flex-column gap-3">
                  {listings.map((p) => {
                    const isPublished = Number(p.is_active) === 1;
                    const mainImage =
                      (p.image_urls && p.image_urls.length > 0 && p.image_urls[0]) ||
                      (p.images && p.images.length > 0 && `/${p.images[0].replace(/^\//, '')}`) ||
                      p.thumbnail_url ||
                      p.location_image_url ||
                      '/images/property-placeholder.jpg';

                    return (
                      <div key={p.id} className="property-item-card p-3 rounded-3 border bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 transition">
                        
                        {/* Left Side: Thumbnail & Title Info */}
                        <div className="d-flex align-items-center gap-3 flex-grow-1">
                          <div className="property-thumb-container position-relative overflow-hidden rounded-3 border shadow-sm flex-shrink-0" style={{ width: '100px', height: '75px' }}>
                            <img 
                              src={mainImage} 
                              alt={p.title} 
                              className="property-thumb-img w-100 h-100 object-fit-cover"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = 'https://placehold.co/100x75/f3f4f6/9ca3af?text=No+Photo';
                              }}
                            />
                          </div>
                          <div className="property-info">
                            <h3 className="h6 fw-bold text-dark mb-1 property-card-title text-truncate" style={{ maxWidth: '300px' }} title={p.title}>
                              {p.title}
                            </h3>
                            <div className="d-flex flex-wrap align-items-center gap-x-3 gap-y-1 text-muted small">
                              <span className="d-flex align-items-center gap-1">
                                <MapPin size={12} className="text-danger" />
                                <span>{p.city_name || 'Coimbatore'}</span>
                              </span>
                              <span className="d-flex align-items-center gap-1">
                                <Layers size={12} className="text-primary" />
                                <span className="text-capitalize">{p.property_type.replace('-', ' ')}</span>
                              </span>
                              <span className="badge bg-light text-dark border text-capitalize">{p.listing_type || 'sale'}</span>
                            </div>
                          </div>
                        </div>

                        {/* Middle Side: Price & Status & Views */}
                        <div className="d-flex flex-row flex-md-column justify-content-between align-items-start align-items-md-end gap-2 px-md-3 min-w-150">
                          <div className="fw-bold text-primary fs-5">{formatPrice(p.price)}</div>
                          <div className="d-flex align-items-center gap-2">
                            {isPublished ? (
                              <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 d-flex align-items-center gap-1">
                                <span className="status-dot bg-success"></span>
                                Published
                              </span>
                            ) : (
                              <span className="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill py-1 px-2.5 d-flex align-items-center gap-1">
                                <span className="status-dot bg-warning"></span>
                                Pending Review
                              </span>
                            )}
                            <span className="text-muted small d-flex align-items-center gap-1" title="Views">
                              <Eye size={13} />
                              <span>{p.views || 0}</span>
                            </span>
                          </div>
                        </div>

                        {/* Right Side: Actions */}
                        <div className="d-flex align-items-center gap-2 flex-shrink-0">
                          {isPublished ? (
                            <Link href={`/property/${p.slug}/`} className="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                              <Eye size={13} />
                              <span>View</span>
                            </Link>
                          ) : (
                            <button className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" disabled title="Not published yet">
                              <Clock size={13} />
                              <span>Pending</span>
                            </button>
                          )}
                          <Link href={`/owner/property/edit/${p.id}`} className="btn btn-sm btn-primary text-white rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 edit-listing-btn">
                            <Edit size={13} />
                            <span>Edit</span>
                          </Link>
                        </div>

                      </div>
                    );
                  })}
                </div>
              )}
            </div>

          </div>
        </div>
      </div>
    </>
  );
}

const pageStyles = `
  /* Premium classic colors and styling */
  .owner-back-btn {
    transition: color .2s, transform .2s;
  }
  .owner-back-btn:hover {
    color: var(--bs-primary) !important;
    transform: translateX(-2px);
  }
  
  .owner-page-title {
    font-family: 'Outfit', 'Inter', sans-serif;
    letter-spacing: -0.5px;
  }

  .add-property-btn {
    background: linear-gradient(135deg, var(--bs-warning) 0%, #ffc107 100%) !important;
    border: none !important;
    transition: transform .2s, box-shadow .2s;
  }
  .add-property-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
  }

  .stats-card {
    border: 1px solid #f0f2f5;
    transition: transform .2s, box-shadow .2s;
  }
  .stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06) !important;
  }
  .stats-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .stats-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .stats-val {
    font-size: 1.25rem;
    line-height: 1.2;
    margin-top: 2px;
  }

  .property-item-card {
    border-color: #eef1f6 !important;
    transition: transform .2s, border-color .2s, box-shadow .2s;
  }
  .property-item-card:hover {
    transform: translateY(-1px);
    border-color: #dbe4f0 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
  }

  .property-thumb-container {
    background: #f8fafc;
  }
  .property-thumb-img {
    transition: transform 0.4s ease;
  }
  .property-item-card:hover .property-thumb-img {
    transform: scale(1.05);
  }

  .property-card-title {
    font-size: 14px;
    transition: color .2s;
  }
  .property-item-card:hover .property-card-title {
    color: var(--bs-primary) !important;
  }

  .status-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
  }

  .edit-listing-btn {
    background: var(--bs-primary) !important;
    border: none !important;
    transition: transform .2s;
  }
  .edit-listing-btn:hover {
    transform: translateY(-1px);
  }

  .min-w-150 {
    min-width: 140px;
  }
`;
