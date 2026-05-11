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

const renderInertiaPage = (page) =>
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
  });

const readStdin = () =>
  new Promise((resolve, reject) => {
    let payload = "";

    process.stdin.setEncoding("utf8");
    process.stdin.on("data", (chunk) => {
      payload += chunk;
    });
    process.stdin.on("end", () => resolve(payload));
    process.stdin.on("error", reject);
  });

const renderOnce = async () => {
  const payload = await readStdin();
  const page = JSON.parse(payload);
  const response = await renderInertiaPage(page);

  process.stdout.write(JSON.stringify(response));
};

if (process.argv.includes("--render-once")) {
  renderOnce().catch((error) => {
    console.error(error);
    process.exitCode = 1;
  });
} else {
  createServer((page) => renderInertiaPage(page));
}
