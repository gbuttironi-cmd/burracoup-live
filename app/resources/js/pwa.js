let deferredPrompt = null;

export function initPWAInstall() {
  // Register SW
  if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
      try {
        await navigator.serviceWorker.register("/sw.js");
      } catch (e) {
        // non bloccare l'app se fallisce
        console.warn("SW registration failed", e);
      }
    });
  }

  // Install prompt (Chrome/Edge/Android/Desktop)
  window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;

    const btn = document.getElementById("pwa-install-btn");
    if (btn) btn.classList.remove("hidden");
  });

  // Dopo installazione
  window.addEventListener("appinstalled", () => {
    deferredPrompt = null;
    const btn = document.getElementById("pwa-install-btn");
    if (btn) btn.classList.add("hidden");
  });

  // Click install
  window.addEventListener("click", async (e) => {
    const target = e.target?.closest?.("#pwa-install-btn");
    if (!target) return;

    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    target.classList.add("hidden");
  });
}

// iOS helper (Safari non mostra beforeinstallprompt)
export function isIOS() {
  const ua = window.navigator.userAgent.toLowerCase();
  return /iphone|ipad|ipod/.test(ua);
}

export function isInStandaloneMode() {
  return window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
}
