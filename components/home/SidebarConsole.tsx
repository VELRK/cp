'use client';

import React from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { User } from 'lucide-react';

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
      <div className="nb-sidebar-promo-card fade-in-up">
        <h3 className="h6 fw-bold text-success mb-2" style={{ color: '#1b5e20' }}>
          Sell or rent faster at the right price!
        </h3>
        <p className="text-secondary mb-3" style={{ fontSize: '0.75rem', lineHeight: '1.4' }}>
          List your property now for free and reach thousands of genuine tenants/buyers in {cityName}.
        </p>
        <div className="d-flex justify-content-between align-items-center">
          <button
            type="button"
            className="nb-promo-btn"
            onClick={() => {
              if (user) {
                router.push('/owner/property/add');
              } else {
                setAuthModalOpen('login');
              }
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
