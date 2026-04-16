const scriptLoads = new Map();
const stylesheetLoads = new Map();

const isBrowser = () => typeof document !== "undefined";

const existingStylesheet = (href) =>
	isBrowser()
		? document.querySelector(`link[rel="stylesheet"][href="${href}"]`)
		: null;

const existingScript = (src) =>
	isBrowser() ? document.querySelector(`script[src="${src}"]`) : null;

const stylesheetLoaded = (element) =>
	element?.dataset.loadState === "loaded" || Boolean(element?.sheet);

const scriptLoaded = (element) =>
	element?.dataset.loadState === "loaded" || element?.readyState === "complete";

const hasUsableGlobal = (globalName) => {
	if (!globalName) {
		return true;
	}

	const globalValue = window[globalName];

	return Boolean(globalValue) && globalValue.__externalAssetFallback !== true;
};

export const loadExternalStylesheet = (href) => {
	if (!isBrowser() || !href) {
		return Promise.resolve();
	}

	if (stylesheetLoads.has(href)) {
		return stylesheetLoads.get(href);
	}

	let link = existingStylesheet(href);

	if (link?.dataset.loadState === "failed") {
		if (link.dataset.externalAsset === "true") {
			link.remove();
		}

		link = null;
	}

	if (!link) {
		link = document.createElement("link");
		link.rel = "stylesheet";
		link.href = href;
		link.dataset.externalAsset = "true";
		link.dataset.loadState = "loading";
		document.head.append(link);
	}

	const promise = new Promise((resolve, reject) => {
		if (stylesheetLoaded(link)) {
			link.dataset.loadState = "loaded";
			resolve();
			return;
		}

		const cleanup = () => {
			link.removeEventListener("load", handleLoad);
			link.removeEventListener("error", handleError);
		};

		const handleLoad = () => {
			cleanup();
			link.dataset.loadState = "loaded";
			resolve();
		};

		const handleError = () => {
			cleanup();
			link.dataset.loadState = "failed";
			stylesheetLoads.delete(href);
			reject(new Error(`Failed to load stylesheet: ${href}`));
		};

		link.addEventListener("load", handleLoad, { once: true });
		link.addEventListener("error", handleError, { once: true });
	});

	stylesheetLoads.set(href, promise);

	return promise;
};

export const loadExternalScript = (src, globalName = null) => {
	if (!isBrowser() || !src) {
		return Promise.resolve();
	}

	if (hasUsableGlobal(globalName)) {
		return Promise.resolve(window[globalName]);
	}

	if (scriptLoads.has(src)) {
		return scriptLoads.get(src);
	}

	let script = existingScript(src);

	if (script?.dataset.loadState === "failed") {
		if (script.dataset.externalAsset === "true") {
			script.remove();
		}

		script = null;
	}

	if (script && scriptLoaded(script) && !hasUsableGlobal(globalName)) {
		if (script.dataset.externalAsset === "true") {
			script.remove();
		}

		script = null;
	}

	const promise = new Promise((resolve, reject) => {
		const handleLoad = () => {
			if (!hasUsableGlobal(globalName)) {
				script.dataset.loadState = "failed";
				scriptLoads.delete(src);

				if (script.dataset.externalAsset === "true") {
					script.remove();
				}

				reject(
					new Error(
						`Global ${globalName} was not available after loading ${src}`,
					),
				);
				return;
			}

			script.dataset.loadState = "loaded";

			resolve(globalName ? window[globalName] : undefined);
		};

		const handleError = () => {
			script.dataset.loadState = "failed";
			scriptLoads.delete(src);

			reject(new Error(`Failed to load script: ${src}`));
		};

		if (script) {
			if (scriptLoaded(script) && hasUsableGlobal(globalName)) {
				script.dataset.loadState = "loaded";
				handleLoad();
				return;
			}

			script.dataset.loadState = "loading";
			script.addEventListener("load", handleLoad, { once: true });
			script.addEventListener("error", handleError, { once: true });
			return;
		}

		script = document.createElement("script");
		script.src = src;
		script.async = true;
		script.dataset.externalAsset = "true";
		script.dataset.loadState = "loading";
		script.addEventListener("load", handleLoad, { once: true });
		script.addEventListener("error", handleError, { once: true });
		document.head.append(script);
	});

	scriptLoads.set(src, promise);

	return promise;
};
