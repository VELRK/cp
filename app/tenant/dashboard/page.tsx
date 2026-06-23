'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import { getTenantEnquiries } from '@/lib/frontendApi';
import { Search, Mail, MessageSquare, ClipboardList, CheckCircle2 } from 'lucide-react';

interface Enquiry {
  id: number;
  property_title: string;
  city_name: string;
  message: string;
  status: string;
  created_at: string;
}

export default function TenantDashboard() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [enquiries, setEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);

  // Protected route check
  useEffect(() => {
    if (!authLoading && (!user || (user.role !== 'tenant' && user.role !== 'customer'))) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch enquiries
  useEffect(() => {
    if (user) {
      getTenantEnquiries()
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.enquiries)) {
            setEnquiries(res.data.enquiries.slice(0, 5));
          }
        })
        .catch((err) => console.error('Error loading enquiries', err))
        .finally(() => setLoading(false));
    }
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading dashboard...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <div className="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
              <h1 className="h3 fw-bold text-dark m-0">Tenant Dashboard</h1>
              <p className="text-muted small m-0">Welcome back, {user?.name}</p>
            </div>
            <div className="badge bg-success py-2 px-3 rounded-pill">
              Approved Tenant
            </div>
          </div>

          {/* Quick Actions */}
          <div className="row g-3 mb-5">
            <div className="col-md-6">
              <div className="card border-0 shadow-sm p-4 bg-white text-center h-100 d-flex flex-column justify-content-between">
                <div>
                  <Search size={32} className="text-primary mx-auto mb-2" />
                  <h2 className="h5 fw-bold mb-2">Find Your Next Home</h2>
                  <p className="text-muted small">Search active listings in Coimbatore and contact owners directly without brokerage.</p>
                </div>
                <Link href="/search" className="btn btn-danger rounded-pill w-100 py-2 fw-semibold text-dark mt-3">
                  Search Properties
                </Link>
              </div>
            </div>

            <div className="col-md-6">
              <div className="card border-0 shadow-sm p-4 bg-white text-center h-100 d-flex flex-column justify-content-between">
                <div>
                  <ClipboardList size={32} className="text-warning mx-auto mb-2" />
                  <h2 className="h5 fw-bold mb-2">My Enquiries</h2>
                  <p className="text-muted small">View all secure inquiries you have sent to listing owners and track their active statuses.</p>
                </div>
                <Link href="/tenant/enquiries" className="btn btn-outline-secondary rounded-pill w-100 py-2 fw-semibold mt-3">
                  View Sent Enquiries ({enquiries.length})
                </Link>
              </div>
            </div>
          </div>

          {/* Recent enquiries */}
          <div className="card border-0 shadow-sm bg-white p-4">
            <h2 className="h6 fw-bold mb-3 d-flex align-items-center gap-2">
              <MessageSquare size={16} className="text-primary" />
              <span>Recent Enquiries</span>
            </h2>

            <ul className="list-group list-group-flush">
              {enquiries.map((e) => (
                <li key={e.id} className="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h3 className="h6 fw-bold m-0">{e.property_title}</h3>
                    <p className="text-muted small m-0 text-truncate" style={{ maxWidth: '400px' }}>{e.message}</p>
                    <small className="text-muted" style={{ fontSize: '0.75rem' }}>
                      {new Date(e.created_at).toLocaleDateString('en-IN')}
                    </small>
                  </div>
                  <span className={`badge rounded-pill py-2 px-3 ${
                    e.status === 'new' ? 'bg-info text-dark' : 'bg-secondary'
                  }`}>
                    {e.status}
                  </span>
                </li>
              ))}
              {enquiries.length === 0 && (
                <li className="list-group-item px-0 py-3 text-muted text-center">
                  You have not submitted any enquiries yet.
                </li>
              )}
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
