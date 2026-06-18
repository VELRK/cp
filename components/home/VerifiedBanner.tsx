'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import { ShieldCheck, TrendingUp, Sparkles, ArrowRight } from 'lucide-react';

const VerifiedBanner: React.FC = () => {
  const router = useRouter();

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2 className="h4 fw-bold m-0" style={{ color: '#0f172a' }}>Explore Highlights</h2>
      </div>

      <div className="row g-4">
        {/* Verified Properties Card */}
        <div className="col-md-4">
          <div
            className="p-4 rounded-4 h-100 position-relative overflow-hidden"
            style={{
              backgroundColor: '#f8fafc',
              border: '1px solid #e2e8f0',
              cursor: 'pointer',
              transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
              e.currentTarget.style.borderColor = '#10b981';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.05)';
              e.currentTarget.style.borderColor = '#e2e8f0';
            }}
            onClick={() => router.push('/search?verified=true')}
          >
            <div className="d-flex align-items-center mb-3">
              <div
                className="d-flex align-items-center justify-content-center rounded-circle me-3"
                style={{ width: '48px', height: '48px', backgroundColor: '#dcfce7', color: '#10b981' }}
              >
                <ShieldCheck size={24} />
              </div>
              <h3 className="h6 fw-bold m-0" style={{ color: '#0f172a' }}>Verified Properties</h3>
            </div>
            <p className="text-secondary mb-4" style={{ fontSize: '0.9rem', lineHeight: '1.5' }}>
              Handpicked properties verified on-site for absolute genuineness and peace of mind.
            </p>
            <div className="d-flex align-items-center fw-semibold" style={{ color: '#10b981', fontSize: '0.9rem' }}>
              Explore Verified <ArrowRight size={16} className="ms-2" />
            </div>

            {/* Background decoration */}
            <div className="position-absolute" style={{ right: '-20px', bottom: '-20px', opacity: 0.03, transform: 'scale(3)' }}>
              <ShieldCheck size={100} />
            </div>
          </div>
        </div>

        {/* Trending Properties Card */}
        <div className="col-md-4">
          <div
            className="p-4 rounded-4 h-100 position-relative overflow-hidden"
            style={{
              backgroundColor: '#f8fafc',
              border: '1px solid #e2e8f0',
              cursor: 'pointer',
              transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
              e.currentTarget.style.borderColor = '#3b82f6';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.05)';
              e.currentTarget.style.borderColor = '#e2e8f0';
            }}
            onClick={() => router.push('/search?sort=trending')}
          >
            <div className="d-flex align-items-center mb-3">
              <div
                className="d-flex align-items-center justify-content-center rounded-circle me-3"
                style={{ width: '48px', height: '48px', backgroundColor: '#dbeafe', color: '#3b82f6' }}
              >
                <TrendingUp size={24} />
              </div>
              <h3 className="h6 fw-bold m-0" style={{ color: '#0f172a' }}>Trending Now</h3>
            </div>
            <p className="text-secondary mb-4" style={{ fontSize: '0.9rem', lineHeight: '1.5' }}>
              Discover the most popular properties that everyone is looking at right now.
            </p>
            <div className="d-flex align-items-center fw-semibold" style={{ color: '#3b82f6', fontSize: '0.9rem' }}>
              View Trending <ArrowRight size={16} className="ms-2" />
            </div>

            {/* Background decoration */}
            <div className="position-absolute" style={{ right: '-20px', bottom: '-20px', opacity: 0.03, transform: 'scale(3)' }}>
              <TrendingUp size={100} />
            </div>
          </div>
        </div>

        {/* Hot Deals Card */}
        <div className="col-md-4">
          <div
            className="p-4 rounded-4 h-100 position-relative overflow-hidden"
            style={{
              backgroundColor: '#f8fafc',
              border: '1px solid #e2e8f0',
              cursor: 'pointer',
              transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
              e.currentTarget.style.borderColor = '#f59e0b';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.05)';
              e.currentTarget.style.borderColor = '#e2e8f0';
            }}
            onClick={() => router.push('/search?sort=price_low')}
          >
            <div className="d-flex align-items-center mb-3">
              <div
                className="d-flex align-items-center justify-content-center rounded-circle me-3"
                style={{ width: '48px', height: '48px', backgroundColor: '#fef3c7', color: '#f59e0b' }}
              >
                <Sparkles size={24} />
              </div>
              <h3 className="h6 fw-bold m-0" style={{ color: '#0f172a' }}>Hot Deals</h3>
            </div>
            <p className="text-secondary mb-4" style={{ fontSize: '0.9rem', lineHeight: '1.5' }}>
              Unbeatable prices and exclusive offers on select premium properties.
            </p>
            <div className="d-flex align-items-center fw-semibold" style={{ color: '#f59e0b', fontSize: '0.9rem' }}>
              See Deals <ArrowRight size={16} className="ms-2" />
            </div>

            {/* Background decoration */}
            <div className="position-absolute" style={{ right: '-20px', bottom: '-20px', opacity: 0.03, transform: 'scale(3)' }}>
              <Sparkles size={100} />
            </div>
          </div>
        </div>

      </div>
    </div>
  );
};

export default VerifiedBanner;

