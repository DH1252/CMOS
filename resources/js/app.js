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

const applyBrandTheme = (themeName) => {
  if (typeof document === "undefined") {
    return;
  }

  const fallbackTheme = "purple";
  const resolvedTheme =
    typeof themeName === "string" && themeName.length > 0
      ? themeName
      : fallbackTheme;

  document.documentElement.setAttribute("data-brand", resolvedTheme);
};

const isDarkModeActive = () => {
  if (typeof document === "undefined") {
    return false;
  }

  return document.documentElement.getAttribute("data-theme") === "dark";
};

const applyThemeVariables = (variables = null) => {
  if (
    typeof document === "undefined" ||
    !variables ||
    typeof variables !== "object"
  ) {
    return;
  }

  // Skip on public pages — the blade template manages its own CSS variables
  if (document.documentElement.getAttribute("data-theme") === "public") {
    return;
  }

  // Handle legacy flat variable format
  if (!variables.customCss && !variables.light && !variables.dark) {
    Object.entries(variables).forEach(([token, value]) => {
      if (typeof token !== "string" || typeof value !== "string") {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    });

    return;
  }

  const customCss = variables.customCss || variables;
  const isDark = isDarkModeActive();

  // Apply shared variables (signal colors, etc.)
  if (customCss.shared && typeof customCss.shared === "object") {
    Object.entries(customCss.shared).forEach(([token, value]) => {
      if (typeof token !== "string" || typeof value !== "string") {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    });
  }

  // Apply mode-specific variables
  const modeVars = isDark ? customCss.dark || {} : customCss.light || {};

  Object.entries(modeVars).forEach(([token, value]) => {
    if (typeof token !== "string" || typeof value !== "string") {
      return;
    }

    document.documentElement.style.setProperty(`--${token}`, value);
  });
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
  name === "LandingPage" || name === "PublicApp" || name.startsWith("public/");
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
  const rootBrand = document.documentElement.getAttribute("data-brand");
  const pageBrand =
    initialInertiaPage?.props?.themeColor ||
    initialInertiaPage?.props?.shell?.themeColor ||
    initialInertiaPage?.props?.theme?.color ||
    rootBrand ||
    "purple";
  const themeVariables =
    initialInertiaPage?.props?.themeVariables ||
    initialInertiaPage?.props?.shell?.themeVariables ||
    initialInertiaPage?.props?.theme?.variables ||
    null;
  const themeCustomCss =
    initialInertiaPage?.props?.themeCustomCss ||
    initialInertiaPage?.props?.shell?.themeCustomCss ||
    initialInertiaPage?.props?.theme?.customCss ||
    null;

  applyBrandTheme(pageBrand);
  applyThemeVariables({ ...themeVariables, customCss: themeCustomCss });

  if (initialInertiaPage?.component === "LandingPage") {
    deferBootstrapForLanding(initialInertiaPage);
  } else {
    void ensureBootstrapModule().then(() => {
      capturePostHogPageview(initialInertiaPage);
    });
  }

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
      setup({ el, App, props }) {
        const brandFromProps =
          props?.initialPage?.props?.themeColor ||
          props?.initialPage?.props?.shell?.themeColor ||
          props?.initialPage?.props?.theme?.color ||
          "purple";
        const variablesFromProps =
          props?.initialPage?.props?.themeVariables ||
          props?.initialPage?.props?.shell?.themeVariables ||
          props?.initialPage?.props?.theme?.variables ||
          null;
        const customCssFromProps =
          props?.initialPage?.props?.themeCustomCss ||
          props?.initialPage?.props?.shell?.themeCustomCss ||
          props?.initialPage?.props?.theme?.customCss ||
          null;
        applyBrandTheme(brandFromProps);
        applyThemeVariables({
          ...variablesFromProps,
          customCss: customCssFromProps,
        });

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
