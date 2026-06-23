'use client';

import React from 'react';
import Link from 'next/link';

export interface PropertyBannerSlide {
  id: number;
  image_url: string;
  title: string;
  slug: string;
  property_type: string;
  bedrooms: number;
  locality: string;
  price_label: string;
}

interface HeroSliderProps {
  slides: PropertyBannerSlide[];
  currentSlide: number;
  getPropertyTypeLabel: (type: string) => string;
}

const HeroSlider: React.FC<HeroSliderProps> = ({
  slides,
  currentSlide,
  getPropertyTypeLabel,
}) => {
  return (
    <section className="nb-hero-slider">
      {slides.map((slide, index) => (
        <div
          key={slide.id}
          className={`nb-hero-slide ${index === currentSlide ? 'active' : ''}`}
          style={{ backgroundImage: `url('${slide.image_url}')` }}
        >
          <div className="nb-hero-slide-overlay" />
          <div className="nb-hero-slide-info">
            <span className="nb-hero-slide-badge">
              {slide.property_type === 'plot'
                ? 'Plot / Land'
                : `${slide.bedrooms} BHK ${getPropertyTypeLabel(slide.property_type)}`}
            </span>
            <h1 className="nb-hero-slide-title">{slide.title}</h1>
            <p className="nb-hero-slide-desc">
              Located in the premium area of{' '}
              <span className="text-accent fw-bold">{slide.locality}</span> | Start living your
              dreams today.
            </p>
            <div className="d-flex justify-content-between align-items-center">
              <div className="nb-hero-slide-price">{slide.price_label}</div>
              {slide.slug ? (
                <Link
                  href={
                    slide.slug.startsWith('search')
                      ? `/${slide.slug}`
                      : `/property/${slide.slug}`
                  }
                  className="btn btn-outline-light btn-sm px-3 py-1.5 fw-semibold border-2 rounded-pill"
                >
                  Explore Now →
                </Link>
              ) : null}
            </div>
          </div>
        </div>
      ))}
    </section>
  );
};

export default HeroSlider;
