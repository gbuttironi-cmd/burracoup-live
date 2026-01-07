/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require("daisyui"),
  ],
  daisyui: {
    themes: [
      {
        burracoup: {
          "primary": "#6D28D9",          // viola
          "primary-content": "#FFFFFF",  // testo sopra il primary -> bianco
          "secondary": "#0EA5E9",
          "secondary-content": "#FFFFFF",
          "accent": "#22C55E",
          "accent-content": "#052E16",

          "neutral": "#111827",
          "neutral-content": "#F9FAFB",

          "base-100": "#FFFFFF",
          "base-200": "#F3F4F6",
          "base-300": "#E5E7EB",
          "base-content": "#111827",

          "info": "#0EA5E9",
          "info-content": "#FFFFFF",
          "success": "#22C55E",
          "success-content": "#052E16",
          "warning": "#F59E0B",
          "warning-content": "#111827",
          "error": "#EF4444",
          "error-content": "#FFFFFF",
        },
      },
      "light",
      "dark",
    ],
  },
};
