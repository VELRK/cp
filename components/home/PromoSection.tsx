'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';

interface PromoSectionProps {
  user: any;
  setAuthModalOpen: (val: 'login' | 'register' | null) => void;
}

const PromoSection: React.FC<PromoSectionProps> = ({ user, setAuthModalOpen }) => {
  return (
    <div className="nb-promo-section mb-5 fade-in-up">
      <div className="nb-promo-banner">
        <img
          src="/assets/assets/img/nb-placeholder-property.svg"
          alt="Post Property Free Agent"
          className="nb-promo-banner-img"
        />
        <div className="nb-promo-banner-content">
          <h3 className="nb-promo-banner-title">
            Sell or rent faster at the right price!
          </h3>
          <p className="nb-promo-banner-subtitle">
            List your property now
          </p>
          <div className="nb-promo-banner-actions">
            <Link
              href={user ? '/owner/property/add' : '#'}
              onClick={(e) => {
                if (!user) {
                  e.preventDefault();
                  setAuthModalOpen('login');
                }
              }}
              className="nb-promo-banner-btn text-decoration-none"
            >
              Post Property, It's FREE
            </Link>
            <a
              href="https://wa.me/919999999999?text=I%20want%20to%20list%20my%20property%20for%20free"
              target="_blank"
              rel="noopener noreferrer"
              className="nb-promo-banner-whatsapp text-decoration-none"
            >
              <svg width="20" height="20" fill="currentColor" className="bi bi-whatsapp me-2" viewBox="0 0 16 16">
                <path d="M13.601 2.326A7.85 7.85 0 0 0 8 0a7.85 7.85 0 0 0-7.852 7.852c0 1.51.417 2.99 1.208 4.3l-.861 3.15 3.255-.853a7.85 7.85 0 0 0 3.758.974h.001c4.341 0 7.863-3.522 7.863-7.863a7.85 7.85 0 0 0-2.266-5.556m-5.602 11.233c-1.393 0-2.756-.372-3.948-1.077l-.283-.168-1.945.51.519-1.898-.184-.294A6.55 6.55 0 0 1 1.776 7.852c0-3.619 2.946-6.565 6.566-6.565 1.753 0 3.4.682 4.64 1.922 1.24 1.24 1.922 2.9 1.92 4.64 0 3.62-2.947 6.565-6.565 6.565m3.56-4.93c-.197-.1-.197-.1-.363-.18-.167-.08-.348-.167-.533-.255-.185-.088-.308-.068-.4.043-.092.11-.355.445-.436.538-.08.093-.16.103-.357.004a4.5 4.5 0 0 1-1.32-.814 4.86 4.86 0 0 1-1.026-1.28c-.114-.196-.012-.302.086-.4.088-.088.197-.23.296-.346.099-.115.132-.196.198-.328.066-.131.033-.246-.016-.346-.05-.1-.4-.96-.55-1.3-.146-.35-.294-.3-.404-.3-.105-.005-.226-.005-.347-.005-.12 0-.317.045-.483.225-.166.18-.635.62-.635 1.517s.652 1.76 1.054 2.222c.402.463 2.508 3.82 6.07 5.36.85.367 1.513.587 2.03.75.86.273 1.64.234 2.26.14.69-.104 1.513-.619 1.723-1.217.21-.6.21-1.115.147-1.218-.063-.103-.228-.163-.424-.263" />
              </svg>
              Post via Whatsapp <ArrowRight size={16} className="ms-1 d-inline" />
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PromoSection;
