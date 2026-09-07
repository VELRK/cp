'use client';

import React from 'react';
import Link from 'next/link';

export default function PrivacyPolicyPage() {
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
              <li className="breadcrumb-item active text-white fw-bold" aria-current="page">/ Privacy Policy</li>
            </ol>
          </nav>
          <h1 className="display-5 fw-extrabold text-white mb-2">Privacy Policy</h1>
          <p className="lead text-white-50 max-w-2xl mx-auto small" style={{ maxWidth: '600px' }}>
            How we collect, use, and protect your information
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
                  At <strong>Coimbatore Properties Real Estate App</strong>, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and protect your data when you use our app.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">1. Information We Collect</h4>
                <p className="text-secondary" style={{ lineHeight: '1.8' }}>We may collect the following types of information:</p>
                <ul className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  <li><strong>Personal Information:</strong> When you create an account, contact us, or make inquiries, we may collect personal details such as your name, email address, phone number, and location.</li>
                  <li><strong>Usage Data:</strong> We collect information about how you use the app, including browsing behavior, the features you use, and device information (e.g., IP address, device type, operating system).</li>
                  <li><strong>Location Data:</strong> With your permission, we may collect location data to provide you with relevant property listings based on your location.</li>
                </ul>

                <h4 className="fw-bold text-dark mt-4 mb-3">2. How We Use Your Information</h4>
                <p className="text-secondary" style={{ lineHeight: '1.8' }}>We use the information we collect for the following purposes:</p>
                <ul className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  <li>To provide and improve our services, including property listings and personalized recommendations.</li>
                  <li>To communicate with you regarding new properties, updates, and other relevant information.</li>
                  <li>To respond to inquiries, requests, or feedback you may provide.</li>
                  <li>To process transactions and manage your account.</li>
                  <li>To comply with legal obligations and protect our rights.</li>
                </ul>

                <h4 className="fw-bold text-dark mt-4 mb-3">3. Sharing Your Information</h4>
                <p className="text-secondary" style={{ lineHeight: '1.8' }}>We do not sell or rent your personal information to third parties. However, we may share your information with:</p>
                <ul className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  <li><strong>Service Providers:</strong> Third-party companies who assist in the operation of our app, such as hosting services, payment processors, and analytics providers.</li>
                  <li><strong>Legal Compliance:</strong> We may disclose your information if required to do so by law or in response to a legal request (e.g., a subpoena or court order).</li>
                </ul>

                <h4 className="fw-bold text-dark mt-4 mb-3">4. Data Security</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  We implement reasonable security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet or electronic storage is completely secure, and we cannot guarantee the absolute security of your information.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">5. Your Rights and Choices</h4>
                <p className="text-secondary" style={{ lineHeight: '1.8' }}>You have the following rights regarding your personal information:</p>
                <ul className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  <li><strong>Access and Update:</strong> You can review and update your personal information through your account settings.</li>
                  <li><strong>Opt-out:</strong> You can opt out of receiving promotional emails by following the unsubscribe instructions in the emails or by contacting us directly.</li>
                  <li><strong>Location Data:</strong> You can manage location data permissions through your device settings.</li>
                </ul>

                <h4 className="fw-bold text-dark mt-4 mb-3">6. Data Retention</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, comply with legal obligations, and resolve disputes.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">7. Changes to This Privacy Policy</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. Please review this Privacy Policy periodically to stay informed about how we protect your information.
                </p>

                <h4 className="fw-bold text-dark mt-4 mb-3">8. Contact Us</h4>
                <p className="text-secondary mb-4" style={{ lineHeight: '1.8' }}>
                  If you have any questions about this Privacy Policy or our data practices, please contact us at:
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
