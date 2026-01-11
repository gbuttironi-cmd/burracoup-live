/* DEV KILL-SWITCH: disinstalla il Service Worker e cancella cache */
self.addEventListener("install", (event) => {
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil((async () => {
    // cancella cache
    const keys = await caches.keys();
    await Promise.all(keys.map((k) => caches.delete(k)));

    // chiudi controllo sui client
    const clientsList = await self.clients.matchAll({ type: "window", includeUncontrolled: true });
    for (const c of clientsList) {
      try { c.navigate(c.url); } catch {}
    }

    // disinstalla il SW
    await self.registration.unregister();
  })());
});
