'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';
import { useAuth } from '@/components/AuthContext';
import ResearchTools from '@/components/common/ResearchTools';
// import LiveUpdateModal from '@/components/common/LiveUpdateModal';

// Homepage subcomponents (extracted for clean architecture & modularity)
import HeroSlider from '../components/home/HeroSlider';
import SearchPanel from '../components/home/SearchPanel';
import RecommendedProperties from '../components/home/RecommendedProperties';
import NewlyLaunchedProjects from '../components/home/NewlyLaunchedProjects';
import RecommendedProjects from '../components/home/RecommendedProjects';
import PropertyCategories from '../components/home/PropertyCategories';
import HandpickedProjects from '../components/home/HandpickedProjects';
import MagicLoans from '../components/home/MagicLoans';
import VerifiedBanner from '../components/home/VerifiedBanner';
import ExploreCities from '../components/home/ExploreCities';
import FeaturedProperties from '../components/home/FeaturedProperties';
import WhyChooseUs from '../components/home/WhyChooseUs';
import PromoSection from '../components/home/PromoSection';
import BlogsSection from '../components/home/BlogsSection';
import SidebarConsole from '../components/home/SidebarConsole';

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
  category?: string;
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

  // Dynamic City Name
  const activeCity = cities.find((c) => c.id.toString() === cityId);
  const cityName = activeCity ? activeCity.name : 'Coimbatore';
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loadingBlogs, setLoadingBlogs] = useState(true);
  const [activeBlogCategory, setActiveBlogCategory] = useState<'news' | 'tax' | 'guide' | 'investment'>('news');

  // Hero slideshow states
  const [heroSlides, setHeroSlides] = useState<Property[]>([]);
  const [loadingHero, setLoadingHero] = useState(true);
  const [currentSlide, setCurrentSlide] = useState(0);

  // Voice Search states
  const [voiceStatus, setVoiceStatus] = useState<'idle' | 'listening' | 'success' | 'error'>('idle');

  // Wishlist states
  const [wishlistedIds, setWishlistedIds] = useState<number[]>([]);

  // Live Update Modal
  // const [showLiveUpdateModal, setShowLiveUpdateModal] = useState(false);

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
      <HeroSlider
        activeSlides={activeSlides}
        currentSlide={currentSlide}
        formatPrice={formatPrice}
        getPropertyTypeLabel={getPropertyTypeLabel}
      />

      {/* Main content grid */}
      <div className="container">

        {/* 2. Glassmorphic Overlapping Search Panel */}
        <SearchPanel
          listingType={listingType}
          setListingType={setListingType}
          propertyType={propertyType}
          setPropertyType={setPropertyType}
          cityId={cityId}
          setCityId={setCityId}
          cities={cities}
          searchQuery={searchQuery}
          setSearchQuery={setSearchQuery}
          minPrice={minPrice}
          setMinPrice={setMinPrice}
          maxPrice={maxPrice}
          setMaxPrice={setMaxPrice}
          bedrooms={bedrooms}
          setBedrooms={setBedrooms}
          sortBy={sortBy}
          setSortBy={setSortBy}
          showAdvanced={showAdvanced}
          setShowAdvanced={setShowAdvanced}
          voiceStatus={voiceStatus}
          handleVoiceSearch={handleVoiceSearch}
          handleLocationSearch={handleLocationSearch}
          handleSearchSubmit={handleSearchSubmit}
          applySearchFilter={applySearchFilter}
          user={user}
          setAuthModalOpen={setAuthModalOpen}
        />

        {/* 3. Two Columns Grid Layout (Left Content, Right Sidebar widgets) */}
        <div className="row g-4 mt-3">

          {/* Left Main Content Column */}
          <div className="col-lg-9">

            {/* Recommended Properties Horizontal Slider */}
            <RecommendedProperties
              featured={featured}
              loadingFeatured={loadingFeatured}
              wishlistedIds={wishlistedIds}
              cityName={cityName}
              handleWishlistToggle={handleWishlistToggle}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            {/* Newly Launched Projects Section */}
            <NewlyLaunchedProjects
              featured={featured}
              loadingFeatured={loadingFeatured}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            {/* Recommended Projects section */}
            <RecommendedProjects
              featured={featured}
              loadingFeatured={loadingFeatured}
              cityName={cityName}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            {/* Property Categories (Apartments, Villas, etc.) */}
            <PropertyCategories
              cityId={cityId}
              cityName={cityName}
            />

            {/* Handpicked Projects Section */}
            <HandpickedProjects
              featured={featured}
              loadingFeatured={loadingFeatured}
            />

            {/* Magic Loans Auto Scroll Banner */}
            <MagicLoans />

            {/* Verified Listings Banner */}
            <VerifiedBanner />

            {/* Classic Property Research Tools Carousel & Calculators */}
            <ResearchTools />

            {/* Explore Cities */}
            <ExploreCities
              cities={cities}
            />

            {/* Featured Properties Grid View */}
            <FeaturedProperties
              featured={featured}
              loadingFeatured={loadingFeatured}
              wishlistedIds={wishlistedIds}
              handleWishlistToggle={handleWishlistToggle}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            {/* Why Choose Us Section */}
            <WhyChooseUs />

            {/* Promo Section (Sell/Rent Faster Banner) */}
            <PromoSection
              user={user}
              setAuthModalOpen={setAuthModalOpen}
            />

            {/* Revamped Blogs & Articles Section */}
            <BlogsSection
              blogs={blogs}
              loadingBlogs={loadingBlogs}
              activeBlogCategory={activeBlogCategory}
              setActiveBlogCategory={setActiveBlogCategory}
            />

          </div>

          {/* Right Sidebar Widgets Column */}
          <div className="col-lg-3">
            <SidebarConsole
              user={user}
              wishlistedIds={wishlistedIds}
              setAuthModalOpen={setAuthModalOpen}
              // setShowLiveUpdateModal={setShowLiveUpdateModal}
              getDashboardPath={getDashboardPath}
              cityName={cityName}
            />
          </div>

        </div>
      </div>

      {/* Live Update Modal */}
      {/* <LiveUpdateModal show={showLiveUpdateModal} onClose={() => setShowLiveUpdateModal(false)} /> */}
    </div>
  );
}
