const confirmedForms = new WeakSet();

export const shouldSkipFormConfirmation = (form) => {
	if (!(form instanceof HTMLFormElement) || !confirmedForms.has(form)) {
		return false;
	}

	confirmedForms.delete(form);

	return true;
};

export const submitConfirmedForm = (form) => {
	if (!(form instanceof HTMLFormElement)) {
		return;
	}

	confirmedForms.add(form);

	if (typeof form.requestSubmit === "function") {
		form.requestSubmit();
		return;
	}

	const submitter = form.querySelector(
		'button[type="submit"], input[type="submit"]',
	);

	if (submitter instanceof HTMLElement) {
		submitter.click();
		return;
	}

	form.submit();
};
