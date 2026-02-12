<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::get('/mobile', function () {
    return view('mobile-app');
})->name('mobile.app');

Route::get('/mobile/sw.js', function () {
    $sw = "const CACHE_NAME='khtwat-erp-v1';self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE_NAME).then(c=>c.addAll(['/mobile','/mobile/'])));self.skipWaiting()});self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(n=>Promise.all(n.filter(x=>x!==CACHE_NAME).map(x=>caches.delete(x)))));self.clients.claim()});self.addEventListener('fetch',e=>{if(e.request.mode==='navigate'){e.respondWith(fetch(e.request).catch(()=>caches.match('/mobile').then(r=>r||new Response('Offline',{status:503}))))}});";
    return response($sw, 200, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
});

Route::get('/mobile/manifest.json', function () {
    return response()->json([
        'name' => 'خطوات ERP',
        'short_name' => 'خطوات',
        'description' => 'تطبيق خطوات ERP للبائعين والإدارة',
        'start_url' => url('/mobile'),
        'display' => 'standalone',
        'orientation' => 'portrait',
        'background_color' => '#0F172A',
        'theme_color' => '#F97316',
        'dir' => 'rtl',
        'lang' => 'ar',
        'icons' => [
            ['src' => asset('images/logo.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => asset('images/logo.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ])->header('Content-Type', 'application/json');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/invoice/{order}', InvoiceController::class)->name('invoice.show');
});
