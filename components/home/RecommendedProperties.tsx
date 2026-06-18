'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronRight, ChevronLeft, Heart, Bed, Bath, Grid, MapPin } from 'lucide-react';
import { Property } from '@/components/property/PropertyCard';

interface RecommendedPropertiesProps {
  featured: Property[];
  loadingFeatured: boolean;
  wishlistedIds: number[];
  cityName: string;
  handleWishlistToggle: (e: React.MouseEvent, id: number) => void;
  formatPrice: (price: number) => string;
  getPropertyTypeLabel: (type: string) => string;
}

const RecommendedProperties: React.FC<RecommendedPropertiesProps> = ({
  featured,
  loadingFeatured,
  wishlistedIds,
  cityName,
  handleWishlistToggle,
  formatPrice,
  getPropertyTypeLabel
}) => {
  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-3">
        <div>
          <h2 className="h4 fw-bold text-dark m-0">Recommended Properties</h2>
          <p className="text-muted small m-0">Curated premium properties in {cityName}</p>
        </div>
        <Link href="/search" className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold">
          <span>See All</span>
          <ChevronRight size={16} />
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
                <div key={p.id} className="nb-classic-property-card-wrap">
                  <div className="nb-classic-card">
                    <div className="nb-classic-card-img-container">
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

                      <div className="nb-classic-card-price-overlay">
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
                        <Heart size={14} fill={isLiked ? '#ef4444' : 'none'} />
                      </button>
                    </div>

                    <div className="nb-classic-card-body">
                      <div>
                        <h3 className="nb-classic-card-title text-truncate" title={p.title}>
                          <Link href={`/property-detail/${p.slug}`} className="text-decoration-none text-dark fw-bold">
                            {p.bedrooms ? `${p.bedrooms} BHK ` : ''}{getPropertyTypeLabel(p.property_type)}
                          </Link>
                        </h3>
                        <p className="nb-classic-card-loc text-truncate">
                          <MapPin size={12} className="me-1 d-inline" /> In {p.locality}
                        </p>
                      </div>

                      <div>
                        <div className="nb-classic-card-stats">
                          {p.bedrooms !== null && (
                            <span className="nb-classic-card-stat-item">
                              <Bed size={13} /> {p.bedrooms} BHK
                            </span>
                          )}
                          {p.bathrooms !== null && (
                            <span className="nb-classic-card-stat-item">
                              <Bath size={13} /> {p.bathrooms} Bath
                            </span>
                          )}
                          {p.area_sqft !== null && (
                            <span className="nb-classic-card-stat-item">
                              <Grid size={13} /> {p.area_sqft} sqft
                            </span>
                          )}
                        </div>

                        <div className="nb-classic-card-footer">
                          <span className="nb-classic-card-author">
                            Posted by <strong>Owner</strong>
                          </span>
                          <span>Recently</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
            {featured.length === 0 && (
              <div className="w-100 text-center py-4 bg-white border text-muted">
                No recommended properties available.
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default RecommendedProperties;
