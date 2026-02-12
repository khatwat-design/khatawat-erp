This is a [Next.js](https://nextjs.org) project bootstrapped with [`create-next-app`](https://nextjs.org/docs/app/api-reference/cli/create-next-app).

## Getting Started

First, run the development server:

```bash
npm run dev
# or
yarn dev
# or
pnpm dev
# or
bun dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser to see the result.

You can start editing the page by modifying `app/page.tsx`. The page auto-updates as you edit the file.

## Tajer Setup (Telegram + Tracking)

### 1) Environment variables
Create a `.env.local` file in the project root and add:

```
NEXT_PUBLIC_SITE_URL=http://localhost:3000
NEXT_PUBLIC_GA_ID=
NEXT_PUBLIC_META_PIXEL_ID=
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHANNEL_ID=
ADMIN_DASHBOARD_TOKEN=
DATABASE_URL=postgresql://USER:PASSWORD@HOST:PORT/DB
```

### 2) Telegram
- Create a bot via BotFather and copy the token.
- Add the bot as an admin in your Telegram group/channel.
- Set `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHANNEL_ID`.

### 3) Tracking (optional)
- Set `NEXT_PUBLIC_GA_ID` for Google Analytics.
- Set `NEXT_PUBLIC_META_PIXEL_ID` for Meta Pixel.

### 4) Database (PostgreSQL)
- Use a managed Postgres مثل Supabase أو Neon أو Railway.
- ضع رابط الاتصال في `DATABASE_URL`.
- شغّل:
```
npx prisma migrate dev --name init
npx prisma db seed
```

### 5) Run
```
npm run dev
```

### 6) Verify setup
Call the health endpoint to confirm all required env vars are present:
```
http://localhost:3000/api/health
```

## إعدادات تاجر (عربي)

### 1) ربط تلجرام
- أنشئ بوت من BotFather وخذ التوكن.
- أضف البوت كمشرف في المجموعة/القناة.
- ضع القيم في `.env.local`:
  - `TELEGRAM_BOT_TOKEN`
  - `TELEGRAM_CHANNEL_ID`
  - `ADMIN_DASHBOARD_TOKEN`

### 2) لوحة التحكم
- افتح `http://localhost:3000/admin/login`
- أدخل رمز الإدارة (`ADMIN_DASHBOARD_TOKEN`) لعرض الطلبات وإدارة المنتجات.

### 3) قاعدة البيانات
- نستخدم PostgreSQL لضمان تخزين دائم وقوي.
- ضع رابط الاتصال في `.env.local`:
  - `DATABASE_URL=postgresql://USER:PASSWORD@HOST:PORT/DB`
- ثم شغّل:
```
npx prisma migrate dev --name init
npx prisma db seed
```

### 4) التحقق
- افتح `http://localhost:3000/api/health`
- يجب أن يظهر `ok: true` عندما تكتمل الإعدادات.

This project uses [`next/font`](https://nextjs.org/docs/app/building-your-application/optimizing/fonts) to automatically optimize and load [Geist](https://vercel.com/font), a new font family for Vercel.

## Learn More

To learn more about Next.js, take a look at the following resources:

- [Next.js Documentation](https://nextjs.org/docs) - learn about Next.js features and API.
- [Learn Next.js](https://nextjs.org/learn) - an interactive Next.js tutorial.

You can check out [the Next.js GitHub repository](https://github.com/vercel/next.js) - your feedback and contributions are welcome!

## Deploy on Hostinger Business

The app is configured for **Hostinger Business** using the **Node.js selector** (standalone build). Static export is not used because the app has API routes (`/api/checkout`, `/api/health`, admin APIs).

### 1) Set your live Laravel URL

- Copy `.env.production.example` to `.env.production` (or set the same variables in Hostinger’s environment).
- Set **`NEXT_PUBLIC_API_URL`** to your **live Laravel API base URL** (no trailing slash), e.g. `https://api.yourdomain.com`.
- Set **`NEXT_PUBLIC_SITE_URL`** to your storefront URL, e.g. `https://store.yourdomain.com`.

### 2) Build

```bash
npm run build
```

This produces a **standalone** output (e.g. `.next/standalone` + `.next/static`). Use Hostinger’s Node.js app to run the standalone server (e.g. `node .next/standalone/server.js` or as per Hostinger’s docs).

### 3) Multi-tenant in production

Tenant is read from the **URL query only** (`?domain=tenant-slug`). It works the same on production:

- Open: `https://your-storefront.com/?domain=tenant-slug`
- All requests to Laravel send **`X-Store-Domain: tenant-slug`** and use **`NEXT_PUBLIC_API_URL`**.

Ensure your Laravel API allows CORS from your storefront origin and uses the `X-Store-Domain` header to resolve the tenant.

---

## Deploy on Vercel

The easiest way to deploy your Next.js app is to use the [Vercel Platform](https://vercel.com/new?utm_medium=default-template&filter=next.js&utm_source=create-next-app&utm_campaign=create-next-app-readme) from the creators of Next.js.

Check out our [Next.js deployment documentation](https://nextjs.org/docs/app/building-your-application/deploying) for more details.
