/** Fallback skyline/landmark images for Explore Cities cards. */
export const CITY_FALLBACK_IMAGES: Record<string, string> = {
  chennai: 'https://images.unsplash.com/photo-1587474265874-48c15c4c86af?auto=format&fit=crop&w=400&q=80',
  coimbatore: 'https://images.unsplash.com/photo-1597852074813-d004b0f79d47?auto=format&fit=crop&w=400&q=80',
  mumbai: 'https://images.unsplash.com/photo-1566552881560-a8d214433484?auto=format&fit=crop&w=400&q=80',
  bangalore: 'https://images.unsplash.com/photo-1598327100852-ed920340a2a2?auto=format&fit=crop&w=400&q=80',
  bengaluru: 'https://images.unsplash.com/photo-1598327100852-ed920340a2a2?auto=format&fit=crop&w=400&q=80',
  delhi: 'https://images.unsplash.com/photo-1582880426168-4a9ea0e7b9b1?auto=format&fit=crop&w=400&q=80',
  'new delhi': 'https://images.unsplash.com/photo-1582880426168-4a9ea0e7b9b1?auto=format&fit=crop&w=400&q=80',
  hyderabad: 'https://images.unsplash.com/photo-1596178060828-4d6878170482?auto=format&fit=crop&w=400&q=80',
  pune: 'https://images.unsplash.com/photo-1568602471122-7832631df4f5?auto=format&fit=crop&w=400&q=80',
  kolkata: 'https://images.unsplash.com/photo-1548013146-724f68d4a1f0?auto=format&fit=crop&w=400&q=80',
};

const GENERIC_CITY_IMAGE =
  'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=400&q=80';

function normalizeCityName(name: string): string {
  return name.trim().toLowerCase();
}

/** True when DB/API image looks like a real upload path or URL. */
export function isValidCityImage(image?: string | null): boolean {
  if (!image) return false;
  const value = image.trim();
  if (value.length < 8) return false;
  if (/^x+$/i.test(value)) return false;
  if (/^https?:\/\//i.test(value)) return true;
  return value.includes('assets/') || value.includes('uploads/') || value.includes('/');
}

const APP_BASE_PATH = '/cp';

/** Next dev (:3000) rewrites /uploads and /assets to PHP; production static site needs /cp prefix. */
function usesDevAssetProxy(): boolean {
  return process.env.NODE_ENV === 'development';
}

/** Normalize uploads/assets paths for the current environment. */
function normalizeAssetPath(pathname: string): string {
  let path = pathname.startsWith('/') ? pathname : `/${pathname}`;

  // Accept /cp/uploads/... or /uploads/... from API — use bare /assets|/uploads segment
  const underCp = path.match(/^\/cp(\/(?:assets|uploads)\/.+)$/i);
  if (underCp) {
    path = underCp[1];
  }

  if (path.startsWith('/assets/') || path.startsWith('/uploads/')) {
    if (usesDevAssetProxy()) {
      return path;
    }
    return `${APP_BASE_PATH}${path}`;
  }

  return path;
}

/** Convert PHP base_url paths to browser-loadable paths (handles /cp on production). */
export function toFrontendAssetUrl(image: string): string {
  const value = image.trim();
  if (!value) return value;

  if (/^https?:\/\//i.test(value)) {
    try {
      return normalizeAssetPath(new URL(value).pathname);
    } catch {
      return value;
    }
  }

  return normalizeAssetPath(value.startsWith('/') ? value : `/${value}`);
}

export function getCityFallbackImage(cityName: string): string {
  return CITY_FALLBACK_IMAGES[normalizeCityName(cityName)] || GENERIC_CITY_IMAGE;
}

/** Resolve display image: valid upload first, else city-specific fallback. */
export function resolveExploreCityImage(
  cityName: string,
  apiImage?: string | null
): string {
  if (isValidCityImage(apiImage)) {
    return toFrontendAssetUrl(apiImage!);
  }
  return getCityFallbackImage(cityName);
}
