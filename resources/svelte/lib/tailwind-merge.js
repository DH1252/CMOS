const normalize = (value) => {
	if (!value) {
		return "";
	}

	if (Array.isArray(value)) {
		return value.map(normalize).filter(Boolean).join(" ");
	}

	if (typeof value === "object") {
		return Object.entries(value)
			.filter(([, included]) => Boolean(included))
			.map(([className]) => className)
			.join(" ");
	}

	return String(value);
};

export const twMerge = (...classLists) =>
	classLists.map(normalize).filter(Boolean).join(" ");

export const extendTailwindMerge = () => twMerge;
