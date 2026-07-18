'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/hooks/useAuth';
import { getAdminPanelUrl } from '@/lib/frontendApi';
import { 
  X, 
  User, 
  PlusCircle, 
  ChevronDown, 
  ChevronUp, 
  Bookmark, 
  Compass, 
  MessageSquare, 
  Newspaper, 
  FileText, 
  LayoutGrid, 
  LogOut, 
  Key, 
  Search,
  Mail,
  Phone
} from 'lucide-react';
import './MobileSidebar.css';

interface MobileSidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

const MobileSidebar: React.FC<MobileSidebarProps> = ({ isOpen, onClose }) => {
  const { user, logout, setAuthModalOpen } = useAuth();
  
  // Collapsible sections state
  const [buyersOpen, setBuyersOpen] = useState(false);
  const [tenantsOpen, setTenantsOpen] = useState(false);
  const [ownersOpen, setOwnersOpen] = useState(false);
  const [insightsOpen, setInsightsOpen] = useState(false);
  const [accountOpen, setAccountOpen] = useState(false);

  const handleLinkClick = () => {
    onClose();
  };

  const handleLoginClick = () => {
    onClose();
    setAuthModalOpen('login');
  };

  return (
    <>
      {/* Backdrop overlay */}
      <div 
        className={`sidebar-backdrop ${isOpen ? 'show' : ''}`} 
        onClick={onClose}
      />

      {/* Sidebar Drawer container */}
      <div className={`sidebar-drawer ${isOpen ? 'open' : ''}`}>
        
        {/* Header section */}
        <div className="sidebar-header">
          {user ? (
            <div className="user-profile-info">
              <div className="user-avatar-circle">
                {user.name ? user.name.charAt(0).toUpperCase() : <User size={20} />}
              </div>
              <div className="user-details">
                <span className="user-name">{user.name}</span>
                <span className="user-role-badge">{user.role}</span>
              </div>
            </div>
          ) : (
            <button className="sidebar-login-btn" onClick={handleLoginClick}>
              <div className="login-avatar-icon">
                <User size={20} />
              </div>
              <span className="login-label">LOGIN / REGISTER</span>
            </button>
          )}
          <button className="sidebar-close-btn" onClick={onClose} aria-label="Close menu">
            <X size={24} />
          </button>
        </div>

        {/* Sidebar Content */}
        <div className="sidebar-content">
          
          {/* Post Property Banner */}
          {(!user || user.role !== 'tenant') && (
            <div className="sidebar-promo-banner">
              <div className="promo-text-container">
                <p className="promo-title">Sell or rent faster at the right price!</p>
                <Link 
                  href={user ? "/owner/property/add" : "/register"} 
                  className="promo-btn"
                  onClick={handleLinkClick}
                >
                  <PlusCircle size={16} />
                  <span>Post Property <span className="free-badge">FREE</span></span>
                </Link>
              </div>
            </div>
          )}

          <div className="sidebar-menu-list">
            
            {/* For Buyers */}
            <div className="menu-group">
              <button 
                className="menu-group-header" 
                onClick={() => setBuyersOpen(!buyersOpen)}
              >
                <span>For Buyers</span>
                {buyersOpen ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              <div className={`menu-group-items ${buyersOpen ? 'expanded' : ''}`}>
                <Link href="/search" className="menu-item" onClick={handleLinkClick}>
                  <Search size={16} />
                  <span>Search Properties</span>
                </Link>
                {user && (
                  <>
                    <Link href="/user/wishlist" className="menu-item" onClick={handleLinkClick}>
                      <Bookmark size={16} />
                      <span>My Wishlist</span>
                    </Link>
                    <Link href="/tenant/enquiries" className="menu-item" onClick={handleLinkClick}>
                      <MessageSquare size={16} />
                      <span>Sent Enquiries</span>
                    </Link>
                    <Link href="/user/viewed" className="menu-item" onClick={handleLinkClick}>
                      <Compass size={16} />
                      <span>Viewed Properties</span>
                    </Link>
                  </>
                )}
              </div>
            </div>

            {/* For Tenants */}
            <div className="menu-group">
              <button 
                className="menu-group-header" 
                onClick={() => setTenantsOpen(!tenantsOpen)}
              >
                <span>For Tenants</span>
                {tenantsOpen ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              <div className={`menu-group-items ${tenantsOpen ? 'expanded' : ''}`}>
                {user && user.role === 'tenant' && user.status === 'approved' && (
                  <Link href="/tenant/dashboard" className="menu-item" onClick={handleLinkClick}>
                    <LayoutGrid size={16} />
                    <span>Tenant Dashboard</span>
                  </Link>
                )}
                <Link href="/search" className="menu-item" onClick={handleLinkClick}>
                  <Search size={16} />
                  <span>Search Properties</span>
                </Link>
                {user && (
                  <Link href="/user/wishlist" className="menu-item" onClick={handleLinkClick}>
                    <Bookmark size={16} />
                    <span>Shortlisted Properties</span>
                  </Link>
                )}
              </div>
            </div>

            {/* For Owners */}
            <div className="menu-group">
              <button 
                className="menu-group-header" 
                onClick={() => setOwnersOpen(!ownersOpen)}
              >
                <span>For Owners</span>
                {ownersOpen ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              <div className={`menu-group-items ${ownersOpen ? 'expanded' : ''}`}>
                {(!user || user.role !== 'tenant') && (
                  <Link href="/owner/property/add" className="menu-item" onClick={handleLinkClick}>
                    <PlusCircle size={16} />
                    <span>Post Property</span>
                  </Link>
                )}
                {user && user.role === 'owner' && user.status === 'approved' && (
                  <>
                    <Link href="/owner/dashboard" className="menu-item" onClick={handleLinkClick}>
                      <LayoutGrid size={16} />
                      <span>Owner Dashboard</span>
                    </Link>
                    <Link href="/owner/listings" className="menu-item" onClick={handleLinkClick}>
                      <Bookmark size={16} />
                      <span>My Properties</span>
                    </Link>
                    <Link href="/owner/enquiries" className="menu-item" onClick={handleLinkClick}>
                      <MessageSquare size={16} />
                      <span>Received Enquiries</span>
                    </Link>
                  </>
                )}
              </div>
            </div>

            {/* Insights & News */}
            <div className="menu-group">
              <button 
                className="menu-group-header" 
                onClick={() => setInsightsOpen(!insightsOpen)}
              >
                <span>Insights & News</span>
                {insightsOpen ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              <div className={`menu-group-items ${insightsOpen ? 'expanded' : ''}`}>
                <Link href="/knowledge-centre" className="menu-item" onClick={handleLinkClick}>
                  <Compass size={16} />
                  <span>Insights Hub</span>
                </Link>
                <Link href="/articles" className="menu-item" onClick={handleLinkClick}>
                  <FileText size={16} />
                  <span>Articles</span>
                </Link>
                <Link href="/news" className="menu-item" onClick={handleLinkClick}>
                  <Newspaper size={16} />
                  <span>Real Estate News</span>
                </Link>
              </div>
            </div>

            {/* User Account Actions */}
            {user && (
              <div className="menu-group">
                <button 
                  className="menu-group-header" 
                  onClick={() => setAccountOpen(!accountOpen)}
                >
                  <span>My Account</span>
                  {accountOpen ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
                </button>
                <div className={`menu-group-items ${accountOpen ? 'expanded' : ''}`}>
                  <Link href="/user/profile" className="menu-item" onClick={handleLinkClick}>
                    <User size={16} />
                    <span>Manage Profile</span>
                  </Link>
                  <Link href="/user/settings" className="menu-item" onClick={handleLinkClick}>
                    <Key size={16} />
                    <span>Change Password</span>
                  </Link>
                  {user.status === 'approved' && (
                    <Link href="/user/feedback" className="menu-item" onClick={handleLinkClick}>
                      <MessageSquare size={16} />
                      <span>Feedback</span>
                    </Link>
                  )}
                  {user.role === 'admin' && (
                    <a href={getAdminPanelUrl()} className="menu-item text-danger fw-bold" onClick={handleLinkClick}>
                      <LayoutGrid size={16} />
                      <span>Admin Panel</span>
                    </a>
                  )}
                  <button 
                    className="menu-item logout-btn text-danger border-0 bg-transparent text-start w-100" 
                    onClick={() => {
                      logout();
                      handleLinkClick();
                    }}
                  >
                    <LogOut size={16} />
                    <span>Logout</span>
                  </button>
                </div>
              </div>
            )}

            {/* Feedback for non-logged in users check */}
            {!user && (
              <Link href="/user/feedback" className="menu-link-direct" onClick={handleLinkClick}>
                <span>Feedback</span>
              </Link>
            )}

            {/* Direct links */}
            <Link href="/about" className="menu-link-direct" onClick={handleLinkClick}>
              <span>About Us</span>
            </Link>
          </div>

          {/* Support and contact info */}
          <div className="sidebar-support-footer">
            <div className="support-info-item">
              <Mail size={14} className="text-muted" />
              <a href="mailto:support@coimbatoreproperties.com">support@coimbatoreproperties.com</a>
            </div>
            <div className="support-info-item">
              <Phone size={14} className="text-muted" />
              <span>Toll Free: 1800 41 99099</span>
            </div>
            <p className="sidebar-copyright">© 2026 Coimbatore Properties. All rights reserved.</p>
          </div>

        </div>
      </div>
    </>
  );
};

export default MobileSidebar;
