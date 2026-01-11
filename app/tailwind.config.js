import daisyui from "daisyui";

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: { extend: {} },
  plugins: [daisyui],
  daisyui: {
    themes: [
      {
        burracoup: {
          "primary": "#6D28D9",
          "primary-content": "#FFFFFF",
          "base-100": "#FFFFFF",
          "base-200": "#F3F4F6",
          "base-300": "#E5E7EB",
          "base-content": "#111827",
          "success": "#16A34A",
          "success-content": "#FFFFFF",
          "warning": "#F59E0B",
          "warning-content": "#111827",
          "error": "#DC2626",
          "error-content": "#FFFFFF",
        },
      },
      "light",
      "dark",
    ],
  },
};
