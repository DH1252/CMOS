<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import DataTable from '../components/DataTable.svelte';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import MetricCard from '../components/MetricCard.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Laporan & Statistik',
    description = '',
    stats = [],
    taskDistribution = [],
    programDistribution = [],
    departments = [],
    topStaff = [],
    exports = [],
  } = $props();

  const fallbackPalette = {
    primary: '#7c3aed',
    info: '#2563eb',
    warning: '#d97706',
    success: '#059669',
    secondary: '#64748b',
  };

  const colorMap = {
    primary: '--brand-primary',
    info: '--signal-info',
    warning: '--signal-warning',
    success: '--signal-success',
    secondary: '--text-muted',
  };

  const resolveToneColor = (tone) => {
    if (typeof document === 'undefined') {
      return fallbackPalette[tone] || fallbackPalette.secondary;
    }

    const variable = colorMap[tone] || colorMap.secondary;
    const value = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
    return value || fallbackPalette[tone] || fallbackPalette.secondary;
  };

  const maxDistributionValue = (items = []) => {
    return items.reduce((max, item) => Math.max(max, Number(item?.value || 0)), 0) || 1;
  };

  const departmentColumns = [
    { label: 'Departemen' },
    { label: 'Anggota' },
    { label: 'Proker' },
    { label: 'Penyelesaian Task' },
  ];

  const departmentRows = $derived.by(() =>
    departments.map((department) => ({
      id: department.name,
      cells: [
        {
          type: 'stack',
          lines: [
            { text: department.name },
            { text: `${department.completionRate}% selesai`, muted: true },
          ],
        },
        {
          type: 'badge',
          label: String(department.members),
          tone: 'info',
        },
        {
          type: 'badge',
          label: String(department.programs),
          tone: 'primary',
        },
        department.totalTasks > 0
          ? {
              type: 'progress',
              value: Number(department.completionRate || 0),
              label: `${department.completedTasks}/${department.totalTasks}`,
              tone: 'success',
            }
          : {
              text: 'Belum ada task',
              muted: true,
            },
      ],
    })),
  );

  const exportVariant = (item) => {
    if (item?.tone === 'danger') {
      return 'destructive';
    }

    if (item?.tone === 'success') {
      return 'secondary';
    }

    return 'outline';
  };

  const fallbackAvatar = (name = 'User') => {
    const initial = (name || 'User').trim().charAt(0).toUpperCase() || 'U';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="32" fill="#251d39"/><text x="50%" y="50%" dy=".35em" fill="#f5c518" font-family="Public Sans, Arial, sans-serif" font-size="28" font-weight="700" text-anchor="middle">${initial}</text></svg>`;

    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

</script>

<Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="report-intro-head border-b border-border/70 pb-4">
    <div class="report-intro-copy-wrap">
      <PageHeader {title} {description} icon="fas fa-chart-column" />
    </div>

    {#if exports.length}
      <div class="report-intro-actions">
        {#each exports as item, index (item.href || index)}
          <Button href={item.href} variant={exportVariant(item)} data-native="true">
            <i class={item.icon}></i>
            <span>{item.label}</span>
          </Button>
        {/each}
      </div>
    {/if}
  </Card.Header>
</Card.Root>

<div class="report-stat-grid">
  {#each stats as stat, index (stat.label || index)}
    <MetricCard
      label={stat.label}
      value={stat.value}
      icon={stat.icon}
      description={stat.meta || ''}
      tone={stat.tone || 'primary'}
    />
  {/each}
</div>

<div class="report-chart-grid">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader title="Distribusi Task" icon="fas fa-chart-pie" compact={true} headingTag="h3" />
    </Card.Header>

    <Card.Content class="pt-5">
      {#if taskDistribution.length}
        <div class="report-distribution-list" aria-label="Distribusi task">
          {#each taskDistribution as item, index (item.label || index)}
            <div class="report-distribution-item">
              <div class="report-distribution-head">
                <strong>{item.label}</strong>
                <span>{item.value}</span>
              </div>
              <div class="report-distribution-track" aria-hidden="true">
                <div class="report-distribution-bar" style={`width:${Math.max(8, (Number(item.value || 0) / maxDistributionValue(taskDistribution)) * 100)}%; background:${resolveToneColor(item.tone)};`}></div>
              </div>
            </div>
          {/each}
        </div>
        <div class="report-legend-strip">
          {#each taskDistribution as item, index (item.label || index)}
            <div class="report-legend-item">
              <span class="report-legend-dot" style={`background:${resolveToneColor(item.tone)}`}></span>
              <div>
                <strong>{item.value}</strong>
                <span>{item.label}</span>
              </div>
            </div>
          {/each}
        </div>
      {:else}
        <EmptyStatePanel title="Belum ada distribusi task" text="Statistik task akan tampil saat data aktivitas tersedia." icon="fas fa-chart-pie" tone="secondary" compact={true} />
      {/if}
    </Card.Content>
  </Card.Root>

  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader title="Status Program" icon="fas fa-chart-simple" compact={true} headingTag="h3" />
    </Card.Header>

    <Card.Content class="pt-5">
      {#if programDistribution.length}
        <div class="report-distribution-list" aria-label="Distribusi program">
          {#each programDistribution as item, index (item.label || index)}
            <div class="report-distribution-item">
              <div class="report-distribution-head">
                <strong>{item.label}</strong>
                <span>{item.value}</span>
              </div>
              <div class="report-distribution-track" aria-hidden="true">
                <div class="report-distribution-bar" style={`width:${Math.max(8, (Number(item.value || 0) / maxDistributionValue(programDistribution)) * 100)}%; background:${resolveToneColor(item.tone)};`}></div>
              </div>
            </div>
          {/each}
        </div>
        <div class="report-legend-strip report-legend-strip-compact">
          {#each programDistribution as item, index (item.label || index)}
            <div class="report-legend-pill">
              <span class="report-legend-dot" style={`background:${resolveToneColor(item.tone)}`}></span>
              <strong>{item.label}</strong>
              <span>{item.value}</span>
            </div>
          {/each}
        </div>
      {:else}
        <EmptyStatePanel title="Belum ada distribusi program" text="Status program akan muncul setelah portofolio proker tersedia." icon="fas fa-chart-simple" tone="secondary" compact={true} />
      {/if}
    </Card.Content>
  </Card.Root>
</div>

<div class="report-detail-grid">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader title="Ritme Departemen" icon="fas fa-building" compact={true} headingTag="h3" />
    </Card.Header>

    <Card.Content class="px-0 pb-0">
      <DataTable
        columns={departmentColumns}
        rows={departmentRows}
        emptyState={{
          title: 'Belum ada data departemen',
          text: 'Data departemen akan tampil setelah struktur organisasi tersedia.',
          icon: 'fas fa-building-circle-xmark',
        }}
      />
    </Card.Content>
  </Card.Root>

  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader title="Top Staff" icon="fas fa-trophy" compact={true} headingTag="h3" />
    </Card.Header>

    <Card.Content class="pt-5">
      {#if topStaff.length}
        <div class="report-leaderboard">
          {#each topStaff as staff, index (staff.rank || index)}
            <article class="report-leaderboard-item">
              <div class={`report-rank ${staff.rank <= 3 ? 'report-rank-highlight' : ''}`.trim()}>#{staff.rank}</div>
              <img src={staff.avatar || fallbackAvatar(staff.name)} alt={staff.name} class="avatar-sm" onerror={handleImageError} />
              <div class="report-leaderboard-copy">
                <strong>{staff.name}</strong>
                <span>{staff.department}</span>
              </div>
              <div class="report-score-pill">{staff.score}</div>
            </article>
          {/each}
        </div>
      {:else}
        <EmptyStatePanel title="Belum ada data evaluasi" text="Top staff akan terisi setelah siklus evaluasi mulai berjalan." icon="fas fa-star-half-stroke" tone="secondary" compact={true} />
      {/if}
    </Card.Content>
  </Card.Root>
</div>

<style>
  .report-intro-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .report-intro-copy-wrap {
    min-width: 0;
    flex: 1;
  }

  .report-intro-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .report-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
  }

  .report-chart-grid,
  .report-detail-grid {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
  }

  .report-chart-grid {
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  }

  .report-detail-grid {
    grid-template-columns: minmax(0, 1.55fr) minmax(0, 0.95fr);
  }

  .report-distribution-list {
    display: grid;
    gap: 0.9rem;
  }

  .report-distribution-item {
    display: grid;
    gap: 0.45rem;
  }

  .report-distribution-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .report-distribution-head strong {
    font-size: 0.9rem;
  }

  .report-distribution-head span {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .report-distribution-track {
    height: 0.7rem;
    overflow: hidden;
    border-radius: 999px;
    background: var(--muted);
  }

  .report-distribution-bar {
    height: 100%;
    border-radius: inherit;
  }

  .report-legend-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    margin-top: 1rem;
  }

  .report-legend-item,
  .report-legend-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border-radius: 0.5rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .report-legend-item strong,
  .report-legend-pill strong {
    display: block;
    font-size: 0.9rem;
  }

  .report-legend-item span,
  .report-legend-pill span {
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .report-legend-dot {
    width: 0.7rem;
    height: 0.7rem;
    border-radius: 999px;
    flex-shrink: 0;
  }

  .report-legend-strip-compact {
    gap: 0.6rem;
  }

  .report-leaderboard {
    display: grid;
    gap: 0.8rem;
  }

  .report-leaderboard-item {
    display: grid;
    grid-template-columns: auto auto 1fr auto;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 0.95rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .report-rank {
    width: 2.2rem;
    height: 2.2rem;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: var(--card);
    color: var(--text-soft);
    font-size: 0.8rem;
    font-weight: 800;
  }

  .report-rank-highlight {
    background: color-mix(in srgb, var(--signal-warning) 18%, transparent);
    color: var(--signal-warning);
  }

  .report-leaderboard-copy {
    display: grid;
    gap: 0.1rem;
    min-width: 0;
  }

  .report-leaderboard-copy span {
    color: var(--text-muted);
    font-size: 0.8rem;
  }

  .report-score-pill {
    min-width: 3.15rem;
    padding: 0.45rem 0.75rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--signal-success) 14%, transparent);
    color: var(--signal-success);
    font-weight: 800;
    text-align: center;
  }

  @media (max-width: 1080px) {
    .report-detail-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .report-intro-head {
      flex-direction: column;
    }

    .report-intro-actions {
      width: 100%;
    }

    .report-intro-actions :global([data-slot='button']) {
      width: 100%;
    }

    .report-leaderboard-item {
      grid-template-columns: auto 1fr auto;
    }

    .report-leaderboard-item :global(.avatar-sm) {
      display: none;
    }
  }
</style>
