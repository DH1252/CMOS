<script>
  import { ArrowRight } from 'lucide-svelte';

  let {
    homeUrl = '/',
    kicker = 'Publikasi Organisasi',
    headline = 'Artikel, pembaruan, dan dokumentasi resmi.',
    description = '',
    stats = [],
    filters = {
      action: '#',
      query: '',
      category: '',
      categories: [],
    },
    featured = null,
    articles = [],
    pagination = null,
  } = $props();

  const hasActiveFilters = $derived(Boolean(filters.query || filters.category));
  const fallbackImage = '/images/logokabinet.png';
  const archiveSummary = $derived.by(() => {
    if (!stats.length) {
      return '';
    }

    const articleCount = Number(stats.find((stat) => stat.label === 'Artikel Terbit')?.value || 0);
    const categoryCount = Number(stats.find((stat) => stat.label === 'Kategori')?.value || 0);

    if (articleCount <= 0) {
      return '';
    }

    if (!categoryCount) {
      return '';
    }

    if (articleCount && categoryCount) {
      return `${articleCount} publikasi tercatat dalam ${categoryCount} kategori arsip.`;
    }

    return `${articleCount} data arsip tercatat saat ini.`;
  });

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };

</script>

<section class="space-y-10">
  <header class="grid gap-6 border-b border-border pb-8 lg:grid-cols-[minmax(0,1.2fr)_18rem] lg:items-end">
    <div class="max-w-[65ch] space-y-4">
      <p class="text-sm font-medium text-brand-secondary">{kicker}</p>
      <h1 class="max-w-[18ch] text-4xl leading-tight text-foreground md:text-5xl">{headline}</h1>
      <p class="max-w-[58ch] text-base leading-8 text-[var(--text-soft)]">{description}</p>
    </div>

    {#if archiveSummary}
      <div class="border-t border-border pt-4 text-sm leading-7 text-muted-foreground lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
        {archiveSummary}
      </div>
    {/if}
  </header>

  <section class="rounded-[10px] border border-border bg-card px-5 py-5">
    <form method="GET" action={filters.action} class="grid gap-3 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_auto] lg:items-end">
      <label class="grid gap-2 text-sm text-muted-foreground">
        Cari arsip
        <input
          type="text"
          name="q"
          class="h-11 rounded-[10px] border border-input bg-background px-3 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground focus:border-brand-secondary"
          placeholder="Judul, ringkasan, atau kata kunci"
          value={filters.query || ''}
        />
      </label>

      <label class="grid gap-2 text-sm text-muted-foreground">
        Kategori
        <select name="kategori" class="h-11 rounded-[10px] border border-input bg-background px-3 text-sm text-foreground outline-none transition-colors focus:border-brand-secondary">
          <option value="">Semua kategori</option>
          {#each filters.categories || [] as category (category.value)}
            <option value={category.value} selected={String(filters.category || '') === String(category.value)}>
              {category.label}
            </option>
          {/each}
        </select>
      </label>

      <button type="submit" class="inline-flex h-11 items-center justify-center rounded-[10px] bg-brand-primary px-4 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
        Terapkan filter
      </button>
    </form>

    <div class="mt-4 flex flex-col gap-3 border-t border-border pt-4 text-sm leading-7 text-muted-foreground md:flex-row md:items-center md:justify-between">
      {#if hasActiveFilters}
        <p>Hasil filter aktif.</p>
        <a href={filters.action} class="font-medium text-foreground underline decoration-border underline-offset-4 transition-colors hover:text-brand-secondary">Hapus filter</a>
      {:else}
        <p>Gunakan pencarian atau kategori.</p>
      {/if}
    </div>
  </section>

  {#if featured}
    <section class="grid gap-6 border-b border-border pb-10 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:items-start">
      {#if featured.coverImage}
        <a href={featured.href} class="block overflow-hidden rounded-[10px] border border-border bg-card">
          <img src={featured.coverImage} alt={featured.title} class="h-full min-h-56 w-full object-cover" loading="eager" decoding="async" fetchpriority="high" sizes="(min-width: 1024px) 12rem, 100vw" onerror={handleImageError} />
        </a>
      {:else}
        <div class="flex min-h-72 items-center justify-center rounded-[10px] border border-border bg-card px-6 text-sm text-muted-foreground">Tanpa sampul</div>
      {/if}

      <div class="space-y-4">
        <div class="text-sm font-medium text-brand-primary">Artikel unggulan</div>
        <a href={featured.href} class="block text-inherit no-underline">
          <h2 class="max-w-[16ch] text-4xl leading-tight text-foreground md:text-5xl">{featured.title}</h2>
        </a>
        <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
          <span>{featured.dateLabel || '-'}</span>
          <span>{featured.author}</span>
        </div>
        <p class="max-w-[60ch] text-sm leading-8 text-[var(--text-soft)]">{featured.excerpt}</p>
        {#if featured.categories?.length}
          <div class="flex flex-wrap gap-2 border-t border-border pt-4">
            {#each featured.categories as category (category)}
              <span class="rounded-[8px] bg-brand-secondary-soft px-2.5 py-1 text-xs font-medium text-foreground">{category}</span>
            {/each}
          </div>
        {/if}
      </div>
    </section>
  {/if}

  {#if !featured && !articles.length}
    <section class="grid gap-8 rounded-[10px] border border-border bg-card px-6 py-8 lg:grid-cols-[minmax(0,1.1fr)_18rem] lg:items-start">
      <div class="space-y-4">
        <h2 class="max-w-[18ch] text-3xl leading-tight text-foreground md:text-4xl">Arsip publik belum terisi.</h2>
        <p class="max-w-[58ch] text-sm leading-8 text-muted-foreground">Belum ada publikasi.</p>
      </div>

      <div class="border-t border-border pt-4 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
        <div class="text-sm font-semibold text-foreground">Sementara itu</div>
        <div class="mt-3 grid gap-3 text-sm leading-7 text-muted-foreground">
          <div>Buka beranda untuk konteks kabinet.</div>
          <div>Kembali nanti untuk publikasi berikutnya.</div>
        </div>
        <a href={homeUrl} class="mt-5 inline-flex items-center gap-2 rounded-[10px] border border-border bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
          Kembali ke beranda
          <ArrowRight size={16} />
        </a>
      </div>
    </section>
  {:else if articles.length}
    <section class="space-y-5">
      {#each articles as article (article.href)}
        <a href={article.href} class="grid gap-4 border-t border-border pt-5 text-inherit no-underline transition-colors hover:text-brand-secondary md:grid-cols-[12rem_minmax(0,1fr)] first:border-t-0 first:pt-0">
          {#if article.coverImage}
            <img src={article.coverImage} alt={article.title} class="h-24 w-24 rounded-[10px] border border-border object-cover" loading="lazy" decoding="async" sizes="96px" onerror={handleImageError} />
          {:else}
            <div class="flex h-40 items-center justify-center rounded-[10px] border border-border bg-card px-5 text-sm text-muted-foreground">
              Tanpa sampul
            </div>
          {/if}

          <div class="min-w-0 space-y-3">
            <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
              <span>{article.dateLabel || '-'}</span>
              <span>{article.author}</span>
            </div>
            <h3 class="max-w-[22ch] text-3xl leading-tight text-foreground md:text-[2rem]">{article.title}</h3>
            <p class="max-w-[62ch] text-sm leading-8 text-[var(--text-soft)]">{article.excerpt}</p>
            {#if article.categories?.length}
              <div class="flex flex-wrap gap-2 pt-1">
                {#each article.categories as category (category)}
                  <span class="rounded-[8px] bg-brand-secondary-soft px-2.5 py-1 text-xs font-medium text-foreground">{category}</span>
                {/each}
              </div>
            {/if}
          </div>
        </a>
      {/each}
    </section>
  {/if}

  {#if pagination && pagination.total > 0}
    <section class="flex flex-col gap-4 border-t border-border pt-6 md:flex-row md:items-center md:justify-between">
      <div class="text-sm leading-7 text-muted-foreground">
        Menampilkan {pagination.from} - {pagination.to} dari {pagination.total} publikasi.
      </div>

      <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="rounded-[8px] border border-border bg-card px-2.5 py-1 text-muted-foreground">
          Halaman {pagination.currentPage} / {pagination.lastPage}
        </span>
        {#if pagination.prevUrl}
          <a href={pagination.prevUrl} class="rounded-[8px] border border-border bg-card px-3 py-2 text-foreground transition-colors hover:bg-muted">Sebelumnya</a>
        {/if}
        {#if pagination.nextUrl}
          <a href={pagination.nextUrl} class="rounded-[8px] border border-border bg-card px-3 py-2 text-foreground transition-colors hover:bg-muted">Selanjutnya</a>
        {/if}
      </div>
    </section>
  {/if}
</section>
