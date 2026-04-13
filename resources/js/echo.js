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

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || null;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || currentScheme;
const reverbHost =
	normalizeHost(import.meta.env.VITE_REVERB_HOST) ||
	(typeof window !== "undefined" ? window.location.hostname : "localhost");
const defaultPort = reverbScheme === "https" ? 443 : 80;
const reverbPort = Number(
	import.meta.env.VITE_REVERB_PORT ||
		(typeof window !== "undefined" ? window.location.port : "") ||
		defaultPort,
);

window.Echo = null;

if (!reverbKey) {
	console.warn("Realtime disabled: missing VITE_REVERB_APP_KEY");
} else {
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
}
