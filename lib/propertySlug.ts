/** Slug used for the static-export fallback shell (see property/__build_placeholder__). */
export const PROPERTY_PLACEHOLDER_SLUG = '__build_placeholder__';

/** Read listing slug from a pathname like /cp/property/my-slug/ */
export function getPropertySlugFromPath(pathname?: string): string | null {
  const path = pathname ?? (typeof window !== 'undefined' ? window.location.pathname : '');
  const m = path.match(/\/property\/([^/]+)\/?$/);
  if (!m) return null;
  const slug = decodeURIComponent(m[1]);
  return slug === PROPERTY_PLACEHOLDER_SLUG ? null : slug;
}
