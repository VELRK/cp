'use client';
import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import api from '../lib/api';
import { ArrowLeft, Save, Upload, X, ShieldAlert, ChevronLeft, ChevronRight } from 'lucide-react';

interface City {
  id: number;
  name: string;
  state: string;
}

interface PropertyFormProps {
  initialData?: any;
  isEdit?: boolean;
}

const AMENITIES_LIST = [
  'Parking',
  'Lift',
  'Security',
  'Power Backup',
  'Gym',
  'Swimming Pool',
  'Club House',
  'Playground',
  'Water Supply',
  'Gated Community',
];

const PROPERTY_TYPES = [
  { val: 'apartment', label: 'Apartment / Flat' },
  { val: 'house', label: 'Independent House' },
  { val: 'villa', label: 'Villa / Duplex' },
  { val: 'plot', label: 'Plot / Land' },
  { val: 'commercial', label: 'Commercial Space' },
  { val: 'office', label: 'Office Space' },
  { val: 'retail', label: 'Retail / Shop' },
  { val: 'warehouse', label: 'Warehouse / Godown' },
  { val: 'farmhouse', label: 'Farmhouse' },
  { val: 'pg', label: 'PG Accommodation' },
  { val: 'shared_flat', label: 'Shared Flat' },
  { val: 'serviced_apartment', label: 'Serviced Apartment' },
];

const PropertyForm: React.FC<PropertyFormProps> = ({ initialData, isEdit = false }) => {
  const router = useRouter();

  // Wizard step state
  const [step, setStep] = useState(1);

  // Form states
  const [title, setTitle] = useState(initialData?.title || '');
  const [propertyType, setPropertyType] = useState(initialData?.property_type || 'apartment');
  const [listingType, setListingType] = useState<'rent' | 'sale'>(initialData?.listing_type || 'sale');
  const [price, setPrice] = useState(initialData?.price || '');
  const [isPriceNegotiable, setIsPriceNegotiable] = useState(initialData?.is_price_negotiable === 1);
  const [bedrooms, setBedrooms] = useState(initialData?.bedrooms || '');
  const [bathrooms, setBathrooms] = useState(initialData?.bathrooms || '');
  const [areaSqft, setAreaSqft] = useState(initialData?.area_sqft || '');
  const [ratePerSqft, setRatePerSqft] = useState(initialData?.rate_per_sqft || '');
  const [address, setAddress] = useState(initialData?.address || '');
  const [locality, setLocality] = useState(initialData?.locality || '');
  const [cityId, setCityId] = useState(initialData?.city_id || '');
  const [description, setDescription] = useState(initialData?.description || '');
  const [location, setLocation] = useState(initialData?.location || '');
  const [videoUrl, setVideoUrl] = useState(initialData?.video_url || '');
  const [availableFrom, setAvailableFrom] = useState(initialData?.available_from ? initialData.available_from.substring(0, 10) : '');
  const [plotLength, setPlotLength] = useState(initialData?.plot_length_ft || '');
  const [plotWidth, setPlotWidth] = useState(initialData?.plot_width_ft || '');
  const [hasBoundaryWall, setHasBoundaryWall] = useState<string>(initialData?.has_boundary_wall !== null && initialData?.has_boundary_wall !== undefined ? initialData.has_boundary_wall.toString() : '');

  // Amenities checklist
  const [selectedAmenities, setSelectedAmenities] = useState<string[]>(() => {
    if (initialData?.amenities) {
      return Array.isArray(initialData.amenities) ? initialData.amenities : [];
    }
    return [];
  });

  // Photo uploads
  const [newImages, setNewImages] = useState<File[]>([]);
  const [existingImages, setExistingImages] = useState<string[]>(initialData?.images || []);
  const [removedImages, setRemovedImages] = useState<string[]>([]);

  // DB Cities
  const [cities, setCities] = useState<City[]>([]);

  // Status indicators
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    // Fetch active cities
    api.get('/api/nb/cities')
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
          if (!isEdit && res.data.cities.length > 0) {
            setCityId(res.data.cities[0].id.toString());
          }
        }
      })
      .catch((err) => console.error('Error fetching cities', err));
  }, [isEdit]);

  const handleAmenityChange = (amenity: string) => {
    if (selectedAmenities.includes(amenity)) {
      setSelectedAmenities(selectedAmenities.filter((a) => a !== amenity));
    } else {
      setSelectedAmenities([...selectedAmenities, amenity]);
    }
  };

  const handleImageFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const filesArr = Array.from(e.target.files);
      setNewImages([...newImages, ...filesArr].slice(0, 10)); // max 10 images total
    }
  };

  const removeNewImage = (idx: number) => {
    setNewImages(newImages.filter((_, i) => i !== idx));
  };

  const removeExistingImage = (path: string) => {
    setExistingImages(existingImages.filter((img) => img !== path));
    setRemovedImages([...removedImages, path]);
  };

  const showPlotFields = propertyType === 'plot' || propertyType === 'others';

  const validateStep = (currentStep: number): boolean => {
    if (currentStep === 1) {
      if (!title.trim()) {
        setErrorMsg('Please enter a descriptive listing title.');
        return false;
      }
      if (!propertyType) {
        setErrorMsg('Please select a property type.');
        return false;
      }
    } else if (currentStep === 2) {
      if (!price) {
        setErrorMsg('Please enter the price or rent.');
        return false;
      }
      if (Number(price) <= 0) {
        setErrorMsg('Please enter a valid price greater than zero.');
        return false;
      }
    } else if (currentStep === 3) {
      if (!cityId) {
        setErrorMsg('Please select a city.');
        return false;
      }
      if (!locality.trim()) {
        setErrorMsg('Please specify the locality/area.');
        return false;
      }
      if (!address.trim()) {
        setErrorMsg('Please specify the detailed address.');
        return false;
      }
    }
    setErrorMsg(null);
    return true;
  };

  const nextStep = () => {
    if (validateStep(step)) {
      setStep((prev) => prev + 1);
    }
  };

  const prevStep = () => {
    setErrorMsg(null);
    setStep((prev) => prev - 1);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateStep(3)) {
      setStep(3);
      return;
    }
    setErrorMsg(null);
    setLoading(true);

    try {
      const formData = new FormData();
      if (isEdit && initialData?.id) {
        formData.append('property_id', initialData.id.toString());
      }
      formData.append('title', title);
      formData.append('property_type', propertyType);
      formData.append('listing_type', listingType);
      formData.append('price', price.toString());
      formData.append('is_price_negotiable', isPriceNegotiable ? '1' : '0');
      if (bedrooms) formData.append('bedrooms', bedrooms.toString());
      if (bathrooms) formData.append('bathrooms', bathrooms.toString());
      if (areaSqft) formData.append('area_sqft', areaSqft.toString());
      if (ratePerSqft) formData.append('rate_per_sqft', ratePerSqft.toString());
      formData.append('address', address);
      formData.append('locality', locality);
      formData.append('city_id', cityId.toString());
      formData.append('description', description);
      formData.append('location', location);
      formData.append('video_url', videoUrl);
      if (availableFrom) formData.append('available_from', availableFrom);
      if (plotLength) formData.append('plot_length_ft', plotLength.toString());
      if (plotWidth) formData.append('plot_width_ft', plotWidth.toString());
      if (hasBoundaryWall !== '') formData.append('has_boundary_wall', hasBoundaryWall);

      // Append amenities array
      selectedAmenities.forEach((amenity) => {
        formData.append('amenities[]', amenity);
      });

      // Append new image files
      newImages.forEach((file) => {
        formData.append('images[]', file);
      });

      // Append existing and removed image paths for edits
      if (isEdit) {
        existingImages.forEach((path) => {
          formData.append('existing_paths[]', path);
        });
        removedImages.forEach((path) => {
          formData.append('remove_existing[]', path);
        });
        formData.append('image_action', 'replace');
      }

      const response = await api.post('/api/property/save', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data?.success) {
        router.push('/owner/listings');
      } else {
        setErrorMsg(response.data?.message || 'Could not save listing.');
      }
    } catch (err: any) {
      setErrorMsg(err.response?.data?.message || 'Error occurred while saving listing.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card border-0 shadow bg-white p-4 rounded-4" style={{ border: '1px solid rgba(0,0,0,0.05)' }}>
      {/* 4-Step Classic Wizard Header */}
      <div className="mb-4">
        <div className="d-flex justify-content-between align-items-center mb-3">
          {[
            { id: 1, label: 'Core Info' },
            { id: 2, label: 'Pricing & Specs' },
            { id: 3, label: 'Location' },
            { id: 4, label: 'Media & Description' },
          ].map((s) => (
            <div key={s.id} className="text-center flex-grow-1" style={{ position: 'relative' }}>
              <div
                className={`mx-auto d-flex align-items-center justify-content-center rounded-circle border fw-bold`}
                style={{
                  width: '36px',
                  height: '36px',
                  fontSize: '0.95rem',
                  backgroundColor: step === s.id ? 'var(--nb-primary)' : step > s.id ? '#10b981' : '#fff',
                  color: step >= s.id ? '#fff' : '#6b7280',
                  borderColor: step === s.id ? 'var(--nb-primary)' : step > s.id ? '#10b981' : '#d1d5db',
                  boxShadow: step === s.id ? '0 0 10px rgba(11, 44, 86, 0.2)' : 'none',
                  transition: 'all 0.3s ease',
                }}
              >
                {step > s.id ? '✓' : s.id}
              </div>
              <span
                className={`d-none d-sm-block mt-2 small ${step === s.id ? 'fw-bold text-dark' : 'text-muted'}`}
                style={{ fontSize: '0.75rem' }}
              >
                {s.label}
              </span>
            </div>
          ))}
        </div>
        {/* Progress bar line */}
        <div className="progress" style={{ height: '4px' }}>
          <div
            className="progress-bar bg-success"
            role="progressbar"
            style={{ width: `${((step - 1) / 3) * 100}%`, transition: 'width 0.4s' }}
            aria-valuenow={step}
            aria-valuemin={1}
            aria-valuemax={4}
          />
        </div>
      </div>

      {errorMsg && (
        <div className="alert alert-danger d-flex align-items-center gap-2 small py-2.5 mb-4 rounded-3">
          <ShieldAlert size={16} />
          <span>{errorMsg}</span>
        </div>
      )}

      <form onSubmit={handleSubmit}>
        {/* STEP 1: Basic Info & Property Type */}
        {step === 1 && (
          <div className="fade-in-up">
            <h3 className="h6 fw-bold text-dark mb-3">Step 1: Core Property Details</h3>
            <div className="row g-3">
              <div className="col-12">
                <label className="form-label small fw-bold text-secondary">Listing Title</label>
                <input
                  type="text"
                  className="form-control"
                  placeholder="e.g. Premium 3 BHK Apartment near Avinashi Road"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  required
                />
                <div className="form-text text-muted small" style={{ fontSize: '0.75rem' }}>
                  Write a catchy title highlighting BHK status, property style, and closest landmark.
                </div>
              </div>

              <div className="col-12 mt-4">
                <label className="form-label small fw-bold text-secondary mb-2">Listing Purpose</label>
                <div className="d-flex gap-3">
                  <button
                    type="button"
                    className={`btn flex-grow-1 py-3 fw-bold rounded-4 border-2 ${listingType === 'sale' ? 'btn-primary border-primary text-white shadow-sm' : 'btn-outline-secondary border-light text-muted bg-light'}`}
                    onClick={() => setListingType('sale')}
                    style={{ transition: 'all 0.2s' }}
                  >
                    Sell
                  </button>
                  <button
                    type="button"
                    className={`btn flex-grow-1 py-3 fw-bold rounded-4 border-2 ${listingType === 'rent' ? 'btn-primary border-primary text-white shadow-sm' : 'btn-outline-secondary border-light text-muted bg-light'}`}
                    onClick={() => setListingType('rent')}
                    style={{ transition: 'all 0.2s' }}
                  >
                    Rent
                  </button>
                </div>
              </div>

              <div className="col-12 mt-4">
                <label className="form-label small fw-bold text-secondary mb-2">Property Category</label>
                <div className="row g-2">
                  {PROPERTY_TYPES.map((pt) => (
                    <div className="col-6 col-sm-4 col-md-3" key={pt.val}>
                      <button
                        type="button"
                        className={`btn w-100 h-100 py-3 small rounded-4 border ${propertyType === pt.val ? 'btn-primary border-primary text-white shadow-sm fw-bold' : 'btn-light border-light text-muted'}`}
                        onClick={() => setPropertyType(pt.val)}
                        style={{ fontSize: '0.85rem', transition: 'all 0.2s' }}
                      >
                        {pt.label}
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* STEP 2: Pricing & Specifications */}
        {step === 2 && (
          <div className="fade-in-up">
            <h3 className="h6 fw-bold text-dark mb-3">Step 2: Pricing & Specifications</h3>

            <div className="row g-3 mb-4">
              <div className="col-md-6">
                <label className="form-label small fw-bold text-secondary">Price (₹)</label>
                <input
                  type="number"
                  className="form-control"
                  placeholder={listingType === 'rent' ? 'Monthly Rent Amount' : 'Total Valuation / Outright Price'}
                  value={price}
                  onChange={(e) => setPrice(e.target.value)}
                  required
                />
              </div>

              <div className="col-md-6 d-flex align-items-center pt-4">
                <div className="form-check border p-2.5 rounded bg-light/50 w-100 ps-4">
                  <input
                    className="form-check-input ms-0 cursor-pointer"
                    type="checkbox"
                    id="isNegotiableCheck"
                    checked={isPriceNegotiable}
                    onChange={(e) => setIsPriceNegotiable(e.target.checked)}
                  />
                  <label className="form-check-label small fw-semibold text-secondary ms-2 cursor-pointer" htmlFor="isNegotiableCheck">
                    Price is Negotiable
                  </label>
                </div>
              </div>
            </div>

            <div className="row g-3 border-top pt-3">
              <span className="text-secondary small fw-bold d-block mb-1">Dimensions & Specifications</span>
              {!showPlotFields ? (
                <>
                  <div className="col-md-4">
                    <label className="form-label small text-secondary fw-semibold">Bedrooms (BHK)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={bedrooms}
                      onChange={(e) => setBedrooms(e.target.value)}
                      min="0"
                      placeholder="e.g. 2"
                    />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label small text-secondary fw-semibold">Bathrooms</label>
                    <input
                      type="number"
                      className="form-control"
                      value={bathrooms}
                      onChange={(e) => setBathrooms(e.target.value)}
                      min="0"
                      placeholder="e.g. 2"
                    />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label small text-secondary fw-semibold">Built-up Area (sqft)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={areaSqft}
                      onChange={(e) => setAreaSqft(e.target.value)}
                      min="0"
                      placeholder="e.g. 1200"
                    />
                  </div>
                </>
              ) : (
                <>
                  <div className="col-md-3">
                    <label className="form-label small text-secondary fw-semibold">Plot Length (ft)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={plotLength}
                      onChange={(e) => setPlotLength(e.target.value)}
                      min="0"
                    />
                  </div>
                  <div className="col-md-3">
                    <label className="form-label small text-secondary fw-semibold">Plot Width (ft)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={plotWidth}
                      onChange={(e) => setPlotWidth(e.target.value)}
                      min="0"
                    />
                  </div>
                  <div className="col-md-3">
                    <label className="form-label small text-secondary fw-semibold">Total Area (sqft)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={areaSqft}
                      onChange={(e) => setAreaSqft(e.target.value)}
                      min="0"
                    />
                  </div>
                  <div className="col-md-3">
                    <label className="form-label small text-secondary fw-semibold">Boundary Wall</label>
                    <select
                      className="form-select"
                      value={hasBoundaryWall}
                      onChange={(e) => setHasBoundaryWall(e.target.value)}
                    >
                      <option value="">Select Option</option>
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                </>
              )}

              <div className="col-md-6">
                <label className="form-label small text-secondary fw-semibold">Rate per sqft (optional)</label>
                <input
                  type="number"
                  className="form-control"
                  value={ratePerSqft}
                  onChange={(e) => setRatePerSqft(e.target.value)}
                  min="0"
                  placeholder="₹ / sqft"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label small text-secondary fw-semibold">Available From</label>
                <input
                  type="date"
                  className="form-control"
                  value={availableFrom}
                  onChange={(e) => setAvailableFrom(e.target.value)}
                />
              </div>
            </div>
          </div>
        )}

        {/* STEP 3: Location Details */}
        {step === 3 && (
          <div className="fade-in-up">
            <h3 className="h6 fw-bold text-dark mb-3">Step 3: Location & Address</h3>

            <div className="row g-3 mb-3">
              <div className="col-md-4">
                <label className="form-label small fw-bold text-secondary">City</label>
                <select
                  className="form-select"
                  value={cityId}
                  onChange={(e) => setCityId(e.target.value)}
                  required
                >
                  <option value="">Select City</option>
                  {cities.map((c) => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>

              <div className="col-md-8">
                <label className="form-label small fw-bold text-secondary">Locality / Area</label>
                <input
                  type="text"
                  className="form-control"
                  placeholder="e.g. Peelamedu, RS Puram, Saravanampatti"
                  value={locality}
                  onChange={(e) => setLocality(e.target.value)}
                  required
                />
              </div>

              <div className="col-12">
                <label className="form-label small fw-bold text-secondary">Detailed Address</label>
                <textarea
                  className="form-control"
                  rows={3}
                  placeholder="Door No., Building Name, Street / Road, Landmarks..."
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  required
                />
              </div>

              <div className="col-12">
                <label className="form-label small text-secondary fw-semibold">Google Map / Coordinates Link (optional)</label>
                <input
                  type="text"
                  className="form-control"
                  placeholder="https://maps.google.com/?q=..."
                  value={location}
                  onChange={(e) => setLocation(e.target.value)}
                />
              </div>
            </div>
          </div>
        )}

        {/* STEP 4: Media, Description & Amenities */}
        {step === 4 && (
          <div className="fade-in-up">
            <h3 className="h6 fw-bold text-dark mb-3">Step 4: Amenities, Details & Photos</h3>

            {/* Description */}
            <div className="mb-4">
              <label className="form-label small fw-bold text-secondary">Property Description</label>
              <textarea
                className="form-control"
                rows={4}
                placeholder="Give a detailed overview of your property, near facilities, rules, or features..."
                value={description}
                onChange={(e) => setDescription(e.target.value)}
              />
            </div>

            {/* Amenities Checkboxes */}
            {!showPlotFields && (
              <div className="mb-4 border-top pt-3">
                <label className="form-label small fw-bold text-secondary mb-2">Select Amenities</label>
                <div className="row g-2">
                  {AMENITIES_LIST.map((amenity) => (
                    <div key={amenity} className="col-6 col-sm-4">
                      <div className="form-check">
                        <input
                          className="form-check-input cursor-pointer"
                          type="checkbox"
                          id={`amenity-${amenity}`}
                          checked={selectedAmenities.includes(amenity)}
                          onChange={() => handleAmenityChange(amenity)}
                        />
                        <label className="form-check-label small cursor-pointer" htmlFor={`amenity-${amenity}`}>
                          {amenity}
                        </label>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Video Url */}
            <div className="mb-4 border-top pt-3">
              <label className="form-label small text-secondary fw-semibold">YouTube Tour Link (optional)</label>
              <input
                type="text"
                className="form-control"
                placeholder="e.g. https://www.youtube.com/watch?v=..."
                value={videoUrl}
                onChange={(e) => setVideoUrl(e.target.value)}
              />
            </div>

            {/* Photo Upload Panel */}
            <div className="border-top pt-3 mb-4">
              <label className="form-label small fw-bold text-secondary d-block">Upload Photos (Max 10)</label>
              <div className="d-inline-block mb-3">
                <label className="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 cursor-pointer rounded-pill">
                  <Upload size={14} />
                  <span>Choose Photos</span>
                  <input
                    type="file"
                    className="d-none"
                    accept="image/*"
                    multiple
                    onChange={handleImageFileChange}
                  />
                </label>
              </div>

              {/* Existing photos preview */}
              {existingImages.length > 0 && (
                <div className="mb-3">
                  <span className="small text-muted d-block mb-2">Existing Photos:</span>
                  <div className="d-flex flex-wrap gap-2">
                    {existingImages.map((path, idx) => (
                      <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                        <img
                          src={`/${path}`}
                          className="w-100 h-100 object-fit-cover"
                          alt="Existing preview"
                        />
                        <button
                          type="button"
                          className="btn btn-danger p-0 position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                          style={{ width: '18px', height: '18px', margin: '3px' }}
                          onClick={() => removeExistingImage(path)}
                        >
                          <X size={10} />
                        </button>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* New upload preview */}
              {newImages.length > 0 && (
                <div>
                  <span className="small text-muted d-block mb-2">New Uploads:</span>
                  <div className="d-flex flex-wrap gap-2">
                    {newImages.map((file, idx) => {
                      const previewUrl = URL.createObjectURL(file);
                      return (
                        <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                          <img
                            src={previewUrl}
                            className="w-100 h-100 object-fit-cover"
                            alt="Upload preview"
                          />
                          <button
                            type="button"
                            className="btn btn-danger p-0 position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                            style={{ width: '18px', height: '18px', margin: '3px' }}
                            onClick={() => removeNewImage(idx)}
                          >
                            <X size={10} />
                          </button>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Dynamic Wizard Action controls */}
        <div className="border-top pt-3 d-flex justify-content-between gap-3 mt-4">
          {step === 1 ? (
            <button
              type="button"
              className="btn btn-outline-secondary px-4 rounded-pill d-inline-flex align-items-center gap-1.5"
              onClick={() => router.push(isEdit ? '/owner/listings' : '/owner/dashboard')}
            >
              <ArrowLeft size={16} />
              <span>Cancel</span>
            </button>
          ) : (
            <button
              type="button"
              className="btn btn-outline-secondary px-4 rounded-pill d-inline-flex align-items-center gap-1.5"
              onClick={prevStep}
            >
              <ChevronLeft size={16} />
              <span>Back</span>
            </button>
          )}

          {step < 4 ? (
            <button
              type="button"
              className="btn btn-primary px-4 rounded-pill d-inline-flex align-items-center gap-1.5 text-white"
              onClick={nextStep}
            >
              <span>Continue</span>
              <ChevronRight size={16} />
            </button>
          ) : (
            <button
              type="submit"
              className="btn btn-danger px-5 rounded-pill text-dark fw-bold d-inline-flex align-items-center gap-1.5"
              disabled={loading}
            >
              <Save size={16} />
              <span>{loading ? 'Saving Listing...' : 'Submit Property'}</span>
            </button>
          )}
        </div>
      </form>
    </div>
  );
};

export default PropertyForm;
