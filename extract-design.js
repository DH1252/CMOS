const { chromium } = require("playwright");
const fs = require("fs");

(async () => {
	console.log("Launching browser...");
	const browser = await chromium.launch();
	const page = await browser.newPage();

	console.log("Navigating to https://himatekkom.ceits.id/ ...");
	await page.goto("https://himatekkom.ceits.id/", { waitUntil: "networkidle" });

	console.log("Extracting design tokens...");
	const designData = await page.evaluate(() => {
		const getComputedStyles = (element) => window.getComputedStyle(element);

		// Extract primary colors from common elements
		const bodyStyles = getComputedStyles(document.body);
		const headings = Array.from(
			document.querySelectorAll("h1, h2, h3, h4, h5, h6"),
		);
		const buttons = Array.from(
			document.querySelectorAll('button, a.btn, a[class*="bg-"]'),
		);
		const links = Array.from(document.querySelectorAll("a"));

		const colors = new Set();
		const bgColors = new Set();
		const fontFamilies = new Set();

		colors.add(bodyStyles.color);
		bgColors.add(bodyStyles.backgroundColor);
		fontFamilies.add(bodyStyles.fontFamily);

		headings.forEach((h) => {
			const s = getComputedStyles(h);
			colors.add(s.color);
			fontFamilies.add(s.fontFamily);
		});

		buttons.forEach((b) => {
			const s = getComputedStyles(b);
			colors.add(s.color);
			bgColors.add(s.backgroundColor);
		});

		return {
			colors: Array.from(colors),
			backgroundColors: Array.from(bgColors),
			fontFamilies: Array.from(fontFamilies),
		};
	});

	console.log("Taking a full page screenshot...");
	await page.screenshot({
		path: "docs/himatekkom-reference.png",
		fullPage: true,
	});

	const mdContent = `
# HIMATEKKOM ITS 2026 - Extracted Design Tokens

## Typography (Font Families)
${designData.fontFamilies.map((f) => `- \`${f}\``).join("\n")}

## Text Colors
${designData.colors.map((c) => `- \`${c}\``).join("\n")}

## Background Colors
${designData.backgroundColors.map((c) => `- \`${c}\``).join("\n")}
`;

	fs.appendFileSync("docs/frontend-design-reference.md", "\n" + mdContent);
	console.log("Updated docs/frontend-design-reference.md");

	await browser.close();
	console.log("Done.");
})();
