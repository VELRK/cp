'use client';

import React from 'react';

const WhyChooseUs: React.FC = () => {
  return (
    <div className="mb-5 fade-in-up pt-5 bg-white text-center rounded-4 shadow-sm border border-light" style={{ paddingBottom: '3rem' }}>
      <p className="text-muted text-uppercase fw-bold mb-2" style={{ fontSize: '0.75rem', letterSpacing: '1px' }}>BENEFITS OF COIMBATORE PROPERTIES</p>
      <h2 className="h2 fw-bold text-dark mb-5" style={{ color: '#0b2c56' }}>Why choose Coimbatore Properties</h2>

      <div className="row g-4 mx-auto text-start px-4 px-md-5" style={{ maxWidth: '1000px' }}>
        <div className="col-md-4">
          <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#f0f7fb' }}>
            <img src="https://img.icons8.com/color/48/skyscrapers.png" alt="Properties" width="28" height="28" />
          </div>
          <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">01.</span> Over 10,000+ properties</h3>
          <p className="text-muted small" style={{ lineHeight: '1.6' }}>100+ new properties are added every day from verified sellers and builders.</p>
        </div>

        <div className="col-md-4">
          <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#fdf8ec' }}>
            <img src="https://img.icons8.com/color/48/approval--v1.png" alt="Verification" width="28" height="28" />
          </div>
          <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">02.</span> Verification by our team</h3>
          <p className="text-muted small" style={{ lineHeight: '1.6' }}>Photos / Videos and other details are verified on location by our experts.</p>
        </div>

        <div className="col-md-4">
          <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#f3f4f6' }}>
            <img src="https://img.icons8.com/color/48/conference-call.png" alt="Users" width="28" height="28" />
          </div>
          <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">03.</span> Large user base</h3>
          <p className="text-muted small" style={{ lineHeight: '1.6' }}>High active user count and user engagement to find and close deals fast.</p>
        </div>
      </div>
    </div>
  );
};

export default WhyChooseUs;
