import "vite/modulepreload-polyfill";
import { createInertiaApp, router } from "@inertiajs/svelte";
import { hydrate, mount } from "svelte";
import { loadExternalScript } from "../svelte/lib/external-assets.js";

let AuthLayout = null;
let bootstrapModulePromise = null;

const ensureAuthLayout = async () => {
  if (AuthLayout) {
    return AuthLayout;
  }

  const module = await import("../svelte/layouts/AuthLayout.svelte");
  AuthLayout = module.default;

  return AuthLayout;
};

const ensureBootstrapModule = async () => {
  if (!bootstrapModulePromise) {
    bootstrapModulePromise = import("./bootstrap");
  }

  return bootstrapModulePromise;
};

const isTruthyDisabledFlag = (value) => {
  return ["1", "true", "yes", "on"].includes(String(value).toLowerCase());
};

const applyPostHogConfig = (posthog = null) => {
  if (typeof window === "undefined" || !posthog) {
    return;
  }

  window.__CMOS_POSTHOG_CONFIG__ = {
    key: posthog.key || "",
    host: posthog.host || "https://app.posthog.com",
    moduleUrl: posthog.moduleUrl || "",
    disabled: isTruthyDisabledFlag(posthog.disabled),
  };
};

const isPostHogEnabled = (page = null) => {
  const posthog = page?.props?.posthog;

  return (
    Boolean(posthog?.key) &&
    Boolean(posthog?.moduleUrl) &&
    !isTruthyDisabledFlag(posthog?.disabled)
  );
};

const capturePostHogPageview = (page = null) => {
  if (typeof window === "undefined") {
    return;
  }

  const runWithPostHog = window.__CMOS_WITH_POSTHOG__;

  if (typeof runWithPostHog !== "function") {
    return;
  }

  const pageUrl =
    page?.url ||
    (typeof window !== "undefined" ? window.location.pathname : "/");
  const component = page?.component || null;

  void runWithPostHog((posthogClient) => {
    posthogClient.capture("$pageview", {
      $current_url:
        typeof window !== "undefined" ? window.location.href : pageUrl,
      $page_path: pageUrl,
      inertia_component: component,
    });
  });
};

const sweetAlertUrl =
  "https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js";

let sweetAlertLoadPromise = null;

const ensureDialogLibrary = async () => {
  if (typeof window === "undefined") {
    return null;
  }

  if (window.Swal && !window.Swal.__externalAssetFallback) {
    return window.Swal;
  }

  if (!sweetAlertLoadPromise) {
    sweetAlertLoadPromise = loadExternalScript(sweetAlertUrl, "Swal")
      .then(() =>
        window.Swal && !window.Swal.__externalAssetFallback
          ? window.Swal
          : null,
      )
      .catch((error) => {
        console.warn(
          "SweetAlert2 failed to load, using fallback dialogs.",
          error,
        );

        return null;
      });
  }

  return sweetAlertLoadPromise;
};

const createDialogFacade = () => {
  const facade = {
    __externalAssetFallback: true,
    async fire(options = {}) {
      const dialogLibrary = await ensureDialogLibrary();

      if (dialogLibrary && dialogLibrary !== facade) {
        return dialogLibrary.fire(options);
      }

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
  };

  return facade;
};

if (typeof window !== "undefined" && !window.Swal) {
  window.Swal = createDialogFacade();
}

const pages = {
  ...import.meta.glob("../svelte/*Page.svelte"),
  ...import.meta.glob("../svelte/PublicApp.svelte"),
  ...import.meta.glob("../svelte/pages/**/*.svelte"),
  ...import.meta.glob("../svelte/public/**/*.svelte"),
};
const isLoginPath =
  typeof window !== "undefined" && window.location.pathname === "/login";
const inertiaRoot =
  typeof document !== "undefined" ? document.getElementById("app") : null;
const inertiaPagePayload = inertiaRoot?.dataset?.page || "";
const inertiaScriptPagePayload =
  typeof document !== "undefined"
    ? document.querySelector('script[data-page="app"][type="application/json"]')
        ?.textContent || ""
    : "";
const resolveInitialPage = (payload) => {
  if (!payload || payload === "null") {
    return null;
  }

  try {
    const initialPage = JSON.parse(payload);

    return initialPage && typeof initialPage.component === "string"
      ? initialPage
      : null;
  } catch (error) {
    console.error("Failed to parse the initial Inertia page payload.", error);

    return null;
  }
};

const isPublicPage = (name) =>
  name === "LandingPage" ||
  name === "PublicApp" ||
  name === "PublicComingSoonPage" ||
  name.startsWith("public/");
const isGuestPage = (name) => name === "LoginPage";

let loginFallbackMounted = false;

const mountLoginFallback = async (sourceProps = {}) => {
  if (!isLoginPath || loginFallbackMounted || !inertiaRoot) {
    return;
  }

  const props = {
    appName: "CMOS",
    loginUrl: "/login",
    homeUrl: "/",
    csrfToken:
      document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "",
    email: "",
    alertMessage: "",
    alertType: "",
    emailError: "",
    passwordError: "",
    remember: false,
    ...sourceProps,
  };

  const { default: LoginPage } = await import("../svelte/LoginPage.svelte");

  mount(LoginPage, {
    target: inertiaRoot,
    props,
  });

  loginFallbackMounted = true;
};

const initialInertiaPage =
  resolveInitialPage(inertiaPagePayload) ||
  resolveInitialPage(inertiaScriptPagePayload);

const deferBootstrapForLanding = (initialPage) => {
  if (typeof window === "undefined") {
    return;
  }

  const loadBootstrap = () => {
    void ensureBootstrapModule().then(() => {
      capturePostHogPageview(initialPage);
    });
  };

  if (document.readyState === "complete") {
    const idleCallback =
      window.requestIdleCallback ||
      ((callback) => window.setTimeout(callback, 350));
    idleCallback(loadBootstrap);
    return;
  }

  window.addEventListener(
    "load",
    () => {
      const idleCallback =
        window.requestIdleCallback ||
        ((callback) => window.setTimeout(callback, 350));
      idleCallback(loadBootstrap);
    },
    { once: true },
  );
};

if (typeof document !== "undefined") {
  applyPostHogConfig(initialInertiaPage?.props?.posthog || null);

  if (initialInertiaPage?.component === "LandingPage") {
    if (isPostHogEnabled(initialInertiaPage)) {
      deferBootstrapForLanding(initialInertiaPage);
    }
  } else {
    void ensureBootstrapModule().then(() => {
      capturePostHogPageview(initialInertiaPage);
    });
  }

  let navigationDirection = "forward";

  if (typeof window !== "undefined" && window.navigation) {
    window.navigation.addEventListener("navigate", (event) => {
      const destinationIndex = event.destination?.index;
      const currentIndex = window.navigation.currentEntry?.index;

      if (destinationIndex !== undefined && currentIndex !== undefined) {
        if (destinationIndex < currentIndex) {
          navigationDirection = "backward";
        } else {
          navigationDirection = "forward";
        }
      } else {
        navigationDirection = "forward";
      }
    });
  }

  router.on("success", () => {
    if (
      document.startViewTransition ||
      document.documentElement.classList.contains("forward-transition") ||
      document.documentElement.classList.contains("back-transition") ||
      document.documentElement.classList.contains("same-page-transition")
    ) {
      document.documentElement.classList.remove(
        "back-transition",
        "forward-transition",
        "same-page-transition",
      );
      if (window.location.pathname === window.__lastPathname) {
        document.documentElement.classList.add("same-page-transition");
      } else {
        document.documentElement.classList.add(
          `${navigationDirection}-transition`,
        );
      }
    }
  });

  router.on("before", () => {
    if (typeof window !== "undefined") {
      window.__lastPathname = window.location.pathname;
    }
  });

  router.on("navigate", (event) => {
    capturePostHogPageview(event?.detail?.page || null);
  });
}

const shouldBootStandaloneLogin =
  isLoginPath &&
  initialInertiaPage?.component === "LoginPage" &&
  !inertiaRoot?.hasAttribute("data-server-rendered");

if (shouldBootStandaloneLogin) {
  void mountLoginFallback(initialInertiaPage?.props || {});
}

if (inertiaRoot && initialInertiaPage && !shouldBootStandaloneLogin) {
  void (async () => {
    if (
      !isPublicPage(initialInertiaPage.component) &&
      !isGuestPage(initialInertiaPage.component)
    ) {
      await ensureAuthLayout();
    }

    return createInertiaApp({
      page: initialInertiaPage,
      resolve: async (name) => {
        const importer = pages[`../svelte/${name}.svelte`];

        if (!importer) {
          throw new Error(`Unknown Inertia page: ${name}`);
        }

        const page = await importer();

        return page;
      },
      layout: (name, page) => {
        if (isPublicPage(name) || isGuestPage(name)) {
          return undefined;
        }

        return [
          AuthLayout,
          {
            shell: page.props.shell,
            flash: page.props.flash,
            errors: page.props.errors,
            pageTitle: page.props.pageTitle,
            pageMeta: page.props.pageMeta,
            title: page.props.title,
            description: page.props.description,
          },
        ];
      },
      defaults: {
        visitOptions: () => {
          if (
            typeof document !== "undefined" &&
            document.startViewTransition &&
            !window.__skipEntryAnimation
          ) {
            return {
              viewTransition: (transition) => {
                transition.ready.catch(() => {});
                transition.updateCallbackDone.catch(() => {});
                transition.finished
                  .then(() => {
                    document.documentElement.classList.remove(
                      "back-transition",
                      "forward-transition",
                      "same-page-transition",
                    );
                  })
                  .catch(() => {});
              },
            };
          }
          return {};
        },
      },
      setup({ el, App, props }) {
        if (el?.hasAttribute("data-server-rendered")) {
          hydrate(App, { target: el, props });

          return;
        }

        mount(App, { target: el, props });
      },
    });
  })().catch((error) => {
    console.error("Failed to boot Inertia app.", error);

    void mountLoginFallback(initialInertiaPage?.props || {});
  });
}

if (inertiaRoot && !initialInertiaPage) {
  console.error(
    "Inertia root was found but no valid initial page payload could be resolved.",
  );

  void mountLoginFallback();
}
