import Echo from "laravel-echo";

import Pusher from "pusher-js";

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || null;
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT || 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || "http";

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
