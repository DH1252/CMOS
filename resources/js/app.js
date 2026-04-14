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

let legacyHelpersPromise = null;

const needsLegacyHelpers = () => {
	return Boolean(
		document.querySelector("table.datatable") ||
			document.querySelector("[data-confirm-delete], [data-confirm]") ||
			document.getElementById("session-swal-data"),
	);
};

const bootLegacyHelpers = async () => {
	if (!needsLegacyHelpers()) {
		return;
	}

	legacyHelpersPromise ??= import("./legacy-helpers");

	const { bootLegacyHelpers: runLegacyHelpers } = await legacyHelpersPromise;
	await runLegacyHelpers(parseJson);
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", bootLegacyHelpers, {
		once: true,
	});
} else {
	bootLegacyHelpers();
}

document.addEventListener("cmos:content-mounted", bootLegacyHelpers);
