'use client';

import React, { useMemo } from 'react';
import Link from 'next/link';
import { ChevronRight, ChevronLeft, Phone } from 'lucide-react';

interface Property {
  locality: string;
  owner_name?: string | null;
  owner_phone?: string | null;
  owner_user_type?: string | null;
  posted_by?: string;
}

interface RecommendedSellersProps {
  properties: Property[];
}

interface Seller {
  id: string;
  name: string;
  type: 'Agent' | 'Owner' | 'Developer';
  logoInitials: string;
  bgColor: string;
  textColor: string;
  experienceYears: number;
  totalListings: number;
  localities: string[];
}

// Helper to generate consistent colors based on name
const getColorsForName = (name: string) => {
  const colors = [
    { bg: '#e5ddd5', text: '#333' },
    { bg: '#d8c89d', text: '#333' },
    { bg: '#1a365d', text: '#fff' },
    { bg: '#e6e2d3', text: '#333' },
    { bg: '#f1f5f9', text: '#1e293b' },
    { bg: '#fee2e2', text: '#991b1b' },
    { bg: '#e0e7ff', text: '#3730a3' },
    { bg: '#dcfce7', text: '#166534' }
  ];
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
};

const RecommendedSellers: React.FC<RecommendedSellersProps> = ({ properties }) => {
  const sellers = useMemo(() => {
    const sellerMap = new Map<string, Seller>();

    properties.forEach(p => {
      // Determine name
      const name = p.owner_name?.trim() || 'Anonymous Seller';
      
      // Determine type
      let type: 'Agent' | 'Owner' | 'Developer' = 'Owner';
      const postedBy = p.posted_by?.toLowerCase() || '';
      const userType = p.owner_user_type?.toLowerCase() || '';
      if (postedBy.includes('developer') || userType.includes('developer') || postedBy.includes('builder') || userType.includes('builder')) {
        type = 'Developer';
      } else if (postedBy.includes('agent') || userType.includes('agent') || postedBy.includes('broker')) {
        type = 'Agent';
      }

      const id = name.toLowerCase().replace(/\s+/g, '-') + '-' + type;

      if (!sellerMap.has(id)) {
        const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'S';
        const colors = getColorsForName(name);
        
        sellerMap.set(id, {
          id,
          name,
          type,
          logoInitials: initials,
          bgColor: colors.bg,
          textColor: colors.text,
          experienceYears: 2, // User requested static 2 years
          totalListings: 1,
          localities: p.locality ? [p.locality] : []
        });
      } else {
        const existing = sellerMap.get(id)!;
        existing.totalListings += 1;
        if (p.locality && !existing.localities.includes(p.locality)) {
          existing.localities.push(p.locality);
        }
      }
    });

    return Array.from(sellerMap.values()).sort((a, b) => b.totalListings - a.totalListings);
  }, [properties]);

  if (sellers.length === 0) return null;

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-3">
        <div>
          <h2 className="h4 fw-bold text-dark m-0">Recommended sellers</h2>
          <p className="text-muted small m-0">Sellers with complete knowledge about locality</p>
        </div>
      </div>

      <div className="nb-scroll-wrapper position-relative">
        <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left" style={{ zIndex: 10 }}>
          <ChevronLeft size={24} />
        </button>
        <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right" style={{ zIndex: 10 }}>
          <ChevronRight size={24} />
        </button>
        <div className="nb-horizontal-scroll d-flex gap-3 pb-2" style={{ overflowX: 'auto', scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
          {sellers.map((seller) => (
            <div key={seller.id} className="card shadow-sm border-0" style={{ minWidth: '280px', borderRadius: '12px' }}>
              
              {/* Top Banner section */}
              <Link href={`/seller/${seller.id}`} className="text-decoration-none">
                <div 
                  className="d-flex align-items-center justify-content-between p-3 rounded-top"
                  style={{ backgroundColor: seller.bgColor, color: seller.textColor, borderTopLeftRadius: '12px', borderTopRightRadius: '12px', height: '70px' }}
                >
                  <div className="d-flex align-items-center gap-2">
                    <div 
                      className="d-flex align-items-center justify-content-center fw-bold"
                      style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: 'rgba(255,255,255,0.2)', backdropFilter: 'blur(4px)', fontSize: '14px' }}
                    >
                      {seller.logoInitials}
                    </div>
                    <div className="fw-bold text-truncate" style={{ maxWidth: '160px' }}>
                      {seller.name}
                      <div style={{ fontSize: '0.65rem', opacity: 0.8, marginTop: '-2px' }}>{seller.type}</div>
                    </div>
                  </div>
                  <ChevronRight size={18} />
                </div>
              </Link>
              
              <div className="card-body p-3 bg-white" style={{ borderBottomLeftRadius: '12px', borderBottomRightRadius: '12px' }}>
                <div className="d-flex align-items-center gap-3 mb-3 small">
                  <div>
                    <span className="fw-bold">{seller.experienceYears} Yrs</span> <span className="text-muted">Experience</span>
                  </div>
                  <div style={{ width: '1px', height: '14px', backgroundColor: '#e2e8f0' }}></div>
                  <div>
                    <span className="fw-bold">{seller.totalListings}</span> <span className="text-muted">Total listings</span>
                  </div>
                </div>
                
                <div className="d-flex flex-wrap gap-2 mb-3">
                  {seller.localities.map((loc, idx) => (
                    <span key={idx} className="badge rounded-pill fw-normal" style={{ backgroundColor: '#f1f5f9', color: '#64748b', fontSize: '0.7rem', padding: '4px 10px' }}>
                      {loc}
                    </span>
                  ))}
                </div>
                
                <button className="btn w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style={{ color: '#6d28d9', borderColor: '#c4b5fd', backgroundColor: '#fff', borderRadius: '8px', padding: '8px 0' }}>
                  <Phone size={16} />
                  Show Contact
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default RecommendedSellers;
