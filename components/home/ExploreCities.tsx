'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { getExploreCities } from '@/lib/frontendApi';
import { getCityFallbackImage, resolveExploreCityImage } from '@/lib/cityImages';

export interface ExploreCity {
  id: number;
  name: string;
  state: string;
  image?: string | null;
  property_count: number;
}

const BG_COLORS = ['#fff9db', '#eef2ff', '#ebfbee', '#e3fafc', '#fff0f6'];

function formatPropertyCount(count: number): string {
  if (count >= 1000) {
    return `${Math.floor(count / 1000) * 1000}+ Properties`;
  }
  if (count === 1) {
    return '1 Property';
  }
  return `${count} Properties`;
}

function ExploreCityCard({ city, index }: { city: ExploreCity; index: number }) {
  const [imgSrc, setImgSrc] = useState(() => resolveExploreCityImage(city.name, city.image));

  useEffect(() => {
    setImgSrc(resolveExploreCityImage(city.name, city.image));
  }, [city.name, city.image]);

  return (
    <Link
      href={`/search?city_id=${city.id}`}
      className="text-decoration-none d-block flex-shrink-0"
    >
      <div
        className="card border-0 shadow-sm p-3 rounded-4 h-100 nb-insight-card-hover d-flex flex-column overflow-hidden"
        style={{
          width: '200px',
          marginRight: '1rem',
          background: BG_COLORS[index % BG_COLORS.length],
        }}
      >
        <div className="rounded-3 overflow-hidden mb-2" style={{ height: '72px' }}>
          <img
            src={imgSrc}
            alt={city.name}
            className="w-100 h-100 object-fit-cover"
            onError={() => setImgSrc(getCityFallbackImage(city.name))}
          />
        </div>
        <div className="mt-auto">
          <h3 className="h6 fw-bold text-dark mb-1 text-truncate">{city.name}</h3>
          {city.state && (
            <p className="text-muted small m-0 text-truncate" style={{ fontSize: '0.7rem' }}>
              {city.state}
            </p>
          )}
          <p className="text-secondary small m-0 mt-1 fw-semibold" style={{ fontSize: '0.75rem' }}>
            {formatPropertyCount(city.property_count)}
          </p>
        </div>
      </div>
    </Link>
  );
}

const ExploreCities: React.FC = () => {
  const [cities, setCities] = useState<ExploreCity[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    getExploreCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setCities(res.data.items);
        } else {
          setCities([]);
        }
      })
      .catch((e) => console.warn('Could not load explore cities', e))
      .finally(() => setLoading(false));
  }, []);

  if (!loading && cities.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h2 className="h4 fw-bold text-dark m-0">Explore Cities</h2>
        <Link href="/search" className="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
          View all Properties
        </Link>
      </div>

      {loading ? (
        <div className="text-muted small py-3">Loading cities...</div>
      ) : (
        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left">
            <ChevronLeft size={24} />
          </button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right">
            <ChevronRight size={24} />
          </button>
          <div className="nb-horizontal-scroll">
            {cities.map((city, i) => (
              <ExploreCityCard key={city.id} city={city} index={i} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default ExploreCities;
