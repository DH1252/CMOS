<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    article = {
      title: '',
      coverImage: null,
      badges: [],
      metadata: [],
      contentHtml: '',
      backAction: null,
      editAction: null,
    },
    latestArticles = [],
  } = $props();

  const actionVariant = (action) => (action === article.editAction ? 'default' : 'secondary');
  const fallbackImage = '/images/logokabinet.png';

  const formatDateTime = (value) => {
    if (!value) {
      return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleString('id-ID', {
      timeZone: 'Asia/Jakarta',
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<div class="row">
  <div class="col-12 col-lg-8">
    <Card.Root class="article-shell animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      {#if article.coverImage}
        <img src={article.coverImage} alt={article.title} class="article-cover" loading="lazy" decoding="async" onerror={handleImageError} />
      {/if}

      <Card.Content class="article-body pt-5">
        <div class="article-topline">
          <div class="article-badges">
            {#each article.badges || [] as badge, index (badge.label || index)}
              <StatusBadge label={badge.label} icon={badge.icon || ''} tone={badge.tone || 'secondary'} />
            {/each}
          </div>

          <div class="article-metadata">
            {#each article.metadata || [] as item, index (item.label || index)}
              <span>
                {#if item.icon}
                  <i class={item.icon}></i>
                {/if}
                {item.icon === 'fas fa-calendar' ? formatDateTime(item.label) : item.label}
              </span>
            {/each}
          </div>
        </div>

        <PageHeader title={article.title} icon="fas fa-newspaper" headingTag="h2" compact={true} />

        <div class="article-content">
          {@html article.contentHtml}
        </div>

        <div class="article-actions">
          {#if article.backAction}
            <Button href={article.backAction.href} variant={actionVariant(article.backAction)}>
              {#if article.backAction.icon}
                <i class={article.backAction.icon}></i>
              {/if}
              <span>{article.backAction.label}</span>
            </Button>
          {/if}

          {#if article.editAction}
            <Button href={article.editAction.href} variant={actionVariant(article.editAction)}>
              {#if article.editAction.icon}
                <i class={article.editAction.icon}></i>
              {/if}
              <span>{article.editAction.label}</span>
            </Button>
          {/if}
        </div>
      </Card.Content>
    </Card.Root>
  </div>

  <div class="col-12 col-lg-4">
    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader title="Artikel Terbaru" icon="fas fa-stream" compact={true} headingTag="h3" />
      </Card.Header>

      <Card.Content class="latest-articles pt-5">
        {#if !latestArticles.length}
          <EmptyStatePanel title="Belum ada artikel lain" text="Belum ada artikel lain." icon="fas fa-stream" tone="secondary" compact={true} />
        {:else}
          {#each latestArticles as item, index (item.href || index)}
            <a href={item.href} class="latest-article-item">
              <div class="latest-article-title">{item.title}</div>
              <div class="latest-article-date">{formatDateTime(item.date)}</div>
            </a>
          {/each}
        {/if}
      </Card.Content>
    </Card.Root>
  </div>
</div>

<style>
  .article-shell {
    overflow: hidden;
  }

  .article-cover {
    display: block;
    width: 100%;
    max-height: 16rem;
    object-fit: cover;
  }

  .article-body {
    padding: 1.5rem;
  }

  .article-topline {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1rem;
  }

  .article-badges,
  .article-metadata {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
  }

  .article-metadata {
    justify-content: flex-end;
    color: var(--text-muted);
    font-size: 0.88rem;
  }

  .article-content {
    margin-top: 1rem;
    font-family: 'Public Sans', sans-serif;
    font-size: 16px;
    line-height: 1.65;
    color: #1f2937;
  }

  .article-content :global(p) {
    margin: 0 0 0.6rem 0;
  }

  .article-content :global(h1) {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.6rem 0;
    line-height: 1.3;
  }

  .article-content :global(h2) {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    line-height: 1.35;
  }

  .article-content :global(h3) {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 0.45rem 0;
    line-height: 1.4;
  }

  .article-content :global(h4) {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.4rem 0;
  }

  .article-content :global(ul),
  .article-content :global(ol) {
    padding-left: 1.25rem;
    margin: 0 0 0.6rem 0;
  }

  .article-content :global(li) {
    margin: 0 0 0.25rem 0;
  }

  .article-content :global(blockquote) {
    border-left: 3px solid #e2e8f0;
    padding-left: 0.75rem;
    margin: 0 0 0.6rem 0;
    font-style: italic;
    color: #64748b;
  }

  .article-content :global(pre) {
    background: #f1f5f9;
    padding: 0.6rem 0.8rem;
    border-radius: 0.5rem;
    margin: 0 0 0.6rem 0;
    overflow-x: auto;
  }

  .article-content :global(code) {
    background: #f1f5f9;
    padding: 0.1rem 0.3rem;
    border-radius: 0.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.875rem;
  }

  .article-content :global(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    display: block;
    margin: 0.5rem 0;
  }

  .article-content :global(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 0.6rem 0;
    font-size: 0.9rem;
  }

  .article-content :global(th),
  .article-content :global(td) {
    border: 1px solid #e2e8f0;
    padding: 0.4rem 0.6rem;
    text-align: left;
    vertical-align: top;
  }

  .article-content :global(th) {
    background: #f8fafc;
    font-weight: 600;
  }

  .article-content :global(hr) {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 1rem 0;
  }

  .article-content :global(a) {
    color: #2563eb;
    text-decoration: underline;
  }

  .article-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.5rem;
  }

  .article-actions :global([data-slot='button'].button-variant-default) {
    background: var(--brand-primary);
    color: #241a0f;
    border-color: color-mix(in srgb, var(--brand-primary) 60%, black);
  }

  .article-actions :global([data-slot='button'].button-variant-secondary) {
    background: var(--background);
    color: var(--foreground);
    border-color: var(--line-soft);
  }

  .article-actions :global([data-slot='button'].button-variant-destructive) {
    background: color-mix(in srgb, var(--signal-danger) 12%, white);
    color: color-mix(in srgb, var(--signal-danger) 80%, black);
    border-color: color-mix(in srgb, var(--signal-danger) 24%, var(--line-soft));
  }

  .article-actions :global([data-slot='button']) {
    min-width: 0;
  }

  .latest-articles {
    display: grid;
    gap: 0.75rem;
  }

  .latest-article-item {
    display: grid;
    gap: 0.2rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
    text-decoration: none;
    color: inherit;
  }

  .latest-article-title {
    font-weight: 700;
  }

  .latest-article-date {
    font-size: 0.85rem;
    color: var(--text-muted);
  }

  .article-content :global(.aligncenter),
  .article-content :global(img.aligncenter) {
    display: block;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
  }

  .article-content :global(.alignleft),
  .article-content :global(img.alignleft) {
    float: left;
    margin-right: 1rem;
    margin-bottom: 0.5rem;
    text-align: left;
  }

  .article-content :global(.alignright),
  .article-content :global(img.alignright) {
    float: right;
    margin-left: 1rem;
    margin-bottom: 0.5rem;
    text-align: right;
  }

  .article-content :global(.alignjustify) {
    text-align: justify;
  }

  .article-content :global(p::after),
  .article-content :global(h1::after),
  .article-content :global(h2::after),
  .article-content :global(h3::after),
  .article-content :global(h4::after) {
    content: '';
    display: table;
    clear: both;
  }

  @media (max-width: 767px) {
    .article-topline {
      flex-direction: column;
    }

    .article-metadata {
      justify-content: flex-start;
    }
  }
</style>
