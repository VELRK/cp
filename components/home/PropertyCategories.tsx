'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, ArrowRight } from 'lucide-react';
import { getPropertyTypeCounts } from '@/lib/frontendApi';

interface PropertyCategoriesProps {
  cityId: string;
  cityName: string;
}

interface CategoryItem {
  id: number;
  slug: string;
  name: string;
  count: number;
}

const BG_CLASSES = ['nb-bg-peach', 'nb-bg-blue', 'nb-bg-green'];
const DEFAULT_IMAGES = [
  'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&q=80',
  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80',
  'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&q=80',
  'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80',
  'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&q=80',
  'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&q=80',
  'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=600&q=80',
];

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
  const [categories, setCategories] = useState<CategoryItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    getPropertyTypeCounts(cityId ? { city_id: cityId } : undefined)
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setCategories(
            res.data.items.map((item: CategoryItem) => ({
              id: item.id,
              slug: item.slug,
              name: item.name,
              count: item.count,
            }))
          );
        } else {
          setCategories([]);
        }
      })
      .catch((e) => {
        console.warn('Could not load property type counts', e);
        setCategories([]);
      })
      .finally(() => setLoading(false));
  }, [cityId]);

  if (!loading && categories.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="mb-3">
        <h2 className="h4 fw-bold text-dark m-0" style={{ fontSize: '1.75rem', fontFamily: 'var(--font-heading)' }}>
          Apartments, Villas and more
        </h2>
        <p className="text-secondary small m-0" style={{ fontSize: '1rem', fontWeight: 500 }}>
          in {cityName}
        </p>
      </div>

      {loading ? (
        <div className="text-muted small py-3">Loading categories...</div>
      ) : (
        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left">
            <ChevronLeft size={24} />
          </button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right">
            <ChevronRight size={24} />
          </button>
          <div className="nb-horizontal-scroll">
            {categories.map((cat, i) => (
              <Link
                key={cat.id}
                href={`/search?property_type=${encodeURIComponent(cat.slug)}${cityId ? `&city_id=${cityId}` : ''}`}
                className={`nb-classic-cat-card ${BG_CLASSES[i % BG_CLASSES.length]} position-relative text-decoration-none`}
                style={{ marginRight: '1rem', flexShrink: 0 }}
              >
                <div className="nb-classic-cat-card-text">
                  <h3 className="nb-classic-cat-card-title text-dark">
                    <span>{cat.name}</span>
                    <span className="nb-classic-cat-card-arrow">
                      <ArrowRight size={18} />
                    </span>
                  </h3>
                  <div className="nb-classic-cat-card-count text-muted">{formatCountLabel(cat.count)}</div>
                </div>
                <div className="nb-classic-cat-card-img-wrap">
                  <img
                    src={DEFAULT_IMAGES[i % DEFAULT_IMAGES.length]}
                    alt={cat.name}
                    className="nb-classic-cat-card-img"
                  />
                </div>
              </Link>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default PropertyCategories;
