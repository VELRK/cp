/**
 * Single source of truth for all Next.js frontend API calls.
 *
 * PHP (proxied in dev): /api/nb/*, /api/blogs, /api/property/*, /api/mobile/*
 * PHP (production):        /api/feedback, /api/owner/*, /api/tenant/*, /api/properties/*
 * Next.js app/api/*       — dev only; production uses Api_web.php
 */
import type { AxiosRequestConfig } from 'axios';
import api, { getAdminPanelUrl } from './api';

export { getAdminPanelUrl };

/** Route paths used by the web frontend only. */
export const API_PATHS = {
  // Auth (PHP)
  login: '/api/nb/login',
  register: '/api/nb/register',
  logout: '/api/nb/logout',
  me: '/api/nb/me',
  updateProfile: '/api/nb/update-profile',
  // Cities & search (PHP)
  cities: '/api/nb/cities',
  exploreCities: '/api/nb/explore-cities',
  search: '/api/nb/search',
  siteBanners: '/api/nb/site-banners',
  homeBanners: '/api/nb/home-banners',
  propertyTypeCounts: '/api/nb/property-type-counts',
  propertyTypes: '/api/property-types',
  propertyTypesFlat: '/api/property-types/flat',
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
  [key: string]: string | number | undefined;
};

// ——— Auth ———

export const getMe = () => api.get(API_PATHS.me, { validateStatus: (status) => status < 500 });

export const login = (loginId: string, password: string) =>
  api.post(API_PATHS.login, { login: loginId, password });

export const register = (formData: FormData, config?: AxiosRequestConfig) =>
  api.post(API_PATHS.register, formData, config);

export const logout = () => api.post(API_PATHS.logout);

export const updateProfile = (formData: FormData, config?: AxiosRequestConfig) =>
  api.post(API_PATHS.updateProfile, formData, config);

// ——— Cities, search, banners ———

export const getCities = () => api.get(API_PATHS.cities);

/** Cities with active listing counts (Explore Cities homepage). */
export const getExploreCities = () => api.get(API_PATHS.exploreCities);

export const searchProperties = (params?: SearchParams) =>
  api.get(API_PATHS.search, { params });

export const getSiteBanners = (params?: { limit?: number }) =>
  api.get(API_PATHS.siteBanners, { params });

/** Property listings flagged as home banner (hero with property details). */
export const getHomeBanners = (params?: SearchParams) =>
  api.get(API_PATHS.homeBanners, { params });

/** Sub property type listing counts for homepage categories. */
export const getPropertyTypeCounts = (params?: { city_id?: number | string }) =>
  api.get(API_PATHS.propertyTypeCounts, { params });

/** Active property types (main + sub_types grouped). */
export const getPropertyTypes = () => api.get(API_PATHS.propertyTypes);

/** Active property types flat list for dropdowns. */
export const getPropertyTypesFlat = () => api.get(API_PATHS.propertyTypesFlat);

// ——— Wishlist ———

export const getWishlist = (userId: number) =>
  api.get(API_PATHS.wishlist, { params: { userId } });

export const checkWishlist = (
  propertyId: number,
  userId: number,
  paramName: 'userId' | 'user_id' = 'userId'
) =>
  api.get(API_PATHS.wishlistCheck, {
    params: { property_id: propertyId, [paramName]: userId },
  });

export const toggleWishlist = (payload: {
  property_id: number;
  userId?: number;
  user_id?: number;
}) => api.post(API_PATHS.wishlistToggle, payload);

// ——— Enquiry & notifications ———

export const submitEnquiry = (payload: Record<string, unknown>) =>
  api.post(API_PATHS.enquiry, payload);

export const getNotifications = () => api.get(API_PATHS.notifications);

// ——— Blogs ———

export const getBlogs = () => api.get(API_PATHS.blogs);

export const getBlogById = (id: string | number) =>
  api.get(API_PATHS.blogs, { params: { id } });

// ——— Property ———

export const getProperty = (idOrSlug: string | number) =>
  api.get(API_PATHS.property(idOrSlug));

export const saveProperty = (formData: FormData, config?: AxiosRequestConfig) =>
  api.post(API_PATHS.propertySave, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    ...config,
  });

// ——— Owner / tenant (Next.js routes) ———

export const getOwnerDashboard = () => api.get(API_PATHS.ownerDashboard);

export const getOwnerListings = () => api.get(API_PATHS.ownerListings);

export const getOwnerEnquiries = () => api.get(API_PATHS.ownerEnquiries);

export const getTenantEnquiries = () => api.get(API_PATHS.tenantEnquiries);

// ——— Feedback (Next.js route) ———

export const getFeedbacks = () => api.get(API_PATHS.feedback);

export const submitFeedback = (formData: FormData) =>
  api.post(API_PATHS.feedback, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

// ——— Live updates ———

export const createLiveUpdate = (formData: FormData) =>
  api.post(API_PATHS.liveUpdateCreate, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

export default api;
