<script>
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import heroBgImage from "../../images/hero-bg.png?enhanced&w=640;960;1280;1920";

  let {
    homeUrl = "/",
    infoUrl = "/informasi",
    kicker = "Publikasi Organisasi",
    headline = "Artikel, pembaruan, dan dokumentasi resmi.",
    description = "",
    stats = [],
    filters = {
      action: "#",
      query: "",
      category: "",
      categories: [],
    },
    searchSummary = "",
    featured = null,
    articles = [],
    pagination = null,
    seo = null,
  } = $props();

  const hasActiveFilters = $derived(Boolean(filters.query || filters.category));
  const fallbackImage = fallbackImageAsset.original ?? fallbackImageAsset;
  const jsonLdScriptOpen = '<script type="application/ld+json">';
  const jsonLdScriptClose = "</" + "script>";
  const visibleStats = $derived(
    stats.filter((stat) => stat?.label === "Artikel Terbit"),
  );
  const cardArticles = $derived(featured ? articles : articles.slice(0));

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<svelte:head>
  <title>{seo?.title || `${headline} - HIMATEKKOM ITS`}</title>
  <meta name="description" content={seo?.description || description} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  {#if seo?.image}
    <meta property="og:image" content={seo.image} />
    <meta name="twitter:image" content={seo.image} />
  {/if}
  <meta property="og:type" content={seo?.type || "website"} />
  <meta property="og:title" content={seo?.title || headline} />
  <meta property="og:description" content={seo?.description || description} />
  <meta
    name="twitter:card"
    content={seo?.image ? "summary_large_image" : "summary"}
  />
  <meta name="twitter:title" content={seo?.title || headline} />
  <meta name="twitter:description" content={seo?.description || description} />
  {#if seo?.jsonLd}
    {@html jsonLdScriptOpen + seo.jsonLd + jsonLdScriptClose}
  {/if}
</svelte:head>

<div class="public-info-index">
  <section class="info-hero" aria-labelledby="info-index-heading">
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

    <div class="taling-section-shell info-hero-grid">
      <div class="info-hero-copy">
        <p class="taling-page-kicker">{kicker}</p>
        <h1 id="info-index-heading" class="taling-page-title">{headline}</h1>

        <!-- Glowing Gradient Bar matching Departemen -->
        <div
          class="hero-glow-wrapper w-full max-w-[280px] md:max-w-[400px] h-[22px] -mt-2"
        >
          <div
            class="h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[1px]"
          ></div>
        </div>

        <p class="taling-page-copy">{description}</p>
      </div>

      {#if visibleStats.length}
        <div class="info-stat-board" aria-label="Ringkasan arsip">
          {#each visibleStats as stat (stat.label)}
            <div class="info-stat-row">
              <span>{stat.label}</span>
              <strong>{stat.value}</strong>
            </div>
          {/each}
        </div>
      {/if}
    </div>
  </section>

  <section class="info-search" aria-label="Filter arsip">
    <div class="taling-section-shell info-search-shell">
      <form method="GET" action={filters.action} class="info-filter-form">
        <label>
          <span>Cari arsip</span>
          <input
            type="text"
            name="q"
            placeholder="Judul, ringkasan, atau kata kunci"
            value={filters.query || ""}
          />
        </label>

        <label>
          <span>Kategori</span>
          <select name="kategori">
            <option value="">Semua kategori</option>
            {#each filters.categories || [] as category (category.value)}
              <option
                value={category.value}
                selected={String(filters.category || "") ===
                  String(category.value)}
              >
                {category.label}
              </option>
            {/each}
          </select>
        </label>

        <button type="submit">Terapkan filter</button>
      </form>

      {#if hasActiveFilters}
        <div class="info-search-note">
          <p>{searchSummary}</p>
          <a href={filters.action}>Hapus filter</a>
        </div>
      {/if}
    </div>
  </section>

  {#if featured}
    <section class="info-feature" aria-labelledby="featured-heading">
      <div class="taling-section-shell info-feature-grid">
        <a href={featured.href} class="info-feature-media">
          {#if featured.coverImage}
            <OptimizedImage
              src={featured.coverImage}
              alt={featured.title}
              class="info-feature-img"
              loading="eager"
              decoding="async"
              fetchpriority="high"
              sizes="(min-width: 900px) 620px, 100vw"
              onerror={handleImageError}
            />
          {:else}
            <div class="info-feature-fallback" aria-hidden="true">HI</div>
          {/if}
        </a>

        <div class="info-feature-copy">
          <p class="info-outline">Artikel Unggulan</p>
          <a href={featured.href}>
            <h2 id="featured-heading">{featured.title}</h2>
          </a>
          <div class="info-feature-rule" aria-hidden="true"></div>
          <div class="taling-meta-line">
            <span>{featured.dateLabel || "-"}</span>
            <span>{featured.author}</span>
          </div>
          <p>{featured.excerpt}</p>
          {#if featured.categories?.length}
            <div class="info-chip-row">
              {#each featured.categories as category (category)}
                <span class="taling-chip">{category}</span>
              {/each}
            </div>
          {/if}
          <a href={featured.href} class="taling-section-link">
            Baca artikel
            <i class="fas fa-arrow-right text-[16px]" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </section>
  {/if}

  {#if !featured && !articles.length}
    <section class="info-empty">
      <div class="taling-section-shell">
        <div class="taling-empty-bright">
          <h2>Arsip publik belum terisi.</h2>
          <p>Belum ada publikasi. Buka beranda untuk konteks kabinet.</p>
          <a href={homeUrl} class="taling-section-link">Kembali ke beranda</a>
        </div>
      </div>
    </section>
  {:else if cardArticles.length}
    <section class="info-strip" aria-labelledby="article-strip-heading">
      <div class="taling-section-shell info-strip-heading">
        <h2 id="article-strip-heading">Kabar lainnya</h2>
        <p>Publikasi lanjutan dari papan informasi HIMATEKKOM ITS.</p>
      </div>

      <div class="info-card-strip">
        {#each cardArticles as article, index (article.href)}
          <a href={article.href} class="info-card">
            <div class="info-card-media">
              {#if article.coverImage}
                <OptimizedImage
                  src={article.coverImage}
                  alt={article.title}
                  class="info-card-img"
                  loading={index < 2 ? "eager" : "lazy"}
                  decoding="async"
                  sizes="(min-width: 900px) 313px, 78vw"
                  onerror={handleImageError}
                />
              {:else}
                <div class="info-card-fallback" aria-hidden="true">
                  {article.title?.slice(0, 2) || "HI"}
                </div>
              {/if}
            </div>
            <div class="info-card-copy">
              <span>{article.dateLabel || "Publikasi"}</span>
              <strong>{article.title}</strong>
              <p>{article.excerpt}</p>
            </div>
          </a>
        {/each}
      </div>
    </section>
  {/if}

  {#if pagination && pagination.total > 0}
    <section class="info-pagination">
      <div class="taling-section-shell info-pagination-inner">
        <p>
          Menampilkan {pagination.from} - {pagination.to} dari {pagination.total}
          publikasi.
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
  .info-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    padding: 6.5rem 0 8rem;
    color: var(--taling-white);
  }

  :global(.taling-page-title) {
    font-family: "The Seasons", "The Seasons", Georgia, serif !important;
    font-weight: 300 !important;
    text-shadow: 0px 0px 20px #ffffff;
  }

  .info-hero-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(220px, 320px);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: end;
  }

  .info-hero-copy {
    display: grid;
    gap: 1.5rem;
    max-width: 820px;
  }

  .info-hero h1 {
    color: var(--taling-white);
  }

  .hero-glow-wrapper {
    position: relative;
  }

  .info-stat-board {
    display: grid;
    border-top: 2px solid rgba(255, 253, 248, 0.78);
    border-bottom: 2px solid rgba(255, 253, 248, 0.78);
  }

  .info-stat-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    padding: 1rem 0;
    border-top: 1px solid rgba(255, 253, 248, 0.3);
    font-weight: 800;
  }

  .info-stat-row:first-child {
    border-top: 0;
  }

  .info-stat-row strong {
    color: var(--taling-yellow);
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
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

  .info-search {
    padding: 3rem 0;
    background: #ffffff;
  }

  .info-search-shell {
    display: grid;
    gap: 1.15rem;
    border-bottom: 2px solid
      color-mix(in srgb, var(--taling-ink) 12%, transparent);
    padding-bottom: 3rem;
  }

  .info-filter-form {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.8fr) auto;
    align-items: end;
  }

  .info-filter-form label {
    display: grid;
    gap: 0.5rem;
    color: var(--taling-purple);
    font-weight: 700;
    font-size: 0.88rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }

  .info-filter-form input,
  .info-filter-form select {
    height: 3rem;
    border: 1px solid rgba(42, 0, 120, 0.2);
    border-radius: 12px;
    background: #fbfbfd;
    color: var(--taling-ink);
    padding: 0 1.2rem;
    font: inherit;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 200ms ease;
  }

  .info-filter-form input:focus,
  .info-filter-form select:focus {
    outline: none;
    border-color: var(--taling-purple);
    box-shadow: 0 0 0 3px rgba(42, 0, 120, 0.12);
    background: #ffffff;
  }

  .info-filter-form button,
  .info-pagination a {
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
  }

  .info-search-note {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: color-mix(in srgb, var(--taling-ink) 72%, transparent);
    font-weight: 800;
    line-height: 1.5;
  }

  .info-search-note p {
    margin: 0;
  }

  .info-search-note a {
    color: var(--taling-purple);
    font-weight: 900;
    text-decoration: none;
  }

  .info-feature {
    padding: 6.5rem 0 7rem;
    background: linear-gradient(135deg, #2a0078 0%, #1e0055 100%);
    color: #ffffff;
    position: relative;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  .info-feature-grid {
    display: grid;
    grid-template-columns: minmax(300px, 1.05fr) minmax(300px, 0.95fr);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .info-feature-media {
    display: block;
    overflow: hidden;
    border: 4px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    background: var(--taling-purple);
    transition:
      transform 300ms ease,
      border-color 300ms ease;
  }

  .info-feature-media:hover {
    transform: scale(1.015);
    border-color: rgba(255, 255, 255, 0.3);
  }

  .info-feature-media :global(.info-feature-img),
  .info-feature-media :global(img),
  .info-feature-fallback {
    width: 100%;
    height: min(58vw, 520px);
    min-height: 340px;
    object-fit: cover;
  }

  .info-feature-fallback {
    display: grid;
    place-items: center;
    color: var(--taling-yellow);
    font-family: var(--taling-font-serif);
    font-size: 7rem;
  }

  .info-feature-copy {
    display: grid;
    gap: 1.2rem;
  }

  .info-outline {
    margin: 0;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(3rem, 7vw, 6.6rem);
    font-weight: 900;
    letter-spacing: 0.05em;
    line-height: 0.9;
    -webkit-text-stroke: 2px var(--taling-purple);
  }

  .info-feature-copy a {
    color: inherit;
    text-decoration: none;
  }

  .info-feature h2 {
    margin: 0;
    color: #ffffff;
    font-family: var(--taling-font-serif);
    font-size: clamp(2.4rem, 5vw, 4.4rem);
    line-height: 1;
    text-shadow: 0 0 12px rgba(255, 255, 255, 0.25);
  }

  .info-feature-rule {
    display: none;
  }

  .info-feature-copy p:not(.info-outline) {
    margin: 0;
    max-width: 62ch;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
    line-height: 1.48;
  }

  .info-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .info-feature :global(.taling-section-link) {
    width: fit-content;
    background: var(--taling-purple);
    color: var(--taling-white);
  }

  .info-strip {
    position: relative;
    overflow: hidden;
    padding: 6rem 0 7rem;
    background: linear-gradient(180deg, #18072e 0%, #12051f 100%);
    color: var(--taling-white);
  }

  .info-strip-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 4rem;
    border-bottom: 2px solid rgba(255, 253, 248, 0.78);
    padding-bottom: 2rem;
  }

  .info-strip h2 {
    margin: 0;
    color: var(--taling-white);
    font-family: var(--taling-font-serif);
    font-size: clamp(3rem, 6.8vw, 6rem);
    line-height: 1;
  }

  .info-strip-heading p {
    max-width: 34ch;
    margin: 0;
    color: color-mix(in srgb, var(--taling-white) 74%, transparent);
    font-weight: 800;
    line-height: 1.45;
  }

  .info-card-strip {
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

  .info-card-strip::-webkit-scrollbar {
    display: none;
  }

  .info-card {
    position: relative;
    display: block;
    height: 420px;
    overflow: hidden;
    color: var(--taling-white);
    text-decoration: none;
    scroll-snap-align: center;
    background: var(--taling-purple);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition:
      transform 300ms ease,
      border-color 300ms ease;
  }

  .info-card:hover {
    transform: translateY(-4px);
    border-color: var(--taling-orange);
  }

  .info-card-media,
  .info-card-media :global(.info-card-img),
  .info-card-media :global(img),
  .info-card-fallback {
    width: 100%;
    height: 100%;
  }

  .info-card-media :global(img) {
    object-fit: cover;
  }

  .info-card-fallback {
    display: grid;
    place-items: center;
    background: linear-gradient(
      145deg,
      rgba(42, 0, 120, 0.82),
      rgba(255, 122, 26, 0.72)
    );
    color: var(--taling-yellow);
    font-family: var(--taling-font-serif);
    font-size: 5rem;
  }

  .info-card-copy {
    position: absolute;
    inset: auto 0 0;
    display: grid;
    gap: 0.45rem;
    padding: 1rem;
    background: linear-gradient(180deg, transparent, rgba(18, 5, 31, 0.9));
  }

  .info-card-copy span {
    width: fit-content;
    border: 1px solid color-mix(in srgb, var(--taling-yellow) 60%, transparent);
    background: rgba(18, 5, 31, 0.62);
    color: var(--taling-yellow);
    padding: 0.25rem 0.55rem;
    font-size: 0.75rem;
    font-weight: 900;
  }

  .info-card-copy strong {
    font-size: 1.2rem;
    line-height: 1.12;
  }

  .info-card-copy p {
    margin: 0;
    color: color-mix(in srgb, var(--taling-white) 78%, transparent);
    font-size: 0.9rem;
    line-height: 1.45;
  }

  .info-empty,
  .info-pagination {
    padding: 4rem 0;
    background: linear-gradient(135deg, #ffd344 0%, #ffb13a 32%, #ff7a1a 100%);
  }

  .info-empty h2,
  .info-empty p {
    margin: 0 0 1rem;
  }

  .info-pagination-inner {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-weight: 900;
  }

  .info-pagination-inner p {
    margin: 0;
  }

  .info-pagination-inner div {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  @media (max-width: 819px) {
    .info-hero,
    .info-feature,
    .info-strip {
      padding: 4.25rem 0 5rem;
    }

    .info-hero-grid,
    .info-feature-grid,
    .info-filter-form {
      grid-template-columns: 1fr;
    }

    .info-star-left {
      left: -88px;
      width: 255px;
    }

    .info-star-right {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }

    .info-strip-heading,
    .info-search-note,
    .info-pagination-inner {
      display: grid;
    }

    .info-card-strip {
      grid-auto-columns: minmax(236px, 78vw);
      margin-inline: -0.75rem;
      padding-inline: 0.75rem;
    }

    .info-card {
      height: 350px;
    }
  }
</style>
