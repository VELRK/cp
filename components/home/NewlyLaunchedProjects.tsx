'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';

interface NewlyLaunchedProjectsProps {
  items: Property[];
  loading: boolean;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const NewlyLaunchedProjects: React.FC<NewlyLaunchedProjectsProps> = ({
  items,
  loading,
  formatPrice,
  getPropertyTypeLabel
}) => {
  if (!loading && items.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="p-4 rounded-4" style={{ backgroundColor: '#f9f6f0' }}>
        <div className="d-flex align-items-center mb-4">
          <div className="me-3">
            <img src="https://img.icons8.com/color/48/city-buildings.png" alt="Buildings" width="36" height="36" />
          </div>
          <div>
            <h2 className="h4 fw-bold text-dark m-0 d-flex align-items-center gap-2">
              Newly launched projects
            </h2>
            <p className="text-muted small m-0">Less upfront payment</p>
          </div>
        </div>

        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
          <div className="nb-horizontal-scroll pb-2" style={{ paddingLeft: '5px' }}>
            {items.slice(0, 5).map((proj, i) => {
              const imgUrl = proj.thumbnail_url || (proj.image_urls && proj.image_urls.length > 0 ? proj.image_urls[0] : 'https://placehold.co/400x300?text=No+Image');
              return (
                <Link key={`new-launch-${proj.id}`} href={`/property/${proj.slug}`} className="text-decoration-none d-block flex-shrink-0" style={{ marginRight: '1rem' }}>
                  <div className="card border-0 shadow-sm rounded-4 bg-white position-relative" style={{ width: '420px' }}>

                    {/* Top Tag */}
                    <div className="position-absolute" style={{ top: '15px', left: '-5px', zIndex: 2 }}>
                      <div className="text-white fw-bold px-3 py-1 text-uppercase" style={{ backgroundColor: '#8b0000', fontSize: '0.7rem', clipPath: 'polygon(0 0, 100% 0, 90% 50%, 100% 100%, 0 100%)', boxShadow: '2px 2px 5px rgba(0,0,0,0.1)', letterSpacing: '0.5px' }}>
                        NEW {i % 2 === 0 ? 'ARRIVAL' : 'LAUNCH'}
                      </div>
                      {/* Fold effect corner */}
                      <div style={{ width: '5px', height: '6px', backgroundColor: '#5c0000', position: 'absolute', bottom: '-6px', left: '0', clipPath: 'polygon(100% 0, 100% 100%, 0 0)' }}></div>
                    </div>

                    <div className="p-3 pt-5 pb-3 d-flex gap-3 align-items-start position-relative z-1">
                      {/* Circular Image & RERA badge */}
                      <div className="position-relative flex-shrink-0 mt-2 ms-2">
                        <div className="rounded-circle overflow-hidden shadow-sm" style={{ width: '70px', height: '70px', border: '1px solid #eaeaea' }}>
                          <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                        </div>
                        <div className="position-absolute text-white fw-bold px-2 py-0.5 rounded text-uppercase text-center" style={{ backgroundColor: '#2f4f4f', bottom: '-8px', left: '50%', transform: 'translateX(-50%)', fontSize: '0.55rem', border: '2px solid white', whiteSpace: 'nowrap' }}>
                          <span className="text-info d-inline-block me-1" style={{ fontSize: '0.65rem' }}>✓</span> RERA
                        </div>
                      </div>

                      {/* Project Info */}
                      <div className="flex-grow-1 overflow-hidden pt-1">
                        <h3 className="h6 fw-bold text-dark mb-1 text-truncate w-100">{proj.title}</h3>
                        <p className="text-muted small mb-2 text-truncate w-100">{proj.locality || proj.city_name}</p>
                        <div className="d-flex align-items-center gap-1 mb-2 text-truncate w-100">
                          <span className="fw-bold text-dark" style={{ fontSize: '0.9rem' }}>{formatPrice(proj.price)} {proj.price > 100000 ? '- ' + formatPrice(proj.price * 1.5) : ''}</span>
                          <span className="text-muted" style={{ fontSize: '0.8rem' }}>|</span>
                          <span className="text-muted text-truncate" style={{ fontSize: '0.8rem' }}>{proj.bedrooms ? `${proj.bedrooms}, ` : ''}{proj.bedrooms ? proj.bedrooms + 1 : 3} BHK {getPropertyTypeLabel(proj.property_type)}</span>
                        </div>
                        {/* <p className="m-0 fw-semibold text-truncate w-100" style={{ fontSize: '0.75rem', color: '#3c763d' }}>
                          {8.3 + i * 0.4}% price increase in last 3 months in {proj.locality || proj.city_name}
                        </p> */}
                      </div>
                    </div>

                    {/* Dotted Divider */}
                    <div className="position-relative w-100">
                      <div style={{ borderTop: '1px dashed #cccccc', margin: '0 15px' }}></div>
                      <div className="position-absolute" style={{ width: '16px', height: '16px', borderRadius: '50%', backgroundColor: '#f9f6f0', left: '-8px', top: '-8px' }}></div>
                      <div className="position-absolute" style={{ width: '16px', height: '16px', borderRadius: '50%', backgroundColor: '#f9f6f0', right: '-8px', top: '-8px' }}></div>
                    </div>

                    {/* Footer */}
                    <div className="p-3 bg-white rounded-bottom-4 d-flex justify-content-between align-items-center">
                      <div className="d-flex align-items-center gap-2">
                        <span className="nb-text-brand" style={{ transform: 'rotate(-45deg)' }}>
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.41l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.41zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z" /></svg>
                        </span>
                        <div className="text-secondary" style={{ fontSize: '0.75rem', lineHeight: '1.3' }}>
                          Get preferred options<br />
                          <strong className="text-dark">@zero brokerage</strong>
                        </div>
                      </div>
                      <button className="btn btn-primary btn-sm fw-bold px-3 py-1.5" style={{ backgroundColor: '#1a365d', border: 'none', borderRadius: '6px' }} onClick={(e) => { e.preventDefault(); e.stopPropagation(); }}>
                        View Number
                      </button>
                    </div>

                  </div>
                </Link>
              );
            })}
            {loading && (
              <div className="text-muted small py-3">Loading projects...</div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default NewlyLaunchedProjects;
