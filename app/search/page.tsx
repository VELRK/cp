'use client';

import React, { useState, useEffect, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import api from '../../lib/api';
import { useAuth } from '../../components/AuthContext';
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
  is_featured?: number;
  images?: string | string[];
  image_urls?: string[];
  thumbnail_url?: string;
  property_type_label?: string;
  description?: string;
}

const PROPERTY_TYPES = [
  { val: 'apartment', label: 'Apartment / Flat' },
  { val: 'house', label: 'Independent House' },
  { val: 'villa', label: 'Villa / Duplex' },
  { val: 'plot', label: 'Plot / Land' },
  { val: 'commercial', label: 'Commercial' },
  { val: 'office', label: 'Office Space' },
  { val: 'retail', label: 'Retail / Shop' },
  { val: 'warehouse', label: 'Warehouse / Godown' },
  { val: 'farmhouse', label: 'Farmhouse' },
  { val: 'pg', label: 'PG Accommodation' },
  { val: 'shared_flat', label: 'Shared Flat' },
  { val: 'serviced_apartment', label: 'Serviced Apartment' },
];

function SearchContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const { user, setAuthModalOpen } = useAuth();

  // Filters from URL/State
  const [cityId, setCityId] = useState(searchParams.get('city_id') || '');
  const [locality, setLocality] = useState(searchParams.get('q') || '');
  const [listingType, setListingType] = useState(searchParams.get('listing_type') || '');
  const [propertyType, setPropertyType] = useState(searchParams.get('property_type') || '');
  const [minPrice, setMinPrice] = useState(searchParams.get('min_price') || '');
  const [maxPrice, setMaxPrice] = useState(searchParams.get('max_price') || '');
  const [bedrooms, setBedrooms] = useState(searchParams.get('bedrooms') || '');
  const [sortBy, setSortBy] = useState(searchParams.get('sort') || 'new');

  // Horizontal top filter tags
  const [activeQuickFilters, setActiveQuickFilters] = useState<string[]>([]);
  const [verifiedOnly, setVerifiedOnly] = useState(false);

  // App UI states
  const [cities, setCities] = useState<City[]>([]);
  const [results, setResults] = useState<Property[]>([]);
  const [totalResults, setTotalResults] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [showMobileFilters, setShowMobileFilters] = useState(false);

  // Keep track of wishlists locally for immediate toggle response
  const [wishlistIds, setWishlistIds] = useState<number[]>([]);

  // Fetch cities list
  useEffect(() => {
    api.get('/api/nb/cities')
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((err) => console.error('Error fetching cities', err));
  }, []);

  // Fetch results when searchParams or filters change
  useEffect(() => {
    setLoading(true);
    setCurrentPage(1);

    const params: any = {};
    if (cityId) params.city_id = cityId;
    if (locality) params.q = locality;
    if (listingType) params.listing_type = listingType;
    if (propertyType) params.property_type = propertyType;
    if (minPrice) params.min_price = minPrice;
    if (maxPrice) params.max_price = maxPrice;
    if (bedrooms) params.bedrooms = bedrooms;
    if (sortBy) params.sort = sortBy;
    params.page = 1;
    params.limit = 12;

    api.get('/api/nb/search', { params })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setResults(res.data.items);
          setTotalResults(res.data.total || res.data.items.length);
        }
      })
      .catch((err) => console.error('Error searching properties', err))
      .finally(() => setLoading(false));
  }, [searchParams]);

  // Load more pagination
  const loadMore = () => {
    setLoadingMore(true);
    const nextPage = currentPage + 1;

    const params: any = {};
    if (cityId) params.city_id = cityId;
    if (locality) params.q = locality;
    if (listingType) params.listing_type = listingType;
    if (propertyType) params.property_type = propertyType;
    if (minPrice) params.min_price = minPrice;
    if (maxPrice) params.max_price = maxPrice;
    if (bedrooms) params.bedrooms = bedrooms;
    if (sortBy) params.sort = sortBy;
    params.page = nextPage;
    params.limit = 12;

    api.get('/api/nb/search', { params })
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
        api.get(`/api/nb/wishlist/check?property_id=${p.id}&user_id=${user.id}`)
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
      const response = await api.post('/api/nb/wishlist/toggle', {
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

  const applyFilters = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    setShowMobileFilters(false);

    const queryParams = new URLSearchParams();
    if (cityId) queryParams.append('city_id', cityId);
    if (locality) queryParams.append('q', locality);
    if (listingType) queryParams.append('listing_type', listingType);
    if (propertyType) queryParams.append('property_type', propertyType);
    if (minPrice) queryParams.append('min_price', minPrice);
    if (maxPrice) queryParams.append('max_price', maxPrice);
    if (bedrooms) queryParams.append('bedrooms', bedrooms);
    if (sortBy) queryParams.append('sort', sortBy);

    router.push(`/search?${queryParams.toString()}`);
  };

  const clearFilters = () => {
    setCityId('');
    setLocality('');
    setListingType('');
    setPropertyType('');
    setMinPrice('');
    setMaxPrice('');
    setBedrooms('');
    setSortBy('new');
    setActiveQuickFilters([]);
    setVerifiedOnly(false);
    router.push('/search');
  };

  const toggleQuickFilter = (tag: string) => {
    if (activeQuickFilters.includes(tag)) {
      setActiveQuickFilters((prev) => prev.filter((t) => t !== tag));
    } else {
      setActiveQuickFilters((prev) => [...prev, tag]);
    }
  };

  const formatPrice = (price: number) => {
    if (price >= 10000000) {
      return `₹ ${(price / 10000000).toFixed(2)} Cr`;
    }
    return `₹ ${(price / 100000).toFixed(0)} L`;
  };

  // Sidebar Filter Layout
  const renderFilterForm = (sfx: string) => (
    <form onSubmit={applyFilters} className="nb-search-filter-form">
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

      {/* Property Type */}
      <div className="mb-3">
        <label className="form-label nb-filter-label">Property Type</label>
        <select
          className="form-select form-select-sm nb-filter-control"
          value={propertyType}
          onChange={(e) => setPropertyType(e.target.value)}
        >
          <option value="">Any Type</option>
          {PROPERTY_TYPES.map((pt) => (
            <option key={pt.val} value={pt.val}>{pt.label}</option>
          ))}
        </select>
      </div>

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
          onChange={(e) => setSortBy(e.target.value)}
        >
          <option value="new">Latest Listed</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
        </select>
      </div>

      <button type="submit" className="btn btn-danger w-100 rounded-pill fw-semibold mb-2 text-dark">
        Apply Filters
      </button>
      <button
        type="button"
        className="btn btn-outline-secondary btn-sm w-100 rounded-pill"
        onClick={clearFilters}
      >
        Reset Filters
      </button>
    </form>
  );

  return (
    <div className="nb-search-page bg-light" style={{ minHeight: '100vh', paddingTop: '5.5rem' }}>
      
      {/* Breadcrumbs and summary header */}
      <div className="container py-3">
        <nav aria-label="breadcrumb">
          <ol className="breadcrumb small text-muted mb-2">
            <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-muted">Home</Link></li>
            <li className="breadcrumb-item"><Link href="/search" className="text-decoration-none text-muted">Property In Coimbatore</Link></li>
            {locality && (
              <li className="breadcrumb-item active text-dark fw-semibold" aria-current="page">
                Property in {locality}
              </li>
            )}
          </ol>
        </nav>

        {/* Dynamic Title */}
        <h1 className="h4 fw-bold text-dark mb-1">
          {totalResults} results | Property in {locality || 'Coimbatore'} for {listingType === 'rent' ? 'Rent' : 'Sale'}
        </h1>
        <p className="text-muted small mb-3">
          Get to know more about <Link href="/about" className="text-primary text-decoration-none fw-semibold">Coimbatore Locality →</Link>
        </p>
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
                    <X size={12} className="cursor-pointer text-muted" onClick={() => setLocality('')} />
                  </span>
                )}
                {listingType && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{listingType === 'rent' ? 'For Rent' : 'For Sale'}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => setListingType('')} />
                  </span>
                )}
                {propertyType && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{propertyType}</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => setPropertyType('')} />
                  </span>
                )}
                {bedrooms && (
                  <span className="badge bg-light border text-dark py-1.5 px-2 rounded-1 d-flex align-items-center gap-1">
                    <span>{bedrooms} BHK</span>
                    <X size={12} className="cursor-pointer text-muted" onClick={() => setBedrooms('')} />
                  </span>
                )}
                {!locality && !listingType && !propertyType && !bedrooms && (
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
                  onChange={(e) => setVerifiedOnly(e.target.checked)}
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
                        setSortBy(e.target.value);
                        applyFilters();
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

                    return (
                      <article key={p.id} className="nb-search-list-card">
                        <div className="row g-0">
                          {/* Image Column */}
                          <div className="col-md-5">
                            <div className="nb-search-list-card__img-wrap">
                              <Link href={`/property-detail/${p.slug}`}>
                                {thumbnail ? (
                                  <img 
                                    src={thumbnail} 
                                    alt={p.title} 
                                    className="w-100 h-100 object-fit-cover position-absolute"
                                    style={{ inset: 0 }}
                                  />
                                ) : (
                                  <div className="w-100 h-100 bg-light text-muted d-flex flex-column align-items-center justify-content-center position-absolute" style={{ inset: 0 }}>
                                    <span className="small">No Photo Provided</span>
                                  </div>
                                )}
                              </Link>
                              
                              <div className="nb-search-list-card__shade" />
                              <span className="nb-search-list-card__img-tag text-uppercase">
                                {p.listing_type === 'rent' ? 'For Rent' : 'For Sale'}
                              </span>
                              <span className="nb-search-list-card__rera">
                                RERA APPROVED
                              </span>

                              <div className="nb-search-list-card__price-badge">
                                {formatPrice(p.price)}
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
                                  <Link href={`/property-detail/${p.slug}`} className="text-decoration-none text-dark">
                                    {p.title}
                                  </Link>
                                </h3>
                                <p className="nb-search-list-card__sub d-flex align-items-center gap-1">
                                  <MapPin size={12} className="text-danger" />
                                  <span>{p.bedrooms} BHK {p.property_type_label || p.property_type} in {p.locality}, {p.city_name || 'Coimbatore'}</span>
                                </p>

                                {/* Config Table details */}
                                <div className="nb-search-list-card__config-table row g-0 text-center py-2 rounded">
                                  <div className="col-4 border-end">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Layout</span>
                                    <strong className="text-dark small">{p.bedrooms} BHK</strong>
                                  </div>
                                  <div className="col-4 border-end">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Area Size</span>
                                    <strong className="text-dark small">{p.area_sqft} sqft</strong>
                                  </div>
                                  <div className="col-4">
                                    <span className="text-muted d-block" style={{ fontSize: '0.65rem' }}>Baths</span>
                                    <strong className="text-dark small">{p.bathrooms} Baths</strong>
                                  </div>
                                </div>

                                {/* Near by Landmarks */}
                                <div className="nb-search-list-card__landmark-chips">
                                  <span className="nb-landmark-chip">Near IT Corridor</span>
                                  <span className="nb-landmark-chip">Gated Security</span>
                                  <span className="nb-landmark-chip">Zero Brokerage</span>
                                </div>

                                {/* Description snippet */}
                                <p className="nb-search-list-card__desc text-truncate">
                                  {p.description || `Excellent modern ${p.property_type} ready for immediate occupancy in the premium locality of ${p.locality}.`}
                                </p>
                              </div>

                              {/* Footer block */}
                              <div className="nb-search-list-card__footer">
                                <div className="nb-search-list-card__posted-by">
                                  Posted by: <strong>Owner</strong> <span className="text-muted">· 1 month ago</span>
                                </div>
                                <div className="nb-search-list-card__actions">
                                  <button 
                                    type="button" 
                                    className="nb-list-btn-outline d-flex align-items-center gap-1"
                                    onClick={() => alert(`Brochure details for project ID #${p.id} successfully queued for download!`)}
                                  >
                                    <FileText size={12} />
                                    <span>Brochure</span>
                                  </button>
                                  <button 
                                    type="button" 
                                    className="nb-list-btn-filled d-flex align-items-center gap-1"
                                    onClick={() => {
                                      if (user) {
                                        alert('Contact owner number: +91 98765 43210 (Direct Route)');
                                      } else {
                                        setAuthModalOpen('login');
                                      }
                                    }}
                                  >
                                    <Phone size={12} />
                                    <span>View Number</span>
                                  </button>
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
