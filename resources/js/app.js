import { createInertiaApp } from "@inertiajs/svelte";
import { hydrate, mount } from "svelte";
import AuthLayout from "../svelte/layouts/AuthLayout.svelte";
import { loadExternalScript } from "../svelte/lib/external-assets.js";

const createDialogFacade = () => ({
	__externalAssetFallback: true,
	async fire(options = {}) {
		const title = options.title || "";
		const text = options.text || "";
		const message = [title, text].filter(Boolean).join("\n\n").trim();

		if (options.showCancelButton) {
			return {
				isConfirmed: window.confirm(message || "Lanjutkan tindakan ini?"),
			};
		}

		window.alert(message || title || "Notifikasi");

		return {
			isConfirmed: true,
		};
	},
});

if (typeof window !== "undefined" && !window.Swal) {
	window.Swal = createDialogFacade();

	void loadExternalScript(
		"https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js",
		"Swal",
	).catch((error) => {
		console.warn("SweetAlert2 failed to load, using fallback dialogs.", error);
	});
}

const pages = {
	...import.meta.glob("../svelte/*Page.svelte"),
	...import.meta.glob("../svelte/pages/**/*.svelte"),
	...import.meta.glob("../svelte/PublicApp.svelte"),
};
const isLoginPath =
	typeof window !== "undefined" && window.location.pathname === "/login";
const inertiaRoot =
	typeof document !== "undefined" ? document.getElementById("app") : null;
const inertiaPagePayload = inertiaRoot?.dataset?.page || "";
const inertiaScriptPagePayload =
	typeof document !== "undefined"
		? document.querySelector('script[data-page="app"][type="application/json"]')
				?.textContent || ""
		: "";
const publicRoot =
	typeof document !== "undefined"
		? document.getElementById("svelte-public-root")
		: null;
const legacyLoginRoot =
	typeof document !== "undefined"
		? document.getElementById("svelte-login-root")
		: null;

const parseJson = (id) => {
	if (typeof document === "undefined") {
		return null;
	}

	const node = document.getElementById(id);

	if (!node?.textContent) {
		return null;
	}

	try {
		return JSON.parse(node.textContent);
	} catch (error) {
		console.error(`Failed to parse JSON from #${id}`, error);

		return null;
	}
};

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

const isPublicPage = (name) =>
	name === "PublicApp" || name.startsWith("public/");
const isGuestPage = (name) => name === "LoginPage";

let loginFallbackMounted = false;

const resolveLoginTarget = () => {
	if (legacyLoginRoot) {
		return legacyLoginRoot;
	}

	if (inertiaRoot) {
		return inertiaRoot;
	}

	if (typeof document === "undefined") {
		return null;
	}

	const existing = document.getElementById("svelte-login-fallback-root");

	if (existing) {
		return existing;
	}

	const fallbackTarget = document.createElement("div");
	fallbackTarget.id = "svelte-login-fallback-root";
	document.body.append(fallbackTarget);

	return fallbackTarget;
};

const legacyLoginProps = () => ({
	appName: legacyLoginRoot?.dataset.appName || "CMOS",
	loginUrl: legacyLoginRoot?.dataset.loginUrl || "/login",
	homeUrl: legacyLoginRoot?.dataset.homeUrl || "/",
	csrfToken:
		legacyLoginRoot?.dataset.csrfToken ||
		document
			.querySelector('meta[name="csrf-token"]')
			?.getAttribute("content") ||
		"",
	email: legacyLoginRoot?.dataset.email || "",
	alertMessage: legacyLoginRoot?.dataset.alertMessage || "",
	alertType: legacyLoginRoot?.dataset.alertType || "",
	emailError: legacyLoginRoot?.dataset.emailError || "",
	passwordError: legacyLoginRoot?.dataset.passwordError || "",
	remember: legacyLoginRoot?.dataset.remember === "1",
});

const mountLoginFallback = async (sourceProps = {}) => {
	if (!isLoginPath || loginFallbackMounted) {
		return;
	}

	const target = resolveLoginTarget();

	if (!target) {
		return;
	}

	const props = {
		...legacyLoginProps(),
		...sourceProps,
	};

	const { default: LoginPage } = await import("../svelte/LoginPage.svelte");

	mount(LoginPage, {
		target,
		props,
	});

	loginFallbackMounted = true;
};

const initialInertiaPage =
	resolveInitialPage(inertiaPagePayload) ||
	resolveInitialPage(inertiaScriptPagePayload);

const shouldBootStandaloneLogin =
	isLoginPath && initialInertiaPage?.component === "LoginPage";

if (shouldBootStandaloneLogin) {
	void mountLoginFallback(initialInertiaPage?.props || {});
}

if (inertiaRoot && initialInertiaPage && !shouldBootStandaloneLogin) {
	void createInertiaApp({
		page: initialInertiaPage,
		resolve: async (name) => {
			const importer = pages[`../svelte/${name}.svelte`];

			if (!importer) {
				throw new Error(`Unknown Inertia page: ${name}`);
			}

			const page = await importer();

			return page;
		},
		layout: (name, page) => {
			if (isPublicPage(name) || isGuestPage(name)) {
				return undefined;
			}

			return [
				AuthLayout,
				{
					shell: page.props.shell,
					flash: page.props.flash,
					errors: page.props.errors,
					pageTitle: page.props.pageTitle,
					pageMeta: page.props.pageMeta,
					title: page.props.title,
					description: page.props.description,
				},
			];
		},
		setup({ el, App, props }) {
			mount(App, { target: el, props });
		},
	}).catch((error) => {
		console.error("Failed to boot Inertia app.", error);

		void mountLoginFallback(initialInertiaPage?.props || {});
	});
}

if (inertiaRoot && !initialInertiaPage) {
	console.error(
		"Inertia root was found but no valid initial page payload could be resolved.",
	);

	void mountLoginFallback();
}

if (publicRoot) {
	const publicPageProps = parseJson("svelte-public-props") || {};

	void import("../svelte/PublicApp.svelte")
		.then(({ default: PublicApp }) => {
			const bootComponent = publicRoot.dataset.ssr === "true" ? hydrate : mount;

			bootComponent(PublicApp, {
				target: publicRoot,
				props: publicPageProps,
			});
		})
		.catch((error) => {
			console.error("Failed to boot the public Svelte app.", error);
		});
}

if (legacyLoginRoot && !initialInertiaPage) {
	void mountLoginFallback();
}
