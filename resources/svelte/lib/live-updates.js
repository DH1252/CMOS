const listeners = new Set();

let bootPromise = null;
let cleanupRealtimeChannels = null;
let currentUserId = null;
let lifecycleListenersBound = false;
let pageIsActive = true;
let scheduledBootHandle = null;
let scheduledBootType = null;

const parseAuthProps = () => {
	if (typeof window === "undefined") {
		return {};
	}

	if (window.__CMOS_AUTH_PROPS__) {
		return window.__CMOS_AUTH_PROPS__;
	}

	return {};
};

const emit = (event) => {
	listeners.forEach((listener) => {
		listener(event);
	});
};

const discardEcho = (echo = null) => {
	const activeEcho = echo || window.Echo || null;

	activeEcho?.disconnect?.();

	if (typeof window !== "undefined" && window.Echo === activeEcho) {
		window.Echo = null;
	}
};

const cancelScheduledBoot = () => {
	if (scheduledBootHandle === null || typeof window === "undefined") {
		return;
	}

	if (
		scheduledBootType === "idle" &&
		typeof window.cancelIdleCallback === "function"
	) {
		window.cancelIdleCallback(scheduledBootHandle);
	} else {
		window.clearTimeout(scheduledBootHandle);
	}

	scheduledBootHandle = null;
	scheduledBootType = null;
};

const teardownConnection = () => {
	cancelScheduledBoot();
	cleanupRealtimeChannels?.();
	cleanupRealtimeChannels = null;
	currentUserId = null;
	bootPromise = null;

	if (typeof window !== "undefined") {
		discardEcho();
	}
};

const scheduleBoot = () => {
	if (
		typeof window === "undefined" ||
		listeners.size === 0 ||
		cleanupRealtimeChannels ||
		bootPromise ||
		scheduledBootHandle !== null ||
		!pageIsActive
	) {
		return;
	}

	const run = () => {
		scheduledBootHandle = null;
		scheduledBootType = null;

		if (listeners.size > 0) {
			void boot();
		}
	};

	if (typeof window.requestIdleCallback === "function") {
		scheduledBootType = "idle";
		scheduledBootHandle = window.requestIdleCallback(run, { timeout: 1500 });
		return;
	}

	scheduledBootType = "timeout";
	scheduledBootHandle = window.setTimeout(run, 1000);
};

const bindLifecycleListeners = () => {
	if (
		lifecycleListenersBound ||
		typeof window === "undefined" ||
		typeof document === "undefined"
	) {
		return;
	}

	window.addEventListener("pagehide", () => {
		pageIsActive = false;
		teardownConnection();
	});

	window.addEventListener("pageshow", () => {
		pageIsActive = true;

		if (listeners.size > 0) {
			scheduleBoot();
		}
	});

	lifecycleListenersBound = true;
};

const ensureEcho = async () => {
	if (typeof window === "undefined") {
		return null;
	}

	if (window.Echo) {
		return window.Echo;
	}

	try {
		const { initEcho } = await import("../../js/echo");

		return initEcho();
	} catch (error) {
		console.error("Failed to load realtime websocket client", error);
		return null;
	}
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

const boot = async () => {
	if (cleanupRealtimeChannels) {
		return window.Echo || null;
	}

	if (bootPromise) {
		return bootPromise;
	}

	const authProps = parseAuthProps();
	currentUserId = Number(authProps.user?.id || 0) || null;

	if (!currentUserId) {
		return null;
	}

	bootPromise = ensureEcho()
		.then((echo) => {
			if (!echo || !pageIsActive) {
				discardEcho(echo);
				return null;
			}

			const userId = currentUserId;

			echo
				.private(`users.${userId}`)
				.listen(".realtime.channels.updated", userCallback)
				.listen(".chat.message.sent", chatCallback);

			echo
				.private("organization")
				.listen(".realtime.channels.updated", organizationCallback);

			cleanupRealtimeChannels = () => {
				echo.leave(`users.${userId}`);
				echo.leave("organization");
				discardEcho(echo);
			};

			return echo;
		})
		.finally(() => {
			bootPromise = null;
		});

	return bootPromise;
};

export const subscribeToLiveUpdates = (endpoint, listener) => {
	if (
		typeof window === "undefined" ||
		typeof listener !== "function" ||
		!endpoint
	) {
		return () => {};
	}

	bindLifecycleListeners();
	listeners.add(listener);
	scheduleBoot();

	return () => {
		listeners.delete(listener);

		if (listeners.size === 0) {
			teardownConnection();
		}
	};
};
