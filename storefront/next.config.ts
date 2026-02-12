import type { NextConfig } from "next";

// Allow images from production API when NEXT_PUBLIC_API_URL is set (e.g. in .env.production)
function getApiImagePatterns() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL;
  if (!apiUrl) return [];
  try {
    const u = new URL(apiUrl);
    return [
      { protocol: "https" as const, hostname: u.hostname },
      { protocol: "http" as const, hostname: u.hostname },
    ];
  } catch {
    return [];
  }
}

const nextConfig: NextConfig = {
  /**
   * Hostinger Business: use "standalone" with Node.js selector (recommended).
   * Static export (output: 'export') is not possible while /api routes exist (checkout, admin, health).
   */
  output: "standalone",
  trailingSlash: false,
  images: {
    unoptimized: true,
    remotePatterns: [
      { protocol: "http", hostname: "127.0.0.1", port: "8000" },
      { protocol: "http", hostname: "localhost", port: "8000" },
      { protocol: "https", hostname: "placehold.co" },
      ...getApiImagePatterns(),
    ],
  },
};

export default nextConfig;
