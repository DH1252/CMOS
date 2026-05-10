<script>
  import brandLogo from "../images/logokabinet.png?enhanced&w=80;160";
  import OptimizedImage from "./components/OptimizedImage.svelte";
  import PublicInformationIndexPage from "./public/PublicInformationIndexPage.svelte";
  import PublicInformationShowPage from "./public/PublicInformationShowPage.svelte";
  import { LogIn, Menu } from "lucide-svelte";

  let {
    page = "info-index",
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    seo = null,
    infoIndex = {},
    infoShow = {},
  } = $props();

  const isInfoIndex = $derived(page === "info-index");
  const isInfoShow = $derived(page === "info-show");

  const pageTitle = $derived.by(() => {
    if (page === "info-show") {
      const seoTitle = infoShow?.article?.seoTitle;

      return `${seoTitle || infoShow?.article?.title || "Papan Informasi"} - ${organizationName}`;
    }

    return `Papan Informasi - ${organizationName}`;
  });

  const pageDescription = $derived.by(() => {
    if (page === "info-show") {
      return infoShow?.article?.excerpt || "Publikasi resmi HIMATEKKOM ITS.";
    }

    return `Portal informasi resmi ${organizationName}. Artikel, pembaruan kegiatan, dan publikasi organisasi.`;
  });

  let menuDetails = $state(null);
</script>

<svelte:head>
  {#if !seo}
    <title>{pageTitle}</title>
    <meta name="description" content={pageDescription} />
  {/if}
</svelte:head>

<div class="landing-terminal min-h-screen">
  <a href="#main-content" class="skip-link">Langsung ke konten</a>

  <header
    class="border-b border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-bg)]"
  >
    <div
      class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8"
    >
      <a
        href={homeUrl}
        class="flex min-w-0 items-center gap-3 text-inherit no-underline"
      >
        <OptimizedImage
          src={brandLogo}
          alt={organizationName}
          class="h-10 w-auto shrink-0"
          loading="eager"
          fetchpriority="high"
          sizes="40px"
        />
        <div class="min-w-0">
          <div
            class="truncate text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
          >
            {organizationName}
          </div>
          <div
            class="truncate text-xs text-[var(--landing-terminal-soft-resolved)]"
          >
            Papan Informasi Publik
          </div>
        </div>
      </a>

      <nav class="hidden items-center gap-6 md:flex">
        <a
          href={homeUrl}
          class="text-sm text-[var(--landing-terminal-soft-resolved)] transition-colors duration-200 hover:text-[var(--landing-terminal-text-resolved)]"
          >Beranda</a
        >
        <a
          href={infoUrl}
          class="text-sm text-[var(--landing-terminal-interactive-resolved)] transition-colors duration-200 hover:text-[var(--landing-terminal-text-resolved)]"
          >Arsip</a
        >
      </nav>

      <div class="flex items-center gap-2">
        <a
          href={loginUrl}
          class="landing-button-secondary hidden items-center gap-2 sm:inline-flex"
        >
          <LogIn size={16} />
          Masuk
        </a>

        <details class="relative md:hidden" bind:this={menuDetails}>
          <summary
            class="inline-flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] text-[var(--landing-terminal-text-resolved)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--landing-terminal-interactive-resolved)] [&::-webkit-details-marker]:hidden"
          >
            <Menu size={18} />
          </summary>
          <div
            class="absolute top-[calc(100%+0.75rem)] right-0 z-20 grid min-w-56 gap-1 border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] p-3"
          >
            <a
              href={homeUrl}
              class="px-3 py-2 text-sm text-[var(--landing-terminal-soft-resolved)] transition-colors duration-200 hover:bg-[var(--landing-terminal-panel-soft-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"
              onclick={() => {
                menuDetails.open = false;
              }}
            >
              Beranda
            </a>
            <a
              href={infoUrl}
              class="px-3 py-2 text-sm text-[var(--landing-terminal-interactive-resolved)] transition-colors duration-200 hover:bg-[var(--landing-terminal-panel-soft-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"
              onclick={() => {
                menuDetails.open = false;
              }}
            >
              Arsip
            </a>
            <a
              href={loginUrl}
              class="landing-button-secondary mt-1 justify-center"
              onclick={() => {
                menuDetails.open = false;
              }}
            >
              Masuk ke CMOS
            </a>
          </div>
        </details>
      </div>
    </div>
  </header>

  <main
    id="main-content"
    class="mx-auto max-w-[1180px] px-5 py-8 lg:px-8 lg:py-10"
  >
    {#if isInfoIndex}
      <PublicInformationIndexPage {...infoIndex} {homeUrl} {infoUrl} {seo} />
    {:else if isInfoShow}
      <PublicInformationShowPage {...infoShow} {homeUrl} {infoUrl} {seo} />
    {/if}
  </main>
</div>

<style>
  .landing-terminal {
    --landing-terminal-bg-resolved: var(
      --landing-terminal-bg,
      oklch(0.18 0.012 304)
    );
    --landing-terminal-hero-bg-resolved: var(
      --landing-terminal-hero-bg,
      var(--landing-terminal-bg, oklch(0.18 0.012 304))
    );
    --landing-terminal-panel-resolved: var(
      --landing-terminal-panel,
      oklch(0.22 0.014 304)
    );
    --landing-terminal-panel-soft-resolved: var(
      --landing-terminal-panel-soft,
      oklch(0.27 0.015 304)
    );
    --landing-terminal-line-resolved: var(
      --landing-terminal-line,
      oklch(0.52 0.032 82)
    );
    --landing-terminal-text-resolved: var(
      --landing-terminal-text,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-heading-resolved: var(
      --landing-terminal-heading,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-soft-resolved: var(
      --landing-terminal-soft,
      oklch(0.74 0.025 92)
    );
    --landing-terminal-muted-resolved: var(
      --landing-terminal-muted,
      oklch(0.62 0.02 88)
    );
    --landing-terminal-accent-resolved: var(
      --landing-terminal-accent,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-interactive-resolved: var(
      --landing-terminal-interactive,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-command-resolved: var(
      --landing-terminal-command,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-frame-accent-resolved: var(
      --landing-terminal-frame-accent,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-icon-resolved: var(
      --landing-terminal-icon,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-button-text-resolved: var(
      --landing-terminal-button-text,
      oklch(0.22 0.02 74)
    );
    --landing-terminal-button-hover-resolved: var(
      --landing-terminal-button-hover,
      oklch(0.82 0.12 84)
    );
    --landing-terminal-button-secondary-text-resolved: var(
      --landing-terminal-button-secondary-text,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-button-secondary-hover-resolved: var(
      --landing-terminal-button-secondary-hover,
      oklch(0.27 0.015 304)
    );
    background: var(--landing-terminal-bg-resolved);
    color: var(--landing-terminal-text-resolved);
    font-family: "Public Sans", sans-serif;
  }

  .landing-terminal :global(h1),
  .landing-terminal :global(h2),
  .landing-terminal :global(h3) {
    color: var(--landing-terminal-heading-resolved);
    font-family: "JetBrains Mono", monospace;
    letter-spacing: -0.02em;
    line-height: 1.2;
  }

  .landing-terminal header,
  .landing-terminal header :global(*),
  :global(.landing-button-primary),
  :global(.landing-button-secondary) {
    font-family: "JetBrains Mono", monospace;
  }

  .landing-terminal :global(p) {
    line-height: 1.65;
  }

  :global(.landing-panel) {
    border: 1px solid var(--landing-terminal-line-resolved);
    background: var(--landing-terminal-panel-resolved);
  }

  :global(.landing-button-primary),
  :global(.landing-button-secondary) {
    min-height: 2.75rem;
    padding: 0.65rem 1rem;
    border: 1px solid var(--landing-terminal-line-resolved);
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    transition:
      background-color 180ms var(--ease-out-quart),
      color 180ms var(--ease-out-quart),
      border-color 180ms var(--ease-out-quart);
  }

  :global(.landing-button-primary) {
    background: var(--landing-terminal-accent-resolved);
    border-color: color-mix(
      in oklch,
      var(--landing-terminal-accent-resolved) 72%,
      var(--landing-terminal-line-resolved) 28%
    );
    color: var(--landing-terminal-button-text-resolved);
  }

  :global(.landing-button-primary:hover) {
    background: var(--landing-terminal-button-hover-resolved);
  }

  :global(.landing-button-secondary) {
    background: var(--landing-terminal-panel-resolved);
    color: var(--landing-terminal-button-secondary-text-resolved);
  }

  :global(.landing-button-secondary:hover),
  :global(.landing-inline-link:hover),
  :global(.landing-article-row:hover) {
    background: var(--landing-terminal-panel-soft-resolved);
  }

  :global(.landing-button-secondary:hover) {
    background: var(--landing-terminal-button-secondary-hover-resolved);
  }

  :global(.landing-inline-link),
  :global(.landing-article-row) {
    color: inherit;
    text-decoration: none;
    transition:
      background-color 180ms var(--ease-out-quart),
      color 180ms var(--ease-out-quart);
  }

  :global(.landing-inline-link) {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.35rem;
    margin: -0.2rem -0.35rem;
  }

  :global(.landing-article-row) {
    display: block;
    padding: 1rem 0.85rem;
    margin-inline: -0.85rem;
  }

  :global(.landing-frame) {
    border: 1px solid var(--landing-terminal-line-resolved);
    background: var(--landing-terminal-panel-resolved);
    transition:
      background-color 180ms var(--ease-out-quart),
      border-color 180ms var(--ease-out-quart);
  }

  :global(.landing-frame__media) {
    overflow: hidden;
    border-bottom: 1px solid var(--landing-terminal-line-resolved);
  }

  :global(.landing-frame__media img) {
    display: block;
    filter: grayscale(0.3) contrast(1.08);
  }

  :global(.landing-feature-link:hover),
  :global(.landing-frame:hover) {
    background: var(--landing-terminal-panel-soft-resolved);
  }

  :global(.landing-frame__caption) {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    padding: 0.6rem 0.8rem;
    font-family: "JetBrains Mono", monospace;
    font-size: 0.72rem;
  }

  @media (prefers-reduced-motion: reduce) {
    .landing-terminal :global(*) {
      transition-duration: 0ms !important;
      animation-duration: 0ms !important;
    }
  }
</style>
