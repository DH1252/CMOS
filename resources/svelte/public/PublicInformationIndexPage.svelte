<script>
  import { ArrowRight } from "lucide-svelte";
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import TerminalTextReveal from "../components/TerminalTextReveal.svelte";

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
    featured = null,
    articles = [],
    pagination = null,
	  } = $props();

	  const hasActiveFilters = $derived(Boolean(filters.query || filters.category));
	  const fallbackImage = fallbackImageAsset.original ?? fallbackImageAsset;
	  const archiveSummary = $derived.by(() => {
    if (!stats.length) {
      return "";
    }

    const articleCount = Number(
      stats.find((stat) => stat.label === "Artikel Terbit")?.value || 0,
    );
    const categoryCount = Number(
      stats.find((stat) => stat.label === "Kategori")?.value || 0,
    );

    if (!articleCount || !categoryCount) {
      return "";
    }

    return `${articleCount} publikasi tercatat dalam ${categoryCount} kategori arsip.`;
  });

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<section
  class="grid gap-8 border-b border-[var(--landing-terminal-line-resolved)] pb-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end"
>
  <div class="max-w-[65ch] space-y-4">
    <TerminalTextReveal
      animate={false}
      tag="p"
      text={kicker}
      textClass="text-sm font-semibold text-[var(--landing-terminal-command-resolved)]"
    />
    <TerminalTextReveal
      animate={false}
      tag="h1"
      text={headline}
      textClass="max-w-[18ch] text-4xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-5xl"
    />
    <TerminalTextReveal
      animate={false}
      tag="p"
      text={description}
      textClass="max-w-[66ch] text-[0.98rem] leading-7 text-[var(--landing-terminal-soft-resolved)]"
    />
  </div>

  {#if archiveSummary}
    <div
      class="landing-panel px-5 py-5 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
    >
      {archiveSummary}
    </div>
  {/if}
</section>

<section class="landing-panel mt-6 px-5 py-5">
  <form
    method="GET"
    action={filters.action}
    class="grid gap-3 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_auto] lg:items-end"
  >
    <label
      class="grid gap-2 text-sm text-[var(--landing-terminal-soft-resolved)]"
    >
      Cari arsip
      <input
        type="text"
        name="q"
        class="h-11 rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-bg)] px-3 text-sm text-[var(--landing-terminal-text-resolved)] transition-colors outline-none placeholder:text-[var(--landing-terminal-muted-resolved)] focus:border-[var(--landing-terminal-interactive-resolved)]"
        placeholder="Judul, ringkasan, atau kata kunci"
        value={filters.query || ""}
      />
    </label>

    <label
      class="grid gap-2 text-sm text-[var(--landing-terminal-soft-resolved)]"
    >
      Kategori
      <select
        name="kategori"
        class="h-11 rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-bg)] px-3 text-sm text-[var(--landing-terminal-text-resolved)] transition-colors outline-none focus:border-[var(--landing-terminal-interactive-resolved)]"
      >
        <option value="">Semua kategori</option>
        {#each filters.categories || [] as category (category.value)}
          <option
            value={category.value}
            selected={String(filters.category || "") === String(category.value)}
          >
            {category.label}
          </option>
        {/each}
      </select>
    </label>

    <button
      type="submit"
      class="landing-button-primary inline-flex h-11 items-center justify-center gap-2 px-4 text-sm font-semibold"
    >
      Terapkan filter
    </button>
  </form>

  <div
    class="mt-4 flex flex-col gap-3 border-t border-[var(--landing-terminal-line-resolved)] pt-4 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)] md:flex-row md:items-center md:justify-between"
  >
    {#if hasActiveFilters}
      <p>Hasil filter aktif.</p>
      <a
        href={filters.action}
        class="landing-inline-link font-medium text-[var(--landing-terminal-interactive-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"
        >Hapus filter</a
      >
    {:else}
      <p>Gunakan pencarian atau kategori.</p>
    {/if}
  </div>
</section>

{#if featured}
  <section
    class={`grid gap-6 border-b border-[var(--landing-terminal-line-resolved)] py-8 ${featured.coverImage ? "lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:items-start" : ""}`}
  >
    {#if featured.coverImage}
      <a
        href={featured.href}
        class="landing-frame landing-feature-link block overflow-hidden text-inherit no-underline"
      >
        <div class="landing-frame__media">
          <OptimizedImage
            src={featured.coverImage}
            alt={featured.title}
            class="w-full"
            loading="eager"
            decoding="async"
            fetchpriority="high"
            sizes="(min-width: 1024px) 12rem, 100vw"
            onerror={handleImageError}
          />
        </div>
      </a>
    {/if}

    <div class={featured.coverImage ? "space-y-4" : "space-y-3"}>
      <div
        class="text-sm font-semibold text-[var(--landing-terminal-command-resolved)]"
      >
        Artikel unggulan
      </div>
      <a
        href={featured.href}
        class="landing-inline-link inline-block text-inherit no-underline"
      >
        <h2
          class="max-w-[16ch] text-4xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-5xl"
        >
          {featured.title}
        </h2>
      </a>
      <div
        class="flex flex-wrap gap-3 text-sm text-[var(--landing-terminal-muted-resolved)]"
      >
        <span>{featured.dateLabel || "-"}</span>
        <span>{featured.author}</span>
      </div>
      <p
        class="max-w-[60ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
      >
        {featured.excerpt}
      </p>
      {#if featured.categories?.length}
        <div
          class="flex flex-wrap gap-2 border-t border-[var(--landing-terminal-line-resolved)] pt-4"
        >
          {#each featured.categories as category (category)}
            <span
              class="rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] px-2.5 py-1 text-xs font-medium text-[var(--landing-terminal-text-resolved)]"
              >{category}</span
            >
          {/each}
        </div>
      {/if}
    </div>
  </section>
{/if}

{#if !featured && !articles.length}
  <section
    class="grid gap-8 border-b border-[var(--landing-terminal-line-resolved)] py-8 lg:grid-cols-[minmax(0,1.1fr)_18rem] lg:items-start"
  >
    <div class="space-y-4">
      <h2
        class="max-w-[18ch] text-3xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-4xl"
      >
        Arsip publik belum terisi.
      </h2>
      <p
        class="max-w-[58ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
      >
        Belum ada publikasi.
      </p>
    </div>

    <div class="landing-panel px-5 py-5">
      <div
        class="text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
      >
        Sementara itu
      </div>
      <div
        class="mt-3 grid gap-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
      >
        <div>Buka beranda untuk konteks kabinet.</div>
        <div>Kembali nanti untuk publikasi berikutnya.</div>
      </div>
      <a
        href={homeUrl}
        class="landing-button-secondary mt-5 inline-flex items-center gap-2"
      >
        Kembali ke beranda
        <ArrowRight size={16} />
      </a>
    </div>
  </section>
{:else if articles.length}
  <section
    class="space-y-0 border-b border-[var(--landing-terminal-line-resolved)] py-2"
  >
    {#each articles as article, index (article.href)}
      <a
        href={article.href}
        class={`landing-article-row ${index === 0 ? "border-t-0 pt-0" : ""}`}
      >
        <div
          class={article.coverImage
            ? "grid gap-4 md:grid-cols-[12rem_minmax(0,1fr)] md:items-start"
            : "grid gap-3"}
        >
          {#if article.coverImage}
            <OptimizedImage
              src={article.coverImage}
              alt={article.title}
              class="w-full rounded-[8px] border border-[var(--landing-terminal-line-resolved)]"
              loading="lazy"
              decoding="async"
              sizes="12rem"
              onerror={handleImageError}
            />
          {/if}

          <div
            class={article.coverImage
              ? "min-w-0 space-y-3"
              : "min-w-0 space-y-2.5"}
          >
            <div
              class="flex flex-wrap gap-3 text-sm text-[var(--landing-terminal-muted-resolved)]"
            >
              <span>{article.dateLabel || "-"}</span>
              <span>{article.author}</span>
            </div>
            <h3
              class="max-w-[22ch] text-3xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[2rem]"
            >
              {article.title}
            </h3>
            <p
              class="max-w-[62ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
            >
              {article.excerpt}
            </p>
            {#if article.categories?.length}
              <div class="flex flex-wrap gap-2 pt-1">
                {#each article.categories as category (category)}
                  <span
                    class="rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] px-2.5 py-1 text-xs font-medium text-[var(--landing-terminal-text-resolved)]"
                    >{category}</span
                  >
                {/each}
              </div>
            {/if}
          </div>
        </div>
      </a>
    {/each}
  </section>
{/if}

{#if pagination && pagination.total > 0}
  <section
    class="flex flex-col gap-4 border-t border-[var(--landing-terminal-line-resolved)] pt-6 md:flex-row md:items-center md:justify-between"
  >
    <div class="text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]">
      Menampilkan {pagination.from} - {pagination.to} dari {pagination.total} publikasi.
    </div>

    <div class="flex flex-wrap items-center gap-2 text-sm">
      <span
        class="rounded-[8px] border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] px-2.5 py-1 text-[var(--landing-terminal-muted-resolved)]"
      >
        Halaman {pagination.currentPage} / {pagination.lastPage}
      </span>
      {#if pagination.prevUrl}
        <a href={pagination.prevUrl} class="landing-button-secondary px-3 py-2"
          >Sebelumnya</a
        >
      {/if}
      {#if pagination.nextUrl}
        <a href={pagination.nextUrl} class="landing-button-secondary px-3 py-2"
          >Selanjutnya</a
        >
      {/if}
    </div>
  </section>
{/if}
