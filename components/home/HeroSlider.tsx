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
    <section className="nb-hero-slider-revamp">
      {slides.map((slide, index) => (
        <div
          key={slide.id}
          className={`nb-hero-slide-revamp ${index === currentSlide ? 'active' : ''}`}
          style={{ backgroundImage: `url('${slide.image_url}')` }}
        >
          <div className="nb-hero-slide-overlay-rich" />
          <div className="nb-hero-slide-content">
            <span className="slide-premium-badge">Exclusive Property</span>
            <h2 className="slide-main-title">{slide.title}</h2>
            <p className="slide-location">
              {slide.bedrooms} BHK {getPropertyTypeLabel(slide.property_type)} in <strong>{slide.locality}</strong>
            </p>
            <div className="slide-footer-row">
              <div className="slide-price-tag">{slide.price_label}</div>
              {slide.slug ? (
                <Link
                  href={
                    slide.slug.startsWith('search')
                      ? `/${slide.slug}`
                      : `/property/${slide.slug}`
                  }
                  className="slide-explore-btn"
                >
                  <span>Discover Details</span>
                  <span className="arrow-icon">→</span>
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
