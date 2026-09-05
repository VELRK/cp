'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronRight, ChevronLeft, Heart, Bed, Bath, Grid, MapPin, Star, Award } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';
import { toFrontendAssetUrl } from '@/lib/cityImages';

interface BestRatedPropertiesProps {
  items: Property[];
  loading: boolean;
  wishlistedIds: number[];
  cityName: string;
  handleWishlistToggle: (e: React.MouseEvent, id: number) => void;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const BestRatedProperties: React.FC<BestRatedPropertiesProps> = ({
  items,
  loading,
  wishlistedIds,
  cityName,
  handleWishlistToggle,
  formatPrice,
  getPropertyTypeLabel,
}) => {
  if (!loading && items.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-3">
        <div className="d-flex align-items-center gap-3">
          <div
            className="d-flex align-items-center justify-content-center shadow-sm rounded-3 flex-shrink-0"
            style={{
              width: '42px',
              height: '42px',
              background: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
              color: '#ffffff',
            }}
          >
            <Star size={22} fill="#ffffff" />
          </div>
          <div>
            <h2 className="h4 fw-bold text-dark m-0 d-flex align-items-center gap-2">
              Best Rated Properties
              <span
                className="badge fw-semibold"
                style={{
                  backgroundColor: '#fef3c7',
                  color: '#b45309',
                  fontSize: '0.72rem',
                  letterSpacing: '0.4px',
                }}
              >
                TOP VALUE
              </span>
            </h2>
            <p className="text-muted small m-0">Highest rated properties with verified pricing in {cityName || 'All Cities'}</p>
          </div>
        </div>
        <Link
          href="/search?tags_best_rate_localities=1"
          className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold"
        >
          <span>See All</span>
          <ChevronRight size={16} />
        </Link>
      </div>

      {loading ? (
        <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
          <div className="spinner-border nb-text-brand" role="status">
            <span className="visually-hidden">Loading best rated properties...</span>
          </div>
        </div>
      ) : (
        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left">
            <ChevronLeft size={24} />
          </button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right">
            <ChevronRight size={24} />
          </button>
          <div className="nb-horizontal-scroll">
            {items.map((p) => {
              const imagesList = Array.isArray(p.image_urls)
                ? p.image_urls
                : typeof p.images === 'string' && p.images.startsWith('[')
                  ? (() => {
                      try {
                        return JSON.parse(p.images);
                      } catch {
                        return [];
                      }
                    })()
                  : Array.isArray(p.images)
                    ? p.images
                    : [];
              const rawThumb = p.thumbnail_url || imagesList[0] || '';
              const thumbnail = rawThumb ? toFrontendAssetUrl(rawThumb) : '';
              const detailUrl = `/property/${p.slug || p.id}`;
              const isLiked = wishlistedIds.includes(p.id);

              return (
                <div key={p.id} className="nb-classic-property-card-wrap">
                  <div className="nb-classic-card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                    <div className="nb-classic-card-img-container position-relative">
                      <Link href={detailUrl}>
                        {thumbnail ? (
                          <img
                            src={thumbnail}
                            alt={p.title}
                            className="nb-classic-card-img"
                            onError={(e) => {
                              (e.target as HTMLImageElement).src =
                                'https://placehold.co/320x200/f3f4f6/9ca3af?text=Property';
                            }}
                          />
                        ) : (
                          <div className="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted small">
                            <span>No Photo</span>
                          </div>
                        )}
                      </Link>

                      {/* Best Rated Star Badge */}
                      <span
                        className="position-absolute top-0 start-0 m-2 badge d-inline-flex align-items-center gap-1 shadow-sm fw-bold"
                        style={{
                          background: 'linear-gradient(135deg, #f59e0b 0%, #b45309 100%)',
                          color: '#fff',
                          fontSize: '0.72rem',
                          padding: '5px 10px',
                          borderRadius: '20px',
                          zIndex: 3,
                        }}
                      >
                        <Star size={12} fill="#fff" /> Best Rated
                      </span>

                      <div className="nb-classic-card-price-overlay">
                        {formatPrice(p.price)}
                      </div>

                      <span
                        className={`nb-classic-card-badge nb-classic-card-badge--${p.listing_type}`}
                        style={{ top: '38px' }}
                      >
                        For {p.listing_type === 'rent' ? 'Rent' : 'Sale'}
                      </span>

                      <button
                        type="button"
                        className={`nb-classic-card-wishlist ${isLiked ? 'active' : ''}`}
                        onClick={(e) => handleWishlistToggle(e, p.id)}
                        aria-label="Add to wishlist"
                      >
                        <Heart size={14} fill={isLiked ? '#ef4444' : 'none'} />
                      </button>
                    </div>

                    <div className="nb-classic-card-body p-3 bg-white">
                      <div>
                        <h3 className="nb-classic-card-title text-truncate mb-1" title={p.title}>
                          <Link href={detailUrl} className="text-decoration-none text-dark fw-bold">
                            {p.title}
                          </Link>
                        </h3>
                        <p className="nb-classic-card-loc text-truncate mb-2 text-muted small">
                          <MapPin size={12} className="inline-block me-1 text-secondary" />
                          {p.locality ? `${p.locality}, ` : ''}{p.city_name || 'Coimbatore'}
                        </p>

                        <div className="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                          <span
                            className="badge rounded-pill d-inline-flex align-items-center gap-1"
                            style={{
                              backgroundColor: '#ecfdf5',
                              color: '#065f46',
                              fontSize: '0.7rem',
                              padding: '3px 8px',
                            }}
                          >
                            <Award size={11} /> Competitive Rate
                          </span>
                          <span
                            className="badge rounded-pill"
                            style={{
                              backgroundColor: '#f8fafc',
                              color: '#64748b',
                              border: '1px solid #e2e8f0',
                              fontSize: '0.7rem',
                            }}
                          >
                            {getPropertyTypeLabel(p.property_type)}
                          </span>
                        </div>
                      </div>

                      <div className="nb-classic-card-specs pt-1">
                        {p.bedrooms ? (
                          <div className="nb-classic-card-spec-item" title={`${p.bedrooms} Bedrooms`}>
                            <Bed size={14} />
                            <span>{p.bedrooms} BHK</span>
                          </div>
                        ) : null}
                        {p.bathrooms ? (
                          <div className="nb-classic-card-spec-item" title={`${p.bathrooms} Bathrooms`}>
                            <Bath size={14} />
                            <span>{p.bathrooms} Baths</span>
                          </div>
                        ) : null}
                        {p.area_sqft ? (
                          <div className="nb-classic-card-spec-item" title={`${p.area_sqft} sq ft`}>
                            <Grid size={14} />
                            <span>{Number(p.area_sqft).toLocaleString('en-IN')} sqft</span>
                          </div>
                        ) : null}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

export default BestRatedProperties;
