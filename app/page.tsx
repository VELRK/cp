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
import { toFrontendAssetUrl } from '@/lib/cityImages';
import { buildSearchUrlParams } from '@/lib/searchFilters';
import { fetchRealDataFacets } from '@/lib/realDataFilters';
import confetti from 'canvas-confetti';
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
import RecommendedSellers from '../components/home/RecommendedSellers';
import PropertyVideos from '../components/home/PropertyVideos';
import BestRatedProperties from '../components/home/BestRatedProperties';
import HighGrowthProperties from '../components/home/HighGrowthProperties';


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
  is_recommended?: number;
  is_newly_launched?: number;
  is_verified_property?: number;
  is_home_banner?: number;
  tags_best_rate_localities?: number;
  tags_high_growth_localities?: number;
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
  const [bestRated, setBestRated] = useState<Property[]>([]);
  const [loadingBestRated, setLoadingBestRated] = useState(true);
  const [highGrowth, setHighGrowth] = useState<Property[]>([]);
  const [loadingHighGrowth, setLoadingHighGrowth] = useState(true);
  // Data states
  const [cities, setCities] = useState<City[]>([]);
  const activeCity = cities.find((c) => c.id.toString() === cityId);
  const cityName = activeCity ? activeCity.name : 'Coimbatore';
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loadingBlogs, setLoadingBlogs] = useState(true);
  const [activeBlogCategory, setActiveBlogCategory] = useState<'all' | 'news' | 'tax' | 'guide' | 'investment'>('all');

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

  // Fetch real cities & blogs on mount
  useEffect(() => {
    // 1. Real cities with real property data
    fetchRealDataFacets()
      .then((facets) => {
        if (facets.cities.length > 0) {
          setCities(
            facets.cities.map((c) => ({
              id: c.id,
              name: `${c.name} (${c.count})`,
              state: 'Tamil Nadu',
            }))
          );
        } else {
          // Fallback to getCities if needed
          getCities()
            .then((res) => {
              if (res.data?.success && Array.isArray(res.data.cities)) {
                setCities(res.data.cities);
              }
            })
            .catch(() => { });
        }
      })
      .catch(() => {
        getCities()
          .then((res) => {
            if (res.data?.success && Array.isArray(res.data.cities)) {
              setCities(res.data.cities);
            }
          })
          .catch(() => { });
      });

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
                const imageUrlRaw =
                  item.home_banner_image_url ||
                  item.thumbnail_url ||
                  (Array.isArray(item.image_urls) ? item.image_urls[0] : '') ||
                  '';
                if (!imageUrlRaw) return null;
                const imageUrl = toFrontendAssetUrl(imageUrlRaw);
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

  // Homepage sections — ultra fast single-batch active listings filtered by city & selected property type
  useEffect(() => {
    const cityParams = cityId ? { city_id: cityId } : {};
    const typeParams = mainTypeSlug ? { property_type: mainTypeSlug } : {};
    const baseParams = { limit: 50, sort: 'new', ...cityParams, ...typeParams };

    setLoadingRecommended(true);
    setLoadingNewlyLaunched(true);
    setLoadingVerified(true);
    setLoadingFeatured(true);
    setLoadingBestRated(true);
    setLoadingHighGrowth(true);

    searchProperties(baseParams)
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.items)) {
          const items: Property[] = res.data.items;

          // Helper to get subset by flag or fallback to items
          const filterByFlag = (flagName: keyof Property, fallbackCount = 6) => {
            const flagged = items.filter((p) => Boolean(p[flagName]));
            return flagged.length > 0 ? flagged : items.slice(0, fallbackCount);
          };

          setRecommended(filterByFlag('is_recommended'));
          setNewlyLaunched(filterByFlag('is_newly_launched'));
          setVerified(filterByFlag('is_verified_property'));
          setFeatured(filterByFlag('is_featured'));
          setBestRated(filterByFlag('tags_best_rate_localities' as keyof Property));
          setHighGrowth(filterByFlag('tags_high_growth_localities' as keyof Property));
        } else {
          setRecommended([]);
          setNewlyLaunched([]);
          setVerified([]);
          setFeatured([]);
          setBestRated([]);
          setHighGrowth([]);
        }
      })
      .catch((e) => {
        console.warn('Could not fetch listings batch', e);
        setRecommended([]);
        setNewlyLaunched([]);
        setVerified([]);
        setFeatured([]);
        setBestRated([]);
        setHighGrowth([]);
      })
      .finally(() => {
        setLoadingRecommended(false);
        setLoadingNewlyLaunched(false);
        setLoadingVerified(false);
        setLoadingFeatured(false);
        setLoadingBestRated(false);
        setLoadingHighGrowth(false);
      });
  }, [cityId, mainTypeSlug]);

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
        setWishlistedIds((prev) => {
          const isWishlisted = prev.includes(propertyId);
          if (!isWishlisted) {
            confetti({
              particleCount: 100,
              spread: 70,
              origin: { y: 0.6 },
              colors: ['#ef4444', '#f87171', '#fca5a5', '#0b2c56', '#f2b203']
            });
            return [...prev, propertyId];
          }
          return prev.filter((id) => id !== propertyId);
        });
      }
    } catch (error) {
      console.error('Error toggling wishlist:', error);
    }
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const queryParams = buildSearchUrlParams({
      mainTypeSlug,
      subTypeSlug,
      propertyType,
      cityId,
      q: searchQuery,
      minPrice,
      maxPrice,
      bedrooms,
      sort: sortBy,
    });
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

            {/* Property Categories (Apartments, Villas, etc.) */}
            <PropertyCategories
              cityId={cityId}
              cityName={cityName}
            />


            <NewlyLaunchedProjects
              items={newlyLaunched}
              loading={loadingNewlyLaunched}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />
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


            {/* Best Rated Properties Section */}
            <BestRatedProperties
              items={bestRated}
              loading={loadingBestRated}
              wishlistedIds={wishlistedIds}
              cityName={cityName}
              handleWishlistToggle={handleWishlistToggle}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />






            {/* Handpicked Projects Section */}
            <HandpickedProjects
              featured={featured}
              loadingFeatured={loadingFeatured}
            />

            {/* Magic Loans Auto Scroll Banner */}
            <MagicLoans />
            <VerifiedProperties
              items={verified}
              loading={loadingVerified}
              cityName={cityName}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />
            {/* Verified Listings Banner */}
            <VerifiedBanner />
            {/* High Growth Properties Section */}
            <HighGrowthProperties
              items={highGrowth}
              loading={loadingHighGrowth}
              wishlistedIds={wishlistedIds}
              cityName={cityName}
              handleWishlistToggle={handleWishlistToggle}
              formatPrice={formatPrice}
              getPropertyTypeLabel={getPropertyTypeLabel}
            />
            {/* Classic Property Research Tools Carousel & Calculators */}
            <ResearchTools />

            {/* Explore Cities */}
            <ExploreCities />
            {/* <RecommendedSellers properties={[...recommended, ...newlyLaunched, ...verified, ...featured]} /> */}

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

            {/* Property Videos Section */}
            <PropertyVideos />

            {/* Promo Section (Sell/Rent Faster Banner) */}
            <PromoSection
              user={user}
              setAuthModalOpen={setAuthModalOpen}
            />

            {/* From Our Blog Section */}
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
