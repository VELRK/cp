'use client';

import React from 'react';
import Link from 'next/link';
import {
  Briefcase,
  ShieldCheck,
  Award,
  ArrowRight,
  TrendingUp,
  CheckCircle2,
  Users,
  Building,
} from 'lucide-react';
import { useAuth } from '@/hooks/useAuth';

interface BecomeAgentBannerProps {
  className?: string;
}

const BecomeAgentBanner: React.FC<BecomeAgentBannerProps> = ({ className = '' }) => {
  const { user, setAuthModalOpen } = useAuth();
  const isAgent = user && (user.role === 'agent' || user.user_type === 'agent');

  return (
    <div className={`nb-become-agent-banner my-4 fade-in-up ${className}`}>
      <div className="card classic-agent-banner-card border-0 rounded-4 overflow-hidden shadow-lg position-relative">
        {/* Subtle decorative golden light orb */}
        <div className="classic-glow-orb position-absolute pointer-events-none" />

        <div className="card-body p-4 p-md-5 text-white position-relative z-index-1">
          <div className="row align-items-center g-4">
            {/* Left content */}
            <div className="col-lg-8">
              <div className="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 classic-tag-badge">
                <ShieldCheck size={15} className="text-warning" />
                <span className="classic-tag-text text-uppercase fw-bold">
                  FOR BROKERS & REAL ESTATE CONSULTANTS
                </span>
              </div>

              <h2 className="classic-banner-heading fw-bold mb-3">
                Grow Your Real Estate Business with Us
              </h2>

              <p className="classic-banner-desc text-white-50 mb-4" style={{ maxWidth: '620px' }}>
                Are you a property broker or independent consultant? Upgrade your customer account to an{' '}
                <strong className="text-white">Accredited Agent Profile</strong>. Post unlimited properties,
                receive genuine buyer leads directly on WhatsApp, and boost your deals with zero listing fees.
              </p>

              {/* 3 Value Pillars */}
              <div className="row g-2 g-md-3 mb-4" style={{ maxWidth: '650px' }}>
                <div className="col-sm-4">
                  <div className="d-flex align-items-center gap-2 classic-pillar-item">
                    <CheckCircle2 size={16} className="text-warning flex-shrink-0" />
                    <span className="small fw-semibold text-white">0% Listing Fee</span>
                  </div>
                </div>
                <div className="col-sm-4">
                  <div className="d-flex align-items-center gap-2 classic-pillar-item">
                    <CheckCircle2 size={16} className="text-warning flex-shrink-0" />
                    <span className="small fw-semibold text-white">Direct Buyer Leads</span>
                  </div>
                </div>
                <div className="col-sm-4">
                  <div className="d-flex align-items-center gap-2 classic-pillar-item">
                    <CheckCircle2 size={16} className="text-warning flex-shrink-0" />
                    <span className="small fw-semibold text-white">Verified Agent Badge</span>
                  </div>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="d-flex flex-wrap align-items-center gap-3">
                <Link
                  href="/user/become-agent"
                  className="btn classic-gold-action-btn rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 shadow"
                >
                  <Briefcase size={18} />
                  <span>{isAgent ? 'Manage Agent Profile' : 'Become an Agent'}</span>
                  <ArrowRight size={16} />
                </Link>

                {!user && (
                  <button
                    type="button"
                    className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-semibold small"
                    onClick={() => setAuthModalOpen('login')}
                  >
                    Already an Agent? Sign In
                  </button>
                )}
              </div>
            </div>

            {/* Right side privilege seal card */}
            <div className="col-lg-4 d-none d-lg-block text-end">
              <div className="classic-card-mockup p-4 rounded-4 text-start border shadow d-inline-block">
                <div className="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary">
                  <span className="text-warning small text-uppercase fw-bold letter-spacing-1">
                    Agent Privilege Pass
                  </span>
                  <Award size={20} className="text-warning" />
                </div>

                <div className="classic-card-chip mb-3" />

                <div className="fw-bold text-white fs-6 mb-1">
                  {user ? user.name : 'Real Estate Partner'}
                </div>
                <div className="text-warning small mb-3">Coimbatore Properties Network</div>

                <div className="d-flex justify-content-between pt-2 border-top border-secondary text-white-50 small">
                  <div>
                    <span className="d-block text-white fw-bold">10,000+</span>
                    <span style={{ fontSize: '0.7rem' }}>Monthly Buyers</span>
                  </div>
                  <div className="text-end">
                    <span className="d-block text-success fw-bold">Verified</span>
                    <span style={{ fontSize: '0.7rem' }}>KYC Status</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <style
        dangerouslySetInnerHTML={{
          __html: `
        .classic-agent-banner-card {
          background: linear-gradient(135deg, #071f3f 0%, #0b2c56 50%, #0e386d 100%);
          border: 1px solid rgba(212, 175, 55, 0.28) !important;
        }
        .classic-glow-orb {
          top: -30%;
          right: -10%;
          width: 400px;
          height: 400px;
          background: radial-gradient(circle, rgba(242, 178, 3, 0.16) 0%, transparent 65%);
          border-radius: 50%;
        }
        .classic-tag-badge {
          background: rgba(242, 178, 3, 0.12);
          border: 1px solid rgba(242, 178, 3, 0.35);
        }
        .classic-tag-text {
          font-size: 0.72rem;
          letter-spacing: 0.08em;
          color: #f2b203;
        }
        .classic-banner-heading {
          font-size: 1.85rem;
          line-height: 1.25;
          letter-spacing: -0.02em;
        }
        .classic-gold-action-btn {
          background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%) !important;
          color: #071f3f !important;
          border: 1px solid #d4af37 !important;
          transition: all 0.25s ease;
        }
        .classic-gold-action-btn:hover {
          background: linear-gradient(135deg, #e5be48 0%, #c9961a 100%) !important;
          transform: translateY(-2px);
          box-shadow: 0 8px 18px rgba(212, 175, 55, 0.35) !important;
        }
        .classic-card-mockup {
          background: rgba(7, 31, 63, 0.88);
          backdrop-filter: blur(8px);
          border-color: rgba(212, 175, 55, 0.35) !important;
          width: 270px;
          transform: rotate(1.5deg);
          transition: transform 0.3s ease;
        }
        .classic-card-mockup:hover {
          transform: rotate(0deg) scale(1.03);
        }
        .classic-card-chip {
          width: 38px;
          height: 26px;
          background: linear-gradient(135deg, #f2b203 0%, #b8860b 100%);
          border-radius: 4px;
          opacity: 0.85;
        }
        .letter-spacing-1 {
          letter-spacing: 0.06em;
        }
      `,
        }}
      />
    </div>
  );
};

export default BecomeAgentBanner;
