import { fileURLToPath } from "node:url";
import { render } from "svelte/server";
import PublicApp from "../svelte/PublicApp.svelte";
import InformationBoardShowPage from "../svelte/pages/InformationBoardShowPage.svelte";

const renderers = {
	informationBoardShowPage: (props = {}) => {
		const { body = "", head = "" } = render(InformationBoardShowPage, {
			props,
		});

		return { body, head };
	},
	publicApp: (props = {}) => {
		const { body = "", head = "" } = render(PublicApp, { props });

		return { body, head };
	},
};

const readStdin = async () => {
	let input = "";

	for await (const chunk of process.stdin) {
		input += chunk;
	}

	return input;
};

export const renderComponent = (component, props = {}) => {
	const renderer = renderers[component];

	if (!renderer) {
		throw new Error(`Unknown SSR component: ${component}`);
	}

	return renderer(props);
};

const runCli = async () => {
	try {
		const payload = JSON.parse((await readStdin()) || "{}");
		const result = renderComponent(payload.component, payload.props ?? {});

		process.stdout.write(JSON.stringify(result));
	} catch (error) {
		const message =
			error instanceof Error ? error.message : "Unknown Svelte SSR error";

		process.stderr.write(`${message}\n`);
		process.exitCode = 1;
	}
};

if (process.argv[1] && fileURLToPath(import.meta.url) === process.argv[1]) {
	await runCli();
}
