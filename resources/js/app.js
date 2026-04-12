import "./bootstrap";
import { hydrate, mount } from "svelte";

const scriptRegistry = new Map();

const loadScriptOnce = (src) => {
	if (typeof document === "undefined") {
		return Promise.resolve();
	}

	if (scriptRegistry.has(src)) {
		return scriptRegistry.get(src);
	}

	const existing = document.querySelector(`script[src="${src}"]`);

	if (existing) {
		const promise = Promise.resolve(existing);
		scriptRegistry.set(src, promise);
		return promise;
	}

	const promise = new Promise((resolve, reject) => {
		const script = document.createElement("script");
		script.src = src;
		script.async = true;
		script.onload = () => resolve(script);
		script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
		document.head.appendChild(script);
	});

	scriptRegistry.set(src, promise);

	return promise;
};

const loadSweetAlert = async () => {
	if (window.Swal) {
		return window.Swal;
	}

	await loadScriptOnce("https://cdn.jsdelivr.net/npm/sweetalert2@11");

	return window.Swal;
};

const loadLegacyTableLibraries = async () => {
	if (window.$?.fn?.DataTable) {
		return;
	}

	await loadScriptOnce("https://code.jquery.com/jquery-3.7.1.min.js");
	await loadScriptOnce(
		"https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js",
	);
};

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
	reportDashboardPage: async () => {
		await loadScriptOnce("https://cdn.jsdelivr.net/npm/chart.js");
		return import("../svelte/pages/ReportDashboardPage.svelte");
	},
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
	themeColor: mountPoint.dataset.themeColor || "purple",
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

const initLegacyTables = async () => {
	if (!document.querySelector("table.datatable")) {
		return;
	}

	await loadLegacyTableLibraries();

	document.querySelectorAll("table.datatable").forEach((table) => {
		if (window.$.fn.DataTable.isDataTable(table)) {
			return;
		}

		window.$(table).DataTable({
			responsive: true,
			language: {
				search: "Cari:",
				lengthMenu: "Tampilkan _MENU_ data",
				info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
				infoEmpty: "Tidak ada data",
				infoFiltered: "(filter dari _MAX_ total data)",
				zeroRecords: "Tidak ada data yang cocok",
				paginate: {
					first: "Pertama",
					last: "Terakhir",
					next: "Selanjutnya",
					previous: "Sebelumnya",
				},
			},
			dom: '<"d-flex justify-between align-center mb-3"lf>rt<"d-flex justify-between align-center mt-3"ip>',
			pageLength: 10,
		});
	});
};

const confirmAction = async (event, label) => {
	const text = `Lanjutkan tindakan untuk ${label || "item ini"}?`;

	try {
		const Swal = await loadSweetAlert();
		const result = await Swal.fire({
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
		return;
	} catch (error) {
		console.error("Failed to load SweetAlert", error);
	}

	if (window.confirm(text)) {
		event.currentTarget.closest("form")?.submit();
	}
};

const showSessionSwal = async () => {
	const payload = parseJson("session-swal-data");

	if (!payload?.title) {
		return;
	}

	try {
		const Swal = await loadSweetAlert();
		await Swal.fire({
			icon: payload.type || "success",
			title: payload.title,
			text: payload.text || "",
			confirmButtonColor: payload.confirmButtonColor || "#f5c518",
		});
	} catch (error) {
		console.error("Failed to load session alert", error);
	}
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
	await Promise.all([initLegacyTables(), showSessionSwal()]);
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", bootLegacyHelpers, {
		once: true,
	});
} else {
	bootLegacyHelpers();
}

document.addEventListener("cmos:content-mounted", bootLegacyHelpers);
