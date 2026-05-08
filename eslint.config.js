import js from "@eslint/js";
import svelte from "eslint-plugin-svelte";
import globals from "globals";

export default [
  {
    ignores: [
      "bootstrap/ssr/**",
      "node_modules/**",
      "public/atlas/**",
      "public/build/**",
      "public/tinymce/**",
      "vendor/**",
      ".kilo/**",
    ],
  },
  js.configs.recommended,
  ...svelte.configs["flat/recommended"],
  {
    files: ["resources/**/*.js", "vite.config.js"],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
  },
  {
    files: ["resources/svelte/**/*.svelte"],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
    rules: {
      "no-unused-vars": "off",
      "svelte/no-at-html-tags": "off",
      "svelte/prefer-svelte-reactivity": "off",
      "svelte/prefer-writable-derived": "off",
      "svelte/require-each-key": "off",
    },
  },
];
