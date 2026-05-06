const toHexChannel = (value) =>
	Math.max(0, Math.min(255, Math.round(value)))
		.toString(16)
		.padStart(2, "0")
		.toUpperCase();

export const parseCssColorToHex = (value) => {
	if (typeof value !== "string") {
		return null;
	}

	const normalized = value.trim();

	if (/^#[0-9A-Fa-f]{6}$/.test(normalized)) {
		return normalized.toUpperCase();
	}

	const rgbMatch = normalized.match(/^rgba?\((.+)\)$/i);

	if (rgbMatch) {
		const channels = rgbMatch[1]
			.replace(/\//g, " ")
			.replace(/,/g, " ")
			.trim()
			.split(/\s+/)
			.slice(0, 3)
			.map((channel) =>
				channel.endsWith("%")
					? (Number.parseFloat(channel) * 255) / 100
					: Number.parseFloat(channel),
			);

		if (
			channels.length === 3 &&
			channels.every((channel) => Number.isFinite(channel))
		) {
			return `#${channels.map(toHexChannel).join("")}`;
		}
	}

	const srgbMatch = normalized.match(
		/^color\(srgb\s+([\d.]+%?)\s+([\d.]+%?)\s+([\d.]+%?)(?:\s*\/\s*[\d.]+%?)?\)$/i,
	);

	if (srgbMatch) {
		const channels = srgbMatch.slice(1, 4).map((channel) => {
			if (channel.endsWith("%")) {
				return (Number.parseFloat(channel) * 255) / 100;
			}

			return Number.parseFloat(channel) * 255;
		});

		if (channels.every((channel) => Number.isFinite(channel))) {
			return `#${channels.map(toHexChannel).join("")}`;
		}
	}

	return null;
};

export const resolveThemeColor = (element, variableName, fallback) => {
	if (!element || typeof document === "undefined") {
		return fallback;
	}

	const probe = document.createElement("span");
	probe.style.color = `var(${variableName})`;
	probe.style.display = "none";
	element.appendChild(probe);

	const resolvedColor = getComputedStyle(probe).color;
	probe.remove();

	return parseCssColorToHex(resolvedColor) || fallback;
};

export const hexToRgba = (hex, alpha = 1) => {
	if (typeof hex !== "string" || !/^#[0-9A-Fa-f]{6}$/.test(hex)) {
		return `rgba(217, 174, 67, ${alpha})`;
	}

	const red = Number.parseInt(hex.slice(1, 3), 16);
	const green = Number.parseInt(hex.slice(3, 5), 16);
	const blue = Number.parseInt(hex.slice(5, 7), 16);

	return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
};
