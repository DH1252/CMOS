const defaultPreviewTheme = {
  backgroundColor: "#18141E",
  surfaceColor: "#221F2E",
  textColor: "#F0E6C8",
  headingColor: "#F0E6C8",
  softColor: "#CABE9E",
  mutedColor: "#9E9278",
  lineColor: "#8A7A3C",
  linkColor: "#D9AE43",
};

const resolvePreviewTheme = (theme = {}) => ({
  ...defaultPreviewTheme,
  ...Object.fromEntries(
    Object.entries(theme).filter(
      ([, value]) => typeof value === "string" && value.trim().length,
    ),
  ),
});

const buildContentRules = (selector, theme) => {
  const resolved = resolvePreviewTheme(theme);

  return `
    ${selector} { font-family: 'Public Sans', sans-serif; font-size: 16px; line-height: 1.65; background: ${resolved.backgroundColor}; color: ${resolved.textColor}; }
    ${selector} p { margin: 0 0 0.6rem 0; color: ${resolved.textColor}; }
    ${selector} h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.6rem 0; line-height: 1.3; color: ${resolved.headingColor}; }
    ${selector} h2 { font-size: 1.25rem; font-weight: 600; margin: 0 0 0.5rem 0; line-height: 1.35; color: ${resolved.headingColor}; }
    ${selector} h3 { font-size: 1.1rem; font-weight: 600; margin: 0 0 0.45rem 0; line-height: 1.4; color: ${resolved.headingColor}; }
    ${selector} h4 { font-size: 1rem; font-weight: 600; margin: 0 0 0.4rem 0; color: ${resolved.headingColor}; }
    ${selector} ul, ${selector} ol { padding-left: 1.25rem; margin: 0 0 0.6rem 0; color: ${resolved.textColor}; }
    ${selector} li { margin: 0 0 0.25rem 0; }
    ${selector} blockquote { border-left: 3px solid ${resolved.lineColor}; padding-left: 0.75rem; margin: 0 0 0.6rem 0; font-style: italic; color: ${resolved.softColor}; }
    ${selector} pre { background: ${resolved.surfaceColor}; color: ${resolved.textColor}; padding: 0.6rem 0.8rem; border-radius: 0.5rem; margin: 0 0 0.6rem 0; overflow-x: auto; }
    ${selector} code { background: ${resolved.surfaceColor}; color: ${resolved.textColor}; padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875rem; }
    ${selector} img { max-width: 100%; height: auto; border-radius: 0.375rem; display: block; margin: 0.5rem 0; }
    ${selector} table { width: 100%; border-collapse: collapse; margin: 0 0 0.6rem 0; font-size: 0.9rem; color: ${resolved.textColor}; }
    ${selector} th, ${selector} td { border: 1px solid ${resolved.lineColor}; padding: 0.4rem 0.6rem; text-align: left; vertical-align: top; }
    ${selector} th { background: ${resolved.surfaceColor}; color: ${resolved.headingColor}; font-weight: 600; }
    ${selector} hr { border: none; border-top: 1px solid ${resolved.lineColor}; margin: 1rem 0; }
    ${selector} a { color: ${resolved.linkColor}; text-decoration: underline; }
	`;
};

const buildAlignmentRules = (selector) => `
  ${selector} .aligncenter, ${selector} img.aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }
  ${selector} .alignleft, ${selector} img.alignleft { float: left; margin-right: 1rem; margin-bottom: 0.5rem; text-align: left; }
  ${selector} .alignright, ${selector} img.alignright { float: right; margin-left: 1rem; margin-bottom: 0.5rem; text-align: right; }
  ${selector} .alignjustify { text-align: justify; }
  ${selector} p::after, ${selector} h1::after, ${selector} h2::after, ${selector} h3::after, ${selector} h4::after { content: ''; display: table; clear: both; }
`;

export const tinymceBaseStyle = `
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lato:wght@400;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap');
  ${buildContentRules("body", defaultPreviewTheme)}
  ${buildAlignmentRules("body")}
`;

export const buildTinymceContentStyle = (theme = {}) => `
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lato:wght@400;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap');
  ${buildContentRules("body", theme)}
  ${buildAlignmentRules("body")}
`;

export const buildScopedTinymceContentStyle = (selector, theme = {}) =>
  buildContentRules(selector, theme);

export const buildScopedTinymceAlignmentStyle = (selector) =>
  buildAlignmentRules(selector);
