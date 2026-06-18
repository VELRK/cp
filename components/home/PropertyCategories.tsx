'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, ArrowRight } from 'lucide-react';

interface PropertyCategoriesProps {
  cityId: string;
  cityName: string;
}

const PropertyCategories: React.FC<PropertyCategoriesProps> = ({
  cityId,
  cityName
}) => {
  const categories = [
    {
      name: 'Residential Land',
      count: '7,000+ Properties',
      bgClass: 'nb-bg-peach',
      query: 'plot',
      imgUrl: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&q=80'
    },
    {
      name: 'Independent House/ Villa',
      count: '4,000+ Properties',
      bgClass: 'nb-bg-blue',
      query: 'villa',
      imgUrl: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80'
    },
    {
      name: 'Residential Apartment',
      count: '1,300+ Properties',
      bgClass: 'nb-bg-green',
      query: 'apartment',
      imgUrl: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&q=80'
    },
    {
      name: 'Builder Floor',
      count: '130+ Properties',
      bgClass: 'nb-bg-peach',
      query: 'house',
      imgUrl: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80'
    },
    {
      name: 'Farm House',
      count: '20+ Properties',
      bgClass: 'nb-bg-blue',
      query: 'villa',
      imgUrl: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&q=80'
    },
    {
      name: '1 RK/ Studio Apartment',
      count: '4 Properties',
      bgClass: 'nb-bg-green',
      query: 'apartment',
      imgUrl: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&q=80'
    },
    {
      name: 'Serviced Apartments',
      count: '3 Properties',
      bgClass: 'nb-bg-peach',
      query: 'commercial',
      imgUrl: 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=600&q=80'
    }
  ];

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
              key={i}
              href={`/search?property_type=${cat.query}&city_id=${cityId}`}
              className={`nb-classic-cat-card ${cat.bgClass} position-relative text-decoration-none`}
              style={{ marginRight: '1rem', flexShrink: 0 }}
            >
              <div className="nb-classic-cat-card-text">
                <h3 className="nb-classic-cat-card-title text-dark">
                  <span>{cat.name}</span>
                  <span className="nb-classic-cat-card-arrow">
                    <ArrowRight size={18} />
                  </span>
                </h3>
                <div className="nb-classic-cat-card-count text-muted">{cat.count}</div>
              </div>
              <div className="nb-classic-cat-card-img-wrap">
                <img
                  src={cat.imgUrl}
                  alt={cat.name}
                  className="nb-classic-cat-card-img"
                />
              </div>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
};

export default PropertyCategories;
