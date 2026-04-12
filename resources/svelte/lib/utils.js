const toClassName = (value) => {
	if (!value) {
		return "";
	}

	if (typeof value === "string") {
		return value;
	}

	if (Array.isArray(value)) {
		return value.map(toClassName).filter(Boolean).join(" ");
	}

	if (typeof value === "object") {
		return Object.entries(value)
			.filter(([, included]) => Boolean(included))
			.map(([className]) => className)
			.join(" ");
	}

	return "";
};

export const cn = (...inputs) =>
	inputs.map(toClassName).filter(Boolean).join(" ");
