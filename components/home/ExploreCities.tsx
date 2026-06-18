'use client';

import React from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight, MapPin } from 'lucide-react';
import { City } from './SearchPanel';

interface ExploreCitiesProps {
  cities: City[];
}

const ExploreCities: React.FC<ExploreCitiesProps> = ({ cities }) => {
  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h2 className="h4 fw-bold text-dark m-0">Explore Cities</h2>
        <Link href="/search" className="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
          View all Properties
        </Link>
      </div>

      <div className="nb-scroll-wrapper">
        <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
        <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
        <div className="nb-horizontal-scroll">
          {cities.map((city, i) => (
            <Link key={city.id} href={`/search?city_id=${city.id}`} className="text-decoration-none d-block flex-shrink-0">
              <div 
                className="card border-0 shadow-sm p-3 rounded-4 h-100 nb-insight-card-hover d-flex flex-column" 
                style={{ 
                  width: '200px', 
                  marginRight: '1rem',
                  background: ['#fff9db', '#eef2ff', '#ebfbee', '#e3fafc', '#fff0f6'][i % 5] 
                }}
              >
                <div className="mb-3">
                  <MapPin className="nb-text-brand" size={22} />
                </div>
                <div className="mt-auto">
                  <h3 className="h6 fw-bold text-dark mb-1 text-truncate">{city.name}</h3>
                  <p className="text-muted small m-0 text-truncate" style={{ fontSize: '0.75rem' }}>View properties in {city.name}</p>
                </div>
              </div>
            </Link>
          ))}
          {cities.length === 0 && (
            <div className="text-muted small py-3">Loading cities...</div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ExploreCities;
