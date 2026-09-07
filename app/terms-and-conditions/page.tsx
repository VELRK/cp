'use client';

import React from 'react';
import Link from 'next/link';

export default function TermsAndConditionsPage() {
  return (
    <div className="nb-body">
      {/* Hero Header Section */}
      <section
        className="text-white text-center py-5 mt-5 d-flex align-items-center justify-content-center position-relative"
        style={{
          background: 'linear-gradient(135deg, #0b2c56 0%, #071f3f 100%)',
          minHeight: '260px',
        }}
      >
        <div className="container position-relative" style={{ zIndex: 2 }}>
          <nav aria-label="breadcrumb" className="d-flex justify-content-center mb-2">
            <ol className="breadcrumb mb-0" style={{ fontSize: '0.85rem' }}>
              <li className="breadcrumb-item"><Link href="/" className="text-white opacity-75 text-decoration-none">Home</Link></li>
              <li className="breadcrumb-item active text-white fw-bold" aria-current="page">/ Terms and Conditions</li>
            </ol>
          </nav>
          <h1 className="display-5 fw-extrabold text-white mb-2">Terms and Conditions</h1>
          <p className="lead text-white-50 max-w-2xl mx-auto small" style={{ maxWidth: '600px' }}>
            Please read these terms carefully before using our services
          </p>
        </div>

        {/* Subtle background glow */}
        <div
          className="position-absolute top-50 start-50 translate-middle rounded-circle opacity-10"
          style={{
            width: '400px',
            height: '400px',
            background: 'radial-gradient(circle, #f2b203 0%, transparent 70%)',
            filter: 'blur(50px)',
            zIndex: 1,
          }}
        />
      </section>

      {/* Main Content Section */}
      <section className="py-5 bg-light">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-lg-10">
              <div className="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  Welcome to <strong>Coimbatore Properties Real Estate App</strong>. By accessing or using our application, website, or any related services, you agree to comply with and be bound by the following Terms and Conditions. Please review them carefully.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">1. Acceptance of Terms</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  By downloading, installing, or using the Coimbatore Properties app, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">2. Use of the App</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  You agree to use the app only for lawful purposes and in a manner that does not infringe the rights of, restrict, or inhibit anyone else's use and enjoyment of the app. Unauthorized use of the app, including but not limited to unauthorized entry into our systems, misuse of passwords, or misuse of any information posted on the app, is strictly prohibited.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">3. Property Listings and Accuracy</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  While we strive to provide accurate and up-to-date property information, Coimbatore Properties does not warrant the completeness, reliability, or accuracy of the property listings, prices, descriptions, or availability. All information is subject to change without notice. Users are encouraged to independently verify all details before making any real estate decisions.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">4. Intellectual Property</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  All content, trademarks, logos, and other intellectual property displayed on the app are the property of Coimbatore Properties or its licensors. You may not reproduce, distribute, modify, or create derivative works of any content without our express prior written consent.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">5. User Accounts</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  To access certain features of the app, you may be required to register an account. You are responsible for maintaining the confidentiality of your account information, including your password, and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">6. Limitation of Liability</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  Coimbatore Properties shall not be liable for any direct, indirect, incidental, special, or consequential damages resulting from the use or inability to use the app, or for any reliance on the information provided through the app. This includes, but is not limited to, damages for loss of profits, data, or other intangible losses.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">7. Modifications to the Service</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  We reserve the right to modify or discontinue, temporarily or permanently, the app or any service to which it connects, with or without notice and without liability to you.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">8. Contact Information</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  If you have any questions or concerns regarding these Terms and Conditions, please contact us at:
                  <br />
                  <br />
                  <strong>Phone:</strong> (+91) 95007 17777
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
