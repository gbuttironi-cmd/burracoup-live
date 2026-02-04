<!doctype html>
<html data-theme="cmyk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'BurracoUP LIVE' }}</title>

{{-- PWA --}}
	@if (config('app.pwa_enabled', false))
		<link rel="manifest" href="/manifest.webmanifest">
		<meta name="theme-color" content="#0f172a">
		<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="default">
	@endif


    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200">


    {{-- Navbar --}}
	 <div class="navbar bg-base-100/80 backdrop-blur border-b border-base-200 sticky top-0 z-50">
	  <div class="flex-1">
		<a href="/" class="btn btn-ghost text-xl tracking-tight">
		  BurracoUP <span class="opacity-70">LIVE</span>
		</a>
	  </div>
	  

	  <div class="flex-none gap-2">
		<button id="pwa-install-btn" class="btn btn-secondary hidden">
		  Installa
		</button>
		<a class="btn btn-ghost hidden sm:inline-flex" href="/eventi">Eventi</a>
		<a class="btn btn-outline hidden sm:inline-flex" href="/giocatori">Giocatori</a>
		<a class="btn btn-primary hidden sm:inline-flex" href="/admin">Admin</a>
	  </div>
	</div>

    {{-- Contenuto --}}
    <main class="max-w-6xl mx-auto p-4">
        @yield('content')
    </main>

    {{-- Bottom navigation (mobile) --}}
    <div class="btm-nav sm:hidden bg-base-100 border-t border-base-300">
		<a href="/" class="{{ request()->is('/') ? 'active text-primary font-semibold' : '' }}">

            <span class="btm-nav-label">Home</span>
        </a>
		<a href="/eventi" class="{{ request()->is('eventi*') ? 'active text-primary font-semibold' : '' }}">
            <span class="btm-nav-label">Eventi</span>
        </a>
		<a href="/giocatori" class="{{ request()->is('giocatori*') ? 'active text-primary font-semibold' : '' }}">

            <span class="btm-nav-label">Giocatori</span>
        </a>
    </div>

    {{-- iOS install hint --}}
    <div id="ios-install-hint" class="hidden fixed bottom-20 left-0 right-0 px-4 sm:hidden z-50">
        <div class="alert bg-base-100 shadow border">
            <div>
                <div class="font-semibold">Installa BurracoUP LIVE</div>
                <div class="text-sm opacity-80">
                    Tocca <strong>Condividi</strong> → <strong>Aggiungi a Home</strong>.
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const ua = (navigator.userAgent || "").toLowerCase();
            const isIOS = /iphone|ipad|ipod/.test(ua);
            const standalone = window.matchMedia("(display-mode: standalone)").matches || navigator.standalone === true;

            if (isIOS && !standalone) {
                const el = document.getElementById("ios-install-hint");
                if (el) el.classList.remove("hidden");
            }
        })();
    </script>

</body>
</html>
