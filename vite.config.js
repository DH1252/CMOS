import path from "node:path";
import { enhancedImages } from "@sveltejs/enhanced-img";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

export default defineConfig({
	build: {
		rollupOptions: {
			onwarn(warning, defaultHandler) {
				if (
					warning.message?.includes("isSameUrlWithoutQueryOrHash") &&
					warning.message?.includes("@inertiajs/core") &&
					warning.message?.includes("Deferred.svelte")
				) {
					return;
				}

				defaultHandler(warning);
			},
		},
	},
	plugins: [
		enhancedImages(),
		svelte(),
		laravel({
			input: ["resources/css/app.css", "resources/js/app.js"],
			ssr: "resources/js/ssr.js",
			refresh: true,
		}),
		tailwindcss(),
	],
	server: {
		watch: {
			ignored: [
				"**/storage/framework/views/**",
				"**/storage/framework/sessions/**",
				"**/storage/framework/cache/**",
				"**/storage/logs/**",
				"**/.playwright-mcp/**",
			],
		},
	},
	resolve: {
		alias: {
			$lib: path.resolve("./resources/svelte/lib"),
			lib: path.resolve("./resources/svelte/lib"),
			components: path.resolve("./resources/svelte/lib/components"),
			hooks: path.resolve("./resources/svelte/lib/hooks"),
			"tailwind-merge": path.resolve(
				"./resources/svelte/lib/tailwind-merge.js",
			),
			ui: path.resolve("./resources/svelte/lib/components/ui"),
			utils: path.resolve("./resources/svelte/lib/utils.js"),
		},
	},
});
