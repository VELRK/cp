/**
 * Public URL prefix.
 * - Next.js dev (:3000/:3001): ''
 * - Production at domain root (coimbatoreproperties.org): ''
 * - Local XAMPP / old /cp host: '/cp'
 */
export function getAppBasePath(): string {
  if (typeof window !== 'undefined') {
    if (window.location.port === '3000' || window.location.port === '3001') {
      return '';
    }
    const path = window.location.pathname || '';
    if (path === '/cp' || path.startsWith('/cp/')) {
      return '/cp';
    }
    return '';
  }

  const env = process.env.NEXT_PUBLIC_APP_BASE_PATH;
  if (env !== undefined) {
    const trimmed = env.replace(/\/$/, '');
    return trimmed === '/' ? '' : trimmed;
  }

  const backend = process.env.NEXT_PUBLIC_BACKEND_URL || process.env.BACKEND_URL || '';
  try {
    if (backend) {
      const p = new URL(backend).pathname.replace(/\/$/, '');
      return p === '/' ? '' : p;
    }
  } catch {
    /* ignore */
  }
  return '';
}
