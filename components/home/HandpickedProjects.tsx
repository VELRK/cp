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
                <div className="nb-handpicked-card nb-insight-card-hover" style={{ borderRadius: '16px', marginRight: '1rem' }}>
                  <div className="nb-handpicked-img-wrap">
                    <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                    {proj.is_featured === 1 && (
                      <span className="nb-handpicked-badge">Featured</span>
                    )}
                    <div className="nb-handpicked-logo">
                      <img src="https://img.icons8.com/color/96/real-estate.png" alt="Property Icon" />
                    </div>
                  </div>
                  <div className="nb-handpicked-body bg-white text-dark">
                    <h3 className="h6 fw-bold mb-1 text-truncate">{proj.title}</h3>
                    <p className="text-muted small mb-2 text-truncate">{proj.property_type.replace('_', ' ')}, {proj.locality || proj.city_name}</p>
                    <div className="fw-bold nb-text-brand mb-0" style={{ fontSize: '0.9rem' }}>
                      ₹{proj.price.toLocaleString('en-IN')}
                    </div>
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
