# قائمة التحقق: عرض المتجر بشكل صحيح

## المشكلة 1: زر "عرض المتجر" يوجّه لـ localhost

**الحل (تم تطبيقه):** الرابط يأتي الآن من `config('app.storefront_url')` أو `.env` باسم `STOREFRONT_URL`.

**على السيرفر:** تأكد من وجود `STOREFRONT_URL` في ملف `.env`:
```env
STOREFRONT_URL=https://store.khtwat.com
```
أو إذا كان المتجر على الـ IP:
```env
STOREFRONT_URL=http://187.77.68.2:3000
```

---

## المشكلة 2: لا يظهر الشعار والمنتجات

### أ. إعداد المتجر (Storefront) - Next.js

في مجلد المتجر (storefront)، أنشئ أو عدّل `.env.production.local`:

```env
NEXT_PUBLIC_API_URL=https://erp.khtwat.com
NEXT_PUBLIC_SITE_URL=https://store.khtwat.com
```

**مهم جداً:** `NEXT_PUBLIC_API_URL` يجب أن يشير إلى عنوان Laravel API (مثلاً `https://erp.khtwat.com`).

### ب. الـ subdomain الصحيح

عند زيارة المتجر استخدم نفس الـ subdomain الذي سجّلته عند إنشاء المتجر:

- مثال: إذا كان الـ subdomain هو `test` فالرابط الصحيح:
  ```
  https://store.khtwat.com/?domain=test
  ```
- إذا سجّلت المتجر بـ subdomain مختلف (مثل `mdry` أو `store1`) فاستخدمه بدل `test`.

يمكنك معرفة الـ subdomain من:
- رابط لوحة التاجر: `/app/{subdomain}/...`
- قاعدة البيانات: جدول `stores`، عمود `subdomain`

### ج. الشعار (Logo)

1. من لوحة التاجر: الإعدادات / البراندينغ
2. ارفع الشعار
3. تأكد حفظ الـ `branding_config` أو `logo_url` في قاعدة البيانات

### د. المنتجات

1. من لوحة التاجر: إضافة منتجات
2. تأكد أن حالة المنتج (status) = `active`
3. كل منتج مرتبط بالمتجر (store) الصحيح

### هـ. CORS

تمت إضافة المصادر التالية في `config/cors.php`:
- `https://store.khtwat.com`
- `http://store.khtwat.com`
- نمط `*.khtwat.com`

### و. اختبار الـ API يدوياً

للتحقق أن الـ API يرجع البيانات بشكل صحيح:

```bash
curl -H "X-Store-Domain: test" https://erp.khtwat.com/api/store
curl -H "X-Store-Domain: test" https://erp.khtwat.com/api/store/products
```

غيّر `test` إلى الـ subdomain الصحيح لمتجرك.

---

## بعد التعديلات

1. إعادة نشر Laravel إذا تم تعديل `.env` أو الكود.
2. إعادة بناء ونشر المتجر (Next.js):
   ```bash
   cd storefront
   npm run build
   pm2 restart storefront
   ```
3. مسح كاش Laravel:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
