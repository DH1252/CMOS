import Echo from "laravel-echo";

import Pusher from "pusher-js";

window.Pusher = Pusher;

const normalizeHost = (value) =>
  String(value || "")
    .replace(/^wss?:\/\//, "")
    .replace(/^https?:\/\//, "")
    .replace(/\/.*$/, "")
    .trim();

const currentScheme =
  typeof window !== "undefined" && window.location.protocol === "https:"
    ? "https"
    : "http";

export const initEcho = () => {
  if (typeof window === "undefined") {
    return null;
  }

  if (window.Echo) {
    return window.Echo;
  }

  const runtimeConfig = window.__CMOS_REALTIME__ || {};
  const reverbKey =
    runtimeConfig.key || import.meta.env.VITE_REVERB_APP_KEY || null;
  const reverbScheme =
    runtimeConfig.scheme || import.meta.env.VITE_REVERB_SCHEME || currentScheme;
  const reverbHost =
    normalizeHost(runtimeConfig.host || import.meta.env.VITE_REVERB_HOST) ||
    window.location.hostname;
  const defaultPort = reverbScheme === "https" ? 443 : 80;
  const reverbPort = Number(
    runtimeConfig.port ||
      import.meta.env.VITE_REVERB_PORT ||
      window.location.port ||
      defaultPort,
  );

  if (!reverbKey) {
    console.warn("Realtime disabled: missing VITE_REVERB_APP_KEY");
    window.Echo = null;
    return null;
  }

  try {
    window.Echo = new Echo({
      broadcaster: "reverb",
      key: reverbKey,
      wsHost: reverbHost,
      wsPort: reverbPort,
      wssPort: reverbPort,
      forceTLS: reverbScheme === "https",
      enabledTransports: ["ws", "wss"],
    });
  } catch (error) {
    console.error("Failed to initialize realtime connection", error);
    window.Echo = null;
  }

  return window.Echo;
};
