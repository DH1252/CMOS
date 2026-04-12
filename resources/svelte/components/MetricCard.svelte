<script>
  import * as Card from '$lib/components/ui/card/index.js';

  let {
    label = '',
    value = '',
    icon = 'fas fa-chart-line',
    description = '',
    tone = 'primary',
  } = $props();

  const toneClass = (value) => {
    if (value === 'success') {
      return 'text-[var(--signal-success)]';
    }

    if (value === 'warning') {
      return 'text-[var(--signal-warning)]';
    }

    if (value === 'danger') {
      return 'text-[var(--signal-danger)]';
    }

    if (value === 'info') {
      return 'text-[var(--signal-info)]';
    }

    return 'text-brand-primary';
  };
</script>

<Card.Root size="sm" class="h-full rounded-[10px] border border-border bg-card shadow-none">
  <Card.Content class="grid gap-3 p-5">
    <div class="flex items-start justify-between gap-3">
      <div class="space-y-1">
        <div class="text-sm text-muted-foreground">{label}</div>
        <div class="text-3xl font-semibold leading-none text-foreground">{value}</div>
      </div>
      <div class={`inline-flex items-center gap-2 text-sm ${toneClass(tone)}`}>
        <i class={icon}></i>
      </div>
    </div>

    {#if description}
      <div class="border-t border-border pt-3 text-sm leading-6 text-muted-foreground">{description}</div>
    {/if}
  </Card.Content>
</Card.Root>
