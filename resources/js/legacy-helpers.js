let confirmListenerBound = false;
let sessionSwalShown = false;

const enhanceLegacyTable = (table) => {
	if (table.dataset.enhanced === "true") {
		return;
	}

	const tbody = table.tBodies[0];

	if (!tbody) {
		return;
	}

	const rows = Array.from(tbody.rows);
	const toolbar = document.createElement("div");
	const searchLabel = document.createElement("label");
	const searchText = document.createElement("span");
	const searchInput = document.createElement("input");
	const pageSizeLabel = document.createElement("label");
	const pageSizeText = document.createElement("span");
	const pageSizeSelect = document.createElement("select");
	const footer = document.createElement("div");
	const info = document.createElement("div");
	const pagination = document.createElement("div");
	const previousButton = document.createElement("button");
	const nextButton = document.createElement("button");
	const pageState = document.createElement("span");

	let query = "";
	let pageSize = 10;
	let currentPage = 1;

	toolbar.className = "d-flex justify-between align-center gap-3 mb-3";
	footer.className = "d-flex justify-between align-center gap-3 mt-3";
	pagination.className = "d-flex align-center gap-2";
	info.className = "text-muted fs-sm";
	pageState.className = "text-muted fs-sm";

	searchLabel.style.minWidth = "min(100%, 18rem)";
	searchLabel.style.display = "grid";
	searchLabel.style.gap = "0.45rem";
	searchText.className = "text-muted fs-sm";
	searchText.textContent = "Cari";
	searchInput.type = "search";
	searchInput.className = "form-control";
	searchInput.placeholder = "Cari data";

	pageSizeLabel.style.minWidth = "9rem";
	pageSizeLabel.style.display = "grid";
	pageSizeLabel.style.gap = "0.45rem";
	pageSizeText.className = "text-muted fs-sm";
	pageSizeText.textContent = "Tampilkan";
	pageSizeSelect.className = "form-select";

	[10, 25, 50, -1].forEach((option) => {
		const element = document.createElement("option");
		element.value = String(option);
		element.textContent = option === -1 ? "Semua" : String(option);
		pageSizeSelect.appendChild(element);
	});

	previousButton.type = "button";
	previousButton.className = "btn btn-secondary btn-sm";
	previousButton.textContent = "Sebelumnya";
	nextButton.type = "button";
	nextButton.className = "btn btn-secondary btn-sm";
	nextButton.textContent = "Selanjutnya";

	searchLabel.append(searchText, searchInput);
	pageSizeLabel.append(pageSizeText, pageSizeSelect);
	pagination.append(previousButton, pageState, nextButton);
	toolbar.append(searchLabel, pageSizeLabel);
	footer.append(info, pagination);

	table.parentElement?.insertBefore(toolbar, table);
	table.parentElement?.appendChild(footer);

	const render = () => {
		const filteredRows = rows.filter((row) => {
			return row.textContent.toLowerCase().includes(query);
		});
		const effectivePageSize =
			pageSize === -1 ? filteredRows.length || 1 : pageSize;
		const totalPages = Math.max(
			1,
			Math.ceil(filteredRows.length / effectivePageSize),
		);

		if (currentPage > totalPages) {
			currentPage = totalPages;
		}

		const start = (currentPage - 1) * effectivePageSize;
		const visibleRows = new Set(
			filteredRows.slice(start, start + effectivePageSize),
		);

		rows.forEach((row) => {
			row.hidden = !visibleRows.has(row);
		});

		const rangeStart = filteredRows.length === 0 ? 0 : start + 1;
		const rangeEnd =
			filteredRows.length === 0
				? 0
				: Math.min(start + effectivePageSize, filteredRows.length);

		info.textContent = `Menampilkan ${rangeStart}-${rangeEnd} dari ${filteredRows.length} data`;
		pageState.textContent = `Halaman ${currentPage} / ${totalPages}`;
		previousButton.disabled = currentPage === 1;
		nextButton.disabled = currentPage === totalPages;
	};

	searchInput.addEventListener("input", (event) => {
		query = event.currentTarget.value.trim().toLowerCase();
		currentPage = 1;
		render();
	});

	pageSizeSelect.addEventListener("change", (event) => {
		pageSize = Number(event.currentTarget.value);
		currentPage = 1;
		render();
	});

	previousButton.addEventListener("click", () => {
		currentPage = Math.max(1, currentPage - 1);
		render();
	});

	nextButton.addEventListener("click", () => {
		const totalPages = Math.max(
			1,
			Math.ceil(
				rows.filter((row) => row.textContent.toLowerCase().includes(query))
					.length / (pageSize === -1 ? rows.length || 1 : pageSize),
			),
		);
		currentPage = Math.min(totalPages, currentPage + 1);
		render();
	});

	table.dataset.enhanced = "true";
	render();
};

const initLegacyTables = () => {
	document.querySelectorAll("table.datatable").forEach((table) => {
		enhanceLegacyTable(table);
	});
};

const confirmAction = async (event, label) => {
	const text = `Lanjutkan tindakan untuk ${label || "item ini"}?`;
	const result = await window.Swal.fire({
		title: "Konfirmasi",
		text,
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Lanjutkan",
		cancelButtonText: "Batal",
	});

	if (result.isConfirmed) {
		event.currentTarget.closest("form")?.submit();
	}
};

const bindConfirmListener = () => {
	if (confirmListenerBound) {
		return;
	}

	document.addEventListener("click", (event) => {
		const button = event.target.closest(
			"[data-confirm-delete], [data-confirm]",
		);

		if (!button) {
			return;
		}

		event.preventDefault();
		void confirmAction(
			event,
			button.dataset.confirmDelete || button.dataset.confirm || "item ini",
		);
	});

	confirmListenerBound = true;
};

const showSessionSwal = async (parseJson) => {
	if (sessionSwalShown) {
		return;
	}

	const payload = parseJson("session-swal-data");

	if (!payload?.title) {
		return;
	}

	sessionSwalShown = true;

	await window.Swal.fire({
		icon: payload.type || "success",
		title: payload.title,
		text: payload.text || "",
	});
};

export const bootLegacyHelpers = async (parseJson) => {
	initLegacyTables();
	bindConfirmListener();
	await showSessionSwal(parseJson);
};
