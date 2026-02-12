# Khatawat ERP — نظام ERP مركزي لمتاجر إلكترونية متعددة

معمارية النشر: **Hostinger (دومين)** → **Cloudflare (DNS + SSL)** → **GitHub** → **Laravel Forge** → **DigitalOcean**

---

## هيكل المشروع

```
├── laravel_app/          # Laravel 11 — API + لوحة Admin + لوحة التاجر
├── storefront/           # Next.js — قالب المتاجر (متعدد المستأجرين)
├── DEPLOYMENT.md         # دليل النشر الكامل
└── README.md
```

---

## التشغيل المحلي

### Laravel API

```bash
cd laravel_app
cp .env.example .env
php artisan key:generate
# إعداد قاعدة البيانات في .env
php artisan migrate
php artisan serve
```

### Storefront

```bash
cd storefront
cp ENV_EXAMPLE.txt .env.local
# NEXT_PUBLIC_API_URL=http://localhost:8000
npm install
npm run dev
```

افتح `http://localhost:3000?domain=SUBDOMAIN` (استبدل SUBDOMAIN بـ subdomain المتجر من قاعدة البيانات).

---

## النشر على الإنتاج

راجع **[DEPLOYMENT.md](./DEPLOYMENT.md)** للخطوات الكاملة.

---

## روابط سريعة

| البيئة | لوحة Admin | لوحة التاجر | API |
|--------|-----------|-------------|-----|
| محلي | http://localhost:8000/admin | http://localhost:8000/seller | http://localhost:8000/api |
| إنتاج | https://api.khatawat.com/admin | https://api.khatawat.com/seller | https://api.khatawat.com/api |
