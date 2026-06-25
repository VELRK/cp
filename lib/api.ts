import axios from 'axios';

/** Low-level HTTP client. Import API functions from `./frontendApi` in app/components. */

const backendBase =
  process.env.NEXT_PUBLIC_BACKEND_URL || 'http://127.0.0.1:8080/cp';

const getBaseUrl = () => {
  if (typeof window !== 'undefined') {
    // XAMPP on default HTTP/HTTPS ports — app lives under /cp
    if (
      window.location.port === '' ||
      window.location.port === '80' ||
      window.location.port === '443'
    ) {
      return `${window.location.origin}/cp`;
    }
    // Next.js dev (port 3000) — same origin; next.config.js rewrites proxy /api/* to PHP
    return window.location.origin;
  }
  // SSR / server-side calls go directly to the PHP backend
  return backendBase;
};

const api = axios.create({
  baseURL: getBaseUrl(),
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Add request interceptor to attach token
api.interceptors.request.use(
  (config) => {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('nb_token');
      if (token) {
        config.headers['X-Api-Token'] = token;
      }
    }
    // FormData must set its own multipart boundary — do not force application/json
    if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
      if (config.headers) {
        delete config.headers['Content-Type'];
        delete config.headers['content-type'];
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;

/** Combine API message with optional upload_errors array from PHP responses. */
export function formatApiErrorMessage(
  data: { message?: string; upload_errors?: string[] } | undefined,
  fallback: string
): string {
  const msg = data?.message?.trim() || fallback;
  const uploadErrors = data?.upload_errors;
  if (Array.isArray(uploadErrors) && uploadErrors.length > 0) {
    return [msg, ...uploadErrors].join('\n');
  }
  return msg;
}

const APP_BASE_PATH = '/cp';

/** App subfolder prefix (empty on Next dev — rewrites proxy to PHP). */
function getAppBasePath(): string {
  if (typeof window === 'undefined') {
    return APP_BASE_PATH;
  }
  if (window.location.port === '3000' || window.location.port === '3001') {
    return '';
  }
  return APP_BASE_PATH;
}

/** Open the CodeIgniter broker admin panel (uses API token SSO when available). */
export const getAdminPanelUrl = (): string => {
  const base = getAppBasePath();
  if (typeof window === 'undefined') {
    return `${base}/panel`;
  }
  const token = localStorage.getItem('nb_token');
  return token
    ? `${base}/panel/auth?token=${encodeURIComponent(token)}`
    : `${base}/panel`;
};
