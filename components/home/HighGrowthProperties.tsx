'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronRight, ChevronLeft, Heart, Bed, Bath, Grid, MapPin, TrendingUp, Zap } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';
import { toFrontendAssetUrl } from '@/lib/cityImages';

interface HighGrowthPropertiesProps {
  items: Property[];
  loading: boolean;
  wishlistedIds: number[];
  cityName: string;
  handleWishlistToggle: (e: React.MouseEvent, id: number) => void;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const HighGrowthProperties: React.FC<HighGrowthPropertiesProps> = ({
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
              background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
              color: '#ffffff',
            }}
          >
            <TrendingUp size={22} />
          </div>
          <div>
            <h2 className="h4 fw-bold text-dark m-0 d-flex align-items-center gap-2">
              High Growth Localities
              <span
                className="badge fw-semibold"
                style={{
                  backgroundColor: '#d1fae5',
                  color: '#065f46',
                  fontSize: '0.72rem',
                  letterSpacing: '0.4px',
                }}
              >
                HIGH APPRECIATION
              </span>
            </h2>
            <p className="text-muted small m-0">Fast-developing corridors with high ROI potential in {cityName || 'All Cities'}</p>
          </div>
        </div>
        <Link
          href="/search?tags_high_growth_localities=1"
          className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold"
        >
          <span>See All</span>
          <ChevronRight size={16} />
        </Link>
      </div>

      {loading ? (
        <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
          <div className="spinner-border nb-text-brand" role="status">
            <span className="visually-hidden">Loading high growth properties...</span>
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

                      {/* High Growth Badge */}
                      <span
                        className="position-absolute top-0 start-0 m-2 badge d-inline-flex align-items-center gap-1 shadow-sm fw-bold"
                        style={{
                          background: 'linear-gradient(135deg, #10b981 0%, #047857 100%)',
                          color: '#fff',
                          fontSize: '0.72rem',
                          padding: '5px 10px',
                          borderRadius: '20px',
                          zIndex: 3,
                        }}
                      >
                        <TrendingUp size={12} /> High Growth
                      </span>

                      <div className="nb-classic-card-price-overlay">
                        {formatPrice(p.price)}
                      </div>

                      <span
                        className={`nb-classic-card-badge nb-classic-card-badge--${p.listing_type}`}
                        style={{ top: '38px' }}
                      >
                        For Sale
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

                    <div className="nb-classic-card-body p-3 bg-white d-flex flex-column h-100">
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

                        <div className="d-flex align-items-center gap-2 mb-3">
                          <span
                            className="badge rounded-pill d-inline-flex align-items-center gap-1"
                            style={{
                              backgroundColor: '#eff6ff',
                              color: '#1e40af',
                              fontSize: '0.7rem',
                              padding: '3px 8px',
                            }}
                          >
                            <Zap size={11} /> High ROI Potential
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

                      <div className="pt-2 border-top mt-1">
                        <div className="nb-classic-card-specs d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 mb-2" style={{ backgroundColor: '#f8fafc', border: '1px solid #f1f5f9' }}>
                          {p.bedrooms ? (
                            <div className="nb-classic-card-spec-item d-flex align-items-center gap-1" title={`${p.bedrooms} Bedrooms`}>
                              <Bed size={13} className="text-primary opacity-75 flex-shrink-0" />
                              <span className="fw-bold text-dark" style={{ fontSize: '0.75rem' }}>{p.bedrooms}</span>
                              <span className="text-muted" style={{ fontSize: '0.7rem' }}>BHK</span>
                            </div>
                          ) : null}
                          {p.bedrooms && (p.bathrooms || p.area_sqft) ? (
                            <span className="text-muted opacity-25" style={{ fontSize: '0.7rem' }}>|</span>
                          ) : null}
                          {p.bathrooms ? (
                            <div className="nb-classic-card-spec-item d-flex align-items-center gap-1" title={`${p.bathrooms} Bathrooms`}>
                              <Bath size={13} className="text-primary opacity-75 flex-shrink-0" />
                              <span className="fw-bold text-dark" style={{ fontSize: '0.75rem' }}>{p.bathrooms}</span>
                              <span className="text-muted" style={{ fontSize: '0.7rem' }}>Baths</span>
                            </div>
                          ) : null}
                          {p.bathrooms && p.area_sqft ? (
                            <span className="text-muted opacity-25" style={{ fontSize: '0.7rem' }}>|</span>
                          ) : null}
                          {p.area_sqft ? (
                            <div className="nb-classic-card-spec-item d-flex align-items-center gap-1" title={`${p.area_sqft} sq ft`}>
                              <Grid size={13} className="text-primary opacity-75 flex-shrink-0" />
                              <span className="fw-bold text-dark" style={{ fontSize: '0.75rem' }}>{Number(p.area_sqft).toLocaleString('en-IN')}</span>
                              <span className="text-muted" style={{ fontSize: '0.7rem' }}>sqft</span>
                            </div>
                          ) : null}
                          {!p.bedrooms && !p.bathrooms && !p.area_sqft && (
                            <span className="text-muted small" style={{ fontSize: '0.72rem' }}>Prime Location</span>
                          )}
                        </div>

                        <div className="d-flex align-items-center justify-content-between">
                          <span className="text-muted" style={{ fontSize: '0.72rem' }}>
                            Posted by <strong className="text-dark fw-semibold">Owner</strong>
                          </span>
                          <Link
                            href={detailUrl}
                            className="text-decoration-none fw-bold d-inline-flex align-items-center gap-1 text-primary"
                            style={{ fontSize: '0.75rem' }}
                          >
                            <span>View Details</span>
                            <ChevronRight size={13} />
                          </Link>
                        </div>
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

export default HighGrowthProperties;
