'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/components/AuthContext';
import api from '@/lib/api';
import { Heart, Image as ImageIcon, MapPin, Bed, Bath, Grid, ArrowRight } from 'lucide-react';

export interface Property {
  id: number;
  title: string;
  slug: string;
  property_type: string;
  listing_type: 'rent' | 'sale';
  price: number;
  price_formatted?: string;
  bedrooms: number;
  bathrooms: number;
  area_sqft: number;
  locality: string;
  city_name?: string;
  city_id?: number;
  is_featured?: number;
  images?: string | string[];
  image_urls?: string[];
  thumbnail_url?: string;
  property_type_label?: string;
}

interface PropertyCardProps {
  property: Property;
}

const PropertyCard: React.FC<PropertyCardProps> = ({ property }) => {
  const { user, setAuthModalOpen } = useAuth();
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [wishlistLoading, setWishlistLoading] = useState(false);
  const [imgLoaded, setImgLoaded] = useState(false);

  const imagesList = Array.isArray(property.image_urls)
    ? property.image_urls
    : typeof property.images === 'string' && property.images.startsWith('[')
      ? (() => {
        try {
          const parsed = JSON.parse(property.images);
          return Array.isArray(parsed) ? parsed : [];
        } catch {
          return [];
        }
      })()
      : [];

  const thumbnail = property.thumbnail_url || imagesList[0] || '';

  useEffect(() => {
    if (user) {
      api.get(`/api/nb/wishlist/check?property_id=${property.id}&userId=${user.id}`)
        .then((res) => {
          if (res.data?.success && res.data.wishlisted) setIsWishlisted(true);
        })
        .catch((e) => console.warn('Wishlist check failed', e));
    } else {
      setIsWishlisted(false);
    }
  }, [user, property.id]);

  const handleWishlistToggle = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (!user) { setAuthModalOpen('login'); return; }
    setWishlistLoading(true);
    try {
      const response = await api.post('/api/nb/wishlist/toggle', {
        property_id: property.id,
        userId: user.id,
      });
      if (response.data?.success) setIsWishlisted(!isWishlisted);
    } catch (error) {
      console.error('Error toggling wishlist:', error);
    } finally {
      setWishlistLoading(false);
    }
  };

  const getListingTypeLabel = () => property.listing_type === 'rent' ? 'Rent' : 'Sale';
  const getPropertyTypeLabel = () => property.property_type_label || property.property_type;
  const formatPrice = (price: number) => property.price_formatted || `₹${price.toLocaleString('en-IN')}`;

  const detailUrl = `/property-detail/${property.slug}`;

  return (
    <>
      <style>{`
        .pc-wrap {
          font-family: 'Inter', 'Segoe UI', sans-serif;
          animation: pc-fadeUp 0.5s cubic-bezier(.16,1,3,1) both;
        }
        @keyframes pc-fadeUp {
          from { opacity: 0; transform: translateY(24px); }
          to   { opacity: 1; transform: translateY(0); }
        }

        .pc-card {
          background: #ffffff;
          border-radius: 16px;
          overflow: hidden;
          border: 1px solid var(--nb-card-border, #e2e8f0);
          box-shadow: 0 4px 20px -2px rgba(11,44,86,0.05);
          transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
          height: 100%;
          display: flex;
          flex-direction: column;
          position: relative;
        }
        .pc-card:hover {
          box-shadow: 0 20px 35px -8px rgba(11,44,86,0.12);
          transform: translateY(-6px);
          border-color: rgba(242, 178, 3, 0.3);
        }

        /* ── IMAGE ZONE ── */
        .pc-img-wrap {
          position: relative;
          overflow: hidden;
          aspect-ratio: 4/3;
          background: #f1f5f9;
          flex-shrink: 0;
        }
        .pc-img {
          width: 100%; height: 100%;
          object-fit: cover;
          display: block;
          transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.35s ease;
          opacity: 0;
        }
        .pc-img.loaded { opacity: 1; }
        .pc-card:hover .pc-img { transform: scale(1.08); }

        .pc-placeholder {
          width: 100%; height: 100%;
          display: flex; flex-direction: column;
          align-items: center; justify-content: center;
          background: #f8fafc;
          color: #94a3b8;
          gap: 8px;
          font-size: 13px;
        }

        /* gradient overlay */
        .pc-overlay {
          position: absolute; inset: 0;
          background: linear-gradient(to top, rgba(11, 44, 86, 0.65) 0%, rgba(11, 44, 86, 0.1) 40%, rgba(0,0,0,0) 70%);
          pointer-events: none;
        }

        /* ── BADGES ── */
        .pc-badge {
          position: absolute;
          top: 14px; left: 14px;
          font-size: 11px; font-weight: 700;
          letter-spacing: .8px; text-transform: uppercase;
          padding: 5px 12px;
          border-radius: 6px;
          line-height: 1.4;
          box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .pc-badge--rent  { background: var(--nb-primary-soft, #eef3fb); color: var(--nb-primary, #0b2c56); }
        .pc-badge--sale  { background: #eaf6ec; color: #1a8a3a; }

        .pc-featured {
          position: absolute;
          top: 14px; right: 52px;
          font-size: 10px; font-weight: 700;
          letter-spacing: .8px; text-transform: uppercase;
          background: linear-gradient(135deg, var(--nb-accent, #f2b203), var(--nb-accent-dark, #d59d02));
          color: var(--nb-primary-dark, #071f3f);
          padding: 5px 12px;
          border-radius: 6px;
          box-shadow: 0 4px 10px rgba(242, 178, 3, 0.25);
        }

        .pc-price {
          position: absolute;
          bottom: 14px; left: 14px;
          font-size: 20px; font-weight: 800;
          color: #ffffff;
          letter-spacing: -.4px;
          text-shadow: 0 2px 8px rgba(11, 44, 86, 0.5);
          line-height: 1;
        }

        /* ── WISHLIST BUTTON ── */
        .pc-wish {
          position: absolute;
          top: 12px; right: 12px;
          width: 34px; height: 34px;
          border-radius: 50%;
          border: none;
          background: rgba(255, 255, 255, 0.9);
          backdrop-filter: blur(8px);
          -webkit-backdrop-filter: blur(8px);
          display: flex; align-items: center; justify-content: center;
          cursor: pointer;
          box-shadow: 0 4px 12px rgba(11, 44, 86, 0.12);
          transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .pc-wish:hover { background: #ffffff; transform: scale(1.15); box-shadow: 0 6px 16px rgba(11, 44, 86, 0.2); }
        .pc-wish.active { background: #fff3f3; }
        .pc-wish:disabled { opacity: 0.6; }

        @keyframes pc-heartPop {
          0%   { transform: scale(1); }
          40%  { transform: scale(1.4); }
          70%  { transform: scale(0.9); }
          100% { transform: scale(1); }
        }
        .pc-wish.active svg { animation: pc-heartPop 0.35s ease; }

        /* ── BODY ── */
        .pc-body {
          padding: 20px;
          display: flex; flex-direction: column;
          flex: 1;
          gap: 0;
        }

        .pc-title {
          font-size: 16px; font-weight: 750;
          color: var(--nb-primary, #0b2c56);
          margin: 0 0 6px;
          line-height: 1.4;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
          transition: color 0.25s ease;
        }
        .pc-title a {
          color: inherit; text-decoration: none;
        }
        .pc-card:hover .pc-title {
          color: var(--nb-accent-dark, #d59d02);
        }

        .pc-loc {
          display: flex; align-items: center; gap: 5px;
          font-size: 13px; color: var(--nb-muted, #64748b);
          margin: 0 0 14px;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pc-loc svg { flex-shrink: 0; color: #94a3b8; }

        /* divider */
        .pc-divider {
          height: 1px;
          background: linear-gradient(to right, var(--nb-card-border, #e2e8f0), transparent);
          margin: 0 0 14px;
        }

        .pc-meta {
          display: flex; gap: 0;
          margin: 0 0 16px;
        }
        .pc-meta-item {
          display: flex; align-items: center; gap: 6px;
          font-size: 13px; color: var(--nb-text, #1e293b); font-weight: 500;
          padding: 4px 8px 4px 0;
          flex: 1;
          border-right: 1px solid var(--nb-card-border, #e2e8f0);
        }
        .pc-meta-item:last-child { border-right: none; padding-right: 0; }
        .pc-meta-item svg { color: var(--nb-primary, #0b2c56); opacity: 0.8; }

        .pc-foot {
          margin-top: auto;
          display: flex; align-items: center; justify-content: space-between;
        }

        .pc-type-pill {
          font-size: 11px; font-weight: 700;
          color: var(--nb-primary, #0b2c56);
          background: var(--nb-primary-soft, #eef3fb);
          border-radius: 20px;
          padding: 4px 12px;
          letter-spacing: .4px;
          text-transform: uppercase;
        }

        .pc-cta {
          display: inline-flex; align-items: center; gap: 6px;
          font-size: 13px; font-weight: 700;
          color: var(--nb-primary, #0b2c56);
          text-decoration: none;
          padding: 6px 14px;
          border-radius: 8px;
          background: var(--nb-primary-soft, #eef3fb);
          transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .pc-cta:hover {
          background: var(--nb-primary, #0b2c56);
          color: #ffffff;
          gap: 9px;
        }
        .pc-cta svg { transition: transform 0.25s; }
        .pc-cta:hover svg { transform: translateX(2px); color: var(--nb-accent, #f2b203) !important; }

        /* skeleton shimmer while image loads */
        .pc-img-skeleton {
          position: absolute; inset: 0;
          background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
          background-size: 200% 100%;
          animation: pc-shimmer 1.5s infinite;
        }
        @keyframes pc-shimmer {
          0%   { background-position: 200% 0; }
          100% { background-position: -200% 0; }
        }
      `}</style>

      <div className="col-md-6 col-lg-4">
        <div className="pc-wrap h-100">
          <article className="pc-card">

            {/* ── IMAGE ZONE ── */}
            <div className="pc-img-wrap">
              {!imgLoaded && <div className="pc-img-skeleton" />}
              <Link href={detailUrl} aria-label={`View ${property.title}`}>
                {thumbnail ? (
                  <img
                    src={thumbnail}
                    className={`pc-img${imgLoaded ? ' loaded' : ''}`}
                    alt={property.title}
                    loading="lazy"
                    onLoad={() => setImgLoaded(true)}
                  />
                ) : (
                  <div className="pc-placeholder">
                    <ImageIcon size={30} />
                    <span>No photo yet</span>
                  </div>
                )}
              </Link>
              <div className="pc-overlay" aria-hidden="true" />

              <span className={`pc-badge pc-badge--${property.listing_type}`}>
                For {getListingTypeLabel()}
              </span>
              {property.is_featured === 1 && (
                <span className="pc-featured">★ Featured</span>
              )}
              <button
                type="button"
                className={`pc-wish${isWishlisted ? ' active' : ''}`}
                onClick={handleWishlistToggle}
                disabled={wishlistLoading}
                aria-label={isWishlisted ? 'Remove from wishlist' : 'Save to wishlist'}
              >
                <Heart size={15} fill={isWishlisted ? '#ef4444' : 'none'} stroke={isWishlisted ? '#ef4444' : '#374151'} />
              </button>
              <span className="pc-price">{formatPrice(property.price)}</span>
            </div>

            {/* ── BODY ── */}
            <div className="pc-body">
              <h3 className="pc-title">
                <Link href={detailUrl}>{property.title}</Link>
              </h3>
              <p className="pc-loc">
                <MapPin size={13} />
                <span>{property.locality}{property.city_name ? ` · ${property.city_name}` : ''}</span>
              </p>

              <div className="pc-divider" />

              <div className="pc-meta">
                <span className="pc-meta-item">
                  <Bed size={14} />
                  {property.bedrooms} BHK
                </span>
                <span className="pc-meta-item">
                  <Bath size={14} />
                  {property.bathrooms} Bath
                </span>
                <span className="pc-meta-item">
                  <Grid size={14} />
                  {property.area_sqft} sqft
                </span>
              </div>

              <div className="pc-foot">
                <span className="pc-type-pill">{getPropertyTypeLabel()}</span>
                <Link href={detailUrl} className="pc-cta">
                  <span>View Details</span>
                  <ArrowRight size={14} />
                </Link>
              </div>
            </div>

          </article>
        </div>
      </div>
    </>
  );
};

export default PropertyCard;
