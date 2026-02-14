# Upload Limits Configuration (10MB)

This project allows merchants to upload high-quality images up to **10MB** for Products, Banners, and Logos.

## Quick Setup

1. **Shared / VPS (PHP-FPM, CGI):** The `public/.user.ini` file is already configured. It takes effect after PHP restarts or the request interval (often ~5 minutes).

2. **Apache with mod_php:** The `public/.htaccess` includes the necessary directives.

3. **Root access (php.ini):** Apply the settings below directly in your `php.ini`.

---

## php.ini Settings (Root Access)

If you have root/SSH access to the server, add or update these in `php.ini`:

```ini
upload_max_filesize = 12M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 120
```

**Note:** `post_max_size` should be slightly larger than `upload_max_filesize` (12M) to accommodate form data and headers.

---

## Livewire Configuration

Filament uses Livewire for file uploads. Livewire's default temporary upload limit is **12MB** (`max:12288` in KB). With `'rules' => null` in `config/livewire.php`, this default is used—no change needed.

To customize, publish and edit the config:

```bash
php artisan livewire:publish --config
```

Then in `config/livewire.php`, under `temporary_file_upload.rules`:

```php
'rules' => ['required', 'file', 'max:12288'],  // 12MB (optional; null uses this default)
```

---

## Verify

After applying changes, create a `phpinfo.php` in `public/` temporarily:

```php
<?php phpinfo(); ?>
```

Check `upload_max_filesize` and `post_max_size`. Remove the file after verification for security.
