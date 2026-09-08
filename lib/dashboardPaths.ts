/** Post-login destination based on API role (agent uses owner panel). */
export function isOwnerLike(role?: string | null): boolean {
  return role === 'owner' || role === 'agent';
}

export function isTenantLike(role?: string | null): boolean {
  return role === 'tenant' || role === 'customer';
}

export function getDashboardPathForRole(role?: string | null): string {
  if (role === 'admin') {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('nb_token');
      return token
        ? `/panel/auth?token=${encodeURIComponent(token)}`
        : '/panel';
    }
    return '/panel';
  }
  if (isOwnerLike(role)) return '/owner/dashboard';
  if (isTenantLike(role)) return '/tenant/dashboard';
  return '/';
}
