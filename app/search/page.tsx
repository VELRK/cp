'use client';

import React, { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import {
  getCities,
  searchProperties,
  checkWishlist,
  toggleWishlist,
} from '@/lib/frontendApi';
import { usePropertyTypeFilters } from '@/hooks/usePropertyTypeFilters';
import { effectivePropertyTypeSlug } from '@/lib/propertyTypes';
import { PropertyTypeFilterFields } from '@/components/common/PropertyTypeSelects';
import OwnerPhoneModal from '@/components/common/OwnerPhoneModal';
import { useAuth } from '@/hooks/useAuth';
import { 
  Filter, 
  Sliders, 
  RefreshCw, 
  X, 
  ChevronDown, 
  ListFilter, 
  Search, 
  MapPin, 
  Heart, 
  Eye, 
  Phone, 
  FileText, 
  Sparkles, 
  CheckCircle2, 
  ShieldAlert 
} from 'lucide-react';
import Link from 'next/link';

interface City {
  id: number;
  name: string;
  state: string;
}

export interface Property {
  id: number;
  title: string;
  slug: string;
  property_type: string;
  listing_type: 'rent' | 'sale';
  price: number;
  price_formatted?: string;
  bedrooms: number;
  bathrooms: number;
  area_sqft: number;
  locality: string;
  city_name?: string;
  city_id?: number;
  images?: string | string[];
  image_urls?: string[];
  thumbnail_url?: string;
  property_type_label?: string;
  description?: string;
  brochure_url?: string | null;
  owner_name?: string | null;
  owner_phone?: string | null;
  owner_user_type?: string | null;
  posted_by?: string;
  amenities?: string[] | null;
  video_url?: string | null;
  is_verified_property?: number;
  is_newly_launched?: number;
  is_featured?: number;
  is_recommended?: number;
  is_price_negotiable?: number;
  views?: number;
  created_at?: string | null;
}

function resolveBrochureUrl(url: string | null | undefined): string | null {
  if (!url || !String(url).trim()) return null;
  const trimmed = String(url).trim();
  if (/^https?:\/\//i.test(trimmed)) return trimmed;
  return trimmed.startsWith('/') ? trimmed : `/${trimmed}`;
}

function formatRelativeTime(dateStr?: string | null): string {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (Number.isNaN(date.getTime())) return '';
  const diffMs = Date.now() - date.getTime();
  const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
  if (days <= 0) return 'today';
  if (days === 1) return '1 day ago';
  if (days < 30) return `${days} days ago`;
  const months = Math.floor(days / 30);
  if (months === 1) return '1 month ago';
  if (months < 12) return `${months} months ago`;
  const years = Math.floor(months / 12);
  return years === 1 ? '1 year ago' : `${years} years ago`;
}

function getPropertyTags(property: Property): string[] {
  const tags: string[] = [];
  if (Number(property.is_verified_property) === 1) tags.push('Verified');
  if (Number(property.is_newly_launched) === 1) tags.push('New Launch');
  if (Number(property.is_featured) === 1) tags.push('Featured');
  if (Number(property.is_recommended) === 1) tags.push('Recommended');
  if (Number(property.is_price_negotiable) === 1) tags.push('Negotiable');
  if (property.video_url) tags.push('Video Tour');
  if (Array.isArray(property.amenities)) {
    property.amenities.forEach((item) => {
      const label = String(item || '').trim();
      if (label && !tags.includes(label)) tags.push(label);
    });
  }
  return tags.slice(0, 4);
}

function formatListingPrice(property: Property): string {
  if (property.price_formatted) return property.price_formatted;
  if (property.price >= 10000000) {
    return `₹ ${(property.price / 10000000).toFixed(2)} Cr`;
  }
  if (property.price >= 100000) {
    return `₹ ${(property.price / 100000).toFixed(2)} L`;
  }
  return `₹ ${property.price.toLocaleString('en-IN')}`;
}

function formatLayout(property: Property): string {
  if (property.bedrooms) return `${property.bedrooms} BHK`;
  if (property.property_type_label) return property.property_type_label;
  if (property.property_type) return property.property_type.replace(/_/g, ' ');
  return '—';
}

function getPostedByLabel(property: Property): string {
  if (property.posted_by === 'Agent' || property.posted_by === 'Owner') {
    return property.posted_by;
  }
  if (property.owner_user_type?.toLowerCase() === 'agent') return 'Agent';
  return 'Owner';
}

const QUICK_FILTER_PARAMS: Record<string, string> = {
  new_launch: 'is_newly_launched',
  verified: 'is_verified_property',
  owner: 'posted_by_owner',
  under_const: 'under_construction',
  ready_move: 'ready_to_move',
  video: 'has_video',
};

function getActiveQuickFilters(sp: URLSearchParams): string[] {
  const active: string[] = [];
  if (sp.get('is_newly_launched')) active.push('new_launch');
  if (sp.get('is_verified_property') || sp.get('verified')) active.push('verified');
  if (sp.get('posted_by_owner') || sp.get('owner_only') || sp.get('owner')) active.push('owner');
  if (sp.get('under_construction')) active.push('under_const');
  if (sp.get('ready_to_move')) active.push('ready_move');
  if (sp.get('has_video') || sp.get('video')) active.push('video');
  return active;
}

function buildApiParamsFromUrl(sp: URLSearchParams, page = 1, limit = 12): Record<string, string | number> {
  const params: Record<string, string | number> = { page, limit };
  const passthrough = ['city_id', 'q', 'listing_type', 'property_type', 'min_price', 'max_price', 'bedrooms', 'sort'] as const;
  passthrough.forEach((key) => {
    const value = sp.get(key);
    if (value) params[key] = value;
  });
  if (sp.get('is_recommended')) params.is_recommended = 1;
  if (sp.get('is_newly_launched')) params.is_newly_launched = 1;
  if (sp.get('is_verified_property') || sp.get('verified')) params.is_verified_property = 1;
  if (sp.get('posted_by_owner') || sp.get('owner_only') || sp.get('owner')) params.posted_by_owner = 1;
  if (sp.get('has_video') || sp.get('video')) params.has_video = 1;
  if (sp.get('ready_to_move')) params.ready_to_move = 1;
  if (sp.get('under_construction')) params.under_construction = 1;
  return params;
}

function SearchContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const { user, setAuthModalOpen } = useAuth();

  // Filters from URL/State
  const [cityId, setCityId] = useState(searchParams.get('city_id') || '');
  const [locality, setLocality] = useState(searchParams.get('q') || '');
  const [listingType, setListingType] = useState(searchParams.get('listing_type') || '');
  const {
    mainTypes,
    mainTypeSlug,
    subTypeSlug,
    subTypes,
    propertyType,
    loading: typesLoading,
    setMainTypeSlug,
    setSubTypeSlug,
  } = usePropertyTypeFilters(searchParams.get('property_type') || '');
  const [minPrice, setMinPrice] = useState(searchParams.get('min_price') || '');
  const [maxPrice, setMaxPrice] = useState(searchParams.get('max_price') || '');
  const [bedrooms, setBedrooms] = useState(searchParams.get('bedrooms') || '');
  const [sortBy, setSortBy] = useState(searchParams.get('sort') || 'new');

  // App UI states
  const [cities, setCities] = useState<City[]>([]);
  const [results, setResults] = useState<Property[]>([]);
  const [totalResults, setTotalResults] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [showMobileFilters, setShowMobileFilters] = useState(false);
  const skipAutoPushRef = useRef(false);
  const debounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const activeQuickFilters = getActiveQuickFilters(searchParams);
  const verifiedOnly = !!searchParams.get('is_verified_property') || !!searchParams.get('verified');

  // Keep track of wishlists locally for immediate toggle response
  const [wishlistIds, setWishlistIds] = useState<number[]>([]);
  const [phoneModal, setPhoneModal] = useState<{
    ownerName?: string | null;
    ownerPhone?: string | null;
    propertyTitle?: string | null;
  } | null>(null);

  // Fetch cities list
  useEffect(() => {
    getCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((err) => console.error('Error fetching cities', err));
  }, []);

  // Keep filter state in sync when URL query changes (e.g. Explore Cities link)
  useEffect(() => {
    skipAutoPushRef.current = true;
    setCityId(searchParams.get('city_id') || '');
    setLocality(searchParams.get('q') || '');
    setListingType(searchParams.get('listing_type') || '');
    setMinPrice(searchParams.get('min_price') || '');
    setMaxPrice(searchParams.get('max_price') || '');
    setBedrooms(searchParams.get('bedrooms') || '');
    setSortBy(searchParams.get('sort') || 'new');
    skipAutoPushRef.current = false;
  }, [searchParams]);

  const pushSearchUrl = useCallback(
    (updates: Record<string, string | null | undefined>, replace = true) => {
      const qp = new URLSearchParams(searchParams.toString());
      Object.entries(updates).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
          qp.delete(key);
        } else {
          qp.set(key, value);
        }
      });
      qp.delete('verified');
      qp.delete('owner');
      qp.delete('video');
      qp.delete('owner_only');
      const qs = qp.toString();
      const path = qs ? `/search?${qs}` : '/search';
      if (replace) {
        router.replace(path);
      } else {
        router.push(path);
      }
    },
    [router, searchParams]
  );

  const pushRefineFilters = useCallback(
    (overrides: Record<string, string | null | undefined> = {}, replace = true) => {
      pushSearchUrl(
        {
          city_id: cityId || null,
          q: locality.trim() || null,
          listing_type: listingType || null,
          property_type: searchParams.get('property_type') ? (propertyType || null) : null,
          min_price: minPrice || null,
          max_price: maxPrice || null,
          bedrooms: bedrooms || null,
          sort: sortBy || 'new',
          ...overrides,
        },
        replace
      );
    },
    [pushSearchUrl, cityId, locality, listingType, propertyType, minPrice, maxPrice, bedrooms, sortBy, searchParams]
  );

  // Auto-apply refine search when sidebar fields change (debounced for text/number)
  useEffect(() => {
    if (skipAutoPushRef.current) return;

    const urlSnapshot = {
      city_id: searchParams.get('city_id') || '',
      q: searchParams.get('q') || '',
      listing_type: searchParams.get('listing_type') || '',
      property_type: searchParams.get('property_type') || '',
      min_price: searchParams.get('min_price') || '',
      max_price: searchParams.get('max_price') || '',
      bedrooms: searchParams.get('bedrooms') || '',
      sort: searchParams.get('sort') || 'new',
    };
    const nextSnapshot = {
      city_id: cityId,
      q: locality.trim(),
      listing_type: listingType,
      property_type: propertyType,
      min_price: minPrice,
      max_price: maxPrice,
      bedrooms,
      sort: sortBy,
    };
    if (JSON.stringify(urlSnapshot) === JSON.stringify(nextSnapshot)) return;

    if (debounceTimerRef.current) clearTimeout(debounceTimerRef.current);
    debounceTimerRef.current = setTimeout(() => {
      pushRefineFilters();
    }, 450);

    return () => {
      if (debounceTimerRef.current) clearTimeout(debounceTimerRef.current);
    };
  }, [
    cityId,
    locality,
    listingType,
    propertyType,
    minPrice,
    maxPrice,
    bedrooms,
    sortBy,
    searchParams,
    pushRefineFilters,
  ]);

  // Fetch results when searchParams change
  useEffect(() => {
    setLoading(true);
    setCurrentPage(1);

    searchProperties(buildApiParamsFromUrl(searchParams, 1, 12))
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setResults(res.data.items);
          setTotalResults(res.data.total || res.data.items.length);
        } else {
          setResults([]);
          setTotalResults(0);
        }
      })
      .catch((err) => {
        console.error('Error searching properties', err);
        setResults([]);
        setTotalResults(0);
      })
      .finally(() => setLoading(false));
  }, [searchParams]);

  // Load more pagination
  const loadMore = () => {
    setLoadingMore(true);
    const nextPage = currentPage + 1;

    searchProperties(buildApiParamsFromUrl(searchParams, nextPage, 12))
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setResults((prev) => [...prev, ...res.data.items]);
          setCurrentPage(nextPage);
        }
      })
      .catch((err) => console.error('Error fetching more properties', err))
      .finally(() => setLoadingMore(false));
  };

  // Check wishlist status for results
  useEffect(() => {
    if (user && results.length > 0) {
      // For each result, query wishlist status
      results.forEach((p) => {
        checkWishlist(p.id, user.id, 'user_id')
          .then((res) => {
            if (res.data?.success && res.data.wishlisted) {
              setWishlistIds((prev) => prev.includes(p.id) ? prev : [...prev, p.id]);
            }
          })
          .catch((e) => console.warn('Wishlist check failed', e));
      });
    }
  }, [user, results]);

  const handleWishlistToggle = async (propertyId: number, e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();

    if (!user) {
      setAuthModalOpen('login');
      return;
    }

    try {
      const response = await toggleWishlist({
        property_id: propertyId,
        user_id: user.id,
      });
      if (response.data?.success) {
        if (wishlistIds.includes(propertyId)) {
          setWishlistIds((prev) => prev.filter((id) => id !== propertyId));
        } else {
          setWishlistIds((prev) => [...prev, propertyId]);
        }
      }
    } catch (error) {
      console.error('Error toggling wishlist:', error);
    }
  };

  const clearPropertyTypeFilter = () => {
    setMainTypeSlug('');
    setSubTypeSlug('');
    pushSearchUrl({ property_type: null });
  };

  const handleMainTypeChange = (slug: string) => {
    setMainTypeSlug(slug);
    pushRefineFilters({ property_type: effectivePropertyTypeSlug(slug, '') || null });
  };

  const handleSubTypeChange = (slug: string) => {
    setSubTypeSlug(slug);
    pushRefineFilters({ property_type: effectivePropertyTypeSlug(mainTypeSlug, slug) || null });
  };

  const handleViewNumber = (property: Property) => {
    if (!user) {
      setAuthModalOpen('login');
      return;
    }
    setPhoneModal({
      ownerName: property.owner_name,
      ownerPhone: property.owner_phone,
      propertyTitle: property.title,
    });
  };

  const clearFilters = () => {
    setCityId('');
    setLocality('');
    setListingType('');
    setMainTypeSlug('');
    setSubTypeSlug('');
    setMinPrice('');
    setMaxPrice('');
    setBedrooms('');
    setSortBy('new');
    router.push('/search');
  };

  const toggleQuickFilter = (tag: string) => {
    const param = QUICK_FILTER_PARAMS[tag];
    if (!param) return;
    const qp = new URLSearchParams(searchParams.toString());
    if (qp.has(param)) {
      qp.delete(param);
    } else {
      qp.set(param, '1');
    }
    qp.delete('verified');
    qp.delete('owner');
    qp.delete('video');
    qp.delete('owner_only');
    const qs = qp.toString();
    router.push(qs ? `/search?${qs}` : '/search');
  };

  const handleVerifiedToggle = (checked: boolean) => {
    pushSearchUrl({ is_verified_property: checked ? '1' : null });
  };

  const removeFilter = (key: string) => {
    if (key === 'property_type') {
      clearPropertyTypeFilter();
      return;
    }
    if (key === 'locality') pushSearchUrl({ q: null });
    else if (key === 'listing_type') pushSearchUrl({ listing_type: null });
    else if (key === 'bedrooms') pushSearchUrl({ bedrooms: null });
    else if (key === 'verified') pushSearchUrl({ is_verified_property: null });
    else pushSearchUrl({ [key]: null });
  };

  const selectedCityName =
    cities.find((c) => String(c.id) === cityId)?.name ||
    results[0]?.city_name ||
    'All Cities';

  const listingTypeLabel =
    listingType === 'rent' ? 'Rent' : listingType === 'sale' ? 'Sale' : 'Sale & Rent';

  const formatPrice = (property: Property) => formatListingPrice(property);

  // Sidebar Filter Layout
  const renderFilterForm = (sfx: string) => (
    <div className="nb-search-filter-form">
      {/* City */}
      <div className="mb-3">
        <label className="form-label nb-filter-label" htmlFor={`city-select-${sfx}`}>City</label>
        <select
          id={`city-select-${sfx}`}
          className="form-select form-select-sm nb-filter-control"
          value={cityId}
          onChange={(e) => setCityId(e.target.value)}
        >
          <option value="">Any City</option>
          {cities.map((c) => (
            <option key={c.id} value={c.id}>{c.name}</option>
          ))}
        </select>
      </div>

      {/* Locality */}
      <div className="mb-3">
        <label className="form-label nb-filter-label" htmlFor={`locality-search-${sfx}`}>Locality / Area</label>
        <input
          type="text"
          id={`locality-search-${sfx}`}
          className="form-control form-control-sm nb-filter-control"
          placeholder="Gandhipuram, Peelamedu..."
          value={locality}
          onChange={(e) => setLocality(e.target.value)}
        />
      </div>

      {/* Listing */}
      <div className="mb-3">
        <label className="form-label nb-filter-label">Listing</label>
        <select
          className="form-select form-select-sm nb-filter-control"
          value={listingType}
          onChange={(e) => setListingType(e.target.value)}
        >
          <option value="">Buy or Rent</option>
          <option value="sale">Buy</option>
          <option value="rent">Rent</option>
        </select>
      </div>

      {/* Property types from API — sub type after city */}
      <PropertyTypeFilterFields
        mainTypes={mainTypes}
        mainTypeSlug={mainTypeSlug}
        subTypeSlug={subTypeSlug}
        subTypes={subTypes}
        onMainChange={handleMainTypeChange}
        onSubChange={handleSubTypeChange}
        loading={typesLoading}
      />

      {/* Budget */}
      <div className="mb-3">
        <label className="form-label nb-filter-label">Budget (₹)</label>
        <div className="row g-2">
          <div className="col-6">
            <input
              type="number"
              className="form-control form-control-sm nb-filter-control"
              placeholder="Min"
              value={minPrice}
              onChange={(e) => setMinPrice(e.target.value)}
              min="0"
            />
          </div>
          <div className="col-6">
            <input
              type="number"
              className="form-control form-control-sm nb-filter-control"
              placeholder="Max"
              value={maxPrice}
              onChange={(e) => setMaxPrice(e.target.value)}
              min="0"
            />
          </div>
        </div>
      </div>

      {/* Layout BHK */}
      <div className="mb-3">
        <label className="form-label nb-filter-label">BHK / Layout</label>
        <select
          className="form-select form-select-sm nb-filter-control"
          value={bedrooms}
          onChange={(e) => setBedrooms(e.target.value)}
        >
          <option value="">Any BHK</option>
          {[1, 2, 3, 4, 5].map((b) => (
            <option key={b} value={b}>{b} BHK</option>
          ))}
        </select>
      </div>

      {/* Sort */}
      <div className="mb-4">
        <label className="form-label nb-filter-label">Sort results</label>
        <select
          className="form-select form-select-sm nb-filter-control"
          value={sortBy}
          onChange={(e) => {
            const value = e.target.value;
            setSortBy(value);
            pushRefineFilters({ sort: value });
          }}
        >
          <option value="new">Latest Listed</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
        </select>
      </div>

      <p className="text-muted small mb-3">
        Filters apply automatically as you change them.
      </p>
      <button
        type="button"
        className="btn btn-outline-secondary btn-sm w-100 rounded-pill"
        onClick={clearFilters}
      >
        Reset Filters
      </button>
    </div>
  );

  return (
    <div className="nb-search-page bg-light" style={{ minHeight: '100vh', paddingTop: '5.5rem' }}>
      
      {/* Breadcrumbs and summary header */}
      <div className="container py-3">
        <nav aria-label="breadcrumb">
          <ol className="breadcrumb small text-muted mb-2">
            <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-muted">Home</Link></li>
            <li className="breadcrumb-item"><Link href="/search" className="text-decoration-none text-muted">Search Properties</Link></li>
            {(locality || cityId) && (
              <li className="breadcrumb-item active text-dark fw-semibold" aria-current="page">
                {locality ? `Property in ${locality}` : `Property in ${selectedCityName}`}
              </li>
            )}
          </ol>
        </nav>

        {/* Dynamic Title */}
        <h1 className="h4 fw-bold text-dark mb-1">
          {totalResults} results | Property in {locality || selectedCityName} for {listingTypeLabel}
        </h1>
        {(locality || selectedCityName !== 'All Cities') && (
          <p className="text-muted small mb-3">
            Browse owner-listed homes in {locality ? `${locality}, ${selectedCityName}` : selectedCityName}
          </p>
        )}
      </div>

      <div className="container pb-5">
        <div className="row g-4">
          
          {/* Left Column Filters Sidebar */}
          <aside className="col-lg-3">
            {/* Applied filters widget */}
            <div className="card border-0 shadow-sm mb-3 p-3 bg-white rounded-3">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <span className="fw-bold text-dark small">Applied Filters</span>
                <button type="button" className="btn btn-link text-danger text-decoration-none small p-0 m-0" onClick={clearFilters}>
                  Clear All
                </button>
              </div>
              <div className="d-flex flex-wrap gap-1.5 mt-2">
                {locality && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{locality}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => removeFilter('locality')} />
                  </span>
                )}
                {listingType && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{listingType === 'rent' ? 'For Rent' : 'For Sale'}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => removeFilter('listing_type')} />
                  </span>
                )}
                {propertyType && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{propertyType}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => removeFilter('property_type')} />
                  </span>
                )}
                {bedrooms && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{bedrooms} BHK</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => removeFilter('bedrooms')} />
                  </span>
                )}
                {verifiedOnly && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>Verified</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => removeFilter('verified')} />
                  </span>
                )}
                {activeQuickFilters.filter((t) => t !== 'verified').map((tag) => (
                  <span key={tag} className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{tag.replace(/_/g, ' ')}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => toggleQuickFilter(tag)} />
                  </span>
                ))}
                {!locality && !listingType && !propertyType && !bedrooms && !verifiedOnly && activeQuickFilters.length === 0 && (
                  <span className="text-muted small">No active filters.</span>
                )}
              </div>
            </div>

            {/* Verified toggle widget */}
            <div className="nb-verified-switch-wrap shadow-sm">
              <div>
                <span className="fw-bold text-danger d-block small mb-0">Verified properties</span>
                <span className="text-muted" style={{ fontSize: '0.65rem' }}>by our moderation team</span>
              </div>
              <div className="form-check form-switch m-0 p-0">
                <input
                  className="form-check-input ms-0 cursor-pointer"
                  type="checkbox"
                  role="switch"
                  id="verifiedSwitch"
                  checked={verifiedOnly}
                  onChange={(e) => handleVerifiedToggle(e.target.checked)}
                  style={{ width: '2.5rem', height: '1.25rem' }}
                />
              </div>
            </div>

            {/* Accordion/Form Filters */}
            <div className="card border-0 shadow-sm bg-white p-3 rounded-3 d-none d-lg-block">
              <h2 className="h6 fw-bold text-dark border-bottom pb-2 mb-3">Refine Search</h2>
              {renderFilterForm('sidebar')}
            </div>
          </aside>

          {/* Right Column: Search Results List */}
          <div className="col-lg-9">
            
            {/* Top horizontal filter pills */}
            <div className="nb-search-horizontal-filters">
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('new_launch') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('new_launch')}
              >
                ★ NEW LAUNCH
              </button>
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('owner') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('owner')}
              >
                Owner
              </button>
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('verified') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('verified')}
              >
                Verified
              </button>
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('under_const') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('under_const')}
              >
                Under Construction
              </button>
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('ready_move') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('ready_move')}
              >
                Ready To Move
              </button>
              <button 
                type="button" 
                className={`nb-search-filter-pill ${activeQuickFilters.includes('video') ? 'active' : ''}`}
                onClick={() => toggleQuickFilter('video')}
              >
                With Video
              </button>
            </div>

            {/* Results items rendering */}
            {loading ? (
              <div className="text-center py-5 my-5 bg-white border rounded shadow-sm">
                <div className="spinner-border text-primary" role="status">
                  <span className="visually-hidden">Searching properties...</span>
                </div>
              </div>
            ) : results.length === 0 ? (
              <div className="text-center py-5 border rounded-3 bg-white shadow-sm">
                <Search size={48} className="text-muted mx-auto mb-3" />
                <h2 className="h4 fw-bold">No properties matched your search</h2>
                <p className="text-muted small px-3">
                  Try adjusting filters, modifying budgets, or exploring neighboring localities.
                </p>
                <button className="btn btn-danger rounded-pill px-4 mt-3 text-dark fw-bold" onClick={clearFilters}>
                  Browse All Properties
                </button>
              </div>
            ) : (
              <div>
                {/* Search Toolbar */}
                <div className="d-flex justify-content-between align-items-center mb-3 bg-white p-3 border rounded shadow-sm">
                  <p className="mb-0 text-muted small">
                    Showing <strong>1</strong>–<strong>{results.length}</strong> of <strong>{totalResults}</strong> listings
                  </p>
                  <div className="d-flex align-items-center gap-2">
                    <span className="text-muted small d-none d-sm-inline">Sort By:</span>
                    <select
                      className="form-select form-select-sm border-0 bg-light fw-semibold text-dark"
                      style={{ width: '150px' }}
                      value={sortBy}
                      onChange={(e) => {
                        const value = e.target.value;
                        setSortBy(value);
                        pushRefineFilters({ sort: value });
                      }}
                    >
                      <option value="new">Latest</option>
                      <option value="price_asc">Price: Low to High</option>
                      <option value="price_desc">Price: High to Low</option>
                    </select>
                  </div>
                </div>

                {/* Wide Card List */}
                <div className="d-flex flex-column gap-4">
                  {results.map((p) => {
                    const imagesList = Array.isArray(p.image_urls) ? p.image_urls : [];
                    const thumbnail = p.thumbnail_url || imagesList[0] || '';
                    const isWishlisted = wishlistIds.includes(p.id);
                    const brochureUrl = resolveBrochureUrl(p.brochure_url);
                    const tags = getPropertyTags(p);
                    const typeLabel = p.property_type_label || p.property_type?.replace(/_/g, ' ') || 'Property';
                    const locationLine = [
                      p.bedrooms ? `${p.bedrooms} BHK` : null,
                      typeLabel,
                      p.locality ? `in ${p.locality}` : null,
                      p.city_name ? `, ${p.city_name}` : null,
                    ]
                      .filter(Boolean)
                      .join(' ');

                    return (
                      <article key={p.id} className="nb-search-list-card">
                        <div className="row g-0">
                          {/* Image Column */}
                          <div className="col-md-5">
                            <div className="nb-search-list-card__img-wrap">
                              <Link href={`/property/${p.slug}`} className="nb-search-list-card__img-link">
                                {thumbnail ? (
                                  <img 
                                    src={thumbnail} 
                                    alt={p.title} 
                                  />
                                ) : (
                                  <div className="nb-search-list-card__img-placeholder bg-light text-muted d-flex flex-column align-items-center justify-content-center">
                                    <span className="small">No Photo Provided</span>
                                  </div>
                                )}
                              </Link>
                              
                              <div className="nb-search-list-card__shade" />
                              <span className="nb-search-list-card__img-tag text-uppercase">
                                {p.listing_type === 'rent' ? 'For Rent' : 'For Sale'}
                              </span>
                              {Number(p.is_verified_property) === 1 && (
                                <span className="nb-search-list-card__rera">VERIFIED</span>
                              )}
                              {Number(p.is_newly_launched) === 1 && (
                                <span
                                  className="nb-search-list-card__rera"
                                  style={{ top: Number(p.is_verified_property) === 1 ? '2.5rem' : undefined }}
                                >
                                  NEW LAUNCH
                                </span>
                              )}

                              <div className="nb-search-list-card__price-badge">
                                {formatPrice(p)}
                              </div>

                              <button 
                                type="button" 
                                className="nb-search-list-card__wishlist-btn"
                                onClick={(e) => handleWishlistToggle(p.id, e)}
                                title="Add to wishlist"
                              >
                                <Heart size={16} fill={isWishlisted ? '#ef4444' : 'none'} className={isWishlisted ? 'text-danger' : 'text-muted'} />
                              </button>
                            </div>
                          </div>

                          {/* Info Column */}
                          <div className="col-md-7">
                            <div className="nb-search-list-card__body">
                              <div>
                                <h3 className="nb-search-list-card__title">
                                  <Link href={`/property/${p.slug}`} className="text-decoration-none text-dark">
                                    {p.title}
                                  </Link>
                                </h3>
                                <p className="nb-search-list-card__sub d-flex align-items-center gap-1">
                                  <MapPin size={12} className="text-danger" />
                                  <span>{locationLine}</span>
                                </p>

                                {/* Config Table details */}
                                <div className="nb-search-list-card__config-table row g-0 text-center py-2 rounded">
                                  <div className="col-4 border-end">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Layout</span>
                                    <strong className="text-dark small">{formatLayout(p)}</strong>
                                  </div>
                                  <div className="col-4 border-end">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Area Size</span>
                                    <strong className="text-dark small">
                                      {p.area_sqft ? `${p.area_sqft} sqft` : '—'}
                                    </strong>
                                  </div>
                                  <div className="col-4">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Baths</span>
                                    <strong className="text-dark small">
                                      {p.bathrooms != null ? `${p.bathrooms} Baths` : '—'}
                                    </strong>
                                  </div>
                                </div>

                                {tags.length > 0 && (
                                  <div className="nb-search-list-card__landmark-chips">
                                    {tags.map((tag) => (
                                      <span key={`${p.id}-${tag}`} className="nb-landmark-chip">
                                        {tag}
                                      </span>
                                    ))}
                                  </div>
                                )}

                                {p.description && (
                                  <p className="nb-search-list-card__desc text-truncate">
                                    {p.description}
                                  </p>
                                )}
                              </div>

                              {/* Footer block */}
                              <div className="nb-search-list-card__footer">
                                <div className="nb-search-list-card__posted-by">
                                  Posted by: <strong>{getPostedByLabel(p)}</strong>
                                  {p.created_at && (
                                    <span className="text-muted"> · {formatRelativeTime(p.created_at)}</span>
                                  )}
                                  {typeof p.views === 'number' && p.views > 0 && (
                                    <span className="text-muted"> · {p.views} views</span>
                                  )}
                                </div>
                                <div className="nb-search-list-card__actions">
                                  {brochureUrl && (
                                    <a
                                      href={brochureUrl}
                                      target="_blank"
                                      rel="noopener noreferrer"
                                      className="nb-list-btn-outline d-flex align-items-center gap-1 text-decoration-none"
                                    >
                                      <FileText size={12} />
                                      <span>Brochure</span>
                                    </a>
                                  )}
                                  {p.owner_phone && (
                                    <button
                                      type="button"
                                      className="nb-list-btn-filled d-flex align-items-center gap-1"
                                      onClick={() => handleViewNumber(p)}
                                    >
                                      <Phone size={12} />
                                      <span>View Number</span>
                                    </button>
                                  )}
                                </div>
                              </div>

                            </div>
                          </div>

                        </div>
                      </article>
                    );
                  })}
                </div>

                {/* Pagination */}
                {totalResults > results.length && (
                  <div className="text-center py-4 mt-3">
                    <button
                      className="btn btn-outline-primary px-5 rounded-pill d-inline-flex align-items-center gap-1"
                      onClick={loadMore}
                      disabled={loadingMore}
                    >
                      {loadingMore ? (
                        <>
                          <RefreshCw size={14} className="spinner" />
                          <span>Loading more...</span>
                        </>
                      ) : (
                        <span>Load More Listings</span>
                      )}
                    </button>
                  </div>
                )}

              </div>
            )}
          </div>

        </div>
      </div>
      
      {/* Mobile filter drawer trigger button (sticky bottom) */}
      <div className="d-lg-none position-fixed bottom-0 start-50 translate-middle-x mb-3" style={{ zIndex: 1000 }}>
        <button 
          type="button" 
          className="btn btn-dark d-flex align-items-center gap-2 px-4 py-2.5 rounded-pill shadow-lg border-2 border-white text-white fw-bold"
          onClick={() => setShowMobileFilters(true)}
        >
          <Sliders size={16} />
          <span>Filters &amp; Sort</span>
        </button>
      </div>

      {/* Mobile Filters Drawer Modal */}
      {showMobileFilters && (
        <div className="position-fixed inset-0 bg-dark bg-opacity-50" style={{ zIndex: 1050, left: 0, top: 0, right: 0, bottom: 0 }}>
          <div className="position-fixed end-0 top-0 bottom-0 bg-white h-100 shadow-lg p-4" style={{ width: '85%', maxWidth: '380px', overflowY: 'auto', transition: 'transform 0.3s ease' }}>
            <div className="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
              <h3 className="h6 fw-bold text-dark m-0 d-flex align-items-center gap-2">
                <Filter size={16} />
                <span>Filters &amp; Sort</span>
              </h3>
              <button 
                type="button" 
                className="btn btn-light rounded-circle p-1 d-flex"
                onClick={() => setShowMobileFilters(false)}
              >
                <X size={18} />
              </button>
            </div>
            {renderFilterForm('mobile')}
          </div>
        </div>
      )}

      <OwnerPhoneModal
        show={!!phoneModal}
        onClose={() => setPhoneModal(null)}
        ownerName={phoneModal?.ownerName}
        ownerPhone={phoneModal?.ownerPhone}
        propertyTitle={phoneModal?.propertyTitle}
      />

    </div>
  );
}

export default function SearchPage() {
  return (
    <Suspense fallback={
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading search...</span>
        </div>
      </div>
    }>
      <SearchContent />
    </Suspense>
  );
}
