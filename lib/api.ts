import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';

/** Low-level HTTP client. Import API functions from `./frontendApi` in app/components. */

const backendBase =
  process.env.NEXT_PUBLIC_BACKEND_URL || 'http://127.0.0.1:8080/cp';

const APP_BASE_PATH = '/cp';

/** App subfolder prefix (empty on Next dev — rewrites proxy /api/* to PHP). */
function getAppBasePath(): string {
  if (typeof window === 'undefined') {
    return APP_BASE_PATH;
  }
  if (window.location.port === '3000' || window.location.port === '3001') {
    return '';
  }
  return APP_BASE_PATH;
}

const getBaseUrl = () => {
  if (typeof window !== 'undefined') {
    const base = getAppBasePath();
    return base ? `${window.location.origin}${base}` : window.location.origin;
  }
  // SSR / server-side calls go directly to the PHP backend
  return backendBase;
};

const api = axios.create({
  baseURL: getBaseUrl(),
  withCredentials: true,
  timeout: 15000,
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
      if (token && token !== 'undefined' && token !== 'null') {
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

// --- In-flight request deduplication & in-memory TTL caching ---
interface CacheEntry<T = any> {
  data: T;
  timestamp: number;
  ttl: number;
}

const cacheStore = new Map<string, CacheEntry>();
const inFlightRequests = new Map<string, Promise<AxiosResponse<any>>>();

export function getCacheKey(url: string, params?: any): string {
  if (!params) return url;
  try {
    const keys = Object.keys(params).sort();
    const sorted: Record<string, any> = {};
    for (const k of keys) {
      if (params[k] !== undefined && params[k] !== null && params[k] !== '') {
        sorted[k] = params[k];
      }
    }
    return `${url}?${JSON.stringify(sorted)}`;
  } catch {
    return url;
  }
}

/**
 * Intelligent cached GET request:
 * 1. Checks in-memory cache for valid unexpired data (instant 0ms response).
 * 2. Deduplicates concurrent in-flight requests for the exact same URL + params so only 1 request is fired.
 * 3. Caches successful responses for `ttlMs` milliseconds.
 */
export async function cachedGet<T = any>(
  url: string,
  config?: AxiosRequestConfig,
  ttlMs = 60000
): Promise<AxiosResponse<T>> {
  const cacheKey = getCacheKey(url, config?.params);

  // 1. Memory cache check (client-side only)
  if (typeof window !== 'undefined' && ttlMs > 0) {
    const entry = cacheStore.get(cacheKey);
    if (entry && Date.now() - entry.timestamp < entry.ttl) {
      return {
        data: entry.data,
        status: 200,
        statusText: 'OK (cached)',
        headers: {},
        config: (config || {}) as any,
      };
    }
  }

  // 2. In-flight request deduplication: reuse active pending promise
  if (inFlightRequests.has(cacheKey)) {
    return inFlightRequests.get(cacheKey)! as Promise<AxiosResponse<T>>;
  }

  // 3. Initiate real network request
  const requestPromise = api.get<T>(url, config)
    .then((response) => {
      if (typeof window !== 'undefined' && ttlMs > 0 && response.status >= 200 && response.status < 300) {
        cacheStore.set(cacheKey, {
          data: response.data,
          timestamp: Date.now(),
          ttl: ttlMs,
        });
      }
      return response;
    })
    .finally(() => {
      inFlightRequests.delete(cacheKey);
    });

  inFlightRequests.set(cacheKey, requestPromise);
  return requestPromise;
}

/**
 * Invalidate cached API entries matching a substring/regex, or purge all cache if no argument provided.
 */
export function invalidateApiCache(pattern?: string | RegExp): void {
  if (!pattern) {
    cacheStore.clear();
    return;
  }
  for (const key of cacheStore.keys()) {
    if (typeof pattern === 'string' ? key.includes(pattern) : pattern.test(key)) {
      cacheStore.delete(key);
    }
  }
}

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

/** App home URL after logout (respects /cp subfolder on production). */
export function getAppHomeUrl(): string {
  const base = getAppBasePath();
  return base ? `${base}/` : '/';
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
