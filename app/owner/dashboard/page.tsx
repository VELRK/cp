'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getOwnerDashboard } from '@/lib/frontendApi';
import { Home, Eye, MessageSquare, Plus, FileText, ClipboardList, CheckCircle, CalendarCheck } from 'lucide-react';

interface Enquiry {
  id: number;
  property_title: string;
  tenant_name: string;
  created_at: string;
  status: string;
}

interface Stats {
  total_listings: number;
  active_listings: number;
  total_views: number;
  enquiry_count: number;
}

export default function OwnerDashboard() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [stats, setStats] = useState<Stats>({ total_listings: 0, active_listings: 0, total_views: 0, enquiry_count: 0 });
  const [recentEnquiries, setRecentEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);

  // Auth routing check
  useEffect(() => {
    if (!authLoading && (!user || (user.role !== 'owner' && user.role !== 'agent'))) {
      router.push('/login?redirect=/owner/dashboard');
    }
  }, [user, authLoading, router]);

  // Fetch stats and enquiries
  useEffect(() => {
    if (user) {
      getOwnerDashboard()
        .then((res) => {
          if (res.data?.success) {
            setStats(res.data.stats);
            setRecentEnquiries(res.data.recent_enquiries || []);
          }
        })
        .catch((err) => console.error('Error fetching owner stats:', err))
        .finally(() => setLoading(false));
    }
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading owner dashboard...</span>
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
              <h1 className="h3 fw-bold text-dark m-0">Owner Dashboard</h1>
              <p className="text-muted small m-0">Welcome back, {user?.name}</p>
            </div>
            <div className="badge bg-success py-2 px-3 rounded-pill">
              Approved Seller / Owner
            </div>
          </div>

          {/* Stats Widgets */}
          <div className="row g-3 mb-4">
            <div className="col-6 col-md-3">
              <div className="card border-0 shadow-sm p-3 bg-white text-center">
                <Home className="mx-auto text-primary mb-1" size={20} />
                <div className="h4 mb-0 fw-bold">{stats.total_listings}</div>
                <small className="text-muted">Total Properties</small>
              </div>
            </div>
            <div className="col-6 col-md-3">
              <div className="card border-0 shadow-sm p-3 bg-white text-center">
                <CheckCircle className="mx-auto text-success mb-1" size={20} />
                <div className="h4 mb-0 fw-bold">{stats.active_listings}</div>
                <small className="text-muted">Active Listings</small>
              </div>
            </div>
            <div className="col-6 col-md-3">
              <div className="card border-0 shadow-sm p-3 bg-white text-center">
                <Eye className="mx-auto text-info mb-1" size={20} />
                <div className="h4 mb-0 fw-bold">{stats.total_views}</div>
                <small className="text-muted">Total Views</small>
              </div>
            </div>
            <div className="col-6 col-md-3">
              <div className="card border-0 shadow-sm p-3 bg-white text-center">
                <MessageSquare className="mx-auto text-warning mb-1" size={20} />
                <div className="h4 mb-0 fw-bold">{stats.enquiry_count}</div>
                <small className="text-muted">Total Enquiries</small>
              </div>
            </div>
          </div>

          {/* Quick Actions */}
          <p className="d-flex gap-2 mb-5">
            <Link href="/owner/property/add" className="btn btn-danger rounded-pill px-4 text-dark fw-bold d-inline-flex align-items-center gap-1">
              <Plus size={16} />
              <span>Add Property</span>
            </Link>
            <Link href="/owner/listings" className="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center gap-1">
              <ClipboardList size={16} />
              <span>My Listings</span>
            </Link>
            <Link href="/owner/enquiries" className="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center gap-1">
              <FileText size={16} />
              <span>Received Enquiries</span>
            </Link>
            <Link href="/owner/site-visits" className="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center gap-1">
              <CalendarCheck size={16} />
              <span>Site Visits</span>
            </Link>
          </p>

          {/* Recent enquiries */}
          <div className="card border-0 shadow-sm bg-white p-4">
            <h2 className="h6 fw-bold mb-4 d-flex align-items-center gap-2">
              <MessageSquare size={16} className="text-primary" />
              <span>Recent Enquiries on Your Properties</span>
            </h2>

            <div className="table-responsive">
              <table className="table align-middle table-sm small">
                <thead>
                  <tr>
                    <th>Property Title</th>
                    <th>From Tenant</th>
                    <th>Date Received</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {recentEnquiries.map((e) => (
                    <tr key={e.id}>
                      <td className="fw-bold">{e.property_title}</td>
                      <td>{e.tenant_name}</td>
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
                  {recentEnquiries.length === 0 && (
                    <tr>
                      <td colSpan={4} className="text-muted text-center py-4">
                        No enquiries received yet.
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
