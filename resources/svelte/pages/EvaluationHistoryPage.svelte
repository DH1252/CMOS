<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Riwayat Evaluasi',
    description = '',
    profile = null,
    createAction = null,
    backAction = null,
    gradeLegend = [],
    periods = [],
    emptyState = {
      title: 'Belum ada evaluasi',
      text: 'Riwayat evaluasi akan tampil di halaman ini setelah penilaian tersedia.',
    },
  } = $props();

  const buttonVariant = (action, isPrimary = false) => {
    if (action?.variant) {
      return action.variant;
    }

    if (action?.tone === 'secondary') {
      return 'secondary';
    }

    if (action?.tone === 'outline') {
      return 'outline';
    }

    if (action?.tone === 'danger') {
      return 'destructive';
    }

    return isPrimary ? 'default' : 'outline';
  };

  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<div class="row">
  <div class="col-12 col-lg-4">
    {#if profile}
      <Card.Root class="mb-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
        <Card.Content class="evaluation-profile-panel pt-5">
          <img src={profile.avatar || fallbackAvatar(profile.name)} alt={profile.name} class="avatar-xl evaluation-profile-avatar" onerror={handleImageError} />
          <h3 class="evaluation-profile-name">{profile.name}</h3>
          {#if profile.email}
            <p class="evaluation-profile-email">{profile.email}</p>
          {/if}
          {#if profile.badge}
            <StatusBadge label={profile.badge.label} tone={profile.badge.tone || 'info'} />
          {/if}

          {#if createAction}
            <Button href={createAction.href} variant={buttonVariant(createAction, true)} class="w-full evaluation-profile-cta">
              <i class={createAction.icon || 'fas fa-plus'}></i>
              <span>{createAction.label}</span>
            </Button>
          {/if}
        </Card.Content>
      </Card.Root>
    {/if}

    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader title="Grade" description="Rentang nilai untuk membaca hasil evaluasi." icon="fas fa-layer-group" compact={true} headingTag="h3" />
      </Card.Header>

      <Card.Content class="evaluation-grade-stack pt-5">
        {#each gradeLegend as grade, index (grade.letter || index)}
          <div class="evaluation-grade-row">
            <span class="evaluation-grade-badge" style={`--grade-color:${grade.color};`}>{grade.letter}</span>
            <div>
              <strong>{grade.label}</strong>
              <p>{grade.range}</p>
            </div>
          </div>
        {/each}
      </Card.Content>
    </Card.Root>
  </div>

  <div class="col-12 col-lg-8">
    <Card.Root class="mb-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader {title} {description} icon="fas fa-chart-simple" />
      </Card.Header>
    </Card.Root>

    {#if periods.length}
      <div class="evaluation-period-list">
        {#each periods as period, index (period.label || index)}
          <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
            <Card.Header class="evaluation-period-head border-b border-border/70 pb-4">
              <div class="evaluation-period-head-copy">
                <PageHeader title={period.label} icon="fas fa-calendar" compact={true} headingTag="h3" />
              </div>

              <div class="evaluation-period-head-meta">
                {#if period.status}
                  <StatusBadge label={period.status.label} tone={period.status.tone || 'secondary'} />
                {/if}
                {#if period.grade}
                  <span class="evaluation-period-grade" style={`--grade-color:${period.grade.color};`}>
                    {period.grade.label}
                  </span>
                {/if}
              </div>
            </Card.Header>

            <Card.Content class="pt-5">
              {#if period.summary?.length}
                <div class="evaluation-summary-grid">
                  {#each period.summary as item, itemIndex (item.label || itemIndex)}
                    <div class="evaluation-summary-box">
                      <span>{item.label}</span>
                      <strong class={item.muted ? 'text-muted' : ''}>{item.value}</strong>
                      {#if item.badge}
                        <small class="evaluation-inline-grade" style={`--grade-color:${item.badge.color};`}>{item.badge.label}</small>
                      {/if}
                    </div>
                  {/each}
                </div>
              {/if}

              <div class="evaluation-entry-list">
                {#each period.entries as entry, entryIndex (entry.label || entry.byline || entryIndex)}
                  <article class="evaluation-entry-card">
                    <div class="evaluation-entry-head">
                      <div class="evaluation-entry-ident">
                        <StatusBadge label={entry.label} tone={entry.tone || 'secondary'} />
                        {#if entry.byline}
                          <p>{entry.byline}</p>
                        {/if}
                      </div>

                      <div class="evaluation-entry-actions">
                        <strong style={`color:${entry.scoreColor || 'inherit'};`}>{entry.score}</strong>
                        {#if entry.editAction}
                          <Button
                            href={entry.editAction.href}
                            variant={buttonVariant(entry.editAction)}
                            size="icon-sm"
                            aria-label={entry.editAction.label}
                            title={entry.editAction.label}
                          >
                            <i class={entry.editAction.icon || 'fas fa-pen'}></i>
                          </Button>
                        {/if}
                      </div>
                    </div>

                    <div class="evaluation-criteria-mini-grid">
                      {#each entry.criteria as criterion, criterionIndex (criterion.label || criterionIndex)}
                        <div class="evaluation-mini-criterion">
                          <span>{criterion.label}</span>
                          <div class="evaluation-mini-dots" aria-label={`${criterion.label}: ${criterion.value} dari 5`}>
                            {#each [1, 2, 3, 4, 5] as score (score)}
                              <i class={`fas fa-circle ${score <= criterion.value ? 'evaluation-mini-dot-active' : 'evaluation-mini-dot'}`}></i>
                            {/each}
                          </div>
                        </div>
                      {/each}
                    </div>

                    {#if entry.notes}
                      <div class="evaluation-entry-note">
                        <small>Catatan</small>
                        <p>{entry.notes}</p>
                      </div>
                    {/if}
                  </article>
                {/each}
              </div>
            </Card.Content>
          </Card.Root>
        {/each}
      </div>
    {:else}
      <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
        <Card.Content class="pt-5">
          <EmptyStatePanel
            title={emptyState.title}
            text={emptyState.text}
            icon="fas fa-star"
            tone="primary"
            action={createAction}
          />
        </Card.Content>
      </Card.Root>
    {/if}

    {#if backAction}
      <div class="mt-4">
        <Button href={backAction.href} variant={buttonVariant(backAction)}>
          <i class={backAction.icon || 'fas fa-arrow-left'}></i>
          <span>{backAction.label}</span>
        </Button>
      </div>
    {/if}
  </div>
</div>

<style>
  .evaluation-profile-panel {
    display: grid;
    justify-items: center;
    text-align: center;
    gap: 0.55rem;
  }

  .evaluation-profile-avatar {
    border: 1px solid var(--line-soft);
    object-fit: cover;
  }

  .evaluation-profile-name {
    margin: 0.2rem 0 0;
    font-weight: 600;
  }

  .evaluation-profile-email {
    margin: 0;
    color: var(--text-muted);
  }

  .evaluation-profile-cta {
    margin-top: 0.65rem;
  }

  .evaluation-grade-stack {
    display: grid;
    gap: 0.85rem;
  }

  .evaluation-grade-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }

  .evaluation-grade-badge {
    width: 2.3rem;
    height: 2.3rem;
    display: grid;
    place-items: center;
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--grade-color) 34%, white);
    color: var(--text-strong);
    border: 1px solid color-mix(in srgb, var(--grade-color) 26%, var(--line-soft));
    font-weight: 800;
    flex-shrink: 0;
  }

  .evaluation-grade-row strong {
    display: block;
    margin-bottom: 0.15rem;
  }

  .evaluation-grade-row p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.84rem;
  }

  .evaluation-period-list {
    display: grid;
    gap: 1rem;
  }

  .evaluation-period-head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .evaluation-period-head-copy {
    min-width: 0;
    flex: 1;
  }

  .evaluation-period-head-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    justify-content: flex-end;
  }

  .evaluation-period-grade,
  .evaluation-inline-grade {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 1.8rem;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--grade-color) 30%, white);
    color: var(--text-strong);
    border: 1px solid color-mix(in srgb, var(--grade-color) 22%, var(--line-soft));
    font-size: 0.74rem;
    font-weight: 800;
  }

  .evaluation-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .evaluation-summary-box {
    display: grid;
    gap: 0.25rem;
    padding: 0.95rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .evaluation-summary-box span {
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .evaluation-summary-box strong {
    font-size: 1.25rem;
    font-weight: 600;
  }

  .evaluation-entry-list {
    display: grid;
    gap: 0.9rem;
  }

  .evaluation-entry-card {
    display: grid;
    gap: 0.9rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .evaluation-entry-head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .evaluation-entry-ident p {
    margin: 0.35rem 0 0;
    color: var(--text-muted);
    font-size: 0.84rem;
  }

  .evaluation-entry-actions {
    display: flex;
    align-items: center;
    gap: 0.55rem;
  }

  .evaluation-entry-actions strong {
    font-size: 1.2rem;
  }

  .evaluation-criteria-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .evaluation-mini-criterion {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: center;
    padding: 0.7rem 0.8rem;
    border-radius: 0.5rem;
    background: var(--muted);
  }

  .evaluation-mini-criterion span {
    font-size: 0.84rem;
  }

  .evaluation-mini-dots {
    display: inline-flex;
    gap: 0.15rem;
    color: var(--text-muted);
  }

  .evaluation-mini-dot-active {
    color: color-mix(in srgb, var(--signal-warning) 84%, black);
  }

  .evaluation-entry-note {
    padding: 0.85rem 0.95rem;
    border-radius: 0.5rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .evaluation-entry-note small {
    display: block;
    margin-bottom: 0.25rem;
    color: var(--text-muted);
    font-size: 0.75rem;
  }

  .evaluation-entry-note p {
    margin: 0;
  }

  @media (max-width: 767px) {
    .evaluation-summary-grid,
    .evaluation-criteria-mini-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .evaluation-period-head,
    .evaluation-entry-head,
    .evaluation-mini-criterion,
    .evaluation-grade-row {
      flex-direction: column;
      align-items: flex-start;
    }

    .evaluation-period-head-meta {
      justify-content: flex-start;
    }
  }
</style>
