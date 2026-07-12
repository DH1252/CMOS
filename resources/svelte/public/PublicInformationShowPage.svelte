<script>
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";

  let {
    homeUrl = "/",
    infoUrl = "/informasi",
    article = {
      title: "",
      date: "",
      author: "",
      coverImage: null,
      categories: [],
      contentHtml: "",
    },
    latestArticles = [],
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
  <title>{seo?.title || `${article.title} - HIMATEKKOM ITS`}</title>
  <meta
    name="description"
    content={seo?.description || article.excerpt || ""}
  />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  {#if seo?.image}
    <meta property="og:image" content={seo.image} />
    <meta name="twitter:image" content={seo.image} />
  {/if}
  <meta property="og:type" content={seo?.type || "article"} />
  <meta property="og:title" content={seo?.title || article.title} />
  <meta
    property="og:description"
    content={seo?.description || article.excerpt || ""}
  />
  <meta
    name="twitter:card"
    content={seo?.image ? "summary_large_image" : "summary"}
  />
  <meta name="twitter:title" content={seo?.title || article.title} />
  <meta
    name="twitter:description"
    content={seo?.description || article.excerpt || ""}
  />
  {#if seo?.jsonLd}
    {@html jsonLdScriptOpen + seo.jsonLd + jsonLdScriptClose}
  {/if}
</svelte:head>

<article class="public-info-show">
  <section class="info-show-hero" aria-labelledby="article-heading">
    <span class="info-show-star" aria-hidden="true"></span>
    <div class="taling-section-shell info-show-hero-grid">
      <div class="info-show-copy">
        <nav class="info-show-breadcrumb" aria-label="Breadcrumb">
          <a href={homeUrl}>Beranda</a>
          <span>/</span>
          <a href={infoUrl}>Kabar Terbaru</a>
        </nav>
        <p class="taling-page-kicker">Publikasi Organisasi</p>
        <h1 id="article-heading" class="taling-page-title">{article.title}</h1>
        <div class="info-show-rule" aria-hidden="true"></div>
        <div class="taling-meta-line">
          <span>{article.dateLabel || "-"}</span>
          <span>{article.author}</span>
        </div>
        {#if article.categories?.length}
          <div class="info-show-chips">
            {#each article.categories as category (category)}
              <span class="taling-chip">{category}</span>
            {/each}
          </div>
        {/if}
      </div>

      {#if article.coverImage}
        <figure class="info-show-cover">
          <OptimizedImage
            src={article.coverImage}
            alt={article.title}
            class="info-show-cover-img"
            loading="eager"
            decoding="async"
            fetchpriority="high"
            sizes="(min-width: 900px) 520px, 100vw"
            onerror={handleImageError}
          />
        </figure>
      {/if}
    </div>
  </section>

  <section class="info-show-body">
    <div class="taling-section-shell info-show-body-grid">
      <div class="info-show-content public-article-content">
        {@html article.contentHtml}
      </div>

      <aside class="info-show-aside">
        <div class="info-aside-block">
          <p class="info-aside-label">Arsip publik</p>
          <h2>Kabar HIMATEKKOM</h2>
          <p>Pengumuman dan dokumentasi resmi kabinet.</p>
          <a href={infoUrl}>
            Kembali ke arsip
            <i class="fas fa-arrow-right text-[16px]" aria-hidden="true"></i>
          </a>
        </div>

        <div class="info-aside-block info-aside-list">
          <p class="info-aside-label">Artikel lainnya</p>
          {#if !latestArticles.length}
            <p>Belum ada artikel lain.</p>
          {:else}
            {#each latestArticles as latest (latest.href)}
              <a href={latest.href}>
                <strong>{latest.title}</strong>
                <span>{latest.dateLabel || "-"}</span>
              </a>
            {/each}
          {/if}
        </div>
      </aside>
    </div>
  </section>
</article>

<style>
  .info-show-hero {
    position: relative;
    overflow: hidden;
    padding: 5.5rem 0 6.5rem;
    background:
      radial-gradient(
        circle at 18% 18%,
        rgba(255, 211, 68, 0.16),
        transparent 26rem
      ),
      linear-gradient(180deg, #18072e 0%, #12051f 100%);
    color: var(--taling-white);
  }

  .info-show-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: 0.15;
    background-image: linear-gradient(
      45deg,
      transparent 46%,
      rgba(255, 211, 68, 0.22) 47%,
      transparent 48%
    );
    background-size: 46px 46px;
  }

  .info-show-hero-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(300px, 0.72fr);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .info-show-copy {
    display: grid;
    gap: 1.35rem;
  }

  .info-show-hero h1 {
    color: var(--taling-white);
  }

  .info-show-rule {
    width: min(534px, 68vw);
    height: 18px;
    background: var(--taling-yellow);
    box-shadow: 0 0 24px rgba(255, 211, 68, 0.52);
  }

  .info-show-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    color: color-mix(in srgb, var(--taling-white) 70%, transparent);
    font-weight: 800;
  }

  .info-show-breadcrumb a {
    color: inherit;
    text-decoration: none;
  }

  .info-show-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .info-show-cover {
    margin: 0;
    overflow: hidden;
    border: 10px solid color-mix(in srgb, var(--taling-yellow) 68%, transparent);
    background: var(--taling-purple);
  }

  .info-show-cover :global(.info-show-cover-img),
  .info-show-cover :global(img) {
    width: 100%;
    height: min(54vw, 560px);
    min-height: 340px;
    object-fit: cover;
  }

  .info-show-star {
    position: absolute;
    right: -92px;
    bottom: 42px;
    z-index: 1;
    width: 250px;
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

  .info-show-body {
    padding: 6rem 0 7rem;
    background: #fffdf8;
  }

  .info-show-body-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
    gap: clamp(2.5rem, 6vw, 5rem);
    align-items: start;
  }

  .info-show-content {
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
    border-left: 0;
    background: var(--taling-cream);
    color: var(--taling-purple);
    font-weight: 900;
  }

  .public-article-content :global(img) {
    max-width: 100%;
    height: auto;
  }

  .info-show-aside {
    display: grid;
    gap: 2rem;
    position: sticky;
    top: 108px;
  }

  .info-aside-block {
    border-top: 8px solid var(--taling-purple);
    padding-top: 1rem;
  }

  .info-aside-label {
    margin: 0 0 0.85rem;
    color: var(--taling-orange);
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .info-aside-block h2 {
    margin: 0;
    color: var(--taling-purple);
    font-family: var(--taling-font-serif);
    font-size: 2rem;
    line-height: 1;
  }

  .info-aside-block p:not(.info-aside-label) {
    margin: 1rem 0 0;
    color: color-mix(in srgb, var(--taling-ink) 74%, transparent);
    font-weight: 800;
    line-height: 1.55;
  }

  .info-aside-block a {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1.15rem;
    color: var(--taling-purple);
    font-weight: 900;
    text-decoration: none;
  }

  .info-aside-list {
    display: grid;
    gap: 1rem;
  }

  .info-aside-list a {
    display: grid;
    gap: 0.35rem;
    margin: 0;
    border-top: 1px solid color-mix(in srgb, var(--taling-ink) 18%, transparent);
    padding-top: 1rem;
    color: var(--taling-ink);
  }

  .info-aside-list strong {
    color: var(--taling-purple);
    font-family: var(--taling-font-serif);
    font-size: 1.3rem;
    line-height: 1.05;
  }

  .info-aside-list span {
    color: color-mix(in srgb, var(--taling-ink) 62%, transparent);
    font-size: 0.9rem;
    font-weight: 800;
  }

  @media (max-width: 819px) {
    .info-show-hero,
    .info-show-body {
      padding: 4.25rem 0 5rem;
    }

    .info-show-hero-grid,
    .info-show-body-grid {
      grid-template-columns: 1fr;
    }

    .info-show-star {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }

    .info-show-aside {
      position: static;
    }
  }
</style>
