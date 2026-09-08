/**
 * Single source of truth for all Next.js frontend API calls.
 *
 * PHP (proxied in dev): /api/nb/*, /api/blogs, /api/property/*, /api/mobile/*
 * PHP (production):        /api/feedback, /api/owner/*, /api/tenant/*, /api/properties/*
 * Next.js app/api/*       — dev only; production uses Api_web.php
 */
import type { AxiosRequestConfig } from 'axios';
import api, { getAdminPanelUrl, cachedGet, invalidateApiCache } from './api';

export { getAdminPanelUrl, invalidateApiCache };

export const getHomeUrl = () => process.env.NODE_ENV === 'production' ? '/cp/' : '/';

/** Route paths used by the web frontend only. */
export const API_PATHS = {
  // Auth (PHP)
  login: '/api/nb/login',
  sendOtp: '/api/nb/send-otp',
  verifyOtp: '/api/nb/verify-otp',
  resendOtp: '/api/nb/resend-otp',
  register: '/api/nb/register',
  logout: '/api/nb/logout',
  me: '/api/nb/me',
  updateProfile: '/api/nb/update-profile',
  agentKyc: '/api/nb/agent-kyc',
  kycHistory: '/api/nb/kyc-history',
  // Cities & search (PHP)
  cities: '/api/nb/cities',
  exploreCities: '/api/nb/explore-cities',
  search: '/api/nb/search',
  siteBanners: '/api/nb/site-banners',
  homeBanners: '/api/nb/home-banners',
  propertyTypeCounts: '/api/nb/property-type-counts',
  propertyTypes: '/api/property-types',
  propertyTypesFlat: '/api/property-types/flat',
  amenities: '/api/amenities',
  // Wishlist & enquiry (PHP)
  wishlist: '/api/nb/wishlist',
  wishlistCheck: '/api/nb/wishlist/check',
  wishlistToggle: '/api/nb/wishlist/toggle',
  enquiry: '/api/nb/enquiry',
  notifications: '/api/nb/notifications',
  // Blogs & property save (PHP)
  blogs: '/api/blogs',
  propertySave: '/api/property/save',
  // Live updates (PHP mobile route, used by web modal)
  liveUpdateCreate: '/api/mobile/live-updates/create',
  videos: '/api/mobile/videos',
  // Property detail (Next.js)
  property: (idOrSlug: string | number) => `/api/properties/${idOrSlug}`,
  // Owner dashboard (Next.js)
  ownerDashboard: '/api/owner/dashboard',
  ownerListings: '/api/owner/listings',
  ownerEnquiries: '/api/owner/enquiries',
  // Tenant (Next.js)
  tenantEnquiries: '/api/tenant/enquiries',
  // Feedback (Next.js)
  feedback: '/api/feedback',
} as const;

export type SearchParams = {
  q?: string;
  city_id?: number | string;
  property_type?: string;
  listing_type?: string;
  min_price?: number | string;
  max_price?: number | string;
  bedrooms?: number | string;
  sort?: string;
  page?: number;
  limit?: number;
  is_featured?: number | string;
  is_recommended?: number | string;
  is_newly_launched?: number | string;
  is_verified_property?: number | string;
  verified?: number | string;
  has_video?: number | string;
  posted_by_owner?: number | string;
  ready_to_move?: number | string;
  under_construction?: number | string;
  is_premium?: number | string;
  is_home_banner?: number | string;
  tags_best_rate_localities?: number | string;
  tags_high_growth_localities?: number | string;
  best_rate?: number | string;
  best_rated?: number | string;
  high_growth?: number | string;
  [key: string]: string | number | undefined;
};

// ——— Auth ———

export const getMe = () =>
  cachedGet(API_PATHS.me, { validateStatus: (status: number) => status < 500 } as any, 10 * 1000);

export const login = async (loginId: string, password: string) => {
  invalidateApiCache();
  return api.post(API_PATHS.login, { login: loginId, password });
};

export const sendOtp = (phone: string, countryCode = '+91') =>
  api.post(API_PATHS.sendOtp, { phone, country_code: countryCode });

export const verifyOtp = async (phone: string, otp: string, countryCode = '+91') => {
  invalidateApiCache();
  return api.post(API_PATHS.verifyOtp, { phone, otp, country_code: countryCode });
};

export const resendOtp = (phone: string, countryCode = '+91') =>
  api.post(API_PATHS.resendOtp, { phone, country_code: countryCode });

export const register = async (formData: FormData, config?: AxiosRequestConfig) => {
  invalidateApiCache();
  return api.post(API_PATHS.register, formData, config);
};

export const logout = async () => {
  invalidateApiCache();
  return api.post(API_PATHS.logout);
};

export const updateProfile = async (formData: FormData, config?: AxiosRequestConfig) => {
  invalidateApiCache(API_PATHS.me);
  return api.post(API_PATHS.updateProfile, formData, config);
};

export const getAgentKyc = () => api.get(API_PATHS.agentKyc);

export const getKycHistory = (userId?: number) =>
  api.get(API_PATHS.kycHistory, userId ? { params: { user_id: userId } } : undefined);

// ——— Cities, search, banners (High-Performance Cached Endpoints) ———

/** Cached for 5 minutes with instant in-flight deduplication */
export const getCities = () => cachedGet(API_PATHS.cities, undefined, 5 * 60 * 1000);

/** Cities with active listing counts (Explore Cities homepage) — cached for 5 minutes */
export const getExploreCities = () => cachedGet(API_PATHS.exploreCities, undefined, 5 * 60 * 1000);

/** Property search with 30s cache and concurrent request deduplication */
export const searchProperties = (params?: SearchParams) =>
  cachedGet(API_PATHS.search, { params }, 30 * 1000);

export const getSiteBanners = (params?: { limit?: number }) =>
  cachedGet(API_PATHS.siteBanners, { params }, 2 * 60 * 1000);

/** Property listings flagged as home banner (hero with property details) — cached 2 minutes */
export const getHomeBanners = (params?: SearchParams) =>
  cachedGet(API_PATHS.homeBanners, { params }, 2 * 60 * 1000);

/** Sub property type listing counts for homepage categories — cached 3 minutes */
export const getPropertyTypeCounts = (params?: { city_id?: number | string }) =>
  cachedGet(API_PATHS.propertyTypeCounts, { params }, 3 * 60 * 1000);

/** Active property types (main + sub_types grouped) — cached 5 minutes */
export const getPropertyTypes = () => cachedGet(API_PATHS.propertyTypes, undefined, 5 * 60 * 1000);

/** Active property types flat list for dropdowns — cached 5 minutes */
export const getPropertyTypesFlat = () => cachedGet(API_PATHS.propertyTypesFlat, undefined, 5 * 60 * 1000);

// ——— Wishlist ———

export const getWishlist = (userId: number) =>
  cachedGet(API_PATHS.wishlist, { params: { userId } }, 15 * 1000);

export const checkWishlist = (
  propertyId: number,
  userId: number,
  paramName: 'userId' | 'user_id' = 'userId'
) =>
  cachedGet(
    API_PATHS.wishlistCheck,
    {
      params: { property_id: propertyId, [paramName]: userId },
    },
    60 * 1000
  );

export const toggleWishlist = async (payload: {
  property_id: number;
  userId?: number;
  user_id?: number;
}) => {
  invalidateApiCache(API_PATHS.wishlist);
  invalidateApiCache(API_PATHS.wishlistCheck);
  return api.post(API_PATHS.wishlistToggle, payload);
};

// ——— Enquiry & notifications ———

export const submitEnquiry = (payload: Record<string, unknown>) =>
  api.post(API_PATHS.enquiry, payload);

export const getNotifications = () => api.get(API_PATHS.notifications);

// ——— Blogs ———

export const getBlogs = () => cachedGet(API_PATHS.blogs, undefined, 3 * 60 * 1000);

export const getBlogById = (id: string | number) =>
  cachedGet(API_PATHS.blogs, { params: { id } }, 3 * 60 * 1000);

// ——— Property ———

export const getProperty = (idOrSlug: string | number) =>
  cachedGet(API_PATHS.property(idOrSlug), undefined, 30 * 1000);

export const saveProperty = async (formData: FormData, config?: AxiosRequestConfig) => {
  invalidateApiCache('/api/properties');
  invalidateApiCache('/api/nb/search');
  invalidateApiCache('/api/nb/home-banners');
  invalidateApiCache('/api/nb/explore-cities');
  invalidateApiCache('/api/nb/property-type-counts');
  return api.post(API_PATHS.propertySave, formData, config);
};

// ——— Owner / tenant (Next.js routes) ———

export const getOwnerDashboard = () => api.get(API_PATHS.ownerDashboard);

export const getOwnerListings = () => api.get(API_PATHS.ownerListings);

export const getOwnerEnquiries = () => api.get(API_PATHS.ownerEnquiries);

export const getTenantEnquiries = () => api.get(API_PATHS.tenantEnquiries);

// ——— Feedback (Next.js route) ———

export const getFeedbacks = () => api.get(API_PATHS.feedback);

export const submitFeedback = (formData: FormData) =>
  api.post(API_PATHS.feedback, formData);

// ——— Live updates ———

export const createLiveUpdate = async (formData: FormData) => {
  invalidateApiCache('/api/mobile/live-updates');
  return api.post(API_PATHS.liveUpdateCreate, formData);
};

// ——— Videos ———

export const getVideos = () => cachedGet(API_PATHS.videos, undefined, 3 * 60 * 1000);

export default api;
