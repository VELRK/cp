'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import PropertyCard, { Property } from '@/components/property/PropertyCard';
import { useAuth } from '@/hooks/useAuth';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import { getPropertySlugFromPath, PROPERTY_PLACEHOLDER_SLUG } from '@/lib/propertySlug';

import { MapPin, Bed, Bath, Grid, Calendar, ShieldCheck, Heart, Eye, Play, ArrowLeft, Mail, Phone, ChevronLeft, ChevronRight, Check, Key, Star, Image as ImageIcon, Compass, Info, Tag, ExternalLink } from 'lucide-react';

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
      if (response.data?.success) setIsWishlisted(!isWishlisted);
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

              {/* Gallery */}
              <div className="nb-detail-gallery-wrap position-relative mb-4 pd-anim-1">
                {property.is_featured === 1 && (
                  <span className="pd-featured-badge">★ Featured</span>
                )}
                {images.length > 0 ? (
                  <div className="position-relative pd-gallery-wrap">
                    <div className="pd-gallery-frame">
                      <img
                        src={images[activeImageIdx]}
                        className="pd-gallery-img"
                        alt={`${property.title} - Photo ${activeImageIdx + 1}`}
                      />
                      <div className="pd-gallery-overlay" />
                    </div>
                    {images.length > 1 && (
                      <>
                        <button className="pd-gallery-nav pd-gallery-nav--prev" type="button"
                          onClick={() => setActiveImageIdx(p => p === 0 ? images.length - 1 : p - 1)}
                          aria-label="Previous photo">
                          <ChevronLeft size={20} />
                        </button>
                        <button className="pd-gallery-nav pd-gallery-nav--next" type="button"
                          onClick={() => setActiveImageIdx(p => p === images.length - 1 ? 0 : p + 1)}
                          aria-label="Next photo">
                          <ChevronRight size={20} />
                        </button>
                      </>
                    )}
                    <div className="pd-photo-count">{activeImageIdx + 1} / {images.length} photos</div>

                    {/* Thumbnails */}
                    {images.length > 1 && (
                      <div className="pd-thumbs">
                        {images.map((img: string, idx: number) => (
                          <button key={idx}
                            className={`pd-thumb${idx === activeImageIdx ? ' active' : ''}`}
                            onClick={() => setActiveImageIdx(idx)}
                            aria-label={`Photo ${idx + 1}`}>
                            <img src={img} alt={`Thumb ${idx + 1}`} />
                          </button>
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

              {/* Title / Spec block */}
              <div className="pd-summary-panel mb-4 pd-anim-2">
                <div className="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                  <h1 className="pd-title m-0">{property.title}</h1>
                  <div className="d-flex align-items-center gap-2">
                    <button type="button"
                      className={`pd-wish-btn${isWishlisted ? ' pd-wish-saved' : ''}${wishPulse ? ' pd-wish-pulse' : ''}`}
                      onClick={handleWishlistToggle}>
                      <Heart size={15} fill={isWishlisted ? '#ef4444' : 'none'} stroke={isWishlisted ? '#ef4444' : '#9ca3af'} />
                      <span>{isWishlisted ? 'Saved' : 'Save'}</span>
                    </button>
                    {property.views > 0 && (
                      <span className="pd-views-badge">
                        <Eye size={12} />
                        <span>{property.views} Views</span>
                      </span>
                    )}
                  </div>
                </div>

                <p className="pd-address-line mb-4">
                  <MapPin size={14} className="text-danger" />
                  <span>{property.address || property.locality}, {property.city_name}</span>
                </p>

                <div className="pd-price-row mb-4">
                  <span className="pd-price">{formatPrice(property.price)}</span>
                  <div className="d-flex flex-wrap gap-2 align-items-center">
                    {property.is_price_negotiable === 1 && (
                      <span className="pd-tag pd-tag--negotiable">Negotiable</span>
                    )}
                    <span className={`pd-tag ${property.listing_type === 'rent' ? 'pd-tag--rent' : 'pd-tag--sale'}`}>
                      For {property.listing_type === 'rent' ? 'Rent' : 'Sale'}
                    </span>
                    <span className="pd-tag pd-tag--type">
                      {property.property_type_label || property.property_type}
                    </span>
                  </div>
                </div>

                <div className="pd-stat-grid mb-3">
                  {property.rate_per_sqft && (
                    <div className="pd-stat">
                      <div className="pd-stat__label">Rate / sqft</div>
                      <div className="pd-stat__value">₹{Number(property.rate_per_sqft).toLocaleString('en-IN')}</div>
                    </div>
                  )}
                  {property.area_sqft && (
                    <div className="pd-stat">
                      <div className="pd-stat__label">Area</div>
                      <div className="pd-stat__value">{property.area_sqft} sq.ft</div>
                    </div>
                  )}
                  {property.bedrooms && (
                    <div className="pd-stat">
                      <div className="pd-stat__label">Bedrooms</div>
                      <div className="pd-stat__value">{property.bedrooms} BHK</div>
                    </div>
                  )}
                  {property.bathrooms && (
                    <div className="pd-stat">
                      <div className="pd-stat__label">Bathrooms</div>
                      <div className="pd-stat__value">{property.bathrooms} Baths</div>
                    </div>
                  )}
                </div>
              </div>

              {/* Property Facts & Specifications Section */}
              <div className="pd-specs-card mb-4 pd-anim-2">
                <h2 className="pd-section-title">
                  <Info size={17} />
                  <span>Facts & Specifications</span>
                </h2>
                <div className="pd-specs-grid">
                  {specItems.map((spec, index) => (
                    <div key={index} className="pd-spec-item">
                      <div className="pd-spec-icon">{spec.icon}</div>
                      <div className="pd-spec-info">
                        <span className="pd-spec-label">{spec.label}</span>
                        <span className="pd-spec-value">{spec.value || 'N/A'}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* About */}
              <div className="pd-section-card mb-4 pd-anim-3">
                <h2 className="pd-section-title">
                  <MapPin size={17} />
                  <span>About this Property</span>
                </h2>
                <div className="pd-description">
                  {property.description || 'No description listed by the owner.'}
                </div>
              </div>

              {/* Amenities */}
              {amenities.length > 0 && (
                <div className="pd-section-card mb-4 pd-anim-3">
                  <h2 className="pd-section-title">
                    <Star size={17} />
                    <span>Amenities</span>
                  </h2>
                  <div className="d-flex flex-wrap gap-2">
                    {amenities.map((item: string, idx: number) => (
                      <span key={idx} className="pd-amenity-pill">
                        <Check size={12} /> {item}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {/* Neighbourhood Guide */}
              {nearbyList.length > 0 && (
                <div className="pd-section-card mb-4 pd-anim-3">
                  <h2 className="pd-section-title">
                    <Compass size={17} />
                    <span>Neighbourhood & Connectivity</span>
                  </h2>
                  <div className="pd-nearby-grid">
                    {nearbyList.map((item: any, idx: number) => {
                      const cat = String(item.category || '').toLowerCase();
                      let catIcon = <MapPin size={14} className="text-secondary" />;
                      if (cat.includes('school') || cat.includes('college') || cat.includes('education')) {
                        catIcon = <Star size={14} className="text-warning" />;
                      } else if (cat.includes('hospital') || cat.includes('medical') || cat.includes('clinic')) {
                        catIcon = <Heart size={14} className="text-danger" fill="#fee2e2" />;
                      } else if (cat.includes('bus') || cat.includes('station') || cat.includes('train') || cat.includes('transit')) {
                        catIcon = <Compass size={14} className="text-primary" />;
                      }
                      return (
                        <div key={idx} className="pd-nearby-card">
                          <div className="pd-nearby-icon-wrap">{catIcon}</div>
                          <div className="pd-nearby-info">
                            <span className="pd-nearby-name">{item.name || item.title || 'Nearby Facility'}</span>
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
                  <div className="pd-video-frame-wrap border rounded-3 overflow-hidden shadow-sm">
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
                <div className="pd-map-frame-wrap border rounded-3 overflow-hidden shadow-sm mb-3">
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
                    <h3 className="pd-location-image-title mb-2 text-secondary small fw-bold uppercase">Location Map / Layout Image</h3>
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
                      <button className="pd-btn-primary w-100" onClick={() => setAuthModalOpen('login')}>
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
    </>
  );
}

/* ═══════════════════════════════════════════
   STYLES — scoped with pd- prefix
═══════════════════════════════════════════ */
const pageStyles = `
  /* ── Page entry animations ── */
  @keyframes pd-fadeUp {
    from { opacity: 0; transform: translateY(20px); }
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

  .pd-ready .pd-anim-1 { animation: pd-fadeUp 0.42s cubic-bezier(.22,.68,0,1.2) both; }
  .pd-ready .pd-anim-2 { animation: pd-fadeUp 0.42s cubic-bezier(.22,.68,0,1.2) 0.08s both; }
  .pd-ready .pd-anim-3 { animation: pd-fadeUp 0.42s cubic-bezier(.22,.68,0,1.2) 0.15s both; }
  .pd-ready .pd-anim-4 { animation: pd-fadeUp 0.42s cubic-bezier(.22,.68,0,1.2) 0.22s both; }

  /* ── Breadcrumb ── */
  .pd-breadcrumb-link { color: var(--nb-muted); transition: color .18s; }
  .pd-breadcrumb-link:hover { color: var(--nb-primary); }

  .pd-back-link {
    color: var(--nb-muted); text-decoration: none; font-size: 13.5px;
    transition: color .18s, gap .18s;
    padding: 0;
  }
  .pd-back-link:hover { color: var(--nb-primary); }

  /* ── Gallery ── */
  .pd-gallery-wrap { border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.1); }
  .pd-gallery-frame {
    position: relative; overflow: hidden;
    height: 480px;
    background: #111827;
  }
  .pd-gallery-img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform 0.6s cubic-bezier(.22,.68,0,1.2);
  }
  .pd-gallery-wrap:hover .pd-gallery-img { transform: scale(1.04); }
  .pd-gallery-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 50%);
    pointer-events: none;
  }

  .pd-gallery-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 42px; height: 42px; border-radius: 50%; border: none;
    background: rgba(255,255,255,0.25); backdrop-filter: blur(8px);
    color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .2s, transform .22s cubic-bezier(.22,.68,0,1.5);
    z-index: 2;
  }
  .pd-gallery-nav:hover { background: rgba(255,255,255,0.45); transform: translateY(-50%) scale(1.12); }
  .pd-gallery-nav--prev { left: 14px; }
  .pd-gallery-nav--next { right: 14px; }

  .pd-photo-count {
    position: absolute; bottom: 14px; right: 14px;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);
    color: #fff; font-size: 12px; font-weight: 600;
    padding: 4px 11px; border-radius: 20px;
  }

  .pd-featured-badge {
    position: absolute; top: 14px; left: 14px; z-index: 3;
    font-size: 11px; font-weight: 800; letter-spacing: .7px; text-transform: uppercase;
    background: linear-gradient(135deg, var(--nb-accent), var(--nb-accent-dark)); color: var(--nb-primary-dark);
    padding: 5px 12px; border-radius: 8px;
    box-shadow: 0 2px 8px rgba(242,178,3,.35);
  }

  .pd-img-placeholder {
    height: 480px; border-radius: 14px;
    background: #f7f8fa; color: #9ca3af;
    border: 2px dashed #e5e7eb;
  }

  /* Thumbnails */
  .pd-thumbs {
    display: flex; gap: 8px; padding: 10px 0 0;
    overflow-x: auto; scrollbar-width: thin;
  }
  .pd-thumbs::-webkit-scrollbar { height: 3px; }
  .pd-thumbs::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
  .pd-thumb {
    flex-shrink: 0; width: 72px; height: 52px; border-radius: 8px;
    overflow: hidden; border: 2.5px solid transparent; cursor: pointer; padding: 0;
    transition: border-color .18s, transform .18s;
  }
  .pd-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .pd-thumb.active { border-color: var(--nb-primary); }
  .pd-thumb:hover:not(.active) { transform: scale(1.06); }

  /* ── Summary panel ── */
  .pd-summary-panel {
    background: #fff; border-radius: 14px; padding: 1.75rem;
    box-shadow: 0 4px 20px rgba(11, 44, 86, 0.05);
    border: 1px solid var(--nb-card-border);
  }

  .pd-title { font-size: 1.8rem; font-weight: 800; color: var(--nb-secondary); line-height: 1.25; }

  .pd-wish-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; padding: 8px 16px;
    border-radius: 30px; border: 1.5px solid var(--nb-card-border);
    background: #ffffff; color: var(--nb-muted); cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
  }
  .pd-wish-btn:hover { background: #fff5f5; color: #ef4444; border-color: #fca5a5; transform: scale(1.04); }
  .pd-wish-btn.pd-wish-saved { background: #fff0f0; color: #dc2626; border-color: #fca5a5; }
  .pd-wish-btn.pd-wish-saved svg { color: #ef4444; }
  .pd-wish-btn.pd-wish-saved:hover { background: #fee2e2; border-color: #f87171; }
  .pd-wish-btn.pd-wish-pulse svg { animation: pd-heartPop 0.38s ease; }

  .pd-views-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; color: var(--nb-muted);
    background: var(--nb-mint); border: 1px solid var(--nb-card-border);
    padding: 6px 12px; border-radius: 20px;
  }

  .pd-address-line {
    display: flex; align-items: center; gap: 5px;
    font-size: 14px; color: var(--nb-muted); margin: 0;
  }

  .pd-price-row { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; }
  .pd-price { font-size: 2.25rem; font-weight: 800; color: var(--nb-primary); letter-spacing: -1px; line-height: 1; }

  .pd-tag {
    font-size: 11.5px; font-weight: 700; letter-spacing: .3px;
    padding: 5px 14px; border-radius: 20px;
    text-transform: uppercase;
  }
  .pd-tag--rent       { background: #e8f5e9; color: #2e7d32; }
  .pd-tag--sale       { background: var(--nb-primary-soft); color: var(--nb-primary); }
  .pd-tag--type       { background: var(--nb-mint); color: var(--nb-secondary); }
  .pd-tag--negotiable { background: #fff8e1; color: var(--nb-accent-dark); border: 1px solid #ffe082; }

  /* Stats grid */
  .pd-stat-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px;
  }
  .pd-stat {
    background: var(--nb-primary-soft); border-radius: 10px; padding: 12px 14px;
    border: 1px solid rgba(11, 44, 86, 0.1);
    transition: transform .2s, box-shadow .2s;
  }
  .pd-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11, 44, 86, 0.08); }
  .pd-stat__label { font-size: 11px; color: var(--nb-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px; }
  .pd-stat__value { font-size: 1.1rem; font-weight: 800; color: var(--nb-primary); }

  /* ── Specs card ── */
  .pd-specs-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.75rem;
    box-shadow: 0 4px 20px rgba(11, 44, 86, 0.05);
    border: 1px solid var(--nb-card-border);
  }
  .pd-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
  }
  .pd-spec-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--nb-light-bg);
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    transition: all 0.2s ease;
  }
  .pd-spec-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(11, 44, 86, 0.05);
    border-color: #cbd5e1;
  }
  .pd-spec-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--nb-primary-soft);
    color: var(--nb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
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
    background: #fff; border-radius: 14px; padding: 1.75rem;
    box-shadow: 0 4px 20px rgba(11, 44, 86, 0.05);
    border: 1px solid var(--nb-card-border);
    transition: box-shadow .25s;
  }
  .pd-section-card:hover { box-shadow: 0 6px 24px rgba(11,44,86,0.08); }

  .pd-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 1.1rem; font-weight: 700; color: var(--nb-secondary);
    margin: 0 0 1.25rem; padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--nb-primary-soft);
  }
  .pd-section-title svg { color: var(--nb-primary); }

  .pd-description { font-size: 14.5px; color: var(--nb-text); line-height: 1.8; white-space: pre-line; }

  /* Amenity pills */
  .pd-amenity-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12.5px; font-weight: 600; color: #2e7d32;
    background: #e8f5e9; border: 1px solid #c8e6c9;
    padding: 6px 14px; border-radius: 20px;
    transition: transform .18s, box-shadow .18s;
  }
  .pd-amenity-pill:hover { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(46,125,50,.15); }
  .pd-amenity-pill svg { color: #2e7d32; }

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
    transition: all 0.2s ease;
  }
  .pd-nearby-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(11, 44, 86, 0.05);
    border-color: #cbd5e1;
  }
  .pd-nearby-icon-wrap {
    width: 32px;
    height: 32px;
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

  /* Video */
  .pd-video-frame-wrap { border-radius: 12px !important; overflow: hidden; background: #000; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
  .pd-video-frame { border-radius: 0 !important; display: block; border: none !important; }

  /* Map and Location */
  .pd-map-frame-wrap { border-radius: 12px !important; overflow: hidden; }
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

  /* ── Enquiry sidebar card ── */
  .pd-enquiry-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(11, 44, 86, 0.12), 0 0 0 1px rgba(11, 44, 86, 0.04);
    overflow: hidden;
  }
  .pd-enquiry-head {
    background: linear-gradient(135deg, var(--nb-primary) 0%, var(--nb-primary-dark) 100%);
    padding: 1.5rem;
    border-bottom: 2px solid var(--nb-accent);
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