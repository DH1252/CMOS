<script>
  import { onDestroy, onMount } from 'svelte';
  import * as Card from '$lib/components/ui/card/index.js';
  import DataTable from './components/DataTable.svelte';
  import EmptyStatePanel from './components/EmptyStatePanel.svelte';
  import PageHeader from './components/PageHeader.svelte';

  let {
    stats = {},
    tasksByStatus = {},
    recentTasks = [],
    upcomingTimelines = [],
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

    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
  };

  const formatLongDate = (value) => {
    if (!value) {
      return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return '-';
    }

    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  const formatWeekday = (value) => value.toLocaleDateString('id-ID', { weekday: 'long' });
  const formatMonthYear = (value) => value.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
  const formatClock = (value) => value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  const formatDayNumber = (value) => value.toLocaleDateString('id-ID', { day: '2-digit' });
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

  const recentTaskColumns = [
    { label: 'Task' },
    { label: 'Status' },
    { label: 'Progress' },
    { label: 'Batas waktu' },
  ];

  const recentTaskRows = $derived.by(() =>
    (recentTasks || []).slice(0, 5).map((task) => ({
      id: task.id,
      cells: [
        {
          type: 'stack',
          lines: [
            {
              text: (task.title || '').length > 36 ? `${task.title.slice(0, 36)}...` : task.title || '-',
              href: taskDetailHref(task),
            },
            { text: task.program?.name || '-', muted: true },
          ],
        },
        {
          type: 'badge',
          label: statusLabel(task.status),
          tone: statusTone(task.status_badge, task.status),
        },
        {
          type: 'progress',
          value: progressValue(task.progress),
          label: `${progressValue(task.progress)}%`,
          tone: progressValue(task.progress) >= 100 ? 'success' : 'primary',
        },
        {
          text: task.is_overdue ? `Terlambat - ${formatShortDate(task.deadline)}` : formatShortDate(task.deadline),
          muted: !task.is_overdue,
        },
      ],
    })),
  );
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
            <a href={item.href} aria-label={`${item.actionLabel} - ${item.label}`} class="block rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class={`mt-2 text-2xl font-semibold leading-none ${toneValueClass(item.tone)}`}>{item.value}</div>
              <p class="mt-2 text-sm leading-6 text-muted-foreground">{item.detail}</p>
              <div class="mt-3 text-sm font-medium text-[color:var(--brand-hover)]">{item.actionLabel}</div>
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
            <a href={action.href} class="inline-flex items-center justify-center rounded-[10px] border border-border bg-background px-3.5 py-2.5 text-sm font-medium text-foreground no-underline transition-colors hover:border-brand-primary hover:bg-muted">
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
            <a href={item.href} aria-label={`Buka ${item.label} task`} class="block rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class="mt-2 text-2xl font-semibold text-foreground">{item.value}</div>
              <div class="mt-3 text-sm font-medium text-[color:var(--brand-hover)]">Buka daftar task</div>
            </a>
          {:else}
            <div class="rounded-[10px] border border-border bg-background px-4 py-4">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class="mt-2 text-2xl font-semibold text-foreground">{item.value}</div>
            </div>
          {/if}
        {/each}
      </div>

      <div class="overflow-hidden rounded-[10px] border border-border/70 bg-background/70">
        <DataTable
          columns={recentTaskColumns}
          rows={recentTaskRows}
          emptyState={{
            title: 'Tidak ada task',
            text: 'Perubahan task akan tampil di sini setelah ada aktivitas baru.',
            icon: 'fas fa-list-check',
          }}
        />
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
          {#each upcomingTimelines.slice(0, 4) as timeline, index (timeline.id || index)}
            {#if links?.timelinesCalendar}
              <a href={links.timelinesCalendar} aria-label={`Buka kalender untuk ${timeline.title}`} class="rounded-[10px] border border-border bg-background px-4 py-4 text-inherit no-underline transition-colors hover:border-brand-primary hover:bg-muted/70">
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
</section>
