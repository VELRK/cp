'use client';

import React from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { User, Sparkles } from 'lucide-react';

interface SidebarConsoleProps {
  user: any;
  wishlistedIds: number[];
  setAuthModalOpen: (val: 'login' | 'register' | null) => void;
  setShowLiveUpdateModal?: (val: boolean) => void;
  getDashboardPath: () => string;
  cityName: string;
}

const SidebarConsole: React.FC<SidebarConsoleProps> = ({
  user,
  wishlistedIds,
  setAuthModalOpen,
  setShowLiveUpdateModal,
  getDashboardPath,
  cityName
}) => {
  const router = useRouter();

  return (
    <div className="d-flex flex-column gap-4 sticky-top" style={{ top: '5.5rem', zIndex: 5 }}>
      {/* 4. Personalized Sidebar Activity Console */}
      <div className="nb-sidebar-profile-console fade-in-up">
        <div className="d-flex align-items-center gap-3 mb-3">
          <div className="nb-profile-avatar-circle">
            {user ? user.name.charAt(0).toUpperCase() : <User size={20} />}
          </div>
          <div>
            <h3 className="h6 fw-bold text-dark m-0">{user ? user.name : 'Welcome, Guest'}</h3>
            <span className="text-muted small d-block" style={{ fontSize: '0.75rem' }}>
              {user ? `${user.role.toUpperCase()} Account` : 'Premium Features Locked'}
            </span>
          </div>
        </div>

        <div className="nb-profile-stat-box">
          <div className="nb-profile-stat-item">
            <div className="nb-profile-stat-num">
              {user ? wishlistedIds.length : '—'}
            </div>
            <div className="nb-profile-stat-txt">Wishlist</div>
          </div>
          <div className="nb-profile-stat-item">
            <div className="nb-profile-stat-num">
              {user ? 'Active' : 'Guest'}
            </div>
            <div className="nb-profile-stat-txt">Status</div>
          </div>
        </div>

        {user ? (
          <>
            <Link
              href={getDashboardPath()}
              className="btn w-100 py-2 rounded-3 small fw-bold text-white mb-2 text-center text-decoration-none"
              style={{ backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)' }}
            >
              Go to Dashboard
            </Link>
            {/*
            <button
              type="button"
              onClick={() => setShowLiveUpdateModal?.(true)}
              className="btn w-100 py-2 rounded-3 small fw-bold text-danger border border-danger bg-white"
            >
              <span className="me-1" style={{ fontSize: '10px' }}>🔴</span> Manage Live Updates
            </button>
            */}
          </>
        ) : (
          <button
            type="button"
            className="btn w-100 py-2 rounded-3 small fw-bold text-white"
            style={{ backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)' }}
            onClick={() => setAuthModalOpen('login')}
          >
            Login to Save Activities
          </button>
        )}

        {!user && (
          <p className="text-center text-muted small mt-2 mb-0" style={{ fontSize: '0.65rem' }}>
            Access dashboard, manage wishlists, and search directly.
          </p>
        )}
      </div>

      {/* Sell or Rent Promo Card */}
      <div
        className="nb-sidebar-promo-card fade-in-up p-4 rounded-4 shadow-sm border position-relative overflow-hidden mt-3"
        style={{
          background: 'linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)',
          borderColor: '#e9ecef',
          transition: 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease',
          cursor: 'pointer'
        }}
        onMouseEnter={(e) => {
          e.currentTarget.style.transform = 'translateY(-5px) scale(1.02)';
          e.currentTarget.style.boxShadow = '0 15px 30px rgba(0,0,0,0.08)';
        }}
        onMouseLeave={(e) => {
          e.currentTarget.style.transform = 'translateY(0) scale(1)';
          e.currentTarget.style.boxShadow = '0 0.125rem 0.25rem rgba(0,0,0,0.075)';
        }}
        onClick={() => {
          if (user) {
            router.push('/owner/property/add');
          } else {
            setAuthModalOpen('login');
          }
        }}
      >
        <div className="position-absolute pointer-events-none" style={{ top: '-15px', right: '-10px', transform: 'rotate(15deg)', opacity: 0.04, zIndex: 0 }}>
          <Sparkles size={110} color="#1b5e20" />
        </div>

        <div className="d-flex align-items-center mb-2 position-relative z-index-1">
          <div className="bg-success bg-opacity-10 p-2 rounded-circle me-2 d-flex align-items-center justify-content-center">
            <Sparkles size={18} className="text-success" />
          </div>
          <h3 className="h6 fw-bold mb-0" style={{ color: '#1b5e20', letterSpacing: '0.2px' }}>
            Sell or rent faster!
          </h3>
        </div>

        <p className="text-secondary mb-4 position-relative z-index-1" style={{ fontSize: '0.85rem', lineHeight: '1.5' }}>
          List your property now for <strong className="text-dark">FREE</strong> and reach thousands of genuine tenants/buyers in <span className="fw-semibold">{cityName}</span>.
        </p>

        <div className="d-flex justify-content-start align-items-center position-relative z-index-1">
          <button
            type="button"
            className="btn rounded-pill px-4 py-2 fw-semibold w-100 shadow-sm d-flex justify-content-center align-items-center gap-2"
            style={{
              backgroundColor: '#1b5e20',
              color: 'white',
              border: 'none',
              transition: 'all 0.3s ease'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.backgroundColor = '#144d18';
              e.currentTarget.style.boxShadow = '0 5px 15px rgba(27, 94, 32, 0.3)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.backgroundColor = '#1b5e20';
              e.currentTarget.style.boxShadow = '0 .125rem .25rem rgba(0,0,0,.075)';
            }}
          >
            Post Property, It's FREE
          </button>
        </div>
      </div>
    </div>
  );
};

export default SidebarConsole;
