'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';

interface HandpickedProjectsProps {
  featured: Property[];
  loadingFeatured: boolean;
}

const HandpickedProjects: React.FC<HandpickedProjectsProps> = ({
  featured,
  loadingFeatured
}) => {
  return (
    <div className="mb-5 fade-in-up">
      <h2 className="h4 fw-bold text-dark mb-3">Handpicked Premium Projects</h2>
      <div className="nb-scroll-wrapper">
        <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
        <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
        <div className="nb-horizontal-scroll">
          {featured.slice(0, 5).map((proj) => {
            const imgUrl = proj.thumbnail_url || (proj.image_urls && proj.image_urls.length > 0 ? proj.image_urls[0] : 'https://placehold.co/400x300?text=No+Image');
            return (
              <Link key={proj.id} href={`/property-detail/${proj.slug}`} className="text-decoration-none d-block flex-shrink-0">
                <div 
                  className="card shadow-sm h-100 overflow-hidden group" 
                  style={{ 
                    width: '280px', 
                    marginRight: '1rem', 
                    borderRadius: '16px', 
                    border: 'none',
                    height: '340px',
                    position: 'relative'
                  }}
                >
                  <img 
                    src={imgUrl} 
                    alt={proj.title} 
                    className="w-100 h-100 object-fit-cover" 
                    style={{ transition: 'transform 0.4s ease' }} 
                    onMouseOver={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                    onMouseOut={(e) => e.currentTarget.style.transform = 'scale(1)'}
                  />
                  
                  {/* Gradient Overlay */}
                  <div 
                    className="position-absolute top-0 start-0 w-100 h-100" 
                    style={{ background: 'linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.9) 100%)', pointerEvents: 'none' }}
                  ></div>

                  {/* Badges */}
                  <div className="position-absolute top-0 start-0 p-3 w-100 d-flex justify-content-between align-items-start" style={{ pointerEvents: 'none' }}>
                    {proj.is_featured === 1 ? (
                      <span className="badge bg-primary px-2 py-1" style={{ fontSize: '0.75rem', fontWeight: '600' }}>Featured</span>
                    ) : (
                      <span></span>
                    )}
                    <div className="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style={{ width: '32px', height: '32px' }}>
                      <img src="https://img.icons8.com/color/96/real-estate.png" alt="Property Icon" style={{ width: '20px', height: '20px', objectFit: 'contain' }} />
                    </div>
                  </div>

                  {/* Content at Bottom */}
                  <div className="position-absolute bottom-0 start-0 w-100 p-3 d-flex flex-column text-white" style={{ pointerEvents: 'none' }}>
                    <div className="d-flex justify-content-between align-items-end mb-1">
                      <h3 className="h6 fw-bold mb-0 text-truncate text-white" style={{ textShadow: '0 2px 4px rgba(0,0,0,0.5)', maxWidth: '75%' }}>
                        {proj.title}
                      </h3>
                      <div className="fw-bold text-white text-end" style={{ fontSize: '1rem', textShadow: '0 2px 4px rgba(0,0,0,0.5)' }}>
                        ₹{proj.price.toLocaleString('en-IN')}
                      </div>
                    </div>
                    <p className="small mb-0 text-truncate text-white-50" style={{ fontSize: '0.8rem' }}>
                      {proj.property_type.replace('_', ' ')}, {proj.locality || proj.city_name}
                    </p>
                  </div>
                </div>
              </Link>
            );
          })}
          {featured.length === 0 && !loadingFeatured && (
            <div className="text-muted small py-3">No premium properties found.</div>
          )}
          {loadingFeatured && (
            <div className="text-muted small py-3">Loading properties...</div>
          )}
        </div>
      </div>
    </div>
  );
};

export default HandpickedProjects;
