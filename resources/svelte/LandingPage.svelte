<script>
  import { inertiaEnhance } from "$lib/inertia-enhance.js";
  import Navbar from "./components/landing/Navbar.svelte";
  import HeroSection from "./components/landing/HeroSection.svelte";
  import KabarTerbaru from "./components/landing/KabarTerbaru.svelte";
  import AcaraMendatang from "./components/landing/AcaraMendatang.svelte";
  import KabinetKami from "./components/landing/KabinetKami.svelte";
  import Footer from "./components/landing/Footer.svelte";

  let {
    organizationName = "HIMATEKKOM ITS",
    seo = null,
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    latestInfo = [],
    upcomingEvents = [],
    informationSection = {
      archiveLabel: "Arsip lengkap",
      emptyText: "Belum ada publikasi yang terbit di papan informasi.",
    },
    eventsSection = {
      archiveLabel: "Semua acara",
      emptyText: "Belum ada acara mendatang yang dipublikasikan.",
    },
    navigation = null,
    cabinetSection = null,
    footer = null,
  } = $props();

  const assetBase = "/images/figma-taling";

  const resolvedNavigation = $derived(
    navigation ?? [
      { href: homeUrl, label: "Beranda" },
      { href: "/departemen", label: "Departemen" },
      { href: "/kompetisi", label: "Kompetisi" },
      { href: "/tentang", label: "Tentang Kami" },
    ],
  );

  const getNavigationHref = (label, fallback) =>
    resolvedNavigation.find((item) => item.label === label)?.href ?? fallback;

  const departemenUrl = $derived(
    getNavigationHref("Departemen", "/departemen"),
  );
  const kompetisiUrl = $derived(getNavigationHref("Kompetisi", "/kompetisi"));
  const tentangUrl = $derived(getNavigationHref("Tentang Kami", "/tentang"));

  const navigationItems = $derived.by(() =>
    resolvedNavigation.map((item) => {
      if (item.label === "Departemen") {
        return {
          ...item,
          children: [
            { href: "/departemen", label: "Orbit departemen" },
            { href: "/departemen/bph", label: "Detail departemen" },
            { href: tentangUrl, label: "Sejarah himpunan" },
          ],
        };
      }

      if (item.label === "Kompetisi") {
        return {
          ...item,
          children: [
            { href: item.href, label: "Kompetisi" },
            { href: acaraUrl, label: "Agenda kegiatan" },
          ],
        };
      }

      return item;
    }),
  );

  const newsCards = $derived.by(() =>
    (Array.isArray(latestInfo) ? latestInfo : []).slice(0, 5),
  );

  const eventItems = $derived.by(() =>
    (Array.isArray(upcomingEvents) ? upcomingEvents : []).map((event) => ({
      title: event.title,
      startsAtLabel: event.startsAtLabel || "Segera",
      excerpt: event.excerpt || "Agenda terbaru HIMATEKKOM ITS.",
      poster: event.poster || null,
      url: event.url ?? acaraUrl,
    })),
  );

  const footerDescription = $derived(
    footer?.description ??
      "Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.",
  );

  const cabinetTitle = $derived(cabinetSection?.title ?? "KABINET KAMI");
  const pageTitle =
    "Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi";
  const pageDescription =
    "Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.";
</script>

<svelte:head>
  <title>{seo?.title || pageTitle}</title>
  <meta name="description" content={seo?.description || pageDescription} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  <meta property="og:type" content={seo?.type || "website"} />
  <meta property="og:title" content={seo?.title || pageTitle} />
  <meta
    property="og:description"
    content={seo?.description || pageDescription}
  />
  {#if seo?.image}
    <meta property="og:image" content={seo.image} />
    <meta name="twitter:image" content={seo.image} />
  {/if}
  <meta
    name="twitter:card"
    content={seo?.image ? "summary_large_image" : "summary"}
  />
  <meta name="twitter:title" content={seo?.title || pageTitle} />
  <meta
    name="twitter:description"
    content={seo?.description || pageDescription}
  />
</svelte:head>

<div
  use:inertiaEnhance
  class="min-h-screen w-full bg-white font-['Plus_Jakarta_Sans',sans-serif] text-[#222]"
>
  <Navbar {homeUrl} {loginUrl} {navigationItems} />

  <main id="main-content" tabindex="-1" class="outline-none">
    <HeroSection {assetBase} />
    <KabarTerbaru
      {assetBase}
      {newsCards}
      {infoUrl}
      emptyText={informationSection.emptyText}
      archiveLabel={informationSection.archiveLabel}
    />
    <div
      class="relative w-full overflow-hidden bg-gradient-to-b from-[#ff7a1a] to-[#ffd344]"
    >
      <picture class="contents">
        <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
        <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
        <img
          class="pointer-events-none absolute inset-0 h-full min-h-full w-full object-cover opacity-10 mix-blend-color-burn"
          src={`${assetBase}/botanical.png`}
          alt=""
          width="1600"
          height="1066"
        />
      </picture>
      <div class="relative z-10 w-full">
        <AcaraMendatang {assetBase} {eventItems} {acaraUrl} {eventsSection} />
        <KabinetKami {assetBase} {cabinetTitle} />
      </div>
    </div>
  </main>

  <Footer
    {infoUrl}
    {acaraUrl}
    {departemenUrl}
    {tentangUrl}
    {kompetisiUrl}
    {organizationName}
    {footerDescription}
  />
</div>
