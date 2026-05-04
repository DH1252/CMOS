<script>
  import { Progress } from '$lib/components/ui/progress/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import Breadcrumbs from '../components/Breadcrumbs.svelte';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Tasks',
    description = '',
    icon = 'fas fa-tasks',
    breadcrumbs = [],
    cards = [],
    emptyState = {
      title: 'Belum ada data',
      text: 'Belum ada data.',
    },
  } = $props();

  const iconToneClass = (tone) => {
    if (tone === 'info') return 'bg-[color:color-mix(in_srgb,var(--signal-info)_16%,transparent)] text-[var(--signal-info)]';
    if (tone === 'success') return 'bg-[color:color-mix(in_srgb,var(--signal-success)_16%,transparent)] text-[var(--signal-success)]';
    if (tone === 'warning') return 'bg-[color:color-mix(in_srgb,var(--signal-warning)_16%,transparent)] text-[var(--signal-warning)]';
    if (tone === 'danger') return 'bg-[color:color-mix(in_srgb,var(--signal-danger)_16%,transparent)] text-[var(--signal-danger)]';
    return 'bg-brand-light/20 text-brand-primary';
  };
</script>

<Breadcrumbs items={breadcrumbs} />

<Card.Root class="mb-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} {icon} />
  </Card.Header>
</Card.Root>

{#if !cards.length}
  <Card.Root class="mt-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="pt-5">
      <EmptyStatePanel title={emptyState.title} text={emptyState.text} icon="fas fa-folder-open" tone="primary" compact={true} />
    </Card.Content>
  </Card.Root>
{:else}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4">
    {#each cards as card, index (card.href || card.title || index)}
      <a href={card.href} class={`flex min-h-full flex-col gap-4 rounded-[10px] border border-border bg-card p-5 text-foreground no-underline transition-colors hover:bg-muted/60 ${card.featured ? 'border-brand-primary/25' : ''}`.trim()}>
        <div class="flex items-start gap-4">
          <div class={`flex h-12 w-12 shrink-0 items-center justify-center rounded-[10px] ${iconToneClass(card.tone)}`}>
            <i class={card.icon || 'fas fa-folder'}></i>
          </div>

          <div class="min-w-0 flex-1">
            <h4 class="m-0 mb-1 text-lg font-semibold text-foreground">{card.title}</h4>
            <p class="m-0 text-muted-foreground leading-relaxed">{card.description}</p>
          </div>
        </div>

        {#if card.progress != null}
          <div class="flex items-center gap-3">
            <Progress value={card.progress} class="flex-1 h-2" />
            <span class="min-w-[2.8rem] text-right font-bold text-muted-foreground text-sm">{card.progress}%</span>
          </div>
        {/if}

        {#if card.stats?.length}
          <div class="flex flex-wrap gap-2 pt-3 border-t border-border/70">
            {#each card.stats as stat, statIndex (stat.label || statIndex)}
              <StatusBadge label={stat.label} icon={stat.icon} tone={stat.tone || 'secondary'} className="shadow-none" />
            {/each}
          </div>
        {/if}
      </a>
    {/each}
  </div>
{/if}
