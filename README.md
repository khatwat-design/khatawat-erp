# خطوات ERP | Khatawat ERP

نظام إدارة موارد مؤسسية مركزي (ERP) لإدارة متاجر إلكترونية متعددة، مع لوحات تحكم للتجار والإدارة، ومتجر Next.js متعدد المستأجرين.

---

## جدول المحتويات

- [نظرة عامة](#نظرة-عامة)
- [البنية المعمارية](#البنية-المعاركية)
- [المتطلبات التقنية](#المتطلبات-التقنية)
- [هيكل المشروع](#هيكل-المشروع)
- [قاعدة البيانات](#قاعدة-البيانات)
- [لوحات التحكم](#لوحات-التحكم)
- [API](#api)
- [المتجر (Storefront)](#المتجر-storefront)
- [التكامل المتعدد المستأجرين](#التكامل-المتعدد-المستأجرين)
- [الإعداد والنشر](#الإعداد-والنشر)
- [خريطة التطوير](#خريطة-التطوير)

---

## نظرة عامة

**خطوات ERP** هو نظام SaaS يسمح بـ:

1. **إدارة مركزية**: إدارة متاجر، منتجات، طلبات، اشتراكات من لوحة واحدة
2. **لوحة تاجر**: كل تاجر يدير متجره (منتجات، طلبات، إعلانات، كوبونات، دعم فني)
3. **متجر موحد**: قالب Next.js واحد يعرض بيانات كل متجر حسب المستأجر (`?domain=xxx`)
4. **API أولاً**: المتجر يجلب المنتجات والطلبات عبر Laravel API

---

## البنية المعمارية

```
┌─────────────────────────────────────────────────────────────────┐
│                        خطوات ERP                                 │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │ لوحة الإدارة  │  │ لوحة التاجر  │  │ متجر Next.js          │  │
│  │ /admin       │  │ /app         │  │ (متعدد المستأجرين)    │  │
│  │ Filament     │  │ Filament     │  │ storefront            │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘  │
│         │                 │                      │               │
│         └─────────────────┼──────────────────────┘               │
│                           ▼                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              Laravel 11 Backend (API + Filament)          │   │
│  │  • IdentifyTenant (X-Store-Domain / ?domain= / hostname)  │   │
│  │  • MySQL / SQLite                                         │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## المتطلبات التقنية

| المكون | الإصدار |
|--------|---------|
| PHP | ^8.2 |
| Laravel | ^11.0 |
| MySQL / SQLite | - |
| Node.js | 18+ |
| Next.js | 16.x |
| Filament | v3 |
| Prisma | 6.x (Storefront) |

---

## هيكل المشروع

```
erp khtwat/
├── laravel_app/          # الباكند (Laravel ERP)
│   ├── app/
│   │   ├── Filament/
│   │   │   ├── Admin/    # لوحة الإدارة
│   │   │   └── Seller/   # لوحة التاجر
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/StorefrontController.php
│   │   │   │   ├── StoreApiController.php
│   │   │   │   └── OrderController.php
│   │   │   └── Middleware/IdentifyTenant.php
│   │   └── Models/
│   ├── config/
│   ├── database/migrations/
│   └── routes/api.php
│
├── storefront/           # المتجر (Next.js)
│   ├── src/
│   │   ├── app/          # صفحات App Router
│   │   ├── components/
│   │   └── lib/          # api.ts, use-products, use-banners
│   ├── store-config.json
│   └── prisma/
│
├── ROADMAP_V2.md
├── ROADMAP_V3.md
└── README.md             # هذا الملف
```

---

## قاعدة البيانات

### النماذج الرئيسية

| الجدول | الوصف |
|--------|-------|
| `users` | المستخدمون (إدارة، تجار) |
| `stores` | المتاجر (subdomain, domain, api_key, branding_config, integrations_config) |
| `products` | المنتجات (store_id, name, price, image_url, stock) |
| `orders` | الطلبات (store_id, customer_*, total_amount, order_details JSON, status) |
| `order_items` | عناصر الطلب |
| `order_status_history` | سجل تغيير حالات الطلب |
| `subscription_plans` | باقات الاشتراك |
| `store_payments` | مدفوعات المتاجر (يدوي، زين كاش) |
| `support_tickets` | تذاكر الدعم الفني |
| `support_ticket_replies` | ردود التذاكر |
| `banners` | إعلانات البانر (store_id, image_url, link, position) |
| `coupons` | كوبونات الخصم (store_id, code, discount_type, discount_value) |
| `broadcasts` | التعميمات من الإدارة |
| `wallet_transactions` | معاملات المحفظة |
| `shipping_settings` | إعدادات الشحن |
| `imports` / `exports` | سجل الاستيراد والتصدير |

### علاقات النماذج

- **Store**: `hasMany` Products, Orders, Banners, Coupons, SupportTickets
- **Product**: `belongsTo` Store
- **Order**: `belongsTo` Store, `hasMany` OrderItems, `hasMany` StatusHistory
- **SupportTicket**: `belongsTo` Store, User; `hasMany` Replies

---

## لوحات التحكم

### لوحة الإدارة (Super Admin) — `/admin`

| القسم | الموارد | الوصف |
|-------|---------|-------|
| الإدارة | المتاجر، المستخدمون | إدارة المتاجر والمستخدمين |
| الاشتراكات | باقات الاشتراك، المدفوعات | إدارة الباقات وموافقة المدفوعات |
| الدعم | تذاكر الدعم | استلام، رد، تعيين، تغيير حالة التذاكر |
| التشغيل | التعميمات | إرسال تعميمات للتجار |

**المميزات:**
- عدادات النمو والحالة المالية
- تصدير الطلبات والمتاجر (Excel/CSV)
- الموافقة على الدومينات المخصصة
- عند موافقة الدفعة → تحديث اشتراك المتجر تلقائياً

### لوحة التاجر (Seller) — `/app`

| القسم | الموارد | الوصف |
|-------|---------|-------|
| المتجر | المنتجات، الطلبات | إدارة المنتجات والطلبات |
| التسويق | الإعلانات، الكوبونات | بانرات وكوبونات |
| المساعدة | الدعم الفني | فتح تذاكر ومتابعة الردود |
| الشحن | إعدادات الشحن | تكلفة الشحن والمحافظات |
| المالية | المحفظة، الاشتراك | باقتك، تجديد الاشتراك، رفع وصل |
| الإعدادات | المظهر، الدومين، التكاملات | ثيم، دومين مخصص، Telegram، Pixels |

**حالات الطلب:** pending, confirmed, processing, ready_to_ship, with_delivery, shipped, delivered, completed, partial_return, full_return, cancelled

---

## API

### Store API (`/api/store/*`)

يُستخدم مع `X-Store-API-Key` أو `X-Store-Domain` أو `?domain=xxx` لتحديد المستأجر.

| الطريقة | المسار | الوصف |
|---------|--------|-------|
| GET | `/api/store` | إعدادات المتجر (اسم، لوجو، ألوان، عملة) |
| GET | `/api/store/products` | قائمة المنتجات |
| GET | `/api/store/products/{id}` | تفاصيل منتج |
| GET | `/api/store/banners` | قائمة البانرات النشطة |
| POST | `/api/store/orders` | إنشاء طلب (نموذج قديم) |

### Storefront API (`/api/storefront/*`)

**يتطلب** تحديد المستأجر عبر `X-Store-Domain` أو `?domain=` أو `Referer` أو hostname.

| الطريقة | المسار | الوصف |
|---------|--------|-------|
| GET | `/api/storefront/settings` | إعدادات المتجر |
| GET | `/api/storefront/products` | المنتجات (صيغة متوافقة مع المتجر) |
| POST | `/api/storefront/validate-coupon` | التحقق من كود الخصم |
| POST | `/api/storefront/orders` | إنشاء طلب مع دعم الكوبون |

### SaaS

| الطريقة | المسار | الوصف |
|---------|--------|-------|
| POST | `/api/saas/register` | تسجيل تاجر جديد |

---

## المتجر (Storefront)

### التقنيات

- **Next.js 16** (App Router, Turbopack)
- **React 19**
- **Tailwind CSS 4**
- **Zustand** (سلة التسوق)
- **Prisma** (اختياري، لبعض الميزات المحلية)

### الصفحات

| المسار | الوصف |
|--------|-------|
| `/` | الصفحة الرئيسية (منتجات + بانرات) |
| `/products` | قائمة المنتجات |
| `/products/[id]` | صفحة المنتج |
| `/cart` | السلة |
| `/checkout` | إتمام الطلب (مع كود الخصم) |
| `/thank-you` | صفحة الشكر بعد الطلب |
| `/admin/*` | لوحة إدارة محلية (إن وُجدت) |

### المتغيرات البيئية (Storefront)

```env
# مطلوب
NEXT_PUBLIC_API_URL=https://api.example.com
NEXT_PUBLIC_STORE_API_KEY=

# اختياري
NEXT_PUBLIC_SITE_URL=https://store.example.com
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHANNEL_ID=
```

### Store Context

- `domain`: من `?domain=xxx` أو hostname
- `domainQuery`: `?domain=xxx` لإلحاقه بالروابط
- جميع طلبات API ترسل `X-Store-Domain`

---

## التكامل المتعدد المستأجرين

### تحديد المستأجر (IdentifyTenant)

1. **X-Store-Domain** (هيدر الطلب)
2. **?domain=xxx** (استعلام URL)
3. **Referer** (من query string المرجع)
4. **Hostname** (مثل custom_domain)

### الحقول في `stores`

| الحقل | الاستخدام |
|-------|-----------|
| `subdomain` | `?domain=subdomain` |
| `domain` | دومين بديل |
| `custom_domain` | دومين مخصص (مثل shop.example.com) |
| `api_key` | للمصادقة بـ X-Store-API-Key |

---

## الإعداد والنشر

### Laravel (التطوير)

```bash
cd laravel_app
cp .env.example .env
php artisan key:generate
# تعديل DB_CONNECTION وبيانات الاتصال
php artisan migrate
php artisan db:seed --class=SubscriptionPlansSeeder
php artisan serve
```

### Laravel (النشر على السيرفر)

```bash
cd /var/www/laravel_app
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan db:seed --class=SubscriptionPlansSeeder --force
```

### Storefront (التطوير)

```bash
cd storefront
cp ENV_EXAMPLE.txt .env.local
# تعديل NEXT_PUBLIC_API_URL
npm install
npm run dev
```

### Storefront (النشر)

```bash
cd /var/www/storefront
git pull origin master   # أو: git fetch origin master && git reset --hard origin/master
npm install
npm run build
pm2 restart storefront
```

### المستودعات

- **Laravel ERP:** https://github.com/khatwat-design/khatawat-erp  
- **Storefront:** https://github.com/khatwat-design/khatawat-storefront  

---

## خريطة التطوير

### مكتمل (V2 + V3)

- لوحات الإدارة والتاجر
- مراحل الطلب وسجل التغييرات
- طباعة الفاتورة
- الدومين المخصص
- Telegram للإشعارات
- تصدير الطلبات والمتاجر
- نظام الاشتراكات والمدفوعات
- نظام الدعم الفني
- البانرات والكوبونات
- إعدادات زين كاش (جاهز للتكامل)

### مخطط (ROADMAP_V3)

- [ ] إدارة الموظفين والصلاحيات
- [ ] تحقق تلقائي من الدومين (DNS)
- [ ] إعدادات API لشركات الشحن
- [ ] Activity Log
- [ ] تقارير وإحصائيات متقدمة

---

## الدعم والتواصل

- **المشروع:** خطوات ERP (Khatawat)
- **الواجهة:** عربية (RTL)
- **الألوان:** برتقالي (#F97316)، أسود، أبيض، رمادي

---

## أوامر سريعة للنشر

### الدخول للسيرفر

```bash
ssh root@YOUR_SERVER_IP
```

### تحديث Laravel

```bash
cd /var/www/laravel_app
git fetch origin master
git reset --hard origin/master
composer dump-autoload
```

### تحديث Storefront

```bash
cd /var/www/storefront
git fetch origin master
git reset --hard origin/master
npm install
npm run build
pm2 restart storefront
```

---

## حالات الطلب (Order Status)

| الحالة | الوصف |
|--------|-------|
| pending | قيد الانتظار |
| confirmed | تم التأكيد |
| processing | قيد التحضير |
| ready_to_ship | جاهز للشحن |
| with_delivery | مع المندوب |
| shipped | تم الشحن |
| delivered | تم التوصيل |
| completed | مكتمل |
| partial_return | مراجع جزئي |
| full_return | مراجع كلي |
| cancelled | ملغي |

---

*آخر تحديث: حسب ROADMAP_V3*
