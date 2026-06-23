/** @type {import('next').NextConfig} */
const backendOrigin = process.env.BACKEND_URL || 'http://127.0.0.1:8080';
const phpBase = `${backendOrigin}/cp/index.php`;
const cpBase = `${backendOrigin}/cp`;
const isStaticExport = process.env.STATIC_EXPORT === '1';

/** @type {import('next').NextConfig} */
const nextConfig = {
  output: isStaticExport ? 'export' : undefined,
  trailingSlash: isStaticExport,
  // Hostinger serves this app from /cp — assets must use /cp/_next/ not /_next/
  basePath: isStaticExport ? '/cp' : undefined,
  assetPrefix: isStaticExport ? '/cp' : undefined,
  images: {
    unoptimized: isStaticExport,
  },
  experimental: {
    middlewareClientMaxBodySize: '50mb',
  },
  reactStrictMode: true,
};

if (!isStaticExport) {
  nextConfig.redirects = async () => [
    {
      source: '/property-detail/:slug',
      destination: '/property/:slug',
      permanent: true,
    },
  ];
}

if (!isStaticExport) {
  nextConfig.rewrites = async () => ({
    beforeFiles: [
      { source: '/api/nb/:path*', destination: `${phpBase}/api/nb/:path*` },
      { source: '/api/blogs', destination: `${phpBase}/api/blogs` },
      { source: '/api/blogs/:path*', destination: `${phpBase}/api/blogs/:path*` },
      { source: '/api/property/:path*', destination: `${phpBase}/api/property/:path*` },
      { source: '/api/mobile/:path*', destination: `${phpBase}/api/mobile/:path*` },
      { source: '/panel', destination: `${phpBase}/panel` },
      { source: '/panel/:path*', destination: `${phpBase}/panel/:path*` },
      { source: '/admin', destination: `${phpBase}/admin` },
      { source: '/admin/:path*', destination: `${phpBase}/admin/:path*` },
      { source: '/logout', destination: `${phpBase}/logout` },
    ],
    fallback: [
      { source: '/api/:path*', destination: `${phpBase}/api/:path*` },
      { source: '/uploads/:path*', destination: `${cpBase}/uploads/:path*` },
      { source: '/assets/:path*', destination: `${cpBase}/assets/:path*` },
      { source: '/promo_agent.png', destination: `${cpBase}/promo_agent.png` },
    ],
  });
}

module.exports = nextConfig;
