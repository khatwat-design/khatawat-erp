<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="theme-color" content="#F97316" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="خطوات ERP" />
  <meta name="mobile-web-app-capable" content="yes" />
  <title>خطوات ERP</title>
  <link rel="manifest" href="{{ url('/mobile/manifest.json') }}" />
  <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #F97316;
      --primary-dark: #EA580C;
      --bg: #0F172A;
      --bg-card: #1E293B;
      --text: #F8FAFC;
      --text-muted: #94A3B8;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Tajawal', sans-serif;
      background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      padding-top: max(1.5rem, env(safe-area-inset-top));
      padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
      color: var(--text);
      overflow-x: hidden;
      -webkit-tap-highlight-color: transparent;
    }
    .logo-section { text-align: center; margin-bottom: 2.5rem; }
    .logo-icon {
      width: 80px; height: 80px;
      background: linear-gradient(145deg, var(--primary) 0%, var(--primary-dark) 100%);
      border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2.5rem;
      box-shadow: 0 10px 40px rgba(249, 115, 22, 0.35);
    }
    .logo-title { font-size: 1.75rem; font-weight: 800; }
    .logo-subtitle { font-size: 0.9rem; color: var(--text-muted); margin-top: 0.25rem; }
    .panel-cards { display: flex; flex-direction: column; gap: 1rem; width: 100%; max-width: 340px; }
    .panel-btn {
      display: flex; align-items: center; gap: 1rem;
      padding: 1.25rem 1.5rem;
      background: var(--bg-card);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      color: var(--text);
      font-family: inherit; font-size: 1.1rem; font-weight: 600;
      text-align: right; cursor: pointer;
      transition: all 0.2s ease; text-decoration: none;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .panel-btn:active { transform: scale(0.98); }
    .panel-btn.seller {
      background: linear-gradient(135deg, rgba(249,115,22,0.2) 0%, rgba(234,88,12,0.15) 100%);
      border-color: rgba(249,115,22,0.4);
    }
    .panel-btn.admin {
      background: linear-gradient(135deg, rgba(59,130,246,0.2) 0%, rgba(37,99,235,0.15) 100%);
      border-color: rgba(59,130,246,0.4);
    }
    .panel-icon {
      width: 48px; height: 48px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; flex-shrink: 0;
    }
    .panel-btn.seller .panel-icon { background: rgba(249,115,22,0.25); }
    .panel-btn.admin .panel-icon { background: rgba(59,130,246,0.25); }
    .panel-desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; }
    .footer { margin-top: 2rem; font-size: 0.8rem; color: var(--text-muted); }
    .hidden { display: none !important; }
    #webview-container {
      position: fixed; inset: 0; z-index: 100;
      background: var(--text);
      display: none; flex-direction: column;
    }
    #webview-container.active { display: flex; }
    #webview-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.85rem 1rem;
      padding-top: max(0.85rem, calc(env(safe-area-inset-top) + 0.5rem));
      background: var(--bg); color: var(--text); flex-shrink: 0;
      min-height: 56px;
    }
    #webview-back {
      background: var(--bg-card); border: none; color: var(--text);
      padding: 0.5rem 1rem; border-radius: 10px;
      font-family: inherit; font-size: 0.95rem; cursor: pointer;
    }
    #webview-title { font-weight: 600; }
    #webview-frame { flex: 1; width: 100%; border: none; background: #fff; }
  </style>
</head>
<body>
  <div id="home">
    <div class="install-hint ios" id="installHint" style="display:none;background:rgba(249,115,22,0.2);border:1px solid rgba(249,115,22,0.5);border-radius:12px;padding:0.75rem 1rem;margin-bottom:1.5rem;font-size:0.85rem;text-align:center">للإضافة كتطبيق: اضغط زر المشاركة ⎋ ثم «إضافة إلى الشاشة الرئيسية»</div>
    <div class="logo-section">
      <div class="logo-icon">📦</div>
      <h1 class="logo-title">خطوات ERP</h1>
      <p class="logo-subtitle">اختر اللوحة للدخول</p>
    </div>
    <div class="panel-cards">
      <button type="button" class="panel-btn seller" data-url="{{ config('app.url') }}/app" data-title="لوحة التاجر">
        <span class="panel-icon">🛒</span>
        <span>
          <span>لوحة التاجر</span>
          <p class="panel-desc">إدارة المنتجات والطلبات والمتجر</p>
        </span>
      </button>
      <button type="button" class="panel-btn admin" data-url="{{ config('app.url') }}/admin" data-title="لوحة الإدارة">
        <span class="panel-icon">⚙️</span>
        <span>
          <span>لوحة الإدارة</span>
          <p class="panel-desc">إدارة المنصة والمستخدمين والمتاجر</p>
        </span>
      </button>
    </div>
    <p class="footer">خطوات ERP © 2025</p>
  </div>
  <div id="webview-container">
    <header id="webview-header">
      <button type="button" id="webview-back">← رجوع</button>
      <span id="webview-title">خطوات ERP</span>
      <span style="width: 70px;"></span>
    </header>
    <iframe id="webview-frame" title="ERP Panel"></iframe>
  </div>
  <script>
    (function() {
      const HOME = document.getElementById('home');
      const CONTAINER = document.getElementById('webview-container');
      const FRAME = document.getElementById('webview-frame');
      const TITLE = document.getElementById('webview-title');
      const BACK_BTN = document.getElementById('webview-back');
      document.querySelectorAll('.panel-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          FRAME.src = this.getAttribute('data-url');
          TITLE.textContent = this.getAttribute('data-title');
          HOME.classList.add('hidden');
          CONTAINER.classList.add('active');
        });
      });
      BACK_BTN.addEventListener('click', function() {
        FRAME.src = 'about:blank';
        HOME.classList.remove('hidden');
        CONTAINER.classList.remove('active');
      });
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{{ url("/mobile/sw.js") }}', { scope: '/mobile/' }).catch(function(){});
      }
      var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
      var isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
      if (isIOS && !isStandalone) document.getElementById('installHint').style.display = 'block';
    })();
  </script>
</body>
</html>
