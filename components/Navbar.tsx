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
              <>
                <span className="small text-muted me-2 d-flex align-items-center gap-1">
                  <User size={14} />
                  <strong>{user.name}</strong>
                  <span className="badge rounded-pill nb-role-pill ms-1">{user.role}</span>
                </span>
                <button className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" onClick={logout}>
                  <LogOut size={14} />
                  <span>Logout</span>
                </button>
              </>
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
