<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import { Label } from '$lib/components/ui/label/index.js';
  import Breadcrumbs from '../components/Breadcrumbs.svelte';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Evaluasi Staff',
    description = '',
    breadcrumbs = [],
    month = { value: '', label: '' },
    months = [],
    monthAction = '',
    monthParam = 'month',
    staff = [],
    emptyState = {
      title: 'Tidak ada staff',
      text: 'Departemen ini belum memiliki staff aktif.',
    },
  } = $props();

  let monthForm = $state(null);
  let selectedMonth = $state('');
  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  $effect(() => {
    selectedMonth = month?.value || '';
  });

  const submitMonth = () => {
    monthForm?.requestSubmit();
  };

  const actionVariant = (action) => {
    if (action?.variant) {
      return action.variant;
    }

    if (action?.tone === 'secondary') {
      return 'secondary';
    }

    if (action?.tone === 'danger') {
      return 'destructive';
    }

    if (action?.tone === 'outline') {
      return 'outline';
    }

    return 'default';
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<Breadcrumbs items={breadcrumbs} />

<Card.Root class="evaluation-staff-intro animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="evaluation-staff-head border-b border-border/70 pb-4">
    <div class="evaluation-staff-head-copy">
      <PageHeader title={title} description={description} icon="fas fa-users" />
    </div>

    <form method="GET" action={monthAction} bind:this={monthForm} class="evaluation-month-form">
      <Label class="evaluation-month-label" for="evaluation-month-select">Periode</Label>
      <select
        id="evaluation-month-select"
        name={monthParam}
        class="evaluation-month-select"
        bind:value={selectedMonth}
        onchange={submitMonth}
      >
        {#each months as option, index (option.value || index)}
          <option value={option.value}>{option.label}</option>
        {/each}
      </select>
    </form>
  </Card.Header>
</Card.Root>

{#if staff.length}
  <div class="evaluation-staff-grid">
    {#each staff as member, index (member.email || member.name || index)}
      <Card.Root class={`evaluation-staff-card animate-fadeIn rounded-[10px] border border-border bg-card shadow-none ${member.hasEvaluated ? 'evaluation-staff-card-complete' : ''}`.trim()}>
        <Card.Content class="space-y-4 pt-4">
          <div class="evaluation-staff-top">
            <img src={member.avatar || fallbackAvatar(member.name)} alt={member.name} class="evaluation-staff-avatar" onerror={handleImageError} />

            <div class="evaluation-staff-copy">
              <h4>{member.name}</h4>
              <p>{member.email}</p>
            </div>

            <StatusBadge label={member.statusLabel} tone={member.statusTone || 'secondary'} />
          </div>

          {#if member.evaluation}
            <div class="evaluation-score-grid">
              <div class="evaluation-score-item">
                <span>Kabinet</span>
                <strong class={member.evaluation.hasKabinet ? '' : 'evaluation-score-muted'}>
                  {member.evaluation.hasKabinet ? member.evaluation.kabinetScore : '-'}
                </strong>
              </div>
              <div class="evaluation-score-item">
                <span>BPH</span>
                <strong class={member.evaluation.hasBph ? '' : 'evaluation-score-muted'}>
                  {member.evaluation.hasBph ? member.evaluation.bphScore : '-'}
                </strong>
              </div>
            </div>

            <div class="evaluation-final-strip">
              <div>
                <small>Skor Final</small>
                <strong>{member.evaluation.finalScore}</strong>
              </div>

              {#if member.evaluation.grade}
                <span class="evaluation-grade-pill" style={`--grade-color:${member.evaluation.grade.color};`}>
                  {member.evaluation.grade.label}
                </span>
              {/if}
            </div>
          {:else}
            <div class="evaluation-staff-empty">
              <i class="fas fa-circle-info"></i>
              <span>Belum ada evaluasi pada periode ini.</span>
            </div>
          {/if}

          <div class="evaluation-staff-actions">
            {#if member.primaryAction}
              <Button href={member.primaryAction.href} variant={actionVariant(member.primaryAction)} size="sm">
                <i class={member.primaryAction.icon}></i>
                <span>{member.primaryAction.label}</span>
              </Button>
            {/if}

            {#if member.secondaryAction}
              <Button href={member.secondaryAction.href} variant={actionVariant(member.secondaryAction)} size="sm">
                <i class={member.secondaryAction.icon}></i>
                <span>{member.secondaryAction.label}</span>
              </Button>
            {/if}
          </div>
        </Card.Content>
      </Card.Root>
    {/each}
  </div>
{:else}
  <Card.Root class="mt-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="pt-5">
      <EmptyStatePanel title={emptyState.title} text={emptyState.text} icon="fas fa-users" tone="primary" compact={true} />
    </Card.Content>
  </Card.Root>
{/if}

<style>
  .evaluation-staff-intro {
    margin-bottom: 1rem;
  }

  .evaluation-staff-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
  }

  .evaluation-staff-head-copy {
    min-width: 0;
    flex: 1;
  }

  .evaluation-month-form {
    min-width: 12rem;
  }

  .evaluation-month-label {
    display: block;
    margin-bottom: 0.45rem;
  }

  .evaluation-month-select {
    width: 100%;
    min-width: 0;
    height: 2.65rem;
    padding: 0.55rem 0.8rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-strong);
    outline: none;
    transition: border-color 160ms ease, box-shadow 160ms ease;
  }

  .evaluation-month-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .evaluation-staff-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
  }

  .evaluation-staff-card-complete {
    background:
      color-mix(in srgb, var(--signal-success) 8%, var(--card));
  }

  .evaluation-staff-top {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }

  .evaluation-staff-avatar {
    width: 3.6rem;
    height: 3.6rem;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid var(--line-soft);
    flex-shrink: 0;
  }

  .evaluation-staff-copy {
    min-width: 0;
    flex: 1;
  }

  .evaluation-staff-copy h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
  }

  .evaluation-staff-copy p {
    margin: 0.2rem 0 0;
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .evaluation-score-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    padding: 0.95rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .evaluation-score-item {
    display: grid;
    gap: 0.2rem;
  }

  .evaluation-score-item span {
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .evaluation-score-item strong {
    font-size: 1.15rem;
  }

  .evaluation-score-muted {
    color: var(--text-muted);
  }

  .evaluation-final-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.95rem 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .evaluation-final-strip small {
    display: block;
    color: var(--text-muted);
    font-size: 0.76rem;
    font-size: 0.76rem;
  }

  .evaluation-final-strip strong {
    display: block;
    font-size: 1.45rem;
    color: var(--brand-primary);
  }

  .evaluation-grade-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.75rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--grade-color) 16%, white);
    color: var(--grade-color);
    font-weight: 800;
    font-size: 0.84rem;
  }

  .evaluation-staff-empty {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  .evaluation-staff-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
  }

  @media (max-width: 767px) {
    .evaluation-staff-head,
    .evaluation-final-strip,
    .evaluation-staff-top {
      flex-direction: column;
      align-items: flex-start;
    }

    .evaluation-month-form {
      min-width: 0;
      width: 100%;
    }
  }
</style>
