'use client';

import React from 'react';
import Link from 'next/link';
import { Search, Sliders, ChevronDown, Mic, Navigation } from 'lucide-react';
import type { PropertyTypeItem } from '@/lib/propertyTypes';

export interface City {
  id: number;
  name: string;
  state: string;
}

interface SearchPanelProps {
  cityId: string;
  setCityId: (val: string) => void;
  cities: City[];
  mainTypes: PropertyTypeItem[];
  mainTypeSlug: string;
  subTypeSlug: string;
  subTypes: PropertyTypeItem[];
  onMainTypeChange: (slug: string) => void;
  onSubTypeChange: (slug: string) => void;
  typesLoading?: boolean;
  searchQuery: string;
  setSearchQuery: (val: string) => void;
  minPrice: string;
  setMinPrice: (val: string) => void;
  maxPrice: string;
  setMaxPrice: (val: string) => void;
  bedrooms: string;
  setBedrooms: (val: string) => void;
  sortBy: string;
  setSortBy: (val: string) => void;
  showAdvanced: boolean;
  setShowAdvanced: (val: boolean) => void;
  voiceStatus: 'idle' | 'listening' | 'success' | 'error';
  handleVoiceSearch: () => void;
  handleLocationSearch: () => void;
  handleSearchSubmit: (e: React.FormEvent) => void;
  user: any;
  setAuthModalOpen: (val: 'login' | 'register' | null) => void;
}

const SearchPanel: React.FC<SearchPanelProps> = ({
  cityId,
  setCityId,
  cities,
  mainTypes,
  mainTypeSlug,
  subTypeSlug,
  subTypes,
  onMainTypeChange,
  onSubTypeChange,
  typesLoading = false,
  searchQuery,
  setSearchQuery,
  minPrice,
  setMinPrice,
  maxPrice,
  setMaxPrice,
  bedrooms,
  setBedrooms,
  sortBy,
  setSortBy,
  showAdvanced,
  setShowAdvanced,
  voiceStatus,
  handleVoiceSearch,
  handleLocationSearch,
  handleSearchSubmit,
  user,
  setAuthModalOpen
}) => {
  return (
    <div className="nb-search-card-premium fade-in-up">
      {/* Tab Header Row — main property types from API */}
      <div className="nb-search-tabs-premium-row">
        <ul className="nb-search-tabs-premium-list">
          {typesLoading && mainTypes.length === 0 ? (
            <li>
              <span className="nb-search-tab-premium-btn text-muted" style={{ cursor: 'default' }}>
                Loading…
              </span>
            </li>
          ) : mainTypes.length === 0 ? (
            <li>
              <span className="nb-search-tab-premium-btn text-muted" style={{ cursor: 'default' }}>
                No property types
              </span>
            </li>
          ) : (
            mainTypes.map((mt) => (
              <li key={mt.id}>
                <button
                  type="button"
                  className={`nb-search-tab-premium-btn ${mainTypeSlug === mt.slug ? 'active' : ''}`}
                  onClick={() => onMainTypeChange(mt.slug)}
                  disabled={typesLoading}
                >
                  {mt.name}
                </button>
              </li>
            ))
          )}
        </ul>
        <Link href={user ? '/owner/property/add' : '#'} onClick={(e) => {
          if (!user) {
            e.preventDefault();
            setAuthModalOpen('login');
          }
        }} className="nb-post-property-free-link my-2 text-decoration-none">
          Post Property <span className="badge bg-success text-white py-1 px-1.5 ms-1">FREE</span>
        </Link>
      </div>

      {/* Search Inputs Row */}
      <form onSubmit={handleSearchSubmit} className="nb-search-inputs-premium-row">
        {/* Sub type dropdown (main type chosen via tabs above) */}
        {mainTypeSlug && subTypes.length > 0 && (
          <div className="nb-search-select-premium-wrap" style={{ width: '170px' }}>
            <select
              className="form-select"
              value={subTypeSlug}
              onChange={(e) => onSubTypeChange(e.target.value)}
              disabled={typesLoading}
              aria-label="Sub property type"
            >
              <option value="">Select</option>
              {subTypes.map((s) => (
                <option key={s.id} value={s.slug}>
                  {s.name}
                </option>
              ))}
            </select>
          </div>
        )}

        {/* City Selector */}
        <div className="nb-search-select-premium-wrap" style={{ width: '140px' }}>
          <select
            className="form-select"
            value={cityId}
            onChange={(e) => setCityId(e.target.value)}
          >
            <option value="">Any City</option>
            {cities.map((c) => (
              <option key={c.id} value={c.id.toString()}>{c.name}</option>
            ))}
          </select>
        </div>

        {/* Keyword/Locality Search Input */}
        <div className="nb-search-input-premium-wrap">
          <Search size={16} className="nb-search-input-premium-icon" />
          <input
            type="text"
            className="form-control"
            placeholder="Locality / Area / Project..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          <div className="nb-search-input-actions">
            <button
              type="button"
              className={`nb-search-action-btn ${voiceStatus === 'listening' ? 'listening' : ''}`}
              title={voiceStatus === 'listening' ? 'Listening...' : 'Voice Search'}
              onClick={handleVoiceSearch}
            >
              <Mic size={16} />
            </button>
            <button type="button" className="nb-search-action-btn" title="Current Location" onClick={handleLocationSearch}>
              <Navigation size={16} />
            </button>
          </div>
        </div>

        {/* Collapsible Trigger button */}
        <button
          type="button"
          className="btn btn-light border rounded-pill px-3 d-flex align-items-center gap-1.5 my-1"
          style={{ fontWeight: 600, color: '#4b5563', fontSize: '0.9rem' }}
          onClick={() => setShowAdvanced(!showAdvanced)}
        >
          <Sliders size={14} />
          <span>Filters</span>
          <ChevronDown size={14} style={{ transform: showAdvanced ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }} />
        </button>

        <button type="submit" className="nb-search-submit-premium-btn">
          Search
        </button>
      </form>

      {/* Collapsible Advanced Options */}
      {showAdvanced && (
        <div className="p-4 border-top animate-fade-in" style={{ background: '#f8fafc', borderBottomLeftRadius: '20px', borderBottomRightRadius: '20px' }}>
          <div className="row g-3">
            {/* Bedrooms Selection */}
            <div className="col-md-3">
              <label className="form-label text-secondary small fw-semibold">BHK / Bedrooms</label>
              <select
                className="form-select form-select-sm"
                value={bedrooms}
                onChange={(e) => setBedrooms(e.target.value)}
              >
                <option value="">Any BHK</option>
                <option value="1">1 BHK</option>
                <option value="2">2 BHK</option>
                <option value="3">3 BHK</option>
                <option value="4">4 BHK</option>
                <option value="5">5 BHK</option>
              </select>
            </div>

            {/* Min Price */}
            <div className="col-md-3">
              <label className="form-label text-secondary small fw-semibold">Min Budget</label>
              <select
                className="form-select form-select-sm"
                value={minPrice}
                onChange={(e) => setMinPrice(e.target.value)}
              >
                <option value="">No Min</option>
                <option value="5000">₹5,000</option>
                <option value="10000">₹10,000</option>
                <option value="20000">₹20,000</option>
                <option value="50000">₹50,000</option>
                <option value="1000000">₹10 Lakhs</option>
                <option value="2000000">₹20 Lakhs</option>
                <option value="5000000">₹50 Lakhs</option>
                <option value="10000000">₹1 Crore</option>
              </select>
            </div>

            {/* Max Price */}
            <div className="col-md-3">
              <label className="form-label text-secondary small fw-semibold">Max Budget</label>
              <select
                className="form-select form-select-sm"
                value={maxPrice}
                onChange={(e) => setMaxPrice(e.target.value)}
              >
                <option value="">No Max</option>
                <option value="10000">₹10,000</option>
                <option value="20000">₹20,000</option>
                <option value="50000">₹50,000</option>
                <option value="100000">₹1 Lakh</option>
                <option value="5000000">₹50 Lakhs</option>
                <option value="10000000">₹1 Crore</option>
                <option value="20000000">₹2 Crores</option>
                <option value="50000000">₹5 Crores</option>
              </select>
            </div>

            {/* Sort By Selection */}
            <div className="col-md-3">
              <label className="form-label text-secondary small fw-semibold">Sort By</label>
              <select
                className="form-select form-select-sm"
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
              >
                <option value="new">Latest Listed</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
              </select>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default SearchPanel;
