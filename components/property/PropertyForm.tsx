'use client';
import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { getCities, saveProperty } from '@/lib/frontendApi';
import { formatApiErrorMessage } from '@/lib/api';
import { usePropertyTypeFilters } from '@/hooks/usePropertyTypeFilters';
import { PropertyTypeFilterFields } from '@/components/common/PropertyTypeSelects';
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
  Mail,
  User,
  Phone,
  LayoutGrid,
  Laptop,
  CheckCircle,
  HelpCircle
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

const PropertyForm: React.FC<PropertyFormProps> = ({ initialData, isEdit = false, ownerMode = false }) => {
  const router = useRouter();
  const { user, sendOtp, verifyOtp, resendOtp, registerUser, setAuthModalOpen } = useAuth();

  // Dual mode UI state: 'modern' | 'classic'
  const [uiMode, setUiMode] = useState<'modern' | 'classic'>('modern');

  // Modern Step 0 Landing options page state (skipped for owner dashboard add)
  const [isLandingMode, setIsLandingMode] = useState(!isEdit && !ownerMode);

  // Modern landing custom options
  const [landingListingType, setLandingListingType] = useState<'sale' | 'rent' | 'pg'>('sale');
  const [landingCategory, setLandingCategory] = useState<'residential' | 'commercial'>('residential');
  const [landingSubcategory, setLandingSubcategory] = useState('apartment');
  const [landingPlotType, setLandingPlotType] = useState('Commercial Land/Inst. Land');
  const [landingPhone, setLandingPhone] = useState('');

  // Local popup states for Step 0
  const [showLoginModal, setShowLoginModal] = useState(false);
  const [loginModalStep, setLoginModalStep] = useState<'phone' | 'otp' | 'register'>('phone');
  const [loginOtp, setLoginOtp] = useState('');
  const [otpResendTimer, setOtpResendTimer] = useState(0);
  const [localPassword, setLocalPassword] = useState('');
  const [localRegName, setLocalRegName] = useState('');
  const [localRegEmail, setLocalRegEmail] = useState('');
  const [localRegCity, setLocalRegCity] = useState('');
  const [showDraftModal, setShowDraftModal] = useState(false);

  // Core Wizard step state
  // In classic mode: step is 1 to 4. In modern mode: step is 1 to 5.
  const [step, setStep] = useState(1);

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
  const [brochureFile, setBrochureFile] = useState<File | null>(null);
  const [audioNotesFile, setAudioNotesFile] = useState<File | null>(null);
  const [removeBrochure, setRemoveBrochure] = useState(false);
  const [removeAudioNotes, setRemoveAudioNotes] = useState(false);
  const [existingBrochureUrl, setExistingBrochureUrl] = useState(initialData?.brochure_url || initialData?.brochure_url_url || '');
  const [existingAudioUrl, setExistingAudioUrl] = useState(initialData?.audio_notes_url || initialData?.audio_notes_url_url || '');
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
  const [modalErrorMsg, setModalErrorMsg] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [modalLoading, setModalLoading] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [successPendingReview, setSuccessPendingReview] = useState(false);

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
      return landingCategory === 'commercial' ? commercial : !commercial;
    });
    const list = options.length > 0 ? options : mainTypes;
    if (!list.some((m) => m.slug === landingSubcategory)) {
      setLandingSubcategory(list[0].slug);
    }
  }, [mainTypes, landingCategory, landingSubcategory]);

  // Draft recovery check on load
  useEffect(() => {
    if (!isEdit) {
      const savedDraft = localStorage.getItem('nb_draft_property');
      if (savedDraft) {
        try {
          const parsed = JSON.parse(savedDraft);
          // Only show resume draft modal if some valuable data exists
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
    if (!isEdit && uiMode === 'modern' && !isLandingMode) {
      const draftData = {
        title,
        propertyType,
        listingType,
        price,
        isPriceNegotiable,
        bedrooms,
        bathrooms,
        areaSqft,
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
      // Only save draft if user has inputted something
      if (title || price || locality || description) {
        localStorage.setItem('nb_draft_property', JSON.stringify(draftData));
      }
    }
  }, [
    title,
    propertyType,
    listingType,
    price,
    isPriceNegotiable,
    bedrooms,
    bathrooms,
    areaSqft,
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
    uiMode,
    isLandingMode,
    isEdit
  ]);

  const handleConfirmDraft = () => {
    const savedDraft = localStorage.getItem('nb_draft_property');
    if (savedDraft) {
      try {
        const parsed = JSON.parse(savedDraft);
        setTitle(parsed.title || '');
        setMainTypeSlug(parsed.propertyType || 'apartment');
        setSubTypeSlug('');
        setListingType(parsed.listingType || 'sale');
        setPrice(parsed.price || '');
        setIsPriceNegotiable(!!parsed.isPriceNegotiable);
        setBedrooms(parsed.bedrooms || '');
        setBathrooms(parsed.bathrooms || '');
        setAreaSqft(parsed.areaSqft || '');
        setRatePerSqft(parsed.ratePerSqft || '');
        setAddress(parsed.address || '');
        setLocality(parsed.locality || '');
        if (parsed.cityId) setCityId(parsed.cityId);
        setDescription(parsed.description || '');
        setLocation(parsed.location || '');
        setVideoUrl(parsed.videoUrl || '');
        setAvailableFrom(parsed.availableFrom || '');
        setPlotLength(parsed.plotLength || '');
        setPlotWidth(parsed.plotWidth || '');
        setHasBoundaryWall(parsed.hasBoundaryWall || '');
        setSelectedAmenities(parsed.selectedAmenities || []);
        setStep(parsed.step || 1);
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

  const showPlotFields = propertyType === 'plot' || propertyType === 'others' || propertyType === 'land';

  // Calculate property completeness score (0 to 100)
  const calculatePropertyScore = () => {
    let score = 0;
    if (title.trim()) score += 15;
    if (propertyType && listingType) score += 15;
    if (price && Number(price) > 0) score += 15;
    if (cityId && locality.trim() && address.trim()) score += 20;
    if (newImages.length > 0 || existingImages.length > 0) score += 15;
    if (description.trim()) score += 10;
    if (selectedAmenities.length > 0 || showPlotFields) score += 10;
    return score;
  };

  const validateStep = (currentStep: number, mode: 'classic' | 'modern'): boolean => {
    if (mode === 'classic') {
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
    } else {
      // Modern mode validation (5 steps)
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
      } else if (currentStep === 3) {
        if (!price) {
          setErrorMsg('Please enter the price or rent.');
          return false;
        }
        if (Number(price) <= 0) {
          setErrorMsg('Please enter a valid price greater than zero.');
          return false;
        }
      }
    }
    setErrorMsg(null);
    return true;
  };

  const maxWizardSteps = uiMode === 'classic' ? 4 : 5;

  const nextStep = () => {
    if (validateStep(step, uiMode)) {
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
    setListingType(landingListingType === 'pg' ? 'rent' : landingListingType);
    setMainTypeSlug(landingSubcategory);
    setSubTypeSlug('');
    const cityLabel = cities.find((c) => c.id.toString() === cityId)?.name || 'your city';
    setTitle(`Premium ${landingSubcategory.replace(/_/g, ' ')} for ${landingListingType === 'sale' ? 'Sale' : 'Rent'} in ${cityLabel}`);
    if (landingPhone) {
      setLandingPhone(landingPhone);
    }

    if (!user) {
      setLoginOtp('');
      setOtpResendTimer(0);
      setLoginModalStep('phone');
      setShowLoginModal(true);
      return;
    }

    // Authenticated, proceed directly
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
        // Build FormData for local signup
        const fd = new FormData();
        fd.append('name', localRegName);
        fd.append('email', localRegEmail);
        fd.append('phone', landingPhone);
        fd.append('password', localPassword);
        fd.append('password_confirm', localPassword);
        fd.append('role', 'owner'); // Default role owner
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
      if (!validateStep(s, uiMode)) {
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
      if (brochureFile) {
        formData.append('brochure', brochureFile);
      }
      if (audioNotesFile) {
        formData.append('audio_notes', audioNotesFile);
      }
      if (isEdit && removeBrochure) {
        formData.append('remove_brochure', '1');
      }
      if (isEdit && removeAudioNotes) {
        formData.append('remove_audio_notes', '1');
      }
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
        return landingCategory === 'commercial' ? commercial : !commercial;
      });
      const list = filtered.length > 0 ? filtered : mainTypes;
      return list.map((m) => ({ val: m.slug, label: m.name }));
    }
    if (landingCategory === 'residential') {
      return [
        { val: 'apartment', label: 'Flat/Apartment' },
        { val: 'house', label: 'Independent House / Villa' },
        { val: 'builder_floor', label: 'Independent / Builder Floor' },
        { val: 'plot', label: 'Plot / Land' },
        { val: 'studio', label: '1 RK / Studio Apartment' },
        { val: 'serviced_apartment', label: 'Serviced Apartment' },
        { val: 'farmhouse', label: 'Farmhouse' },
        { val: 'others', label: 'Other' },
      ];
    } else {
      return [
        { val: 'office', label: 'Office Space' },
        { val: 'retail', label: 'Retail / Shop' },
        { val: 'plot', label: 'Plot / Land' },
        { val: 'warehouse', label: 'Storage / Godown' },
        { val: 'commercial', label: 'Commercial Space' },
        { val: 'others', label: 'Other' },
      ];
    }
  };

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

      {/* 1. Header Dual-Mode Toggle Bar */}
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
            Premium CP Form
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
            Classic 4-Step Form
          </button>
        </div>
      </div>
      )}

      {/* 2. Step 0 Landing Option page (Modern Mode only) */}
      {uiMode === 'modern' && isLandingMode ? (
        <div className="nb-post-landing-container">
          {/* Left panel - visual benefits */}
          <div className="nb-post-landing-left">
            <h1 className="nb-post-landing-title">
              Sell or Rent Property<br />
              <span>online faster</span> with CP
            </h1>
            <ul className="nb-post-landing-list">
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Advertise for FREE</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get unlimited enquiries</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Get shortlisted buyers and tenants</span>
              </li>
              <li className="nb-post-landing-item">
                <CheckCircle2 size={20} />
                <span>Assistance in co-ordinating site visits</span>
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
              * Available with Owner Assist Plans
            </p>
          </div>

          {/* Right panel - form configuration */}
          <div className="nb-post-landing-right">
            <h2 className="nb-post-landing-card-title">Start posting your property, it&apos;s free</h2>
            <p className="nb-post-landing-card-subtitle">Add Basic Details</p>

            <form onSubmit={handleLandingStartNow}>
              {/* Sell / Rent / PG selection */}
              <div className="mb-4">
                <span className="nb-post-pill-group-title d-block">You&apos;re looking to ...</span>
                <div className="d-flex gap-2">
                  {(['sale', 'rent', 'pg'] as const).map((t) => (
                    <button
                      key={t}
                      type="button"
                      className={`nb-post-pill text-capitalize ${landingListingType === t ? 'active' : ''}`}
                      onClick={() => setLandingListingType(t)}
                    >
                      {t === 'rent' ? 'Rent / Lease' : t === 'sale' ? 'Sell' : 'PG'}
                    </button>
                  ))}
                </div>
              </div>

              {/* Residential / Commercial selector */}
              <div className="mb-4">
                <span className="nb-post-pill-group-title d-block">And it&apos;s a ...</span>
                <div className="d-flex gap-4 align-items-center mb-3">
                  <label className="d-flex align-items-center gap-2 cursor-pointer small fw-semibold text-secondary">
                    <input
                      type="radio"
                      name="landingCat"
                      className="form-check-input mt-0"
                      checked={landingCategory === 'residential'}
                      onChange={() => {
                        setLandingCategory('residential');
                        setLandingSubcategory('apartment');
                      }}
                    />
                    Residential
                  </label>
                  <label className="d-flex align-items-center gap-2 cursor-pointer small fw-semibold text-secondary">
                    <input
                      type="radio"
                      name="landingCat"
                      className="form-check-input mt-0"
                      checked={landingCategory === 'commercial'}
                      onChange={() => {
                        setLandingCategory('commercial');
                        setLandingSubcategory('office');
                      }}
                    />
                    Commercial
                  </label>
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

              {/* Plot Land Type Specific Selector */}
              {landingSubcategory === 'plot' && (
                <div className="mb-4 animate-pulse">
                  <span className="nb-post-pill-group-title d-block">Your plot / land type is ...</span>
                  <div className="nb-post-pills">
                    {[
                      'Commercial Land/Inst. Land',
                      'Agricultural/Farm Land',
                      'Industrial Lands/Plots'
                    ].map((type) => (
                      <button
                        key={type}
                        type="button"
                        className={`nb-post-pill ${landingPlotType === type ? 'active' : ''}`}
                        onClick={() => setLandingPlotType(type)}
                      >
                        {type}
                      </button>
                    ))}
                  </div>
                </div>
              )}

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
                    Are you a registered user? <button type="button" onClick={() => { setLoginOtp(''); setLoginModalStep('phone'); setShowLoginModal(true); }} className="btn btn-link p-0 small text-decoration-none fw-semibold">Login</button>
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
        /* 3. Modern Wizard Screen (Steps 1-5 side-by-side) */
        <div className="nb-modern-wizard-grid">
          {/* Left Sidebar Checklist & Property score */}
          <div className="nb-modern-wizard-sidebar">
            <div className="nb-modern-wizard-steps-card">
              <ul className="nb-modern-wizard-steps-list">
                {[
                  { num: 1, label: 'Basic Details' },
                  { num: 2, label: 'Location Details' },
                  { num: 3, label: 'Property Profile' },
                  { num: 4, label: 'Photos & Videos' },
                  { num: 5, label: 'Amenities section' },
                ].map((s) => (
                  <li
                    key={s.num}
                    className={`nb-modern-wizard-step-item ${step === s.num ? 'active' : step > s.num ? 'done' : ''}`}
                    onClick={() => {
                      if (validateStep(step, 'modern') || s.num < step) {
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
                <p className="nb-score-info-desc">Better your property score, greater your visibility</p>
              </div>
            </div>
          </div>

          {/* Right Main Panel form content */}
          <div className="nb-modern-wizard-form-panel">
            <div className="nb-wizard-welcome-hdr border-bottom pb-3">
              <h2 className="nb-wizard-welcome-title">
                Welcome back {user?.name || 'User'},
              </h2>
              <p className="nb-wizard-welcome-subtitle">
                Fill out property details step-by-step
              </p>
            </div>

            {errorMsg && (
              <div className="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-4 rounded-3">
                <ShieldAlert size={16} />
                <span style={{ whiteSpace: 'pre-line' }}>{errorMsg}</span>
              </div>
            )}

            <form onSubmit={(e) => e.preventDefault()} onKeyDown={handleFormKeyDown}>
              {/* STEP 1: Basic Details */}
              {step === 1 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 1: Basic Details</h3>
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
                        Highlight key landmarks, BHK, and structure in title.
                      </div>
                    </div>

                    <div className="col-md-6 mt-4">
                      <label className="form-label small fw-bold text-secondary">Listing Type</label>
                      <select
                        className="form-select"
                        value={listingType}
                        onChange={(e) => setListingType(e.target.value as 'sale' | 'rent')}
                      >
                        <option value="sale">Sell / Outright Sale</option>
                        <option value="rent">Rent / Lease</option>
                      </select>
                    </div>

                    <div className="col-md-6 mt-4">
                      <label className="form-label small fw-bold text-secondary">Main property type</label>
                      <select
                        className="form-select"
                        value={mainTypeSlug}
                        onChange={(e) => setMainTypeSlug(e.target.value)}
                        required
                      >
                        <option value="">Select main type</option>
                        {mainTypes.map((m) => (
                          <option key={m.id} value={m.slug}>{m.name}</option>
                        ))}
                      </select>
                    </div>

                    {mainTypeSlug && subTypes.length > 0 && (
                      <div className="col-md-6 mt-4">
                        <label className="form-label small fw-bold text-secondary">Sub property type</label>
                        <select
                          className="form-select"
                          value={subTypeSlug}
                          onChange={(e) => setSubTypeSlug(e.target.value)}
                        >
                          <option value="">Select sub type (optional)</option>
                          {subTypes.map((s) => (
                            <option key={s.id} value={s.slug}>{s.name}</option>
                          ))}
                        </select>
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* STEP 2: Location Details */}
              {step === 2 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 2: Location Details</h3>
                  <div className="row g-3">
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
                        placeholder="e.g. Peelamedu, RS Puram"
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
                        placeholder="Flat No, Building, Street Details"
                        value={address}
                        onChange={(e) => setAddress(e.target.value)}
                        required
                      />
                    </div>

                    <div className="col-12">
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

              {/* STEP 3: Property Profile / Pricing */}
              {step === 3 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 3: Property Profile & Pricing</h3>
                  <div className="row g-3 mb-4">
                    <div className="col-md-6">
                      <label className="form-label small fw-bold text-secondary">Expected Price / Rent (₹)</label>
                      <input
                        type="number"
                        className="form-control"
                        placeholder={listingType === 'rent' ? 'Rent per month' : 'Sale price'}
                        value={price}
                        onChange={(e) => setPrice(e.target.value)}
                        required
                      />
                    </div>

                    <div className="col-md-6 d-flex align-items-center pt-4">
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
                  </div>

                  <div className="row g-3 border-top pt-3">
                    <span className="text-secondary small fw-bold d-block mb-1">Specifications</span>
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
                          <label className="form-label small text-secondary fw-semibold">Super Area (sqft)</label>
                          <input
                            type="number"
                            className="form-control"
                            value={areaSqft}
                            onChange={(e) => setAreaSqft(e.target.value)}
                            min="0"
                            placeholder="sqft"
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
                          <label className="form-label small text-secondary fw-semibold">Total Land Area (sqft)</label>
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
                            <option value="">Choose</option>
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
                      <label className="form-label small text-secondary fw-semibold">Possession Date</label>
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

              {/* STEP 4: Photos & Videos */}
              {step === 4 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 4: Photos & Videos</h3>
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

                  <div className="mb-3">
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

                  <div className="mb-3">
                    <label className="form-label small text-secondary fw-semibold">Audio notes (optional)</label>
                    {existingAudioUrl && !removeAudioNotes && (
                      <div className="small mb-2">
                        <audio controls preload="none" className="w-100" style={{ maxWidth: 420 }}>
                          <source src={existingAudioUrl} />
                        </audio>
                        <div className="form-check mt-1">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="removeAudioModern"
                            checked={removeAudioNotes}
                            onChange={(e) => setRemoveAudioNotes(e.target.checked)}
                          />
                          <label className="form-check-label text-danger" htmlFor="removeAudioModern">Remove audio notes</label>
                        </div>
                      </div>
                    )}
                    <input
                      type="file"
                      className="form-control"
                      accept="audio/*,.mp3,.wav,.m4a,.ogg,.webm,.aac"
                      onChange={(e) => setAudioNotesFile(e.target.files?.[0] || null)}
                    />
                  </div>
                </div>
              )}

              {/* STEP 5: Amenities & Details */}
              {step === 5 && (
                <div className="fade-in-up-wizard">
                  <h3 className="h6 fw-bold text-primary mb-3">Step 5: Amenities & Details</h3>
                  <div className="mb-4">
                    <label className="form-label small fw-bold text-secondary">Property Description</label>
                    <textarea
                      className="form-control"
                      rows={4}
                      placeholder="Detailed details, nearby shops, hospital, rules..."
                      value={description}
                      onChange={(e) => setDescription(e.target.value)}
                    />
                  </div>

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
                    </div>
                  )}
                </div>
              )}

              {/* Wizard Control Buttons */}
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
                    <span>Continue</span>
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
        /* 4. Classic 4-step wizard form layout */
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
                    className="mx-auto d-flex align-items-center justify-content-center rounded-circle border fw-bold"
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
                style={{ width: `${((step - 1) / 3) * 100}%`, transition: 'width 0.4s' }}
                aria-valuenow={step}
                aria-valuemin={1}
                aria-valuemax={4}
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
                      {mainTypes.map((m) => (
                        <div className="col-6 col-sm-4 col-md-3" key={m.slug}>
                          <button
                            type="button"
                            className={`btn w-100 h-100 py-3 small rounded-4 border ${mainTypeSlug === m.slug ? 'btn-primary border-primary text-white shadow-sm fw-bold' : 'btn-light border-light text-muted'}`}
                            onClick={() => setMainTypeSlug(m.slug)}
                            style={{ fontSize: '0.85rem', transition: 'all 0.2s' }}
                          >
                            {m.name}
                          </button>
                        </div>
                      ))}
                    </div>
                  </div>

                  {mainTypeSlug && subTypes.length > 0 && (
                    <div className="col-12 mt-3">
                      <label className="form-label small fw-bold text-secondary mb-2">Sub property type</label>
                      <div className="row g-2">
                        {subTypes.map((s) => (
                          <div className="col-6 col-sm-4 col-md-3" key={s.slug}>
                            <button
                              type="button"
                              className={`btn w-100 py-2 small rounded-4 border ${subTypeSlug === s.slug ? 'btn-primary border-primary text-white fw-bold' : 'btn-light border-light text-muted'}`}
                              onClick={() => setSubTypeSlug(s.slug)}
                            >
                              {s.name}
                            </button>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
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
                        id="isNegotiableCheckClassic"
                        checked={isPriceNegotiable}
                        onChange={(e) => setIsPriceNegotiable(e.target.checked)}
                      />
                      <label className="form-check-label small fw-semibold text-secondary ms-2 cursor-pointer" htmlFor="isNegotiableCheckClassic">
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

                <div className="mb-4">
                  <label className="form-label small text-secondary fw-semibold">Audio notes (optional)</label>
                  {existingAudioUrl && !removeAudioNotes && (
                    <div className="small mb-2">
                      <audio controls preload="none" className="w-100" style={{ maxWidth: 420 }}>
                        <source src={existingAudioUrl} />
                      </audio>
                      <div className="form-check mt-1">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id="removeAudioClassic"
                          checked={removeAudioNotes}
                          onChange={(e) => setRemoveAudioNotes(e.target.checked)}
                        />
                        <label className="form-check-label text-danger" htmlFor="removeAudioClassic">Remove audio notes</label>
                      </div>
                    </div>
                  )}
                  <input
                    type="file"
                    className="form-control"
                    accept="audio/*,.mp3,.wav,.m4a,.ogg,.webm,.aac"
                    onChange={(e) => setAudioNotesFile(e.target.files?.[0] || null)}
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

      {/* 5. Custom Modals */}

      {/* A. Login / Register Overlay Modal */}
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
                  <label className="form-label small fw-bold text-secondary">Email Address</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text"><Mail size={14} /></span>
                    <input
                      type="email"
                      className="form-control"
                      placeholder="email@example.com"
                      value={localRegEmail}
                      onChange={(e) => setLocalRegEmail(e.target.value)}
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

                <div className="mb-3">
                  <label className="form-label small fw-bold text-secondary">Password</label>
                  <div className="input-group input-group-sm">
                    <span className="input-group-text"><Lock size={14} /></span>
                    <input
                      type="password"
                      className="form-control"
                      placeholder="Create Password"
                      value={localPassword}
                      onChange={(e) => setLocalPassword(e.target.value)}
                      required
                    />
                  </div>
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

      {/* B. Resume Draft Overlay Modal */}
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
