import path from "node:path";
import { brotliCompressSync, constants as zlibConstants } from "node:zlib";
import inertia from "@inertiajs/vite";
import { enhancedImages } from "@sveltejs/enhanced-img";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

const brotliAssetPattern = /\.(?:css|html|js|json|map|mjs|svg|txt|wasm|xml)$/i;

const viteBrotliAssets = () => {
	/** @type {import("vite").ResolvedConfig | null} */
	let resolvedConfig = null;

	return {
		name: "vite-brotli-assets",
		apply: "build",
		configResolved(config) {
			resolvedConfig = config;
		},
		generateBundle(_outputOptions, bundle) {
			if (resolvedConfig?.build.ssr) {
				return;
			}

			for (const [fileName, artifact] of Object.entries(bundle)) {
				if (!brotliAssetPattern.test(fileName)) {
					continue;
				}

				const source =
					artifact.type === "asset" ? artifact.source : artifact.code;
				const sourceBuffer =
					source instanceof Uint8Array
						? Buffer.from(source)
						: Buffer.from(String(source), "utf8");

				if (sourceBuffer.byteLength < 1024) {
					continue;
				}

				this.emitFile({
					type: "asset",
					fileName: `${fileName}.br`,
					source: brotliCompressSync(sourceBuffer, {
						params: {
							[zlibConstants.BROTLI_PARAM_MODE]:
								zlibConstants.BROTLI_MODE_GENERIC,
							[zlibConstants.BROTLI_PARAM_QUALITY]:
								zlibConstants.BROTLI_MAX_QUALITY,
							[zlibConstants.BROTLI_PARAM_SIZE_HINT]: sourceBuffer.byteLength,
						},
					}),
				});
			}
		},
	};
};

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
		viteBrotliAssets(),
		laravel({
			input: [
				"resources/css/app.css",
				"resources/css/public.css",
				"resources/js/app.js",
				"resources/js/public.js",
			],
			ssr: "resources/js/ssr.js",
			refresh: true,
		}),
		inertia({
			ssr: {
				entry: "resources/js/ssr.js",
				host: "127.0.0.1",
				port: 13714,
			},
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
