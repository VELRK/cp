'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import PropertyCard, { Property } from '@/components/property/PropertyCard';
import { useAuth } from '@/hooks/useAuth';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import { getPropertySlugFromPath, PROPERTY_PLACEHOLDER_SLUG } from '@/lib/propertySlug';
import confetti from 'canvas-confetti';

import {
  MapPin, Bed, Bath, Grid, Calendar, ShieldCheck, Heart, Eye, Play,
  ArrowLeft, Mail, Phone, ChevronLeft, ChevronRight, Check, Key, Star,
  Image as ImageIcon, Compass, Info, Tag, ExternalLink, Share2,
  Printer, BookOpen, ChevronUp, ChevronDown,
  Car, Wifi, Wind, Tv, Coffee, Dumbbell, Trees, Shield, Droplets, Zap
} from 'lucide-react';

const getAmenityInfo = (name: string) => {
  const n = name.toLowerCase();
  if (n.includes('park') || n.includes('garage') || n.includes('car')) return { icon: <Car size={18} />, color: '#0d6efd', bg: '#e8f4fd' };
  if (n.includes('wifi') || n.includes('internet')) return { icon: <Wifi size={18} />, color: '#6b46c1', bg: '#f2eefb' };
  if (n.includes('ac') || n.includes('air cond') || n.includes('hvac')) return { icon: <Wind size={18} />, color: '#0891b2', bg: '#e0f2fe' };
  if (n.includes('tv') || n.includes('cable') || n.includes('dish')) return { icon: <Tv size={18} />, color: '#dc3545', bg: '#fff0f0' };
  if (n.includes('gym') || n.includes('fit') || n.includes('health')) return { icon: <Dumbbell size={18} />, color: '#ea580c', bg: '#ffedd5' };
  if (n.includes('garden') || n.includes('landscape') || n.includes('tree')) return { icon: <Trees size={18} />, color: '#16a34a', bg: '#dcfce7' };
  if (n.includes('security') || n.includes('guard') || n.includes('cctv') || n.includes('safe')) return { icon: <Shield size={18} />, color: '#4f46e5', bg: '#e0e7ff' };
  if (n.includes('pool') || n.includes('water') || n.includes('swim')) return { icon: <Droplets size={18} />, color: '#0284c7', bg: '#e0f2fe' };
  if (n.includes('power') || n.includes('backup') || n.includes('electric')) return { icon: <Zap size={18} />, color: '#d97706', bg: '#fef3c7' };
  if (n.includes('club') || n.includes('coffee') || n.includes('lounge')) return { icon: <Coffee size={18} />, color: '#78350f', bg: '#fef3c7' };
  return { icon: <Check size={18} />, color: '#475569', bg: '#f1f5f9' };
};

interface PropertyDetailClientProps {
  slug: string;
}

export default function PropertyDetailClient({ slug: slugProp }: PropertyDetailClientProps) {
  const { user, setAuthModalOpen } = useAuth();

  const [slug, setSlug] = useState(slugProp);
  const [property, setProperty] = useState<any>(null);
  const [similar, setSimilar] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeImageIdx, setActiveImageIdx] = useState(0);
  const [enqEmail, setEnqEmail] = useState('');
  const [enqPhone, setEnqPhone] = useState('');
  const [enqMessage, setEnqMessage] = useState('I\'m interested in this listing. Please share owner contact details.');
  const [enqLoading, setEnqLoading] = useState(false);
  const [enqStatus, setEnqStatus] = useState<{ success: boolean; message: string } | null>(null);
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [wishPulse, setWishPulse] = useState(false);
  const [pageReady, setPageReady] = useState(false);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [shareOpen, setShareOpen] = useState(false);
  const [isCompared, setIsCompared] = useState(false);
  const [descExpanded, setDescExpanded] = useState(false);
  const [copied, setCopied] = useState(false);

  const handleCopyLink = () => {
    if (typeof window !== 'undefined') {
      navigator.clipboard.writeText(window.location.href);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  const formatDate = (dateStr: string | null | undefined) => {
    if (!dateStr) return 'Recently';
    try {
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return 'Recently';
      return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
      return 'Recently';
    }
  };

  useEffect(() => {
    const fromPath = getPropertySlugFromPath();
    if (fromPath) {
      setSlug(fromPath);
    } else if (slugProp && slugProp !== PROPERTY_PLACEHOLDER_SLUG) {
      setSlug(slugProp);
    }
  }, [slugProp]);

  useEffect(() => {
    if (!slug || slug === PROPERTY_PLACEHOLDER_SLUG) return;
    setLoading(true);
    setError(null);
    api.get(`/api/properties/${slug}`)
      .then((res) => {
        if (res.data?.success && res.data.property) {
          const p = res.data.property;
          setProperty(p);
          if (user) {
            setEnqEmail(user.email || '');
            setEnqPhone(user.phone || '');
          }
          api.get(`/api/nb/search?city_id=${p.city_id}&property_type=${p.property_type}&limit=4`)
            .then((sRes) => {
              if (sRes.data?.success && Array.isArray(sRes.data.items)) {
                setSimilar(sRes.data.items.filter((item: Property) => item.id !== p.id).slice(0, 3));
              }
            })
            .catch((e) => console.warn('Could not load similar properties', e));
        } else {
          setError('Property not found');
        }
      })
      .catch((err) => {
        setError(err.response?.data?.message || 'Property loading failed.');
      })
      .finally(() => {
        setLoading(false);
        setTimeout(() => setPageReady(true), 60);
      });
  }, [slug, user]);

  useEffect(() => {
    if (user && property) {
      api.get(`/api/nb/wishlist/check?property_id=${property.id}&userId=${user.id}`)
        .then((res) => {
          if (res.data?.success && res.data.wishlisted === true) setIsWishlisted(true);
        })
        .catch((e) => console.warn('Wishlist check failed', e));
    }
  }, [user, property]);

  const handleWishlistToggle = async () => {
    if (!user) { setAuthModalOpen('login'); return; }
    setWishPulse(true);
    setTimeout(() => setWishPulse(false), 400);
    try {
      const response = await api.post('/api/nb/wishlist/toggle', {
        property_id: property.id,
        userId: user.id,
      });
      if (response.data?.success) {
        if (!isWishlisted) {
          confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#ef4444', '#f87171', '#fca5a5', '#0b2c56', '#f2b203']
          });
        }
        setIsWishlisted(!isWishlisted);
      }
    } catch (error) {
      console.error('Error toggling wishlist:', error);
    }
  };

  const handleEnquirySubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) { setAuthModalOpen('login'); return; }
    setEnqLoading(true);
    setEnqStatus(null);
    try {
      const response = await api.post('/api/nb/enquiry', {
        property_id: property.id,
        user_id: user.id,
        name: user.name,
        email: enqEmail,
        phone: enqPhone,
        message: enqMessage,
      });
      if (response.data?.success) {
        setEnqStatus({ success: true, message: response.data.message || 'Enquiry sent! The administrator will contact you shortly.' });
      } else {
        setEnqStatus({ success: false, message: response.data.message || 'Could not send enquiry. Please try again.' });
      }
    } catch (err: any) {
      setEnqStatus({ success: false, message: err.response?.data?.message || 'A network error occurred. Please check and try again.' });
    } finally {
      setEnqLoading(false);
    }
  };

  /* ── LOADING ── */
  if (loading) {
    return (
      <>
        <style>{`
          @keyframes pd-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
          .pd-skel { background: linear-gradient(90deg,#f0f2f5 25%,#e4e6ea 50%,#f0f2f5 75%); background-size:200% 100%; animation:pd-shimmer 1.5s infinite; border-radius:10px; }
          .pd-skel-page { padding:2.5rem 1rem; max-width:1200px; margin:0 auto; display:flex; flex-direction:column; gap:1.25rem; }
          .pd-skel-hero { height:420px; }
          .pd-skel-row  { display:flex; gap:1.5rem; }
          .pd-skel-main { flex:1; display:flex; flex-direction:column; gap:.75rem; }
          .pd-skel-line { height:16px; } .pd-skel-line.w80{width:80%} .pd-skel-line.w55{width:55%}
          .pd-skel-box  { height:100px; }
          .pd-skel-side { width:320px; flex-shrink:0; height:380px; }
        `}</style>
        <div className="pd-skel-page">
          <div className="pd-skel pd-skel-hero" />
          <div className="pd-skel-row">
            <div className="pd-skel-main">
              <div className="pd-skel pd-skel-line w80" />
              <div className="pd-skel pd-skel-line w55" />
              <div className="pd-skel pd-skel-box" />
              <div className="pd-skel pd-skel-line w80" />
              <div className="pd-skel pd-skel-line w55" />
            </div>
            <div className="pd-skel pd-skel-side" />
          </div>
        </div>
      </>
    );
  }

  /* ── ERROR ── */
  if (error || !property) {
    return (
      <>
        <style>{pageStyles}</style>
        <div className="container py-5 text-center">
          <div className="pd-error-box py-4 my-5">
            <h2 className="fw-bold mb-2">Property not found</h2>
            <p className="text-muted">{error || 'Property details could not be loaded.'}</p>
            <Link href="/search" className="pd-btn-primary mt-2 d-inline-flex align-items-center gap-1">
              <ArrowLeft size={14} /> Back to Search
            </Link>
          </div>
        </div>
      </>
    );
  }

  const images = (Array.isArray(property.image_urls) ? property.image_urls : [])
    .filter((url: unknown): url is string => typeof url === 'string' && url.trim() !== '')
    .map((url: string) => toFrontendAssetUrl(url));
  const amenities = Array.isArray(property.amenities) ? property.amenities : [];
  const isOwner = user && Number(user.id) === Number(property.owner_id);
  const isApproved = user && user.status === 'approved';

  const formatPrice = (price: unknown) => {
    if (property.price_formatted) return property.price_formatted;
    const amount = Number(price);
    return Number.isFinite(amount) ? `₹${amount.toLocaleString('en-IN')}` : 'Price on request';
  };

  const getYoutubeEmbed = (url: string) => {
    if (!url) return null;
    const match = url.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/);
    if (match && match[2].length === 11) {
      return (
        <iframe width="100%" height="400"
          src={`https://www.youtube.com/embed/${match[2]}`}
          title="YouTube video player" frameBorder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowFullScreen className="pd-video-frame rounded-3 border" />
      );
    }
    return null;
  };

  const videoEmbed = property.video_url ? getYoutubeEmbed(property.video_url) : null;

  const hasCoords = property.latitude && property.longitude;
  const mapQuery = hasCoords
    ? `${property.latitude},${property.longitude}`
    : `${property.address || property.locality || ''}, ${property.city_name || ''}`;
  const mapUrl = `https://maps.google.com/maps?q=${encodeURIComponent(mapQuery)}&t=&z=15&ie=UTF8&iwloc=&output=embed`;

  const locationImageUrl = property.location_image
    ? toFrontendAssetUrl(String(property.location_image))
    : null;

  const nearbyList = (() => {
    if (!property.nearby) return [];
    try {
      const parsed = typeof property.nearby === 'string' ? JSON.parse(property.nearby) : property.nearby;
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      console.warn('Error parsing nearby list:', e);
      return [];
    }
  })();

  const specItems = [
    { label: 'Property Type', value: property.property_type_label || property.property_type, icon: <Grid size={16} /> },
    { label: 'Listing For', value: property.listing_type === 'rent' ? 'Rent' : 'Sale', icon: <Tag size={16} /> },
    { label: 'Bedrooms', value: property.bedrooms ? `${property.bedrooms} BHK` : 'N/A', icon: <Bed size={16} /> },
    { label: 'Bathrooms', value: property.bathrooms ? `${property.bathrooms} Baths` : 'N/A', icon: <Bath size={16} /> },
    { label: 'Covered Area', value: property.area_sqft ? `${property.area_sqft} sq.ft.` : 'N/A', icon: <Compass size={16} /> },
    { label: 'Rate / Sq.Ft.', value: property.rate_per_sqft ? `₹${Number(property.rate_per_sqft).toLocaleString('en-IN')}` : 'N/A', icon: <Key size={16} /> },
    { label: 'Plot Length', value: property.plot_length_ft ? `${property.plot_length_ft} ft` : 'N/A', icon: <Grid size={16} /> },
    { label: 'Plot Width', value: property.plot_width_ft ? `${property.plot_width_ft} ft` : 'N/A', icon: <Grid size={16} /> },
    { label: 'Boundary Wall', value: property.has_boundary_wall === 1 ? 'Yes' : property.has_boundary_wall === 0 ? 'No' : 'N/A', icon: <ShieldCheck size={16} /> },
    { label: 'Available From', value: property.available_from ? new Date(property.available_from).toLocaleDateString('en-IN') : 'Immediate', icon: <Calendar size={16} /> },
    { label: 'Negotiable Price', value: property.is_price_negotiable === 1 ? 'Yes' : 'No', icon: <Info size={16} /> },
    { label: 'Listing ID', value: `#${property.id}`, icon: <Info size={16} /> },
  ];

  const specColors = [
    { bg: '#eef2ff', color: '#4f46e5' },
    { bg: '#f0fdf4', color: '#16a34a' },
    { bg: '#fff7ed', color: '#ea580c' },
    { bg: '#fdf2f8', color: '#db2777' },
    { bg: '#eff6ff', color: '#2563eb' },
    { bg: '#fef2f2', color: '#dc2626' },
    { bg: '#f5f3ff', color: '#7c3aed' },
    { bg: '#f0fdfa', color: '#0d9488' },
  ];

  return (
    <>
      <style>{pageStyles}</style>

      <div className={`nb-property-detail py-5${pageReady ? ' pd-ready' : ''}`}>
        <div className="container pt-3">

          {/* Breadcrumb */}
          <nav className="mb-3 pd-anim-1" aria-label="breadcrumb">
            <ol className="breadcrumb small">
              <li className="breadcrumb-item">
                <Link href="/" className="text-decoration-none pd-breadcrumb-link">Home</Link>
              </li>
              <li className="breadcrumb-item">
                <Link href="/search" className="text-decoration-none pd-breadcrumb-link">Search results</Link>
              </li>
              <li className="breadcrumb-item active text-truncate" aria-current="page" style={{ maxWidth: '300px' }}>
                {property.title}
              </li>
            </ol>
          </nav>

          <Link href="/search" className="pd-back-link mb-4 d-inline-flex align-items-center gap-1 pd-anim-1">
            <ArrowLeft size={14} />
            <span>Back to search results</span>
          </Link>

          <div className="row g-4 g-xl-5">

            {/* ── LEFT COLUMN ── */}
            <div className="col-lg-8">

              {/* Gallery Section */}
              <div className="nb-detail-gallery-wrap position-relative mb-4 pd-anim-1">
                {property.is_featured === 1 && (
                  <span className="pd-featured-badge">★ Featured</span>
                )}

                {images.length > 0 ? (
                  <div className="pd-gallery-container">
                    <div className="row g-2">
                      <div className={images.length > 1 ? "col-md-8" : "col-12"}>
                        <div className="pd-gallery-main-viewport" onClick={() => setLightboxOpen(true)}>
                          <img
                            src={images[activeImageIdx]}
                            className="pd-gallery-main-img"
                            alt={`${property.title} - Photo ${activeImageIdx + 1}`}
                          />
                          <div className="pd-gallery-overlay" />
                          <div className="pd-photo-count-badge">
                            <ImageIcon size={14} />
                            <span>{activeImageIdx + 1} / {images.length} Photos</span>
                          </div>

                          {/* Floating Gallery Actions */}
                          <div className="pd-gallery-float-actions">
                            <button
                              type="button"
                              className={`pd-gallery-action-btn${isWishlisted ? ' active' : ''}`}
                              onClick={(e) => { e.stopPropagation(); handleWishlistToggle(); }}
                              aria-label="Wishlist"
                              title="Save to Wishlist"
                            >
                              <Heart size={16} fill={isWishlisted ? '#ef4444' : 'none'} stroke={isWishlisted ? '#ef4444' : '#1f2937'} />
                            </button>
                            <button
                              type="button"
                              className={`pd-gallery-action-btn${shareOpen ? ' active' : ''}`}
                              onClick={(e) => { e.stopPropagation(); setShareOpen(!shareOpen); }}
                              aria-label="Share"
                              title="Share Listing"
                            >
                              <Share2 size={16} />
                            </button>
                            <button
                              type="button"
                              className={`pd-gallery-action-btn${isCompared ? ' active' : ''}`}
                              onClick={(e) => { e.stopPropagation(); setIsCompared(!isCompared); }}
                              aria-label="Compare"
                              title="Compare Property"
                            >
                              {isCompared ? <Check size={16} className="text-success" /> : <Grid size={16} />}
                            </button>
                            <button
                              type="button"
                              className="pd-gallery-action-btn"
                              onClick={(e) => { e.stopPropagation(); window.print(); }}
                              aria-label="Print"
                              title="Print Listing"
                            >
                              <Printer size={16} />
                            </button>
                          </div>
                        </div>
                      </div>

                      {images.length > 1 && (
                        <div className="col-md-4 d-none d-md-block">
                          <div className="pd-gallery-side-grid">
                            {images.slice(0, 4).map((img: string, idx: number) => {
                              const isLastVisible = idx === 3 && images.length > 4;
                              return (
                                <div
                                  key={idx}
                                  className={`pd-gallery-side-item${idx === activeImageIdx ? ' active' : ''}`}
                                  onClick={() => isLastVisible ? setLightboxOpen(true) : setActiveImageIdx(idx)}
                                >
                                  <img src={img} alt={`Side preview ${idx + 1}`} />
                                  {isLastVisible && (
                                    <div className="pd-gallery-side-overlay">
                                      <span>+{images.length - 4} Photos</span>
                                    </div>
                                  )}
                                </div>
                              );
                            })}
                          </div>
                        </div>
                      )}
                    </div>

                    {/* Mobile Thumbnails scroll */}
                    {images.length > 1 && (
                      <div className="pd-mobile-thumbs-scroll d-md-none mt-2 px-2">
                        {images.map((img: string, idx: number) => (
                          <div
                            key={idx}
                            className={`pd-mobile-thumb-item${idx === activeImageIdx ? ' active' : ''}`}
                            onClick={() => setActiveImageIdx(idx)}
                          >
                            <img src={img} alt={`Thumb ${idx + 1}`} />
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="pd-img-placeholder d-flex flex-column align-items-center justify-content-center text-center text-muted">
                    <ImageIcon size={48} />
                    <span className="small mt-2">No photos listed by owner</span>
                  </div>
                )}
              </div>

              {/* Floating Property Summary Card */}
              <div className="pd-summary-card mb-4 pd-anim-2">
                <div className="pd-summary-header d-flex flex-wrap align-items-start justify-content-between gap-3">
                  <div>
                    <span className="pd-summary-id-badge">Listing ID: #{property.id}</span>
                    <h1 className="pd-summary-title mt-2 mb-2">{property.title}</h1>
                    <div className="pd-summary-meta-row d-flex flex-wrap align-items-center gap-3">
                      <span className="pd-summary-location">
                        <MapPin size={14} className="text-gold" />
                        <span>{property.address || property.locality}, {property.city_name}</span>
                      </span>
                      <span className="pd-summary-rating-badge">
                        <Star size={12} fill="var(--nb-accent)" stroke="var(--nb-accent)" />
                        <span>4.8 (12 Verified Reviews)</span>
                      </span>
                    </div>
                  </div>

                  <div className="pd-summary-price-box text-md-end">
                    <div className="pd-summary-price">{formatPrice(property.price)}</div>
                    {property.rate_per_sqft && (
                      <div className="pd-summary-rate-sqft">
                        ₹{Number(property.rate_per_sqft).toLocaleString('en-IN')} / sq.ft.
                      </div>
                    )}
                  </div>
                </div>

                <hr className="pd-divider" />

                <div className="pd-summary-details-row d-flex flex-wrap align-items-center justify-content-between gap-3">
                  <div className="d-flex flex-wrap gap-2 align-items-center">
                    <span className={`pd-summary-tag ${property.listing_type === 'rent' ? 'rent' : 'sale'}`}>
                      For {property.listing_type === 'rent' ? 'Rent' : 'Sale'}
                    </span>
                    <span className="pd-summary-tag type">
                      {property.property_type_label || property.property_type}
                    </span>
                    {property.is_price_negotiable === 1 && (
                      <span className="pd-summary-tag negotiable">Negotiable</span>
                    )}
                  </div>

                  <div className="pd-summary-dates d-flex align-items-center gap-3 text-muted small">
                    <span>Posted: <strong>{formatDate(property.created_at)}</strong></span>
                    {property.updated_at && property.updated_at !== property.created_at && (
                      <span>Updated: <strong>{formatDate(property.updated_at)}</strong></span>
                    )}
                  </div>
                </div>

                {/* Share Dropdown UI */}
                {shareOpen && (
                  <div className="pd-share-dropdown-menu">
                    <div className="pd-share-dropdown-header">
                      <span>Share this Listing</span>
                      <button className="close-btn" onClick={() => setShareOpen(false)}>&times;</button>
                    </div>
                    <div className="pd-share-dropdown-body">
                      <a
                        href={`https://api.whatsapp.com/send?text=${encodeURIComponent(property.title + ' - ' + (typeof window !== 'undefined' ? window.location.href : ''))}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="share-option whatsapp"
                      >
                        WhatsApp
                      </a>
                      <a
                        href={`mailto:?subject=${encodeURIComponent(property.title)}&body=${encodeURIComponent(typeof window !== 'undefined' ? window.location.href : '')}`}
                        className="share-option email"
                      >
                        Email
                      </a>
                      <div className="share-copy-box">
                        <input
                          type="text"
                          readOnly
                          value={typeof window !== 'undefined' ? window.location.href : ''}
                          className="share-copy-input"
                        />
                        <button className="share-copy-btn" onClick={handleCopyLink}>
                          {copied ? 'Copied!' : 'Copy Link'}
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* Overview & Highlights Section */}
              <div className="pd-section-card mb-4 pd-anim-3">
                <h2 className="pd-section-title">
                  <Info size={17} />
                  <span>Overview & Highlights</span>
                </h2>
                <div className="pd-overview-grid">
                  {property.bedrooms && (
                    <div className="pd-overview-item">
                      <div className="pd-overview-icon-container">
                        <Bed size={20} />
                      </div>
                      <div className="pd-overview-content">
                        <span className="pd-overview-label">Bedrooms</span>
                        <span className="pd-overview-value">{property.bedrooms} BHK</span>
                      </div>
                    </div>
                  )}
                  {property.bathrooms && (
                    <div className="pd-overview-item">
                      <div className="pd-overview-icon-container">
                        <Bath size={20} />
                      </div>
                      <div className="pd-overview-content">
                        <span className="pd-overview-label">Bathrooms</span>
                        <span className="pd-overview-value">{property.bathrooms} Baths</span>
                      </div>
                    </div>
                  )}
                  {property.area_sqft && (
                    <div className="pd-overview-item">
                      <div className="pd-overview-icon-container">
                        <Compass size={20} />
                      </div>
                      <div className="pd-overview-content">
                        <span className="pd-overview-label">Covered Area</span>
                        <span className="pd-overview-value">{property.area_sqft} Sq.Ft.</span>
                      </div>
                    </div>
                  )}
                  <div className="pd-overview-item">
                    <div className="pd-overview-icon-container">
                      <Tag size={20} />
                    </div>
                    <div className="pd-overview-content">
                      <span className="pd-overview-label">Listing Type</span>
                      <span className="pd-overview-value text-capitalize">For {property.listing_type}</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Specifications / Property Details Section */}
              <div className="pd-specs-card mb-4 pd-anim-2">
                <h2 className="pd-section-title">
                  <Grid size={17} />
                  <span>Property Details & Specs</span>
                </h2>
                <div className="pd-specs-grid-classic">
                  {specItems.map((spec, index) => (
                    <div key={index} className="pd-spec-item-classic">
                      <div className="pd-spec-icon-classic">{spec.icon}</div>
                      <div className="pd-spec-info-classic">
                        <span className="pd-spec-label-classic">{spec.label}</span>
                        <span className="pd-spec-value-classic">{spec.value || 'N/A'}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* About Property with read more */}
              <div className="pd-section-card mb-4 pd-anim-3">
                <h2 className="pd-section-title">
                  <BookOpen size={17} />
                  <span>About this Property</span>
                </h2>
                <div className="pd-description-container">
                  <p className="pd-description">
                    {property.description
                      ? (descExpanded || property.description.length <= 350
                        ? property.description
                        : `${property.description.substring(0, 350)}...`)
                      : 'No description listed by the owner.'}
                  </p>
                  {property.description && property.description.length > 350 && (
                    <button
                      type="button"
                      className="pd-readmore-btn d-inline-flex align-items-center gap-1 mt-2"
                      onClick={() => setDescExpanded(!descExpanded)}
                    >
                      <span>{descExpanded ? 'Read Less' : 'Read More'}</span>
                      {descExpanded ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
                    </button>
                  )}
                </div>
              </div>

              {/* Amenities Section - Redesigned with Icons */}
              {amenities.length > 0 && (
                <div className="mb-4 pd-anim-3 shadow-sm rounded-4 bg-white" style={{ border: '1px solid #eaeaea', overflow: 'hidden' }}>
                  <div className="px-4 py-3 border-bottom d-flex align-items-center" style={{ backgroundColor: '#fcfcfc' }}>
                    <div className="d-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm border me-3" style={{ width: '42px', height: '42px', borderColor: '#f0f0f0' }}>
                      <Star size={20} className="text-warning" style={{ fill: '#ffc107' }} />
                    </div>
                    <h2 className="mb-0" style={{ fontSize: '1.3rem', fontWeight: 600, color: '#2c3e50' }}>
                      Premium Amenities
                    </h2>
                  </div>
                  <div className="p-4">
                    <div className="row g-3">
                      {amenities.map((item: string, idx: number) => {
                        const amenityInfo = getAmenityInfo(item);
                        return (
                          <div key={idx} className="col-6 col-md-4 col-lg-3">
                            <div className="d-flex align-items-center p-2 rounded-3" style={{ backgroundColor: '#fff', border: '1px solid #f1f3f5', transition: 'all 0.2s ease' }}
                                 onMouseEnter={(e) => { e.currentTarget.style.backgroundColor = amenityInfo.bg; e.currentTarget.style.borderColor = amenityInfo.color; e.currentTarget.style.transform = 'translateY(-2px)'; }}
                                 onMouseLeave={(e) => { e.currentTarget.style.backgroundColor = '#fff'; e.currentTarget.style.borderColor = '#f1f3f5'; e.currentTarget.style.transform = 'translateY(0)'; }}
                            >
                              <div className="me-3 rounded-circle d-flex align-items-center justify-content-center" style={{ width: '36px', height: '36px', backgroundColor: amenityInfo.bg, color: amenityInfo.color }}>
                                {amenityInfo.icon}
                              </div>
                              <span className="text-dark fw-medium" style={{ fontSize: '0.9rem', lineHeight: '1.2' }}>{item}</span>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              )}

              {/* Neighbourhood Guide & Connectivity */}
              {nearbyList.length > 0 && (
                <div className="pd-section-card mb-4 pd-anim-3">
                  <h2 className="pd-section-title">
                    <Compass size={17} />
                    <span>Neighbourhood & Connectivity</span>
                  </h2>
                  <div className="pd-nearby-grid">
                    {nearbyList.map((item: any, idx: number) => {
                      const cat = String(item.category || '').toLowerCase();
                      let catClass = 'other';
                      let catIcon = <MapPin size={14} />;
                      if (cat.includes('school') || cat.includes('college') || cat.includes('education')) {
                        catClass = 'education';
                        catIcon = <Star size={14} />;
                      } else if (cat.includes('hospital') || cat.includes('medical') || cat.includes('clinic')) {
                        catClass = 'medical';
                        catIcon = <Heart size={14} />;
                      } else if (cat.includes('bus') || cat.includes('station') || cat.includes('train') || cat.includes('transit')) {
                        catClass = 'transit';
                        catIcon = <Compass size={14} />;
                      }
                      return (
                        <div key={idx} className={`pd-nearby-card category-${catClass}`}>
                          <div className="pd-nearby-icon-wrap">{catIcon}</div>
                          <div className="pd-nearby-info">
                            <span className="pd-nearby-name" title={item.name || item.title || 'Nearby Facility'}>
                              {item.name || item.title || 'Nearby Facility'}
                            </span>
                            <span className="pd-nearby-cat">{item.category || 'Facility'}</span>
                          </div>
                          {item.distance && (
                            <span className="pd-nearby-dist">{item.distance}</span>
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}

              {/* Video */}
              {videoEmbed && (
                <div className="pd-section-card mb-4 pd-anim-4">
                  <h2 className="pd-section-title">
                    <Play size={17} />
                    <span>Video Tour</span>
                  </h2>
                  <div className="pd-video-frame-wrap overflow-hidden shadow-sm">
                    {videoEmbed}
                  </div>
                </div>
              )}

              {/* Location Map */}
              <div className="pd-section-card mb-4 pd-anim-4">
                <h2 className="pd-section-title">
                  <MapPin size={17} />
                  <span>Property Location & Maps</span>
                </h2>

                <p className="pd-location-text mb-3">
                  <MapPin size={14} className="text-danger flex-shrink-0 mt-1" />
                  <span>{property.address || property.locality}, {property.city_name}</span>
                </p>

                {/* Google Maps Embed iframe */}
                <div className="pd-map-frame-wrap overflow-hidden shadow-sm mb-3">
                  <iframe
                    width="100%"
                    height="350"
                    style={{ border: 0 }}
                    loading="lazy"
                    allowFullScreen
                    referrerPolicy="no-referrer-when-downgrade"
                    src={mapUrl}
                  />
                </div>

                <div className="d-flex flex-wrap gap-2">
                  <a href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(mapQuery)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="pd-map-link d-inline-flex align-items-center gap-2">
                    <ExternalLink size={14} />
                    <span>Open in Google Maps</span>
                  </a>
                  {property.location && typeof property.location === 'string' && property.location.startsWith('http') && (
                    <a href={property.location}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="pd-map-link pd-map-link--secondary d-inline-flex align-items-center gap-2">
                      <ExternalLink size={14} />
                      <span>View Original Link</span>
                    </a>
                  )}
                </div>

                {/* Owner's Location Image Uploaded */}
                {locationImageUrl && (
                  <div className="pd-location-image-wrap mt-4 pt-3 border-top">
                    <h3 className="pd-location-image-title mb-2 text-secondary small fw-bold text-uppercase">Location Map / Layout Image</h3>
                    <div className="pd-location-image-container border rounded overflow-hidden shadow-sm bg-light text-center p-2">
                      <img
                        src={locationImageUrl}
                        className="pd-location-image img-fluid rounded"
                        alt="Location layout / schematic uploaded by owner"
                        style={{ maxHeight: '280px', objectFit: 'contain' }}
                      />
                    </div>
                  </div>
                )}
              </div>

              {/* Luxury Owner / Agent Profile Card */}
              <div className="pd-section-card mb-4 pd-anim-4">
                <h2 className="pd-section-title">
                  <ShieldCheck size={17} />
                  <span>Agent & Listing Owner Info</span>
                </h2>
                <div className="pd-agent-profile-card">
                  <div className="d-flex align-items-center gap-3">
                    <div className="pd-agent-avatar text-white">
                      {(property.owner_name || 'Owner').substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                      <h3 className="pd-agent-name">{property.owner_name || 'Verified Listing Owner'}</h3>
                      <div className="d-flex align-items-center gap-2">
                        <span className="pd-agent-badge">Owner</span>
                        <span className="pd-agent-verified-label">
                          <ShieldCheck size={12} className="text-success inline-block align-middle me-1" />Verified Seller
                        </span>
                      </div>
                    </div>
                  </div>
                  <div className="pd-agent-stats mt-3 pt-3 border-top d-flex gap-4">
                    <div>
                      <div className="pd-agent-stat-label">Active Listings</div>
                      <div className="pd-agent-stat-value">1 Listing</div>
                    </div>
                    <div>
                      <div className="pd-agent-stat-label">Member Since</div>
                      <div className="pd-agent-stat-value">2026</div>
                    </div>
                  </div>
                  <div className="pd-agent-notice mt-3 p-3 bg-light rounded text-muted small">
                    <Info size={13} className="text-primary me-1 inline-block align-middle" />
                    <span>To get direct contact details of the owner, please use the enquiry form on the right. Details will be sent to your registered email and phone.</span>
                  </div>
                </div>
              </div>

            </div>

            {/* ── SIDEBAR ── */}
            <div className="col-lg-4">
              <div className="pd-enquiry-card sticky-top pd-anim-2" style={{ top: '6.5rem', zIndex: 10 }}>
                <div className="pd-enquiry-head">
                  <h2 className="pd-enquiry-title">Get Owner Details</h2>
                  <p className="pd-enquiry-sub">Submit your details and we'll route the enquiry to the owner.</p>
                </div>
                <div className="pd-enquiry-body">
                  {enqStatus && (
                    <div className={`pd-alert ${enqStatus.success ? 'pd-alert--success' : 'pd-alert--error'} mb-3`}>
                      {enqStatus.message}
                    </div>
                  )}

                  {!user ? (
                    <div className="text-center py-2">
                      <button className="pd-btn-primary w-100 animate-pulse" onClick={() => setAuthModalOpen('login')}>
                        Sign in to Enquire
                      </button>
                      <p className="small text-muted mt-3 mb-0">
                        Register free to browse and post listings.
                      </p>
                    </div>
                  ) : !isApproved ? (
                    <div className="pd-alert pd-alert--warning">
                      Your account is pending admin approval before you can contact owners.
                    </div>
                  ) : isOwner ? (
                    <div className="pd-alert pd-alert--info">
                      This is your listing. Buyer enquiries appear in your Owner Dashboard.
                    </div>
                  ) : (
                    <form onSubmit={handleEnquirySubmit}>
                      <div className="pd-form-group">
                        <label className="pd-form-label">Your Name</label>
                        <input type="text" className="pd-form-input" value={user.name} readOnly />
                      </div>
                      <div className="pd-form-group">
                        <label className="pd-form-label">Your Email</label>
                        <input type="email" className="pd-form-input"
                          value={enqEmail} onChange={(e) => setEnqEmail(e.target.value)} required />
                      </div>
                      <div className="pd-form-group">
                        <label className="pd-form-label">Your Phone</label>
                        <input type="tel" className="pd-form-input"
                          value={enqPhone} onChange={(e) => setEnqPhone(e.target.value)} required />
                      </div>
                      <div className="pd-form-group">
                        <label className="pd-form-label">Message</label>
                        <textarea className="pd-form-input" rows={3}
                          value={enqMessage} onChange={(e) => setEnqMessage(e.target.value)} required />
                      </div>
                      <button type="submit" className="pd-btn-primary w-100" disabled={enqLoading}>
                        {enqLoading
                          ? <span className="pd-spinner" />
                          : 'Send Enquiry'
                        }
                      </button>
                    </form>
                  )}
                </div>
                <div className="pd-enquiry-trust">
                  <ShieldCheck size={13} />
                  <span>Verified listings · Your info stays private</span>
                </div>
              </div>
            </div>
          </div>

          {/* Similar Properties */}
          {similar.length > 0 && (
            <section className="mt-5 pt-4 pd-similar-section pd-anim-4">
              <div className="mb-4">
                <h2 className="h4 fw-bold mb-1" style={{ color: 'var(--nb-secondary)' }}>Similar Properties</h2>
                <p className="text-muted small">Properties matching the same region &amp; category</p>
              </div>
              <div className="row g-4 nb-property-grid">
                {similar.map((sp) => (
                  <PropertyCard key={sp.id} property={sp} />
                ))}
              </div>
            </section>
          )}

        </div>
      </div>

      {/* Lightbox Modal */}
      {lightboxOpen && images.length > 0 && (
        <div className="pd-lightbox" onClick={() => setLightboxOpen(false)}>
          <button className="pd-lightbox-close" onClick={() => setLightboxOpen(false)} aria-label="Close lightbox">
            &times;
          </button>
          <div className="pd-lightbox-content" onClick={(e) => e.stopPropagation()}>
            <img
              src={images[activeImageIdx]}
              className="pd-lightbox-img animate-scale"
              alt={`${property.title} - Photo ${activeImageIdx + 1}`}
            />

            {images.length > 1 && (
              <>
                <button className="pd-lightbox-nav pd-lightbox-nav--prev"
                  onClick={() => setActiveImageIdx(p => p === 0 ? images.length - 1 : p - 1)}
                  aria-label="Previous photo">
                  <ChevronLeft size={28} />
                </button>
                <button className="pd-lightbox-nav pd-lightbox-nav--next"
                  onClick={() => setActiveImageIdx(p => p === images.length - 1 ? 0 : p + 1)}
                  aria-label="Next photo">
                  <ChevronRight size={28} />
                </button>
              </>
            )}

            <div className="pd-lightbox-counter">
              {activeImageIdx + 1} / {images.length}
            </div>
          </div>
        </div>
      )}
    </>
  );
}

/* ═══════════════════════════════════════════
   STYLES — scoped with pd- prefix (Luxury Real Estate Redesign)
═══════════════════════════════════════════ */
const pageStyles = `
  /* ── Page entry animations ── */
  @keyframes pd-fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes pd-heartPop {
    0%  { transform: scale(1); }
    35% { transform: scale(1.45); }
    65% { transform: scale(0.85); }
    100%{ transform: scale(1); }
  }
  @keyframes pd-spin {
    to { transform: rotate(360deg); }
  }
  @keyframes pd-pulse {
    0% { transform: scale(1); box-shadow: 0 4px 14px rgba(242, 178, 3, 0.3); }
    50% { transform: scale(1.02); box-shadow: 0 4px 20px rgba(242, 178, 3, 0.5); }
    100% { transform: scale(1); box-shadow: 0 4px 14px rgba(242, 178, 3, 0.3); }
  }

  .pd-ready .pd-anim-1 { animation: pd-fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
  .pd-ready .pd-anim-2 { animation: pd-fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both; }
  .pd-ready .pd-anim-3 { animation: pd-fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.16s both; }
  .pd-ready .pd-anim-4 { animation: pd-fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.24s both; }

  /* ── Breadcrumb ── */
  .pd-breadcrumb-link { color: var(--nb-muted); transition: color .18s; }
  .pd-breadcrumb-link:hover { color: var(--nb-primary); }

  .pd-back-link {
    color: var(--nb-muted); text-decoration: none; font-size: 13.5px;
    transition: all .2s;
    padding: 0;
  }
  .pd-back-link:hover { color: var(--nb-primary); transform: translateX(-2px); }

  /* ── Luxury Gallery Layout ── */
  .pd-gallery-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(11, 44, 86, 0.08);
    background: #071f3f;
    margin-bottom: 2rem;
  }
  .pd-gallery-main-viewport {
    position: relative;
    height: 480px;
    overflow: hidden;
    cursor: pointer;
  }
  .pd-gallery-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  }
  .pd-gallery-main-viewport:hover .pd-gallery-main-img {
    transform: scale(1.05);
  }
  .pd-gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(7, 31, 63, 0.5) 0%, transparent 60%);
    pointer-events: none;
  }

  /* Floating Photo Counter Badge */
  .pd-photo-count-badge {
    position: absolute;
    bottom: 1.25rem;
    left: 1.25rem;
    background: rgba(11, 44, 86, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 4;
  }

  /* Floating actions on gallery */
  .pd-gallery-float-actions {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
    display: flex;
    gap: 8px;
    z-index: 5;
  }
  .pd-gallery-action-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: var(--nb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(11, 44, 86, 0.15);
    transition: all 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  }
  .pd-gallery-action-btn:hover {
    transform: scale(1.1);
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(11, 44, 86, 0.25);
  }
  .pd-gallery-action-btn.active {
    background: #fff3f3;
  }

  /* Gallery Side Grid */
  .pd-gallery-side-grid {
    display: grid;
    grid-template-rows: repeat(2, 236px);
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    height: 480px;
  }
  .pd-gallery-side-item {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    background: #1e293b;
    border: 2px solid transparent;
    transition: all 0.25s;
  }
  .pd-gallery-side-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.8s ease;
  }
  .pd-gallery-side-item:hover img {
    transform: scale(1.08);
  }
  .pd-gallery-side-item.active {
    border-color: var(--nb-accent);
  }
  .pd-gallery-side-overlay {
    position: absolute;
    inset: 0;
    background: rgba(7, 31, 63, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
  }

  /* Mobile Thumbnails Horizontal Scroll */
  .pd-mobile-thumbs-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
  }
  .pd-mobile-thumb-item {
    flex-shrink: 0;
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
  }
  .pd-mobile-thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .pd-mobile-thumb-item.active {
    border-color: var(--nb-primary);
  }

  /* Image Placeholder */
  .pd-img-placeholder {
    height: 480px; border-radius: 20px;
    background: #f7f8fa; color: #9ca3af;
    border: 2px dashed #e5e7eb;
  }

  /* Full Screen Lightbox */
  .pd-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(7, 31, 63, 0.95);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .pd-lightbox-close {
    position: absolute;
    top: 2rem;
    right: 2rem;
    background: none;
    border: none;
    color: #fff;
    font-size: 3rem;
    cursor: pointer;
    line-height: 1;
    transition: transform 0.2s;
    z-index: 2100;
  }
  .pd-lightbox-close:hover {
    transform: scale(1.1);
  }
  .pd-lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .pd-lightbox-img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 10px 45px rgba(0,0,0,0.5);
  }
  .pd-lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s;
    z-index: 2050;
  }
  .pd-lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) scale(1.05);
  }
  .pd-lightbox-nav--prev {
    left: -80px;
  }
  .pd-lightbox-nav--next {
    right: -80px;
  }
  @media (max-width: 992px) {
    .pd-lightbox-nav--prev { left: 10px; }
    .pd-lightbox-nav--next { right: 10px; }
  }
  .pd-lightbox-counter {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 600;
    margin-top: 1rem;
  }

  /* Floating Summary Card */
  .pd-summary-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(11, 44, 86, 0.05);
    border: 1px solid var(--nb-card-border);
    position: relative;
    margin-top: -40px;
    z-index: 10;
  }
  .pd-summary-id-badge {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--nb-muted);
    background: var(--nb-primary-soft);
    padding: 4px 10px;
    border-radius: 6px;
  }
  .pd-summary-title {
    font-size: 24px;
    font-weight: 850;
    color: var(--nb-secondary);
    letter-spacing: -0.5px;
    line-height: 1.25;
  }
  .pd-summary-location {
    font-size: 14px;
    color: var(--nb-muted);
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .text-gold {
    color: var(--nb-accent) !important;
  }
  .pd-summary-rating-badge {
    font-size: 13px;
    font-weight: 600;
    color: var(--nb-secondary);
    background: #fffdf5;
    border: 1px solid rgba(242, 178, 3, 0.3);
    padding: 2px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .pd-summary-price-box {
    align-self: center;
  }
  .pd-summary-price {
    font-size: 32px;
    font-weight: 900;
    color: var(--nb-primary);
    line-height: 1;
    letter-spacing: -0.8px;
  }
  .pd-summary-rate-sqft {
    font-size: 13px;
    color: var(--nb-muted);
    font-weight: 600;
    margin-top: 4px;
  }
  .pd-divider {
    border: 0;
    border-top: 1px solid var(--nb-card-border);
    margin: 1.5rem 0;
  }
  
  .pd-summary-tag {
    font-size: 11px;
    font-weight: 750;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 6px 14px;
    border-radius: 20px;
  }
  .pd-summary-tag.rent {
    background: #e8f5e9;
    color: #2e7d32;
  }
  .pd-summary-tag.sale {
    background: var(--nb-primary-soft);
    color: var(--nb-primary);
  }
  .pd-summary-tag.negotiable {
    background: #fff8e1;
    color: var(--nb-accent-dark);
    border: 1px solid #ffe082;
  }
  .pd-summary-tag.type {
    background: var(--nb-mint);
    color: var(--nb-secondary);
  }

  /* Share Dropdown */
  .pd-share-dropdown-menu {
    position: absolute;
    top: calc(100% - 10px);
    right: 2rem;
    width: 320px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid var(--nb-card-border);
    box-shadow: 0 10px 30px rgba(11, 44, 86, 0.15);
    z-index: 100;
    padding: 1.25rem;
    animation: pd-fadeUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  .pd-share-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    font-weight: 700;
    color: var(--nb-secondary);
  }
  .pd-share-dropdown-header .close-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: var(--nb-muted);
  }
  .pd-share-dropdown-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .share-option {
    display: block;
    width: 100%;
    padding: 10px;
    text-align: center;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    font-size: 13.5px;
    transition: all 0.2s;
  }
  .share-option.whatsapp {
    background: #e8f5e9;
    color: #2e7d32;
  }
  .share-option.whatsapp:hover {
    background: #c8e6c9;
  }
  .share-option.email {
    background: var(--nb-primary-soft);
    color: var(--nb-primary);
  }
  .share-option.email:hover {
    background: #dbeafe;
  }
  .share-copy-box {
    display: flex;
    gap: 6px;
    margin-top: 8px;
  }
  .share-copy-input {
    flex-grow: 1;
    font-size: 12px;
    padding: 8px;
    border: 1px solid var(--nb-card-border);
    border-radius: 6px;
    outline: none;
    color: var(--nb-muted);
  }
  .share-copy-btn {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--nb-primary-dark);
    background: var(--nb-accent);
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  .share-copy-btn:hover {
    background: var(--nb-accent-dark);
  }

  .pd-featured-badge {
    position: absolute;
    top: 1.25rem;
    left: 1.25rem;
    z-index: 3;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .7px;
    text-transform: uppercase;
    background: linear-gradient(135deg, var(--nb-accent), var(--nb-accent-dark));
    color: var(--nb-primary-dark);
    padding: 5px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(242,178,3,.35);
  }

  /* Overview Features */
  .pd-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.25rem;
  }
  .pd-overview-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.25rem;
    background: linear-gradient(135deg, var(--nb-primary-soft) 0%, rgba(238,243,251,0.4) 100%);
    border-radius: 12px;
    border: 1px solid rgba(11, 44, 86, 0.05);
    transition: transform 0.25s, box-shadow 0.25s;
  }
  .pd-overview-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(11, 44, 86, 0.08);
  }
  .pd-overview-icon-container {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #ffffff;
    color: var(--nb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(11, 44, 86, 0.06);
    flex-shrink: 0;
  }
  .pd-overview-content {
    display: flex;
    flex-direction: column;
  }
  .pd-overview-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--nb-muted);
    font-weight: 600;
  }
  .pd-overview-value {
    font-size: 15px;
    font-weight: 750;
    color: var(--nb-secondary);
  }

  /* ── Specs card ── */
  .pd-specs-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(11, 44, 86, 0.04);
    border: 1px solid var(--nb-card-border);
  }
  .pd-specs-grid-classic {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    border-top: 2px solid var(--nb-secondary);
    border-left: 1px solid #e0e0e0;
  }
  .pd-spec-item-classic {
    display: flex;
    align-items: center;
    padding: 14px 16px;
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    transition: background-color 0.2s;
  }
  .pd-spec-item-classic:nth-child(even) {
    background: #f9f9f9;
  }
  .pd-spec-item-classic:hover {
    background: #f1f1f1;
  }
  .pd-spec-icon-classic {
    width: 28px;
    height: 28px;
    color: var(--nb-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    opacity: 0.8;
  }
  .pd-spec-info-classic {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: space-between;
    margin-left: 12px;
  }
  .pd-spec-label-classic {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--nb-muted);
    font-weight: 600;
  }
  .pd-spec-value-classic {
    font-size: 14px;
    font-weight: 700;
    color: var(--nb-secondary);
  }
  .pd-spec-info {
    display: flex;
    flex-direction: column;
  }
  .pd-spec-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--nb-muted);
    font-weight: 600;
  }
  .pd-spec-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--nb-secondary);
  }

  /* ── Section cards ── */
  .pd-section-card {
    background: #fff; border-radius: 20px; padding: 2rem;
    box-shadow: 0 4px 20px rgba(11, 44, 86, 0.04);
    border: 1px solid var(--nb-card-border);
    transition: box-shadow .25s, transform 0.25s;
  }
  .pd-section-card:hover { box-shadow: 0 8px 30px rgba(11,44,86,0.07); transform: translateY(-2px); }

  .pd-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 1.15rem; font-weight: 750; color: var(--nb-secondary);
    margin: 0 0 1.5rem; padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--nb-primary-soft);
  }
  .pd-section-title svg { color: var(--nb-primary); }

  .pd-description { font-size: 14.5px; color: var(--nb-text); line-height: 1.8; white-space: pre-line; margin: 0; }

  /* Description Card Readmore */
  .pd-description-container {
    position: relative;
  }
  .pd-readmore-btn {
    background: none;
    border: none;
    color: var(--nb-primary);
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    padding: 4px 0;
    transition: gap 0.2s;
  }
  .pd-readmore-btn:hover {
    color: var(--nb-accent-dark);
  }

  /* Amenities */
  .pd-amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
  }
  .pd-amenity-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: var(--nb-light-bg);
    border: 1px solid var(--nb-card-border);
    border-radius: 10px;
    transition: all 0.22s ease;
  }
  .pd-amenity-card:hover {
    transform: translateY(-2px) scale(1.02);
    border-color: rgba(46, 125, 50, 0.3);
    background: #f4faf4;
    box-shadow: 0 6px 15px rgba(46,125,50,0.06);
  }
  .pd-amenity-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #2e7d32;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .pd-amenity-name {
    font-size: 13.5px;
    font-weight: 650;
    color: var(--nb-secondary);
  }

  /* Nearby Grid */
  .pd-nearby-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
  }
  .pd-nearby-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--nb-light-bg);
    border: 1px solid var(--nb-card-border);
    border-radius: 10px;
    transition: all 0.22s ease;
  }
  .pd-nearby-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(11, 44, 86, 0.06);
    border-color: #cbd5e1;
  }
  .pd-nearby-icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--nb-card-border);
    flex-shrink: 0;
  }
  .pd-nearby-info {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    overflow: hidden;
  }
  .pd-nearby-name {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--nb-secondary);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }
  .pd-nearby-cat {
    font-size: 11px;
    color: var(--nb-muted);
    text-transform: capitalize;
  }
  .pd-nearby-dist {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--nb-primary);
    background: var(--nb-primary-soft);
    padding: 2px 8px;
    border-radius: 12px;
    white-space: nowrap;
  }

  /* Nearby Color Coded Categories */
  .pd-nearby-card.category-education {
    border-left: 4px solid var(--nb-accent);
  }
  .pd-nearby-card.category-medical {
    border-left: 4px solid #ef4444;
  }
  .pd-nearby-card.category-transit {
    border-left: 4px solid var(--nb-primary);
  }
  .pd-nearby-card.category-education .pd-nearby-icon-wrap {
    background: #fffdf2;
    color: var(--nb-accent-dark);
    border-color: rgba(242, 178, 3, 0.2);
  }
  .pd-nearby-card.category-medical .pd-nearby-icon-wrap {
    background: #fef2f2;
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.2);
  }
  .pd-nearby-card.category-transit .pd-nearby-icon-wrap {
    background: var(--nb-primary-soft);
    color: var(--nb-primary);
    border-color: rgba(11, 44, 86, 0.1);
  }

  /* Video */
  .pd-video-frame-wrap { border-radius: 16px !important; overflow: hidden; background: #000; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
  .pd-video-frame { border-radius: 0 !important; display: block; border: none !important; }

  /* Map and Location */
  .pd-map-frame-wrap { border-radius: 16px !important; overflow: hidden; }
  .pd-map-link {
    font-size: 13.5px; font-weight: 700; color: var(--nb-primary-dark);
    text-decoration: none; padding: 8px 16px; border-radius: 8px;
    background: var(--nb-accent); border: 1.5px solid var(--nb-accent);
    transition: all .2s;
  }
  .pd-map-link:hover { background: var(--nb-accent-dark); border-color: var(--nb-accent-dark); transform: translateY(-1px); }
  .pd-map-link--secondary {
    color: var(--nb-secondary); background: #fff; border: 1.5px solid var(--nb-card-border);
  }
  .pd-map-link--secondary:hover { background: var(--nb-light-bg); border-color: #cbd5e1; }
  .pd-location-text {
    font-size: 14px; color: var(--nb-text); line-height: 1.6;
    background: var(--nb-light-bg); border: 1px solid var(--nb-card-border);
    border-radius: 10px; padding: 12px 14px; display: flex; gap: 8px; align-items: flex-start;
  }

  /* Agent Profile Card */
  .pd-agent-profile-card {
    background: linear-gradient(to bottom right, #ffffff, var(--nb-light-bg));
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid var(--nb-card-border);
  }
  .pd-agent-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--nb-primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(11, 44, 86, 0.15);
  }
  .pd-agent-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--nb-secondary);
    margin: 0;
  }
  .pd-agent-badge {
    font-size: 11px;
    font-weight: 700;
    background: var(--nb-primary-soft);
    color: var(--nb-primary);
    padding: 2px 8px;
    border-radius: 4px;
  }
  .pd-agent-verified-label {
    font-size: 12px;
    color: var(--nb-muted);
    font-weight: 600;
  }
  .pd-agent-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--nb-muted);
    font-weight: 600;
  }
  .pd-agent-stat-value {
    font-size: 15px;
    font-weight: 750;
    color: var(--nb-secondary);
  }

  /* ── Enquiry sidebar card ── */
  .pd-enquiry-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(11, 44, 86, 0.08), 0 0 0 1px rgba(11, 44, 86, 0.04);
    overflow: hidden;
    border: 1px solid var(--nb-card-border);
  }
  .pd-enquiry-head {
    background: linear-gradient(135deg, var(--nb-primary) 0%, var(--nb-primary-dark) 100%);
    padding: 1.5rem;
    border-top: 4px solid var(--nb-accent);
  }
  .pd-enquiry-title { font-size: 17px; font-weight: 800; color: #fff; margin: 0 0 4px; }
  .pd-enquiry-sub   { font-size: 12.5px; color: rgba(255,255,255,.8); margin: 0; line-height: 1.4; }
  .pd-enquiry-body  { padding: 1.5rem; }
  .pd-enquiry-trust {
    display: flex; align-items: center; gap: 6px;
    padding: 0.85rem 1.5rem; background: var(--nb-light-bg);
    border-top: 1px solid var(--nb-card-border);
    font-size: 11.5px; color: var(--nb-muted);
  }
  .pd-enquiry-trust svg { color: #2e7d32; }

  /* Form */
  .pd-form-group { margin-bottom: 1rem; }
  .pd-form-label { display: block; font-size: 11px; font-weight: 700; color: var(--nb-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .5px; }
  .pd-form-input {
    display: block; width: 100%; padding: 10px 14px;
    font-size: 13.5px; color: var(--nb-text); font-family: inherit;
    background: var(--nb-light-bg); border: 1.5px solid var(--nb-card-border); border-radius: 9px;
    outline: none; transition: all .18s;
  }
  .pd-form-input:focus {
    border-color: var(--nb-primary); background: #fff;
    box-shadow: 0 0 0 3.5px rgba(11, 44, 86, 0.1);
  }
  .pd-form-input[readonly] { background: #f1f5f9; color: #94a3b8; cursor: default; }
  textarea.pd-form-input { resize: vertical; }

  /* Alerts */
  .pd-alert {
    font-size: 13px; padding: 12px 14px; border-radius: 9px; line-height: 1.45;
  }
  .pd-alert--success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
  .pd-alert--error   { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
  .pd-alert--warning { background: #fff8e1; color: #b7791f; border: 1px solid #ffe082; }
  .pd-alert--info    { background: var(--nb-primary-soft); color: var(--nb-primary); border: 1px solid rgba(11, 44, 86, 0.1); }

  /* Primary button */
  .pd-btn-primary {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    font-size: 14.5px; font-weight: 700; padding: 12px 22px;
    background: linear-gradient(135deg, var(--nb-accent), var(--nb-accent-dark));
    color: var(--nb-primary-dark); border: none; border-radius: 10px; cursor: pointer;
    box-shadow: 0 4px 14px rgba(242, 178, 3, 0.3);
    text-decoration: none;
    transition: all .2s;
    width: 100%;
  }
  .pd-btn-primary:hover {
    background: linear-gradient(135deg, var(--nb-accent-dark), #d59d02);
    transform: translateY(-1px); color: var(--nb-primary-dark);
    box-shadow: 0 6px 20px rgba(242, 178, 3, 0.45);
  }
  .pd-btn-primary:disabled { opacity: .65; transform: none; cursor: not-allowed; }

  .pd-spinner {
    display: inline-block; width: 18px; height: 18px;
    border: 2.5px solid rgba(7,31,63,.2);
    border-top-color: var(--nb-primary-dark); border-radius: 50%;
    animation: pd-spin .65s linear infinite;
  }

  .animate-pulse {
    animation: pd-pulse 2s infinite ease-in-out;
  }
  
  .animate-scale {
    animation: pd-fadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
  }

  /* Similar section */
  .pd-similar-section { border-top: 1px solid var(--nb-card-border); }

  /* Error box */
  .pd-error-box {
    background: #fff; border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    max-width: 480px; margin: 0 auto; padding: 2.5rem 2rem;
    text-align: center;
  }
`;