'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { getOwnerDashboard } from '@/lib/frontendApi';
import {
  Home,
  Eye,
  MessageSquare,
  Plus,
  FileText,
  ClipboardList,
  CheckCircle,
  CalendarCheck,
  ShieldCheck,
  ArrowRight,
  TrendingUp,
  Sparkles,
  Search,
  User,
  Phone,
  Mail,
  HelpCircle,
  Clock,
  ChevronRight
} from 'lucide-react';

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
  const [stats, setStats] = useState<Stats>({
    total_listings: 0,
    active_listings: 0,
    total_views: 0,
    enquiry_count: 0
  });
  const [recentEnquiries, setRecentEnquiries] = useState<Enquiry[]>([]);
  const [loading, setLoading] = useState(true);

  // Set page document title to "Dashboard"
  useEffect(() => {
    document.title = 'Dashboard | Coimbatore Properties';
  }, []);

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
            setStats(res.data.stats || { total_listings: 0, active_listings: 0, total_views: 0, enquiry_count: 0 });
            setRecentEnquiries(res.data.recent_enquiries || []);
          }
        })
        .catch((err) => console.error('Error fetching owner stats:', err))
        .finally(() => setLoading(false));
    }
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="container py-5 my-5 text-center">
        <div className="spinner-border text-primary" role="status" style={{ width: '3rem', height: '3rem' }}>
          <span className="visually-hidden">Loading dashboard...</span>
        </div>
        <p className="text-muted mt-3 fw-medium">Loading your dashboard...</p>
      </div>
    );
  }

  return (
    <>
      <style>{dashboardStyles}</style>

      <div className="classic-dashboard-wrapper py-4 py-md-5">
        <div className="container">

          {/* Breadcrumb Navigation */}
          <nav aria-label="breadcrumb" className="mb-3">
            <ol className="breadcrumb classic-breadcrumb mb-0">
              <li className="breadcrumb-item">
                <Link href="/" className="text-decoration-none">Home</Link>
              </li>
              <li className="breadcrumb-item active" aria-current="page">
                Dashboard
              </li>
            </ol>
          </nav>

          {/* Dashboard Header / Welcome Card */}
          <div className="classic-header-card p-4 p-md-4 rounded-4 shadow-sm mb-4">
            <div className="row align-items-center g-3">
              <div className="col-lg-8">
                <div className="d-flex flex-wrap align-items-center gap-2 mb-2">
                  <h1 className="classic-title m-0">Dashboard</h1>
                  <span className="badge classic-role-badge d-inline-flex align-items-center gap-1">
                    <ShieldCheck size={14} className="text-success" />
                    <span>Approved Seller / Owner</span>
                  </span>
                </div>
                <p className="text-muted mb-0 classic-subtitle">
                  Welcome back, <strong className="text-dark">{user?.name || 'Partner'}</strong>! Monitor your property listings, buyer leads, and performance in real time.
                </p>
              </div>

              <div className="col-lg-4 d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                <Link
                  href="/owner/property/add"
                  className="btn btn-primary classic-btn-primary rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm text-decoration-none"
                >
                  <Plus size={18} />
                  <span>Post Property</span>
                  <span className="badge bg-success text-white rounded-pill px-2 py-0.5 font-monospace" style={{ fontSize: '0.65rem' }}>FREE</span>
                </Link>
              </div>
            </div>
          </div>

          {/* KPI Stat Cards (4 Classic Cards) */}
          <div className="row g-3 g-xl-4 mb-4">
            {/* Total Properties */}
            <div className="col-6 col-lg-3">
              <Link href="/owner/listings" className="text-decoration-none text-reset d-block h-100">
                <div className="classic-stat-card p-3 p-md-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                  <div className="d-flex align-items-center justify-content-between mb-3">
                    <span className="classic-stat-label">Total Properties</span>
                    <div className="classic-icon-box bg-blue-subtle text-primary">
                      <Home size={22} />
                    </div>
                  </div>
                  <div>
                    <div className="classic-stat-val text-dark fw-extrabold">{stats.total_listings}</div>
                    <div className="classic-stat-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                      <span className="text-muted small">Properties listed</span>
                      <span className="classic-arrow-link text-primary small fw-semibold d-flex align-items-center gap-1">
                        View <ChevronRight size={14} />
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </div>

            {/* Active Listings */}
            <div className="col-6 col-lg-3">
              <Link href="/owner/listings" className="text-decoration-none text-reset d-block h-100">
                <div className="classic-stat-card p-3 p-md-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                  <div className="d-flex align-items-center justify-content-between mb-3">
                    <span className="classic-stat-label">Active Listings</span>
                    <div className="classic-icon-box bg-green-subtle text-success">
                      <CheckCircle size={22} />
                    </div>
                  </div>
                  <div>
                    <div className="classic-stat-val text-success fw-extrabold">{stats.active_listings}</div>
                    <div className="classic-stat-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                      <span className="text-muted small">Live on portal</span>
                      <span className="classic-arrow-link text-success small fw-semibold d-flex align-items-center gap-1">
                        Manage <ChevronRight size={14} />
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </div>

            {/* Total Views */}
            <div className="col-6 col-lg-3">
              <div className="classic-stat-card p-3 p-md-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                <div className="d-flex align-items-center justify-content-between mb-3">
                  <span className="classic-stat-label">Total Views</span>
                  <div className="classic-icon-box bg-info-subtle text-info">
                    <Eye size={22} />
                  </div>
                </div>
                <div>
                  <div className="classic-stat-val text-dark fw-extrabold">
                    {stats.total_views.toLocaleString('en-IN')}
                  </div>
                  <div className="classic-stat-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                    <span className="text-muted small">Buyer impressions</span>
                    <span className="text-info small fw-semibold d-flex align-items-center gap-1">
                      <TrendingUp size={14} />
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Total Enquiries */}
            <div className="col-6 col-lg-3">
              <Link href="/owner/enquiries" className="text-decoration-none text-reset d-block h-100">
                <div className="classic-stat-card p-3 p-md-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                  <div className="d-flex align-items-center justify-content-between mb-3">
                    <span className="classic-stat-label">Total Enquiries</span>
                    <div className="classic-icon-box bg-amber-subtle text-warning">
                      <MessageSquare size={22} />
                    </div>
                  </div>
                  <div>
                    <div className="classic-stat-val text-warning-emphasis fw-extrabold">{stats.enquiry_count}</div>
                    <div className="classic-stat-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                      <span className="text-muted small">Direct buyer leads</span>
                      <span className="classic-arrow-link text-warning-emphasis small fw-semibold d-flex align-items-center gap-1">
                        Respond <ChevronRight size={14} />
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </div>

          {/* Quick Actions Navigation Bar */}
          <div className="classic-quick-actions-bar p-3 p-md-3 rounded-4 shadow-sm mb-4 bg-white">
            <div className="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <span className="fw-bold text-dark small text-uppercase tracking-wider d-flex align-items-center gap-1.5 px-2">
                <Sparkles size={16} className="text-primary" />
                <span>Quick Actions</span>
              </span>

              <div className="d-flex flex-wrap gap-2">
                <Link
                  href="/owner/property/add"
                  className="btn btn-sm classic-action-pill classic-action-pill--primary d-inline-flex align-items-center gap-1.5"
                >
                  <Plus size={15} />
                  <span>Add Property</span>
                </Link>

                <Link
                  href="/owner/listings"
                  className="btn btn-sm classic-action-pill d-inline-flex align-items-center gap-1.5"
                >
                  <ClipboardList size={15} />
                  <span>My Listings</span>
                  {stats.total_listings > 0 && (
                    <span className="badge bg-light text-dark rounded-pill ms-1">{stats.total_listings}</span>
                  )}
                </Link>

                <Link
                  href="/owner/enquiries"
                  className="btn btn-sm classic-action-pill d-inline-flex align-items-center gap-1.5"
                >
                  <MessageSquare size={15} />
                  <span>Received Enquiries</span>
                  {stats.enquiry_count > 0 && (
                    <span className="badge bg-warning text-dark rounded-pill ms-1">{stats.enquiry_count}</span>
                  )}
                </Link>

                <Link
                  href="/search"
                  className="btn btn-sm classic-action-pill d-inline-flex align-items-center gap-1.5"
                >
                  <Search size={15} />
                  <span>Explore Search</span>
                </Link>
              </div>
            </div>
          </div>

          {/* Main Dashboard Grid: Left Table (Col 8) & Right Sidebar (Col 4) */}
          <div className="row g-4">
            
            {/* Left Column: Recent Enquiries */}
            <div className="col-lg-8">
              <div className="classic-panel-card rounded-4 shadow-sm bg-white overflow-hidden h-100">
                {/* Header */}
                <div className="p-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div className="d-flex align-items-center gap-2">
                    <div className="classic-icon-box-sm bg-primary-subtle text-primary">
                      <MessageSquare size={17} />
                    </div>
                    <div>
                      <h2 className="classic-panel-title m-0">Recent Enquiries on Your Properties</h2>
                      <span className="text-muted small">Potential buyers and tenants who inquired</span>
                    </div>
                  </div>

                  <Link
                    href="/owner/enquiries"
                    className="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 text-decoration-none fw-semibold"
                  >
                    <span>View All Enquiries</span>
                    <ArrowRight size={14} />
                  </Link>
                </div>

                {/* Table or Empty State */}
                {recentEnquiries.length > 0 ? (
                  <div className="table-responsive">
                    <table className="table align-middle classic-table mb-0">
                      <thead>
                        <tr>
                          <th className="ps-4">Property Title</th>
                          <th>From Tenant</th>
                          <th>Date Received</th>
                          <th>Status</th>
                          <th className="text-end pe-4">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        {recentEnquiries.map((e) => (
                          <tr key={e.id} className="classic-table-row">
                            <td className="ps-4">
                              <div className="fw-semibold text-dark text-truncate" style={{ maxWidth: '220px' }} title={e.property_title}>
                                {e.property_title}
                              </div>
                            </td>
                            <td>
                              <span className="text-secondary fw-medium">{e.tenant_name || 'Guest User'}</span>
                            </td>
                            <td className="text-muted small">
                              {new Date(e.created_at).toLocaleDateString('en-IN', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric'
                              })}
                            </td>
                            <td>
                              <span className={`badge classic-status-badge ${
                                e.status === 'new'
                                  ? 'bg-warning-subtle text-warning-emphasis border border-warning'
                                  : e.status === 'responded'
                                  ? 'bg-success-subtle text-success-emphasis border border-success'
                                  : 'bg-secondary-subtle text-secondary border'
                              }`}>
                                {e.status}
                              </span>
                            </td>
                            <td className="text-end pe-4">
                              <Link
                                href="/owner/enquiries"
                                className="btn btn-sm btn-light border rounded-pill px-3 py-1 text-decoration-none fw-semibold small text-primary"
                              >
                                View Details
                              </Link>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                ) : (
                  <div className="text-center py-5 px-4 my-2">
                    <div className="classic-empty-icon mx-auto mb-3">
                      <MessageSquare size={34} className="text-muted" />
                    </div>
                    <h3 className="h6 fw-bold text-dark mb-2">No enquiries received yet</h3>
                    <p className="text-muted small mx-auto mb-4" style={{ maxWidth: '420px' }}>
                      Once prospective buyers or tenants contact you regarding your properties, their requests and contact information will appear here.
                    </p>
                    <div className="d-flex justify-content-center gap-2 flex-wrap">
                      <Link
                        href="/owner/property/add"
                        className="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5"
                      >
                        <Plus size={16} />
                        <span>Post a Property</span>
                      </Link>
                      <Link
                        href="/owner/listings"
                        className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5"
                      >
                        <ClipboardList size={16} />
                        <span>View My Listings</span>
                      </Link>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Right Column: Helpful Shortcuts & Tips */}
            <div className="col-lg-4">
              <div className="d-flex flex-column gap-4">

                {/* Property Management Shortcuts */}
                <div className="classic-panel-card rounded-4 shadow-sm bg-white p-4">
                  <h2 className="classic-panel-title mb-3 d-flex align-items-center gap-2">
                    <ClipboardList size={18} className="text-primary" />
                    <span>Manage Portal</span>
                  </h2>

                  <div className="d-flex flex-column gap-2.5">
                    <Link
                      href="/owner/property/add"
                      className="classic-shortcut-item p-3 rounded-3 d-flex align-items-center justify-content-between text-decoration-none"
                    >
                      <div className="d-flex align-items-center gap-3">
                        <div className="classic-icon-box-sm bg-primary-subtle text-primary">
                          <Plus size={16} />
                        </div>
                        <div>
                          <div className="fw-semibold text-dark small">Post New Property</div>
                          <div className="text-muted" style={{ fontSize: '0.75rem' }}>100% Free • Zero Brokerage</div>
                        </div>
                      </div>
                      <ChevronRight size={16} className="text-muted" />
                    </Link>

                    <Link
                      href="/owner/listings"
                      className="classic-shortcut-item p-3 rounded-3 d-flex align-items-center justify-content-between text-decoration-none"
                    >
                      <div className="d-flex align-items-center gap-3">
                        <div className="classic-icon-box-sm bg-success-subtle text-success">
                          <Home size={16} />
                        </div>
                        <div>
                          <div className="fw-semibold text-dark small">Manage Listings</div>
                          <div className="text-muted" style={{ fontSize: '0.75rem' }}>Edit photos, prices, & amenities</div>
                        </div>
                      </div>
                      <ChevronRight size={16} className="text-muted" />
                    </Link>
                  </div>
                </div>

                {/* Seller Best Practices Tips */}
                <div className="classic-panel-card rounded-4 shadow-sm bg-white p-4">
                  <h2 className="classic-panel-title mb-3 d-flex align-items-center gap-2">
                    <Sparkles size={18} className="text-warning" />
                    <span>Tips for 3x Faster Leads</span>
                  </h2>

                  <ul className="list-unstyled mb-0 d-flex flex-column gap-3">
                    <li className="d-flex align-items-start gap-2.5">
                      <div className="classic-tip-num text-primary fw-bold">1</div>
                      <div className="small">
                        <strong className="text-dark d-block">Add 4+ Real Photos</strong>
                        <span className="text-muted">Properties with bright, clear photos receive significantly more views and genuine enquiries.</span>
                      </div>
                    </li>
                    <li className="d-flex align-items-start gap-2.5">
                      <div className="classic-tip-num text-primary fw-bold">2</div>
                      <div className="small">
                        <strong className="text-dark d-block">Set Competitive Pricing</strong>
                        <span className="text-muted">Compare recent market prices in your locality for quick buyer interest and faster deals.</span>
                      </div>
                    </li>
                    <li className="d-flex align-items-start gap-2.5">
                      <div className="classic-tip-num text-primary fw-bold">3</div>
                      <div className="small">
                        <strong className="text-dark d-block">Respond Promptly</strong>
                        <span className="text-muted">Tenants appreciate quick callbacks. Prompt replies increase deal closing rate by 70%.</span>
                      </div>
                    </li>
                  </ul>
                </div>

                {/* Need Help Support Card */}
                <div className="classic-support-card p-4 rounded-4 shadow-sm">
                  <div className="d-flex align-items-center gap-2 mb-2">
                    <HelpCircle size={20} className="text-primary" />
                    <h3 className="h6 fw-bold text-dark m-0">Need Assistance?</h3>
                  </div>
                  <p className="text-muted small mb-3">
                    Our dedicated property support team is here to assist you with posting, verification, and buyer inquiries.
                  </p>
                  <div className="d-flex flex-column gap-2 small">
                    <div className="d-flex align-items-center gap-2 text-dark fw-semibold">
                      <Phone size={15} className="text-primary" />
                      <span>Toll Free: 1800 41 99099</span>
                    </div>
                    <div className="d-flex align-items-center gap-2 text-dark">
                      <Mail size={15} className="text-primary" />
                      <a href="mailto:support@coimbatoreproperties.com" className="text-decoration-none text-muted text-truncate">
                        support@coimbatoreproperties.com
                      </a>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>

        </div>
      </div>
    </>
  );
}

const dashboardStyles = `
  .classic-dashboard-wrapper {
    background-color: #f8fafc;
    min-height: 85vh;
  }

  /* Breadcrumbs */
  .classic-breadcrumb {
    font-size: 0.82rem;
    font-weight: 500;
  }
  .classic-breadcrumb .breadcrumb-item a {
    color: var(--nb-muted, #64748b);
  }
  .classic-breadcrumb .breadcrumb-item.active {
    color: var(--nb-primary, #0b2c56);
    font-weight: 600;
  }

  /* Header Card */
  .classic-header-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
  }
  .classic-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--nb-primary, #0b2c56);
    letter-spacing: -0.02em;
  }
  .classic-subtitle {
    font-size: 0.92rem;
    line-height: 1.5;
  }
  .classic-role-badge {
    background-color: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    font-weight: 600;
    font-size: 0.78rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
  }
  .classic-btn-primary {
    background: linear-gradient(135deg, var(--nb-primary, #0b2c56) 0%, var(--nb-primary-dark, #071f3f) 100%) !important;
    border: none !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .classic-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(11, 44, 86, 0.25) !important;
  }

  /* Stat Cards */
  .classic-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .classic-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(11, 44, 86, 0.08) !important;
    border-color: #cbd5e1;
  }
  .classic-stat-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  .classic-stat-val {
    font-size: 2rem;
    line-height: 1.15;
    letter-spacing: -0.02em;
  }
  .classic-stat-footer {
    border-color: #f1f5f9 !important;
  }
  .classic-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .classic-icon-box-sm {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .bg-blue-subtle { background-color: #eef2ff !important; }
  .bg-green-subtle { background-color: #ecfdf5 !important; }
  .bg-info-subtle { background-color: #f0f9ff !important; }
  .bg-amber-subtle { background-color: #fffbeb !important; }

  /* Quick Actions Bar */
  .classic-quick-actions-bar {
    border: 1px solid #e2e8f0;
  }
  .classic-action-pill {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-weight: 600;
    font-size: 0.84rem;
    padding: 0.45rem 1rem;
    border-radius: 9999px;
    transition: all 0.2s ease;
    text-decoration: none;
  }
  .classic-action-pill:hover {
    background: #f1f5f9;
    color: var(--nb-primary, #0b2c56);
    border-color: #94a3b8;
  }
  .classic-action-pill--primary {
    background: var(--nb-primary, #0b2c56);
    color: #ffffff !important;
    border-color: var(--nb-primary, #0b2c56);
  }
  .classic-action-pill--primary:hover {
    background: var(--nb-primary-dark, #071f3f) !important;
    color: #ffffff !important;
  }

  /* Panel Cards */
  .classic-panel-card {
    border: 1px solid #e2e8f0;
  }
  .classic-panel-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--nb-primary, #0b2c56);
  }

  /* Table */
  .classic-table {
    font-size: 0.86rem;
  }
  .classic-table thead th {
    background-color: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
  }
  .classic-table-row {
    transition: background-color 0.15s ease;
  }
  .classic-table-row:hover {
    background-color: #f8fafc;
  }
  .classic-table td {
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .classic-status-badge {
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: capitalize;
    padding: 0.3rem 0.6rem;
    border-radius: 9999px;
  }

  /* Empty State */
  .classic-empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Shortcuts */
  .classic-shortcut-item {
    background: #f8fafc;
    border: 1px solid #eef2f6;
    transition: all 0.2s ease;
  }
  .classic-shortcut-item:hover {
    background: #eef3fb;
    border-color: #cbd5e1;
    transform: translateX(3px);
  }

  /* Tips */
  .classic-tip-num {
    width: 24px;
    height: 24px;
    background: #eef2ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
  }

  /* Support Card */
  .classic-support-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
  }
`;
