<script>
  import heroPhoto from "../images/himatekkom.jpg?enhanced&w=960;1440;1920";
  import brandLogo from "../images/logokabinet.png?enhanced&w=96;192;384";
  import { Badge } from "$lib/components/ui/badge/index.js";
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import OptimizedImage from "./components/OptimizedImage.svelte";

  let {
    appName = "CMOS",
    organizationName = "HIMATEKKOM ITS",
    seo = null,
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    latestInfo = [],
    upcomingEvents = [],
    navigation = null,
    hero = null,
    informationSection = null,
    eventsSection = null,
    profileSection = null,
    footer = null,
  } = $props();

  const jsonLdScriptOpen = '<script type="application/ld+json">';
  const jsonLdScriptClose = "</" + "script>";

  const resolvedNavigation = $derived(
    navigation ?? [
      { href: homeUrl, label: "Beranda" },
      { href: "/departemen", label: "Departemen" },
      { href: "/kompetisi", label: "Kompetisi" },
      { href: "/tentang", label: "Tentang Kami" },
    ],
  );

  const resolvedHero = $derived(
    hero ?? {
      titleVariants: ["Kabinet Sentra Sinergi"],
      description: "#OKE | Optimalisasi | Kolaborasi | Ekspansi",
    },
  );

  const resolvedInformationSection = $derived(
    informationSection ?? {
      title: "Kabar Terbaru",
      archiveLabel: "Arsip lengkap",
      emptyText: "Belum ada publikasi yang terbit di papan informasi.",
    },
  );

  const resolvedEventsSection = $derived(
    eventsSection ?? {
      title: "Acara Mendatang",
      archiveLabel: "Semua acara",
      emptyText: "Belum ada acara mendatang yang dipublikasikan.",
    },
  );

  const resolvedProfileSection = $derived(
    profileSection ?? {
      title: "Kabinet Kami",
      description:
        "Kabinet Sentra Sinergi menjalankan publikasi dan operasional HIMATEKKOM ITS dengan alur kerja yang rapi, terdokumentasi, dan mudah dibaca oleh publik maupun pengurus.",
      vision:
        "Menjaga ruang kerja organisasi yang progresif, inklusif, dan berdampak melalui kolaborasi yang kuat.",
    },
  );

  const resolvedFooter = $derived(
    footer ?? {
      description:
        "Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.",
      sections: [],
    },
  );

  const fallbackNews = $derived([
    {
      title: "Publikasi HIMATEKKOM ITS",
      excerpt: resolvedInformationSection.emptyText,
      publishedAtLabel: "Terbaru",
      category: "Papan Informasi",
      url: infoUrl,
      coverImage: null,
    },
  ]);

  let menuDetails = $state(null);
  let activeEventIndex = $state(0);

  const newsItems = $derived(latestInfo.length ? latestInfo : fallbackNews);
  const eventCount = $derived(upcomingEvents.length);
  const activeEvent = $derived(upcomingEvents[activeEventIndex] ?? null);
  const footerSections = $derived(resolvedFooter.sections ?? []);
  const footerLinks = $derived.by(() => {
    const links = [
      ...resolvedNavigation,
      ...footerSections.flatMap((section) => section.links ?? []),
    ];
    const seen = [];

    return links.filter((link) => {
      if (!link?.href || seen.includes(link.href)) {
        return false;
      }

      seen.push(link.href);

      return true;
    });
  });
  const cabinetLead = $derived(
    resolvedHero.titleVariants?.[0] ?? "Kabinet Sentra Sinergi",
  );

  const pageTitle =
    "Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi";
  const pageDescription =
    "Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.";

  const closeMenu = () => {
    if (menuDetails) {
      menuDetails.open = false;
    }
  };

  const showPreviousEvent = () => {
    if (eventCount < 2) {
      return;
    }

    activeEventIndex = (activeEventIndex - 1 + eventCount) % eventCount;
  };

  const showNextEvent = () => {
    if (eventCount < 2) {
      return;
    }

    activeEventIndex = (activeEventIndex + 1) % eventCount;
  };

  const getEventOffset = (index) => {
    if (eventCount === 0) {
      return 0;
    }

    let offset = index - activeEventIndex;

    if (offset > eventCount / 2) {
      offset -= eventCount;
    }

    if (offset < -eventCount / 2) {
      offset += eventCount;
    }

    return offset;
  };

  const eventPosterClass = (index) => {
    const offset = getEventOffset(index);

    if (offset === 0) {
      return "taling-event-poster taling-event-poster-active";
    }

    if (offset === -1) {
      return "taling-event-poster taling-event-poster-prev";
    }

    if (offset === 1) {
      return "taling-event-poster taling-event-poster-next";
    }

    if (offset === -2) {
      return "taling-event-poster taling-event-poster-far-prev";
    }

    if (offset === 2) {
      return "taling-event-poster taling-event-poster-far-next";
    }

    return "taling-event-poster taling-event-poster-hidden";
  };
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
  {#if seo?.jsonLd}
    {@html jsonLdScriptOpen + seo.jsonLd + jsonLdScriptClose}
  {/if}
</svelte:head>

<div class="taling-landing">
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
        {#each resolvedNavigation as item (item.href)}
          <a href={item.href} class="taling-nav-link">{item.label}</a>
        {/each}
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
            {#each resolvedNavigation as item (item.href)}
              <a href={item.href} class="taling-menu-link" onclick={closeMenu}
                >{item.label}</a
              >
            {/each}
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

  <main id="main-content">
    <section class="taling-hero" aria-label="Sambutan">
      <div class="taling-hero-media" aria-hidden="true">
        <OptimizedImage
          src={heroPhoto}
          alt=""
          class="taling-hero-img"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="100vw"
        />
        <div class="taling-hero-scrim"></div>
      </div>
      <span class="taling-star taling-star-left" aria-hidden="true"></span>
      <span class="taling-star taling-star-right" aria-hidden="true"></span>

      <div class="taling-hero-center">
        <OptimizedImage
          src={brandLogo}
          alt={cabinetLead}
          class="taling-hero-logo"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="360px"
        />
        <h1>dari kita untuk kita</h1>
        <div class="taling-hero-rule" aria-hidden="true"></div>
      </div>

      <div class="taling-hero-signature">
        <OptimizedImage
          src={brandLogo}
          alt=""
          class="taling-signature-logo"
          loading="lazy"
          decoding="async"
          sizes="180px"
        />
        <p>{resolvedHero.description}</p>
      </div>
    </section>

    <section id="kabar" class="taling-news" aria-labelledby="kabar-heading">
      <div class="taling-section-shell">
        <div class="taling-news-heading">
          <h2 id="kabar-heading">{resolvedInformationSection.title}</h2>
          <Button href={infoUrl} variant="ghost" class="taling-news-link">
            {resolvedInformationSection.archiveLabel}
          </Button>
        </div>

        <div class="taling-news-strip">
          {#each newsItems as article, index (article.url ?? `${article.title}-${index}`)}
            <Card.Root class="taling-news-card" size="sm">
              <a href={article.url ?? infoUrl} class="taling-news-card-link">
                {#if article.coverImage}
                  <OptimizedImage
                    src={article.coverImage}
                    alt={article.title}
                    class="taling-news-img"
                    loading={index === 0 ? "eager" : "lazy"}
                    decoding="async"
                    sizes="(min-width: 1100px) 313px, 78vw"
                  />
                {:else}
                  <div class="taling-news-placeholder" aria-hidden="true">
                    {article.title?.slice(0, 2) || "HI"}
                  </div>
                {/if}
                <div class="taling-news-copy">
                  <Badge class="taling-news-badge" variant="outline">
                    {article.publishedAtLabel || "Publikasi"}
                  </Badge>
                  <strong>{article.title}</strong>
                </div>
              </a>
            </Card.Root>
          {/each}
        </div>
      </div>
    </section>

    <section id="acara" class="taling-events" aria-labelledby="acara-heading">
      <span class="taling-flower" aria-hidden="true"></span>
      <span class="taling-puzzle" aria-hidden="true"></span>
      <div class="taling-section-shell taling-events-shell">
        <h2 id="acara-heading">{resolvedEventsSection.title}</h2>

        {#if activeEvent}
          <div class="taling-event-feature">
            <div class="taling-event-carousel">
              <div class="taling-event-posters" aria-live="polite">
                {#each upcomingEvents as event, index (event.url ?? `${event.title}-${index}`)}
                  <a
                    href={event.url ?? acaraUrl}
                    class={eventPosterClass(index)}
                    aria-label={event.title}
                    aria-current={index === activeEventIndex
                      ? "true"
                      : undefined}
                    tabindex={Math.abs(getEventOffset(index)) > 1
                      ? -1
                      : undefined}
                  >
                    {#if event.poster}
                      <OptimizedImage
                        src={event.poster}
                        alt={event.title}
                        class="taling-event-poster-img"
                        loading={index === activeEventIndex ? "eager" : "lazy"}
                        decoding="async"
                        sizes="(min-width: 900px) 330px, 64vw"
                      />
                    {:else}
                      <div class="taling-event-poster-fallback">
                        <span>{event.startsAtLabel || "Segera"}</span>
                        <strong>{event.title}</strong>
                      </div>
                    {/if}
                  </a>
                {/each}
              </div>

              {#if eventCount > 1}
                <div class="taling-event-controls" aria-label="Navigasi acara">
                  <Button
                    type="button"
                    variant="ghost"
                    class="taling-carousel-nav"
                    onclick={showPreviousEvent}
                    aria-label="Acara sebelumnya"
                  >
                    ‹
                  </Button>
                  <div class="taling-event-dots" aria-label="Pilih acara">
                    {#each upcomingEvents as event, index (event.url ?? `${event.title}-dot-${index}`)}
                      <Button
                        type="button"
                        variant="ghost"
                        class={`taling-carousel-dot ${index === activeEventIndex ? "taling-carousel-dot-active" : ""}`}
                        onclick={() => (activeEventIndex = index)}
                        aria-label={`Tampilkan ${event.title}`}
                        aria-current={index === activeEventIndex
                          ? "true"
                          : undefined}
                      >
                        <span></span>
                      </Button>
                    {/each}
                  </div>
                  <Button
                    type="button"
                    variant="ghost"
                    class="taling-carousel-nav"
                    onclick={showNextEvent}
                    aria-label="Acara berikutnya"
                  >
                    ›
                  </Button>
                </div>
              {/if}
            </div>

            <Card.Root class="taling-event-copy" size="sm">
              <Badge class="taling-event-date" variant="default">
                {activeEvent.startsAtLabel || "Segera"}
              </Badge>
              <h3>{activeEvent.title}</h3>
              <div class="taling-event-rule" aria-hidden="true"></div>
              <p class="taling-event-description">
                {activeEvent.excerpt ||
                  resolvedEventsSection.description ||
                  "Agenda dan kegiatan terbaru HIMATEKKOM ITS yang akan datang."}
              </p>
              {#if activeEvent.location}
                <p class="taling-event-location">{activeEvent.location}</p>
              {/if}
              <Button
                href={activeEvent.url ?? acaraUrl}
                class="taling-section-link"
              >
                Lihat detail acara
              </Button>
            </Card.Root>
          </div>
        {:else}
          <Card.Root class="taling-empty-bright" size="sm">
            {resolvedEventsSection.emptyText}
          </Card.Root>
        {/if}
      </div>
    </section>

    <section class="taling-cabinet" aria-labelledby="cabinet-heading">
      <div class="taling-section-shell taling-cabinet-grid">
        <div class="taling-cabinet-copy">
          <p class="taling-jargon">Contoh Jargon</p>
          <h2 id="cabinet-heading">Kabinet Kami</h2>
          <p>{resolvedProfileSection.description}</p>
          <p>{resolvedProfileSection.vision}</p>
        </div>
        <div class="taling-cabinet-photo">
          <OptimizedImage
            src={heroPhoto}
            alt="Dokumentasi Kabinet Sentra Sinergi"
            class="taling-cabinet-img"
            loading="lazy"
            decoding="async"
            sizes="(min-width: 900px) 620px, 100vw"
          />
        </div>
      </div>
    </section>
  </main>

  <footer class="taling-footer">
    <div class="taling-footer-inner">
      <div>
        <strong>{organizationName}</strong>
        <p>{resolvedFooter.description}</p>
      </div>
      <div class="taling-footer-links">
        {#each footerLinks.slice(0, 8) as link (link.href)}
          <a href={link.href}>{link.label}</a>
        {/each}
      </div>
    </div>
    <div class="taling-footer-base">
      <span>&copy; {organizationName} 2026</span>
      <span>{appName}</span>
    </div>
  </footer>
</div>

<style>
  .taling-landing {
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

  .taling-section-shell,
  .taling-header-inner,
  .taling-footer-inner,
  .taling-footer-base {
    width: min(1248px, calc(100% - 3rem));
    margin-inline: auto;
  }

  .taling-header {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #fffdf8;
    color: var(--taling-ink);
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

  .taling-nav-link:hover {
    color: var(--taling-purple);
  }

  .taling-header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .taling-login,
  .taling-menu-login,
  .taling-section-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

  .taling-login:hover,
  .taling-menu-login:hover,
  .taling-section-link:hover {
    filter: saturate(1.12) brightness(1.02);
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

  .taling-hero {
    position: relative;
    display: grid;
    min-height: 896px;
    overflow: hidden;
    color: var(--taling-white);
  }

  .taling-hero-media,
  .taling-hero-scrim {
    position: absolute;
    inset: 0;
  }

  .taling-hero :global(.taling-hero-img),
  .taling-hero-media :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .taling-hero-scrim {
    background:
      linear-gradient(180deg, rgba(42, 0, 120, 0.12), rgba(18, 8, 34, 0.76)),
      color-mix(in srgb, var(--taling-purple) 44%, transparent);
  }

  .taling-hero-center {
    position: relative;
    z-index: 2;
    display: grid;
    place-items: center;
    align-self: center;
    width: min(900px, calc(100% - 2rem));
    margin: 0 auto;
    padding-top: 4rem;
    text-align: center;
  }

  .taling-hero-center :global(.taling-hero-logo),
  .taling-hero-center :global(img) {
    width: min(398px, 70vw);
    height: auto;
    margin-bottom: 2.1rem;
    filter: drop-shadow(0 10px 16px rgba(18, 8, 34, 0.45));
  }

  .taling-hero h1 {
    margin: 0;
    color: var(--taling-white);
    font-family: var(--taling-font-serif);
    font-size: clamp(3.3rem, 7vw, 6.8rem);
    font-weight: 700;
    line-height: 1;
    text-shadow: 0 0 22px rgba(255, 253, 248, 0.72);
  }

  .taling-hero-rule {
    width: min(534px, 68vw);
    height: 22px;
    margin-top: 1.9rem;
    background: var(--taling-yellow);
    box-shadow: 0 0 24px rgba(255, 211, 68, 0.52);
  }

  .taling-hero-signature {
    position: absolute;
    left: clamp(2rem, 7vw, 7rem);
    bottom: 3.8rem;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 1.15rem;
    max-width: 540px;
  }

  .taling-hero-signature :global(.taling-signature-logo),
  .taling-hero-signature :global(img) {
    width: 180px;
    height: auto;
  }

  .taling-hero-signature p {
    margin: 0;
    color: var(--taling-white);
    font-weight: 800;
    line-height: 1.35;
  }

  .taling-star {
    position: absolute;
    z-index: 1;
    width: 270px;
    aspect-ratio: 1;
    background: var(--taling-yellow);
    clip-path: polygon(
      50% 0,
      59% 35%,
      98% 35%,
      66% 56%,
      78% 96%,
      50% 70%,
      22% 96%,
      34% 56%,
      2% 35%,
      41% 35%
    );
    opacity: 0.72;
  }

  .taling-star-left {
    top: -112px;
    left: 16%;
    width: 355px;
  }

  .taling-star-right {
    right: -118px;
    bottom: 42px;
  }

  .taling-news {
    position: relative;
    padding: 6rem 0 7rem;
    overflow: hidden;
    background:
      radial-gradient(
        circle at 20% 20%,
        rgba(255, 211, 68, 0.12),
        transparent 32rem
      ),
      linear-gradient(180deg, #18072e 0%, #12051f 100%);
    color: var(--taling-white);
  }

  .taling-news::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: 0.18;
    background-image:
      linear-gradient(
        45deg,
        transparent 46%,
        rgba(255, 211, 68, 0.2) 47%,
        transparent 48%
      ),
      linear-gradient(
        -45deg,
        transparent 46%,
        rgba(255, 255, 255, 0.16) 47%,
        transparent 48%
      );
    background-size: 46px 46px;
  }

  .taling-news-heading,
  .taling-news-strip {
    position: relative;
    z-index: 1;
  }

  .taling-news-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 4rem;
    border-bottom: 2px solid rgba(255, 253, 248, 0.78);
    padding-bottom: 2rem;
  }

  .taling-news h2,
  .taling-events h2,
  .taling-cabinet h2,
  .taling-jargon {
    margin: 0;
    font-family: var(--taling-font-serif);
    font-weight: 700;
    line-height: 1;
  }

  .taling-news h2 {
    color: var(--taling-white);
    font-size: clamp(3rem, 6.8vw, 6rem);
  }

  .taling-news-link {
    min-height: 36px;
    padding-inline: 0;
    color: color-mix(in srgb, var(--taling-white) 76%, transparent);
    background: transparent;
    font-weight: 800;
  }

  .taling-news-strip {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(240px, 313px);
    gap: 1.4rem;
    margin-inline: calc((100vw - min(1248px, calc(100vw - 3rem))) / -2);
    padding-inline: calc((100vw - min(1248px, calc(100vw - 3rem))) / 2);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
  }

  .taling-news-strip::-webkit-scrollbar {
    display: none;
  }

  .taling-news-card {
    position: relative;
    display: block;
    height: 397px;
    overflow: hidden;
    padding: 0;
    border-radius: 0;
    box-shadow: none;
    color: var(--taling-white);
    text-decoration: none;
    scroll-snap-align: center;
    background: var(--taling-purple);
  }

  .taling-news-card-link {
    position: absolute;
    inset: 0;
    display: block;
    color: inherit;
    text-decoration: none;
  }

  .taling-news-card :global(.taling-news-img),
  .taling-news-card :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .taling-news-placeholder {
    display: grid;
    place-items: center;
    width: 100%;
    height: 100%;
    background:
      linear-gradient(145deg, rgba(42, 0, 120, 0.82), rgba(255, 122, 26, 0.72)),
      var(--taling-purple);
    color: var(--taling-yellow);
    font-family: var(--taling-font-serif);
    font-size: 5rem;
  }

  .taling-news-copy {
    position: absolute;
    inset: auto 0 0;
    display: grid;
    gap: 0.4rem;
    padding: 1rem;
    background: linear-gradient(180deg, transparent, rgba(18, 5, 31, 0.88));
  }

  .taling-news-badge {
    width: fit-content;
    border-color: color-mix(in srgb, var(--taling-yellow) 60%, transparent);
    background: rgba(18, 5, 31, 0.62);
    color: var(--taling-yellow);
    font-weight: 900;
  }

  .taling-news-copy strong {
    font-size: 1.05rem;
    line-height: 1.18;
  }

  .taling-events {
    position: relative;
    padding: 6rem 0 7.5rem;
    overflow: hidden;
    background:
      radial-gradient(
        circle at 17% 18%,
        rgba(255, 211, 68, 0.86),
        transparent 10rem
      ),
      linear-gradient(135deg, #ffd344 0%, #ffb13a 32%, #ff7a1a 100%);
    color: var(--taling-purple);
  }

  .taling-events h2 {
    margin-bottom: 5.25rem;
    color: #211028;
    text-align: center;
    font-size: clamp(3.4rem, 6.8vw, 6.6rem);
  }

  .taling-events-shell {
    position: relative;
    z-index: 1;
  }

  .taling-event-feature {
    display: grid;
    grid-template-columns: minmax(280px, 0.92fr) minmax(320px, 1.08fr);
    gap: clamp(3rem, 7vw, 7rem);
    align-items: center;
  }

  .taling-event-carousel {
    display: grid;
    gap: 1.75rem;
  }

  .taling-event-posters {
    position: relative;
    min-height: 440px;
    isolation: isolate;
  }

  .taling-event-poster {
    position: absolute;
    top: 0;
    left: 50%;
    display: block;
    width: 265px;
    height: 374px;
    overflow: hidden;
    border: 8px solid var(--taling-purple);
    background: var(--taling-purple);
    opacity: 0;
    pointer-events: none;
    transform: translateX(-50%) translateY(64px) scale(0.74);
    transition:
      transform 260ms cubic-bezier(0.22, 1, 0.36, 1),
      opacity 260ms cubic-bezier(0.22, 1, 0.36, 1),
      filter 260ms cubic-bezier(0.22, 1, 0.36, 1);
  }

  .taling-event-poster-active {
    z-index: 3;
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(0) scale(1);
  }

  .taling-event-poster-prev {
    z-index: 2;
    opacity: 0.86;
    pointer-events: auto;
    filter: brightness(0.82);
    transform: translateX(calc(-50% - 165px)) translateY(42px) scale(0.86);
  }

  .taling-event-poster-next {
    z-index: 1;
    opacity: 0.72;
    pointer-events: auto;
    filter: brightness(0.72);
    transform: translateX(calc(-50% + 165px)) translateY(46px) scale(0.86);
  }

  .taling-event-poster-far-prev,
  .taling-event-poster-far-next {
    z-index: 0;
    opacity: 0.34;
    filter: brightness(0.62);
  }

  .taling-event-poster-far-prev {
    transform: translateX(calc(-50% - 275px)) translateY(82px) scale(0.7);
  }

  .taling-event-poster-far-next {
    transform: translateX(calc(-50% + 275px)) translateY(82px) scale(0.7);
  }

  .taling-event-poster :global(.taling-event-poster-img),
  .taling-event-poster :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .taling-event-poster-fallback {
    display: grid;
    align-content: center;
    gap: 1rem;
    width: 100%;
    height: 100%;
    padding: 1.25rem;
    background:
      linear-gradient(
        160deg,
        rgba(255, 211, 68, 0.96),
        rgba(255, 122, 26, 0.92)
      ),
      var(--taling-yellow);
    color: var(--taling-purple);
  }

  .taling-event-poster-fallback span {
    font-weight: 900;
  }

  .taling-event-poster-fallback strong {
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
  }

  .taling-event-copy {
    max-width: 620px;
    padding: 0;
    overflow: visible;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: var(--taling-purple);
  }

  .taling-event-date {
    display: inline-block;
    margin: 0 0 0.65rem;
    padding: 0.35rem 0.8rem;
    background: var(--taling-purple);
    color: var(--taling-white);
    font-weight: 900;
  }

  .taling-event-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.9rem;
  }

  .taling-carousel-nav {
    width: 45px;
    height: 45px;
    padding: 0;
    border-radius: 0.25rem;
    border: 2px solid var(--taling-purple);
    background: color-mix(
      in srgb,
      var(--taling-white) 86%,
      var(--taling-yellow)
    );
    color: var(--taling-purple);
    font-size: 1.8rem;
    font-weight: 900;
    line-height: 1;
    box-shadow: 6px 6px 0
      color-mix(in srgb, var(--taling-purple) 28%, transparent);
  }

  .taling-carousel-nav:hover {
    background: var(--taling-purple);
    color: var(--taling-white);
    box-shadow: 3px 3px 0
      color-mix(in srgb, var(--taling-purple) 34%, transparent);
  }

  .taling-event-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
  }

  .taling-carousel-dot {
    width: 24px;
    height: 24px;
    min-height: 24px;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: transparent;
  }

  .taling-carousel-dot span {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: color-mix(
      in srgb,
      var(--taling-purple) 34%,
      var(--taling-white)
    );
  }

  .taling-carousel-dot-active span {
    width: 18px;
    background: var(--taling-purple);
  }

  .taling-event-copy :global(h3) {
    margin: 0;
    color: var(--taling-purple);
    font-family: var(--taling-font-sans);
    font-size: clamp(3rem, 7vw, 6.8rem);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.92;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
    color: transparent;
  }

  .taling-event-rule {
    width: 100%;
    height: 12px;
    margin: 1.35rem 0 1.75rem;
    background: var(--taling-purple);
  }

  .taling-event-description,
  .taling-event-location {
    margin: 0;
    max-width: 64ch;
    color: #231328;
    font-weight: 800;
    line-height: 1.44;
  }

  .taling-event-location {
    margin-top: 1rem;
    color: var(--taling-purple);
  }

  .taling-event-copy :global(.taling-section-link) {
    margin-top: 1.5rem;
    background: var(--taling-purple);
    color: var(--taling-white);
  }

  .taling-flower,
  .taling-puzzle {
    position: absolute;
    pointer-events: none;
  }

  .taling-flower {
    top: 16%;
    left: -34px;
    width: 190px;
    aspect-ratio: 1;
    background:
      radial-gradient(
          ellipse at center,
          rgba(255, 122, 26, 0.72) 0 38%,
          transparent 40%
        )
        50% 0 / 50% 50%,
      radial-gradient(
          ellipse at center,
          rgba(255, 122, 26, 0.72) 0 38%,
          transparent 40%
        )
        100% 50% / 50% 50%,
      radial-gradient(
          ellipse at center,
          rgba(255, 122, 26, 0.72) 0 38%,
          transparent 40%
        )
        50% 100% / 50% 50%,
      radial-gradient(
          ellipse at center,
          rgba(255, 122, 26, 0.72) 0 38%,
          transparent 40%
        )
        0 50% / 50% 50%;
    background-repeat: no-repeat;
    rotate: -22deg;
  }

  .taling-puzzle {
    right: -28px;
    bottom: 30px;
    width: 250px;
    aspect-ratio: 1;
    background:
      linear-gradient(var(--taling-purple), var(--taling-purple)) 30% 10% / 46%
        38%,
      linear-gradient(var(--taling-purple), var(--taling-purple)) 58% 42% / 46%
        38%,
      linear-gradient(var(--taling-purple), var(--taling-purple)) 10% 54% / 38%
        34%;
    background-repeat: no-repeat;
    rotate: 36deg;
  }

  .taling-cabinet {
    padding: 6rem 0 7rem;
    background:
      radial-gradient(
        circle at 86% 20%,
        rgba(255, 211, 68, 0.42),
        transparent 15rem
      ),
      linear-gradient(160deg, #f6bb2f 0%, #ff8a1f 54%, #c85910 100%);
    color: #1f1520;
  }

  .taling-cabinet-grid {
    display: grid;
    grid-template-columns: minmax(280px, 0.85fr) minmax(320px, 1.15fr);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .taling-jargon {
    margin-bottom: 2.5rem;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(3rem, 7vw, 6.6rem);
    font-weight: 900;
    letter-spacing: 0.05em;
    line-height: 0.9;
    -webkit-text-stroke: 2px var(--taling-purple);
  }

  .taling-cabinet h2 {
    margin-bottom: 1.8rem;
    color: #1f1520;
    font-size: clamp(2.3rem, 4.5vw, 4rem);
  }

  .taling-cabinet-copy p:not(.taling-jargon) {
    margin: 0 0 1.2rem;
    max-width: 60ch;
    color: #241422;
    font-weight: 800;
    line-height: 1.48;
  }

  .taling-cabinet-photo {
    overflow: hidden;
    border: 10px solid color-mix(in srgb, var(--taling-yellow) 45%, transparent);
    background: var(--taling-purple);
  }

  .taling-cabinet-photo :global(.taling-cabinet-img),
  .taling-cabinet-photo :global(img) {
    width: 100%;
    height: 420px;
    object-fit: cover;
  }

  .taling-empty-bright {
    padding: 3rem;
    border: 4px solid var(--taling-purple);
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: var(--taling-purple);
    font-weight: 900;
    text-align: center;
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

  :global(.taling-news-link) {
    width: auto;
    height: auto;
    min-height: 36px;
    padding-inline: 0;
    background: transparent;
    color: color-mix(in srgb, var(--taling-white) 76%, transparent);
    font-weight: 800;
  }

  :global(.taling-news-card) {
    position: relative;
    display: block;
    height: 397px;
    overflow: hidden;
    padding: 0;
    border-radius: 0;
    box-shadow: none;
    color: var(--taling-white);
    scroll-snap-align: center;
    background: var(--taling-purple);
  }

  :global(.taling-news-card) :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  :global(.taling-news-badge),
  :global(.taling-event-date) {
    width: fit-content;
    height: auto;
    font-weight: 900;
  }

  :global(.taling-news-badge) {
    border-color: color-mix(in srgb, var(--taling-yellow) 60%, transparent);
    background: rgba(18, 5, 31, 0.62);
    color: var(--taling-yellow);
  }

  :global(.taling-event-copy) {
    max-width: 620px;
    padding: 0;
    overflow: visible;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: var(--taling-purple);
  }

  :global(.taling-event-date) {
    display: inline-block;
    margin: 0 0 0.65rem;
    padding: 0.35rem 0.8rem;
    border-color: var(--taling-purple);
    background: var(--taling-purple);
    color: var(--taling-white);
  }

  :global(.taling-event-copy) :global(h3) {
    margin: 0;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(3rem, 7vw, 6.8rem);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.92;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
  }

  :global(.taling-event-copy) :global(.taling-section-link) {
    margin-top: 1.5rem;
    background: var(--taling-purple);
    color: var(--taling-white);
  }

  :global(.taling-carousel-nav) {
    width: 45px;
    height: 45px;
    min-height: 45px;
    padding: 0;
    border-radius: 0.25rem;
    border: 2px solid var(--taling-purple);
    background: color-mix(
      in srgb,
      var(--taling-white) 86%,
      var(--taling-yellow)
    );
    color: var(--taling-purple);
    font-size: 1.8rem;
    font-weight: 900;
    line-height: 1;
    box-shadow: 6px 6px 0
      color-mix(in srgb, var(--taling-purple) 28%, transparent);
  }

  :global(.taling-carousel-nav:hover) {
    background: var(--taling-purple);
    color: var(--taling-white);
    box-shadow: 3px 3px 0
      color-mix(in srgb, var(--taling-purple) 34%, transparent);
  }

  :global(.taling-carousel-dot) {
    width: 24px;
    height: 24px;
    min-height: 24px;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: transparent;
  }

  :global(.taling-carousel-dot) :global(span) {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: color-mix(
      in srgb,
      var(--taling-purple) 34%,
      var(--taling-white)
    );
  }

  :global(.taling-carousel-dot-active) :global(span) {
    width: 18px;
    background: var(--taling-purple);
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

  @media (min-width: 820px) {
    .taling-nav,
    .taling-login {
      display: flex;
    }

    :global(.taling-login) {
      display: flex;
    }

    .taling-menu {
      display: none;
    }
  }

  @media (max-width: 819px) {
    .taling-section-shell,
    .taling-header-inner,
    .taling-footer-inner,
    .taling-footer-base {
      width: min(100% - 1.5rem, 620px);
    }

    .taling-login {
      display: none;
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

    .taling-hero {
      min-height: 760px;
    }

    .taling-hero-center {
      padding-top: 1rem;
    }

    .taling-hero-signature {
      left: 1rem;
      right: 1rem;
      bottom: 2rem;
      flex-direction: column;
      align-items: start;
    }

    .taling-hero-signature :global(.taling-signature-logo),
    .taling-hero-signature :global(img) {
      width: 142px;
    }

    .taling-star-left {
      left: -88px;
      width: 255px;
    }

    .taling-star-right {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }

    .taling-news {
      padding: 4.25rem 0 5rem;
    }

    .taling-news-heading {
      display: grid;
      margin-bottom: 2.25rem;
      padding-bottom: 1.3rem;
    }

    .taling-news-strip {
      grid-auto-columns: minmax(236px, 78vw);
      margin-inline: -0.75rem;
      padding-inline: 0.75rem;
    }

    .taling-news-card {
      height: 330px;
    }

    .taling-events {
      padding: 4.5rem 0 5.5rem;
    }

    .taling-events h2 {
      margin-bottom: 3rem;
    }

    .taling-event-feature,
    .taling-cabinet-grid,
    .taling-footer-inner {
      grid-template-columns: 1fr;
    }

    .taling-event-posters {
      min-height: 355px;
    }

    .taling-event-poster {
      width: min(235px, 58vw);
      height: 322px;
      border-width: 6px;
    }

    .taling-event-poster-active {
      transform: translateX(-50%) translateY(0) scale(1);
    }

    .taling-event-poster-prev {
      transform: translateX(calc(-50% - 88px)) translateY(36px) scale(0.82);
    }

    .taling-event-poster-next {
      transform: translateX(calc(-50% + 88px)) translateY(38px) scale(0.82);
    }

    .taling-event-poster-far-prev {
      transform: translateX(calc(-50% - 152px)) translateY(70px) scale(0.66);
    }

    .taling-event-poster-far-next {
      transform: translateX(calc(-50% + 152px)) translateY(70px) scale(0.66);
    }

    .taling-event-controls {
      gap: 0.6rem;
    }

    .taling-event-copy :global(h3),
    :global(.taling-event-copy) :global(h3) {
      font-size: clamp(3rem, 17vw, 5.5rem);
    }

    .taling-flower {
      width: 140px;
      left: -50px;
    }

    .taling-puzzle {
      width: 180px;
      right: -70px;
    }

    .taling-cabinet {
      padding: 4.5rem 0 5rem;
    }

    .taling-cabinet-photo :global(.taling-cabinet-img),
    .taling-cabinet-photo :global(img) {
      height: 280px;
    }

    .taling-footer-links {
      justify-content: start;
    }

    .taling-footer-base {
      display: grid;
    }
  }
</style>
