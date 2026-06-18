'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, MapPin, Bed, Bath, Grid, Heart } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';

interface FeaturedPropertiesProps {
  featured: Property[];
  loadingFeatured: boolean;
  wishlistedIds: number[];
  handleWishlistToggle: (e: React.MouseEvent, id: number) => void;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const FeaturedProperties: React.FC<FeaturedPropertiesProps> = ({
  featured,
  loadingFeatured,
  wishlistedIds,
  handleWishlistToggle,
  formatPrice,
  getPropertyTypeLabel
}) => {
  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 className="h4 fw-bold text-dark m-0">Featured Properties</h2>
          <p className="text-muted small m-0">Top-rated premium listings in a list view</p>
        </div>
        <Link href="/search?sort=featured" className="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
          View all Featured
        </Link>
      </div>

      {loadingFeatured ? (
        <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
          <div className="spinner-border nb-text-brand" role="status">
            <span className="visually-hidden">Loading properties...</span>
          </div>
        </div>
      ) : (
        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
          <div className="nb-horizontal-scroll">
            {featured.map((p) => {
              const imagesList = Array.isArray(p.image_urls) ? p.image_urls : [];
              const thumbnail = p.thumbnail_url || imagesList[0] || '';
              const isLiked = wishlistedIds.includes(p.id);
              return (
                <div key={`featured-${p.id}`} className="nb-classic-property-card-wrap" style={{ width: '320px' }}>
                  <div className="nb-classic-card h-100 shadow-sm">
                    <div className="nb-classic-card-img-container" style={{ height: '200px' }}>
                      <Link href={`/property-detail/${p.slug}`}>
                        {thumbnail ? (
                          <img
                            src={thumbnail}
                            alt={p.title}
                            className="nb-classic-card-img"
                          />
                        ) : (
                          <div className="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted small">
                            <span>No Photo</span>
                          </div>
                        )}
                      </Link>

                      <div className="nb-classic-card-price-overlay px-3 py-2" style={{ fontSize: '1.25rem' }}>
                        {formatPrice(p.price)}
                      </div>

                      <span className={`nb-classic-card-badge nb-classic-card-badge--${p.listing_type}`}>
                        For {p.listing_type === 'rent' ? 'Rent' : 'Sale'}
                      </span>

                      {p.is_featured === 1 && (
                        <span className="nb-classic-card-featured">
                          FEATURED
                        </span>
                      )}

                      <button
                        type="button"
                        className={`nb-classic-card-wishlist ${isLiked ? 'active' : ''}`}
                        onClick={(e) => handleWishlistToggle(e, p.id)}
                        aria-label="Add to wishlist"
                      >
                        <Heart size={16} fill={isLiked ? '#ef4444' : 'none'} />
                      </button>
                    </div>

                    <div className="nb-classic-card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                      <div className="mb-3">
                        <h3 className="nb-classic-card-title text-truncate" title={p.title} style={{ fontSize: '1.1rem' }}>
                          <Link href={`/property-detail/${p.slug}`} className="text-decoration-none text-dark fw-bold">
                            {p.bedrooms ? `${p.bedrooms} BHK ` : ''}{getPropertyTypeLabel(p.property_type)}
                          </Link>
                        </h3>
                        <p className="nb-classic-card-loc text-truncate m-0 mt-1">
                          <MapPin size={14} className="me-1 d-inline text-muted" /> In {p.locality}
                        </p>
                      </div>

                      <div>
                        <div className="nb-classic-card-stats mb-3 pb-3 border-bottom d-flex gap-3">
                          {p.bedrooms !== null && (
                            <span className="nb-classic-card-stat-item fs-6">
                              <Bed size={16} className="me-1 nb-text-brand" /> {p.bedrooms} BHK
                            </span>
                          )}
                          {p.bathrooms !== null && (
                            <span className="nb-classic-card-stat-item fs-6">
                              <Bath size={16} className="me-1 nb-text-brand" /> {p.bathrooms} Bath
                            </span>
                          )}
                          {p.area_sqft !== null && (
                            <span className="nb-classic-card-stat-item fs-6">
                              <Grid size={16} className="me-1 nb-text-brand" /> {p.area_sqft} sqft
                            </span>
                          )}
                        </div>

                        <div className="nb-classic-card-footer m-0 pt-0 border-0 d-flex justify-content-between align-items-center">
                          <span className="nb-classic-card-author">
                            Posted by <strong>Owner</strong>
                          </span>
                          <Link href={`/property-detail/${p.slug}`} className="btn btn-primary btn-sm fw-semibold rounded-pill px-3">
                            Details
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
            {featured.length === 0 && (
              <div className="w-100 text-center py-4 bg-white border text-muted">
                No featured properties available.
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default FeaturedProperties;
