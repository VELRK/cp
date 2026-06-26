/** Post-login destination based on nb_users.role */
export function getDashboardPathForRole(role?: string | null): string {
  if (role === 'owner') return '/owner/dashboard';
  if (role === 'tenant' || role === 'customer') return '/tenant/dashboard';
  return '/';
}
