'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { toFrontendAssetUrl } from '@/lib/cityImages';

interface PromoSectionProps {
  user: any;
  setAuthModalOpen: (val: 'login' | 'register' | null) => void;
}

const PromoSection: React.FC<PromoSectionProps> = ({ user, setAuthModalOpen }) => {
  return (
    <div className="container mb-5 fade-in-up mt-5">
      <div className="row align-items-center">
        {/* Left Side - Image with Gradient Background */}
        <div className="col-md-6 mb-4 mb-md-0 position-relative">
          <div
            className="rounded-4 overflow-hidden position-relative d-flex justify-content-center align-items-end promo-image-container"
            style={{
              background: 'linear-gradient(135deg, #f2f9f5 0%, #c1e3d6 100%)',
              minHeight: '400px',
              paddingTop: '2rem',
              boxShadow: '0 20px 40px rgba(0,0,0,0.05)'
            }}
          >
            <img
              src={toFrontendAssetUrl('/assets/img/promo-man.png')}
              alt="Post Property"
              className="img-fluid promo-image-animated"
              style={{
                maxHeight: '400px',
                objectFit: 'contain',
                position: 'relative',
                zIndex: 2,
                transition: 'transform 0.5s ease',
                marginBottom: '-2rem' // Pull it down slightly to match the "standing in frame" look
              }}
            />
            {/* Animated floating shapes in background */}
            <div className="position-absolute rounded-circle bg-white opacity-25 promo-shape-1" style={{ width: '100px', height: '100px', top: '10%', left: '10%' }}></div>
            <div className="position-absolute rounded-circle bg-white opacity-25 promo-shape-2" style={{ width: '150px', height: '150px', bottom: '20%', right: '-10%' }}></div>
          </div>
        </div>

        {/* Right Side - Content */}
        <div className="col-md-5 offset-md-1">
          <h2 className="fw-bold mb-3 text-dark" style={{ fontSize: '2.5rem', lineHeight: '1.2', color: '#0b1a30' }}>
            Sell or rent faster at the<br />right price!
          </h2>
          <p className="text-muted mb-4 fs-5">
            List your property now
          </p>

          <div className="d-flex flex-column align-items-start gap-4">
            <Link
              href={user ? '/owner/property/add' : '#'}
              onClick={(e) => {
                if (!user) {
                  e.preventDefault();
                  setAuthModalOpen('login');
                }
              }}
              className="btn btn-primary btn-lg px-5 py-3 fw-bold shadow promo-btn-animated"
              style={{ borderRadius: '8px', backgroundColor: '#0b2c56', border: 'none' }}
            >
              Post Property, It's FREE
            </Link>

            <a
              href="https://wa.me/919999999999?text=I%20want%20to%20list%20my%20property%20for%20free"
              target="_blank"
              rel="noopener noreferrer"
              className="text-dark fw-bold text-decoration-none d-flex align-items-center promo-wa-link"
              style={{ fontSize: '1.1rem' }}
            >
              Post via
              <svg width="24" height="24" fill="#25D366" className="bi bi-whatsapp mx-2" viewBox="0 0 16 16">
                <path d="M13.601 2.326A7.85 7.85 0 0 0 8 0a7.85 7.85 0 0 0-7.852 7.852c0 1.51.417 2.99 1.208 4.3l-.861 3.15 3.255-.853a7.85 7.85 0 0 0 3.758.974h.001c4.341 0 7.863-3.522 7.863-7.863a7.85 7.85 0 0 0-2.266-5.556m-5.602 11.233c-1.393 0-2.756-.372-3.948-1.077l-.283-.168-1.945.51.519-1.898-.184-.294A6.55 6.55 0 0 1 1.776 7.852c0-3.619 2.946-6.565 6.566-6.565 1.753 0 3.4.682 4.64 1.922 1.24 1.24 1.922 2.9 1.92 4.64 0 3.62-2.947 6.565-6.565 6.565m3.56-4.93c-.197-.1-.197-.1-.363-.18-.167-.08-.348-.167-.533-.255-.185-.088-.308-.068-.4.043-.092.11-.355.445-.436.538-.08.093-.16.103-.357.004a4.5 4.5 0 0 1-1.32-.814 4.86 4.86 0 0 1-1.026-1.28c-.114-.196-.012-.302.086-.4.088-.088.197-.23.296-.346.099-.115.132-.196.198-.328.066-.131.033-.246-.016-.346-.05-.1-.4-.96-.55-1.3-.146-.35-.294-.3-.404-.3-.105-.005-.226-.005-.347-.005-.12 0-.317.045-.483.225-.166.18-.635.62-.635 1.517s.652 1.76 1.054 2.222c.402.463 2.508 3.82 6.07 5.36.85.367 1.513.587 2.03.75.86.273 1.64.234 2.26.14.69-.104 1.513-.619 1.723-1.217.21-.6.21-1.115.147-1.218-.063-.103-.228-.163-.424-.263" />
              </svg>
              Whatsapp
              <ArrowRight size={20} className="ms-1" style={{ transition: 'transform 0.3s ease' }} />
            </a>
          </div>
        </div>
      </div>

      {/* Add inline styles for animations to make it lively */}
      <style dangerouslySetInnerHTML={{
        __html: `
        .promo-image-container:hover .promo-image-animated {
          transform: scale(1.05) translateY(-10px);
        }
        .promo-btn-animated {
          transition: all 0.3s ease;
        }
        .promo-btn-animated:hover {
          transform: translateY(-3px);
          box-shadow: 0 10px 20px rgba(0, 102, 255, 0.3) !important;
        }
        .promo-wa-link:hover {
          color: #25D366 !important;
        }
        .promo-wa-link:hover svg {
          transform: scale(1.1);
          transition: transform 0.3s ease;
        }
        .promo-wa-link:hover .ms-1 {
          transform: translateX(5px);
        }
        @keyframes float {
          0% { transform: translateY(0px); }
          50% { transform: translateY(-20px); }
          100% { transform: translateY(0px); }
        }
        .promo-shape-1 {
          animation: float 6s ease-in-out infinite;
        }
        .promo-shape-2 {
          animation: float 8s ease-in-out infinite reverse;
        }
      `}} />
    </div>
  );
};

export default PromoSection;
