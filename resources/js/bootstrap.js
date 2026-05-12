import axios from "axios";

import { initEcho } from "./echo";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const postHogApiKey = import.meta.env.VITE_POSTHOG_KEY || "";
const postHogHost =
  import.meta.env.VITE_POSTHOG_HOST || "https://app.posthog.com";
const postHogDisabled = import.meta.env.VITE_POSTHOG_DISABLED || "false";

const isTruthyDisabledFlag = (value) => {
  return ["1", "true", "yes", "on"].includes(String(value).toLowerCase());
};

const resolvePostHogConfig = () => {
  const runtimeConfig =
    typeof window !== "undefined" && window.__CMOS_POSTHOG_CONFIG__
      ? window.__CMOS_POSTHOG_CONFIG__
      : {};

  return {
    key: runtimeConfig.key || postHogApiKey,
    host: runtimeConfig.host || postHogHost,
    moduleUrl: runtimeConfig.moduleUrl || "",
    disabled: isTruthyDisabledFlag(runtimeConfig.disabled ?? postHogDisabled),
  };
};

const loadPostHog = async () => {
  if (typeof window === "undefined") {
    return null;
  }

  const config = resolvePostHogConfig();

  if (config.disabled || !config.key || !config.moduleUrl) {
    return null;
  }

  if (window.__CMOS_POSTHOG__) {
    return window.__CMOS_POSTHOG__;
  }

  if (!window.__CMOS_POSTHOG_PROMISE__) {
    window.__CMOS_POSTHOG_PROMISE__ = import(
      /* @vite-ignore */ config.moduleUrl
    )
      .then(({ default: posthog }) => {
        posthog.init(config.key, {
          api_host: config.host,
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
}

export const initRealtime = async () => {
  return initEcho();
};
