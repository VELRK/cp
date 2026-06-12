'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from './AuthContext';
import api from '../lib/api';
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
          animation: pc-fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) both;
        }
        @keyframes pc-fadeUp {
          from { opacity: 0; transform: translateY(22px); }
          to   { opacity: 1; transform: translateY(0); }
        }

        .pc-card {
          background: #ffffff;
          border-radius: 16px;
          overflow: hidden;
          box-shadow: 0 2px 10px rgba(0,0,0,0.07), 0 0 0 1px rgba(0,0,0,0.04);
          transition: box-shadow 0.28s ease, transform 0.28s cubic-bezier(.22,.68,0,1.2);
          height: 100%;
          display: flex;
          flex-direction: column;
          position: relative;
        }
        .pc-card:hover {
          box-shadow: 0 16px 40px rgba(0,0,0,0.13), 0 0 0 1px rgba(0,0,0,0.06);
          transform: translateY(-4px);
        }

        /* ── IMAGE ZONE ── */
        .pc-img-wrap {
          position: relative;
          overflow: hidden;
          aspect-ratio: 4/3;
          background: #f0f0f0;
          flex-shrink: 0;
        }
        .pc-img {
          width: 100%; height: 100%;
          object-fit: cover;
          display: block;
          transition: transform 0.55s cubic-bezier(.22,.68,0,1.2), opacity 0.35s ease;
          opacity: 0;
        }
        .pc-img.loaded { opacity: 1; }
        .pc-card:hover .pc-img { transform: scale(1.06); }

        .pc-placeholder {
          width: 100%; height: 100%;
          display: flex; flex-direction: column;
          align-items: center; justify-content: center;
          background: #f7f8fa;
          color: #b0b5c0;
          gap: 6px;
          font-size: 13px;
        }

        /* gradient overlay */
        .pc-overlay {
          position: absolute; inset: 0;
          background: linear-gradient(to top, rgba(10,10,25,0.52) 0%, rgba(10,10,25,0.0) 50%);
          pointer-events: none;
        }

        /* ── BADGES ── */
        .pc-badge {
          position: absolute;
          top: 12px; left: 12px;
          font-size: 11px; font-weight: 700;
          letter-spacing: .6px; text-transform: uppercase;
          padding: 4px 10px;
          border-radius: 6px;
          line-height: 1.4;
        }
        .pc-badge--rent  { background: #e8f4fd; color: #1a7fc1; }
        .pc-badge--sale  { background: #eaf6ec; color: #1a8a3a; }

        .pc-featured {
          position: absolute;
          top: 12px; right: 50px;
          font-size: 10px; font-weight: 700;
          letter-spacing: .7px; text-transform: uppercase;
          background: linear-gradient(135deg, #f59e0b, #d97706);
          color: #fff;
          padding: 4px 10px;
          border-radius: 6px;
        }

        .pc-price {
          position: absolute;
          bottom: 12px; left: 12px;
          font-size: 17px; font-weight: 800;
          color: #ffffff;
          letter-spacing: -.3px;
          text-shadow: 0 1px 6px rgba(0,0,0,0.4);
          line-height: 1;
        }

        /* ── WISHLIST BUTTON ── */
        .pc-wish {
          position: absolute;
          top: 10px; right: 10px;
          width: 34px; height: 34px;
          border-radius: 50%;
          border: none;
          background: rgba(255,255,255,0.92);
          backdrop-filter: blur(6px);
          display: flex; align-items: center; justify-content: center;
          cursor: pointer;
          box-shadow: 0 2px 8px rgba(0,0,0,0.14);
          transition: background 0.2s, transform 0.22s cubic-bezier(.22,.68,0,1.5);
        }
        .pc-wish:hover { background: #fff; transform: scale(1.18); }
        .pc-wish.active { background: #fff3f3; }
        .pc-wish:disabled { opacity: 0.6; }

        @keyframes pc-heartPop {
          0%   { transform: scale(1); }
          40%  { transform: scale(1.45); }
          70%  { transform: scale(0.88); }
          100% { transform: scale(1); }
        }
        .pc-wish.active svg { animation: pc-heartPop 0.38s ease; }

        /* ── BODY ── */
        .pc-body {
          padding: 16px 18px 18px;
          display: flex; flex-direction: column;
          flex: 1;
          gap: 0;
        }

        .pc-title {
          font-size: 15px; font-weight: 700;
          color: #111827;
          margin: 0 0 5px;
          line-height: 1.35;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pc-title a {
          color: inherit; text-decoration: none;
          transition: color 0.18s;
        }
        .pc-title a:hover { color: #2563eb; }

        .pc-loc {
          display: flex; align-items: center; gap: 4px;
          font-size: 12.5px; color: #6b7280;
          margin: 0 0 12px;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pc-loc svg { flex-shrink: 0; color: #9ca3af; }

        /* divider */
        .pc-divider {
          height: 1px;
          background: linear-gradient(to right, #e5e7eb, transparent);
          margin: 0 0 12px;
        }

        .pc-meta {
          display: flex; gap: 0;
          margin: 0 0 14px;
        }
        .pc-meta-item {
          display: flex; align-items: center; gap: 5px;
          font-size: 12.5px; color: #374151; font-weight: 500;
          padding: 5px 10px 5px 0;
          flex: 1;
          border-right: 1px solid #e5e7eb;
        }
        .pc-meta-item:last-child { border-right: none; padding-right: 0; }
        .pc-meta-item svg { color: #6366f1; }

        .pc-foot {
          margin-top: auto;
          display: flex; align-items: center; justify-content: space-between;
        }

        .pc-type-pill {
          font-size: 11px; font-weight: 600;
          color: #6366f1;
          background: #eef2ff;
          border-radius: 20px;
          padding: 4px 10px;
          letter-spacing: .2px;
        }

        .pc-cta {
          display: inline-flex; align-items: center; gap: 4px;
          font-size: 13px; font-weight: 700;
          color: #2563eb;
          text-decoration: none;
          padding: 6px 12px;
          border-radius: 8px;
          background: #eff6ff;
          transition: background 0.2s, gap 0.22s, color 0.2s;
        }
        .pc-cta:hover {
          background: #2563eb;
          color: #fff;
          gap: 7px;
        }
        .pc-cta svg { transition: transform 0.22s; }
        .pc-cta:hover svg { transform: translateX(2px); }

        /* skeleton shimmer while image loads */
        .pc-img-skeleton {
          position: absolute; inset: 0;
          background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
          background-size: 200% 100%;
          animation: pc-shimmer 1.4s infinite;
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
                <MapPin size={12} />
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
                  <span>View</span>
                  <ArrowRight size={15} />
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