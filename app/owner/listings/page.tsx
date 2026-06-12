'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../components/AuthContext';
import api from '../../../lib/api';
import { ArrowLeft, Plus, Eye, Edit, ShieldAlert } from 'lucide-react';

interface Listing {
  id: number;
  title: string;
  slug: string;
  is_active: number;
  city_name: string;
  price: number;
  views: number;
  property_type: string;
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
      api.get('/api/owner/listings')
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

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link href="/owner/dashboard" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          <div className="d-flex justify-content-between align-items-center mb-4">
            <h1 className="h3 fw-bold text-dark m-0">My Listed Properties</h1>
            <Link href="/owner/property/add" className="btn btn-danger btn-sm rounded-pill px-4 text-dark fw-bold d-inline-flex align-items-center gap-1">
              <Plus size={14} />
              <span>Add Property</span>
            </Link>
          </div>

          <div className="card border-0 shadow-sm bg-white p-4">
            <div className="table-responsive">
              <table className="table align-middle table-sm small">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>City</th>
                    <th>Price</th>
                    <th>Views</th>
                    <th className="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {listings.map((p) => {
                    const isPublished = Number(p.is_active) === 1;
                    return (
                      <tr key={p.id}>
                        <td className="fw-semibold text-truncate" style={{ maxWidth: '250px' }}>{p.title}</td>
                        <td>
                          {isPublished ? (
                            <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-3">
                              Published
                            </span>
                          ) : (
                            <span className="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill py-1 px-3">
                              Pending admin
                            </span>
                          )}
                        </td>
                        <td>{p.city_name}</td>
                        <td className="fw-bold">₹{p.price.toLocaleString('en-IN')}</td>
                        <td>{p.views}</td>
                        <td className="text-end text-nowrap">
                          {isPublished ? (
                            <Link href={`/property-detail/${p.slug}`} className="btn btn-sm btn-outline-primary rounded-pill me-2 px-3">
                              View
                            </Link>
                          ) : (
                            <button className="btn btn-sm btn-outline-secondary rounded-pill me-2 px-3" disabled title="Not published yet">
                              Pending
                            </button>
                          )}
                          <Link href={`/owner/property/edit/${p.id}`} className="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Edit
                          </Link>
                        </td>
                      </tr>
                    );
                  })}
                  {listings.length === 0 && (
                    <tr>
                      <td colSpan={6} className="text-muted text-center py-4">
                        You have not posted any properties yet.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
