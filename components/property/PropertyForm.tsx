'use client';
import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { getCities, saveProperty } from '@/lib/frontendApi';
import { formatApiErrorMessage } from '@/lib/api';
import { usePropertyTypeFilters } from '@/hooks/usePropertyTypeFilters';
import { useAuth } from '@/hooks/useAuth';
import {
  ArrowLeft,
  Save,
  Upload,
  X,
  ShieldAlert,
  ChevronLeft,
  ChevronRight,
  Check,
  CheckCircle2,
  Lock,
  User,
  CheckCircle,
  HelpCircle,
} from 'lucide-react';

interface City {
  id: number;
  name: string;
  state: string;
}

interface PropertyFormProps {
  initialData?: any;
  isEdit?: boolean;
  /** Owner dashboard add flow — skip public landing, require login */
  ownerMode?: boolean;
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

const COMMERCIAL_TYPE_SLUGS = new Set([
  'commercial',
  'office',
  'retail',
  'warehouse',
  'shop',
  'industrial',
  'godown',
]);

const RESIDENTIAL_TYPE_OPTIONS = [
  { val: 'apartment', label: 'Flat / Apartment' },
  { val: 'house', label: 'Independent House / Villa' },
  { val: 'plot', label: 'Plot / Land' },
  { val: 'farmhouse', label: 'Farm Land / Farmhouse' },
  { val: 'builder_floor', label: 'Independent / Builder Floor' },
  { val: 'studio', label: '1 RK / Studio Apartment' },
  { val: 'serviced_apartment', label: 'Serviced Apartment' },
  { val: 'others', label: 'Other' },
];

/** Check if property type is land / plot / farm */
const isLandOrPlotType = (type: string): boolean => {
  const t = (type || '').toLowerCase();
  return (
    t.includes('plot') ||
    t.includes('land') ||
    t.includes('farm') ||
    t === 'others'
  );
};

const PropertyForm: React.FC<PropertyFormProps> = ({ initialData, isEdit = false, ownerMode = false }) => {
  const router = useRouter();
  const { user, sendOtp, verifyOtp, resendOtp, registerUser, setAuthModalOpen } = useAuth();

  // Dual mode UI state: 'modern' | 'classic'
  const [uiMode, setUiMode] = useState<'modern' | 'classic'>('modern');

  // Modern Step 0 Landing options page state
  const [isLandingMode, setIsLandingMode] = useState(!isEdit);

  // Modern landing custom options - locked to Sell and Residential
  const [landingListingType] = useState<'sale'>('sale');
  const [landingCategory] = useState<'residential'>('residential');
  const [landingSubcategory, setLandingSubcategory] = useState(initialData?.property_type || 'apartment');
  const [landingPlotType, setLandingPlotType] = useState('Residential Land / Plot');
  const [landingPhone, setLandingPhone] = useState('');

  // Property position: 'new' | 'resale'
  const [propertyPosition, setPropertyPosition] = useState<'new' | 'resale'>(() => {
    if (initialData?.property_position) return initialData.property_position;
    if (initialData?.is_newly_launched === 1) return 'new';
    return 'new';
  });

  // Local popup states for Step 0
  const [showLoginModal, setShowLoginModal] = useState(false);
  const [loginModalStep, setLoginModalStep] = useState<'phone' | 'otp' | 'register'>('phone');
  const [loginOtp, setLoginOtp] = useState('');
  const [otpResendTimer, setOtpResendTimer] = useState(0);
  const [localRegName, setLocalRegName] = useState('');
  const [localRegCity, setLocalRegCity] = useState('');
  const [showDraftModal, setShowDraftModal] = useState(false);

  // Core Wizard step state: 3 Steps Overall
  const [step, setStep] = useState(1);
  const maxWizardSteps = 3;

  // Form states
  const [title, setTitle] = useState(initialData?.title || '');
  const {
    mainTypes,
    mainTypeSlug,
    subTypeSlug,
    subTypes,
    propertyType,
    setMainTypeSlug,
    setSubTypeSlug,
  } = usePropertyTypeFilters(initialData?.property_type || '');

  // Listing type locked to 'sale'
  const [listingType] = useState<'sale'>('sale');
  const [price, setPrice] = useState(initialData?.price || '');
  const [isPriceNegotiable, setIsPriceNegotiable] = useState(initialData?.is_price_negotiable === 1);
  const [bedrooms, setBedrooms] = useState(initialData?.bedrooms || '');
  const [bathrooms, setBathrooms] = useState(initialData?.bathrooms || '');
  const [areaSqft, setAreaSqft] = useState(initialData?.area_sqft || '');
  const [plotAreaSqft, setPlotAreaSqft] = useState(initialData?.plot_area_sqft || '');
  const [ratePerSqft, setRatePerSqft] = useState(initialData?.rate_per_sqft || '');
  const [address, setAddress] = useState(initialData?.address || '');
  const [locality, setLocality] = useState(initialData?.locality || '');
  const [cityId, setCityId] = useState(initialData?.city_id || '');
  const [description, setDescription] = useState(initialData?.description || '');
  const [location, setLocation] = useState(
    initialData?.map_url || initialData?.location || ''
  );
  const [videoUrl, setVideoUrl] = useState(initialData?.video_url || '');
  const [brochureFile, setBrochureFile] = useState<File | null>(null);
  const [removeBrochure, setRemoveBrochure] = useState(false);
  const [existingBrochureUrl, setExistingBrochureUrl] = useState(initialData?.brochure_url || initialData?.brochure_url_url || '');
  const [availableFrom, setAvailableFrom] = useState(initialData?.available_from ? initialData.available_from.substring(0, 10) : '');
  const [plotLength, setPlotLength] = useState(initialData?.plot_length_ft || '');
  const [plotWidth, setPlotWidth] = useState(initialData?.plot_width_ft || '');
  const [hasBoundaryWall, setHasBoundaryWall] = useState<string>(
    initialData?.has_boundary_wall !== null && initialData?.has_boundary_wall !== undefined
      ? initialData.has_boundary_wall.toString()
      : ''
  );

  // Amenities checklist
  const [selectedAmenities, setSelectedAmenities] = useState<string[]>(() => {
    if (initialData?.amenities) {
      return Array.isArray(initialData.amenities) ? initialData.amenities : [];
    }
    return [];
  });
  const [customAmenity, setCustomAmenity] = useState('');

  // Photo uploads
  const [newImages, setNewImages] = useState<File[]>([]);
  const [existingImages, setExistingImages] = useState<string[]>(initialData?.images || []);
  const [removedImages, setRemovedImages] = useState<string[]>([]);

  // DB Cities
  const [cities, setCities] = useState<City[]>([]);

  // Status indicators
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [modalErrorMsg, setModalErrorMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [modalLoading, setModalLoading] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [successPendingReview, setSuccessPendingReview] = useState(false);

  // Determine if land / plot type fields should be displayed
  const effectiveType = propertyType || landingSubcategory;
  const isLandOrPlot = isLandOrPlotType(effectiveType);

  // Fetch Cities on mount
  useEffect(() => {
    getCities()
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

  // Keep landing sub-type in sync with API main types
  useEffect(() => {
    if (mainTypes.length === 0) return;
    const options = mainTypes.filter((m) => {
      const commercial =
        COMMERCIAL_TYPE_SLUGS.has(m.slug) ||
        m.name.toLowerCase().includes('commercial') ||
        m.name.toLowerCase().includes('office') ||
        m.name.toLowerCase().includes('shop');
      return !commercial;
    });
    const list = options.length > 0 ? options : mainTypes;
    if (!list.some((m) => m.slug === landingSubcategory)) {
      setLandingSubcategory(list[0].slug);
    }
  }, [mainTypes, landingSubcategory]);

  // Draft recovery check on load
  useEffect(() => {
    if (!isEdit) {
      const savedDraft = localStorage.getItem('nb_draft_property');
      if (savedDraft) {
        try {
          const parsed = JSON.parse(savedDraft);
          if (parsed.title || parsed.price || parsed.locality) {
            setShowDraftModal(true);
          }
        } catch (e) {
          console.warn('Failed to parse draft property', e);
        }
      }
    }
  }, [isEdit]);

  // Sync draft to localStorage in modern mode
  useEffect(() => {
    if (!isEdit && !isLandingMode) {
      const draftData = {
        title,
        propertyType,
        listingType: 'sale',
        propertyPosition,
        price,
        isPriceNegotiable,
        bedrooms,
        bathrooms,
        areaSqft,
        plotAreaSqft,
        ratePerSqft,
        address,
        locality,
        cityId,
        description,
        location,
        videoUrl,
        availableFrom,
        plotLength,
        plotWidth,
        hasBoundaryWall,
        selectedAmenities,
        step,
      };
      if (title || price || locality || description) {
        localStorage.setItem('nb_draft_property', JSON.stringify(draftData));
      }
    }
  }, [
    title,
    propertyType,
    propertyPosition,
    price,
    isPriceNegotiable,
    bedrooms,
    bathrooms,
    areaSqft,
    plotAreaSqft,
    ratePerSqft,
    address,
    locality,
    cityId,
    description,
    location,
    videoUrl,
    availableFrom,
    plotLength,
    plotWidth,
    hasBoundaryWall,
    selectedAmenities,
    step,
    isLandingMode,
    isEdit,
  ]);

  const handleConfirmDraft = () => {
    const savedDraft = localStorage.getItem('nb_draft_property');
    if (savedDraft) {
      try {
        const parsed = JSON.parse(savedDraft);
        setTitle(parsed.title || '');
        setMainTypeSlug(parsed.propertyType || 'apartment');
        setSubTypeSlug('');
        setPropertyPosition(parsed.propertyPosition || 'new');
        setPrice(parsed.price || '');
        setIsPriceNegotiable(!!parsed.isPriceNegotiable);
        setBedrooms(parsed.bedrooms || '');
        setBathrooms(parsed.bathrooms || '');
        setAreaSqft(parsed.areaSqft || '');
        setPlotAreaSqft(parsed.plotAreaSqft || '');
        setRatePerSqft(parsed.ratePerSqft || '');
        setAddress(parsed.address || '');
        setLocality(parsed.locality || '');
        if (parsed.cityId) setCityId(parsed.cityId);
        setDescription(parsed.description || '');
        setLocation(parsed.map_url || parsed.location || '');
        setVideoUrl(parsed.videoUrl || '');
        setAvailableFrom(parsed.availableFrom || '');
        setPlotLength(parsed.plotLength || '');
        setPlotWidth(parsed.plotWidth || '');
        setHasBoundaryWall(parsed.hasBoundaryWall || '');
        setSelectedAmenities(parsed.selectedAmenities || []);
        setStep(Math.min(parsed.step || 1, maxWizardSteps));
        setIsLandingMode(false);
      } catch (e) {
        console.error('Error restoring draft', e);
      }
    }
    setShowDraftModal(false);
  };

  const handleCancelDraft = () => {
    localStorage.removeItem('nb_draft_property');
    setShowDraftModal(false);
  };

  const handleAmenityChange = (amenity: string) => {
    if (selectedAmenities.includes(amenity)) {
      setSelectedAmenities(selectedAmenities.filter((a) => a !== amenity));
    } else {
      setSelectedAmenities([...selectedAmenities, amenity]);
    }
  };

  const handleAddCustomAmenity = () => {
    const trimmed = customAmenity.trim();
    if (trimmed && !selectedAmenities.includes(trimmed)) {
      setSelectedAmenities([...selectedAmenities, trimmed]);
    }
    setCustomAmenity('');
  };

  const allAmenitiesToRender = Array.from(new Set([...AMENITIES_LIST, ...selectedAmenities]));

  const handleImageFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files) return;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxBytes = 15 * 1024 * 1024;
    const validationErrors: string[] = [];
    const validFiles: File[] = [];

    Array.from(e.target.files).forEach((file) => {
      if (!allowedTypes.includes(file.type)) {
        validationErrors.push(`${file.name}: only JPG, PNG, or WEBP allowed`);
        return;
      }
      if (file.size > maxBytes) {
        validationErrors.push(`${file.name}: max size is 15MB`);
        return;
      }
      validFiles.push(file);
    });

    if (validationErrors.length > 0) {
      setErrorMsg(validationErrors.join('\n'));
    } else {
      setErrorMsg(null);
    }
    if (validFiles.length > 0) {
      setNewImages([...newImages, ...validFiles].slice(0, 10));
    }
    e.target.value = '';
  };

  const removeNewImage = (idx: number) => {
    setNewImages(newImages.filter((_, i) => i !== idx));
  };

  const removeExistingImage = (path: string) => {
    setExistingImages(existingImages.filter((img) => img !== path));
    setRemovedImages([...removedImages, path]);
  };

  // Calculate property score (0 to 100)
  const calculatePropertyScore = () => {
    let score = 0;
    if (title.trim()) score += 15;
    if (propertyType) score += 15;
    if (price && Number(price) > 0) score += 20;
    if (cityId && locality.trim() && address.trim()) score += 20;
    if (newImages.length > 0 || existingImages.length > 0) score += 15;
    if (description.trim()) score += 15;
    return Math.min(100, score);
  };

  // 3-Step Wizard validation
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
    } else if (currentStep === 2) {
      if (!price) {
        setErrorMsg('Please enter the expected price.');
        return false;
      }
      if (Number(price) <= 0) {
        setErrorMsg('Please enter a valid price greater than zero.');
        return false;
      }
    }
    setErrorMsg(null);
    return true;
  };

  const nextStep = () => {
    if (validateStep(step)) {
      if (step < maxWizardSteps) {
        setStep((prev) => prev + 1);
      }
    }
  };

  const handleContinue = (e: React.MouseEvent<HTMLButtonElement>) => {
    e.preventDefault();
    e.stopPropagation();
    nextStep();
  };

  const prevStep = () => {
    setErrorMsg(null);
    if (step > 1) {
      setStep((prev) => prev - 1);
    }
  };

  // Step 0 Start Now trigger
  const handleLandingStartNow = (e: React.FormEvent) => {
    e.preventDefault();
    setMainTypeSlug(landingSubcategory);
    setSubTypeSlug('');
    const cityLabel = cities.find((c) => c.id.toString() === cityId)?.name || 'Coimbatore';
    const subLabel = getSubcategories().find((s) => s.val === landingSubcategory)?.label || landingSubcategory;
    if (!title.trim()) {
      setTitle(`${propertyPosition === 'new' ? 'New' : 'Resale'} ${subLabel} for Sale in ${cityLabel}`);
    }

    if (!user) {
      setLoginOtp('');
      setOtpResendTimer(0);
      setLoginModalStep('phone');
      setShowLoginModal(true);
      return;
    }

    // Authenticated, proceed directly to wizard step 1
    setIsLandingMode(false);
    setStep(1);
  };

  useEffect(() => {
    if (otpResendTimer <= 0) return;
    const t = setTimeout(() => setOtpResendTimer((s) => s - 1), 1000);
    return () => clearTimeout(t);
  }, [otpResendTimer]);

  // Local login handler
  const handleLocalLoginSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setModalErrorMsg(null);
    setModalLoading(true);

    try {
      if (loginModalStep === 'phone') {
        const phone = landingPhone.replace(/\D/g, '').slice(0, 10);
        if (phone.length !== 10) {
          setModalErrorMsg('Please enter a valid 10-digit phone number.');
          setModalLoading(false);
          return;
        }
        const result = await sendOtp(phone);
        if (!result.success) {
          setModalErrorMsg(result.message || 'Could not send OTP.');
          setModalLoading(false);
          return;
        }
        setLandingPhone(phone);
        setLoginOtp('');
        setLoginModalStep('otp');
        setOtpResendTimer(60);
      } else if (loginModalStep === 'otp') {
        const otp = loginOtp.replace(/\D/g, '').slice(0, 4);
        if (otp.length !== 4) {
          setModalErrorMsg('Enter the 4-digit OTP.');
          setModalLoading(false);
          return;
        }
        const result = await verifyOtp(landingPhone, otp);
        if (result.success) {
          setShowLoginModal(false);
          setIsLandingMode(false);
          setStep(1);
        } else {
          setModalErrorMsg(result.message || 'Invalid OTP.');
        }
      } else if (loginModalStep === 'register') {
        const fd = new FormData();
        fd.append('name', localRegName);
        fd.append('phone', landingPhone);
        fd.append('role', 'owner');
        fd.append('city_id', localRegCity || (cities[0]?.id.toString() || '1'));
        fd.append('accept_terms', '1');

        const result = await registerUser(fd);
        if (result.success) {
          setShowLoginModal(false);
          setIsLandingMode(false);
          setStep(1);
        } else {
          setModalErrorMsg(result.message || 'Registration failed.');
        }
      }
    } catch (err: any) {
      setModalErrorMsg(err.response?.data?.message || 'Authentication failed. Please check inputs.');
    } finally {
      setModalLoading(false);
    }
  };

  const handleFormKeyDown = (e: React.KeyboardEvent<HTMLFormElement>) => {
    if (e.key !== 'Enter') return;
    const target = e.target as HTMLElement;
    if (target.tagName === 'TEXTAREA') return;
    if (step >= maxWizardSteps) return;
    e.preventDefault();
    nextStep();
  };

  const handleSave = async () => {
    if (!user) {
      setErrorMsg('Please log in as a property owner to publish this listing.');
      setAuthModalOpen('login');
      return;
    }
    if (step !== maxWizardSteps) {
      setErrorMsg(`Please complete step ${step} and continue to step ${maxWizardSteps} before submitting.`);
      return;
    }
    for (let s = 1; s <= maxWizardSteps; s += 1) {
      if (!validateStep(s)) {
        setStep(s);
        return;
      }
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
      formData.append('listing_type', 'sale');
      formData.append('property_position', propertyPosition);
      formData.append('is_newly_launched', propertyPosition === 'new' ? '1' : '0');
      formData.append('price', price.toString());
      formData.append('is_price_negotiable', isPriceNegotiable ? '1' : '0');
      if (bedrooms && !isLandOrPlot) formData.append('bedrooms', bedrooms.toString());
      if (bathrooms && !isLandOrPlot) formData.append('bathrooms', bathrooms.toString());
      if (areaSqft) formData.append('area_sqft', areaSqft.toString());
      if (plotAreaSqft) formData.append('plot_area_sqft', plotAreaSqft.toString());
      if (ratePerSqft) formData.append('rate_per_sqft', ratePerSqft.toString());
      formData.append('address', address);
      formData.append('locality', locality);
      formData.append('city_id', cityId.toString());
      formData.append('description', description);
      formData.append('location', location);
      if (location.trim()) {
        formData.append('map_url', location.trim());
      }
      formData.append('video_url', videoUrl);
      if (brochureFile) {
        formData.append('brochure', brochureFile);
      }
      if (isEdit && removeBrochure) {
        formData.append('remove_brochure', '1');
      }
      if (availableFrom) formData.append('available_from', availableFrom);
      if (plotLength) formData.append('plot_length_ft', plotLength.toString());
      if (plotWidth) formData.append('plot_width_ft', plotWidth.toString());
      if (hasBoundaryWall !== '') formData.append('has_boundary_wall', hasBoundaryWall);

      selectedAmenities.forEach((amenity) => {
        formData.append('amenities[]', amenity);
      });

      newImages.forEach((file) => {
        formData.append('images[]', file);
      });

      if (isEdit) {
        existingImages.forEach((path) => {
          formData.append('existing_paths[]', path);
        });
        removedImages.forEach((path) => {
          formData.append('remove_existing[]', path);
        });
        formData.append('image_action', 'replace');
      }

      const response = await saveProperty(formData);

      if (response.data?.success) {
        localStorage.removeItem('nb_draft_property');
        setShowSuccess(true);
        setSuccessPendingReview(!!response.data?.pending_review);
        setTimeout(() => {
          router.push('/owner/listings');
        }, 2500);
      } else {
        setErrorMsg(formatApiErrorMessage(response.data, 'Could not save listing.'));
      }
    } catch (err: any) {
      setErrorMsg(formatApiErrorMessage(err.response?.data, 'Error occurred while saving listing.'));
    } finally {
      setLoading(false);
    }
  };

  const getSubcategories = () => {
    if (mainTypes.length > 0) {
      const filtered = mainTypes.filter((m) => {
        const commercial =
          COMMERCIAL_TYPE_SLUGS.has(m.slug) ||
          m.name.toLowerCase().includes('commercial') ||
          m.name.toLowerCase().includes('office') ||
          m.name.toLowerCase().includes('shop');
        return !commercial;
      });
      if (filtered.length > 0) {
        return filtered.map((m) => ({ val: m.slug, label: m.name }));
      }
    }
    return RESIDENTIAL_TYPE_OPTIONS;
  };

  const wizardStepsConfig = [
    { num: 1, label: 'Property & Location' },
    { num: 2, label: 'Pricing & Specs' },
    { num: 3, label: 'Photos & Details' },
  ];

  return (
    <div className="w-100 position-relative">
      {/* Success Animation Overlay */}
      {showSuccess && (
        <div className="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white" style={{ zIndex: 9999 }}>
          <div className="mb-4 animate-bounce">
            <div className="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style={{ width: '120px', height: '120px' }}>
              <CheckCircle size={70} className="text-success animate-pulse" />
            </div>
          </div>
          <h2 className="fw-bold text-dark mb-2 animate-fade-in-up">
            {successPendingReview
              ? (isEdit ? 'Updated — Pending Approval!' : 'Submitted for Verification!')
              : 'Property Added Successfully!'}
          </h2>
          <p className="text-muted animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
            {successPendingReview
              ? 'Our team will review your listing. It will go live on the website only after admin approval.'
              : 'Redirecting you to your listings...'}
          </p>
          <style>{`
            @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
            .animate-bounce { animation: bounce 2s infinite ease-in-out; }
            @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(25, 135, 84, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
            .animate-pulse { animation: pulse 2s infinite; borderRadius: 50%; }
            @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
            .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
          `}</style>
        </div>
      )}

      {ownerMode && (
        <div className="alert alert-info border-0 shadow-sm small mb-4">
          Listings from owners and agents are saved as <strong>Pending</strong>. An admin must approve before your property appears in search and on the public site.
        </div>
      )}

      {/* Header Dual-Mode Toggle Bar */}
      {!ownerMode && (
        <div className="nb-post-option-toggle-bar">
          <div className="btn-group border rounded-pill p-1 bg-white shadow-sm" role="group">
            <button
              type="button"
              className={`btn btn-sm rounded-pill px-3 py-1.5 fw-semibold ${uiMode === 'modern' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted'}`}
              onClick={() => {
                setUiMode('modern');
                setErrorMsg(null);
              }}
              style={{ fontSize: '0.8rem' }}
            >
              Modern 3-Step Form
            </button>
            <button
              type="button"
              className={`btn btn-sm rounded-pill px-3 py-1.5 fw-semibold ${uiMode === 'classic' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted'}`}
              onClick={() => {
                setUiMode('classic');
                setIsLandingMode(false);
                setStep(1);
                setErrorMsg(null);
              }}
              style={{ fontSize: '0.8rem' }}
            >
              Classic Form
            </button>
          </div>
        </div>
      )}

      {/* Step 0 Landing Option page (Modern Mode only) */}
      {uiMode === 'modern' && isLandingMode ? (
        <div className="nb-post-landing-container">
          {/* Left panel - visual benefits */}
          <div className="nb-post-landing-left">
            <h1 className="nb-post-landing-title">
              Sell Residential Property<br />
              <span>online faster</span> with CP
            </h1>
            <ul className="nb-post-landing-list">
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Advertise for FREE</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get unlimited genuine buyer enquiries</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get shortlisted verified buyers</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Quick 3-Step Listing Process</span>
              </li>
            </ul>

            {/* Laptop Vector Visual */}
            <div className="nb-post-landing-ill">
              <svg viewBox="0 0 500 300" className="w-100 h-auto" style={{ maxHeight: '240px' }} fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="500" height="300" rx="12" fill="#eff6ff" />
                <path d="M120 180h260v12H120z" fill="#94a3b8" />
                <path d="M130 80h240v100H130z" fill="#cbd5e1" />
                <rect x="140" y="90" width="220" height="80" rx="4" fill="#fff" />
                <path d="M160 110h80v8h-80zm0 15h120v6H160zm0 15h100v6H160z" fill="#cbd5e1" />
                <rect x="290" y="105" width="60" height="50" rx="4" fill="#3b82f6" />
                <path d="M305 130l8 8 16-16" stroke="#fff" strokeWidth="3" strokeLinecap="round" />
                <circle cx="250" cy="220" r="14" fill="#10b981" />
                <path d="M246 220l3 3 6-6" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" />
                <circle cx="150" cy="220" r="14" fill="#f59e0b" />
                <circle cx="350" cy="220" r="14" fill="#3b82f6" />
              </svg>
            </div>
            <p className="text-secondary small mt-3 text-center text-md-start">
              * Zero brokerage for direct owner-buyer sales
            </p>
          </div>

          {/* Right panel - form configuration */}
          <div className="nb-post-landing-right">
            <h2 className="nb-post-landing-card-title">Start posting your property, it&apos;s free</h2>
            <p className="nb-post-landing-card-subtitle">Add Basic Details</p>

            <form onSubmit={handleLandingStartNow}>
              {/* Sell only selection */}
              <div className="mb-4">
                <span className="nb-post-pill-group-title d-block">You&apos;re looking to ...</span>
                <div className="d-flex gap-2">
                  <button
                    type="button"
                    className="nb-post-pill active"
                  >
                    Sell
                  </button>
                </div>
              </div>

              {/* Residential only selector */}
              <div className="mb-4">
                <span className="nb-post-pill-group-title d-block">And it&apos;s a ...</span>
                <div className="d-flex gap-3 align-items-center mb-3">
                  <span className="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold" style={{ fontSize: '0.85rem' }}>
                    Residential
                  </span>
                </div>

                {/* Subcategory Pills */}
                <div className="nb-post-pills">
                  {getSubcategories().map((sub) => (
                    <button
                      key={sub.val}
                      type="button"
                      className={`nb-post-pill ${landingSubcategory === sub.val ? 'active' : ''}`}
                      onClick={() => setLandingSubcategory(sub.val)}
                    >
                      {sub.label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Property Position: New or Resale */}
              <div className="mb-4">
                <span className="nb-post-pill-group-title d-block">Property Position</span>
                <div className="d-flex gap-2">
                  <button
                    type="button"
                    className={`nb-post-pill ${propertyPosition === 'new' ? 'active' : ''}`}
                    onClick={() => setPropertyPosition('new')}
                  >
                    New Property
                  </button>
                  <button
                    type="button"
                    className={`nb-post-pill ${propertyPosition === 'resale' ? 'active' : ''}`}
                    onClick={() => setPropertyPosition('resale')}
                  >
                    Resale
                  </button>
                </div>
              </div>

              {/* Plot Land Type Specific Selector removed */}

              {!user && (
                <div className="mb-4 pt-2 border-top">
                  <label className="form-label small fw-bold text-secondary">Your contact details for the buyer to reach you</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light fw-bold text-muted">+91</span>
                    <input
                      type="tel"
                      className="form-control"
                      placeholder="Phone Number"
                      maxLength={10}
                      value={landingPhone}
                      onChange={(e) => setLandingPhone(e.target.value.replace(/\D/g, ''))}
                      required
                    />
                  </div>
                  <div className="form-text small text-muted">
                    Are you a registered user?{' '}
                    <button
                      type="button"
                      onClick={() => {
                        setLoginOtp('');
                        setLoginModalStep('phone');
                        setShowLoginModal(true);
                      }}
                      className="btn btn-link p-0 small text-decoration-none fw-semibold"
                    >
                      Login
                    </button>
                  </div>
                </div>
              )}

              <button
                type="submit"
                className="btn btn-primary w-100 py-2.5 rounded-3 fw-bold text-white shadow"
                style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
              >
                Start now
              </button>
            </form>
          </div>
        </div>
      ) : uiMode === 'modern' ? (
        /* Modern Wizard Screen (3 Steps) */
        <div className="nb-modern-wizard-grid">
          {/* Left Sidebar Checklist & Property score */}
          <div className="nb-modern-wizard-sidebar">
            <div className="nb-modern-wizard-steps-card">
              <ul className="nb-modern-wizard-steps-list">
                {wizardStepsConfig.map((s) => (
                  <li
                    key={s.num}
                    className={`nb-modern-wizard-step-item ${step === s.num ? 'active' : step > s.num ? 'done' : ''}`}
                    onClick={() => {
                      if (validateStep(step) || s.num < step) {
                        setStep(s.num);
                      }
                    }}
                  >
                    <div className="nb-modern-wizard-step-circle">
                      {step > s.num ? <Check size={14} /> : s.num}
                    </div>
                    <span className="nb-modern-wizard-step-label">{s.label}</span>
                  </li>
                ))}
              </ul>
            </div>

            {/* Property Score Widget */}
            <div className="nb-score-widget-card">
              <div className="nb-score-circle-wrap">
                <svg className="nb-score-circle-svg">
                  <circle className="nb-score-circle-bg" cx="30" cy="30" r="25" />
                  <circle
                    className="nb-score-circle-fg"
                    cx="30"
                    cy="30"
                    r="25"
                    strokeDasharray="157"
                    strokeDashoffset={157 - (157 * calculatePropertyScore()) / 100}
                  />
                </svg>
                <div className="nb-score-circle-text">{calculatePropertyScore()}%</div>
              </div>
              <div>
                <h3 className="nb-score-info-title">Property Score</h3>
                <p className="nb-score-info-desc">Better your property score, greater your buyer visibility</p>
              </div>
            </div>
          </div>

          {/* Right Main Panel form content */}
          <div className="nb-modern-wizard-form-panel">
            <div className="nb-wizard-welcome-hdr border-bottom pb-3">
              <h2 className="nb-wizard-welcome-title">
                Welcome back {user?.name || 'Owner'},
              </h2>
              <p className="nb-wizard-welcome-subtitle">
                Fill out property details in 3 quick steps
              </p>
            </div>

            {errorMsg && (
              <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-4 rounded-3">
                <ShieldAlert size={16} />
                <span style={{ whiteSpace: 'pre-line' }}>{errorMsg}</span>
              </div>
            )}

            <form onSubmit={(e) => e.preventDefault()} onKeyDown={handleFormKeyDown}>
              {/* STEP 1: Property & Location Details */}
              {step === 1 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 1: Property & Location Details</h3>
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
                      <div className="form-text small text-muted">
                        Highlight key landmarks, BHK or structure in your listing title.
                      </div>
                    </div>

                    {/* Property Position: New or Resale */}
                    <div className="col-12 mt-3">
                      <label className="form-label small fw-bold text-secondary d-block">Property Position</label>
                      <div className="d-flex gap-2">
                        <button
                          type="button"
                          className={`nb-post-pill ${propertyPosition === 'new' ? 'active' : ''}`}
                          onClick={() => setPropertyPosition('new')}
                        >
                          New Property
                        </button>
                        <button
                          type="button"
                          className={`nb-post-pill ${propertyPosition === 'resale' ? 'active' : ''}`}
                          onClick={() => setPropertyPosition('resale')}
                        >
                          Resale
                        </button>
                      </div>
                    </div>

                    {/* Property Types */}
                    <div className="col-md-6 mt-3">
                      <label className="form-label small fw-bold text-secondary">Property Type</label>
                      <select
                        className="form-select"
                        value={mainTypeSlug}
                        onChange={(e) => setMainTypeSlug(e.target.value)}
                        required
                      >
                        <option value="">Select Property Type</option>
                        {mainTypes.length > 0
                          ? mainTypes
                              .filter(
                                (m) =>
                                  !COMMERCIAL_TYPE_SLUGS.has(m.slug) &&
                                  !m.name.toLowerCase().includes('commercial') &&
                                  !m.name.toLowerCase().includes('office') &&
                                  !m.name.toLowerCase().includes('shop')
                              )
                              .map((m) => (
                                <option key={m.id} value={m.slug}>
                                  {m.name}
                                </option>
                              ))
                          : RESIDENTIAL_TYPE_OPTIONS.map((o) => (
                              <option key={o.val} value={o.val}>
                                {o.label}
                              </option>
                            ))}
                      </select>
                    </div>

                    {mainTypeSlug && subTypes.length > 0 && (
                      <div className="col-md-6 mt-3">
                        <label className="form-label small fw-bold text-secondary">Sub Property Type (optional)</label>
                        <select
                          className="form-select"
                          value={subTypeSlug}
                          onChange={(e) => setSubTypeSlug(e.target.value)}
                        >
                          <option value="">Select sub type</option>
                          {subTypes.map((s) => (
                            <option key={s.id} value={s.slug}>
                              {s.name}
                            </option>
                          ))}
                        </select>
                      </div>
                    )}

                    {/* Location fields */}
                    <div className="col-md-4 mt-3">
                      <label className="form-label small fw-bold text-secondary">City</label>
                      <select
                        className="form-select"
                        value={cityId}
                        onChange={(e) => setCityId(e.target.value)}
                        required
                      >
                        <option value="">Select City</option>
                        {cities.map((c) => (
                          <option key={c.id} value={c.id}>
                            {c.name}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div className="col-md-8 mt-3">
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

                    <div className="col-12 mt-3">
                      <label className="form-label small fw-bold text-secondary">Detailed Address</label>
                      <textarea
                        className="form-control"
                        rows={3}
                        placeholder="Door No., Flat No, Building Name, Street / Road Details"
                        value={address}
                        onChange={(e) => setAddress(e.target.value)}
                        required
                      />
                    </div>

                    <div className="col-12 mt-3">
                      <label className="form-label small text-secondary fw-semibold">Google Maps Location Link (optional)</label>
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

              {/* STEP 2: Pricing & Specifications */}
              {step === 2 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 2: Pricing & Specifications</h3>
                  <div className="row g-3 mb-4">
                    <div className="col-md-6">
                      <label className="form-label small fw-bold text-secondary">Expected Price (₹)</label>
                      <input
                        type="number"
                        className="form-control"
                        placeholder="Total Sale Price in ₹"
                        value={price}
                        onChange={(e) => setPrice(e.target.value)}
                        required
                      />
                    </div>

                    <div className="col-md-6 d-flex align-items-center pt-md-4">
                      <div className="form-check p-2.5 rounded bg-light border w-100 ps-4">
                        <input
                          className="form-check-input ms-0 cursor-pointer"
                          type="checkbox"
                          id="negotiableCheckModern"
                          checked={isPriceNegotiable}
                          onChange={(e) => setIsPriceNegotiable(e.target.checked)}
                        />
                        <label className="form-check-label small fw-semibold text-secondary ms-2 cursor-pointer" htmlFor="negotiableCheckModern">
                          Price is Negotiable
                        </label>
                      </div>
                    </div>

                    <div className="col-md-6">
                      <label className="form-label small text-secondary fw-semibold">Rate per sq.ft (₹ / sqft)</label>
                      <input
                        type="number"
                        className="form-control"
                        value={ratePerSqft}
                        onChange={(e) => setRatePerSqft(e.target.value)}
                        min="0"
                        placeholder="e.g. 4500"
                      />
                    </div>

                    <div className="col-md-6">
                      <label className="form-label small text-secondary fw-semibold">Possession Date</label>
                      <input
                        type="date"
                        className="form-control"
                        value={availableFrom}
                        onChange={(e) => setAvailableFrom(e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="border-top pt-3">
                    <span className="text-secondary small fw-bold d-block mb-3">
                      {isLandOrPlot ? 'Land & Plot Dimensions' : 'Apartment & Villa Specifications'}
                    </span>

                    {/* VILLA & APARTMENT FIELDS */}
                    {!isLandOrPlot ? (
                      <div className="row g-3">
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Bedrooms (BHK)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={bedrooms}
                            onChange={(e) => setBedrooms(e.target.value)}
                            min="0"
                            placeholder="e.g. 3"
                          />
                        </div>
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">No. of Bathrooms</label>
                          <input
                            type="number"
                            className="form-control"
                            value={bathrooms}
                            onChange={(e) => setBathrooms(e.target.value)}
                            min="0"
                            placeholder="e.g. 2"
                          />
                        </div>
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Built-up Area (sq.ft)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={areaSqft}
                            onChange={(e) => setAreaSqft(e.target.value)}
                            min="0"
                            placeholder="e.g. 1500"
                          />
                        </div>
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Plot Area (sq.ft)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={plotAreaSqft}
                            onChange={(e) => setPlotAreaSqft(e.target.value)}
                            min="0"
                            placeholder="e.g. 2400"
                          />
                        </div>
                      </div>
                    ) : (
                      /* FARM LAND & PLOT / LAND FIELDS (NO BEDROOMS, NO BATHROOMS) */
                      <div className="row g-3">
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Plot / Land Area (sq.ft)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={areaSqft}
                            onChange={(e) => {
                              setAreaSqft(e.target.value);
                              setPlotAreaSqft(e.target.value);
                            }}
                            min="0"
                            placeholder="Total sq.ft"
                          />
                        </div>
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Plot Length (ft)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={plotLength}
                            onChange={(e) => setPlotLength(e.target.value)}
                            min="0"
                            placeholder="Length in ft"
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
                            placeholder="Width in ft"
                          />
                        </div>
                        <div className="col-md-3">
                          <label className="form-label small text-secondary fw-semibold">Boundary Wall</label>
                          <select
                            className="form-select"
                            value={hasBoundaryWall}
                            onChange={(e) => setHasBoundaryWall(e.target.value)}
                          >
                            <option value="">Choose</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                          </select>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* STEP 3: Photos, Features & Description */}
              {step === 3 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 3: Photos, Features & Description</h3>
                  <div className="border p-3 rounded bg-light mb-4">
                    <label className="form-label small fw-bold text-secondary d-block">Upload Photos (Max 10)</label>
                    <label className="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 cursor-pointer rounded-pill bg-white px-3 py-2">
                      <Upload size={14} />
                      <span>Select Photos</span>
                      <input
                        type="file"
                        className="d-none"
                        accept="image/*"
                        multiple
                        onChange={handleImageFileChange}
                      />
                    </label>

                    {/* New photos preview */}
                    {newImages.length > 0 && (
                      <div className="d-flex flex-wrap gap-2 mt-3">
                        {newImages.map((file, idx) => {
                          const previewUrl = URL.createObjectURL(file);
                          return (
                            <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                              <img src={previewUrl} className="w-100 h-100 object-fit-cover" alt="Preview" />
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
                    )}

                    {/* Existing photos preview */}
                    {existingImages.length > 0 && (
                      <div className="mt-3">
                        <span className="small text-muted d-block mb-1">Existing:</span>
                        <div className="d-flex flex-wrap gap-2">
                          {existingImages.map((path, idx) => (
                            <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                              <img src={`/${path}`} className="w-100 h-100 object-fit-cover" alt="Existing" />
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
                  </div>

                  <div className="mb-3">
                    <label className="form-label small text-secondary fw-semibold">YouTube Tour Link (optional)</label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="e.g. https://www.youtube.com/watch?v=..."
                      value={videoUrl}
                      onChange={(e) => setVideoUrl(e.target.value)}
                    />
                  </div>

                  <div className="mb-4">
                    <label className="form-label small text-secondary fw-semibold">Brochure (optional)</label>
                    {existingBrochureUrl && !removeBrochure && (
                      <div className="small mb-2">
                        <a href={existingBrochureUrl} target="_blank" rel="noopener noreferrer">View current brochure</a>
                        <div className="form-check mt-1">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="removeBrochureModern"
                            checked={removeBrochure}
                            onChange={(e) => setRemoveBrochure(e.target.checked)}
                          />
                          <label className="form-check-label text-danger" htmlFor="removeBrochureModern">Remove brochure</label>
                        </div>
                      </div>
                    )}
                    <input
                      type="file"
                      className="form-control"
                      accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp"
                      onChange={(e) => setBrochureFile(e.target.files?.[0] || null)}
                    />
                  </div>

                  <div className="mb-4 border-top pt-3">
                    <label className="form-label small fw-bold text-secondary">Property Description</label>
                    <textarea
                      className="form-control"
                      rows={4}
                      placeholder="Detailed features, nearby landmarks, schools, hospital, road access..."
                      value={description}
                      onChange={(e) => setDescription(e.target.value)}
                    />
                  </div>

                  {/* Amenities (shown for residential houses/apartments) */}
                  {!isLandOrPlot && (
                    <div className="mb-4 border-top pt-3">
                      <label className="form-label small fw-bold text-secondary mb-2">Select Amenities</label>
                      <div className="row g-2 mb-3">
                        {allAmenitiesToRender.map((amenity) => (
                          <div key={amenity} className="col-6 col-sm-4">
                            <div className="form-check">
                              <input
                                className="form-check-input cursor-pointer"
                                type="checkbox"
                                id={`amenity-modern-${amenity}`}
                                checked={selectedAmenities.includes(amenity)}
                                onChange={() => handleAmenityChange(amenity)}
                              />
                              <label className="form-check-label small cursor-pointer" htmlFor={`amenity-modern-${amenity}`}>
                                {amenity}
                              </label>
                            </div>
                          </div>
                        ))}
                      </div>
                      <div className="d-flex gap-2 align-items-center">
                        <input
                          type="text"
                          className="form-control form-control-sm w-auto"
                          placeholder="Other amenity..."
                          value={customAmenity}
                          onChange={(e) => setCustomAmenity(e.target.value)}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              e.preventDefault();
                              handleAddCustomAmenity();
                            }
                          }}
                        />
                        <button
                          type="button"
                          className="btn btn-sm btn-outline-primary"
                          onClick={handleAddCustomAmenity}
                        >
                          Add
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* Wizard Navigation Controls */}
              <div className="border-top pt-3 d-flex justify-content-between gap-3 mt-4">
                {step === 1 ? (
                  <button
                    type="button"
                    className="btn btn-outline-secondary px-4 rounded-pill d-inline-flex align-items-center gap-1.5"
                    onClick={() => {
                      if (!isEdit) {
                        setIsLandingMode(true);
                      } else {
                        router.push('/owner/listings');
                      }
                    }}
                  >
                    <ArrowLeft size={16} />
                    <span>Back</span>
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

                {step < maxWizardSteps ? (
                  <button
                    type="button"
                    className="btn btn-primary px-4 rounded-pill d-inline-flex align-items-center gap-1.5 text-white"
                    onClick={handleContinue}
                    style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
                  >
                    <span>Continue to Step {step + 1}</span>
                    <ChevronRight size={16} />
                  </button>
                ) : (
                  <button
                    type="button"
                    className="btn btn-danger px-5 rounded-pill text-dark fw-bold d-inline-flex align-items-center gap-1.5"
                    disabled={loading}
                    onClick={handleSave}
                    style={{ background: 'var(--nb-accent)', borderColor: 'var(--nb-accent)' }}
                  >
                    <Save size={16} />
                    <span>{loading ? 'Saving Listing...' : isEdit ? 'Update Property' : 'Submit Property'}</span>
                  </button>
                )}
              </div>
            </form>
          </div>
        </div>
      ) : (
        /* Classic 3-step wizard form layout */
        <div className="card border-0 shadow bg-white p-4 rounded-4" style={{ border: '1px solid rgba(0,0,0,0.05)' }}>
          {/* 3-Step Classic Wizard Header */}
          <div className="mb-4">
            <div className="d-flex justify-content-between align-items-center mb-3">
              {wizardStepsConfig.map((s) => (
                <div key={s.num} className="text-center flex-grow-1" style={{ position: 'relative' }}>
                  <div
                    className="mx-auto d-flex align-items-center justify-content-center rounded-circle border fw-bold"
                    style={{
                      width: '36px',
                      height: '36px',
                      fontSize: '0.95rem',
                      backgroundColor: step === s.num ? 'var(--nb-primary)' : step > s.num ? '#10b981' : '#fff',
                      color: step >= s.num ? '#fff' : '#6b7280',
                      borderColor: step === s.num ? 'var(--nb-primary)' : step > s.num ? '#10b981' : '#d1d5db',
                      boxShadow: step === s.num ? '0 0 10px rgba(11, 44, 86, 0.2)' : 'none',
                      transition: 'all 0.3s ease',
                    }}
                  >
                    {step > s.num ? '✓' : s.num}
                  </div>
                  <span
                    className="d-none d-sm-block mt-2 small text-muted"
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
                style={{ width: `${((step - 1) / (maxWizardSteps - 1)) * 100}%`, transition: 'width 0.4s' }}
                aria-valuenow={step}
                aria-valuemin={1}
                aria-valuemax={maxWizardSteps}
              />
            </div>
          </div>

          {errorMsg && (
            <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-4 rounded-3">
              <ShieldAlert size={16} />
              <span style={{ whiteSpace: 'pre-line' }}>{errorMsg}</span>
            </div>
          )}

          <form onSubmit={(e) => e.preventDefault()} onKeyDown={handleFormKeyDown}>
            {/* STEP 1: Property & Location Details */}
            {step === 1 && (
              <div className="fade-in-up">
                <h3 className="h6 fw-bold text-dark mb-3">Step 1: Property & Location Details</h3>
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
                  </div>

                  <div className="col-12 mt-3">
                    <label className="form-label small fw-bold text-secondary d-block">Property Position</label>
                    <div className="d-flex gap-2">
                      <button
                        type="button"
                        className={`nb-post-pill ${propertyPosition === 'new' ? 'active' : ''}`}
                        onClick={() => setPropertyPosition('new')}
                      >
                        New Property
                      </button>
                      <button
                        type="button"
                        className={`nb-post-pill ${propertyPosition === 'resale' ? 'active' : ''}`}
                        onClick={() => setPropertyPosition('resale')}
                      >
                        Resale
                      </button>
                    </div>
                  </div>

                  <div className="col-12 mt-3">
                    <label className="form-label small fw-bold text-secondary mb-2">Property Category</label>
                    <div className="row g-2">
                      {getSubcategories().map((m) => (
                        <div className="col-6 col-sm-4 col-md-3" key={m.val}>
                          <button
                            type="button"
                            className={`btn w-100 h-100 py-2.5 small rounded-4 border ${mainTypeSlug === m.val ? 'btn-primary border-primary text-white shadow-sm fw-bold' : 'btn-light border-light text-muted'}`}
                            onClick={() => setMainTypeSlug(m.val)}
                            style={{ fontSize: '0.85rem' }}
                          >
                            {m.label}
                          </button>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="col-md-4 mt-3">
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

                  <div className="col-md-8 mt-3">
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

                  <div className="col-12 mt-3">
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

                  <div className="col-12 mt-3">
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

            {/* STEP 2: Pricing & Specifications */}
            {step === 2 && (
              <div className="fade-in-up">
                <h3 className="h6 fw-bold text-dark mb-3">Step 2: Pricing & Specifications</h3>

                <div className="row g-3 mb-4">
                  <div className="col-md-6">
                    <label className="form-label small fw-bold text-secondary">Expected Price (₹)</label>
                    <input
                      type="number"
                      className="form-control"
                      placeholder="Total Outright Price in ₹"
                      value={price}
                      onChange={(e) => setPrice(e.target.value)}
                      required
                    />
                  </div>

                  <div className="col-md-6 d-flex align-items-center pt-md-4">
                    <div className="form-check border p-2.5 rounded bg-light/50 w-100 ps-4">
                      <input
                        className="form-check-input ms-0 cursor-pointer"
                        type="checkbox"
                        id="isNegotiableCheckClassic"
                        checked={isPriceNegotiable}
                        onChange={(e) => setIsPriceNegotiable(e.target.checked)}
                      />
                      <label className="form-check-label small fw-semibold text-secondary ms-2 cursor-pointer" htmlFor="isNegotiableCheckClassic">
                        Price is Negotiable
                      </label>
                    </div>
                  </div>

                  <div className="col-md-6">
                    <label className="form-label small text-secondary fw-semibold">Rate per sq.ft (₹ / sqft)</label>
                    <input
                      type="number"
                      className="form-control"
                      value={ratePerSqft}
                      onChange={(e) => setRatePerSqft(e.target.value)}
                      min="0"
                      placeholder="e.g. 4500"
                    />
                  </div>

                  <div className="col-md-6">
                    <label className="form-label small text-secondary fw-semibold">Possession Date</label>
                    <input
                      type="date"
                      className="form-control"
                      value={availableFrom}
                      onChange={(e) => setAvailableFrom(e.target.value)}
                    />
                  </div>
                </div>

                <div className="border-top pt-3">
                  <span className="text-secondary small fw-bold d-block mb-3">
                    {isLandOrPlot ? 'Land & Plot Dimensions' : 'Apartment & Villa Specifications'}
                  </span>

                  {!isLandOrPlot ? (
                    <div className="row g-3">
                      <div className="col-md-3">
                        <label className="form-label small text-secondary fw-semibold">Bedrooms (BHK)</label>
                        <input
                          type="number"
                          className="form-control"
                          value={bedrooms}
                          onChange={(e) => setBedrooms(e.target.value)}
                          min="0"
                          placeholder="e.g. 3"
                        />
                      </div>
                      <div className="col-md-3">
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
                      <div className="col-md-3">
                        <label className="form-label small text-secondary fw-semibold">Built-up Area (sq.ft)</label>
                        <input
                          type="number"
                          className="form-control"
                          value={areaSqft}
                          onChange={(e) => setAreaSqft(e.target.value)}
                          min="0"
                          placeholder="e.g. 1500"
                        />
                      </div>
                      <div className="col-md-3">
                        <label className="form-label small text-secondary fw-semibold">Plot Area (sq.ft)</label>
                        <input
                          type="number"
                          className="form-control"
                          value={plotAreaSqft}
                          onChange={(e) => setPlotAreaSqft(e.target.value)}
                          min="0"
                          placeholder="e.g. 2400"
                        />
                      </div>
                    </div>
                  ) : (
                    <div className="row g-3">
                      <div className="col-md-3">
                        <label className="form-label small text-secondary fw-semibold">Plot / Land Area (sq.ft)</label>
                        <input
                          type="number"
                          className="form-control"
                          value={areaSqft}
                          onChange={(e) => {
                            setAreaSqft(e.target.value);
                            setPlotAreaSqft(e.target.value);
                          }}
                          min="0"
                          placeholder="Total sq.ft"
                        />
                      </div>
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
                        <label className="form-label small text-secondary fw-semibold">Boundary Wall</label>
                        <select
                          className="form-select"
                          value={hasBoundaryWall}
                          onChange={(e) => setHasBoundaryWall(e.target.value)}
                        >
                          <option value="">Select</option>
                          <option value="1">Yes</option>
                          <option value="0">No</option>
                        </select>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}

            {/* STEP 3: Photos, Amenities & Description */}
            {step === 3 && (
              <div className="fade-in-up">
                <h3 className="h6 fw-bold text-dark mb-3">Step 3: Photos, Features & Description</h3>

                {/* Photo Upload Panel */}
                <div className="border p-3 rounded bg-light mb-4">
                  <label className="form-label small fw-bold text-secondary d-block">Upload Photos (Max 10)</label>
                  <label className="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 cursor-pointer rounded-pill bg-white px-3 py-2">
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

                  {/* Existing preview */}
                  {existingImages.length > 0 && (
                    <div className="mt-3">
                      <span className="small text-muted d-block mb-1">Existing:</span>
                      <div className="d-flex flex-wrap gap-2">
                        {existingImages.map((path, idx) => (
                          <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                            <img src={`/${path}`} className="w-100 h-100 object-fit-cover" alt="Existing" />
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

                  {/* New preview */}
                  {newImages.length > 0 && (
                    <div className="mt-3">
                      <span className="small text-muted d-block mb-1">New:</span>
                      <div className="d-flex flex-wrap gap-2">
                        {newImages.map((file, idx) => {
                          const previewUrl = URL.createObjectURL(file);
                          return (
                            <div key={idx} className="position-relative border rounded overflow-hidden" style={{ width: '80px', height: '80px' }}>
                              <img src={previewUrl} className="w-100 h-100 object-fit-cover" alt="New" />
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

                <div className="mb-3">
                  <label className="form-label small text-secondary fw-semibold">YouTube Tour Link (optional)</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="e.g. https://www.youtube.com/watch?v=..."
                    value={videoUrl}
                    onChange={(e) => setVideoUrl(e.target.value)}
                  />
                </div>

                <div className="mb-4">
                  <label className="form-label small text-secondary fw-semibold">Brochure (optional)</label>
                  {existingBrochureUrl && !removeBrochure && (
                    <div className="small mb-2">
                      <a href={existingBrochureUrl} target="_blank" rel="noopener noreferrer">View current brochure</a>
                      <div className="form-check mt-1">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id="removeBrochureClassic"
                          checked={removeBrochure}
                          onChange={(e) => setRemoveBrochure(e.target.checked)}
                        />
                        <label className="form-check-label text-danger" htmlFor="removeBrochureClassic">Remove brochure</label>
                      </div>
                    </div>
                  )}
                  <input
                    type="file"
                    className="form-control"
                    accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp"
                    onChange={(e) => setBrochureFile(e.target.files?.[0] || null)}
                  />
                </div>

                {/* Description */}
                <div className="mb-4 border-top pt-3">
                  <label className="form-label small fw-bold text-secondary">Property Description</label>
                  <textarea
                    className="form-control"
                    rows={4}
                    placeholder="Overview of your property, features, nearby places..."
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                  />
                </div>

                {/* Amenities Checkboxes */}
                {!isLandOrPlot && (
                  <div className="mb-4 border-top pt-3">
                    <label className="form-label small fw-bold text-secondary mb-2">Select Amenities</label>
                    <div className="row g-2 mb-3">
                      {allAmenitiesToRender.map((amenity) => (
                        <div key={amenity} className="col-6 col-sm-4">
                          <div className="form-check">
                            <input
                              className="form-check-input cursor-pointer"
                              type="checkbox"
                              id={`amenity-classic-${amenity}`}
                              checked={selectedAmenities.includes(amenity)}
                              onChange={() => handleAmenityChange(amenity)}
                            />
                            <label className="form-check-label small cursor-pointer" htmlFor={`amenity-classic-${amenity}`}>
                              {amenity}
                            </label>
                          </div>
                        </div>
                      ))}
                    </div>
                    <div className="d-flex gap-2 align-items-center">
                      <input
                        type="text"
                        className="form-control form-control-sm w-auto"
                        placeholder="Other amenity..."
                        value={customAmenity}
                        onChange={(e) => setCustomAmenity(e.target.value)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter') {
                            e.preventDefault();
                            handleAddCustomAmenity();
                          }
                        }}
                      />
                      <button
                        type="button"
                        className="btn btn-sm btn-outline-primary"
                        onClick={handleAddCustomAmenity}
                      >
                        Add
                      </button>
                    </div>
                  </div>
                )}
              </div>
            )}

            {/* Classic Navigation Controls */}
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

              {step < maxWizardSteps ? (
                <button
                  type="button"
                  className="btn btn-primary px-4 rounded-pill d-inline-flex align-items-center gap-1.5 text-white"
                  onClick={handleContinue}
                >
                  <span>Continue</span>
                  <ChevronRight size={16} />
                </button>
              ) : (
                <button
                  type="button"
                  className="btn btn-danger px-5 rounded-pill text-dark fw-bold d-inline-flex align-items-center gap-1.5"
                  disabled={loading}
                  onClick={handleSave}
                >
                  <Save size={16} />
                  <span>{loading ? 'Saving Listing...' : isEdit ? 'Update Property' : 'Submit Property'}</span>
                </button>
              )}
            </div>
          </form>
        </div>
      )}

      {/* Modals */}
      {/* Login / Register Overlay Modal */}
      {showLoginModal && (
        <div className="nb-auth-overlay-backdrop">
          <div className="nb-auth-overlay-card">
            <button
              type="button"
              className="nb-auth-overlay-close"
              onClick={() => setShowLoginModal(false)}
            >
              <X size={16} />
            </button>

            {modalErrorMsg && (
              <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-3 rounded-3">
                <ShieldAlert size={16} />
                <span>{modalErrorMsg}</span>
              </div>
            )}

            {loginModalStep === 'phone' && (
              <form onSubmit={handleLocalLoginSubmit}>
                <h3 className="h5 fw-bold text-primary mb-1">Sign in with Phone</h3>
                <p className="small text-muted mb-4">We will send a 4-digit OTP to your WhatsApp</p>
                <div className="mb-4">
                  <label className="form-label small fw-bold text-secondary">Phone Number</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light fw-bold text-muted">+91</span>
                    <input
                      type="tel"
                      className="form-control"
                      placeholder="10-digit mobile number"
                      maxLength={10}
                      value={landingPhone}
                      onChange={(e) => setLandingPhone(e.target.value.replace(/\D/g, ''))}
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-primary w-100 py-2 fw-bold rounded-pill text-white"
                  style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
                  disabled={modalLoading || landingPhone.length !== 10}
                >
                  {modalLoading ? 'Sending OTP...' : 'Send OTP'}
                </button>
                <div className="text-center mt-3 small text-muted">
                  Don&apos;t have an account?{' '}
                  <button
                    type="button"
                    className="btn btn-link p-0 small fw-bold text-decoration-none"
                    onClick={() => {
                      setLoginModalStep('register');
                      setModalErrorMsg(null);
                    }}
                  >
                    Register here
                  </button>
                </div>
              </form>
            )}

            {loginModalStep === 'otp' && (
              <form onSubmit={handleLocalLoginSubmit}>
                <button
                  type="button"
                  className="btn btn-link btn-sm p-0 mb-3 text-decoration-none"
                  onClick={() => {
                    setLoginModalStep('phone');
                    setLoginOtp('');
                    setModalErrorMsg(null);
                  }}
                >
                  &larr; Change number
                </button>
                <h3 className="h5 fw-bold text-primary mb-1">Verify OTP</h3>
                <p className="small text-muted mb-4">OTP sent to +91 {landingPhone}</p>
                <div className="mb-4">
                  <label className="form-label small fw-bold text-secondary">Enter 4-digit OTP</label>
                  <div className="input-group">
                    <span className="input-group-text bg-light"><Lock size={16} /></span>
                    <input
                      type="text"
                      inputMode="numeric"
                      autoComplete="one-time-code"
                      className="form-control text-center fw-bold"
                      placeholder="• • • •"
                      maxLength={4}
                      value={loginOtp}
                      onChange={(e) => setLoginOtp(e.target.value.replace(/\D/g, '').slice(0, 4))}
                      required
                      style={{ letterSpacing: '0.35em', fontSize: '1.25rem' }}
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-primary w-100 py-2 fw-bold rounded-pill text-white mb-3"
                  style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
                  disabled={modalLoading || loginOtp.length !== 4}
                >
                  {modalLoading ? 'Verifying...' : 'Verify & Sign In'}
                </button>

                <div className="text-center small">
                  {otpResendTimer > 0 ? (
                    <span className="text-muted">Resend OTP in {otpResendTimer}s</span>
                  ) : (
                    <button
                      type="button"
                      className="btn btn-link p-0 small fw-bold text-decoration-none"
                      disabled={modalLoading}
                      onClick={async () => {
                        setModalErrorMsg(null);
                        setModalLoading(true);
                        try {
                          const result = await resendOtp(landingPhone);
                          if (!result.success) {
                            setModalErrorMsg(result.message || 'Could not resend OTP.');
                          } else {
                            setOtpResendTimer(60);
                          }
                        } catch {
                          setModalErrorMsg('Failed to resend OTP.');
                        } finally {
                          setModalLoading(false);
                        }
                      }}
                    >
                      Resend OTP
                    </button>
                  )}
                </div>
              </form>
            )}

            {loginModalStep === 'register' && (
              <form onSubmit={handleLocalLoginSubmit} style={{ maxHeight: '75vh', overflowY: 'auto' }}>
                <h3 className="h5 fw-bold text-primary mb-1">Create Owner Account</h3>
                <p className="small text-muted mb-4">Quick registration for Owner/Agent posting</p>
                <div className="mb-2">
                  <label className="form-label small fw-bold text-secondary">Full Name</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text"><User size={14} /></span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Your Name"
                      value={localRegName}
                      onChange={(e) => setLocalRegName(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <div className="mb-2">
                  <label className="form-label small fw-bold text-secondary">Phone Number</label>
                  <input
                    type="text"
                    className="form-control form-control-sm bg-light"
                    value={landingPhone}
                    disabled
                  />
                </div>

                <div className="mb-4">
                  <label className="form-label small fw-bold text-secondary">City</label>
                  <select
                    className="form-select form-select-sm"
                    value={localRegCity}
                    onChange={(e) => setLocalRegCity(e.target.value)}
                    required
                  >
                    <option value="">Select City</option>
                    {cities.map((c) => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                </div>

                <div className="d-flex gap-2">
                  <button
                    type="button"
                    className="btn btn-outline-secondary flex-grow-1 py-1.5 fw-semibold rounded-pill btn-sm"
                    onClick={() => {
                      setLoginModalStep('phone');
                      setModalErrorMsg(null);
                    }}
                  >
                    Back to Login
                  </button>
                  <button
                    type="submit"
                    className="btn btn-primary flex-grow-1 py-1.5 fw-bold rounded-pill text-white btn-sm"
                    style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
                    disabled={modalLoading}
                  >
                    {modalLoading ? 'Registering...' : 'Sign Up Free'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}

      {/* Resume Draft Overlay Modal */}
      {showDraftModal && (
        <div className="nb-auth-overlay-backdrop">
          <div className="nb-auth-overlay-card text-center p-4">
            <div className="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-primary-soft text-primary mb-3" style={{ width: '60px', height: '60px' }}>
              <HelpCircle size={32} style={{ color: '#3b82f6' }} />
            </div>
            <h3 className="h5 fw-bold text-primary mb-2">Continue where you left off?</h3>
            <p className="small text-muted mb-4">Pick up from where you left the form last time</p>

            <div className="d-flex gap-3 justify-content-center">
              <button
                type="button"
                className="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-pill flex-grow-1"
                onClick={handleCancelDraft}
              >
                Cancel
              </button>
              <button
                type="button"
                className="btn btn-primary px-4 py-2 fw-bold rounded-pill text-white flex-grow-1"
                style={{ background: '#3b82f6', borderColor: '#3b82f6' }}
                onClick={handleConfirmDraft}
              >
                Confirm
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PropertyForm;
