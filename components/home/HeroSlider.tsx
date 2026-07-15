'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import './HeroSlider.css';

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
  onSlideSelect?: (index: number) => void;
}

const HeroSlider: React.FC<HeroSliderProps> = ({
  slides,
  currentSlide,
  getPropertyTypeLabel,
  onSlideSelect,
}) => {
  const handlePrev = (e: React.MouseEvent) => {
    e.preventDefault();
    if (onSlideSelect && slides.length > 0) {
      const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
      onSlideSelect(prevIndex);
    }
  };

  const handleNext = (e: React.MouseEvent) => {
    e.preventDefault();
    if (onSlideSelect && slides.length > 0) {
      const nextIndex = (currentSlide + 1) % slides.length;
      onSlideSelect(nextIndex);
    }
  };

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

      {slides.length > 1 && (
        <>
          <button
            className="nb-slider-arrow nb-slider-arrow-left"
            onClick={handlePrev}
            aria-label="Previous slide"
          >
            <ChevronLeft size={24} strokeWidth={1.5} />
          </button>
          <button
            className="nb-slider-arrow nb-slider-arrow-right"
            onClick={handleNext}
            aria-label="Next slide"
          >
            <ChevronRight size={24} strokeWidth={1.5} />
          </button>
          <div className="nb-slider-dots-container">
            {slides.map((_, idx) => (
              <button
                key={idx}
                className={`nb-slider-dot ${idx === currentSlide ? 'active' : ''}`}
                onClick={(e) => {
                  e.preventDefault();
                  if (onSlideSelect) onSlideSelect(idx);
                }}
                aria-label={`Go to slide ${idx + 1}`}
              />
            ))}
          </div>
        </>
      )}
    </section>
  );
};

export default HeroSlider;
