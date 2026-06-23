'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, MapPin } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';

interface VerifiedPropertiesProps {
  items: Property[];
  loading: boolean;
  cityName: string;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const VerifiedProperties: React.FC<VerifiedPropertiesProps> = ({
  items,
  loading,
  cityName,
  formatPrice,
  getPropertyTypeLabel,
}) => {
  if (!loading && items.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-3">
        <div>
          <h2 className="h4 fw-bold text-dark m-0">Verified Properties</h2>
          <p className="text-muted small m-0">Admin-verified listings in {cityName}</p>
        </div>
        <Link
          href="/search?is_verified_property=1"
          className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold"
        >
          <span>See All</span>
          <ChevronRight size={16} />
        </Link>
      </div>

      <div className="nb-scroll-wrapper">
        <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left">
          <ChevronLeft size={24} />
        </button>
        <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right">
          <ChevronRight size={24} />
        </button>
        <div className="nb-horizontal-scroll">
          {loading && (
            <div className="text-muted small py-3">Loading verified properties...</div>
          )}
          {!loading &&
            items.slice(0, 8).map((proj) => {
              const imgUrl =
                proj.thumbnail_url ||
                (proj.image_urls && proj.image_urls.length > 0
                  ? proj.image_urls[0]
                  : 'https://placehold.co/400x300?text=No+Image');
              return (
                <Link
                  key={proj.id}
                  href={`/property/${proj.slug}`}
                  className="text-decoration-none d-block flex-shrink-0"
                >
                  <div
                    className="card border-0 shadow-sm rounded-4 overflow-hidden flex-shrink-0 nb-insight-card-hover"
                    style={{ width: '260px', marginRight: '1rem' }}
                  >
                    <div className="position-relative" style={{ height: '140px' }}>
                      <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                      <span
                        className="position-absolute top-0 start-0 m-2 badge bg-success text-white text-uppercase"
                        style={{ fontSize: '0.65rem' }}
                      >
                        ✓ Verified
                      </span>
                    </div>
                    <div
                      className="p-3 bg-white d-flex flex-column justify-content-between"
                      style={{ height: '120px' }}
                    >
                      <div>
                        <h3 className="h6 fw-bold text-dark m-0 text-truncate">{proj.title}</h3>
                        <p className="text-secondary m-0 text-truncate" style={{ fontSize: '0.75rem' }}>
                          {proj.bedrooms ? `${proj.bedrooms} BHK ` : ''}
                          {getPropertyTypeLabel(proj.property_type)}
                        </p>
                        <p className="text-muted m-0 text-truncate" style={{ fontSize: '0.7rem' }}>
                          <MapPin size={10} className="d-inline me-1" />
                          {proj.locality || proj.city_name}
                        </p>
                      </div>
                      <div className="fw-bold nb-text-brand pt-2 border-top" style={{ fontSize: '0.85rem' }}>
                        {formatPrice(proj.price)}
                      </div>
                    </div>
                  </div>
                </Link>
              );
            })}
        </div>
      </div>
    </div>
  );
};

export default VerifiedProperties;
