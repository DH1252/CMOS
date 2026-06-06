<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import {
    shouldSkipFormConfirmation,
    submitConfirmedForm,
  } from "$lib/confirmable-form.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import MetricCard from "../components/MetricCard.svelte";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Manajemen Acara",
    description = "",
    icon = "fas fa-calendar-days",
    primaryAction = null,
    filters = {
      action: "#",
      query: "",
      status: "",
      statusOptions: [],
    },
    stats = [],
    events = [],
    pagination = null,
    emptyState = {
      title: "Belum ada acara",
      text: "Belum ada acara.",
    },
    csrfToken = "",
  } = $props();

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

  const confirmSubmission = async (event, item) => {
    const form = event.currentTarget;

    if (shouldSkipFormConfirmation(form)) {
      return;
    }

    if (!item?.confirmText) {
      return;
    }

    event.preventDefault();

    const text = item.confirmText;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: "Konfirmasi",
        text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Lanjutkan",
        cancelButtonText: "Batal",
      });

      if (result.isConfirmed) {
        submitConfirmedForm(form);
      }

      return;
    }

    if (window.confirm(text)) {
      submitConfirmedForm(form);
    }
  };
</script>

<Card.Root
  class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="board-intro-head border-b border-border/70 pb-4">
    <div class="board-intro-copy-wrap">
      <PageHeader {title} {description} {icon} />
    </div>

    {#if primaryAction}
      <Button href={primaryAction.href}>
        {#if primaryAction.icon}
          <i class={primaryAction.icon}></i>
        {/if}
        <span>{primaryAction.label}</span>
      </Button>
    {/if}
  </Card.Header>
</Card.Root>

{#if stats.length}
  <div class="board-stat-grid">
    {#each stats as stat, index (stat.label || index)}
      <MetricCard
        label={stat.label}
        value={stat.value}
        icon={stat.icon}
        tone={stat.tone || "primary"}
      />
    {/each}
  </div>
{/if}

<Card.Root
  class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Content class="board-filter-shell pt-5">
    <form method="GET" action={filters.action} class="board-filter-form">
      <Input
        type="text"
        name="q"
        class="board-filter-input"
        placeholder="Cari judul, deskripsi, atau lokasi acara..."
        value={filters.query || ""}
      />

      <select name="status" class="board-filter-select">
        <option value="">Semua Status</option>
        {#each filters.statusOptions || [] as option, index (option.value || index)}
          <option
            value={option.value}
            selected={String(filters.status || "") === String(option.value)}
            >{option.label}</option
          >
        {/each}
      </select>

      <Button type="submit" variant="secondary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
      </Button>
    </form>
  </Card.Content>
</Card.Root>

{#if !events.length}
  <Card.Root
    class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="pt-5">
      <EmptyStatePanel
        title={emptyState.title}
        text={emptyState.text}
        icon="fas fa-calendar-days"
        tone="primary"
      />
    </Card.Content>
  </Card.Root>
{:else}
  <div class="board-list">
    {#each events as item, index (item.showHref || index)}
      <Card.Root
        class="board-row animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
      >
        <Card.Content class="board-row-copy pt-5">
          <div class="board-row-top">
            {#if item.poster}
              <div class="board-row-thumb" aria-hidden="true">
                <OptimizedImage
                  src={item.poster}
                  alt={item.title}
                  class="h-full w-full object-cover"
                  loading="lazy"
                  decoding="async"
                  sizes="96px"
                />
              </div>
            {/if}

            <div class="board-row-main">
              <div class="board-row-meta">
                <StatusBadge
                  label={item.statusLabel}
                  tone={item.statusTone || "secondary"}
                />
              </div>

              <h4 class="board-row-title">
                <a href={item.showHref}>{item.title}</a>
              </h4>
              <div class="board-row-byline">
                <span
                  ><i class="fas fa-calendar"></i>
                  {formatDateTime(item.startsAt)}</span
                >
                {#if item.location}
                  <span
                    ><i class="fas fa-location-dot"></i> {item.location}</span
                  >
                {/if}
              </div>
            </div>
          </div>

          <div class="board-row-footer">
            <div class="board-row-byline">
              <span><i class="fas fa-user"></i> {item.author}</span>
            </div>

            <div class="board-row-actions">
              <Button href={item.showHref} variant="secondary" size="sm">
                <i class="fas fa-eye"></i>
                <span>Lihat</span>
              </Button>

              {#if item.editHref}
                <Button href={item.editHref} size="sm">
                  <i class="fas fa-pen"></i>
                  <span>Edit</span>
                </Button>
              {/if}

              {#if item.deleteAction}
                <form
                  method="POST"
                  action={item.deleteAction}
                  onsubmit={(event) => confirmSubmission(event, item)}
                >
                  <input type="hidden" name="_token" value={csrfToken} />
                  <input type="hidden" name="_method" value="DELETE" />
                  <Button type="submit" variant="destructive" size="sm">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                  </Button>
                </form>
              {/if}
            </div>
          </div>
        </Card.Content>
      </Card.Root>
    {/each}
  </div>
{/if}

{#if pagination && pagination.total > 0}
  <Card.Root
    class="board-pagination-card animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="board-pagination pt-5">
      <p class="board-pagination-copy">
        Menampilkan {pagination.from} - {pagination.to} dari {pagination.total} acara
      </p>

      <div class="board-pagination-actions">
        <StatusBadge
          label={`Halaman ${pagination.currentPage} / ${pagination.lastPage}`}
          tone="secondary"
        />
        {#if pagination.prevUrl}
          <Button href={pagination.prevUrl} variant="secondary" size="sm">
            <i class="fas fa-arrow-left"></i>
            <span>Sebelumnya</span>
          </Button>
        {/if}
        {#if pagination.nextUrl}
          <Button href={pagination.nextUrl} variant="secondary" size="sm">
            <span>Selanjutnya</span>
            <i class="fas fa-arrow-right"></i>
          </Button>
        {/if}
      </div>
    </Card.Content>
  </Card.Root>
{/if}

<style>
  .board-intro-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .board-intro-copy-wrap {
    min-width: 0;
    flex: 1;
  }

  .board-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
  }

  .board-filter-shell {
    padding-block: 1rem;
    background: color-mix(in srgb, var(--brand-light) 18%, var(--card));
    border-radius: 0.75rem;
  }

  .board-filter-form {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(180px, 1fr) auto;
    gap: 0.75rem;
  }

  :global(.board-filter-input) {
    background: var(--background);
  }

  .board-filter-select {
    width: 100%;
    min-width: 0;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-strong);
    outline: none;
    transition:
      border-color 160ms ease,
      box-shadow 160ms ease;
  }

  .board-filter-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .board-list {
    display: grid;
    gap: 1rem;
    margin-top: 1.5rem;
  }

  .board-row {
    display: block;
    overflow: hidden;
    transition:
      border-color 160ms ease,
      background 160ms ease;
  }

  .board-row:hover {
    border-color: color-mix(in srgb, var(--brand-secondary) 20%, var(--border));
    background: color-mix(
      in srgb,
      var(--brand-secondary-soft) 10%,
      var(--card)
    );
  }

  .board-row-copy {
    display: grid;
    gap: 0.95rem;
    padding: 1rem 1.25rem;
  }

  .board-row-top {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: start;
  }

  .board-row-thumb {
    width: 94px;
    height: 72px;
    overflow: hidden;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--brand-light) 28%, var(--panel-muted));
  }

  .board-row-thumb :global(img) {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    object-position: center;
  }

  .board-row-main {
    min-width: 0;
    display: grid;
    gap: 0.6rem;
  }

  .board-row-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
  }

  .board-row-title {
    margin: 0;
    font-size: 1.15rem;
  }

  .board-row-title a {
    color: inherit;
    text-decoration: none;
  }

  .board-row-title a:hover {
    color: var(--brand-secondary);
  }

  .board-row-footer {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-end;
    margin-top: auto;
  }

  .board-row-byline {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    color: var(--text-muted);
    font-size: 0.88rem;
  }

  .board-row-byline i {
    color: color-mix(in srgb, var(--brand-secondary) 56%, var(--text-muted));
  }

  .board-row-actions,
  form {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 0;
  }

  .board-pagination-card {
    margin-top: 1.5rem;
    background: color-mix(
      in srgb,
      var(--brand-secondary-soft) 10%,
      var(--card)
    );
  }

  .board-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .board-pagination-copy {
    margin: 0;
    color: var(--text-soft);
  }

  .board-pagination-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  @media (max-width: 1023px) {
    .board-filter-form {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  @media (max-width: 767px) {
    .board-row-top {
      grid-template-columns: minmax(0, 1fr);
    }

    .board-row-thumb {
      width: 100%;
      height: 148px;
    }

    .board-row-footer {
      align-items: flex-start;
      flex-direction: column;
    }
  }
</style>
