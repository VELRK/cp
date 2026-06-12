'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../components/AuthContext';
import api from '../../../lib/api';
import { ArrowLeft, MessageSquare, Calendar } from 'lucide-react';

interface Enquiry {
  id: number;
  property_title: string;
  tenant_name: string;
  message: string;
  status: string;
  created_at: string;
}

export default function OwnerEnquiriesPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [enquiries, setEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && (!user || user.role !== 'owner')) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch enquiries
  useEffect(() => {
    if (user) {
      api.get('/api/owner/enquiries')
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.enquiries)) {
            setEnquiries(res.data.enquiries);
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
          <span className="visually-hidden">Loading enquiries...</span>
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

          <h1 className="h3 fw-bold text-dark mb-2">Enquiries on Your Properties</h1>
          <p className="text-muted small mb-4">
            Contact details and callbacks are handled securely by our administrator. You can view inquiry submissions and active counts here.
          </p>

          <div className="card border-0 shadow-sm bg-white p-4">
            <div className="table-responsive">
              <table className="table align-middle table-sm small">
                <thead>
                  <tr>
                    <th>Property</th>
                    <th>From Tenant</th>
                    <th>Inquiry Message</th>
                    <th>Date Received</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {enquiries.map((e) => (
                    <tr key={e.id}>
                      <td className="fw-bold">{e.property_title}</td>
                      <td>{e.tenant_name}</td>
                      <td className="text-muted" style={{ maxWidth: '300px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {e.message}
                      </td>
                      <td>{new Date(e.created_at).toLocaleDateString('en-IN')}</td>
                      <td>
                        <span className={`badge rounded-pill ${
                          e.status === 'new' ? 'bg-info text-dark' : 'bg-secondary'
                        }`}>
                          {e.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                  {enquiries.length === 0 && (
                    <tr>
                      <td colSpan={5} className="text-muted text-center py-4">
                        No enquiries received yet on your properties.
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
