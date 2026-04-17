import axios from "axios";
import posthog from "posthog-js";

import { initEcho } from "./echo";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const postHogApiKey = import.meta.env.VITE_POSTHOG_KEY || "";
const postHogHost =
	import.meta.env.VITE_POSTHOG_HOST || "https://app.posthog.com";
const postHogDisabled =
	String(import.meta.env.VITE_POSTHOG_DISABLED || "false").toLowerCase() ===
	"true";

const ensurePostHog = () => {
	if (typeof window === "undefined") {
		return null;
	}

	if (postHogDisabled || !postHogApiKey) {
		return null;
	}

	if (!window.__CMOS_POSTHOG_READY__) {
		posthog.init(postHogApiKey, {
			api_host: postHogHost,
			autocapture: false,
			capture_pageview: true,
			capture_pageleave: true,
			person_profiles: "identified_only",
		});

		window.__CMOS_POSTHOG_READY__ = true;
	}

	return posthog;
};

const posthogClient = ensurePostHog();

if (posthogClient) {
	window.__CMOS_POSTHOG__ = posthogClient;
	window.posthog = posthogClient;
}

export const initRealtime = async () => {
	return initEcho();
};
