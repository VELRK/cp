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
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;

/** Open the CodeIgniter broker admin panel (uses API token SSO when available). */
export const getAdminPanelUrl = (): string => {
  if (typeof window === 'undefined') {
    return '/panel';
  }
  const token = localStorage.getItem('nb_token');
  return token ? `/panel/auth?token=${encodeURIComponent(token)}` : '/panel';
};
