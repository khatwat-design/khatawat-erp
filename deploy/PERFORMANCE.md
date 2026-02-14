# Performance & Deployment Guide

## Laravel (Production)

Run these after deployment or config changes:

```bash
# Route caching (reduces bootstrap time)
php artisan route:cache

# Config caching
php artisan config:cache

# View caching
php artisan view:cache

# Clear caches when needed (e.g. after code/config updates)
php artisan optimize:clear
```

**Queue:** Ensure the queue worker runs for jobs (e.g. Google Sheets push):

```bash
php artisan queue:work --tries=3
```

## Next.js (Production)

- **API caching:** `api.ts` uses in-memory stale-while-revalidate (60s TTL) for GET requests (store details, products, banners). No extra setup.
- **Static export:** If using `next export`, ensure `output: 'standalone'` or similar in `next.config.js` for optimal production builds.

### Optional: `generateStaticParams` for Product Pages

For SEO and faster first loads, you can pre-render product pages at build time. This requires fetching product IDs from the API during build:

```ts
// In app/products/[id]/page.tsx - add to the page if using SSR
export async function generateStaticParams() {
  // Fetch from API - requires a default/store domain at build time
  const products = await getProducts(process.env.BUILD_STORE_DOMAIN);
  return products.map((p) => ({ id: p.id }));
}
```

Set `BUILD_STORE_DOMAIN` in your build environment if you want to pre-render a specific store's products. Omit this if products are highly dynamic.

## Environment Variables

### Next.js Storefront

| Variable | Description |
|----------|-------------|
| `NEXT_PUBLIC_API_URL` | Laravel API base URL (e.g. `https://api.khatawat.com`) |
| `NEXT_PUBLIC_MAIN_DOMAIN` | Main SaaS domain for middleware (e.g. `khatawat.com`) |
| `NEXT_PUBLIC_STORE_API_KEY` | Optional; used when API key auth is needed |
