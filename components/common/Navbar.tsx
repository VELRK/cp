'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/hooks/useAuth';
import { getAdminPanelUrl } from '@/lib/frontendApi';
import MobileSidebar from './MobileSidebar';
import { 
  Home, 
  User, 
  Menu, 
  LogOut, 
  Key, 
  PlusCircle, 
  Bookmark, 
  Compass, 
  MessageSquare, 
  Newspaper, 
  FileText, 
  ChevronDown,
  LayoutGrid,
  Headphones,
  Mail,
  Phone,
  Briefcase
} from 'lucide-react';

const Navbar: React.FC = () => {
  const { user, logout, setAuthModalOpen } = useAuth();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  return (
    <nav className="navbar navbar-expand-lg fixed-top navbar-light navbar-classic-fresh" id="nbNavbar">
      <div className="container-fluid px-xl-5 px-lg-4 px-3 d-flex align-items-center justify-content-between">
        
        {/* Left: Brand logo with official cplogo.png */}
        <Link href="/" className="navbar-brand d-flex align-items-center text-decoration-none py-1 me-0 me-xl-3 flex-shrink-0" style={{ color: 'var(--nb-primary)' }}>
          <img 
            src="/assets/images/logo/cplogo.png" 
            alt="Coimbatore Properties" 
            className="nb-logo-img me-2"
            style={{ height: '48px', width: 'auto', objectFit: 'contain' }}
          />
          <div className="d-none d-sm-flex flex-column leading-tight">
            <span className="nb-brand-text fw-extrabold text-primary" style={{ letterSpacing: '-0.02em', fontSize: '1.25rem', lineHeight: '1.15' }}>
              Coimbatore<span style={{ color: 'var(--nb-accent-dark)' }}>Properties</span>
            </span>
            <span className="text-muted fw-semibold text-uppercase" style={{ fontSize: '0.62rem', letterSpacing: '0.1em' }}>
              Buy • Rent • Sell
            </span>
          </div>
        </Link>

        {/* Center: Desktop horizontal menu links (Perfect alignment) */}
        <div className="collapse navbar-collapse justify-content-center flex-grow-1 mx-xl-3" id="nbNav">
          <ul className="navbar-nav align-items-center gap-1 gap-xl-2 mb-0">
            <li className="nav-item">
              <Link href="/search" className="nav-link nav-link-premium text-nowrap">Search</Link>
            </li>
            <li className="nav-item dropdown">
              <a className="nav-link nav-link-premium text-nowrap dropdown-toggle d-flex align-items-center gap-1" href="#" id="knowledgeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span>Knowledge Centre</span>
              </a>
              <ul className="dropdown-menu dropdown-menu-premium border-0 shadow mt-2" aria-labelledby="knowledgeDropdown">
                <li>
                  <Link href="/knowledge-centre" className="dropdown-item py-2">
                    <Compass size={16} className="text-primary" />
                    <span>Insights Hub</span>
                  </Link>
                </li>
                <li>
                  <Link href="/articles" className="dropdown-item py-2">
                    <FileText size={16} className="text-primary" />
                    <span>Articles</span>
                  </Link>
                </li>
                <li>
                  <Link href="/news" className="dropdown-item py-2">
                    <Newspaper size={16} className="text-primary" />
                    <span>Real Estate News</span>
                  </Link>
                </li>
              </ul>
            </li>
            <li className="nav-item">
              <Link href="/user/become-agent" className="nav-link nav-link-premium text-nowrap d-flex align-items-center gap-1.5">
                <Briefcase size={15} className="text-warning" />
                <span>Become an Agent</span>
                <span className="badge bg-warning-subtle text-dark border border-warning-subtle px-1.5 py-0.5 rounded-pill" style={{ fontSize: '0.62rem' }}>
                  NEW
                </span>
              </Link>
            </li>
            {user && (
              <li className="nav-item">
                <Link href="/owner/listings" className="nav-link nav-link-premium text-nowrap">
                  My Properties
                </Link>
              </li>
            )}
            {user && user.role === 'admin' && (
              <li className="nav-item">
                <a href={getAdminPanelUrl()} className="nav-link nav-link-premium text-nowrap text-danger fw-bold">
                  Admin Panel
                </a>
              </li>
            )}
          </ul>
        </div>

        {/* Right: Unified Action Buttons (Always cleanly aligned on mobile & desktop) */}
        <div className="d-flex align-items-center gap-2 flex-shrink-0 ms-auto ms-lg-0">
          
          {/* Post Property button */}
          {(!user || user.role !== 'tenant') && (
            <Link 
              href={user ? "/owner/property/add" : "/register"} 
              className="btn btn-sm post-property-btn-navbar rounded-pill px-3 py-1.5 fw-semibold d-none d-md-flex align-items-center gap-1.5 shadow-sm text-decoration-none"
            >
              <span>Post Property</span>
              <span className="badge bg-success rounded-1" style={{ fontSize: '0.65rem' }}>FREE</span>
            </Link>
          )}

          {/* Support Dropdown */}
          <div className="dropdown">
            <button 
              className="btn btn-sm btn-link text-decoration-none text-muted p-2 rounded-circle hover-bg-light d-flex align-items-center justify-content-center support-dropdown-btn"
              type="button"
              id="supportDropdown"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              style={{ width: '38px', height: '38px' }}
              title="Customer Support"
            >
              <Headphones size={20} className="text-primary" />
            </button>
            <ul className="dropdown-menu dropdown-menu-end shadow border-0 p-3 mt-2 animate-fade-in" aria-labelledby="supportDropdown" style={{ width: '260px', borderRadius: '12px' }}>
              <li className="fw-bold text-primary mb-2" style={{ fontSize: '0.9rem' }}>Customer Support</li>
              <li className="text-muted small mb-3">Get assistance with listing, searching or general enquiries.</li>
              <li className="d-flex align-items-center gap-2 py-1.5 small border-bottom">
                <Mail size={14} className="text-primary" />
                <a href="mailto:support@coimbatoreproperties.com" className="text-decoration-none text-dark fw-semibold">support@coimbatoreproperties.com</a>
              </li>
              <li className="d-flex align-items-center gap-2 py-1.5 small">
                <Phone size={14} className="text-primary" />
                <span className="fw-semibold">Toll Free: 1800 41 99099</span>
              </li>
            </ul>
          </div>

          {/* User Profile / Login Dropdown */}
          {user ? (
            <div className="dropdown">
              <button
                className="btn btn-sm user-dropdown-btn-premium d-flex align-items-center gap-2 dropdown-toggle rounded-pill px-3 py-2 shadow-sm"
                type="button"
                id="userDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style={{ width: '26px', height: '26px', background: 'linear-gradient(135deg, var(--nb-primary) 0%, var(--nb-primary-dark) 100%) !important' }}>
                  <User size={13} />
                </div>
                <span className="fw-semibold text-primary d-none d-sm-inline">{user.name}</span>
                <ChevronDown size={14} className="text-muted" />
              </button>
              <ul className="dropdown-menu dropdown-menu-end dropdown-menu-premium shadow border-0 animate-fade-in mt-2" aria-labelledby="userDropdown">
                <li className="px-3 py-2 mb-1 bg-light border-bottom d-flex align-items-center justify-content-between">
                  <span className="fw-bold text-dark fs-6 text-truncate" style={{ maxWidth: '150px' }}>{user.name}</span>
                  <span className="badge nb-role-pill text-uppercase">{user.role}</span>
                </li>

                {(!user || user.role !== 'tenant') && (
                  <li>
                    <Link href="/owner/property/add" className="dropdown-item d-flex justify-content-between align-items-center py-2 fw-semibold post-property-free-btn-dropdown">
                      <div className="d-flex align-items-center gap-2">
                        <PlusCircle size={16} />
                        <span>Post Property</span>
                      </div>
                      <span className="badge bg-success rounded-1">FREE</span>
                    </Link>
                  </li>
                )}

                {/* Become an Agent / Agent Profile Link */}
                {user.role !== 'agent' && user.user_type !== 'agent' ? (
                  <li>
                    <Link
                      href="/user/become-agent"
                      className="dropdown-item d-flex justify-content-between align-items-center py-2 fw-semibold"
                      style={{ background: 'rgba(242, 178, 3, 0.08)' }}
                    >
                      <div className="d-flex align-items-center gap-2">
                        <Briefcase size={16} className="text-warning" />
                        <span className="text-dark">Become an Agent</span>
                      </div>
                      <span className="badge bg-warning text-dark" style={{ fontSize: '0.62rem' }}>UPGRADE</span>
                    </Link>
                  </li>
                ) : (
                  <li>
                    <Link href="/user/become-agent" className="dropdown-item d-flex align-items-center gap-2 py-2">
                      <Briefcase size={16} className="text-primary" />
                      <span>Agent Profile & KYC</span>
                    </Link>
                  </li>
                )}

                {/* Owner / Agent / Property Actions */}
                {user && (
                  <>
                    <li><h6 className="dropdown-header">{user.role === 'agent' ? 'Agent Actions' : 'Property Management'}</h6></li>
                    {(user.role === 'owner' || user.role === 'agent') && user.status === 'approved' && (
                      <li>
                        <Link href="/owner/dashboard" className="dropdown-item py-2">
                          <LayoutGrid size={16} className="text-muted" />
                          <span>Owner Dashboard</span>
                        </Link>
                      </li>
                    )}
                    <li>
                      <Link href="/owner/listings" className="dropdown-item py-2">
                        <Bookmark size={16} className="text-muted" />
                        <span>My Properties</span>
                      </Link>
                    </li>
                    {(user.role === 'owner' || user.role === 'agent') && user.status === 'approved' && (
                      <li>
                        <Link href="/owner/enquiries" className="dropdown-item py-2">
                          <MessageSquare size={16} className="text-muted" />
                          <span>Received Enquiries</span>
                        </Link>
                      </li>
                    )}
                  </>
                )}

                {user.role === 'tenant' && user.status === 'approved' && (
                  <>
                    <li><h6 className="dropdown-header">Tenant Actions</h6></li>
                    <li>
                      <Link href="/tenant/dashboard" className="dropdown-item py-2">
                        <LayoutGrid size={16} className="text-muted" />
                        <span>Tenant Dashboard</span>
                      </Link>
                    </li>
                  </>
                )}

                <li><h6 className="dropdown-header">Account</h6></li>
                <li>
                  <Link href="/user/profile" className="dropdown-item py-2">
                    <User size={16} className="text-muted" />
                    <span>Manage Profile</span>
                  </Link>
                </li>
                <li>
                  <Link href="/user/settings" className="dropdown-item py-2">
                    <Key size={16} className="text-muted" />
                    <span>Change Password</span>
                  </Link>
                </li>
                {user.status === 'approved' && (
                  <li>
                    <Link href="/user/feedback" className="dropdown-item py-2">
                      <MessageSquare size={16} className="text-muted" />
                      <span>Feedback</span>
                    </Link>
                  </li>
                )}
                <li>
                  <button
                    type="button"
                    className="dropdown-item py-2 text-danger"
                    onClick={(e) => {
                      e.preventDefault();
                      void logout();
                    }}
                  >
                    <LogOut size={16} />
                    <span>Logout</span>
                  </button>
                </li>

                <li><hr className="dropdown-divider" /></li>

                <li><h6 className="dropdown-header">My Activity</h6></li>
                <li>
                  <Link href="/search" className="dropdown-item py-2">
                    <Compass size={16} className="text-muted" />
                    <span>Recent Searches</span>
                  </Link>
                </li>
                <li>
                  <Link href="/tenant/enquiries" className="dropdown-item py-2">
                    <MessageSquare size={16} className="text-muted" />
                    <span>Sent Enquiries</span>
                  </Link>
                </li>
                <li>
                  <Link href="/user/wishlist" className="dropdown-item py-2">
                    <Bookmark size={16} className="text-muted" />
                    <span>My Wishlist</span>
                  </Link>
                </li>
                <li>
                  <Link href="/owner/listings" className="dropdown-item py-2">
                    <Home size={16} className="text-muted" />
                    <span>My Properties</span>
                  </Link>
                </li>
              </ul>
            </div>
          ) : (
            <div className="dropdown">
              <button
                className="btn btn-sm user-dropdown-btn-premium d-flex align-items-center gap-2 dropdown-toggle rounded-pill px-3 py-2 shadow-sm"
                type="button"
                id="guestDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <div className="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style={{ width: '26px', height: '26px' }}>
                  <User size={14} className="text-primary" />
                </div>
                <span className="fw-semibold text-primary d-none d-sm-inline">Login / Register</span>
                <ChevronDown size={14} className="text-muted" />
              </button>
              <ul className="dropdown-menu dropdown-menu-end dropdown-menu-premium shadow border-0 animate-fade-in mt-2" aria-labelledby="guestDropdown" style={{ width: '250px', borderRadius: '12px' }}>
                <li className="p-3 text-center border-bottom">
                  <p className="text-muted small mb-2" style={{ lineHeight: '1.4' }}>Login to manage properties, bookmarks & activities</p>
                  <button 
                    className="btn btn-danger btn-sm w-100 fw-bold text-white rounded-pill py-2 shadow-sm hover-scale-sm"
                    style={{ background: 'linear-gradient(135deg, var(--nb-accent) 0%, var(--nb-accent-dark) 100%)', border: 'none' }}
                    onClick={() => setAuthModalOpen('login')}
                  >
                    Login / Register
                  </button>
                </li>
                <li><h6 className="dropdown-header">My Activity</h6></li>
                <li>
                  <Link href="/search" className="dropdown-item py-2">
                    <Compass size={16} className="text-muted" />
                    <span>Recent Searches</span>
                  </Link>
                </li>
                <li>
                  <Link href="/user/wishlist" className="dropdown-item py-2">
                    <Bookmark size={16} className="text-muted" />
                    <span>My Wishlist</span>
                  </Link>
                </li>
                <li>
                  <Link href="/owner/listings" className="dropdown-item py-2">
                    <Home size={16} className="text-muted" />
                    <span>My Properties</span>
                  </Link>
                </li>
                <li><hr className="dropdown-divider" /></li>
                <li>
                  <Link href="/user/become-agent" className="dropdown-item d-flex justify-content-between align-items-center py-2 fw-semibold">
                    <div className="d-flex align-items-center gap-2">
                      <Briefcase size={16} className="text-warning" />
                      <span>Become an Agent</span>
                    </div>
                    <span className="badge bg-warning text-dark" style={{ fontSize: '0.62rem' }}>JOIN</span>
                  </Link>
                </li>
                <li>
                  <Link href="/owner/property/add" className="dropdown-item d-flex justify-content-between align-items-center py-2 fw-semibold post-property-free-btn-dropdown">
                    <div className="d-flex align-items-center gap-2">
                      <PlusCircle size={16} />
                      <span>Post Property</span>
                    </div>
                    <span className="badge bg-success rounded-1">FREE</span>
                  </Link>
                </li>
              </ul>
            </div>
          )}

          {/* Hamburger Menu (Sidebar) Button */}
          <button
            className="btn btn-sm btn-link text-decoration-none text-muted p-2 rounded-circle hover-bg-light d-flex align-items-center justify-content-center sidebar-menu-btn"
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            aria-label="Toggle menu"
            style={{ width: '38px', height: '38px' }}
          >
            <Menu size={22} className="text-primary" />
          </button>

        </div>
      </div>
      <MobileSidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} />
    </nav>
  );
};

export default Navbar;
