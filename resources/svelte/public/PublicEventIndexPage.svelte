<script>
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import heroBgImage from "../../images/hero-bg.png?enhanced&w=640;960;1280;1920";

  let {
    homeUrl = "/",
    acaraUrl = "/acara",
    title = "Acara Mendatang",
    kicker = "Agenda Kabinet",
    description = "",
    events = [],
    pagination = null,
    emptyText = "Belum ada acara mendatang yang dipublikasikan.",
    seo = null,
  } = $props();

  const fallbackImage = fallbackImageAsset.original ?? fallbackImageAsset;
  const jsonLdScriptOpen = '<script type="application/ld+json">';
  const jsonLdScriptClose = "</" + "script>";

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<svelte:head>
  <title>{seo?.title || `${title} - HIMATEKKOM ITS`}</title>
  <meta name="description" content={seo?.description || description} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  {#if seo?.image}
    <meta property="og:image" content={seo.image} />
    <meta name="twitter:image" content={seo.image} />
  {/if}
  <meta property="og:type" content={seo?.type || "website"} />
  <meta property="og:title" content={seo?.title || title} />
  <meta property="og:description" content={seo?.description || description} />
  <meta
    name="twitter:card"
    content={seo?.image ? "summary_large_image" : "summary"}
  />
  <meta name="twitter:title" content={seo?.title || title} />
  <meta name="twitter:description" content={seo?.description || description} />
  {#if seo?.jsonLd}
    {@html jsonLdScriptOpen + seo.jsonLd + jsonLdScriptClose}
  {/if}
</svelte:head>

<div class="public-event-index">
  <section class="event-hero" aria-labelledby="event-index-heading">
    <!-- Texture Overlay -->
    <div
      class="pointer-events-none absolute inset-0 opacity-15 mix-blend-overlay overflow-hidden"
    >
      <OptimizedImage
        src={heroBgImage}
        alt=""
        class="w-full h-full object-cover object-center"
        loading="eager"
        decoding="async"
        fetchpriority="high"
      />
    </div>
    <span class="event-star event-star-left" aria-hidden="true"></span>
    <span class="event-star event-star-right" aria-hidden="true"></span>
    <div class="taling-section-shell event-hero-copy">
      <p class="taling-page-kicker">{kicker}</p>
      <h1 id="event-index-heading" class="taling-page-title">{title}</h1>
      <div class="event-hero-rule" aria-hidden="true"></div>
      <p class="taling-page-copy">{description}</p>
    </div>
  </section>

  {#if !events.length}
    <section class="event-empty">
      <div class="taling-section-shell">
        <div class="taling-empty-bright">
          <h2>Belum ada acara mendatang.</h2>
          <p>{emptyText}</p>
          <a href={homeUrl} class="taling-section-link">Kembali ke beranda</a>
        </div>
      </div>
    </section>
  {:else}
    <section class="event-posters" aria-label="Daftar acara">
      <div class="taling-section-shell event-poster-grid">
        {#each events as event, index (event.href)}
          <a href={event.href} class="event-card">
            <div class="event-card-poster">
              {#if event.poster}
                <OptimizedImage
                  src={event.poster}
                  alt={event.title}
                  class="event-card-img"
                  loading={index < 3 ? "eager" : "lazy"}
                  decoding="async"
                  sizes="(min-width: 1000px) 310px, 78vw"
                  onerror={handleImageError}
                />
              {:else}
                <div class="event-card-fallback">
                  <span>{event.dateLabel || "Segera"}</span>
                  <strong>{event.title}</strong>
                </div>
              {/if}
            </div>
            <div class="event-card-copy">
              <span>{event.dateLabel || "Segera"}</span>
              <h2>{event.title}</h2>
              {#if event.location}
                <p>{event.location}</p>
              {/if}
            </div>
          </a>
        {/each}
      </div>
    </section>
  {/if}

  {#if pagination && pagination.total > 0}
    <section class="event-pagination">
      <div class="taling-section-shell event-pagination-inner">
        <p>
          Menampilkan {pagination.from} - {pagination.to} dari {pagination.total}
          acara.
        </p>
        <div>
          <span>Halaman {pagination.currentPage} / {pagination.lastPage}</span>
          {#if pagination.prevUrl}
            <a href={pagination.prevUrl}>Sebelumnya</a>
          {/if}
          {#if pagination.nextUrl}
            <a href={pagination.nextUrl}>Selanjutnya</a>
          {/if}
        </div>
      </div>
    </section>
  {/if}
</div>

<style>
  .event-hero {
    position: relative;
    overflow: hidden;
    padding: 6rem 0 7rem;
    background:
      radial-gradient(
        circle at 20% 20%,
        rgba(255, 211, 68, 0.12),
        transparent 32rem
      ),
      linear-gradient(180deg, #18072e 0%, #12051f 100%);
    color: var(--taling-white);
  }

  .event-hero::before {
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

  .event-hero-copy {
    position: relative;
    z-index: 2;
    display: grid;
    justify-items: center;
    gap: 1.5rem;
    text-align: center;
  }

  .event-hero-rule {
    width: min(534px, 68vw);
    height: 18px;
    background: var(--taling-yellow);
    box-shadow: 0 0 24px rgba(255, 211, 68, 0.52);
  }

  .event-star {
    position: absolute;
    z-index: 1;
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

  .event-star-left {
    top: -96px;
    left: 14%;
    width: 300px;
  }

  .event-star-right {
    right: -92px;
    bottom: 42px;
    width: 230px;
  }

  .event-posters {
    padding: 6rem 0 7.5rem;
    background: #fffdf8;
    color: var(--taling-ink);
  }

  .event-poster-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.5rem, 3vw, 2.5rem);
  }

  .event-card {
    display: grid;
    gap: 1.2rem;
    color: inherit;
    text-decoration: none;
    transition: transform 300ms ease;
  }

  .event-card:hover {
    transform: translateY(-4px);
  }

  .event-card:nth-child(3n + 2) {
    transform: translateY(2.25rem);
  }

  .event-card:nth-child(3n + 2):hover {
    transform: translateY(calc(2.25rem - 6px));
  }

  .event-card:nth-child(3n) {
    transform: translateY(4.5rem);
  }

  .event-card:nth-child(3n):hover {
    transform: translateY(calc(4.5rem - 6px));
  }

  .event-card-poster {
    height: 430px;
    overflow: hidden;
    border: 8px solid var(--taling-purple);
    background: var(--taling-purple);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition:
      border-color 300ms ease,
      box-shadow 300ms ease;
  }

  .event-card:hover .event-card-poster {
    border-color: var(--taling-orange);
    box-shadow: 0 16px 40px rgba(255, 122, 26, 0.25);
  }

  .event-card-poster :global(.event-card-img),
  .event-card-poster :global(img),
  .event-card-fallback {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .event-card-fallback {
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

  .event-card-fallback span,
  .event-card-copy span {
    font-weight: 900;
  }

  .event-card-copy span {
    color: var(--taling-orange);
  }

  .event-card-fallback strong {
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
  }

  .event-card-copy {
    display: grid;
    gap: 0.6rem;
  }

  .event-card-copy h2 {
    margin: 0;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.95;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
    transition: color 250ms ease;
  }

  .event-card:hover .event-card-copy h2 {
    color: var(--taling-purple);
  }

  .event-card-copy p {
    margin: 0;
    color: color-mix(in srgb, var(--taling-ink) 72%, transparent);
    font-weight: 800;
  }

  .event-empty {
    padding: 6rem 0;
    background: #fffdf8;
    color: var(--taling-ink);
  }

  .event-pagination {
    padding: 4rem 0;
    background: #fffdf8;
    color: var(--taling-ink);
  }

  .event-pagination-inner {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-weight: 800;
    border-top: 2px solid color-mix(in srgb, var(--taling-ink) 16%, transparent);
    padding-top: 2rem;
  }

  .event-pagination-inner p {
    margin: 0;
  }

  .event-pagination-inner div {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .event-pagination a {
    min-height: 3rem;
    border: 2px solid var(--taling-purple);
    border-radius: 999px;
    background: linear-gradient(
      90deg,
      var(--taling-orange),
      var(--taling-yellow)
    );
    color: var(--taling-purple);
    padding: 0.65rem 1.2rem;
    font: inherit;
    font-weight: 900;
    text-decoration: none;
    transition:
      transform 200ms ease,
      box-shadow 200ms ease;
  }

  .event-pagination a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 122, 26, 0.2);
  }

  @media (max-width: 980px) {
    .event-poster-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .event-card:nth-child(n) {
      transform: none;
    }
  }

  @media (max-width: 819px) {
    .event-hero {
      padding: 4.25rem 0 5rem;
    }

    .event-posters {
      padding: 4rem 0 5.5rem;
    }

    .event-poster-grid {
      grid-template-columns: 1fr;
    }

    .event-card-poster {
      height: min(122vw, 430px);
      border-width: 6px;
    }

    .event-star-left {
      left: -88px;
      width: 255px;
    }

    .event-star-right {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }

    .event-pagination-inner {
      display: grid;
    }
  }
</style>
