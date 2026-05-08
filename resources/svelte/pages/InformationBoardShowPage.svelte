<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";
  import {
    buildScopedTinymceAlignmentStyle,
    buildScopedTinymceContentStyle,
  } from "../lib/tinymceContentStyle.js";

  let {
    article = {
      title: "",
      coverImage: null,
      badges: [],
      metadata: [],
      contentHtml: "",
      backAction: null,
      editAction: null,
    },
    latestArticles = [],
    previewTheme = {},
  } = $props();

  const previewShellStyle = $derived(
    previewTheme?.backgroundColor
      ? `background: ${previewTheme.backgroundColor} !important;`
      : "",
  );

  const actionVariant = (action) =>
    action === article.editAction ? "default" : "secondary";
  const fallbackImage = "/images/logokabinet.png";

  const formatDateTime = (value) => {
    if (!value) {
      return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleString("id-ID", {
      timeZone: "Asia/Jakarta",
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<div class="article-page-wrapper">
  <div class="row">
    <div class="col-lg-8 col-12">
      <Card.Root
        class="article-meta-shell animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
      >
        <Card.Content class="article-meta-body">
          <div class="article-meta-top">
            <div class="article-status-row">
              {#each article.badges || [] as badge, index (badge.label || index)}
                {#if index === 0}
                  <span
                    class={`article-status-pill article-status-${badge.tone}`}
                  >
                    {#if badge.icon}
                      <i class={badge.icon}></i>
                    {/if}
                    {badge.label}
                  </span>
                {:else}
                  <span class="article-category-pill">{badge.label}</span>
                {/if}
              {/each}
            </div>

            <div class="article-metadata">
              {#each article.metadata || [] as item, index (item.label || index)}
                <span class="article-meta-item">
                  {#if item.icon}
                    <i class={item.icon}></i>
                  {/if}
                  {item.icon === "fas fa-calendar"
                    ? formatDateTime(item.label)
                    : item.label}
                </span>
              {/each}
            </div>
          </div>

          <h1 class="article-title">{article.title}</h1>
        </Card.Content>
      </Card.Root>

      {#if article.coverImage}
        <img
          src={article.coverImage}
          alt={article.title}
          class="article-cover"
          loading="lazy"
          decoding="async"
          onerror={handleImageError}
        />
      {/if}

      <Card.Root
        class="article-shell animate-fadeIn rounded-[10px] border border-border shadow-none"
        style={previewShellStyle}
      >
        <Card.Content class="article-body pt-5">
          {@html `<style>${buildScopedTinymceContentStyle(".article-content", previewTheme)} ${buildScopedTinymceAlignmentStyle(".article-content")}</style>`}
          <div class="article-content">
            {@html article.contentHtml}
          </div>

          <div class="article-actions">
            {#if article.backAction}
              <Button
                href={article.backAction.href}
                variant={actionVariant(article.backAction)}
              >
                {#if article.backAction.icon}
                  <i class={article.backAction.icon}></i>
                {/if}
                <span>{article.backAction.label}</span>
              </Button>
            {/if}

            {#if article.editAction}
              <Button
                href={article.editAction.href}
                variant={actionVariant(article.editAction)}
              >
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

    <div class="col-lg-4 col-12">
      <Card.Root
        class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
      >
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader
            title="Artikel Terbaru"
            icon="fas fa-stream"
            compact={true}
            headingTag="h3"
          />
        </Card.Header>

        <Card.Content class="latest-articles pt-5">
          {#if !latestArticles.length}
            <EmptyStatePanel
              title="Belum ada artikel lain"
              text="Belum ada artikel lain."
              icon="fas fa-stream"
              tone="secondary"
              compact={true}
            />
          {:else}
            {#each latestArticles as item, index (item.href || index)}
              <a href={item.href} class="latest-article-item">
                <div class="latest-article-title">{item.title}</div>
                <div class="latest-article-date">
                  {formatDateTime(item.date)}
                </div>
              </a>
            {/each}
          {/if}
        </Card.Content>
      </Card.Root>
    </div>
  </div>
</div>

<style>
  .article-page-wrapper {
    padding-top: 1rem;
    padding-bottom: 2rem;
  }
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

  .article-meta-shell {
    margin-bottom: 0.75rem;
    overflow: hidden;
  }

  .article-meta-body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
  }

  .article-meta-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .article-status-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem;
  }

  .article-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .article-status-pill i {
    font-size: 0.7rem;
  }

  .article-status-success {
    background: color-mix(in srgb, var(--signal-success) 14%, transparent);
    color: var(--signal-success);
    border: 1px solid color-mix(in srgb, var(--signal-success) 24%, transparent);
  }

  .article-status-secondary {
    background: color-mix(in srgb, var(--text-muted) 12%, transparent);
    color: var(--text-muted);
    border: 1px solid color-mix(in srgb, var(--line-soft) 60%, transparent);
  }

  .article-status-info {
    background: color-mix(in srgb, var(--signal-info) 14%, transparent);
    color: var(--signal-info);
    border: 1px solid color-mix(in srgb, var(--signal-info) 24%, transparent);
  }

  .article-category-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-soft);
    background: var(--muted);
    border: 1px solid var(--line-soft);
  }

  .article-metadata {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    color: var(--text-muted);
    font-size: 0.85rem;
  }

  .article-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }

  .article-meta-item i {
    font-size: 0.8rem;
    opacity: 0.7;
  }

  .article-title {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1.25;
    color: var(--text-strong);
    margin: 0;
  }

  .article-content {
    margin-top: 1rem;
  }

  .article-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.5rem;
  }

  .article-actions :global([data-slot="button"].button-variant-default) {
    background: var(--brand-primary);
    color: #241a0f;
    border-color: color-mix(in srgb, var(--brand-primary) 60%, black);
  }

  .article-actions :global([data-slot="button"].button-variant-secondary) {
    background: var(--background);
    color: var(--foreground);
    border-color: var(--line-soft);
  }

  .article-actions :global([data-slot="button"].button-variant-destructive) {
    background: color-mix(in srgb, var(--signal-danger) 12%, white);
    color: color-mix(in srgb, var(--signal-danger) 80%, black);
    border-color: color-mix(
      in srgb,
      var(--signal-danger) 24%,
      var(--line-soft)
    );
  }

  .article-actions :global([data-slot="button"]) {
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

  @media (max-width: 767px) {
    .article-meta-top {
      flex-direction: column;
      align-items: flex-start;
    }

    .article-title {
      font-size: 1.35rem;
    }
  }
</style>
