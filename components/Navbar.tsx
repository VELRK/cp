'use client';

import React from 'react';
import Link from 'next/link';
import { useAuth } from './AuthContext';
import { Home, Search, Heart, User, LogOut, Shield, Menu } from 'lucide-react';

const Navbar: React.FC = () => {
  const { user, logout, setAuthModalOpen } = useAuth();

  return (
    <nav className="navbar navbar-expand-lg fixed-top navbar-light bg-white border-bottom shadow-sm" id="nbNavbar">
      <div className="container">
        <Link href="/" className="navbar-brand fw-bold nb-brand d-flex align-items-center" style={{ color: 'var(--nb-primary)' }}>
          <Home className="me-2 text-warning" size={24} fill="var(--nb-accent)" />
          <span>Coimbatore Properties</span>
        </Link>
        <button
          className="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#nbNav"
          aria-controls="nbNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <Menu size={20} />
        </button>
        <div className="collapse navbar-collapse" id="nbNav">
          <ul className="navbar-nav me-auto mb-2 mb-lg-0">
            <li className="nav-item">
              <Link href="/search" className="nav-link nb-nav-link">Search</Link>
            </li>
            {user && user.role === 'owner' && user.status === 'approved' && (
              <>
                <li className="nav-item">
                  <Link href="/owner/dashboard" className="nav-link nb-nav-link">Owner Dashboard</Link>
                </li>
                <li className="nav-item">
                  <Link href="/owner/enquiries" className="nav-link nb-nav-link">Received Enquiries</Link>
                </li>
                <li className="nav-item">
                  <Link href="/tenant/enquiries" className="nav-link nb-nav-link">Sent Enquiries</Link>
                </li>
              </>
            )}
            {user && user.role === 'tenant' && user.status === 'approved' && (
              <>
                <li className="nav-item">
                  <Link href="/tenant/dashboard" className="nav-link nb-nav-link">Tenant Dashboard</Link>
                </li>
                <li className="nav-item">
                  <Link href="/user/wishlist" className="nav-link nb-nav-link">My Wishlist</Link>
                </li>
              </>
            )}
            {user && user.status === 'approved' && (
              <>
                <li className="nav-item">
                  <Link href="/user/live-updates" className="nav-link nb-nav-link">Live Updates</Link>
                </li>
                <li className="nav-item">
                  <Link href="/user/feedback" className="nav-link nb-nav-link">Feedback</Link>
                </li>
              </>
            )}
            {user && user.role === 'admin' && (
              <li className="nav-item">
                <a href="/panel" className="nav-link nb-nav-link text-danger fw-bold">Admin Panel</a>
              </li>
            )}
          </ul>
          <div className="d-flex gap-2 align-items-center flex-wrap">
            {user ? (
              <div className="dropdown">
                <button
                  className="btn btn-sm btn-light border-0 d-flex align-items-center gap-2 dropdown-toggle rounded-pill px-3 py-1.5"
                  type="button"
                  id="userDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style={{ width: '24px', height: '24px' }}>
                    <User size={14} />
                  </div>
                  <span className="fw-semibold text-dark">{user.name}</span>
                </button>
                <ul className="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown" style={{ minWidth: '240px', borderRadius: '12px', marginTop: '10px' }}>
                  <li className="px-3 py-2 mb-1">
                    <div className="fw-bold text-dark fs-6">{user.name}</div>
                  </li>

                  <li>
                    <Link href="/owner/property/add" className="dropdown-item d-flex justify-content-between align-items-center py-2 fw-semibold text-primary">
                      <span>Post Property</span>
                      <span className="badge bg-success rounded-1">FREE</span>
                    </Link>
                  </li>

                  {user.role === 'owner' && (
                    <>
                      <li><h6 className="dropdown-header text-dark fw-bold mt-2">Owner Plans <span className="text-danger ms-1" style={{ fontSize: '8px', verticalAlign: 'middle' }}>●</span></h6></li>
                      <li><Link href="/owner/dashboard" className="dropdown-item py-2">Manage Listings</Link></li>
                      <li><Link href="/owner/enquiries" className="dropdown-item py-2">View Responses</Link></li>
                    </>
                  )}

                  <li><Link href="/user/profile" className="dropdown-item py-2">Manage Profile</Link></li>
                  <li><Link href="/user/settings" className="dropdown-item py-2">Change Password</Link></li>
                  <li><button className="dropdown-item py-2 text-dark" onClick={logout}>Logout</button></li>

                  <li><hr className="dropdown-divider my-2" /></li>

                  <li><h6 className="dropdown-header text-dark fw-bold">My Activity</h6></li>
                  <li><Link href="/search" className="dropdown-item py-2">Recent Searches</Link></li>
                  <li><Link href="/tenant/enquiries" className="dropdown-item py-2">Contacted properties</Link></li>
                  <li><Link href="/user/wishlist" className="dropdown-item py-2">Shortlisted properties</Link></li>
                  <li><Link href="/user/viewed" className="dropdown-item py-2">Viewed properties</Link></li>
                </ul>
              </div>
            ) : (
              <>
                <button
                  className="btn btn-sm btn-outline-primary fw-semibold px-3"
                  onClick={() => setAuthModalOpen('login')}
                >
                  Login
                </button>
                <button
                  className="btn btn-sm btn-danger fw-semibold px-3"
                  onClick={() => setAuthModalOpen('register')}
                >
                  Register Free
                </button>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
