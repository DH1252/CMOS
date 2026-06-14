<script>
  import { inertiaEnhance } from "$lib/inertia-enhance.js";
  import brandLogo from "../images/logokabinet.png?enhanced&w=80;160";
  import OptimizedImage from "./components/OptimizedImage.svelte";
  import PublicInformationIndexPage from "./public/PublicInformationIndexPage.svelte";
  import PublicInformationShowPage from "./public/PublicInformationShowPage.svelte";
  import PublicEventIndexPage from "./public/PublicEventIndexPage.svelte";
  import PublicEventShowPage from "./public/PublicEventShowPage.svelte";
  import { Button } from "$lib/components/ui/button/index.js";

  let {
    page = "info-index",
    appName = "CMOS",
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    seo = null,
    infoIndex = {},
    infoShow = {},
    acaraIndex = {},
    acaraShow = {},
  } = $props();

  const isInfoIndex = $derived(page === "info-index");
  const isInfoShow = $derived(page === "info-show");
  const isAcaraIndex = $derived(page === "acara-index");
  const isAcaraShow = $derived(page === "acara-show");

  const pageTitle = $derived.by(() => {
    if (page === "info-show") {
      const seoTitle = infoShow?.article?.seoTitle;

      return `${seoTitle || infoShow?.article?.title || "Papan Informasi"} - ${organizationName}`;
    }

    if (page === "acara-show") {
      const seoTitle = acaraShow?.event?.seoTitle;

      return `${seoTitle || acaraShow?.event?.title || "Acara"} - ${organizationName}`;
    }

    if (page === "acara-index") {
      return `Acara Mendatang - ${organizationName}`;
    }

    return `Papan Informasi - ${organizationName}`;
  });

  const pageDescription = $derived.by(() => {
    if (page === "info-show") {
      return infoShow?.article?.excerpt || "Publikasi resmi HIMATEKKOM ITS.";
    }

    if (page === "acara-show") {
      return acaraShow?.event?.excerpt || "Agenda resmi HIMATEKKOM ITS.";
    }

    if (page === "acara-index") {
      return `Daftar acara dan kegiatan resmi ${organizationName}.`;
    }

    return `Portal informasi resmi ${organizationName}. Artikel, pembaruan kegiatan, dan publikasi organisasi.`;
  });

  let menuDetails = $state(null);

  const closeMenu = () => {
    if (menuDetails) {
      menuDetails.open = false;
    }
  };
</script>

<svelte:head>
  {#if !seo}
    <title>{pageTitle}</title>
    <meta name="description" content={pageDescription} />
  {/if}
</svelte:head>

<div use:inertiaEnhance class="taling-public">
  <a href="#main-content" class="skip-link">Langsung ke konten</a>

  <header class="taling-header">
    <div class="taling-header-inner">
      <a href={homeUrl} class="taling-brand" aria-label={organizationName}>
        <OptimizedImage
          src={brandLogo}
          alt=""
          class="taling-brand-mark"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="76px"
        />
        <span>{organizationName}</span>
      </a>

      <nav class="taling-nav" aria-label="Navigasi utama">
        <a href={homeUrl} class="taling-nav-link">Beranda</a>
        <a
          href={infoUrl}
          class={`taling-nav-link ${isInfoIndex || isInfoShow ? "taling-nav-link-active" : ""}`}
        >
          Kabar Terbaru
        </a>
        <a
          href={acaraUrl}
          class={`taling-nav-link ${isAcaraIndex || isAcaraShow ? "taling-nav-link-active" : ""}`}
        >
          Acara Mendatang
        </a>
      </nav>

      <div class="taling-header-actions">
        <Button href={loginUrl} class="taling-login">Masuk</Button>

        <details class="taling-menu" bind:this={menuDetails}>
          <summary class="taling-menu-trigger" aria-label="Buka menu">
            <span></span>
            <span></span>
            <span></span>
          </summary>
          <div class="taling-menu-panel">
            <a href={homeUrl} class="taling-menu-link" onclick={closeMenu}
              >Beranda</a
            >
            <a href={infoUrl} class="taling-menu-link" onclick={closeMenu}
              >Kabar Terbaru</a
            >
            <a href={acaraUrl} class="taling-menu-link" onclick={closeMenu}
              >Acara Mendatang</a
            >
            <Button
              href={loginUrl}
              class="taling-menu-login"
              onclick={closeMenu}
            >
              Masuk
            </Button>
          </div>
        </details>
      </div>
    </div>
  </header>

  <main id="main-content" tabindex="-1" class="outline-none">
    {#if isInfoIndex}
      <PublicInformationIndexPage {...infoIndex} {homeUrl} {infoUrl} {seo} />
    {:else if isInfoShow}
      <PublicInformationShowPage {...infoShow} {homeUrl} {infoUrl} {seo} />
    {:else if isAcaraIndex}
      <PublicEventIndexPage {...acaraIndex} {homeUrl} {acaraUrl} {seo} />
    {:else if isAcaraShow}
      <PublicEventShowPage {...acaraShow} {homeUrl} {acaraUrl} {seo} />
    {/if}
  </main>

  <footer class="taling-footer">
    <div class="taling-footer-inner">
      <div>
        <strong>{organizationName}</strong>
        <p>
          Kanal publik Kabinet Sentra Sinergi untuk membaca kabar, dokumentasi,
          dan agenda resmi HIMATEKKOM ITS.
        </p>
      </div>
      <div class="taling-footer-links">
        <a href={homeUrl}>Beranda</a>
        <a href={infoUrl}>Kabar Terbaru</a>
        <a href={acaraUrl}>Acara Mendatang</a>
        <a href={loginUrl}>Masuk</a>
      </div>
    </div>
    <div class="taling-footer-base">
      <span>&copy; {organizationName} 2026</span>
      <span>{appName}</span>
    </div>
  </footer>
</div>

<style>
  .taling-public {
    --taling-yellow: #ffd344;
    --taling-purple: #2a0078;
    --taling-purple-deep: #5d0077;
    --taling-orange: #ff7a1a;
    --taling-ink: #222222;
    --taling-white: #fffdf8;
    --taling-cream: #fff4d3;
    --taling-section: #f59b1a;
    min-height: 100vh;
    overflow: clip;
    background: var(--taling-white);
    color: var(--taling-ink);
    font-family: var(--taling-font-sans);
  }

  .taling-header {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #fffdf8;
    color: var(--taling-ink);
  }

  .taling-header-inner,
  .taling-footer-inner,
  .taling-footer-base,
  :global(.taling-section-shell) {
    width: min(1248px, calc(100% - 3rem));
    margin-inline: auto;
  }

  .taling-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 84px;
    gap: 2rem;
  }

  .taling-brand {
    display: inline-flex;
    align-items: center;
    color: inherit;
    text-decoration: none;
  }

  .taling-brand span {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
  }

  .taling-brand :global(.taling-brand-mark),
  .taling-brand :global(img) {
    width: 76px;
    height: auto;
  }

  .taling-nav {
    display: none;
    align-items: center;
    gap: clamp(1.6rem, 3vw, 3rem);
    font-size: 0.95rem;
    font-weight: 700;
  }

  .taling-nav-link {
    color: inherit;
    text-decoration: none;
  }

  .taling-nav-link:hover,
  .taling-nav-link-active {
    color: var(--taling-purple);
  }

  .taling-header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  :global(.taling-login),
  :global(.taling-menu-login),
  :global(.taling-section-link) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: auto;
    height: auto;
    min-height: 36px;
    padding: 0.55rem 1.9rem;
    border-radius: 999px;
    background: linear-gradient(
      90deg,
      var(--taling-orange),
      var(--taling-yellow)
    );
    color: var(--taling-purple);
    font-weight: 800;
    text-decoration: none;
  }

  .taling-menu {
    position: relative;
  }

  .taling-menu-trigger {
    display: grid;
    place-items: center;
    gap: 5px;
    width: 44px;
    height: 44px;
    list-style: none;
    cursor: pointer;
  }

  .taling-menu-trigger::-webkit-details-marker {
    display: none;
  }

  .taling-menu-trigger span {
    width: 26px;
    height: 3px;
    border-radius: 2px;
    background: var(--taling-ink);
  }

  .taling-menu-panel {
    position: absolute;
    top: calc(100% + 0.7rem);
    right: 0;
    display: grid;
    gap: 0.35rem;
    min-width: 15rem;
    padding: 0.8rem;
    border: 1px solid color-mix(in srgb, var(--taling-purple) 18%, transparent);
    background: #fffdf8;
    box-shadow: 0 10px 24px rgba(34, 34, 34, 0.14);
  }

  .taling-menu-link {
    padding: 0.7rem 0.8rem;
    color: var(--taling-ink);
    font-weight: 700;
    text-decoration: none;
  }

  .taling-menu-link:hover {
    background: color-mix(in srgb, var(--taling-yellow) 22%, transparent);
  }

  .taling-footer {
    background: #fffdf8;
    color: var(--taling-ink);
    padding: 4rem 0 2rem;
  }

  .taling-footer-inner {
    display: grid;
    grid-template-columns: minmax(260px, 1fr) minmax(280px, 1fr);
    gap: 2rem;
    align-items: start;
  }

  .taling-footer strong {
    font-family: var(--taling-font-serif);
    font-size: 2rem;
  }

  .taling-footer p {
    max-width: 56ch;
    margin: 1rem 0 0;
    color: color-mix(in srgb, var(--taling-ink) 74%, transparent);
    line-height: 1.6;
  }

  .taling-footer-links {
    display: flex;
    flex-wrap: wrap;
    justify-content: end;
    gap: 0.85rem 1.35rem;
  }

  .taling-footer-links a {
    color: var(--taling-ink);
    font-weight: 800;
    text-decoration: none;
  }

  .taling-footer-base {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 4rem;
    padding-top: 1.5rem;
    border-top: 1px solid color-mix(in srgb, var(--taling-ink) 14%, transparent);
    color: color-mix(in srgb, var(--taling-ink) 62%, transparent);
    font-weight: 800;
  }

  :global(.taling-page-kicker) {
    margin: 0;
    color: var(--taling-yellow);
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  :global(.taling-page-title) {
    margin: 0;
    font-family: var(--taling-font-serif);
    font-size: clamp(3.3rem, 7vw, 6.8rem);
    font-weight: 700;
    line-height: 1;
  }

  :global(.taling-page-copy) {
    margin: 0;
    max-width: 66ch;
    font-weight: 800;
    line-height: 1.48;
  }

  :global(.taling-meta-line) {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem 1rem;
    font-weight: 800;
  }

  :global(.taling-chip) {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    min-height: 1.9rem;
    padding: 0.35rem 0.75rem;
    background: var(--taling-purple);
    color: var(--taling-white);
    font-size: 0.78rem;
    font-weight: 900;
  }

  :global(.taling-empty-bright) {
    padding: 3rem;
    border: 4px solid var(--taling-purple);
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: var(--taling-purple);
    font-weight: 900;
    text-align: center;
  }

  @media (min-width: 820px) {
    .taling-nav,
    :global(.taling-login) {
      display: flex;
    }

    .taling-menu {
      display: none;
    }
  }

  @media (max-width: 819px) {
    .taling-header-inner,
    .taling-footer-inner,
    .taling-footer-base,
    :global(.taling-section-shell) {
      width: min(100% - 1.5rem, 620px);
    }

    :global(.taling-login) {
      display: none;
    }

    .taling-header-inner {
      min-height: 72px;
    }

    .taling-brand :global(.taling-brand-mark),
    .taling-brand :global(img) {
      width: 58px;
    }

    .taling-footer-inner {
      grid-template-columns: 1fr;
    }

    .taling-footer-links {
      justify-content: start;
    }

    .taling-footer-base {
      display: grid;
    }
  }
</style>
