<script>
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import heroBgImage from "../../images/hero-bg.png?enhanced&w=640;960;1280;1920";

  let {
    homeUrl = "/",
    acaraUrl = "/acara",
    event = {
      title: "",
      location: null,
      dateLabel: "",
      endDateLabel: "",
      poster: null,
      contentHtml: "",
      excerpt: "",
    },
    upcomingEvents = [],
    seo = null,
  } = $props();

  const fallbackImage = fallbackImageAsset.original ?? fallbackImageAsset;
  const jsonLdScriptOpen = '<script type="application/ld+json">';
  const jsonLdScriptClose = "</" + "script>";

  const handleImageError = (imgEvent) => {
    if (imgEvent.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    imgEvent.currentTarget.src = fallbackImage;
  };
</script>

<svelte:head>
  <title>{seo?.title || `${event.title} - HIMATEKKOM ITS`}</title>
  <meta name="description" content={seo?.description || event.excerpt || ""} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  {#if seo?.image}
    <meta property="og:image" content={seo.image} />
    <meta name="twitter:image" content={seo.image} />
  {/if}
  <meta property="og:type" content={seo?.type || "article"} />
  <meta property="og:title" content={seo?.title || event.title} />
  <meta
    property="og:description"
    content={seo?.description || event.excerpt || ""}
  />
  <meta
    name="twitter:card"
    content={seo?.image ? "summary_large_image" : "summary"}
  />
  <meta name="twitter:title" content={seo?.title || event.title} />
  <meta
    name="twitter:description"
    content={seo?.description || event.excerpt || ""}
  />
  {#if seo?.jsonLd}
    {@html jsonLdScriptOpen + seo.jsonLd + jsonLdScriptClose}
  {/if}
</svelte:head>

<article class="public-event-show">
  <section class="event-show-hero" aria-labelledby="event-heading">
    <!-- Ambient Background Gradient & Texture matching Departemen -->
    <div
      class="absolute inset-0 -z-10 bg-gradient-to-br from-[#5d0077] to-[#2a0078] overflow-hidden"
    >
      <picture class="contents">
        <source srcset="/images/figma-taling/hero-bg.avif" type="image/avif" />
        <source srcset="/images/figma-taling/hero-bg.webp" type="image/webp" />
        <img
          class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-overlay pointer-events-none"
          src="/images/figma-taling/hero-bg.png"
          alt=""
          loading="eager"
          decoding="async"
          fetchpriority="high"
        />
      </picture>
      <picture class="contents">
        <source
          srcset="/images/figma-taling/botanical.avif"
          type="image/avif"
        />
        <source
          srcset="/images/figma-taling/botanical.webp"
          type="image/webp"
        />
        <img
          class="animate-slow-pan absolute -top-[22%] -left-[20%] h-[180%] w-[170%] max-w-none object-cover opacity-25 mix-blend-soft-light pointer-events-none"
          src="/images/figma-taling/botanical.png"
          alt=""
          width="1600"
          height="1066"
          loading="eager"
          decoding="async"
          fetchpriority="high"
        />
      </picture>
    </div>

    <!-- Branded Floating Vector Stars matching Departemen -->
    <img
      src="/images/figma-taling/star-large.svg"
      alt=""
      class="star-large pointer-events-none opacity-80 drop-shadow-2xl"
      width="492"
      height="463"
    />
    <img
      src="/images/figma-taling/star-small.svg"
      alt=""
      class="star-small pointer-events-none opacity-80 drop-shadow-2xl"
      width="375"
      height="404"
    />
    <div class="taling-section-shell event-show-grid">
      <div class="event-show-poster-wrap">
        <a href={acaraUrl} class="event-show-back">
          <i class="fas fa-arrow-left"></i> Kembali ke acara
        </a>
        {#if event.poster}
          <figure class="event-show-poster">
            <OptimizedImage
              src={event.poster}
              alt={event.title}
              class="event-show-poster-img"
              loading="eager"
              decoding="async"
              fetchpriority="high"
              sizes="(min-width: 900px) 330px, 70vw"
              onerror={handleImageError}
            />
          </figure>
        {:else}
          <div class="event-show-poster event-show-poster-fallback">
            <span>{event.dateLabel || "Segera"}</span>
            <strong>{event.title}</strong>
          </div>
        {/if}
      </div>

      <div class="event-show-copy">
        <p class="event-date-badge">{event.dateLabel || "Segera"}</p>
        <h1 id="event-heading" class="taling-page-title">{event.title}</h1>
        <!-- Glowing Gradient Bar matching Departemen -->
        <div class="hero-glow-wrapper w-full max-w-[280px] h-[22px] -mt-1 mb-2">
          <div
            class="h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[1px]"
          ></div>
        </div>
        <p class="taling-page-copy">{event.excerpt}</p>
        <div class="event-show-meta">
          {#if event.endDateLabel}
            <span>s.d. {event.endDateLabel}</span>
          {/if}
          {#if event.location}
            <span>{event.location}</span>
          {/if}
        </div>
      </div>
    </div>
  </section>

  <section class="event-show-body">
    <div class="taling-section-shell event-show-body-grid">
      <div class="event-show-content public-article-content">
        {@html event.contentHtml}
      </div>

      <aside class="event-show-aside">
        <div class="event-aside-block">
          <p class="event-aside-label">Agenda kabinet</p>
          <h2>Acara lainnya</h2>
          <p>Acara dan kegiatan resmi mendatang.</p>
          <a href={acaraUrl}>
            Lihat semua acara
            <i class="fas fa-arrow-right text-[16px]" aria-hidden="true"></i>
          </a>
        </div>

        {#if upcomingEvents.length}
          <div class="event-mini-list">
            {#each upcomingEvents as item (item.href)}
              <a href={item.href}>
                <strong>{item.title}</strong>
                <span>{item.dateLabel || "-"}</span>
              </a>
            {/each}
          </div>
        {/if}
      </aside>
    </div>
  </section>
</article>

<style>
  .event-show-hero,
  .event-show-body {
    position: relative;
    overflow: hidden;
  }

  .event-show-hero {
    isolation: isolate;
    padding: 6.5rem 0 8rem;
    color: var(--taling-white);
  }

  :global(.taling-page-title) {
    font-family: "The Seasons", "The Seasons", Georgia, serif !important;
    font-weight: 300 !important;
    text-shadow: 0px 0px 20px #ffffff;
  }

  .event-show-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(280px, 0.9fr) minmax(320px, 1.1fr);
    gap: clamp(3rem, 7vw, 7rem);
    align-items: center;
  }

  .event-show-poster-wrap {
    display: grid;
    gap: 1.2rem;
  }

  .event-show-back {
    width: fit-content;
    color: var(--taling-yellow);
    font-weight: 900;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: transform 200ms ease;
  }

  .event-show-back:hover {
    text-decoration: underline;
    transform: translateX(-4px);
  }

  .event-show-poster {
    margin: 0;
    width: min(330px, 72vw);
    min-height: 466px;
    overflow: hidden;
    border: 8px solid var(--taling-purple);
    background: var(--taling-purple);
    box-shadow: 24px 24px 0 rgba(255, 211, 68, 0.15);
  }

  .event-show-poster :global(.event-show-poster-img),
  .event-show-poster :global(img) {
    width: 100%;
    height: 466px;
    object-fit: cover;
  }

  .event-show-poster-fallback {
    display: grid;
    align-content: center;
    gap: 1rem;
    padding: 1.25rem;
    background: linear-gradient(
      160deg,
      rgba(255, 211, 68, 0.96),
      rgba(255, 122, 26, 0.92)
    );
    color: var(--taling-purple);
  }

  .event-show-poster-fallback span {
    font-weight: 900;
  }

  .event-show-poster-fallback strong {
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
  }

  .event-show-copy {
    display: grid;
    gap: 1.25rem;
    max-width: 680px;
  }

  .event-date-badge {
    width: fit-content;
    margin: 0;
    padding: 0.35rem 0.8rem;
    background: var(--taling-yellow);
    color: var(--taling-purple);
    font-weight: 900;
  }

  /* Glow wrapper matching standard design system */
  .hero-glow-wrapper {
    position: relative;
  }

  .event-show-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.5rem;
    color: color-mix(in srgb, var(--taling-white) 78%, transparent);
    font-weight: 800;
  }

  .star-large {
    position: absolute;
    top: -60px;
    left: -90px;
    width: 200px;
    height: 188px;
    animation: floatLarge 8s ease-in-out infinite;
  }
  @media (min-width: 768px) {
    .star-large {
      top: -100px;
      left: -150px;
      width: 320px;
      height: 301px;
    }
  }
  @media (min-width: 1024px) {
    .star-large {
      top: -155px;
      left: -239px;
      width: 507px;
      height: 476px;
    }
  }

  .star-small {
    position: absolute;
    top: 320px;
    right: -80px;
    width: 150px;
    height: 161px;
    animation: floatSmall 6s ease-in-out infinite;
  }
  @media (min-width: 768px) {
    .star-small {
      top: 300px;
      right: -130px;
      width: 250px;
      height: 269px;
    }
  }
  @media (min-width: 1024px) {
    .star-small {
      top: 250px;
      right: -188px;
      width: 388px;
      height: 418px;
    }
  }

  @keyframes floatLarge {
    0%,
    100% {
      transform: translateY(0) rotate(-10deg);
    }
    50% {
      transform: translateY(-15px) rotate(-8deg);
    }
  }

  @keyframes floatSmall {
    0%,
    100% {
      transform: translateY(0) rotate(15deg);
    }
    50% {
      transform: translateY(-12px) rotate(10deg);
    }
  }

  .event-show-body {
    padding: 6rem 0 7rem;
    background: #ffffff;
  }

  .event-show-body-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
    gap: clamp(2.5rem, 6vw, 5rem);
    align-items: start;
  }

  .event-show-content {
    max-width: 76ch;
    color: var(--taling-ink);
    font-size: 1.08rem;
    font-weight: 700;
    line-height: 1.78;
  }

  .public-article-content :global(h1),
  .public-article-content :global(h2),
  .public-article-content :global(h3),
  .public-article-content :global(h4),
  .public-article-content :global(p),
  .public-article-content :global(ul),
  .public-article-content :global(ol),
  .public-article-content :global(blockquote) {
    margin-top: 0;
    margin-bottom: 1.35rem;
  }

  .public-article-content :global(h2),
  .public-article-content :global(h3),
  .public-article-content :global(h4) {
    color: var(--taling-purple);
    font-family: var(--taling-font-serif);
    line-height: 1.08;
  }

  .public-article-content :global(a) {
    color: var(--taling-purple);
    font-weight: 900;
    text-decoration: underline;
    text-underline-offset: 0.2rem;
  }

  .public-article-content :global(ul),
  .public-article-content :global(ol) {
    padding-left: 1.2rem;
  }

  .public-article-content :global(blockquote) {
    margin-left: 0;
    padding: 1.25rem 1.4rem;
    background: var(--taling-cream);
    color: var(--taling-purple);
    font-weight: 900;
  }

  .public-article-content :global(img) {
    max-width: 100%;
    height: auto;
  }

  .event-show-aside {
    display: grid;
    gap: 2rem;
    position: sticky;
    top: 108px;
  }

  .event-aside-block {
    border-top: 8px solid var(--taling-purple);
    padding-top: 1rem;
  }

  .event-aside-label {
    margin: 0 0 0.85rem;
    color: var(--taling-orange);
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .event-aside-block h2 {
    margin: 0;
    color: var(--taling-purple);
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
  }

  .event-aside-block p:not(.event-aside-label) {
    margin: 1rem 0 0;
    color: color-mix(in srgb, var(--taling-ink) 74%, transparent);
    font-weight: 800;
    line-height: 1.55;
  }

  .event-aside-block a,
  .event-mini-list a {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1.15rem;
    color: var(--taling-purple);
    font-weight: 900;
    text-decoration: none;
  }

  .event-mini-list {
    display: grid;
    gap: 1rem;
  }

  .event-mini-list a {
    display: grid;
    gap: 0.35rem;
    margin: 0;
    border-top: 1px solid color-mix(in srgb, var(--taling-ink) 18%, transparent);
    padding-top: 1rem;
    color: var(--taling-ink);
    transition: transform 200ms ease;
  }

  .event-mini-list a:hover {
    transform: translateX(4px);
  }

  .event-mini-list strong {
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: 1.7rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.94;
    text-transform: uppercase;
    -webkit-text-stroke: 1px var(--taling-purple);
    transition: color 200ms ease;
  }

  .event-mini-list a:hover strong {
    color: var(--taling-purple);
  }

  .event-mini-list span {
    color: color-mix(in srgb, var(--taling-ink) 62%, transparent);
    font-size: 0.9rem;
    font-weight: 800;
  }

  @media (max-width: 819px) {
    .event-show-hero,
    .event-show-body {
      padding: 4.25rem 0 5rem;
    }

    .event-show-grid,
    .event-show-body-grid {
      grid-template-columns: 1fr;
    }

    .event-show-poster {
      width: min(280px, 78vw);
      min-height: 390px;
      border-width: 6px;
    }

    .event-show-poster :global(.event-show-poster-img),
    .event-show-poster :global(img) {
      height: 390px;
    }

    .event-show-aside {
      position: static;
    }
  }
</style>
