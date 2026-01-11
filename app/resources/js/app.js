import "./bootstrap";
import "../css/app.css";

const ENABLE_PWA = import.meta.env.VITE_ENABLE_PWA === "1";

// PWA solo se esplicitamente abilitata
if (ENABLE_PWA) {
  import("./pwa").then(({ initPWAInstall }) => initPWAInstall());
}
