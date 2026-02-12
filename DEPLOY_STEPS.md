# أوامر النشر خطوة بخطوة

## افتراض: مشروع Laravel في `~/khatawat-erp/laravel_app` ومشروع المتجر في `~/khatawat-erp/storefront`
## غيّر المسارات إذا كانت مختلفة عندك

---

## الخطوة 1: الاتصال بالسيرفر عبر SSH

```bash
ssh root@187.77.68.2
```

(أو استخدم المستخدم الذي تستخدمه عادة)

---

## الخطوة 2: الانتقال لمجلد مشروع Laravel

```bash
cd ~/khatawat-erp/laravel_app
```

أو إذا كان المشروع في مسار آخر، مثلاً:

```bash
cd /var/www/khatawat-erp/laravel_app
```

---

## الخطوة 3: تحديث ملف .env في Laravel

```bash
nano .env
```

ابحث عن السطر `STOREFRONT_URL` أو أضفه في نهاية الملف:

```
STOREFRONT_URL=http://187.77.68.2:3000
```

احفظ الملف: `Ctrl+O` ثم `Enter` ثم `Ctrl+X`

---

## الخطوة 4: مسح كاش Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

---

## الخطوة 5: الانتقال لمجلد المتجر (Storefront)

```bash
cd ~/khatawat-erp/storefront
```

أو المسار الصحيح لمشروع المتجر عندك.

---

## الخطوة 6: تحديث متغيرات البيئة للمتجر

```bash
nano .env.production.local
```

إذا لم يكن الملف موجوداً:

```bash
nano .env.local
```

أضف أو عدّل هذه الأسطر:

```
NEXT_PUBLIC_API_URL=https://erp.khtwat.com
NEXT_PUBLIC_SITE_URL=http://187.77.68.2:3000
```

احفظ: `Ctrl+O` ثم `Enter` ثم `Ctrl+X`

---

## الخطوة 7: إعادة بناء المتجر

```bash
npm run build
```

---

## الخطوة 8: إعادة تشغيل المتجر (PM2)

```bash
pm2 restart storefront
```

أو إذا كان الاسم مختلفاً، اعرض القائمة أولاً:

```bash
pm2 list
```

ثم استخدم الاسم الصحيح:

```bash
pm2 restart <الاسم>
```

---

## الخطوة 9: التأكد من التشغيل

```bash
pm2 list
pm2 logs storefront
```

اضغط `Ctrl+C` للخروج من عرض اللوجات.

---

## ملخص المتغيرات

| المشروع | المتغير | القيمة |
|---------|---------|--------|
| Laravel | STOREFRONT_URL | http://187.77.68.2:3000 |
| Storefront | NEXT_PUBLIC_API_URL | https://erp.khtwat.com |
| Storefront | NEXT_PUBLIC_SITE_URL | http://187.77.68.2:3000 |
