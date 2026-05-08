import "vite/modulepreload-polyfill";
import { createInertiaApp, router } from "@inertiajs/svelte";
import { hydrate, mount } from "svelte";
import LandingPage from "../svelte/LandingPage.svelte";
import PublicApp from "../svelte/PublicApp.svelte";

const pages = {
	LandingPage: { default: LandingPage },
	PublicApp: { default: PublicApp },
};

const resolvePublicPage = (name) => {
	const page = pages[name];

	if (!page) {
		throw new Error(`Unknown Inertia page: ${name}`);
	}

	return page;
};

const applyBrandTheme = (themeName) => {
	if (typeof document === "undefined") {
		return;
	}

	document.documentElement.setAttribute(
		"data-brand",
		typeof themeName === "string" && themeName.length > 0
			? themeName
			: "purple",
	);
};

const applyThemeVariables = (variables = null) => {
	if (
		typeof document === "undefined" ||
		!variables ||
		typeof variables !== "object"
	) {
		return;
	}

	const customCss = variables.customCss || variables;
	const landingVars = customCss.landing || {};

	Object.entries({ ...(variables || {}), ...(landingVars || {}) }).forEach(
		([token, value]) => {
			if (typeof token !== "string" || typeof value !== "string") {
				return;
			}

			document.documentElement.style.setProperty(`--${token}`, value);
		},
	);
};

let bootstrapModulePromise = null;

const ensureBootstrapModule = async () => {
	if (!bootstrapModulePromise) {
		bootstrapModulePromise = import("./bootstrap");
	}

	return bootstrapModulePromise;
};

const capturePostHogPageview = (page = null) => {
	if (typeof window === "undefined") {
		return;
	}

	const runWithPostHog = window.__CMOS_WITH_POSTHOG__;

	if (typeof runWithPostHog !== "function") {
		return;
	}

	const pageUrl =
		page?.url ||
		(typeof window !== "undefined" ? window.location.pathname : "/");
	const component = page?.component || null;

	void runWithPostHog((posthogClient) => {
		posthogClient.capture("$pageview", {
			$current_url:
				typeof window !== "undefined" ? window.location.href : pageUrl,
			$page_path: pageUrl,
			inertia_component: component,
		});
	});
};

const deferBootstrapForPublic = (initialPage) => {
	if (typeof window === "undefined") {
		return;
	}

	const loadBootstrap = () => {
		void ensureBootstrapModule().then(() => {
			capturePostHogPageview(initialPage);
			router.on("navigate", (event) => {
				capturePostHogPageview(event?.detail?.page || null);
			});
		});
	};

	const schedule = () => {
		const idleCallback =
			window.requestIdleCallback ||
			((callback) => window.setTimeout(callback, 500));
		idleCallback(loadBootstrap);
	};

	if (document.readyState === "complete") {
		schedule();
		return;
	}

	window.addEventListener("load", schedule, { once: true });
};

const inertiaRoot =
	typeof document !== "undefined" ? document.getElementById("app") : null;
const inertiaPagePayload = inertiaRoot?.dataset?.page || "";
const inertiaScriptPagePayload =
	typeof document !== "undefined"
		? document.querySelector('script[data-page="app"][type="application/json"]')
				?.textContent || ""
		: "";

const resolveInitialPage = (payload) => {
	if (!payload || payload === "null") {
		return null;
	}

	try {
		const initialPage = JSON.parse(payload);

		return initialPage && typeof initialPage.component === "string"
			? initialPage
			: null;
	} catch (error) {
		console.error("Failed to parse the initial Inertia page payload.", error);

		return null;
	}
};

const initialInertiaPage =
	resolveInitialPage(inertiaPagePayload) ||
	resolveInitialPage(inertiaScriptPagePayload);

let publicAppBootPromise = null;

const bootPublicInertiaApp = () => {
	if (!inertiaRoot || !initialInertiaPage) {
		return Promise.resolve();
	}

	if (!publicAppBootPromise) {
		publicAppBootPromise = createInertiaApp({
			page: initialInertiaPage,
			resolve: resolvePublicPage,
			layout: () => undefined,
			setup({ el, App, props }) {
				if (el?.hasAttribute("data-server-rendered")) {
					hydrate(App, { target: el, props });
					return;
				}

				mount(App, { target: el, props });
			},
		});
	}

	return publicAppBootPromise;
};

const deferPublicAppBoot = () => {
	if (typeof window === "undefined") {
		return;
	}

	const boot = () => {
		void bootPublicInertiaApp().catch((error) => {
			console.error("Failed to boot public Inertia app.", error);
		});
	};

	const schedule = () => {
		const idleCallback =
			window.requestIdleCallback ||
			((callback) => window.setTimeout(callback, 650));

		idleCallback(boot, { timeout: 2000 });
	};

	if (document.readyState === "complete") {
		schedule();
		return;
	}

	window.addEventListener("load", schedule, { once: true });
};

if (typeof document !== "undefined" && initialInertiaPage) {
	const pageBrand =
		initialInertiaPage?.props?.themeColor ||
		initialInertiaPage?.props?.theme?.color ||
		document.documentElement.getAttribute("data-brand") ||
		"purple";
	const themeVariables =
		initialInertiaPage?.props?.themeVariables ||
		initialInertiaPage?.props?.theme?.variables ||
		null;
	const themeCustomCss =
		initialInertiaPage?.props?.themeCustomCss ||
		initialInertiaPage?.props?.theme?.customCss ||
		null;

	applyBrandTheme(pageBrand);
	applyThemeVariables({ ...themeVariables, customCss: themeCustomCss });
	deferBootstrapForPublic(initialInertiaPage);
	deferPublicAppBoot();
}
