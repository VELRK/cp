'use client';

import React, { useMemo, useState } from 'react';
import Link from 'next/link';
import { Search, Navigation } from 'lucide-react';
import type { PropertyTypeItem } from '@/lib/propertyTypes';

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

  return (
    <div className="nb-search-card-premium fade-in-up">
      <div className="nb-search-tabs-premium-row">
        <ul className="nb-search-tabs-premium-list">
          <li>
            <button
              type="button"
              className={`nb-search-tab-premium-btn ${!mainTypeSlug ? 'active' : ''}`}
              onClick={() => onMainTypeChange('')}
            >
              All Properties
              {allCount !== null && allCount >= 0 ? (
                <span className="ms-1 opacity-75">({formatCount(allCount)})</span>
              ) : null}
            </button>
          </li>

          {mainTypes.map((mt) => {
            const count = typeCounts[mt.slug];
            return (
              <li key={mt.id || mt.slug}>
                <button
                  type="button"
                  className={`nb-search-tab-premium-btn ${mainTypeSlug === mt.slug ? 'active' : ''}`}
                  onClick={() => onMainTypeChange(mt.slug)}
                >
                  {mt.name}
                  {typeof count === 'number' ? (
                    <span className="ms-1 opacity-75">({formatCount(count)})</span>
                  ) : null}
                </button>
              </li>
            );
          })}
        </ul>
        <Link
          href={user ? '/owner/property/add' : '#'}
          onClick={(e) => {
            if (!user) {
              e.preventDefault();
              setAuthModalOpen('login');
            }
          }}
          className="nb-post-property-free-link my-2 text-decoration-none"
        >
          Post Property <span className="badge bg-success text-white py-1 px-1.5 ms-1">FREE</span>
        </Link>
      </div>

      <form onSubmit={handleSearchSubmit} className="nb-search-inputs-premium-row">
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

          <div className="nb-search-input-premium-wrap position-relative">
            <Search size={16} className="nb-search-input-premium-icon" />
            <input
              type="text"
              className="form-control"
              placeholder="Locality / Area / Project..."
              value={searchQuery}
              onChange={(e) => {
                setSearchQuery(e.target.value);
                setShowSuggestions(true);
              }}
              onFocus={() => setShowSuggestions(true)}
              onBlur={() => setTimeout(() => setShowSuggestions(false), 180)}
              autoComplete="off"
            />
            <div className="nb-search-input-actions">
              <button
                type="button"
                className="nb-search-action-btn"
                title="Current Location"
                onClick={handleLocationSearch}
              >
                <Navigation size={16} />
              </button>
            </div>
            {showSuggestions && filteredSuggestions.length > 0 && (
              <ul className="nb-search-suggest-list list-unstyled mb-0">
                {filteredSuggestions.map((loc) => (
                  <li key={loc.name}>
                    <button
                      type="button"
                      className="nb-search-suggest-item"
                      onMouseDown={(e) => e.preventDefault()}
                      onClick={() => {
                        setSearchQuery(loc.name);
                        setShowSuggestions(false);
                      }}
                    >
                      <span>{loc.name}</span>
                      {loc.count > 0 ? (
                        <span className="text-muted small">{loc.count}</span>
                      ) : null}
                    </button>
                  </li>
                ))}
              </ul>
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
