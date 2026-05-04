<script>
  import { ArrowRight } from 'lucide-svelte';
  import OptimizedImage from '../components/OptimizedImage.svelte';

  let {
    homeUrl = '/',
    infoUrl = '/informasi',
    article = {
      title: '',
      date: '',
      author: '',
      coverImage: null,
      categories: [],
      contentHtml: '',
    },
    latestArticles = [],
  } = $props();

  const fallbackImage = '/images/logokabinet.png';

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };

</script>

<article class="space-y-10">
  <nav class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
    <a href={homeUrl} class="transition-colors hover:text-foreground">Beranda</a>
    <span>/</span>
    <a href={infoUrl} class="transition-colors hover:text-foreground">Arsip Informasi</a>
    <span>/</span>
    <span class="text-foreground">{article.title}</span>
  </nav>

  <header class="max-w-[70ch] space-y-5 border-b border-border pb-8">
    <h1 class="text-4xl leading-tight text-foreground md:text-5xl">{article.title}</h1>

    <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
      <span>{article.dateLabel || '-'}</span>
      <span>{article.author}</span>
    </div>

    {#if article.categories?.length}
      <div class="flex flex-wrap gap-2">
        {#each article.categories as category (category)}
          <span class="rounded-[8px] bg-brand-secondary-soft px-2.5 py-1 text-xs font-medium text-foreground">{category}</span>
        {/each}
      </div>
    {/if}
  </header>

  {#if article.coverImage}
    <div class="overflow-hidden rounded-[10px] border border-border bg-card">
      <OptimizedImage src={article.coverImage} alt={article.title} class="max-h-[34rem] w-full object-cover" loading="eager" decoding="async" fetchpriority="high" sizes="(min-width: 1280px) 72rem, 100vw" onerror={handleImageError} />
    </div>
  {/if}

  <div class="grid gap-10 lg:grid-cols-[minmax(0,1.12fr)_18rem] lg:items-start">
    <section class="min-w-0">
      <div class="public-article-content max-w-[72ch] text-[1.02rem] leading-8 text-foreground">
        {@html article.contentHtml}
      </div>
    </section>

    <aside class="grid gap-6 lg:sticky lg:top-24">
      <div class="rounded-[10px] border border-border bg-card px-5 py-5">
        <div class="text-sm font-semibold text-foreground">Arsip publik</div>
        <p class="mt-3 text-sm leading-7 text-muted-foreground">Pengumuman dan dokumentasi resmi.</p>
        <a href={infoUrl} class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-foreground transition-colors hover:text-brand-secondary">
          Kembali ke arsip
          <ArrowRight size={16} />
        </a>
      </div>

      <div class="rounded-[10px] border border-border bg-card px-5 py-5">
        <h2 class="text-lg text-foreground">Artikel lainnya</h2>

        {#if !latestArticles.length}
          <p class="mt-3 text-sm leading-7 text-muted-foreground">Belum ada artikel lain.</p>
        {:else}
          <div class="mt-4 grid gap-4">
            {#each latestArticles as latest (latest.href)}
              <a href={latest.href} class="border-t border-border pt-4 text-inherit no-underline transition-colors hover:text-brand-secondary first:border-t-0 first:pt-0">
                <strong class="block text-base leading-7 text-foreground">{latest.title}</strong>
                <span class="mt-1 block text-sm text-muted-foreground">{latest.dateLabel || '-'}</span>
              </a>
            {/each}
          </div>
        {/if}
      </div>
    </aside>
  </div>
</article>

<style>
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
    font-family: var(--font-display);
    line-height: 1.2;
    color: var(--text-strong);
  }

  .public-article-content :global(a) {
    color: var(--brand-secondary);
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
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: color-mix(in srgb, var(--brand-secondary-soft) 46%, white);
    color: var(--text-soft);
  }

  .public-article-content :global(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
  }
</style>
