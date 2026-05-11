<script>
  import { ArrowRight } from "lucide-svelte";
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import TerminalTextReveal from "../components/TerminalTextReveal.svelte";

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

<article class="space-y-8">
  <nav
    class="flex flex-wrap items-center gap-2 text-sm text-[var(--landing-terminal-soft-resolved)]"
  >
    <a
      href={homeUrl}
      class="landing-inline-link transition-colors hover:text-[var(--landing-terminal-text-resolved)]"
      >Beranda</a
    >
    <span>/</span>
    <a
      href={infoUrl}
      class="landing-inline-link transition-colors hover:text-[var(--landing-terminal-text-resolved)]"
      >Arsip Informasi</a
    >
    <span>/</span>
    <span class="text-[var(--landing-terminal-heading-resolved)]"
      >{article.title}</span
    >
  </nav>

  <header
    class="max-w-[70ch] space-y-4 border-b border-[var(--landing-terminal-line-resolved)] pb-6"
  >
    <TerminalTextReveal
      animate={false}
      tag="h1"
      text={article.title}
      textClass="text-4xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-5xl"
    />

    <div
      class="flex flex-wrap gap-3 text-sm text-[var(--landing-terminal-muted-resolved)]"
    >
      <span>{article.dateLabel || "-"}</span>
      <span>{article.author}</span>
    </div>

    {#if article.categories?.length}
      <div class="flex flex-wrap gap-2">
        {#each article.categories as category (category)}
          <span
            class="rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] px-2.5 py-1 text-xs font-medium text-[var(--landing-terminal-text-resolved)]"
            >{category}</span
          >
        {/each}
      </div>
    {/if}
  </header>

  {#if article.coverImage}
    <div class="landing-frame overflow-hidden">
      <div class="landing-frame__media">
        <OptimizedImage
          src={article.coverImage}
          alt={article.title}
          class="w-full"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="(min-width: 1280px) 72rem, 100vw"
          onerror={handleImageError}
        />
      </div>
    </div>
  {/if}

  <div class="grid gap-8 lg:grid-cols-[minmax(0,1.12fr)_18rem] lg:items-start">
    <section class="min-w-0">
      <div
        class="public-article-content public-article-content-shell max-w-[72ch] px-5 py-5 text-[1.02rem] leading-8 text-[var(--landing-terminal-text-resolved)]"
      >
        {@html article.contentHtml}
      </div>
    </section>

    <aside class="grid gap-6 lg:sticky lg:top-24">
      <div class="landing-panel px-5 py-5">
        <div
          class="text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
        >
          Arsip publik
        </div>
        <p
          class="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
        >
          Pengumuman dan dokumentasi resmi.
        </p>
        <a
          href={infoUrl}
          class="landing-inline-link mt-5 inline-flex items-center gap-2 text-sm font-medium text-[var(--landing-terminal-interactive-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"
        >
          Kembali ke arsip
          <ArrowRight size={16} />
        </a>
      </div>

      <div class="landing-panel px-5 py-5">
        <h2 class="text-lg text-[var(--landing-terminal-heading-resolved)]">
          Artikel lainnya
        </h2>

        {#if !latestArticles.length}
          <p
            class="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
          >
            Belum ada artikel lain.
          </p>
        {:else}
          <div class="mt-4 grid gap-4">
            {#each latestArticles as latest (latest.href)}
              <a
                href={latest.href}
                class="landing-article-row border-t border-[var(--landing-terminal-line-resolved)] pt-4 text-inherit no-underline first:border-t-0 first:pt-0 hover:text-[var(--landing-terminal-interactive-resolved)]"
              >
                <strong
                  class="block text-base leading-7 text-[var(--landing-terminal-heading-resolved)]"
                  >{latest.title}</strong
                >
                <span
                  class="mt-1 block text-sm text-[var(--landing-terminal-muted-resolved)]"
                  >{latest.dateLabel || "-"}</span
                >
              </a>
            {/each}
          </div>
        {/if}
      </div>
    </aside>
  </div>
</article>

<style>
  .public-article-content-shell {
    border: 1px solid var(--landing-terminal-line-resolved);
    background: var(--landing-terminal-bg-resolved);
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
    margin-bottom: 1.2rem;
  }

  .public-article-content :global(h2),
  .public-article-content :global(h3),
  .public-article-content :global(h4) {
    color: var(--landing-terminal-heading-resolved);
    font-family: var(
      --font-terminal,
      ui-monospace,
      SFMono-Regular,
      Menlo,
      Monaco,
      Consolas,
      "Liberation Mono",
      "Courier New",
      monospace
    );
    line-height: 1.2;
  }

  .public-article-content :global(a) {
    color: var(--landing-terminal-interactive-resolved);
    text-decoration: underline;
    text-underline-offset: 0.2rem;
  }

  .public-article-content :global(ul),
  .public-article-content :global(ol) {
    padding-left: 1.1rem;
  }

  .public-article-content :global(blockquote) {
    margin-left: 0;
    padding: 1rem 1.1rem;
    border: 1px solid var(--landing-terminal-line-resolved);
    border-radius: 0.5rem;
    background: var(--landing-terminal-panel-soft-resolved);
    color: var(--landing-terminal-soft-resolved);
  }

  .public-article-content :global(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
  }
</style>
