import { createInertiaApp } from "@inertiajs/svelte";
import createServer from "@inertiajs/svelte/server";
import { render } from "svelte/server";
import AuthLayout from "../svelte/layouts/AuthLayout.svelte";

const pages = {
  ...import.meta.glob("../svelte/*Page.svelte"),
  ...import.meta.glob("../svelte/pages/**/*.svelte"),
  ...import.meta.glob("../svelte/PublicApp.svelte"),
};

const isPublicPage = (name) =>
  name === "LandingPage" || name === "PublicApp" || name.startsWith("public/");
const isGuestPage = (name) => name === "LoginPage";

createServer((page) =>
  createInertiaApp({
    page,
    render,
    resolve: async (name) => {
      const importer = pages[`../svelte/${name}.svelte`];

      if (!importer) {
        throw new Error(`Unknown Inertia page: ${name}`);
      }

      return await importer();
    },
    layout: (name, pagePayload) => {
      if (isPublicPage(name) || isGuestPage(name)) {
        return undefined;
      }

      return [
        AuthLayout,
        {
          shell: pagePayload.props.shell,
          flash: pagePayload.props.flash,
          errors: pagePayload.props.errors,
          pageTitle: pagePayload.props.pageTitle,
          pageMeta: pagePayload.props.pageMeta,
          title: pagePayload.props.title,
          description: pagePayload.props.description,
        },
      ];
    },
    setup: ({ App, props }) => {
      return render(App, { props });
    },
  }),
);
