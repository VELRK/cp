'use client';

import React from 'react';
import { ArrowRight } from 'lucide-react';

const MagicLoans: React.FC = () => {
  return (
    <div className="mb-5 fade-in-up">
      <div className="nb-magic-loans-banner">
        <div className="nb-magic-loans-content">
          <div className="nb-magic-loans-logo">
            Coimbatore Properties<span> Loans</span>
          </div>
          <h2 className="nb-magic-loans-title">Compare Home Loan Offers from 40+ Banks</h2>
          <div className="nb-magic-loans-features">
            <span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="me-1 d-inline-block">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg> 
              Rates starting from <span className="highlight">7.1%</span>
            </span>
            <span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="me-1 d-inline-block">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg> 
              <span className="highlight">0%*</span> Processing Fee
            </span>
          </div>

          <div className="nb-magic-loans-partners-label">Our Banking Partners</div>
          <div className="nb-magic-loans-banks-wrapper">
            <div className="nb-magic-loans-banks-track">
              {[1, 2].map((loop) => (
                <div key={loop} style={{ display: 'flex', gap: '1rem' }}>
                  <div className="nb-magic-loans-bank-card">
                    <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>HDFC Bank</h4>
                    <p>Starts at 7.25%</p>
                  </div>
                  <div className="nb-magic-loans-bank-card">
                    <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>Bajaj Finserv</h4>
                    <p>Starts at 7.15%</p>
                  </div>
                  <div className="nb-magic-loans-bank-card">
                    <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>LIC HFL</h4>
                    <p>Starts at 7.8%</p>
                  </div>
                  <div className="nb-magic-loans-bank-card">
                    <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>SBI</h4>
                    <p>Starts at 7.25%</p>
                  </div>
                  <div className="nb-magic-loans-bank-card">
                    <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>Canara Bank</h4>
                    <p>Starts at 7.15%</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="nb-magic-loans-actions">
            <button className="nb-magic-loans-btn-outline" onClick={() => window.open('/loans', '_self')}>
              Explore Bank Offers <ArrowRight size={16} className="ms-1" />
            </button>
            <button className="nb-magic-loans-btn-filled" onClick={() => window.open('/loans/eligibility', '_self')}>
              Check Your Eligibility
            </button>
          </div>
        </div>

        <div className="nb-magic-loans-image-wrapper">
          <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" style={{ mixBlendMode: 'multiply', opacity: 0.9, borderRadius: '50%', width: '100%', height: '100%', objectFit: 'cover' }} alt="Home Loan" />
        </div>
      </div>
    </div>
  );
};

export default MagicLoans;
