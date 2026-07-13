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

  // Client-side search and filtering states
  let searchQuery = $state("");
  let selectedLocation = $state("All");

  const filteredEvents = $derived(
    events.filter((event) => {
      const matchesSearch =
        event.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (event.location &&
          event.location.toLowerCase().includes(searchQuery.toLowerCase()));
      const matchesLocation =
        selectedLocation === "All" ||
        (event.location && event.location.includes(selectedLocation));
      return matchesSearch && matchesLocation;
    }),
  );

  const uniqueLocations = $derived([
    "All",
    ...new Set(events.map((e) => e.location).filter(Boolean)),
  ]);
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
  <!-- Hero Section -->
  <section class="event-hero" aria-labelledby="event-index-heading">
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

    <div class="taling-section-shell event-hero-copy">
      <p class="taling-page-kicker">{kicker}</p>
      <h1 id="event-index-heading" class="taling-page-title">{title}</h1>

      <!-- Glowing Gradient Bar matching Departemen -->
      <div
        class="hero-glow-wrapper w-full max-w-[300px] md:max-w-[534px] h-[22px] mx-auto -mt-2"
      >
        <div
          class="h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[1px]"
        ></div>
      </div>

      <p class="taling-page-copy">{description}</p>
    </div>
  </section>

  <!-- Empty State (No Events Registered at all) -->
  {#if !events.length}
    <section class="event-empty">
      <div class="taling-section-shell">
        <div class="taling-empty-bright">
          <i
            class="far fa-calendar-alt text-6xl text-[#ff7a1a]/80 mb-6"
            aria-hidden="true"
          ></i>
          <h2>Belum ada acara mendatang.</h2>
          <p>{emptyText}</p>
          <a href={homeUrl} class="taling-section-link">Kembali ke beranda</a>
        </div>
      </div>
    </section>
  {:else}
    <!-- Interactive Client-side Filter Section -->
    <section class="event-search" aria-label="Filter agenda">
      <div class="taling-section-shell event-search-shell">
        <div class="event-filter-form">
          <label>
            <span>Cari acara</span>
            <input
              type="text"
              placeholder="Ketik judul acara atau tempat..."
              bind:value={searchQuery}
            />
          </label>

          <label>
            <span>Tempat Pelaksanaan</span>
            <select bind:value={selectedLocation}>
              <option value="All">Semua Tempat</option>
              {#each uniqueLocations as location}
                {#if location !== "All"}
                  <option value={location}>{location}</option>
                {/if}
              {/each}
            </select>
          </label>
        </div>

        {#if searchQuery || selectedLocation !== "All"}
          <div class="event-search-note">
            <p>Ditemukan {filteredEvents.length} acara yang sesuai filter.</p>
            <button
              type="button"
              onclick={() => {
                searchQuery = "";
                selectedLocation = "All";
              }}
            >
              Hapus filter pencarian
            </button>
          </div>
        {/if}
      </div>
    </section>

    <!-- Featured Event Section (Only if no active filter query, to act as static highlight) -->
    {#if filteredEvents.length > 0 && !searchQuery && selectedLocation === "All"}
      {@const featured = filteredEvents[0]}
      <section class="event-feature" aria-labelledby="featured-heading">
        <div class="taling-section-shell event-feature-grid">
          <a href={featured.href} class="event-feature-media">
            {#if featured.poster}
              <OptimizedImage
                src={featured.poster}
                alt={featured.title}
                class="event-feature-img"
                loading="eager"
                decoding="async"
                fetchpriority="high"
                sizes="(min-width: 900px) 620px, 100vw"
                onerror={handleImageError}
              />
            {:else}
              <div class="event-feature-fallback" aria-hidden="true">
                <i class="far fa-calendar-alt" style="font-size: 5rem;"></i>
              </div>
            {/if}
          </a>

          <div class="event-feature-copy">
            <span class="event-feature-badge">Acara Utama</span>
            <h2 id="featured-heading" class="event-feature-title">
              <a href={featured.href}>{featured.title}</a>
            </h2>
            <div class="event-feature-meta">
              <div class="event-meta-item">
                <i class="far fa-calendar-alt" aria-hidden="true"></i>
                <span>{featured.dateLabel || "Segera"}</span>
              </div>
              {#if featured.location}
                <div class="event-meta-item">
                  <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                  <span>{featured.location}</span>
                </div>
              {/if}
            </div>
            <p class="event-feature-excerpt">
              Bergabunglah dalam agenda unggulan kami! Dapatkan pengalaman baru,
              wawasan berharga, serta kesempatan berjejaring dengan rekan-rekan
              mahasiswa Teknik Komputer.
            </p>
            <a href={featured.href} class="taling-section-link"
              >Lihat Detail Acara</a
            >
          </div>
        </div>
      </section>
    {/if}

    <!-- Event Cards Grid -->
    {@const displayEvents =
      searchQuery || selectedLocation !== "All"
        ? filteredEvents
        : filteredEvents.slice(1)}

    {#if displayEvents.length > 0}
      <section class="event-posters" aria-label="Daftar acara">
        <div class="taling-section-shell event-poster-grid">
          {#each displayEvents as event, index (event.href)}
            <a href={event.href} class="event-card">
              <div class="event-card-poster">
                {#if event.poster}
                  <OptimizedImage
                    src={event.poster}
                    alt={event.title}
                    class="event-card-img"
                    loading="lazy"
                    decoding="async"
                    sizes="(min-width: 1000px) 310px, 78vw"
                    onerror={handleImageError}
                  />
                {:else}
                  <div class="event-card-fallback" aria-hidden="true">
                    <span>{event.dateLabel || "Segera"}</span>
                    <strong>{event.title}</strong>
                  </div>
                {/if}
              </div>
              <div class="event-card-copy">
                <div class="event-card-date">
                  <i class="far fa-calendar-alt text-xs mr-1" aria-hidden="true"
                  ></i>
                  <span>{event.dateLabel || "Segera"}</span>
                </div>
                <h2 class="event-card-title">{event.title}</h2>
                {#if event.location}
                  <p class="event-card-location">
                    <i
                      class="fas fa-map-marker-alt text-xs mr-1"
                      aria-hidden="true"
                    ></i>
                    <span>{event.location}</span>
                  </p>
                {/if}
              </div>
            </a>
          {/each}
        </div>
      </section>
    {:else if filteredEvents.length === 0}
      <section class="event-empty">
        <div class="taling-section-shell">
          <div class="taling-empty-bright">
            <i
              class="far fa-calendar-times text-6xl text-gray-300 mb-4"
              aria-hidden="true"
            ></i>
            <h2>Acara tidak ditemukan.</h2>
            <p>Tidak ada agenda acara yang sesuai dengan pencarian Anda.</p>
            <button
              type="button"
              onclick={() => {
                searchQuery = "";
                selectedLocation = "All";
              }}
              class="taling-section-link"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </section>
    {/if}
  {/if}

  <!-- Pagination section (Only show if no active local filters, to maintain consistency with backend pages) -->
  {#if pagination && pagination.total > 0 && !searchQuery && selectedLocation === "All"}
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

  .event-hero-copy {
    position: relative;
    z-index: 2;
    display: grid;
    justify-items: center;
    gap: 1.5rem;
    text-align: center;
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

  .event-search {
    padding: 3rem 0;
    background: #ffffff;
  }

  .event-search-shell {
    display: grid;
    gap: 1.25rem;
    border-bottom: 2px solid
      color-mix(in srgb, var(--taling-ink) 12%, transparent);
    padding-bottom: 3rem;
  }

  .event-filter-form {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.8fr);
    align-items: end;
  }

  .event-filter-form label {
    display: grid;
    gap: 0.5rem;
    color: var(--taling-purple);
    font-weight: 700;
    font-size: 0.88rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }

  .event-filter-form input,
  .event-filter-form select {
    height: 3.2rem;
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

  .event-filter-form input:focus,
  .event-filter-form select:focus {
    outline: none;
    border-color: var(--taling-purple);
    box-shadow: 0 0 0 3px rgba(42, 0, 120, 0.12);
    background: #ffffff;
  }

  .event-search-note {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: color-mix(in srgb, var(--taling-ink) 72%, transparent);
    font-weight: 800;
    line-height: 1.5;
    align-items: center;
  }

  .event-search-note p {
    margin: 0;
  }

  .event-search-note button {
    color: var(--taling-purple);
    font-weight: 900;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font: inherit;
    transition: color 150ms ease;
  }

  .event-search-note button:hover {
    color: var(--taling-orange);
  }

  .event-feature {
    padding: 6.5rem 0 7rem;
    background: linear-gradient(135deg, #2a0078 0%, #1e0055 100%);
    color: #ffffff;
    position: relative;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  .event-feature-grid {
    display: grid;
    grid-template-columns: minmax(300px, 1.05fr) minmax(300px, 0.95fr);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .event-feature-media {
    display: block;
    overflow: hidden;
    border: 4px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    background: var(--taling-purple);
    transition:
      transform 300ms ease,
      border-color 300ms ease;
  }

  .event-feature-media:hover {
    transform: scale(1.015);
    border-color: rgba(255, 255, 255, 0.3);
  }

  .event-feature-media :global(.event-feature-img),
  .event-feature-media :global(img),
  .event-feature-fallback {
    width: 100%;
    height: min(58vw, 490px);
    min-height: 340px;
    object-fit: cover;
  }

  .event-feature-fallback {
    display: grid;
    place-items: center;
    color: var(--taling-yellow);
    background: var(--taling-purple);
  }

  .event-feature-copy {
    display: grid;
    gap: 1.3rem;
  }

  .event-feature-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    height: auto;
    min-height: 28px;
    padding: 0.25rem 1.1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: var(--taling-yellow);
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .event-feature-title {
    margin: 0;
    color: #ffffff;
    font-family: var(--taling-font-serif);
    font-size: clamp(2.4rem, 5vw, 4.4rem);
    line-height: 1.05;
    text-shadow: 0 0 12px rgba(255, 255, 255, 0.25);
  }

  .event-feature-title a {
    color: inherit;
    text-decoration: none;
    transition: opacity 150ms ease;
  }

  .event-feature-title a:hover {
    opacity: 0.85;
  }

  .event-feature-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem 1.5rem;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.9);
  }

  .event-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .event-feature-excerpt {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 500;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.8);
  }

  .event-posters {
    padding: 6.5rem 0 8rem;
    background: #ffffff;
    color: var(--taling-ink);
  }

  .event-poster-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.5rem, 3.5vw, 2.75rem);
  }

  .event-card {
    display: grid;
    gap: 1.25rem;
    color: inherit;
    text-decoration: none;
    transition: transform 300ms ease;
  }

  .event-card:hover {
    transform: translateY(-6px);
  }

  .event-card-poster {
    height: 390px;
    overflow: hidden;
    border: 6px solid var(--taling-purple);
    border-radius: 12px;
    background: var(--taling-purple);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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
    padding: 1.5rem;
    background: linear-gradient(
      160deg,
      rgba(255, 211, 68, 0.96),
      rgba(255, 122, 26, 0.92)
    );
    color: var(--taling-purple);
    text-align: center;
  }

  .event-card-fallback span {
    font-weight: 900;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .event-card-fallback strong {
    font-family: var(--taling-font-serif);
    font-size: 1.85rem;
    line-height: 1.1;
  }

  .event-card-copy {
    display: grid;
    gap: 0.6rem;
  }

  .event-card-date {
    font-weight: 900;
    color: var(--taling-orange);
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.9rem;
  }

  .event-card-title {
    margin: 0;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(1.6rem, 3.2vw, 2.4rem);
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 0.95;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
    transition: color 250ms ease;
  }

  .event-card:hover .event-card-title {
    color: var(--taling-purple);
  }

  .event-card-location {
    margin: 0;
    color: color-mix(in srgb, var(--taling-ink) 72%, transparent);
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.95rem;
  }

  .event-empty {
    padding: 8rem 0;
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
    align-items: center;
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
    padding: 0.65rem 1.35rem;
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
    .event-feature-grid {
      grid-template-columns: 1fr;
      gap: 3rem;
    }

    .event-feature-media :global(.event-feature-img),
    .event-feature-media :global(img) {
      height: 380px;
    }

    .event-poster-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 819px) {
    .event-hero {
      padding: 4.5rem 0 5rem;
    }

    .event-filter-form {
      grid-template-columns: 1fr;
      gap: 1.25rem;
    }

    .event-posters {
      padding: 4.5rem 0 5.5rem;
    }

    .event-poster-grid {
      grid-template-columns: 1fr;
    }

    .event-card-poster {
      height: min(120vw, 420px);
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
      gap: 1.5rem;
      justify-items: start;
    }
  }
</style>
