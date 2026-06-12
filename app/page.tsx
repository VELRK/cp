'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import api from '../lib/api';
import { useAuth } from '../components/AuthContext';
import {
  Search,
  MapPin,
  Mic,
  Navigation,
  User,
  Plus,
  Heart,
  ArrowRight,
  ChevronRight,
  ChevronLeft,
  Info,
  Layers,
  Map,
  BookOpen,
  Bed,
  Bath,
  Grid,
  Sparkles,
  Clock,
  Compass,
  Sliders,
  ChevronDown
} from 'lucide-react';

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
}

export interface Blog {
  id: number;
  name: string;
  author: string;
  date: string;
  short_notes: string;
  description: string;
  gallery: string[];
  image: string | null;
}

export default function Home() {
  const router = useRouter();
  const { user, setAuthModalOpen } = useAuth();

  // Search parameters
  const [listingType, setListingType] = useState<'sale' | 'rent'>('sale');
  const [propertyType, setPropertyType] = useState('apartment');
  const [cityId, setCityId] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const [bedrooms, setBedrooms] = useState('');
  const [sortBy, setSortBy] = useState('new');
  const [showAdvanced, setShowAdvanced] = useState(false);

  // Data states
  const [cities, setCities] = useState<City[]>([]);
  const [featured, setFeatured] = useState<Property[]>([]);
  const [loadingFeatured, setLoadingFeatured] = useState(true);
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loadingBlogs, setLoadingBlogs] = useState(true);

  // Hero slideshow states
  const [heroSlides, setHeroSlides] = useState<Property[]>([]);
  const [loadingHero, setLoadingHero] = useState(true);
  const [currentSlide, setCurrentSlide] = useState(0);

  // Voice Search states
  const [voiceStatus, setVoiceStatus] = useState<'idle' | 'listening' | 'success' | 'error'>('idle');

  // Wishlist states
  const [wishlistedIds, setWishlistedIds] = useState<number[]>([]);

  // Fetch Cities and Blogs once on mount
  useEffect(() => {
    // 1. Cities
    api.get('/api/nb/cities')
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
          // Auto-select Coimbatore if present
          const cbe = res.data.cities.find((c: City) => c.name.toLowerCase().includes('coimbatore'));
          if (cbe) {
            setCityId(cbe.id.toString());
          } else if (res.data.cities.length > 0) {
            setCityId(res.data.cities[0].id.toString());
          }
        }
      })
      .catch((e) => console.warn('Could not fetch cities', e));

    // 2. Blogs/Articles
    api.get('/api/blogs')
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogs(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs', e))
      .finally(() => setLoadingBlogs(false));
  }, []);

  // Fetch Recommended Properties & Hero Banner Slideshow whenever selected City changes
  useEffect(() => {
    setLoadingFeatured(true);
    setLoadingHero(true);

    const cityParam = cityId ? `&city_id=${cityId}` : '';

    // Fetch Recommended Properties
    api.get(`/api/nb/search?limit=8${cityParam}`)
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setFeatured(res.data.items);
        }
      })
      .catch((e) => console.warn('Could not fetch featured listings', e))
      .finally(() => setLoadingFeatured(false));

    // Fetch Hero Banner Slideshow
    api.get(`/api/nb/search?limit=5&sort=featured${cityParam}`)
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setHeroSlides(res.data.items);
        }
      })
      .catch((e) => console.warn('Could not fetch hero slides', e))
      .finally(() => setLoadingHero(false));
  }, [cityId]);

  // Fetch wishlist IDs if logged in
  useEffect(() => {
    if (user) {
      api.get(`/api/nb/wishlist?userId=${user.id}`)
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.wishlist)) {
            setWishlistedIds(res.data.wishlist.map((item: any) => parseInt(item.propertyId, 10)));
          }
        })
        .catch((e) => console.warn('Could not fetch wishlist', e));
    } else {
      setWishlistedIds([]);
    }
  }, [user]);

  // Slideshow interval timer
  useEffect(() => {
    const slidesCount = heroSlides.length > 0 ? heroSlides.length : 3; // fallback uses 3
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slidesCount);
    }, 5500);
    return () => clearInterval(interval);
  }, [heroSlides]);

  // Enable mouse drag-to-scroll and vertical mouse-wheel to horizontal scroll
  useEffect(() => {
    const sliders = document.querySelectorAll('.nb-horizontal-scroll');

    sliders.forEach((slider: Element) => {
      const el = slider as HTMLElement;
      let isDown = false;
      let startX: number;
      let scrollLeft: number;

      const onMouseDown = (e: MouseEvent) => {
        isDown = true;
        el.classList.add('active');
        startX = e.pageX - el.offsetLeft;
        scrollLeft = el.scrollLeft;
      };

      const onMouseLeave = () => {
        isDown = false;
        el.classList.remove('active');
      };

      const onMouseUp = () => {
        isDown = false;
        el.classList.remove('active');
      };

      const onMouseMove = (e: MouseEvent) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - el.offsetLeft;
        const walk = (x - startX) * 2; // Scroll speed multiplier
        el.scrollLeft = scrollLeft - walk;
      };

      const onWheel = (e: WheelEvent) => {
        if (e.deltaY !== 0 && Math.abs(e.deltaX) < Math.abs(e.deltaY)) {
          const maxScrollLeft = el.scrollWidth - el.clientWidth;
          // Only scroll horizontally if we aren't at the very edge of the scroll
          if ((e.deltaY < 0 && el.scrollLeft > 0) || (e.deltaY > 0 && el.scrollLeft < maxScrollLeft)) {
            e.preventDefault();
            el.scrollLeft += e.deltaY;
          }
        }
      };

      el.addEventListener('mousedown', onMouseDown);
      el.addEventListener('mouseleave', onMouseLeave);
      el.addEventListener('mouseup', onMouseUp);
      el.addEventListener('mousemove', onMouseMove);
      el.addEventListener('wheel', onWheel, { passive: false });

      return () => {
        el.removeEventListener('mousedown', onMouseDown);
        el.removeEventListener('mouseleave', onMouseLeave);
        el.removeEventListener('mouseup', onMouseUp);
        el.removeEventListener('mousemove', onMouseMove);
        el.removeEventListener('wheel', onWheel);
      };
    });

    // Global click handler for arrow buttons
    const handleScrollArrow = (e: MouseEvent) => {
      const btn = (e.target as HTMLElement).closest('.nb-scroll-arrow');
      if (!btn) return;
      const wrapper = btn.closest('.nb-scroll-wrapper');
      if (!wrapper) return;
      const scrollContainer = wrapper.querySelector('.nb-horizontal-scroll');
      if (!scrollContainer) return;

      const direction = btn.classList.contains('nb-scroll-arrow-left') ? -1 : 1;
      const scrollAmount = Math.max(scrollContainer.clientWidth * 0.8, 300);
      scrollContainer.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    };

    document.addEventListener('click', handleScrollArrow);

    return () => {
      document.removeEventListener('click', handleScrollArrow);
    };
  }, [cities, featured]);

  // Fallback Slides if heroSlides is loading/empty
  const fallbackSlides: Property[] = [
    {
      id: -1,
      title: "Step Into a World of Prime Living",
      locality: "Nehru Nagar West, Sitra, Coimbatore",
      price: 12000000,
      image_urls: ["https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1920&q=80"],
      slug: "search?q=Sitra",
      property_type: "apartment",
      listing_type: 'sale',
      bedrooms: 3,
      bathrooms: 3,
      area_sqft: 1800
    },
    {
      id: -2,
      title: "Luxury Duplex Villas & Row Houses",
      locality: "Avinashi Road, Civil Aerodrome, Coimbatore",
      price: 18500000,
      image_urls: ["https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1920&q=80"],
      slug: "search?property_type=villa",
      property_type: "villa",
      listing_type: 'sale',
      bedrooms: 4,
      bathrooms: 4,
      area_sqft: 2800
    },
    {
      id: -3,
      title: "Premium Plots & Commercial Land",
      locality: "Saravanampatti, IT Corridor, Coimbatore",
      price: 4500000,
      image_urls: ["https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1920&q=80"],
      slug: "search?property_type=plot",
      property_type: "plot",
      listing_type: 'sale',
      bedrooms: 0,
      bathrooms: 0,
      area_sqft: 2400
    }
  ];

  const activeSlides = heroSlides.length > 0 ? heroSlides : fallbackSlides;

  // Real Browser voice search helper using Speech Recognition API
  const handleVoiceSearch = () => {
    if (typeof window !== 'undefined') {
      const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
      if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'en-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        setVoiceStatus('listening');
        recognition.start();

        recognition.onresult = (event: any) => {
          const speechToText = event.results[0][0].transcript;
          setSearchQuery(speechToText);
          setVoiceStatus('success');
          setTimeout(() => setVoiceStatus('idle'), 1500);
        };

        recognition.onerror = (e: any) => {
          console.warn('Speech recognition error', e.error);
          setVoiceStatus('error');
          setTimeout(() => setVoiceStatus('idle'), 1500);
        };

        recognition.onend = () => {
          setVoiceStatus('idle');
        };
      } else {
        alert('Voice recognition is not supported in this browser. Please type your query.');
      }
    }
  };

  const handleWishlistToggle = async (e: React.MouseEvent, propertyId: number) => {
    e.preventDefault();
    e.stopPropagation();

    if (!user) {
      setAuthModalOpen('login');
      return;
    }

    try {
      const response = await api.post('/api/nb/wishlist/toggle', {
        property_id: propertyId,
        userId: user.id,
      });
      if (response.data?.success) {
        setWishlistedIds((prev) =>
          prev.includes(propertyId)
            ? prev.filter((id) => id !== propertyId)
            : [...prev, propertyId]
        );
      }
    } catch (error) {
      console.error('Error toggling wishlist:', error);
    }
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const queryParams = new URLSearchParams();
    queryParams.append('listing_type', listingType);
    if (propertyType) queryParams.append('property_type', propertyType);
    if (cityId) queryParams.append('city_id', cityId);
    if (searchQuery) queryParams.append('q', searchQuery);
    if (minPrice) queryParams.append('min_price', minPrice);
    if (maxPrice) queryParams.append('max_price', maxPrice);
    if (bedrooms) queryParams.append('bedrooms', bedrooms);
    if (sortBy) queryParams.append('sort', sortBy);

    router.push(`/search?${queryParams.toString()}`);
  };

  const getDashboardPath = () => {
    if (user?.role === 'owner') return '/owner/dashboard';
    return '/tenant/dashboard';
  };

  const formatPrice = (price: number) => {
    if (price >= 10000000) {
      return `₹${(price / 10000000).toFixed(1)} Cr`;
    } else if (price >= 100000) {
      return `₹${(price / 100000).toFixed(0)} L`;
    }
    return `₹${price.toLocaleString('en-IN')}`;
  };

  const getPropertyTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
      apartment: 'Apartment',
      house: 'Independent House',
      villa: 'Villa',
      plot: 'Plot/Land',
      commercial: 'Commercial Space'
    };
    return labels[type] || type;
  };

  // Helper search filters
  function applySearchFilter(type: string) {
    const queryParams = new URLSearchParams();
    queryParams.append('listing_type', listingType);
    queryParams.append('property_type', type);
    if (cityId) queryParams.append('city_id', cityId);
    router.push(`/search?${queryParams.toString()}`);
  }

  function handleLocationSearch() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          router.push(`/search?lat=${latitude}&lng=${longitude}&radius_km=5&city_id=${cityId}&listing_type=${listingType}`);
        },
        (error) => {
          console.warn('Geolocation error:', error);
          alert('Could not retrieve current location. Searching all areas.');
          router.push(`/search?city_id=${cityId}&listing_type=${listingType}`);
        }
      );
    } else {
      alert('Geolocation is not supported by your browser.');
    }
  }

  return (
    <div className="home-container" style={{ background: '#f8fafc', paddingBottom: '3rem' }}>

      {/* 1. API-Called Slideshow Banner */}
      <section className="nb-hero-slider">
        {activeSlides.map((slide, index) => {
          const imagesList = Array.isArray(slide.image_urls) ? slide.image_urls : [];
          const bgImg = slide.thumbnail_url || imagesList[0] || '';
          return (
            <div
              key={slide.id}
              className={`nb-hero-slide ${index === currentSlide ? 'active' : ''}`}
              style={{ backgroundImage: `url('${bgImg}')` }}
            >
              <div className="nb-hero-slide-overlay" />
              <div className="nb-hero-slide-info">
                <span className="nb-hero-slide-badge">
                  {slide.property_type === 'plot' ? 'Plot / Land' : `${slide.bedrooms} BHK ${getPropertyTypeLabel(slide.property_type)}`}
                </span>
                <h1 className="nb-hero-slide-title">{slide.title}</h1>
                <p className="nb-hero-slide-desc">
                  Located in the premium area of <span className="text-accent fw-bold">{slide.locality}</span> | Start living your dreams today.
                </p>
                <div className="d-flex justify-content-between align-items-center">
                  <div className="nb-hero-slide-price">{formatPrice(slide.price)}</div>
                  {slide.slug ? (
                    <Link href={slide.slug.startsWith('search') ? `/${slide.slug}` : `/property-detail/${slide.slug}`} className="btn btn-outline-light btn-sm px-3 py-1.5 fw-semibold border-2 rounded-pill">
                      Explore Now →
                    </Link>
                  ) : null}
                </div>
              </div>
            </div>
          );
        })}
      </section>

      {/* Main content grid */}
      <div className="container">

        {/* 2. Glassmorphic Overlapping Search Panel */}
        <div className="nb-search-card-premium fade-in-up">
          {/* Tab Header Row */}
          <div className="nb-search-tabs-premium-row">
            <ul className="nb-search-tabs-premium-list">
              <li>
                <button
                  type="button"
                  className={`nb-search-tab-premium-btn ${listingType === 'sale' ? 'active' : ''}`}
                  onClick={() => setListingType('sale')}
                >
                  Buy
                </button>
              </li>
              <li>
                <button
                  type="button"
                  className={`nb-search-tab-premium-btn ${listingType === 'rent' ? 'active' : ''}`}
                  onClick={() => setListingType('rent')}
                >
                  Rent
                </button>
              </li>
              <li>
                <button
                  type="button"
                  className="nb-search-tab-premium-btn"
                  onClick={() => {
                    setListingType('sale');
                    setPropertyType('apartment');
                    router.push('/search?sort=new');
                  }}
                >
                  New Launch
                  <span className="badge-dot" />
                </button>
              </li>
              <li>
                <button
                  type="button"
                  className="nb-search-tab-premium-btn"
                  onClick={() => {
                    setPropertyType('commercial');
                    applySearchFilter('commercial');
                  }}
                >
                  Commercial
                </button>
              </li>
              <li>
                <button
                  type="button"
                  className="nb-search-tab-premium-btn"
                  onClick={() => {
                    setPropertyType('plot');
                    applySearchFilter('plot');
                  }}
                >
                  Plots/Land
                </button>
              </li>
            </ul>
            <Link href={user ? '/owner/property/add' : '#'} onClick={(e) => {
              if (!user) {
                e.preventDefault();
                setAuthModalOpen('login');
              }
            }} className="nb-post-property-free-link my-2">
              Post Property <span className="badge bg-success text-white py-1 px-1.5 ms-1">FREE</span>
            </Link>
          </div>

          {/* Search Inputs Row */}
          <form onSubmit={handleSearchSubmit} className="nb-search-inputs-premium-row">
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

            {/* Property Type Selector */}
            <div className="nb-search-select-premium-wrap" style={{ width: '160px' }}>
              <select
                className="form-select"
                value={propertyType}
                onChange={(e) => setPropertyType(e.target.value)}
              >
                <option value="">Any Type</option>
                <option value="apartment">Apartment</option>
                <option value="house">Independent House</option>
                <option value="villa">Villas &amp; Duplex</option>
                <option value="plot">Plots &amp; Land</option>
                <option value="commercial">Commercial Space</option>
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
            <div className="p-4 border-top" style={{ background: '#f8fafc', borderBottomLeftRadius: '20px', borderBottomRightRadius: '20px' }}>
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

        {/* 3. Two Columns Grid Layout (Left Content, Right Sidebar widgets) */}
        <div className="row g-4 mt-3">

          {/* Left Main Content Column */}
          <div className="col-lg-9">

            {/* Quick Circular Category Pills */}
            <div className="mb-5 fade-in-up">
              <h2 className="h4 fw-bold text-dark mb-3">Explore Properties by Category</h2>
              <div className="nb-quick-cat-strip">
                {[
                  { name: 'Residential Apartments', count: '1,400+ Properties', bg: 'rgba(238, 242, 255, 0.7)', iconBg: '#eef2ff', iconColor: '#4f46e5', query: 'apartment', icon: <Layers size={22} /> },
                  { name: 'Independent Houses & Villas', count: '4,000+ Properties', bg: 'rgba(236, 253, 245, 0.7)', iconBg: '#ecfdf5', iconColor: '#10b981', query: 'villa', icon: <Compass size={22} /> },
                  { name: 'Residential Plots & Land', count: '6,900+ Properties', bg: 'rgba(255, 240, 246, 0.7)', iconBg: '#fff0f6', iconColor: '#db2777', query: 'plot', icon: <MapPin size={22} /> },
                  { name: 'Commercial Shops & Space', count: '800+ Properties', bg: 'rgba(254, 243, 199, 0.7)', iconBg: '#fef3c7', iconColor: '#d97706', query: 'commercial', icon: <BookOpen size={22} /> }
                ].map((cat, i) => (
                  <Link
                    key={i}
                    href={`/search?property_type=${cat.query}`}
                    className="nb-quick-cat-card-new"
                  >
                    <div className="nb-quick-cat-icon-wrap" style={{ background: cat.iconBg, color: cat.iconColor }}>
                      {cat.icon}
                    </div>
                    <div className="nb-quick-cat-title">{cat.name}</div>
                    <div className="nb-quick-cat-count">{cat.count}</div>
                  </Link>
                ))}
              </div>
            </div>

            {/* Continue Browsing Quick Links */}
            {/* <div className="mb-5 fade-in-up">
              <span className="text-secondary small d-block mb-2 fw-semibold">Continue browsing...</span>
              <div className="d-flex gap-2 flex-wrap">
                <Link href="/search?city_id=1" className="btn btn-white bg-white border btn-sm shadow-sm rounded-pill py-2 px-3 d-flex align-items-center gap-2">
                  <span className="bg-primary-soft nb-text-brand p-1 rounded-circle d-flex"><MapPin size={12} /></span>
                  <span className="fw-semibold text-dark small">Buy in Coimbatore</span>
                </Link>
                <button onClick={() => alert('Search and select other major cities in Tamil Nadu')} className="btn btn-white bg-white border btn-sm shadow-sm rounded-pill py-2 px-3 d-flex align-items-center gap-2">
                  <span className="bg-light text-secondary p-1 rounded-circle d-flex"><Navigation size={12} /></span>
                  <span className="fw-semibold text-secondary small">Explore New City</span>
                </button>
              </div>
            </div> */}

            {/* Recommended Properties Horizontal Slider */}
            <div className="mb-5 fade-in-up">
              <div className="d-flex justify-content-between align-items-end mb-3">
                <div>
                  <h2 className="h4 fw-bold text-dark m-0">Recommended Properties</h2>
                  <p className="text-muted small m-0">Curated premium properties in Coimbatore</p>
                </div>
                <Link href="/search" className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold">
                  <span>See All</span>
                  <ChevronRight size={16} />
                </Link>
              </div>

              {loadingFeatured ? (
                <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
                  <div className="spinner-border nb-text-brand" role="status">
                    <span className="visually-hidden">Loading properties...</span>
                  </div>
                </div>
              ) : (
                <div className="nb-scroll-wrapper">
                  <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
                  <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
                  <div className="nb-horizontal-scroll">
                    {featured.map((p) => {
                      const imagesList = Array.isArray(p.image_urls) ? p.image_urls : [];
                      const thumbnail = p.thumbnail_url || imagesList[0] || '';
                      const isLiked = wishlistedIds.includes(p.id);
                      return (
                        <div key={p.id} className="nb-classic-property-card-wrap">
                          <div className="nb-classic-card">
                            <div className="nb-classic-card-img-container">
                              <Link href={`/property-detail/${p.slug}`}>
                                {thumbnail ? (
                                  <img
                                    src={thumbnail}
                                    alt={p.title}
                                    className="nb-classic-card-img"
                                  />
                                ) : (
                                  <div className="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted small">
                                    <span>No Photo</span>
                                  </div>
                                )}
                              </Link>

                              <div className="nb-classic-card-price-overlay">
                                {formatPrice(p.price)}
                              </div>

                              <span className={`nb-classic-card-badge nb-classic-card-badge--${p.listing_type}`}>
                                For {p.listing_type === 'rent' ? 'Rent' : 'Sale'}
                              </span>

                              {p.is_featured === 1 && (
                                <span className="nb-classic-card-featured">
                                  FEATURED
                                </span>
                              )}

                              <button
                                type="button"
                                className={`nb-classic-card-wishlist ${isLiked ? 'active' : ''}`}
                                onClick={(e) => handleWishlistToggle(e, p.id)}
                                aria-label="Add to wishlist"
                              >
                                <Heart size={14} fill={isLiked ? '#ef4444' : 'none'} />
                              </button>
                            </div>

                            <div className="nb-classic-card-body">
                              <div>
                                <h3 className="nb-classic-card-title text-truncate" title={p.title}>
                                  <Link href={`/property-detail/${p.slug}`} className="text-decoration-none text-dark fw-bold">
                                    {p.bedrooms ? `${p.bedrooms} BHK ` : ''}{getPropertyTypeLabel(p.property_type)}
                                  </Link>
                                </h3>
                                <p className="nb-classic-card-loc text-truncate">
                                  <MapPin size={12} className="me-1 d-inline" /> In {p.locality}
                                </p>
                              </div>

                              <div>
                                <div className="nb-classic-card-stats">
                                  {p.bedrooms !== null && (
                                    <span className="nb-classic-card-stat-item">
                                      <Bed size={13} /> {p.bedrooms} BHK
                                    </span>
                                  )}
                                  {p.bathrooms !== null && (
                                    <span className="nb-classic-card-stat-item">
                                      <Bath size={13} /> {p.bathrooms} Bath
                                    </span>
                                  )}
                                  {p.area_sqft !== null && (
                                    <span className="nb-classic-card-stat-item">
                                      <Grid size={13} /> {p.area_sqft} sqft
                                    </span>
                                  )}
                                </div>

                                <div className="nb-classic-card-footer">
                                  <span className="nb-classic-card-author">
                                    Posted by <strong>Owner</strong>
                                  </span>
                                  <span>Recently</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                    {featured.length === 0 && (
                      <div className="w-100 text-center py-4 bg-white border text-muted">
                        No recommended properties available.
                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>

            {/* Newly Launched Projects Section */}
            <div className="mb-5 fade-in-up">
              <div className="p-4 rounded-4" style={{ backgroundColor: '#f0f7fb' }}>
                <div className="d-flex align-items-center mb-4">
                  <div className="me-3">
                    <img src="https://img.icons8.com/color/48/city-buildings.png" alt="Buildings" width="36" height="36" />
                  </div>
                  <div>
                    <h2 className="h4 fw-bold text-dark m-0 d-flex align-items-center gap-2">
                      Newly launched projects
                    </h2>
                    <p className="text-muted small m-0">Less upfront payment</p>
                  </div>
                </div>

                <div className="nb-scroll-wrapper">
                  <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
                  <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
                  <div className="nb-horizontal-scroll pb-2" style={{ paddingLeft: '5px' }}>
                    {featured.slice(0, 5).map((proj, i) => {
                      const imgUrl = proj.thumbnail_url || (proj.image_urls && proj.image_urls.length > 0 ? proj.image_urls[0] : 'https://placehold.co/400x300?text=No+Image');
                      return (
                        <Link key={`new-launch-${proj.id}`} href={`/property-detail/${proj.slug}`} className="text-decoration-none d-block flex-shrink-0" style={{ marginRight: '1rem' }}>
                          <div className="card border-0 shadow-sm rounded-4 bg-white position-relative" style={{ width: '420px' }}>

                            {/* Top Tag */}
                            <div className="position-absolute" style={{ top: '15px', left: '-5px', zIndex: 2 }}>
                              <div className="text-dark fw-bold px-3 py-1 text-uppercase" style={{ backgroundColor: '#ffe6a7', fontSize: '0.7rem', clipPath: 'polygon(0 0, 100% 0, 90% 50%, 100% 100%, 0 100%)', boxShadow: '2px 2px 5px rgba(0,0,0,0.1)', letterSpacing: '0.5px' }}>
                                NEW {i % 2 === 0 ? 'ARRIVAL' : 'LAUNCH'}
                              </div>
                              {/* Fold effect corner */}
                              <div style={{ width: '5px', height: '6px', backgroundColor: '#d1a000', position: 'absolute', bottom: '-6px', left: '0', clipPath: 'polygon(100% 0, 100% 100%, 0 0)' }}></div>
                            </div>

                            <div className="p-3 pt-5 pb-3 d-flex gap-3 align-items-start position-relative z-1">
                              {/* Circular Image & RERA badge */}
                              <div className="position-relative flex-shrink-0 mt-2 ms-2">
                                <div className="rounded-circle overflow-hidden shadow-sm" style={{ width: '70px', height: '70px', border: '1px solid #eaeaea' }}>
                                  <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                                </div>
                                <div className="position-absolute text-white fw-bold px-2 py-0.5 rounded text-uppercase text-center" style={{ backgroundColor: '#003366', bottom: '-8px', left: '50%', transform: 'translateX(-50%)', fontSize: '0.55rem', border: '2px solid white', whiteSpace: 'nowrap' }}>
                                  <span className="text-info d-inline-block me-1" style={{ fontSize: '0.65rem' }}>✓</span> RERA
                                </div>
                              </div>

                              {/* Project Info */}
                              <div className="flex-grow-1 overflow-hidden pt-1">
                                <h3 className="h6 fw-bold text-dark mb-1 text-truncate w-100">{proj.title}</h3>
                                <p className="text-muted small mb-2 text-truncate w-100">{proj.locality || proj.city_name}</p>
                                <div className="d-flex align-items-center gap-1 mb-2 text-truncate w-100">
                                  <span className="fw-bold text-dark" style={{ fontSize: '0.9rem' }}>{formatPrice(proj.price)} {proj.price > 100000 ? '- ' + formatPrice(proj.price * 1.5) : ''}</span>
                                  <span className="text-muted" style={{ fontSize: '0.8rem' }}>|</span>
                                  <span className="text-muted text-truncate" style={{ fontSize: '0.8rem' }}>{proj.bedrooms ? `${proj.bedrooms}, ` : ''}{proj.bedrooms ? proj.bedrooms + 1 : 3} BHK {getPropertyTypeLabel(proj.property_type)}</span>
                                </div>
                                <p className="m-0 fw-semibold text-truncate w-100" style={{ fontSize: '0.75rem', color: '#059669' }}>
                                  {8.3 + i * 0.4}% price increase in last 3 months in {proj.locality || proj.city_name}
                                </p>
                              </div>
                            </div>

                            {/* Dotted Divider */}
                            <div className="position-relative w-100">
                              <div style={{ borderTop: '1px dashed #d1d5db', margin: '0 15px' }}></div>
                              <div className="position-absolute" style={{ width: '16px', height: '16px', borderRadius: '50%', backgroundColor: '#f0f7fb', left: '-8px', top: '-8px' }}></div>
                              <div className="position-absolute" style={{ width: '16px', height: '16px', borderRadius: '50%', backgroundColor: '#f0f7fb', right: '-8px', top: '-8px' }}></div>
                            </div>

                            {/* Footer */}
                            <div className="p-3 bg-white rounded-bottom-4 d-flex justify-content-between align-items-center">
                              <div className="d-flex align-items-center gap-2">
                                <span className="nb-text-brand" style={{ transform: 'rotate(-45deg)' }}>
                                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.41l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.41zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z" /></svg>
                                </span>
                                <div className="text-secondary" style={{ fontSize: '0.75rem', lineHeight: '1.3' }}>
                                  Get preferred options<br />
                                  <strong className="text-dark">@zero brokerage</strong>
                                </div>
                              </div>
                              <button className="btn btn-primary btn-sm fw-bold px-3 py-1.5" style={{ backgroundColor: '#1877f2', border: 'none', borderRadius: '6px' }} onClick={(e) => e.preventDefault()}>
                                View Number
                              </button>
                            </div>

                          </div>
                        </Link>
                      );
                    })}
                    {featured.length === 0 && !loadingFeatured && (
                      <div className="text-muted small py-3">No newly launched projects found.</div>
                    )}
                    {loadingFeatured && (
                      <div className="text-muted small py-3">Loading projects...</div>
                    )}
                  </div>
                </div>
              </div>
            </div>

            {/* Recommended Projects section */}
            <div className="mb-5 fade-in-up">
              <div className="d-flex justify-content-between align-items-end mb-3">
                <div>
                  <h2 className="h4 fw-bold text-dark m-0">Recommended Projects</h2>
                  <p className="text-muted small m-0">The most searched premium projects in Coimbatore</p>
                </div>
                <button onClick={() => router.push('/search?sort=featured')} className="btn btn-link text-decoration-none nb-text-brand small p-0 d-flex align-items-center gap-1 fw-bold">
                  <span>See All</span>
                  <ChevronRight size={16} />
                </button>
              </div>

              <div className="nb-scroll-wrapper">
                <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
                <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
                <div className="nb-horizontal-scroll">
                  {featured.slice(0, 5).map((proj, i) => {
                    const imgUrl = proj.thumbnail_url || (proj.image_urls && proj.image_urls.length > 0 ? proj.image_urls[0] : 'https://placehold.co/400x300?text=No+Image');
                    return (
                      <Link key={proj.id} href={`/property-detail/${proj.slug}`} className="text-decoration-none d-block flex-shrink-0">
                        <div className="card border-0 shadow-sm rounded-4 overflow-hidden flex-shrink-0 nb-insight-card-hover" style={{ width: '260px' }}>
                          <div className="position-relative" style={{ height: '140px' }}>
                            <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                            <span className="position-absolute top-0 start-0 m-2 badge bg-success text-white text-uppercase" style={{ fontSize: '0.65rem' }}>
                              ✓ RERA
                            </span>
                            <div className="position-absolute bottom-0 start-0 m-2 px-2 py-0.5 rounded text-white bg-dark bg-opacity-75 small" style={{ fontSize: '0.75rem' }}>
                              Ready To Move
                            </div>
                          </div>
                          <div className="p-3 bg-white d-flex flex-column justify-content-between" style={{ height: '120px' }}>
                            <div>
                              <h3 className="h6 fw-bold text-dark m-0 text-truncate">{proj.title}</h3>
                              <p className="text-secondary m-0 text-truncate" style={{ fontSize: '0.75rem' }}>
                                {proj.bedrooms ? `${proj.bedrooms} BHK ` : ''}{getPropertyTypeLabel(proj.property_type)}
                              </p>
                              <p className="text-muted m-0 text-truncate" style={{ fontSize: '0.7rem' }}>
                                <MapPin size={10} className="d-inline me-1" />{proj.locality || proj.city_name}
                              </p>
                            </div>
                            <div className="fw-bold nb-text-brand pt-2 border-top" style={{ fontSize: '0.85rem' }}>
                              {formatPrice(proj.price)}
                            </div>
                          </div>
                        </div>
                      </Link>
                    );
                  })}
                  {featured.length === 0 && !loadingFeatured && (
                    <div className="text-muted small py-3">No premium properties found.</div>
                  )}
                  {loadingFeatured && (
                    <div className="text-muted small py-3">Loading properties...</div>
                  )}
                </div>
              </div>
            </div>

            {/* Handpicked Projects Section */}
            <div className="mb-5 fade-in-up">
              <h2 className="h4 fw-bold text-dark mb-3">Handpicked Premium Projects</h2>
              <div className="nb-scroll-wrapper">
                <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
                <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
                <div className="nb-horizontal-scroll">
                  {featured.slice(0, 5).map((proj, i) => {
                    const imgUrl = proj.thumbnail_url || (proj.image_urls && proj.image_urls.length > 0 ? proj.image_urls[0] : 'https://placehold.co/400x300?text=No+Image');
                    return (
                      <Link key={proj.id} href={`/property-detail/${proj.slug}`} className="text-decoration-none d-block flex-shrink-0">
                        <div className="nb-handpicked-card nb-insight-card-hover" style={{ borderRadius: '16px' }}>
                          <div className="nb-handpicked-img-wrap">
                            <img src={imgUrl} alt={proj.title} className="w-100 h-100 object-fit-cover" />
                            {proj.is_featured === 1 && (
                              <span className="nb-handpicked-badge">Featured</span>
                            )}
                            <div className="nb-handpicked-logo">
                              <img src="https://img.icons8.com/color/96/real-estate.png" alt="Property Icon" />
                            </div>
                          </div>
                          <div className="nb-handpicked-body bg-white text-dark">
                            <h3 className="h6 fw-bold mb-1 text-truncate">{proj.title}</h3>
                            <p className="text-muted small mb-2 text-truncate">{proj.property_type.replace('_', ' ')}, {proj.locality || proj.city_name}</p>
                            <div className="fw-bold nb-text-brand mb-0" style={{ fontSize: '0.9rem' }}>
                              ₹{proj.price.toLocaleString('en-IN')}
                            </div>
                          </div>
                        </div>
                      </Link>
                    );
                  })}
                  {featured.length === 0 && !loadingFeatured && (
                    <div className="text-muted small py-3">No premium properties found.</div>
                  )}
                  {loadingFeatured && (
                    <div className="text-muted small py-3">Loading properties...</div>
                  )}
                </div>
              </div>
            </div>



            {/* Magic Loans Auto Scroll Banner */}
            <div className="mb-5 fade-in-up">
              <div className="nb-magic-loans-banner">
                <div className="nb-magic-loans-content">
                  <div className="nb-magic-loans-logo">
                    Coimbatore Properties<span> Loans</span>
                  </div>
                  <h2 className="nb-magic-loans-title">Compare Home Loan Offers from 40+ Banks</h2>
                  <div className="nb-magic-loans-features">
                    <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Rates starting from <span className="highlight">7.1%</span></span>
                    <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> <span className="highlight">0%*</span> Processing Fee</span>
                  </div>

                  <div className="nb-magic-loans-partners-label">Our Banking Partners</div>
                  <div className="nb-magic-loans-banks-wrapper">
                    <div className="nb-magic-loans-banks-track">
                      {[1, 2].map((loop) => (
                        <div key={loop} style={{ display: 'flex', gap: '1rem' }}>
                          <div className="nb-magic-loans-bank-card">
                            <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>HDFC Bank</h4>
                            <p>Starts at 7.25%</p>
                          </div>
                          <div className="nb-magic-loans-bank-card">
                            <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>Bajaj Finserv</h4>
                            <p>Starts at 7.15%</p>
                          </div>
                          <div className="nb-magic-loans-bank-card">
                            <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>LIC HFL</h4>
                            <p>Starts at 7.8%</p>
                          </div>
                          <div className="nb-magic-loans-bank-card">
                            <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>SBI</h4>
                            <p>Starts at 7.25%</p>
                          </div>
                          <div className="nb-magic-loans-bank-card">
                            <h4 className="fw-bold text-dark mb-1" style={{ fontSize: '0.95rem' }}>Canara Bank</h4>
                            <p>Starts at 7.15%</p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="nb-magic-loans-actions">
                    <button className="nb-magic-loans-btn-outline">
                      Explore Bank Offers <ArrowRight size={16} className="ms-1" />
                    </button>
                    <button className="nb-magic-loans-btn-filled">
                      Check Your Eligibility
                    </button>
                  </div>
                </div>

                <div className="nb-magic-loans-image-wrapper">
                  <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" style={{ mixBlendMode: 'multiply', opacity: 0.9, borderRadius: '50%' }} alt="Home Loan Hand" />
                </div>
              </div>
            </div>
            {/* Recommended Insights & Real Estate Trends */}
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
                      <div className="card border-0 shadow-sm p-3 rounded-4 h-100 nb-insight-card-hover d-flex flex-column" style={{ width: '200px', background: ['#fff9db', '#eef2ff', '#ebfbee', '#e3fafc', '#fff0f6'][i % 5] }}>
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

            {/* Featured Properties Grid View */}
            <div className="mb-5 fade-in-up">
              <div className="d-flex justify-content-between align-items-end mb-4">
                <div>
                  <h2 className="h4 fw-bold text-dark m-0">Featured Properties</h2>
                  <p className="text-muted small m-0">Top-rated premium listings in a list view</p>
                </div>
                <Link href="/search?sort=featured" className="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                  View all Featured
                </Link>
              </div>

              {loadingFeatured ? (
                <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
                  <div className="spinner-border nb-text-brand" role="status">
                    <span className="visually-hidden">Loading properties...</span>
                  </div>
                </div>
              ) : (
                <div className="nb-scroll-wrapper">
                  <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
                  <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
                  <div className="nb-horizontal-scroll">
                    {featured.map((p) => {
                      const imagesList = Array.isArray(p.image_urls) ? p.image_urls : [];
                      const thumbnail = p.thumbnail_url || imagesList[0] || '';
                      const isLiked = wishlistedIds.includes(p.id);
                      return (
                        <div key={`featured-${p.id}`} className="nb-classic-property-card-wrap" style={{ width: '320px' }}>
                          <div className="nb-classic-card h-100 shadow-sm">
                            <div className="nb-classic-card-img-container" style={{ height: '200px' }}>
                              <Link href={`/property-detail/${p.slug}`}>
                                {thumbnail ? (
                                  <img
                                    src={thumbnail}
                                    alt={p.title}
                                    className="nb-classic-card-img"
                                  />
                                ) : (
                                  <div className="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted small">
                                    <span>No Photo</span>
                                  </div>
                                )}
                              </Link>

                              <div className="nb-classic-card-price-overlay px-3 py-2" style={{ fontSize: '1.25rem' }}>
                                {formatPrice(p.price)}
                              </div>

                              <span className={`nb-classic-card-badge nb-classic-card-badge--${p.listing_type}`}>
                                For {p.listing_type === 'rent' ? 'Rent' : 'Sale'}
                              </span>

                              {p.is_featured === 1 && (
                                <span className="nb-classic-card-featured">
                                  FEATURED
                                </span>
                              )}

                              <button
                                type="button"
                                className={`nb-classic-card-wishlist ${isLiked ? 'active' : ''}`}
                                onClick={(e) => handleWishlistToggle(e, p.id)}
                                aria-label="Add to wishlist"
                              >
                                <Heart size={16} fill={isLiked ? '#ef4444' : 'none'} />
                              </button>
                            </div>

                            <div className="nb-classic-card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
                              <div className="mb-3">
                                <h3 className="nb-classic-card-title text-truncate" title={p.title} style={{ fontSize: '1.1rem' }}>
                                  <Link href={`/property-detail/${p.slug}`} className="text-decoration-none text-dark fw-bold">
                                    {p.bedrooms ? `${p.bedrooms} BHK ` : ''}{getPropertyTypeLabel(p.property_type)}
                                  </Link>
                                </h3>
                                <p className="nb-classic-card-loc text-truncate m-0 mt-1">
                                  <MapPin size={14} className="me-1 d-inline text-muted" /> In {p.locality}
                                </p>
                              </div>

                              <div>
                                <div className="nb-classic-card-stats mb-3 pb-3 border-bottom d-flex gap-3">
                                  {p.bedrooms !== null && (
                                    <span className="nb-classic-card-stat-item fs-6">
                                      <Bed size={16} className="me-1 nb-text-brand" /> {p.bedrooms} BHK
                                    </span>
                                  )}
                                  {p.bathrooms !== null && (
                                    <span className="nb-classic-card-stat-item fs-6">
                                      <Bath size={16} className="me-1 nb-text-brand" /> {p.bathrooms} Bath
                                    </span>
                                  )}
                                  {p.area_sqft !== null && (
                                    <span className="nb-classic-card-stat-item fs-6">
                                      <Grid size={16} className="me-1 nb-text-brand" /> {p.area_sqft} sqft
                                    </span>
                                  )}
                                </div>

                                <div className="nb-classic-card-footer m-0 pt-0 border-0 d-flex justify-content-between align-items-center">
                                  <span className="nb-classic-card-author">
                                    Posted by <strong>Owner</strong>
                                  </span>
                                  <Link href={`/property-detail/${p.slug}`} className="btn btn-primary btn-sm fw-semibold rounded-pill px-3">
                                    Details
                                  </Link>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                    {featured.length === 0 && (
                      <div className="w-100 text-center py-4 bg-white border text-muted">
                        No featured properties available.
                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>
            {/* Why Choose Section */}
            <div className="mb-5 fade-in-up pt-5 bg-white text-center rounded-4 shadow-sm border border-light" style={{ paddingBottom: '3rem' }}>
              <p className="text-muted text-uppercase fw-bold mb-2" style={{ fontSize: '0.75rem', letterSpacing: '1px' }}>BENEFITS OF COIMBATORE PROPERTIES</p>
              <h2 className="h2 fw-bold text-dark mb-5" style={{ color: '#0b2c56' }}>Why choose Coimbatore Properties</h2>

              <div className="row g-4 mx-auto text-start px-4 px-md-5" style={{ maxWidth: '1000px' }}>
                <div className="col-md-4">
                  <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#f0f7fb' }}>
                    <img src="https://img.icons8.com/color/48/skyscrapers.png" alt="Properties" width="28" height="28" />
                  </div>
                  <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">01.</span> Over 10,000+ properties</h3>
                  <p className="text-muted small" style={{ lineHeight: '1.6' }}>100+ new properties are added every day from verified sellers and builders.</p>
                </div>

                <div className="col-md-4">
                  <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#fdf8ec' }}>
                    <img src="https://img.icons8.com/color/48/approval--v1.png" alt="Verification" width="28" height="28" />
                  </div>
                  <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">02.</span> Verification by our team</h3>
                  <p className="text-muted small" style={{ lineHeight: '1.6' }}>Photos / Videos and other details are verified on location by our experts.</p>
                </div>

                <div className="col-md-4">
                  <div className="mb-3 d-inline-block rounded-circle" style={{ padding: '12px', backgroundColor: '#f3f4f6' }}>
                    <img src="https://img.icons8.com/color/48/conference-call.png" alt="Users" width="28" height="28" />
                  </div>
                  <h3 className="h6 fw-bold mb-2 text-dark"><span className="text-primary me-1">03.</span> Large user base</h3>
                  <p className="text-muted small" style={{ lineHeight: '1.6' }}>High active user count and user engagement to find and close deals fast.</p>
                </div>
              </div>
            </div>


            {/* Promo Banner and Blogs Section */}
            <div className="nb-promo-section mb-5 fade-in-up">
              <div className="nb-promo-banner">
                <img
                  src="/promo_agent.png"
                  alt="Post Property Free Agent"
                  className="nb-promo-banner-img"
                />
                <div className="nb-promo-banner-content">
                  <h3 className="nb-promo-banner-title">
                    Sell or rent faster at the right price!
                  </h3>
                  <p className="nb-promo-banner-subtitle">
                    List your property now
                  </p>
                  <div className="nb-promo-banner-actions">
                    <Link
                      href={user ? '/owner/property/add' : '#'}
                      onClick={(e) => {
                        if (!user) {
                          e.preventDefault();
                          setAuthModalOpen('login');
                        }
                      }}
                      className="nb-promo-banner-btn"
                    >
                      Post Property, It's FREE
                    </Link>
                    <a
                      href="https://wa.me/919999999999?text=I%20want%20to%20list%20my%20property%20for%20free"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="nb-promo-banner-whatsapp"
                    >
                      <svg width="20" height="20" fill="currentColor" className="bi bi-whatsapp me-2" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 8 0a7.85 7.85 0 0 0-7.852 7.852c0 1.51.417 2.99 1.208 4.3l-.861 3.15 3.255-.853a7.85 7.85 0 0 0 3.758.974h.001c4.341 0 7.863-3.522 7.863-7.863a7.85 7.85 0 0 0-2.266-5.556m-5.602 11.233c-1.393 0-2.756-.372-3.948-1.077l-.283-.168-1.945.51.519-1.898-.184-.294A6.55 6.55 0 0 1 1.776 7.852c0-3.619 2.946-6.565 6.566-6.565 1.753 0 3.4.682 4.64 1.922 1.24 1.24 1.922 2.9 1.92 4.64 0 3.62-2.947 6.565-6.565 6.565m3.56-4.93c-.197-.1-.197-.1-.363-.18-.167-.08-.348-.167-.533-.255-.185-.088-.308-.068-.4.043-.092.11-.355.445-.436.538-.08.093-.16.103-.357.004a4.5 4.5 0 0 1-1.32-.814 4.86 4.86 0 0 1-1.026-1.28c-.114-.196-.012-.302.086-.4.088-.088.197-.23.296-.346.099-.115.132-.196.198-.328.066-.131.033-.246-.016-.346-.05-.1-.4-.96-.55-1.3-.146-.35-.294-.3-.404-.3-.105-.005-.226-.005-.347-.005-.12 0-.317.045-.483.225-.166.18-.635.62-.635 1.517s.652 1.76 1.054 2.222c.402.463 2.508 3.82 6.07 5.36.85.367 1.513.587 2.03.75.86.273 1.64.234 2.26.14.69-.104 1.513-.619 1.723-1.217.21-.6.21-1.115.147-1.218-.063-.103-.228-.163-.424-.263" />
                      </svg>
                      Post via Whatsapp <ArrowRight size={16} className="ms-1 d-inline" />
                    </a>
                  </div>
                </div>
              </div>

              {/* Blogs Card Overlay */}
              <div className="nb-blogs-card">
                <div className="nb-blogs-card-left">
                  <div>
                    <h4 className="nb-blogs-card-left-title">
                      Articles and guides for property owners
                    </h4>
                    <p className="nb-blogs-card-left-desc">
                      Read from Beginners check-list to Pro Tips
                    </p>
                  </div>
                  <Link href="/blog" className="nb-blogs-card-left-link">
                    Read realty news, guides & articles <ArrowRight size={14} className="ms-1" />
                  </Link>
                </div>

                <div className="nb-blogs-card-right">
                  {loadingBlogs ? (
                    Array.from({ length: 4 }).map((_, i) => (
                      <div key={i} className="d-flex gap-3 align-items-center placeholder-glow">
                        <div className="placeholder rounded" style={{ width: '80px', height: '64px', backgroundColor: '#f3f4f6' }}></div>
                        <div className="w-100">
                          <div className="placeholder col-8 mb-2"></div>
                          <div className="placeholder col-4"></div>
                        </div>
                      </div>
                    ))
                  ) : blogs.length > 0 ? (
                    blogs.slice(0, 4).map((blog) => {
                      const image = blog.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=150&q=80';
                      const formattedDate = blog.date
                        ? new Date(blog.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
                        : '';
                      return (
                        <Link key={blog.id} href={`/blog/${blog.id}`} className="nb-blog-item">
                          <img
                            src={image}
                            alt={blog.name}
                            className="nb-blog-item-img"
                          />
                          <div className="nb-blog-item-content">
                            <h5 className="nb-blog-item-title" title={blog.name}>
                              {blog.name}
                            </h5>
                            <span className="nb-blog-item-date">{formattedDate}</span>
                          </div>
                        </Link>
                      );
                    })
                  ) : (
                    [
                      {
                        id: 1,
                        name: "Noida's Floor-wise registration policy...",
                        date: "Jun 09, 2026",
                        img: "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=150&q=80"
                      },
                      {
                        id: 2,
                        name: "Cost of constructing a house in India...",
                        date: "Jun 04, 2026",
                        img: "https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=150&q=80"
                      },
                      {
                        id: 3,
                        name: "All about maintenance charges on properties",
                        date: "Jun 03, 2026",
                        img: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=150&q=80"
                      },
                      {
                        id: 4,
                        name: "All about possession certificate in detail",
                        date: "May 22, 2026",
                        img: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=150&q=80"
                      }
                    ].map((dummy) => (
                      <Link key={dummy.id} href="/blog" className="nb-blog-item">
                        <img
                          src={dummy.img}
                          alt={dummy.name}
                          className="nb-blog-item-img"
                        />
                        <div className="nb-blog-item-content">
                          <h5 className="nb-blog-item-title" title={dummy.name}>
                            {dummy.name}
                          </h5>
                          <span className="nb-blog-item-date">{dummy.date}</span>
                        </div>
                      </Link>
                    ))
                  )}
                </div>
              </div>
            </div>

          </div>

          {/* Right Sidebar Widgets Column */}
          <div className="col-lg-3">
            <div className="d-flex flex-column gap-4 sticky-top" style={{ top: '5.5rem', zIndex: 5 }}>

              {/* 4. Personalized Sidebar Activity Console */}
              <div className="nb-sidebar-profile-console fade-in-up">
                <div className="d-flex align-items-center gap-3 mb-3">
                  <div className="nb-profile-avatar-circle">
                    {user ? user.name.charAt(0).toUpperCase() : <User size={20} />}
                  </div>
                  <div>
                    <h3 className="h6 fw-bold text-dark m-0">{user ? user.name : 'Welcome, Guest'}</h3>
                    <span className="text-muted small d-block" style={{ fontSize: '0.75rem' }}>
                      {user ? `${user.role.toUpperCase()} Account` : 'Premium Features Locked'}
                    </span>
                  </div>
                </div>

                <div className="nb-profile-stat-box">
                  <div className="nb-profile-stat-item">
                    <div className="nb-profile-stat-num">
                      {user ? wishlistedIds.length : '—'}
                    </div>
                    <div className="nb-profile-stat-txt">Wishlist</div>
                  </div>
                  <div className="nb-profile-stat-item">
                    <div className="nb-profile-stat-num">
                      {user ? 'Active' : 'Guest'}
                    </div>
                    <div className="nb-profile-stat-txt">Status</div>
                  </div>
                </div>

                {user ? (
                  <Link
                    href={getDashboardPath()}
                    className="btn w-100 py-2 rounded-3 small fw-bold text-white"
                    style={{ backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)' }}
                  >
                    Go to Dashboard
                  </Link>
                ) : (
                  <button
                    type="button"
                    className="btn w-100 py-2 rounded-3 small fw-bold text-white"
                    style={{ backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)' }}
                    onClick={() => setAuthModalOpen('login')}
                  >
                    Login to Save Activities
                  </button>
                )}

                {!user && (
                  <p className="text-center text-muted small mt-2 mb-0" style={{ fontSize: '0.65rem' }}>
                    Access dashboard, manage wishlists, and search directly.
                  </p>
                )}
              </div>

              {/* Sell or Rent Promo Card */}
              <div className="nb-sidebar-promo-card fade-in-up">
                <h3 className="h6 fw-bold text-success mb-2" style={{ color: '#1b5e20' }}>
                  Sell or rent faster at the right price!
                </h3>
                <p className="text-secondary mb-3" style={{ fontSize: '0.75rem', lineHeight: '1.4' }}>
                  List your property now for free and reach thousands of genuine tenants/buyers in Coimbatore.
                </p>
                <div className="d-flex justify-content-between align-items-center">
                  <button
                    type="button"
                    className="nb-promo-btn"
                    onClick={() => {
                      if (user) {
                        router.push('/owner/property/add');
                      } else {
                        setAuthModalOpen('login');
                      }
                    }}
                  >
                    Post Property, It's FREE
                  </button>
                  {/* <img
                    src="https://img.icons8.com/color/96/property-agent.png"
                    alt="Seller banner illustration"
                    style={{ width: '48px', height: '48px', objectFit: 'contain' }}
                  /> */}
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
