/** @type {import('next').NextConfig} */
const nextConfig = {
  experimental: {
    middlewareClientMaxBodySize: '50mb',
  },
  reactStrictMode: true,
  async rewrites() {
    return {
      fallback: [
        {
          source: '/api/:path*',
          destination: 'http://127.0.0.1/cp/index.php/api/:path*',
        },
        {
          source: '/uploads/:path*',
          destination: 'http://127.0.0.1/cp/uploads/:path*',
        },
        {
          source: '/assets/:path*',
          destination: 'http://127.0.0.1/cp/assets/:path*',
        },
        {
          source: '/panel/:path*',
          destination: 'http://127.0.0.1/cp/index.php/panel/:path*',
        },
        {
          source: '/admin/:path*',
          destination: 'http://127.0.0.1/cp/index.php/admin/:path*',
        },
      ]
    };
  },
};

module.exports = nextConfig;
