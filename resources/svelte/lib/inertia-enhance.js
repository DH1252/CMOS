import { router } from "@inertiajs/svelte";

const isModifiedEvent = (event) => {
	return (
		event.defaultPrevented ||
		event.button !== 0 ||
		event.metaKey ||
		event.ctrlKey ||
		event.shiftKey ||
		event.altKey
	);
};

const isSkippableHref = (href) => {
	if (!href) {
		return true;
	}

	return (
		href.startsWith("#") ||
		href.startsWith("mailto:") ||
		href.startsWith("tel:") ||
		href.startsWith("javascript:")
	);
};

const isExternalUrl = (href) => {
	try {
		const url = new URL(href, window.location.href);

		return url.origin !== window.location.origin;
	} catch {
		return true;
	}
};

const formMethod = (form) => {
	const spoofed = form.querySelector('input[name="_method"]')?.value;
	const method = (
		spoofed ||
		form.getAttribute("method") ||
		"get"
	).toLowerCase();

	return ["get", "post", "put", "patch", "delete"].includes(method)
		? method
		: "get";
};

const formDataToObject = (formData) => {
	const payload = {};

	for (const [key, value] of formData.entries()) {
		if (Object.hasOwn(payload, key)) {
			payload[key] = Array.isArray(payload[key])
				? [...payload[key], value]
				: [payload[key], value];
			continue;
		}

		payload[key] = value;
	}

	return payload;
};

export const inertiaEnhance = (node, enabled = true) => {
	let attached = false;

	const handleClick = (event) => {
		const anchor =
			event.target instanceof Element ? event.target.closest("a[href]") : null;

		if (!anchor || isModifiedEvent(event)) {
			return;
		}

		if (anchor.dataset.native === "true" || anchor.hasAttribute("download")) {
			return;
		}

		const href = anchor.getAttribute("href") || "";

		if (
			isSkippableHref(href) ||
			isExternalUrl(href) ||
			(anchor.target && anchor.target !== "_self")
		) {
			return;
		}

		event.preventDefault();
		router.visit(href);
	};

	const handleSubmit = (event) => {
		const form = event.target;

		if (!(form instanceof HTMLFormElement)) {
			return;
		}

		if (
			form.dataset.native === "true" ||
			(form.target && form.target !== "_self")
		) {
			return;
		}

		const action = form.getAttribute("action") || window.location.href;

		if (isExternalUrl(action)) {
			return;
		}

		const method = formMethod(form);
		const data = new FormData(form);

		event.preventDefault();

		if (method === "get") {
			router.get(action, formDataToObject(data), {
				preserveState: true,
				preserveScroll: true,
				replace: true,
			});

			return;
		}

		data.delete("_method");

		router.visit(action, {
			method,
			data,
			forceFormData: true,
			preserveScroll: true,
		});
	};

	const attachListeners = () => {
		if (attached) {
			return;
		}

		node.addEventListener("click", handleClick);
		node.addEventListener("submit", handleSubmit);
		attached = true;
	};

	const detachListeners = () => {
		if (!attached) {
			return;
		}

		node.removeEventListener("click", handleClick);
		node.removeEventListener("submit", handleSubmit);
		attached = false;
	};

	if (enabled) {
		attachListeners();
	}

	return {
		update(nextEnabled = true) {
			if (nextEnabled) {
				attachListeners();
				return;
			}

			detachListeners();
		},
		destroy() {
			detachListeners();
		},
	};
};
