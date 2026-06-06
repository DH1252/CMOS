<script>
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";

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
    <span class="event-flower" aria-hidden="true"></span>
    <span class="event-puzzle" aria-hidden="true"></span>
    <div class="taling-section-shell event-hero-copy">
      <p class="event-kicker">{kicker}</p>
      <h1 id="event-index-heading">{title}</h1>
      <p>{description}</p>
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
  .event-hero,
  .event-empty,
  .event-posters,
  .event-pagination {
    position: relative;
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

  .event-hero {
    padding: 6rem 0 2rem;
  }

  .event-hero-copy {
    position: relative;
    z-index: 1;
    display: grid;
    justify-items: center;
    gap: 1.2rem;
    text-align: center;
  }

  .event-kicker {
    margin: 0;
    color: #211028;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .event-hero h1 {
    margin: 0;
    color: #211028;
    font-family: var(--taling-font-serif);
    font-size: clamp(3.4rem, 6.8vw, 6.6rem);
    line-height: 1;
  }

  .event-hero p:not(.event-kicker) {
    margin: 0;
    max-width: 62ch;
    color: #231328;
    font-weight: 800;
    line-height: 1.44;
  }

  .event-posters {
    padding: 3rem 0 7.5rem;
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
  }

  .event-card:nth-child(3n + 2) {
    transform: translateY(2.25rem);
  }

  .event-card:nth-child(3n) {
    transform: translateY(4.5rem);
  }

  .event-card-poster {
    height: 430px;
    overflow: hidden;
    border: 8px solid var(--taling-purple);
    background: var(--taling-purple);
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
    font-size: clamp(2.5rem, 5vw, 4.8rem);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.92;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
  }

  .event-card-copy p {
    margin: 0;
    color: #231328;
    font-weight: 800;
  }

  .event-flower,
  .event-puzzle {
    position: absolute;
    pointer-events: none;
  }

  .event-flower {
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

  .event-puzzle {
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

  .event-empty,
  .event-pagination {
    padding: 4rem 0;
  }

  .event-empty h2,
  .event-empty p {
    margin: 0 0 1rem;
  }

  .event-pagination-inner {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-weight: 900;
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
    background: var(--taling-purple);
    color: var(--taling-white);
    padding: 0.65rem 1.2rem;
    font-weight: 900;
    text-decoration: none;
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
      padding: 4.5rem 0 1rem;
    }

    .event-posters {
      padding: 2rem 0 5.5rem;
    }

    .event-poster-grid {
      grid-template-columns: 1fr;
    }

    .event-card-poster {
      height: min(122vw, 430px);
      border-width: 6px;
    }

    .event-flower {
      left: -50px;
      width: 140px;
    }

    .event-puzzle {
      right: -70px;
      width: 180px;
    }

    .event-pagination-inner {
      display: grid;
    }
  }
</style>
