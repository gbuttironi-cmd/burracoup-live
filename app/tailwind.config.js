import daisyui from "daisyui";

/** @type {import('tailwindcss').Config} */
export default {
  content: ["./resources/**/*.blade.php", "./resources/**/*.js", "./resources/**/*.vue"],
  plugins: [daisyui],
  daisyui: {
    themes: [
      "light",
      "dark",
      "retro",
      "cupcake",
      "synthwave",
      {
        burracoup: {
          primary: "#6D28D9",
          "primary-content": "#FFFFFF",
          secondary: "#111827",
          "secondary-content": "#FFFFFF",
          accent: "#0EA5E9",
          "accent-content": "#0B1220",
          neutral: "#111827",
          "neutral-content": "#FFFFFF",
          "base-100": "#FFFFFF",
          "base-200": "#F3F4F6",
          "base-300": "#E5E7EB",
          "base-content": "#111827",
          info: "#2563EB",
          "info-content": "#FFFFFF",
          success: "#16A34A",
          "success-content": "#FFFFFF",
          warning: "#F59E0B",
          "warning-content": "#111827",
          error: "#DC2626",
          "error-content": "#FFFFFF",
        },
      },
    ],
  },
};
