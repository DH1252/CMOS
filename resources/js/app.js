import "./bootstrap";
import { hydrate, mount } from "svelte";

const createDialogFacade = () => ({
	async fire(options = {}) {
		const title = options.title || "";
		const text = options.text || "";
		const message = [title, text].filter(Boolean).join("\n\n").trim();

		if (options.showCancelButton) {
			return {
				isConfirmed: window.confirm(message || "Lanjutkan tindakan ini?"),
			};
		}

		window.alert(message || title || "Notifikasi");

		return {
			isConfirmed: true,
		};
	},
});

if (typeof window !== "undefined" && !window.Swal) {
	window.Swal = createDialogFacade();
}

const parseJson = (id) => {
	const node = document.getElementById(id);

	if (!node?.textContent) {
		return {};
	}

	try {
		return JSON.parse(node.textContent);
	} catch (error) {
		console.error(`Failed to parse JSON from #${id}`, error);
		return {};
	}
};

const loaders = {
	authApp: () => import("../svelte/AuthApp.svelte"),
	publicApp: () => import("../svelte/PublicApp.svelte"),
	dashboardPage: () => import("../svelte/DashboardPage.svelte"),
	loginPage: () => import("../svelte/LoginPage.svelte"),
	crudTablePage: () => import("../svelte/pages/CrudTablePage.svelte"),
	entityFormPage: () => import("../svelte/pages/EntityFormPage.svelte"),
	entityDetailPage: () => import("../svelte/pages/EntityDetailPage.svelte"),
	programDetailPage: () => import("../svelte/pages/ProgramDetailPage.svelte"),
	linkDirectoryPage: () => import("../svelte/pages/LinkDirectoryPage.svelte"),
	driveDirectoryPage: () => import("../svelte/pages/DriveDirectoryPage.svelte"),
	informationBoardIndexPage: () =>
		import("../svelte/pages/InformationBoardIndexPage.svelte"),
	informationBoardEditorPage: () =>
		import("../svelte/pages/InformationBoardEditorPage.svelte"),
	informationBoardShowPage: () =>
		import("../svelte/pages/InformationBoardShowPage.svelte"),
	announcementFeedPage: () =>
		import("../svelte/pages/AnnouncementFeedPage.svelte"),
	taskHubPage: () => import("../svelte/pages/TaskHubPage.svelte"),
	taskFormPage: () => import("../svelte/pages/TaskFormPage.svelte"),
	taskDetailPage: () => import("../svelte/pages/TaskDetailPage.svelte"),
	taskBoardPage: () => import("../svelte/pages/TaskBoardPage.svelte"),
	evaluationHubPage: () => import("../svelte/pages/EvaluationHubPage.svelte"),
	evaluationStaffPage: () =>
		import("../svelte/pages/EvaluationStaffPage.svelte"),
	evaluationFormPage: () => import("../svelte/pages/EvaluationFormPage.svelte"),
	evaluationHistoryPage: () =>
		import("../svelte/pages/EvaluationHistoryPage.svelte"),
	timelineCollectionPage: () =>
		import("../svelte/pages/TimelineCollectionPage.svelte"),
	timelineFormPage: () => import("../svelte/pages/TimelineFormPage.svelte"),
	timelineCalendarPage: () =>
		import("../svelte/pages/TimelineCalendarPage.svelte"),
	reportDashboardPage: () =>
		import("../svelte/pages/ReportDashboardPage.svelte"),
	settingsPage: () => import("../svelte/pages/SettingsPage.svelte"),
	notificationInboxPage: () =>
		import("../svelte/pages/NotificationInboxPage.svelte"),
	profileSettingsPage: () =>
		import("../svelte/pages/ProfileSettingsPage.svelte"),
	userImportPage: () => import("../svelte/pages/UserImportPage.svelte"),
	messagesPage: () => import("../svelte/pages/MessagesPage.svelte"),
};

const mountIfPresent = (mountId, loadComponent, propsFactory = () => ({})) => {
	const mountPoint = document.getElementById(mountId);

	if (!mountPoint) {
		return;
	}

	void loadComponent()
		.then(({ default: Component }) => {
			const shouldHydrate = mountPoint.dataset.ssr === "true";
			const bootComponent = shouldHydrate ? hydrate : mount;

			bootComponent(Component, {
				target: mountPoint,
				props: propsFactory(mountPoint),
			});
		})
		.catch((error) => {
			console.error(`Failed to load Svelte component for #${mountId}`, error);
		});
};

mountIfPresent("svelte-login-root", loaders.loginPage, (mountPoint) => ({
	appName: mountPoint.dataset.appName || "CMOS",
	loginUrl: mountPoint.dataset.loginUrl || "/login",
	homeUrl: mountPoint.dataset.homeUrl || "/",
	csrfToken: mountPoint.dataset.csrfToken || "",
	email: mountPoint.dataset.email || "",
	alertMessage: mountPoint.dataset.alertMessage || "",
	alertType: mountPoint.dataset.alertType || "",
	emailError: mountPoint.dataset.emailError || "",
	passwordError: mountPoint.dataset.passwordError || "",
	remember: mountPoint.dataset.remember === "1",
}));

[
	["svelte-public-root", loaders.publicApp, "svelte-public-props"],
	["svelte-auth-root", loaders.authApp, "svelte-auth-props"],
	["svelte-dashboard-root", loaders.dashboardPage, "svelte-dashboard-props"],
	["svelte-crud-table-root", loaders.crudTablePage, "svelte-crud-table-props"],
	[
		"svelte-entity-form-root",
		loaders.entityFormPage,
		"svelte-entity-form-props",
	],
	[
		"svelte-entity-detail-root",
		loaders.entityDetailPage,
		"svelte-entity-detail-props",
	],
	[
		"svelte-program-detail-root",
		loaders.programDetailPage,
		"svelte-program-detail-props",
	],
	[
		"svelte-link-directory-root",
		loaders.linkDirectoryPage,
		"svelte-link-directory-props",
	],
	[
		"svelte-drive-directory-root",
		loaders.driveDirectoryPage,
		"svelte-drive-directory-props",
	],
	[
		"svelte-information-board-index-root",
		loaders.informationBoardIndexPage,
		"svelte-information-board-index-props",
	],
	[
		"svelte-information-board-editor-root",
		loaders.informationBoardEditorPage,
		"svelte-information-board-editor-props",
	],
	[
		"svelte-information-board-show-root",
		loaders.informationBoardShowPage,
		"svelte-information-board-show-props",
	],
	[
		"svelte-announcement-feed-root",
		loaders.announcementFeedPage,
		"svelte-announcement-feed-props",
	],
	["svelte-task-hub-root", loaders.taskHubPage, "svelte-task-hub-props"],
	["svelte-task-form-root", loaders.taskFormPage, "svelte-task-form-props"],
	[
		"svelte-task-detail-root",
		loaders.taskDetailPage,
		"svelte-task-detail-props",
	],
	["svelte-task-board-root", loaders.taskBoardPage, "svelte-task-board-props"],
	[
		"svelte-evaluation-hub-root",
		loaders.evaluationHubPage,
		"svelte-evaluation-hub-props",
	],
	[
		"svelte-evaluation-staff-root",
		loaders.evaluationStaffPage,
		"svelte-evaluation-staff-props",
	],
	[
		"svelte-evaluation-form-root",
		loaders.evaluationFormPage,
		"svelte-evaluation-form-props",
	],
	[
		"svelte-evaluation-history-root",
		loaders.evaluationHistoryPage,
		"svelte-evaluation-history-props",
	],
	[
		"svelte-timeline-collection-root",
		loaders.timelineCollectionPage,
		"svelte-timeline-collection-props",
	],
	[
		"svelte-timeline-form-root",
		loaders.timelineFormPage,
		"svelte-timeline-form-props",
	],
	[
		"svelte-timeline-calendar-root",
		loaders.timelineCalendarPage,
		"svelte-timeline-calendar-props",
	],
	[
		"svelte-report-dashboard-root",
		loaders.reportDashboardPage,
		"svelte-report-dashboard-props",
	],
	["svelte-settings-root", loaders.settingsPage, "svelte-settings-props"],
	[
		"svelte-notification-inbox-root",
		loaders.notificationInboxPage,
		"svelte-notification-inbox-props",
	],
	[
		"svelte-profile-settings-root",
		loaders.profileSettingsPage,
		"svelte-profile-settings-props",
	],
	[
		"svelte-user-import-root",
		loaders.userImportPage,
		"svelte-user-import-props",
	],
	["svelte-messages-root", loaders.messagesPage, "svelte-messages-props"],
].forEach(([mountId, loader, propsId]) => {
	mountIfPresent(mountId, loader, () => parseJson(propsId));
});

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

const showSessionSwal = async () => {
	const payload = parseJson("session-swal-data");

	if (!payload?.title) {
		return;
	}

	await window.Swal.fire({
		icon: payload.type || "success",
		title: payload.title,
		text: payload.text || "",
	});
};

document.addEventListener("click", (event) => {
	const button = event.target.closest("[data-confirm-delete], [data-confirm]");

	if (!button) {
		return;
	}

	event.preventDefault();
	confirmAction(
		event,
		button.dataset.confirmDelete || button.dataset.confirm || "item ini",
	);
});

const bootLegacyHelpers = async () => {
	initLegacyTables();
	await showSessionSwal();
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", bootLegacyHelpers, {
		once: true,
	});
} else {
	bootLegacyHelpers();
}

document.addEventListener("cmos:content-mounted", bootLegacyHelpers);
