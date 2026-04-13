const listeners = new Set();

let booted = false;
let currentUserId = null;
let cleanupRealtimeChannels = null;

const parseAuthProps = () => {
	if (typeof document === "undefined") {
		return {};
	}

	const node = document.getElementById("svelte-auth-props");

	if (!node?.textContent) {
		return {};
	}

	try {
		return JSON.parse(node.textContent);
	} catch (error) {
		console.error("Failed to parse auth realtime props", error);
		return {};
	}
};

const emit = (event) => {
	listeners.forEach((listener) => {
		listener(event);
	});
};

const ensureEcho = () => {
	if (typeof window === "undefined") {
		return null;
	}

	return window.Echo || null;
};

const organizationCallback = (payload = {}) => {
	emit({
		type: "realtime.channels.updated",
		currentUserId,
		changed: Array.isArray(payload.changed) ? payload.changed : [],
		payload: payload.snapshot || {},
		context: payload.context || {},
		scope: payload.scope || "organization",
		raw: payload,
	});
};

const userCallback = (payload = {}) => {
	emit({
		type: "realtime.channels.updated",
		currentUserId,
		changed: Array.isArray(payload.changed) ? payload.changed : [],
		payload: payload.snapshot || {},
		context: payload.context || {},
		scope: payload.scope || "user",
		raw: payload,
	});
};

const chatCallback = (payload = {}) => {
	const senderId = Number(payload.message?.senderId || 0);
	const receiverId = Number(payload.message?.receiverId || 0);
	const participantId = senderId === currentUserId ? receiverId : senderId;
	const unreadCount = Number(
		payload.unreadCounts?.[String(currentUserId)] || 0,
	);

	emit({
		type: "chat.message.sent",
		currentUserId,
		changed: ["messages"],
		payload: {
			messages: {
				unreadCount,
			},
		},
		participantId,
		message: payload.message || null,
		raw: payload,
	});
};

const boot = () => {
	if (booted) {
		return;
	}

	const authProps = parseAuthProps();
	currentUserId = Number(authProps.user?.id || 0) || null;

	if (!currentUserId) {
		return;
	}

	const echo = ensureEcho();

	if (!echo) {
		console.warn("Echo is not available for realtime subscriptions");
		return;
	}

	echo
		.private(`users.${currentUserId}`)
		.listen(".realtime.channels.updated", userCallback)
		.listen(".chat.message.sent", chatCallback);

	echo
		.private("organization")
		.listen(".realtime.channels.updated", organizationCallback);

	cleanupRealtimeChannels = () => {
		echo.leave(`users.${currentUserId}`);
		echo.leave("organization");
	};

	booted = true;
	window.addEventListener("pagehide", cleanupRealtimeChannels, { once: true });
};

export const subscribeToLiveUpdates = (_unused, listener) => {
	if (typeof window === "undefined" || typeof listener !== "function") {
		return () => {};
	}

	listeners.add(listener);
	boot();

	return () => {
		listeners.delete(listener);
	};
};
