# دليل نشر Khatawat ERP — DigitalOcean + Forge + Cloudflare + GitHub

هذا الدليل يطبق المعمارية المختارة:

| الطبقة | المزود | الدور |
|--------|--------|------|
| الدومين | Hostinger | khatawat.com — تغيير الـ Nameservers فقط |
| DNS & SSL | Cloudflare | التوجيه، SSL، Wildcard، الحماية |
| الكود | GitHub | 3 مستودعات |
| الأتمتة | Laravel Forge | السحب والـ Deploy |
| البنية | DigitalOcean | Droplet |

---

## 1. إعداد GitHub

### خيار أ: مستودعات منفصلة (موصى به)

| المستودع | المحتوى | المسار على السيرفر |
|----------|---------|-------------------|
| `khatawat-api` | Laravel ERP | `/home/forge/api.khatawat.com` |
| `khatawat-storefront` | Next.js | `/home/forge/store.khatawat.com` |

```bash
# 1. إنشاء Repo فارغ: khatawat-api
cd laravel_app
git init
git remote add origin https://github.com/YOUR_USERNAME/khatawat-api.git
git add .
git commit -m "Initial commit"
git branch -M main
git push -u origin main

# 2. إنشاء Repo: khatawat-storefront
cd ../storefront
git init
git remote add origin https://github.com/YOUR_USERNAME/khatawat-storefront.git
git add .
git commit -m "Initial commit"
git branch -M main
git push -u origin main
```

### خيار ب: مستودع واحد (Monorepo)

```bash
# من جذر المشروع (المجلد الذي يحتوي laravel_app و storefront)
git init
git remote add origin https://github.com/YOUR_USERNAME/khatawat.git
git add .
git commit -m "Initial commit"
git branch -M main
git push -u origin main
```

في Forge — عند إنشاء الـ Site:
- **Laravel**: اختر المستودع → **Web Directory** = `laravel_app/public`
- **Storefront**: اختر نفس المستودع → **Root Directory** = `storefront`

---

## 2. DigitalOcean — إنشاء Droplet

1. سجّل دخولك إلى [DigitalOcean](https://digitalocean.com)
2. **Create** → **Droplets**
3. الاختيارات:
   - **Image**: Ubuntu 24.04 LTS
   - **Plan**: Basic — **2 GB RAM** أو أكثر (يفضّل 2GB لـ Laravel + MySQL)
   - **Region**: اختر الأقرب للمستخدمين
   - **Authentication**: SSH Key
4. اسم الـ Droplet: مثلاً `khatawat-prod`

---

## 3. Laravel Forge

### ربط الحسابات

1. [forge.laravel.com](https://forge.laravel.com) → **Account** → **Server Providers**
2. اربط **DigitalOcean** بحسابك
3. **Account** → **Source Control** → اربط **GitHub**

### إنشاء السيرفر

1. **Servers** → **Create Server**
2. **Provider**: DigitalOcean
3. **Server**: اختر الـ Droplet الذي أنشأته (أو أنشئ سيرفراً جديداً من Forge)
4. انتظر انتهاء الإعداد (PHP, Nginx, MySQL، إلخ)

### إنشاء Site — Laravel API

1. **Sites** → **New Site**
2. **Domain**: `api.khatawat.com`
3. **Project Type**: Laravel
4. **Web Directory**: `/public`
5. **Create Site**
6. **Repository**: اختر `khatawat-api`، الفرع `main`
7. **Deploy Now**

### Deploy Script (Laravel)

في **Sites** → اختر الموقع → **Deployment**، استخدم:

```bash
cd /home/forge/api.khatawat.com
git pull origin $FORGE_SITE_BRANCH
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload
) 9>/tmp/fpmlock
if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    $FORGE_PHP artisan queue:restart
fi
```

> ملاحظة: المسار `api.khatawat.com` هو افتراضي في Forge. تأكد من أن مسار الدليل يطابق موقعك.

### إعداد Environment Variables

**Sites** → الموقع → **Environment**:

انسخ محتوى `laravel_app/.env.forge.example` وعدّل القيم:

- `APP_KEY`: `php artisan key:generate --show`
- `APP_URL`: `https://api.khatawat.com`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: حسب قاعدة البيانات

### إنشاء قاعدة البيانات

**Servers** → السيرفر → **Database** → **Create Database**

- Name: `khatawat`
- User: `forge` (أو مستخدم مخصّص)
- كوّن كلمة المرور واحفظها في `.env`

### SSL

**Sites** → الموقع → **SSL** → **Let's Encrypt** → تفعيل

---

## 4. Cloudflare

### إضافة الموقع

1. [dash.cloudflare.com](https://dash.cloudflare.com) → **Add Site**
2. أدخل `khatawat.com`
3. اختر خطة (Free كافية)

### تغيير Nameservers (Hostinger)

1. Hostinger → لوحة تحكم الدومين → **Nameservers**
2. غيّر إلى الـ Nameservers التي يعرضها Cloudflare (مثل `xxx.ns.cloudflare.com`)

### إعداد DNS

| النوع | الاسم | المحتوى | Proxy |
|-------|-------|---------|-------|
| A | `api` | IP الـ Droplet | برتقالي (Proxied) |
| A | `store` أو `*` | IP الـ Droplet | برتقالي |
| A | `@` | IP الـ Droplet | برتقالي |

> `*` = Wildcard لجميع المتاجر الفرعية (مثل `store1.khatawat.com`)

### SSL في Cloudflare

**SSL/TLS** → **Overview** → **Full (strict)**

---

## 5. نشر Storefront (Next.js)

### خيار أ: Forge (Node.js Site)

1. **Sites** → **New Site**
2. **Domain**: `store.khatawat.com` (أو `*.khatawat.com` عبر Wildcard)
3. **Project Type**: **Static** أو **Node**
4. **Repository**: `khatawat-storefront`

للـ Node:

1. **Build Command**: `npm ci && npm run build`
2. **Start Command**: `npm start`
3. **Node Version**: 20
4. **Output Directory**: `.next` (للـ Static: `out` إذا استخدمت `next export`)

### خيار ب: Vercel (للـ Next.js)

1. [vercel.com](https://vercel.com) → استيراد `khatawat-storefront`
2. **Environment Variables**:
   - `NEXT_PUBLIC_API_URL` = `https://api.khatawat.com`
   - `NEXT_PUBLIC_SITE_URL` = `https://store.khatawat.com`
3. نشر وإضافة الدومين من Vercel

### متغيرات Storefront للإنتاج

في `.env.production` أو في Forge/Vercel:

```env
NEXT_PUBLIC_API_URL=https://api.khatawat.com
NEXT_PUBLIC_SITE_URL=https://store.khatawat.com
NEXT_PUBLIC_STORE_API_KEY=
```

---

## 6. CORS في Laravel

تحديث `laravel_app/config/cors.php`:

```php
'allowed_origins' => [
    'https://khatawat.com',
    'https://www.khatawat.com',
    'https://store.khatawat.com',
],
'allowed_origins_patterns' => [
    '#^https://[a-z0-9-]+\.khatawat\.com$#',
],
```

---

## 7. الروابط والتكامل

| العنصر | الرابط |
|--------|--------|
| لوحة Admin | `https://api.khatawat.com/admin` |
| لوحة التاجر | `https://api.khatawat.com/seller` |
| API | `https://api.khatawat.com/api/storefront/*` |
| متجر تجريبي | `https://store.khatawat.com?domain=SUBDOMAIN` |

---

## 8. سير العمل اليومي

```
تطوير في Cursor → git push → GitHub
                              ↓
                    Forge يسحب ويشغّل Deploy تلقائياً
```

لتفعيل **Quick Deploy** في Forge: **Sites** → **Deployment** → **Enable Quick Deploy**

---

## 9. استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| 502 Bad Gateway | تأكد من تشغيل PHP-FPM و Nginx |
| Store not found | تحقق من `subdomain`/`domain` في جدول `stores` |
| CORS error | راجع `config/cors.php` والدومين في `allowed_origins` |
| Migrations fail | راجع بيانات الاتصال بقاعدة البيانات في `.env` |
