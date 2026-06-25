'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import {
  getCities,
  getBlogs,
  searchProperties,
  getHomeBanners,
  getWishlist,
  toggleWishlist,
} from '@/lib/frontendApi';
import { usePropertyTypeFilters } from '@/hooks/usePropertyTypeFilters';
import { useAuth } from '@/hooks/useAuth';
import ResearchTools from '@/components/common/ResearchTools';
// import LiveUpdateModal from '@/components/common/LiveUpdateModal';

// Homepage subcomponents (extracted for clean architecture & modularity)
import HeroSlider, { PropertyBannerSlide } from '../components/home/HeroSlider';
import SearchPanel from '../components/home/SearchPanel';
import RecommendedProperties from '../components/home/RecommendedProperties';
import NewlyLaunchedProjects from '../components/home/NewlyLaunchedProjects';
import VerifiedProperties from '../components/home/VerifiedProperties';
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
  is_home_banner?: number;
  home_banner_image_url?: string;
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
  const {
    mainTypes,
    mainTypeSlug,
    subTypeSlug,
    subTypes,
    propertyType,
    loading: typesLoading,
    setMainTypeSlug,
    setSubTypeSlug,
  } = usePropertyTypeFilters('');
  const [cityId, setCityId] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const [bedrooms, setBedrooms] = useState('');
  const [sortBy, setSortBy] = useState('new');
  const [showAdvanced, setShowAdvanced] = useState(false);

  // Homepage listing sections (each filtered by DB flags)
  const [recommended, setRecommended] = useState<Property[]>([]);
  const [loadingRecommended, setLoadingRecommended] = useState(true);
  const [newlyLaunched, setNewlyLaunched] = useState<Property[]>([]);
  const [loadingNewlyLaunched, setLoadingNewlyLaunched] = useState(true);
  const [verified, setVerified] = useState<Property[]>([]);
  const [loadingVerified, setLoadingVerified] = useState(true);
  const [featured, setFeatured] = useState<Property[]>([]);
  const [loadingFeatured, setLoadingFeatured] = useState(true);
  // Data states
  const [cities, setCities] = useState<City[]>([]);
  const activeCity = cities.find((c) => c.id.toString() === cityId);
  const cityName = activeCity ? activeCity.name : 'Coimbatore';
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loadingBlogs, setLoadingBlogs] = useState(true);
  const [activeBlogCategory, setActiveBlogCategory] = useState<'news' | 'tax' | 'guide' | 'investment'>('news');

  // Hero slideshow — properties with Home Banner enabled (property edit toggle)
  const [heroSlides, setHeroSlides] = useState<PropertyBannerSlide[]>([]);
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
    getCities()
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
    getBlogs()
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogs(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs', e))
      .finally(() => setLoadingBlogs(false));
  }, []);

  // Home banner hero — any property with is_home_banner + banner image
  useEffect(() => {
    setLoadingHero(true);
    setCurrentSlide(0);
    getHomeBanners({ limit: 10 })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setHeroSlides(
            res.data.items
              .map((item: Property & { price_formatted?: string }) => {
                const imageUrl =
                  item.home_banner_image_url ||
                  item.thumbnail_url ||
                  (Array.isArray(item.image_urls) ? item.image_urls[0] : '') ||
                  '';
                if (!imageUrl) return null;
                return {
                  id: item.id,
                  image_url: imageUrl,
                  title: item.title,
                  slug: item.slug,
                  property_type: item.property_type,
                  bedrooms: item.bedrooms ?? 0,
                  locality: item.locality,
                  price_label:
                    item.price_formatted ||
                    (item.listing_type === 'rent'
                      ? `₹${Number(item.price).toLocaleString('en-IN')} / month`
                      : `₹${Number(item.price).toLocaleString('en-IN')}`),
                };
              })
              .filter(Boolean) as PropertyBannerSlide[]
          );
        } else {
          setHeroSlides([]);
        }
      })
      .catch((e) => {
        console.warn('Could not fetch home banners', e);
        setHeroSlides([]);
      })
      .finally(() => setLoadingHero(false));
  }, []);

  // Homepage sections — active listings filtered by DB flags
  useEffect(() => {
    const cityParams = cityId ? { city_id: cityId } : {};
    const baseParams = { limit: 12, ...cityParams };

    setLoadingRecommended(true);
    setLoadingNewlyLaunched(true);
    setLoadingVerified(true);
    setLoadingFeatured(true);

    searchProperties({ ...baseParams, is_recommended: 1 })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setRecommended(res.data.items);
        } else {
          setRecommended([]);
        }
      })
      .catch((e) => console.warn('Could not fetch recommended listings', e))
      .finally(() => setLoadingRecommended(false));

    searchProperties({ ...baseParams, is_newly_launched: 1 })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setNewlyLaunched(res.data.items);
        } else {
          setNewlyLaunched([]);
        }
      })
      .catch((e) => console.warn('Could not fetch newly launched listings', e))
      .finally(() => setLoadingNewlyLaunched(false));

    searchProperties({ ...baseParams, is_verified_property: 1 })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setVerified(res.data.items);
        } else {
          setVerified([]);
        }
      })
      .catch((e) => console.warn('Could not fetch verified listings', e))
      .finally(() => setLoadingVerified(false));

    searchProperties({ ...baseParams, is_featured: 1 })
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          setFeatured(res.data.items);
        } else {
          setFeatured([]);
        }
      })
      .catch((e) => console.warn('Could not fetch featured listings', e))
      .finally(() => setLoadingFeatured(false));
  }, [cityId]);

  // Fetch wishlist IDs if logged in
  useEffect(() => {
    if (user) {
      getWishlist(user.id)
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
    if (heroSlides.length <= 1) return undefined;
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
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
      const response = await toggleWishlist({
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

  function handleLocationSearch() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          router.push(`/search?lat=${latitude}&lng=${longitude}&radius_km=5${cityId ? `&city_id=${cityId}` : ''}`);
        },
        (error) => {
          console.warn('Geolocation error:', error);
          alert('Could not retrieve current location. Searching all areas.');
          router.push(cityId ? `/search?city_id=${cityId}` : '/search');
        }
      );
    } else {
      alert('Geolocation is not supported by your browser.');
    }
  }

  return (
    <div className="home-container" style={{ background: '#f8fafc', paddingBottom: '3rem' }}>

      {/* 1. Home banner hero — real properties only (no dummy fallback) */}
      {loadingHero ? (
        <section className="nb-hero-slider-revamp" aria-hidden="true" />
      ) : heroSlides.length > 0 ? (
        <HeroSlider
          slides={heroSlides}
          currentSlide={currentSlide}
          getPropertyTypeLabel={getPropertyTypeLabel}
          onSlideSelect={setCurrentSlide}
        />
      ) : (
        <section className="nb-hero-slider-revamp" aria-hidden="true" />
      )}

      {/* Main content grid */}
      <div className="container">

        {/* 2. Glassmorphic Overlapping Search Panel */}
        <SearchPanel
          cityId={cityId}
          setCityId={setCityId}
          cities={cities}
          mainTypes={mainTypes}
          mainTypeSlug={mainTypeSlug}
          subTypeSlug={subTypeSlug}
          subTypes={subTypes}
          onMainTypeChange={setMainTypeSlug}
          onSubTypeChange={setSubTypeSlug}
          typesLoading={typesLoading}
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
          user={user}
          setAuthModalOpen={setAuthModalOpen}
        />

        {/* 3. Two Columns Grid Layout (Left Content, Right Sidebar widgets) */}
        <div className="row g-4 mt-3">

          {/* Left Main Content Column */}
          <div className="col-lg-9">

            {/* Recommended Properties Horizontal Slider */}
            <RecommendedProperties
              items={recommended}
              loading={loadingRecommended}
              wishlistedIds={wishlistedIds}
              cityName={cityName}
              handleWishlistToggle={handleWishlistToggle}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            <NewlyLaunchedProjects
              items={newlyLaunched}
              loading={loadingNewlyLaunched}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />

            <VerifiedProperties
              items={verified}
              loading={loadingVerified}
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
            <ExploreCities />

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
