'use client';

import React, { useMemo, useState } from 'react';
import Link from 'next/link';
import { Search, Navigation } from 'lucide-react';
import type { PropertyTypeItem } from '@/lib/propertyTypes';
import { useAuth } from '@/hooks/useAuth';

export interface City {
  id: number;
  name: string;
  state: string;
  count?: number;
}

interface SearchPanelProps {
  cityId: string;
  setCityId: (val: string) => void;
  cities: City[];
  mainTypes: PropertyTypeItem[];
  mainTypeSlug: string;
  onMainTypeChange: (slug: string) => void;
  typeCounts: Record<string, number>;
  allCount: number | null;
  searchQuery: string;
  setSearchQuery: (val: string) => void;
  localitySuggestions: { name: string; count: number }[];
  handleLocationSearch: () => void;
  handleSearchSubmit: (e: React.FormEvent) => void;
  user: any;
  setAuthModalOpen: (val: 'login' | 'register' | null) => void;
}

function formatCount(count: number): string {
  if (count >= 1000) return `${Math.floor(count / 1000)}k+`;
  return String(count);
}

const SearchPanel: React.FC<SearchPanelProps> = ({
  cityId,
  setCityId,
  cities,
  mainTypes,
  mainTypeSlug,
  onMainTypeChange,
  typeCounts,
  allCount,
  searchQuery,
  setSearchQuery,
  localitySuggestions,
  handleLocationSearch,
  handleSearchSubmit,
  user,
  setAuthModalOpen,
}) => {
  const [showSuggestions, setShowSuggestions] = useState(false);

  const filteredSuggestions = useMemo(() => {
    const q = searchQuery.trim().toLowerCase();
    if (q.length < 1) return localitySuggestions.slice(0, 8);
    return localitySuggestions
      .filter((loc) => loc.name.toLowerCase().includes(q))
      .slice(0, 8);
  }, [localitySuggestions, searchQuery]);

  const handlePostPropertyClick = (e: React.MouseEvent) => {
    if (!user) {
      e.preventDefault();
      setAuthModalOpen('login');
      return;
    }
  };

  return (
    <div className="nb-search-card-premium fade-in-up">
      {/* Tab Header Row — main property types filtered to real data */}
      <div className="nb-search-tabs-premium-row">
        <ul className="nb-search-tabs-premium-list" role="tablist">
          <li className="nb-search-tab-premium-item">
            <button
              type="button"
              className={`nb-search-tab-premium-btn ${!mainTypeSlug ? 'active' : ''}`}
              onClick={() => onMainTypeChange('')}
            >
              <span>All Types</span>
              {allCount !== null ? (
                <span className="badge bg-light text-dark rounded-pill ms-1.5 px-2 py-0.5 fw-semibold" style={{ fontSize: '0.7rem' }}>
                  {formatCount(allCount)}
                </span>
              ) : null}
            </button>
          </li>
          {mainTypes.map((mt) => {
            const count = typeCounts[mt.slug] ?? 0;
            return (
              <li key={mt.slug} className="nb-search-tab-premium-item">
                <button
                  type="button"
                  className={`nb-search-tab-premium-btn ${mainTypeSlug === mt.slug ? 'active' : ''}`}
                  onClick={() => onMainTypeChange(mt.slug)}
                >
                  <span>{mt.name}</span>
                  {count > 0 ? (
                    <span className="badge bg-light text-dark rounded-pill ms-1.5 px-2 py-0.5 fw-semibold" style={{ fontSize: '0.7rem' }}>
                      {formatCount(count)}
                    </span>
                  ) : null}
                </button>
              </li>
            );
          })}
        </ul>
        <Link
          href="/owner/property/add"
          onClick={handlePostPropertyClick}
          className="nb-post-property-free-link my-2 text-decoration-none"
        >
          Post Property <span className="badge bg-success text-white py-1 px-1.5 ms-1">FREE</span>
        </Link>
      </div>

      <form
        onSubmit={(e) => {
          setShowSuggestions(false);
          handleSearchSubmit(e);
        }}
        className="nb-search-inputs-premium-row"
      >
        <div className="nb-search-inputs-main-group">
          <div className="nb-search-select-premium-wrap">
            <select
              className="form-select"
              value={cityId}
              onChange={(e) => setCityId(e.target.value)}
              aria-label="City"
            >
              <option value="">
                Any City{allCount !== null && !cityId ? ` (${formatCount(allCount)})` : ''}
              </option>
              {cities.map((c) => (
                <option key={c.id} value={c.id.toString()}>
                  {c.name}
                  {typeof c.count === 'number' ? ` (${formatCount(c.count)})` : ''}
                </option>
              ))}
            </select>
          </div>

          <div className="nb-search-input-premium-wrap position-relative flex-grow-1">
            <Search size={16} className="nb-search-input-premium-icon" />
            <input
              type="text"
              className="form-control"
              placeholder="Search by area, landmark or project..."
              value={searchQuery}
              onChange={(e) => {
                setSearchQuery(e.target.value);
                setShowSuggestions(true);
              }}
              onFocus={() => setShowSuggestions(true)}
              onBlur={() => {
                setTimeout(() => setShowSuggestions(false), 200);
              }}
              aria-label="Search keywords"
              autoComplete="off"
            />
            <div className="nb-search-input-actions">
              <button
                type="button"
                className="nb-search-action-btn"
                title="Locate Me (uses your GPS to find properties nearby)"
                onClick={handleLocationSearch}
              >
                <Navigation size={16} />
              </button>
            </div>

            {showSuggestions && filteredSuggestions.length > 0 && (
              <div
                className="nb-search-suggest-list"
                style={{
                  position: 'absolute',
                  top: 'calc(100% + 8px)',
                  left: 0,
                  right: 0,
                  zIndex: 1050,
                  maxHeight: '280px',
                  overflowY: 'auto',
                  background: '#ffffff',
                  border: '1px solid rgba(11, 44, 86, 0.12)',
                  borderRadius: '12px',
                  boxShadow: '0 12px 36px rgba(11, 44, 86, 0.18)',
                  padding: 0,
                }}
              >
                <div
                  className="px-3 py-2 bg-light text-muted small fw-bold border-bottom d-flex align-items-center justify-content-between"
                  style={{ borderTopLeftRadius: '12px', borderTopRightRadius: '12px' }}
                >
                  <span>Popular Localities</span>
                  <span className="badge bg-secondary-subtle text-secondary" style={{ fontSize: '0.65rem' }}>
                    {filteredSuggestions.length} found
                  </span>
                </div>
                <div className="p-1">
                  {filteredSuggestions.map((loc) => (
                    <button
                      key={loc.name}
                      type="button"
                      className="nb-search-suggest-item"
                      onMouseDown={(e) => {
                        e.preventDefault();
                        setSearchQuery(loc.name);
                        setShowSuggestions(false);
                      }}
                    >
                      <span className="d-flex align-items-center gap-2">
                        <Search size={14} className="text-muted" />
                        <span>{loc.name}</span>
                      </span>
                      <span className="badge bg-light text-muted border rounded-pill" style={{ fontSize: '0.7rem' }}>
                        {loc.count} {loc.count === 1 ? 'property' : 'properties'}
                      </span>
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="nb-search-actions-group">
          <button type="submit" className="nb-search-submit-premium-btn">
            Search
          </button>
        </div>
      </form>
    </div>
  );
};

export default SearchPanel;
