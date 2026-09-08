'use client';

import React, { useEffect, useState, useRef } from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, ArrowRight, Sparkles } from 'lucide-react';
import { getPropertyTypes, searchProperties } from '@/lib/frontendApi';

interface PropertyCategoriesProps {
  cityId: string;
  cityName: string;
}

interface RealCategoryCard {
  id: number | string;
  slug: string;
  name: string;
  subtitle: string;
  badge: string;
  priceHint: string;
  count: number;
  image: string;
  bgClass: string;
}

// Visual fallback images and gradient themes mapped to standard slugs
const TYPE_CONFIG_MAP: Record<string, { bgClass: string; badge: string; defaultImage: string; defaultSubtitle: string }> = {
  apartment: {
    bgClass: 'nb-bg-peach',
    badge: 'Popular Choice',
    defaultImage: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=700&q=80',
    defaultSubtitle: 'Modern flats & gated communities',
  },
  villa: {
    bgClass: 'nb-bg-blue',
    badge: 'Exclusive',
    defaultImage: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=700&q=80',
    defaultSubtitle: 'Independent villas & private estates',
  },
  plot: {
    bgClass: 'nb-bg-amber',
    badge: 'High Growth',
    defaultImage: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=700&q=80',
    defaultSubtitle: 'Approved residential & investment plots',
  },
  commercial: {
    bgClass: 'nb-bg-green',
    badge: 'High Yield',
    defaultImage: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=700&q=80',
    defaultSubtitle: 'Offices, retail & commercial spaces',
  },
  house: {
    bgClass: 'nb-bg-rose',
    badge: 'Independent',
    defaultImage: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=700&q=80',
    defaultSubtitle: 'Independent houses & duplex homes',
  },
};

function formatPriceShort(price: number): string {
  if (!price || price <= 0) return '';
  if (price >= 10000000) {
    return `From ₹${(price / 10000000).toFixed(1)} Cr`;
  }
  if (price >= 100000) {
    return `From ₹${(price / 100000).toFixed(0)} L`;
  }
  return `From ₹${price.toLocaleString('en-IN')}`;
}

function formatCountLabel(count: number): string {
  if (count >= 1000) {
    return `${Math.floor(count / 1000) * 1000}+ Properties`;
  }
  if (count === 1) {
    return '1 Property';
  }
  return `${count} Properties`;
}

const PropertyCategories: React.FC<PropertyCategoriesProps> = ({ cityId, cityName }) => {
  const [categories, setCategories] = useState<RealCategoryCard[]>([]);
  const [loading, setLoading] = useState(true);
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(true);

  // Check scroll boundary to update button states
  const updateScrollState = () => {
    const el = scrollContainerRef.current;
    if (!el) return;
    setCanScrollLeft(el.scrollLeft > 10);
    setCanScrollRight(el.scrollLeft < el.scrollWidth - el.clientWidth - 10);
  };

  useEffect(() => {
    const el = scrollContainerRef.current;
    if (el) {
      el.addEventListener('scroll', updateScrollState, { passive: true });
      updateScrollState();
      return () => el.removeEventListener('scroll', updateScrollState);
    }
  }, [categories]);

  const handleScroll = (direction: 'left' | 'right') => {
    const el = scrollContainerRef.current;
    if (!el) return;
    const scrollAmount = 330;
    el.scrollBy({
      left: direction === 'left' ? -scrollAmount : scrollAmount,
      behavior: 'smooth',
    });
  };

  useEffect(() => {
    let isMounted = true;
    setLoading(true);

    // Fetch real property types from admin DB + real active properties
    Promise.all([
      getPropertyTypes().catch(() => ({ data: { success: false, items: [] } })),
      searchProperties({ limit: 100, ...(cityId ? { city_id: cityId } : {}) }).catch(() => ({ data: { success: false, items: [] } })),
    ])
      .then(([typesRes, propsRes]) => {
        if (!isMounted) return;

        const dbTypes: any[] = typesRes.data?.success && Array.isArray(typesRes.data.items)
          ? typesRes.data.items
          : [];

        const realProperties: any[] = propsRes.data?.success && Array.isArray(propsRes.data.items)
          ? propsRes.data.items
          : [];

        if (dbTypes.length === 0) {
          // If no types found in admin DB, don't show any unverified fake categories
          setCategories([]);
          return;
        }

        // Map ONLY real property types from the admin DB
        const mappedCards: RealCategoryCard[] = dbTypes.map((t, idx) => {
          const typeSlug = String(t.slug || '').toLowerCase();
          const subTypeSlugs = Array.isArray(t.sub_types)
            ? t.sub_types.map((st: any) => String(st.slug || '').toLowerCase())
            : [];
          const allSlugs = new Set([typeSlug, ...subTypeSlugs]);

          // Filter matching real properties for this property type
          const matchingProps = realProperties.filter((p) => {
            const pType = String(p.property_type || '').toLowerCase();
            return allSlugs.has(pType);
          });

          const realCount = matchingProps.length;

          // Calculate real min price from actual listings
          const propsWithPrice = matchingProps.filter((p) => p.price && Number(p.price) > 0);

          let priceHint = '';
          if (propsWithPrice.length > 0) {
            const minPrice = Math.min(...propsWithPrice.map((p) => Number(p.price)));
            priceHint = formatPriceShort(minPrice);
          }

          // Real image: use real property image if available, else admin image_url, else curated config
          const propWithImage = matchingProps
            .find((p) => p.thumbnail_url || (Array.isArray(p.image_urls) && p.image_urls[0]));

          const cfg = TYPE_CONFIG_MAP[typeSlug] || {
            bgClass: idx % 3 === 0 ? 'nb-bg-peach' : idx % 3 === 1 ? 'nb-bg-blue' : 'nb-bg-green',
            badge: 'Verified',
            defaultImage: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=700&q=80',
            defaultSubtitle: 'Quality real estate listings',
          };

          const realImage = t.image_url || propWithImage?.thumbnail_url || propWithImage?.image_urls?.[0] || cfg.defaultImage;

          // Collect localities from real listings for subtitle
          const localities = Array.from(
            new Set(
              matchingProps
                .map((p) => (p.locality || p.city_name || '').split(',')[0].trim())
                .filter(Boolean)
            )
          );

          const subtitle = localities.length > 0
            ? `Available in ${localities.slice(0, 2).join(', ')}`
            : cfg.defaultSubtitle;

          return {
            id: t.id,
            slug: t.slug,
            name: t.name,
            subtitle,
            badge: realCount > 0 ? cfg.badge : 'Available on Request',
            priceHint,
            count: realCount,
            image: realImage,
            bgClass: cfg.bgClass,
          };
        });

        setCategories(cityId ? mappedCards.filter((card) => card.count > 0) : mappedCards);
      })
      .catch((err) => {
        console.warn('Error loading real property types', err);
        setCategories([]);
      })
      .finally(() => {
        if (isMounted) setLoading(false);
      });

    return () => {
      isMounted = false;
    };
  }, [cityId]);

  if (!loading && categories.length === 0) {
    return null;
  }

  return (
    <section className="nb-classic-categories-section mb-5 fade-in-up" aria-label="Explore Property Types">
      {/* Header section with classic typography */}
      <div className="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
          {/* <div className="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-light border text-primary mb-2 small fw-semibold">
            <Sparkles size={14} className="text-warning" />
            <span style={{ fontSize: '0.75rem', letterSpacing: '0.04em', textTransform: 'uppercase' }}>
              Real Verified Inventory
            </span>
          </div> */}
          <h2 className="nb-classic-section-title fw-bold text-dark m-0">
            Explore by Property Type
          </h2>
          <p className="nb-classic-section-sub text-secondary m-0 mt-1">
            {cityName ? (
              <>Properties available in <strong className="text-dark">{cityName}</strong></>
            ) : (
              'Verified properties available in your location'
            )}
          </p>
        </div>

        {/* Custom Navigation Arrows */}
        {categories.length > 2 && (
          <div className="d-none d-sm-flex align-items-center gap-2">
            <button
              type="button"
              className={`nb-nav-btn ${!canScrollLeft ? 'nb-nav-btn-disabled' : ''}`}
              onClick={() => handleScroll('left')}
              disabled={!canScrollLeft}
              aria-label="Scroll left property types"
            >
              <ChevronLeft size={20} />
            </button>
            <button
              type="button"
              className={`nb-nav-btn ${!canScrollRight ? 'nb-nav-btn-disabled' : ''}`}
              onClick={() => handleScroll('right')}
              disabled={!canScrollRight}
              aria-label="Scroll right property types"
            >
              <ChevronRight size={20} />
            </button>
          </div>
        )}
      </div>

      {/* Horizontal Carousel */}
      <div className="nb-scroll-wrapper position-relative">
        <div
          ref={scrollContainerRef}
          className="nb-horizontal-scroll nb-classic-cat-scroll"
          style={{ paddingBottom: '1rem', scrollSnapType: 'x mandatory' }}
        >
          {categories.map((cat, i) => (
            <Link
              key={cat.id}
              href={`/search?property_type=${encodeURIComponent(cat.slug)}${cityId ? `&city_id=${cityId}` : ''}`}
              className={`nb-classic-cat-card ${cat.bgClass} text-decoration-none`}
              style={{
                flexShrink: 0,
                scrollSnapAlign: 'start',
                animationDelay: `${i * 60}ms`,
              }}
            >
              {/* Shimmer / Gleam Sheen Layer */}
              <div className="nb-card-gleam-overlay" />

              {/* Upper Content Area */}
              <div className="nb-classic-cat-card-text">
                {/* Badge and Real Price Hint */}
                <div className="d-flex align-items-center justify-content-between mb-2">
                  <span className="nb-classic-cat-badge">
                    {cat.badge}
                  </span>
                  {cat.priceHint && (
                    <span className="nb-classic-price-hint">
                      {cat.priceHint}
                    </span>
                  )}
                </div>

                {/* Real DB Title */}
                <h3 className="nb-classic-cat-card-title">
                  <span>{cat.name}</span>
                </h3>

                {/* Subtitle / Real Localities */}
                <p className="nb-classic-cat-subtitle text-muted">
                  {cat.subtitle}
                </p>

                {/* Footer Count & Kinetic Button */}
                <div className="nb-classic-cat-footer d-flex align-items-center justify-content-between pt-2">
                  <div className="d-flex align-items-center">
                    <span className="nb-live-pulse-dot" />
                    <span className="nb-classic-cat-count">{formatCountLabel(cat.count)}</span>
                  </div>

                  <span className="nb-classic-cat-arrow-btn" aria-hidden="true">
                    <ArrowRight size={16} />
                  </span>
                </div>
              </div>

              {/* Arch Dome Photographic Window with Ken Burns Zoom */}
              <div className="nb-classic-cat-card-img-wrap">
                <img
                  src={cat.image}
                  alt={cat.name}
                  loading="lazy"
                  className="nb-classic-cat-card-img"
                  onError={(e) => {
                    // Fallback if local upload image not reachable
                    const target = e.currentTarget;
                    const fallback = TYPE_CONFIG_MAP[cat.slug]?.defaultImage || 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=700&q=80';
                    if (target.src !== fallback) {
                      target.src = fallback;
                    }
                  }}
                />
                <div className="nb-classic-card-img-gradient" />
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default PropertyCategories;
