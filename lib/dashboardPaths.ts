import { getAdminPanelUrl } from './api';

/** Post-login destination based on API role (agent uses owner panel). */
export function isOwnerLike(role?: string | null): boolean {
  return role === 'owner' || role === 'agent';
}

export function isTenantLike(role?: string | null): boolean {
  return role === 'tenant' || role === 'customer';
}

type NextRouterLike = {
  push: (href: string) => void;
  replace: (href: string) => void;
};

export function getDashboardPathForRole(role?: string | null): string {
  if (role === 'admin') {
    return getAdminPanelUrl();
  }
  if (isOwnerLike(role)) return '/owner/dashboard';
  if (isTenantLike(role)) return '/tenant/dashboard';
  return '/';
}

/** Admin panel is PHP — must be a full page load, not Next.js client navigation. */
export function navigateToDashboardForRole(
  role?: string | null,
  router?: NextRouterLike,
  mode: 'push' | 'replace' = 'push'
): void {
  if (role === 'admin') {
    if (typeof window !== 'undefined') {
      window.location.assign(getAdminPanelUrl());
    }
    return;
  }
  const path = getDashboardPathForRole(role);
  if (router) {
    if (mode === 'replace') {
      router.replace(path);
    } else {
      router.push(path);
    }
    return;
  }
  if (typeof window !== 'undefined') {
    window.location.assign(path);
  }
}
