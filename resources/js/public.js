import "vite/modulepreload-polyfill";
import { createInertiaApp, router } from "@inertiajs/svelte";
import { hydrate, mount } from "svelte";

const pages = {
  ...import.meta.glob("../svelte/LandingPage.svelte", { eager: true }),
  ...import.meta.glob("../svelte/PublicApp.svelte"),
  ...import.meta.glob("../svelte/PublicComingSoonPage.svelte"),
  ...import.meta.glob("../svelte/public/**/*.svelte"),
};

const resolvePublicPage = async (name) => {
  const page = pages[`../svelte/${name}.svelte`];

  if (!page) {
    throw new Error(`Unknown Inertia page: ${name}`);
  }

  if (typeof page === "function") {
    return await page();
  }

  return page;
};

const applyBrandTheme = (themeName) => {
  if (typeof document === "undefined") {
    return;
  }

  document.documentElement.setAttribute(
    "data-brand",
    typeof themeName === "string" && themeName.length > 0
      ? themeName
      : "purple",
  );
};

const applyThemeVariables = (variables = null) => {
  if (
    typeof document === "undefined" ||
    !variables ||
    typeof variables !== "object"
  ) {
    return;
  }

  const customCss = variables.customCss || variables;
  const landingVars = customCss.landing || {};

  Object.entries({ ...(variables || {}), ...(landingVars || {}) }).forEach(
    ([token, value]) => {
      if (typeof token !== "string" || typeof value !== "string") {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    },
  );
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

let bootstrapModulePromise = null;

const ensureBootstrapModule = async () => {
  if (!bootstrapModulePromise) {
    bootstrapModulePromise = import("./bootstrap");
  }

  return bootstrapModulePromise;
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

const deferBootstrapForPublic = (initialPage) => {
  if (typeof window === "undefined") {
    return;
  }

  const loadBootstrap = () => {
    void ensureBootstrapModule().then(() => {
      capturePostHogPageview(initialPage);
      router.on("navigate", (event) => {
        capturePostHogPageview(event?.detail?.page || null);
      });
    });
  };

  const schedule = () => {
    const idleCallback =
      window.requestIdleCallback ||
      ((callback) => window.setTimeout(callback, 0));
    idleCallback(loadBootstrap, { timeout: 750 });
  };

  if (
    document.readyState === "complete" ||
    document.readyState === "interactive"
  ) {
    schedule();
    return;
  }

  window.addEventListener("DOMContentLoaded", schedule, { once: true });
};

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

const initialInertiaPage =
  resolveInitialPage(inertiaPagePayload) ||
  resolveInitialPage(inertiaScriptPagePayload);

let publicAppBootPromise = null;

const bootPublicInertiaApp = () => {
  if (!inertiaRoot || !initialInertiaPage) {
    return Promise.resolve();
  }

  if (!publicAppBootPromise) {
    publicAppBootPromise = createInertiaApp({
      page: initialInertiaPage,
      resolve: resolvePublicPage,
      layout: () => undefined,
      defaults: {
        visitOptions: () => {
          if (typeof document !== "undefined" && document.startViewTransition) {
            return {
              viewTransition: (transition) => {
                transition.ready.catch(() => {});
                transition.updateCallbackDone.catch(() => {});
                transition.finished
                  .then(() => {
                    document.documentElement.classList.remove(
                      "back-transition",
                      "forward-transition",
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
  }

  return publicAppBootPromise;
};

if (typeof document !== "undefined" && initialInertiaPage) {
  const pageBrand =
    initialInertiaPage?.props?.themeColor ||
    initialInertiaPage?.props?.theme?.color ||
    document.documentElement.getAttribute("data-brand") ||
    "purple";
  const themeVariables =
    initialInertiaPage?.props?.themeVariables ||
    initialInertiaPage?.props?.theme?.variables ||
    null;
  const themeCustomCss =
    initialInertiaPage?.props?.themeCustomCss ||
    initialInertiaPage?.props?.theme?.customCss ||
    null;

  applyBrandTheme(pageBrand);
  applyThemeVariables({ ...themeVariables, customCss: themeCustomCss });
  applyPostHogConfig(initialInertiaPage?.props?.posthog || null);

  if (isPostHogEnabled(initialInertiaPage)) {
    deferBootstrapForPublic(initialInertiaPage);
  }

  void bootPublicInertiaApp().catch((error) => {
    console.error("Failed to boot public Inertia app.", error);
  });

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
    if (document.startViewTransition) {
      document.documentElement.classList.remove(
        "back-transition",
        "forward-transition",
      );
      document.documentElement.classList.add(
        `${navigationDirection}-transition`,
      );
    }
  });

  router.on("before", () => {
    if (typeof window !== "undefined") {
      window.__lastPathname = window.location.pathname;

      // Capture running state of floating/pan animations to seamlessly transition them
      window.__lastAnimationTimes = {};
      const captureAnim = (selector, key, animNameSub) => {
        const el = document.querySelector(selector);
        if (el) {
          const anims = el.getAnimations();
          const anim = anims.find(
            (a) => a.animationName && a.animationName.includes(animNameSub),
          );
          if (anim && anim.currentTime !== null) {
            window.__lastAnimationTimes[key] = {
              time: anim.currentTime,
              timestamp: Date.now(),
            };
          }
        }
      };

      captureAnim(".animate-slow-pan", "botanical", "slowPan");
      captureAnim(".star-large", "starLarge", "floatLarge");
      captureAnim(".star-small", "starSmall", "floatSmall");
    }
  });

  router.on("navigate", (event) => {
    capturePostHogPageview(event?.detail?.page || null);
  });
}
