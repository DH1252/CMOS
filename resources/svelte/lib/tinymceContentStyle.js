export const tinymceBaseStyle = `
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lato:wght@400;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap');
  body { font-family: 'Public Sans', sans-serif; font-size: 16px; line-height: 1.65; }
  p { margin: 0 0 0.6rem 0; }
  h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.6rem 0; line-height: 1.3; }
  h2 { font-size: 1.25rem; font-weight: 600; margin: 0 0 0.5rem 0; line-height: 1.35; }
  h3 { font-size: 1.1rem; font-weight: 600; margin: 0 0 0.45rem 0; line-height: 1.4; }
  h4 { font-size: 1rem; font-weight: 600; margin: 0 0 0.4rem 0; }
  ul, ol { padding-left: 1.25rem; margin: 0 0 0.6rem 0; }
  li { margin: 0 0 0.25rem 0; }
  blockquote { border-left: 3px solid #e2e8f0; padding-left: 0.75rem; margin: 0 0 0.6rem 0; font-style: italic; color: #64748b; }
  pre { background: #f1f5f9; padding: 0.6rem 0.8rem; border-radius: 0.5rem; margin: 0 0 0.6rem 0; overflow-x: auto; }
  code { background: #f1f5f9; padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875rem; }
  img { max-width: 100%; height: auto; border-radius: 0.375rem; display: block; margin: 0.5rem 0; }
  table { width: 100%; border-collapse: collapse; margin: 0 0 0.6rem 0; font-size: 0.9rem; }
  th, td { border: 1px solid #e2e8f0; padding: 0.4rem 0.6rem; text-align: left; vertical-align: top; }
  th { background: #f8fafc; font-weight: 600; }
  hr { border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0; }
  a { color: #2563eb; text-decoration: underline; }
`;

export const tinymceAlignmentStyle = `
  .aligncenter, img.aligncenter { display: block; margin-left: auto; margin-right: auto; text-align: center; }
  .alignleft, img.alignleft { float: left; margin-right: 1rem; margin-bottom: 0.5rem; text-align: left; }
  .alignright, img.alignright { float: right; margin-left: 1rem; margin-bottom: 0.5rem; text-align: right; }
  .alignjustify { text-align: justify; }
  p::after, h1::after, h2::after, h3::after, h4::after { content: ''; display: table; clear: both; }
`;
