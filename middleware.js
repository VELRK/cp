import { NextResponse } from 'next/server';

/**
 * Forward the browser Host to PHP as X-Forwarded-Host so CodeIgniter base_url
 * stays on localhost:3000 (not .env BASE_URL :8080/cp) for panel form saves.
 */
export function middleware(request) {
  const requestHeaders = new Headers(request.headers);
  const host = request.headers.get('host');
  if (host) {
    requestHeaders.set('x-forwarded-host', host);
  }
  return NextResponse.next({
    request: {
      headers: requestHeaders,
    },
  });
}

export const config = {
  matcher: ['/panel/:path*', '/api/:path*', '/admin/:path*'],
};
