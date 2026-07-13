<script>
  import { inertiaEnhance } from "$lib/inertia-enhance.js";
  import brandLogo from "../images/logokabinet.png?enhanced&w=80;160";
  import OptimizedImage from "./components/OptimizedImage.svelte";
  import PublicInformationIndexPage from "./public/PublicInformationIndexPage.svelte";
  import PublicInformationShowPage from "./public/PublicInformationShowPage.svelte";
  import PublicEventIndexPage from "./public/PublicEventIndexPage.svelte";
  import PublicEventShowPage from "./public/PublicEventShowPage.svelte";
  import Navbar from "./components/landing/Navbar.svelte";
  import Footer from "./components/landing/Footer.svelte";
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

  const navigationItems = $derived([
    { href: homeUrl, label: "Beranda" },
    {
      href: "/departemen",
      label: "Departemen",
      children: [
        { href: "/departemen", label: "Departemen" },
        { href: "/departemen/overview", label: "Detail departemen" },
        { href: "/tentang", label: "Sejarah himpunan" },
      ],
    },
    { href: "/kompetisi", label: "Kompetisi" },
    { href: infoUrl, label: "Kabar Terbaru" },
    { href: acaraUrl, label: "Acara Mendatang" },
    { href: "/tentang", label: "Tentang Kami" },
  ]);
</script>

<svelte:head>
  {#if !seo}
    <title>{pageTitle}</title>
    <meta name="description" content={pageDescription} />
  {/if}
</svelte:head>

<div use:inertiaEnhance class="taling-public">
  <a href="#main-content" class="skip-link">Langsung ke konten</a>

  <Navbar {homeUrl} {loginUrl} {navigationItems} />

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

  <Footer
    {infoUrl}
    {acaraUrl}
    departemenUrl="/departemen"
    tentangUrl="/tentang"
    kompetisiUrl="/kompetisi"
    {organizationName}
  />
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

  .taling-footer-inner,
  .taling-footer-base,
  :global(.taling-section-shell) {
    width: min(1248px, calc(100% - 3rem));
    margin-inline: auto;
  }

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

  @media (max-width: 819px) {
    :global(.taling-section-shell) {
      width: min(100% - 1.5rem, 620px);
    }
  }
</style>
