<script>
  import { onDestroy, onMount } from 'svelte';
  import * as Card from '$lib/components/ui/card/index.js';
  import EmptyStatePanel from './components/EmptyStatePanel.svelte';
  import PageHeader from './components/PageHeader.svelte';

  let {
    stats = {},
    tasksByStatus = {},
    recentTasks = [],
    upcomingTimelines = [],
    latestInformationBoards = [],
    departmentProgress = [],
    staffRanking = [],
    monthlyTrends = [],
    links = {},
    now = {},
  } = $props();

  const initialDateTime = (() => {
    const parsed = now?.iso ? new Date(now.iso) : new Date();

    return Number.isNaN(parsed.getTime()) ? new Date() : parsed;
  })();

  const initialTimeMs = initialDateTime.getTime();
  const mountedAtMs = Date.now();
  let currentTickMs = $state(mountedAtMs);
  let clockInterval = $state(null);

  const currentDateTime = $derived.by(() => {
    return new Date(initialTimeMs + (currentTickMs - mountedAtMs));
  });

  onMount(() => {
    clockInterval = window.setInterval(() => {
      currentTickMs = Date.now();
    }, 1000);
  });

  onDestroy(() => {
    if (clockInterval) {
      window.clearInterval(clockInterval);
    }
  });

  const totalTaskCount = $derived.by(
    () => Number(tasksByStatus.todo || 0) + Number(tasksByStatus.in_progress || 0) + Number(tasksByStatus.done || 0),
  );

  const completionRate = $derived.by(() =>
    totalTaskCount ? Math.round((Number(tasksByStatus.done || 0) / totalTaskCount) * 100) : 0,
  );

  const overdueTaskCount = $derived.by(() => Number(stats.overdueTasks || 0));
  const nextTimeline = $derived.by(() => upcomingTimelines?.[0] || null);

  const quickActions = $derived.by(
    () =>
      [
        links?.tasksIndex ? { href: links.tasksIndex, label: 'Buka task' } : null,
        links?.timelinesIndex ? { href: links.timelinesIndex, label: 'Buka timeline' } : null,
      ].filter(Boolean),
  );

  const summaryItems = $derived.by(() => [
    {
      label: 'Task terlambat',
      value: overdueTaskCount.toLocaleString('id-ID'),
      detail: overdueTaskCount > 0 ? 'Cek lebih dulu.' : 'Tidak ada yang terlewat.',
      tone: overdueTaskCount > 0 ? 'danger' : 'success',
      href: links?.tasksIndex || null,
      actionLabel: 'Buka task',
    },
    {
      label: 'Agenda terdekat',
      value: nextTimeline ? formatShortDate(nextTimeline.start_date) : '-',
      detail: nextTimeline?.title || 'Belum ada agenda dekat.',
      tone: 'secondary',
      href: links?.timelinesIndex || null,
      actionLabel: 'Buka timeline',
    },
    {
      label: 'Penyelesaian',
      value: `${completionRate}%`,
      detail: totalTaskCount ? `${totalTaskCount} task tercatat.` : 'Belum ada task tercatat.',
      tone: 'primary',
      href: links?.tasksIndex || null,
      actionLabel: 'Lihat task',
    },
  ]);

  const statusRows = $derived.by(() => [
    { label: 'Todo', value: Number(tasksByStatus.todo || 0), href: links?.tasksIndex || null },
    { label: 'Berjalan', value: Number(tasksByStatus.in_progress || 0), href: links?.tasksIndex || null },
    { label: 'Selesai', value: Number(tasksByStatus.done || 0), href: links?.tasksIndex || null },
  ]);

  const taskDetailHref = (task) => {
    if (!task?.id || !links?.tasksIndex) {
      return null;
    }

    return `${String(links.tasksIndex).replace(/\/$/, '')}/${task.id}`;
  };

  const formatShortDate = (value) => {
    if (!value) {
      return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return '-';
    }

    return date.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short' });
  };

  const formatLongDate = (value) => {
    if (!value) {
      return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return '-';
    }

    return date.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric' });
  };

  const formatWeekday = (value) => value.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', weekday: 'long' });
  const formatMonthYear = (value) => value.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', month: 'long', year: 'numeric' });
  const formatClock = (value) => value.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit' });
  const formatDayNumber = (value) => value.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit' });
  const progressValue = (value) => Math.max(0, Math.min(100, Number(value) || 0));

  const toneValueClass = (tone) => {
    if (tone === 'danger') {
      return 'text-[var(--signal-danger)]';
    }

    if (tone === 'success') {
      return 'text-[var(--signal-success)]';
    }

    return 'text-foreground';
  };

  const statusLabel = (status) => {
    if (status === 'in_progress') {
      return 'Berjalan';
    }

    if (status === 'done') {
      return 'Selesai';
    }

    return 'Todo';
  };

  const statusTone = (statusBadge, status) => {
    if (statusBadge === 'success' || status === 'done') {
      return 'success';
    }

    if (statusBadge === 'warning' || status === 'in_progress') {
      return 'warning';
    }

    if (statusBadge === 'danger') {
      return 'danger';
    }

    return 'secondary';
  };

</script>

<section class="grid gap-4">
  <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border pb-4">
      <PageHeader
        title="Fokus hari ini"
        description="Mulai dari task yang mendesak, lalu cek agenda terdekat."
        action={links?.tasksIndex ? { href: links.tasksIndex, label: 'Buka task', tone: 'secondary' } : null}
        compact={true}
        headingTag="h2"
      />
    </Card.Header>
    <Card.Content class="grid gap-5 pt-5">
      <div class="grid gap-3 md:grid-cols-3">
        {#each summaryItems as item, index (item.label || index)}
          {#if item.href}
            <a href={item.href} class="block rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class={`mt-2 text-2xl font-semibold leading-none ${toneValueClass(item.tone)}`}>{item.value}</div>
              <p class="mt-2 text-sm leading-6 text-muted-foreground">{item.detail}</p>
              <div class="dashboard-link-action mt-3 text-sm font-medium">{item.actionLabel}</div>
            </a>
          {:else}
            <section class="rounded-[10px] border border-border bg-background px-4 py-4">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class={`mt-2 text-2xl font-semibold leading-none ${toneValueClass(item.tone)}`}>{item.value}</div>
              <p class="mt-2 text-sm leading-6 text-muted-foreground">{item.detail}</p>
            </section>
          {/if}
        {/each}
      </div>

      {#if quickActions.length > 1}
        <div class="flex flex-wrap gap-2 border-t border-border pt-5">
          {#each quickActions as action, index (action.href || index)}
            <a href={action.href} class="dashboard-toolbar-link inline-flex items-center justify-center rounded-[10px] border border-border bg-background px-3.5 py-2.5 text-sm font-medium text-foreground no-underline transition-colors hover:border-brand-primary hover:bg-muted">
              {action.label}
            </a>
          {/each}
        </div>
      {/if}
    </Card.Content>
  </Card.Root>

  <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border pb-4">
      <PageHeader
        title="Pergerakan task"
        description="Ringkasan status dan perubahan terbaru."
        compact={true}
        headingTag="h3"
      />
    </Card.Header>
    <Card.Content class="grid gap-5 pt-5">
      <div class="grid gap-3 sm:grid-cols-3">
        {#each statusRows as item, index (item.label || index)}
          {#if item.href}
            <a href={item.href} class="dashboard-quick-action block rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class="mt-2 text-2xl font-semibold text-foreground">{item.value}</div>
              <div class="dashboard-link-action mt-3 text-sm font-medium">Buka daftar task</div>
            </a>
          {:else}
            <div class="rounded-[10px] border border-border bg-background px-4 py-4">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class="mt-2 text-2xl font-semibold text-foreground">{item.value}</div>
            </div>
          {/if}
        {/each}
      </div>

      <div class="dashboard-list-surface">
        {#if recentTasks.length === 0}
          <EmptyStatePanel title="Tidak ada task" text="Perubahan task akan tampil di sini setelah ada aktivitas baru." icon="fas fa-list-check" compact={true} />
        {:else}
          <div class="dashboard-list">
            {#each recentTasks.slice(0, 5) as task, index (`task-${task.id || task.title || index}-${index}`)}
              <a href={taskDetailHref(task) || links?.tasksIndex || '#'} class="dashboard-list-item">
                <div class="dashboard-list-main">
                  <strong class="dashboard-list-title">{(task.title || '').length > 42 ? `${task.title.slice(0, 42)}...` : task.title || '-'}</strong>
                  <span class="dashboard-list-meta">{task.program?.name || '-'}</span>
                </div>
                <div class="dashboard-list-side">
                  <span class={`dashboard-pill dashboard-pill-${statusTone(task.status_badge, task.status)}`}>{statusLabel(task.status)}</span>
                  <span class="dashboard-list-progress">{progressValue(task.progress)}%</span>
                  <span class={`dashboard-list-meta ${task.is_overdue ? 'text-[var(--signal-danger)]' : ''}`}>{task.is_overdue ? `Terlambat - ${formatShortDate(task.deadline)}` : formatShortDate(task.deadline)}</span>
                </div>
              </a>
            {/each}
          </div>
        {/if}
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border pb-4">
      <PageHeader
        title="Agenda terdekat"
        description="Lihat jadwal yang paling dekat dari sekarang."
        action={links?.timelinesCalendar ? { href: links.timelinesCalendar, label: 'Buka kalender', tone: 'secondary' } : null}
        compact={true}
        headingTag="h3"
      />
    </Card.Header>
    <Card.Content class="grid gap-5 pt-5">
      <div class="grid gap-3 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center">
        <div>
          <div class="text-sm text-muted-foreground">Waktu lokal</div>
          <div class="mt-2 text-3xl font-semibold leading-none text-foreground">{formatClock(currentDateTime)}</div>
        </div>
        <p class="text-sm leading-6 text-muted-foreground">{formatWeekday(currentDateTime)}, {formatDayNumber(currentDateTime)} {formatMonthYear(currentDateTime)}</p>
      </div>

      {#if upcomingTimelines.length === 0}
        <EmptyStatePanel title="Tidak ada agenda" text="Belum ada agenda mendatang yang terjadwal." icon="fas fa-calendar-xmark" compact={true} />
      {:else}
        <div class="grid gap-3">
          {#each upcomingTimelines.slice(0, 4) as timeline, index (`timeline-${timeline.id || timeline.title || index}-${index}`)}
            {#if links?.timelinesCalendar}
              <a href={links.timelinesCalendar} class="rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
                <strong class="block text-sm text-foreground">{timeline.title}</strong>
                <div class="mt-2 text-sm text-muted-foreground">{formatLongDate(timeline.start_date)}</div>
              </a>
            {:else}
              <div class="rounded-[10px] border border-border bg-background px-4 py-4">
                <strong class="block text-sm text-foreground">{timeline.title}</strong>
                <div class="mt-2 text-sm text-muted-foreground">{formatLongDate(timeline.start_date)}</div>
              </div>
            {/if}
          {/each}
        </div>
      {/if}
    </Card.Content>
  </Card.Root>

  {#if latestInformationBoards.length > 0}
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
        <PageHeader
          title="Artikel terbaru"
          description="Pembaruan informasi internal yang baru dipublikasikan."
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="grid gap-3 pt-5 md:grid-cols-2">
        {#each latestInformationBoards.slice(0, 4) as article, index (`latest-${article.href || article.title || index}-${index}`)}
          <a href={article.href} class="dashboard-link-card">
            <strong class="dashboard-link-card-title">{article.title}</strong>
            <p class="dashboard-link-card-copy">{article.excerpt || 'Buka artikel untuk melihat detail lengkap.'}</p>
            <span class="dashboard-link-card-meta">{formatLongDate(article.publishedAt)}</span>
          </a>
        {/each}
      </Card.Content>
    </Card.Root>
  {/if}

  {#if departmentProgress.length > 0 || staffRanking.length > 0}
    <div class="grid gap-4 xl:grid-cols-2">
      {#if departmentProgress.length > 0}
        <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
          <Card.Header class="border-b border-border pb-4">
            <PageHeader
              title="Progress departemen"
              description="Ringkasan penyelesaian task lintas departemen."
              compact={true}
              headingTag="h3"
            />
          </Card.Header>
          <Card.Content class="dashboard-list-surface pt-5">
            <div class="dashboard-list">
              {#each departmentProgress as department, index (`dept-${department.name || index}-${index}`)}
                <section class="dashboard-list-item dashboard-list-item-static">
                  <div class="dashboard-list-main">
                    <strong class="dashboard-list-title">{department.name || '-'}</strong>
                    <span class="dashboard-list-meta">{Number(department.total || 0)} task tercatat</span>
                  </div>
                  <div class="dashboard-list-side">
                    <span class="dashboard-list-progress">{Number(department.percentage || 0)}%</span>
                    <span class="dashboard-list-meta">{Number(department.done || 0)} / {Number(department.total || 0)} selesai</span>
                  </div>
                </section>
              {/each}
            </div>
          </Card.Content>
        </Card.Root>
      {/if}

      {#if staffRanking.length > 0}
        <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
          <Card.Header class="border-b border-border pb-4">
            <PageHeader
              title="Peringkat staff"
              description="Rata-rata evaluasi terbaru untuk staff aktif."
              compact={true}
              headingTag="h3"
            />
          </Card.Header>
          <Card.Content class="dashboard-list-surface pt-5">
            <div class="dashboard-list">
              {#each staffRanking as staff, index (`staff-${staff.name || index}-${index}`)}
                <section class="dashboard-list-item dashboard-list-item-static">
                  <div class="dashboard-list-main">
                    <strong class="dashboard-list-title">{staff.name || '-'}</strong>
                    <span class="dashboard-list-meta">{staff.department || 'Tanpa departemen'}</span>
                  </div>
                  <div class="dashboard-list-side">
                    <span class="dashboard-list-progress">{Number(staff.score || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 })}</span>
                  </div>
                </section>
              {/each}
            </div>
          </Card.Content>
        </Card.Root>
      {/if}
    </div>
  {/if}

  {#if monthlyTrends.length > 0}
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
        <PageHeader
          title="Tren bulanan"
          description="Perbandingan task dibuat dan diselesaikan dalam enam bulan terakhir."
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="grid gap-3 pt-5 md:grid-cols-2 xl:grid-cols-3">
        {#each monthlyTrends as trend, index (`trend-${trend.month || index}-${index}`)}
          <section class="dashboard-stat-card">
            <div class="dashboard-stat-card-label">{trend.month}</div>
            <div class="dashboard-stat-card-grid">
              <div>
                <div class="dashboard-stat-card-value">{Number(trend.created || 0).toLocaleString('id-ID')}</div>
                <div class="dashboard-stat-card-copy">Task dibuat</div>
              </div>
              <div>
                <div class="dashboard-stat-card-value text-[var(--signal-success)]">{Number(trend.completed || 0).toLocaleString('id-ID')}</div>
                <div class="dashboard-stat-card-copy">Task selesai</div>
              </div>
            </div>
          </section>
        {/each}
      </Card.Content>
    </Card.Root>
  {/if}
</section>

<style>
  .dashboard-link-card {
    display: grid;
    gap: 0.45rem;
    padding: 1rem 1.05rem;
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--background);
    text-decoration: none;
    color: inherit;
    transition: border-color 160ms ease, background 160ms ease;
  }

  .dashboard-link-card:hover {
    border-color: var(--brand-primary);
    background: color-mix(in srgb, var(--muted) 80%, transparent);
  }

  .dashboard-link-card-title {
    font-size: 0.95rem;
    line-height: 1.45;
    color: var(--text-strong);
  }

  .dashboard-link-card-copy,
  .dashboard-link-card-meta,
  .dashboard-stat-card-copy,
  .dashboard-stat-card-label {
    font-size: 0.84rem;
    line-height: 1.55;
    color: var(--text-muted);
  }

  .dashboard-link-action {
    color: var(--text-strong);
  }

  .dashboard-quick-action {
    color: var(--text-strong);
  }

  .dashboard-toolbar-link {
    color: var(--text-strong);
  }

  .dashboard-stat-card {
    display: grid;
    gap: 0.85rem;
    padding: 1rem 1.05rem;
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--background);
  }

  .dashboard-stat-card-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .dashboard-stat-card-value {
    font-size: 1.55rem;
    font-weight: 700;
    line-height: 1;
    color: var(--text-strong);
  }

  .dashboard-list-surface {
    overflow: hidden;
    border: 1px solid color-mix(in srgb, var(--border) 80%, transparent);
    border-radius: 0.625rem;
    background: color-mix(in srgb, var(--background) 70%, transparent);
  }

  .dashboard-list {
    display: grid;
  }

  .dashboard-list-item {
    display: grid;
    gap: 0.85rem;
    padding: 1rem 1.05rem;
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid color-mix(in srgb, var(--border) 72%, transparent);
  }

  .dashboard-list-item:last-child {
    border-bottom: none;
  }

  .dashboard-list-item:hover {
    background: color-mix(in srgb, var(--muted) 70%, transparent);
  }

  .dashboard-list-item-static:hover {
    background: transparent;
  }

  .dashboard-list-main,
  .dashboard-list-side {
    display: grid;
    gap: 0.25rem;
  }

  .dashboard-list-title {
    font-size: 0.94rem;
    color: var(--text-strong);
  }

  .dashboard-list-meta {
    font-size: 0.82rem;
    line-height: 1.55;
    color: var(--text-muted);
  }

  .dashboard-list-progress {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-strong);
  }

  .dashboard-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    font-size: 0.72rem;
    font-weight: 700;
  }

  .dashboard-pill-secondary {
    background: var(--muted);
    color: var(--pill-text-secondary);
  }

  .dashboard-pill-warning {
    background: color-mix(in srgb, var(--signal-warning) 14%, transparent);
    color: var(--pill-text-warning);
  }

  .dashboard-pill-success {
    background: color-mix(in srgb, var(--signal-success) 14%, transparent);
    color: var(--pill-text-success);
  }

  .dashboard-pill-danger {
    background: color-mix(in srgb, var(--signal-danger) 14%, transparent);
    color: var(--pill-text-danger);
  }

  @media (min-width: 768px) {
    .dashboard-list-item {
      grid-template-columns: minmax(0, 1fr) auto;
      align-items: center;
    }

    .dashboard-list-side {
      justify-items: end;
      text-align: right;
    }
  }
</style>
