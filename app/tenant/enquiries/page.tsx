'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import api from '@/lib/api';
import { ArrowLeft, MessageSquare, Tag, Calendar } from 'lucide-react';

interface Enquiry {
  id: number;
  property_title: string;
  city_name: string;
  message: string;
  status: string;
  created_at: string;
}

export default function TenantEnquiriesPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [enquiries, setEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && (!user || (user.role !== 'tenant' && user.role !== 'customer'))) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch enquiries
  useEffect(() => {
    if (user) {
      api.get('/api/tenant/enquiries')
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.enquiries)) {
            setEnquiries(res.data.enquiries);
          }
        })
        .catch((err) => console.error('Error fetching enquiries', err))
        .finally(() => setLoading(false));
    }
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
        <div className="col-lg-9">
          <Link href="/tenant/dashboard" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Dashboard</span>
          </Link>

          <h1 className="h3 fw-bold text-dark mb-4">My Sent Enquiries</h1>

          <div className="d-flex flex-column gap-3">
            {enquiries.map((e) => (
              <div key={e.id} className="card border-0 shadow-sm bg-white p-4">
                <div className="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                  <h2 className="h6 fw-bold m-0 d-flex align-items-center gap-1 text-primary">
                    <MessageSquare size={16} />
                    <span>{e.property_title}</span>
                    <span className="badge bg-secondary ms-2 small">{e.city_name}</span>
                  </h2>
                  <span className={`badge rounded-pill py-2 px-3 ${
                    e.status === 'new' ? 'bg-info text-dark' : 'bg-secondary'
                  }`}>
                    {e.status}
                  </span>
                </div>
                <div className="text-secondary small mb-3 p-3 bg-light rounded-3" style={{ whiteSpace: 'pre-line' }}>
                  {e.message}
                </div>
                <div className="d-flex align-items-center gap-3 text-muted small">
                  <span className="d-flex align-items-center gap-1">
                    <Calendar size={12} />
                    {new Date(e.created_at).toLocaleString('en-IN')}
                  </span>
                  <span className="d-flex align-items-center gap-1">
                    <Tag size={12} />
                    Status: <strong>{e.status}</strong>
                  </span>
                </div>
              </div>
            ))}

            {enquiries.length === 0 && (
              <div className="text-center py-5 border bg-white rounded-3 my-2 text-muted">
                <MessageSquare size={36} className="mx-auto mb-2 opacity-50" />
                <p className="mb-0">You have not submitted any enquiries yet.</p>
                <Link href="/search" className="btn btn-sm btn-danger rounded-pill px-4 text-dark mt-3 fw-semibold">
                  Find Properties
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
