/** @type {import('next').NextConfig} */
const backendUrl = (process.env.BACKEND_URL || process.env.NEXT_PUBLIC_BACKEND_URL || 'http://127.0.0.1:8080/cp').replace(/\/$/, '');
const phpBase = `${backendUrl}/index.php`;
const assetBase = backendUrl;
const isStaticExport = process.env.STATIC_EXPORT === '1';

function urlPathPrefix(url) {
  try {
    const p = new URL(url).pathname.replace(/\/$/, '');
    return p === '/' ? '' : p;
  } catch {
    return '';
  }
}

const envBase = process.env.NEXT_PUBLIC_APP_BASE_PATH;
const appBasePath = envBase !== undefined
  ? String(envBase).replace(/\/$/, '').replace(/^\/$/, '')
  : urlPathPrefix(backendUrl);
const useSubfolder = isStaticExport && appBasePath !== '';

/** @type {import('next').NextConfig} */
const nextConfig = {
  output: isStaticExport ? 'export' : undefined,
  trailingSlash: isStaticExport,
  // Subfolder installs (local XAMPP /cp) set basePath; domain-root production does not.
  basePath: useSubfolder ? appBasePath : undefined,
  assetPrefix: useSubfolder ? appBasePath : undefined,
  images: {
    unoptimized: isStaticExport,
  },
  experimental: {
    middlewareClientMaxBodySize: '50mb',
  },
  reactStrictMode: true,
  webpack: (config, { isServer }) => {
    // Keep auth context singleton — duplicate bundles break useAuth on property/search pages.
    if (!isServer) {
      config.optimization = config.optimization || {};
      config.optimization.splitChunks = config.optimization.splitChunks || {};
      config.optimization.splitChunks.cacheGroups = config.optimization.splitChunks.cacheGroups || {};

      config.optimization.splitChunks.cacheGroups.authContextStore = {
        test: /[\\/]lib[\\/]auth-context-store(\.|$)/,
        name: 'auth-context-store',
        chunks: 'all',
        enforce: true,
        priority: 40,
      };
    }
    return config;
  },
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
      { source: '/cp/panel', destination: `${phpBase}/panel` },
      { source: '/cp/panel/:path*', destination: `${phpBase}/panel/:path*` },
      { source: '/admin', destination: `${phpBase}/admin` },
      { source: '/admin/:path*', destination: `${phpBase}/admin/:path*` },
      { source: '/cp/admin', destination: `${phpBase}/admin` },
      { source: '/cp/admin/:path*', destination: `${phpBase}/admin/:path*` },
      { source: '/logout', destination: `${phpBase}/logout` },
      { source: '/cp/logout', destination: `${phpBase}/logout` },
      { source: '/cp/property/:slug*', destination: '/property/:slug*' },
    ],
    fallback: [
      { source: '/api/:path*', destination: `${phpBase}/api/:path*` },
      { source: '/uploads/:path*', destination: `${assetBase}/uploads/:path*` },
      { source: '/assets/:path*', destination: `${assetBase}/assets/:path*` },
      { source: '/cp/uploads/:path*', destination: `${assetBase}/uploads/:path*` },
      { source: '/cp/assets/:path*', destination: `${assetBase}/assets/:path*` },
      { source: '/promo_agent.png', destination: `${assetBase}/promo_agent.png` },
    ],
  });
}

module.exports = nextConfig;
