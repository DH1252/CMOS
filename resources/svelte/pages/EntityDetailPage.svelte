<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import DataTable from "../components/DataTable.svelte";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import MetricCard from "../components/MetricCard.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let { summary = {}, stats = [], sections = [], csrfToken = "" } = $props();

  const buttonVariant = (action, isPrimary = false) => {
    if (action?.variant) {
      return action.variant;
    }

    if (action?.tone === "danger") {
      return "destructive";
    }

    if (action?.tone === "secondary") {
      return "secondary";
    }

    if (action?.tone === "ghost") {
      return "ghost";
    }

    return isPrimary ? "default" : "outline";
  };

  const fallbackImage = "/images/logokabinet.png";

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<div class="grid gap-4 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]">
  <div>
    <Card.Root
      class="detail-summary-card animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Content class="space-y-5 pt-4">
        <div class="detail-summary-head">
          {#if summary.image}
            <img
              src={summary.image}
              alt={summary.title}
              class="avatar-xl"
              onerror={handleImageError}
            />
          {:else}
            <div class="detail-summary-icon">
              <i class={summary.icon || "fas fa-circle"}></i>
            </div>
          {/if}

          <div class="detail-summary-copy">
            <h3 class="detail-summary-title">{summary.title}</h3>
            {#if summary.subtitle}
              <p class="detail-summary-subtitle">{summary.subtitle}</p>
            {/if}
            {#if summary.badges?.length}
              <div class="detail-badges">
                {#each summary.badges as badge, badgeIndex (badge.label || badgeIndex)}
                  <StatusBadge
                    label={badge.label}
                    icon={badge.icon}
                    tone={badge.tone || "secondary"}
                  />
                {/each}
              </div>
            {/if}
          </div>
        </div>

        {#if summary.description}
          <p class="detail-summary-description">{summary.description}</p>
        {/if}

        {#if summary.facts?.length}
          <div class="detail-facts">
            {#each summary.facts as fact, factIndex (fact.label || factIndex)}
              <div class="detail-fact-row">
                <span>{fact.label}</span>
                <strong>{fact.value}</strong>
              </div>
            {/each}
          </div>
        {/if}

        {#if summary.actions?.length}
          <div class="detail-summary-actions">
            {#each summary.actions as item, itemIndex (item.href || item.label || itemIndex)}
              <Button
                href={item.href}
                variant={buttonVariant(item, itemIndex === 0)}
                class="detail-summary-action"
              >
                {#if item.icon}
                  <i class={item.icon}></i>
                {/if}
                <span>{item.label}</span>
              </Button>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>

  <div>
    {#if stats.length}
      <div class="row mb-4">
        {#each stats as stat, statIndex (stat.label || statIndex)}
          <div class="col-md-6 col-lg-4 col-12">
            <MetricCard
              label={stat.label}
              value={stat.value}
              icon={stat.icon}
              description={stat.description || ""}
              tone={stat.tone || "primary"}
            />
          </div>
        {/each}
      </div>
    {/if}

    {#each sections as section, sectionIndex (section.title || sectionIndex)}
      <Card.Root
        class={`animate-fadeIn rounded-[10px] border border-border bg-card shadow-none ${section.spacingClass || "mb-4"}`.trim()}
      >
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader
            title={section.title}
            icon={section.icon || "fas fa-table"}
            action={section.action || null}
            compact={true}
            headingTag="h3"
          />
        </Card.Header>

        <Card.Content class={section.kind === "table" ? "px-0 pb-0" : "pt-5"}>
          {#if section.kind === "table"}
            <DataTable
              columns={section.columns || []}
              rows={section.rows || []}
              emptyState={{
                title: section.emptyText || "Belum ada data.",
                text: section.emptyDescription || "",
                icon: section.emptyIcon || "fas fa-table-list",
              }}
              {csrfToken}
            />
          {:else if section.kind === "list"}
            {#if !section.items?.length}
              <EmptyStatePanel
                title={section.emptyText || "Belum ada data."}
                text={section.emptyDescription || ""}
                icon={section.emptyIcon || "fas fa-layer-group"}
                compact={true}
              />
            {:else}
              <div class="detail-list">
                {#each section.items as item, itemIndex (item.title || itemIndex)}
                  <div class="detail-list-item">
                    <div class="detail-list-item-main">
                      <div class="detail-list-item-title">{item.title}</div>
                      {#if item.subtitle}
                        <div class="detail-list-item-subtitle">
                          {item.subtitle}
                        </div>
                      {/if}
                    </div>
                    {#if item.trailing}
                      <StatusBadge
                        label={item.trailing.label}
                        tone={item.trailing.tone || "secondary"}
                        icon={item.trailing.icon || ""}
                      />
                    {/if}
                  </div>
                {/each}
              </div>
            {/if}
          {/if}
        </Card.Content>
      </Card.Root>
    {/each}
  </div>
</div>

<style>
  .detail-summary-card {
    position: static;
  }

  .detail-summary-head {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .detail-summary-icon {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 0.625rem;
    display: grid;
    place-items: center;
    background: color-mix(in srgb, var(--brand-light) 28%, transparent);
    color: color-mix(in srgb, var(--brand-primary) 84%, black);
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  .detail-summary-copy {
    min-width: 0;
  }

  .detail-summary-title {
    margin: 0;
    font-size: 1.35rem;
    line-height: 1.2;
  }

  .detail-summary-subtitle {
    margin: 0.35rem 0 0;
    color: var(--text-muted);
  }

  .detail-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.85rem;
  }

  .detail-summary-description {
    margin: 0;
    color: var(--text-soft);
    line-height: 1.7;
  }

  .detail-facts {
    display: grid;
    gap: 0.65rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line-soft);
  }

  .detail-fact-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .detail-fact-row span {
    color: var(--text-muted);
  }

  .detail-fact-row strong {
    color: var(--text-strong);
    text-align: right;
  }

  .detail-summary-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .detail-summary-action {
    flex: 1 1 12rem;
  }

  .detail-list {
    display: grid;
    gap: 0.85rem;
  }

  .detail-list-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--line-soft);
  }

  .detail-list-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
  }

  .detail-list-item-main {
    min-width: 0;
  }

  .detail-list-item-title {
    font-weight: 700;
  }

  .detail-list-item-subtitle {
    margin-top: 0.2rem;
    font-size: 0.9rem;
    color: var(--text-muted);
  }

  @media (max-width: 1023px) {
    .detail-summary-card {
      margin-bottom: 1rem;
    }
  }
</style>
