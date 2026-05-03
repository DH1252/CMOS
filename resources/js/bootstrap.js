import axios from "axios";

import { initEcho } from "./echo";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const postHogApiKey = import.meta.env.VITE_POSTHOG_KEY || "";
const postHogHost =
	import.meta.env.VITE_POSTHOG_HOST || "https://app.posthog.com";
const postHogDisabled =
	String(import.meta.env.VITE_POSTHOG_DISABLED || "false").toLowerCase() ===
	"true";

const loadPostHog = async () => {
	if (typeof window === "undefined") {
		return null;
	}

	if (postHogDisabled || !postHogApiKey) {
		return null;
	}

	if (window.__CMOS_POSTHOG__) {
		return window.__CMOS_POSTHOG__;
	}

	if (!window.__CMOS_POSTHOG_PROMISE__) {
		window.__CMOS_POSTHOG_PROMISE__ = import("posthog-js")
			.then(({ default: posthog }) => {
				posthog.init(postHogApiKey, {
					api_host: postHogHost,
					autocapture: false,
					capture_pageview: true,
					capture_pageleave: true,
					person_profiles: "identified_only",
				});

				window.__CMOS_POSTHOG_READY__ = true;
				window.__CMOS_POSTHOG__ = posthog;
				window.posthog = posthog;

				return posthog;
			})
			.catch((error) => {
				console.warn("PostHog failed to load.", error);

				return null;
			})
			.finally(() => {
				window.__CMOS_POSTHOG_PROMISE__ = null;
			});
	}

	return window.__CMOS_POSTHOG_PROMISE__;
};

export const withPostHog = async (callback) => {
	const posthogClient = await loadPostHog();

	if (posthogClient && typeof callback === "function") {
		callback(posthogClient);
	}

	return posthogClient;
};

if (typeof window !== "undefined") {
	window.__CMOS_WITH_POSTHOG__ = withPostHog;
	window.__CMOS_POSTHOG__ = null;
	window.__CMOS_POSTHOG_PROMISE__ = null;
	window.__CMOS_POSTHOG_READY__ = false;
	void loadPostHog();
}

export const initRealtime = async () => {
	return initEcho();
};
