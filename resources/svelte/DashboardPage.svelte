<script>
  import * as Card from '$lib/components/ui/card/index.js';
  import DataTable from './components/DataTable.svelte';
  import EmptyStatePanel from './components/EmptyStatePanel.svelte';
  import MetricCard from './components/MetricCard.svelte';
  import PageHeader from './components/PageHeader.svelte';

  let {
    user = {},
    stats = {},
    tasksByStatus = {},
    recentTasks = [],
    upcomingTimelines = [],
    staffRanking = [],
    departmentProgress = [],
    monthlyTrends = [],
    myPrograms = [],
    links = {},
    now = {},
  } = $props();

  const currentDateTime = $derived.by(() => {
    const parsed = now?.iso ? new Date(now.iso) : new Date();
    return Number.isNaN(parsed.getTime()) ? new Date() : parsed;
  });

  const hasRanking = $derived.by(() => Array.isArray(staffRanking) && staffRanking.length > 0);
  const hasDepartmentProgress = $derived.by(() => Array.isArray(departmentProgress) && departmentProgress.length > 0);
  const hasMonthlyTrends = $derived.by(() => Array.isArray(monthlyTrends) && monthlyTrends.length > 0);

  const totalTaskCount = $derived.by(
    () => Number(tasksByStatus.todo || 0) + Number(tasksByStatus.in_progress || 0) + Number(tasksByStatus.done || 0),
  );

  const completionRate = $derived.by(() =>
    totalTaskCount ? Math.round((Number(tasksByStatus.done || 0) / totalTaskCount) * 100) : 0,
  );

  const nextTimeline = $derived.by(() => upcomingTimelines?.[0] || null);

  const quickActions = $derived.by(
    () =>
      [
        links?.tasksIndex ? { href: links.tasksIndex, label: 'Buka task' } : null,
        links?.timelinesCalendar ? { href: links.timelinesCalendar, label: 'Lihat timeline' } : null,
        links?.announcementsIndex ? { href: links.announcementsIndex, label: 'Buka pengumuman' } : null,
      ].filter(Boolean),
  );

  const statusRows = $derived.by(() => [
    { label: 'Todo', value: Number(tasksByStatus.todo || 0) },
    { label: 'In Progress', value: Number(tasksByStatus.in_progress || 0) },
    { label: 'Done', value: Number(tasksByStatus.done || 0) },
  ]);

  const metricCards = $derived.by(() => {
    return [
      {
        label: 'Program kerja',
        value: Number(stats.totalPrograms || 0).toLocaleString('id-ID'),
        icon: 'fas fa-diagram-project',
        tone: 'info',
        description: 'Program yang sedang dipantau organisasi.',
      },
      {
        label: 'Task selesai',
        value: Number(stats.completedTasks || 0).toLocaleString('id-ID'),
        icon: 'fas fa-check-circle',
        tone: 'success',
        description: 'Task yang telah ditandai selesai.',
      },
      {
        label: 'Task butuh perhatian',
        value: Number(stats.pendingTasks || 0).toLocaleString('id-ID'),
        icon: 'fas fa-clock',
        tone: Number(stats.overdueTasks || 0) > 0 ? 'danger' : 'warning',
        description:
          Number(stats.overdueTasks || 0) > 0
            ? `${Number(stats.overdueTasks || 0)} task melewati deadline.`
            : 'Tidak ada task terlambat saat ini.',
      },
      {
        label: 'Akun aktif',
        value: Number(stats.totalUsers || 0).toLocaleString('id-ID'),
        icon: 'fas fa-users',
        tone: 'primary',
        description: 'Pengguna yang saat ini tercatat di workspace.',
      },
    ];
  });

  const topStaff = $derived.by(() => (staffRanking || []).slice(0, 5));
  const myProgramsList = $derived.by(() => (myPrograms || []).slice(0, 4));

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

  const statusLabel = (status) => {
    if (status === 'in_progress') {
      return 'In Progress';
    }

    if (status === 'done') {
      return 'Done';
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
    { label: 'Deadline' },
  ];

  const recentTaskRows = $derived.by(() =>
    (recentTasks || []).slice(0, 5).map((task) => ({
      id: task.id,
      cells: [
        {
          type: 'stack',
          lines: [
            { text: (task.title || '').length > 36 ? `${task.title.slice(0, 36)}...` : task.title || '-' },
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
          text: task.is_overdue ? `Overdue - ${formatShortDate(task.deadline)}` : formatShortDate(task.deadline),
          muted: !task.is_overdue,
        },
      ],
    })),
  );

  const scoreForStaff = (staff) => ((Number(staff?.evaluations_avg_total_score) || 0) / 4).toFixed(1);
  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<section class="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_20rem]">
  <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="grid gap-4 p-6">
      <h2 class="m-0 text-lg font-semibold text-foreground">Ringkasan hari ini</h2>

      <div class="grid gap-4 md:grid-cols-3">
        <div>
          <div class="text-sm font-medium text-foreground">Progress task</div>
          <div class="mt-2 text-3xl font-semibold leading-none text-foreground">{completionRate}%</div>
          <p class="mt-2 text-sm leading-6 text-muted-foreground">{totalTaskCount ? `${totalTaskCount} task tercatat dalam dashboard.` : 'Belum ada task yang tercatat.'}</p>
        </div>
        <div>
          <div class="text-sm font-medium text-foreground">Agenda terdekat</div>
          {#if nextTimeline}
            <div class="mt-2 text-lg font-semibold leading-snug text-foreground">{nextTimeline.title}</div>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">{formatLongDate(nextTimeline.start_date)}</p>
          {:else}
            <p class="mt-2 text-sm leading-6 text-muted-foreground">Belum ada agenda mendatang yang perlu dipantau.</p>
          {/if}
        </div>
        <div>
          <div class="text-sm font-medium text-foreground">Waktu lokal</div>
          <div class="mt-2 text-3xl font-semibold leading-none text-foreground">{formatClock(currentDateTime)}</div>
          <p class="mt-2 text-sm leading-6 text-muted-foreground">{formatWeekday(currentDateTime)}, {formatDayNumber(currentDateTime)} {formatMonthYear(currentDateTime)}</p>
        </div>
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="grid gap-4 p-6">
      <h2 class="m-0 text-lg font-semibold text-foreground">Akses cepat</h2>

      <div class="grid gap-3">
        {#each quickActions as action, index (action.href || index)}
          <a href={action.href} class="rounded-[10px] border border-border bg-background px-4 py-3 text-inherit no-underline transition-colors hover:bg-muted">
            <div class="text-sm font-medium text-foreground">{action.label}</div>
          </a>
        {/each}
      </div>
    </Card.Content>
  </Card.Root>
</section>

<div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
  {#each metricCards as card, index (card.label || index)}
    <MetricCard
      label={card.label}
      value={card.value}
      icon={card.icon}
      description={card.description || ''}
      tone={card.tone || 'primary'}
    />
  {/each}
</div>

<div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.18fr)_minmax(0,0.82fr)]">
  <div class="grid gap-4">
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
          <PageHeader title="Yang perlu ditindaklanjuti" description="Status kerja yang perlu dipantau sebelum masuk ke detail task." compact={true} headingTag="h3" />
      </Card.Header>
      <Card.Content class="grid gap-4 pt-5">
        <div class="grid gap-3 sm:grid-cols-3">
          {#each statusRows as item, index (item.label || index)}
            <div class="rounded-[10px] border border-border bg-background px-4 py-4">
              <div class="text-sm text-muted-foreground">{item.label}</div>
              <div class="mt-2 text-2xl font-semibold text-foreground">{item.value}</div>
            </div>
          {/each}
        </div>

        <div class="rounded-[10px] border border-border bg-background px-4 py-4">
          <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-muted-foreground">Penyelesaian total</span>
            <span class="font-semibold text-foreground">{completionRate}%</span>
          </div>
          <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
            <div class="h-full rounded-full bg-brand-primary" style={`width:${completionRate}%`}></div>
          </div>
          <div class="mt-3 text-sm text-muted-foreground">{totalTaskCount ? `${totalTaskCount} task tercatat di workspace.` : 'Belum ada task tercatat.'}</div>
        </div>
      </Card.Content>
    </Card.Root>

    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
          <PageHeader
          title="Perubahan task terbaru"
          description="Masuk ke daftar ini jika perlu memeriksa update terbaru sebelum membuka board penuh."
          action={links?.tasksIndex ? { href: links.tasksIndex, label: 'Lihat semua task', tone: 'secondary' } : null}
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="px-0 pb-0">
        <DataTable
          columns={recentTaskColumns}
          rows={recentTaskRows}
          emptyState={{
            title: 'Tidak ada task',
            text: 'Task terbaru akan muncul setelah ada aktivitas baru.',
            icon: 'fas fa-list-check',
          }}
        />
      </Card.Content>
    </Card.Root>

    {#if hasDepartmentProgress || hasMonthlyTrends}
      <div class="grid gap-4 lg:grid-cols-2">
        <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
          <Card.Header class="border-b border-border pb-4">
            <PageHeader title="Progress departemen" description="Ringkasan per departemen, bukan area kerja utama halaman ini." headingTag="h3" compact={true} />
          </Card.Header>
          <Card.Content class="grid gap-3 pt-5">
            {#if hasDepartmentProgress}
              {#each departmentProgress as item, index (item.name || index)}
                <div class="rounded-[10px] border border-border bg-background px-4 py-4">
                  <div class="flex items-center justify-between gap-3 text-sm">
                    <strong class="text-foreground">{item.name}</strong>
                    <span class="text-muted-foreground">{item.done} / {item.total}</span>
                  </div>
                  <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                    <div class="h-full rounded-full bg-brand-primary" style={`width:${item.total ? Math.round((item.done / item.total) * 100) : 0}%`}></div>
                  </div>
                </div>
              {/each}
            {:else}
              <EmptyStatePanel title="Belum ada data" text="Progress departemen akan muncul di sini." compact={true} />
            {/if}
          </Card.Content>
        </Card.Root>

        <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
          <Card.Header class="border-b border-border pb-4">
            <PageHeader title="Tren bulanan" description="Pola aktivitas bulanan untuk konteks, bukan prioritas utama." headingTag="h3" compact={true} />
          </Card.Header>
          <Card.Content class="grid gap-3 pt-5">
            {#if hasMonthlyTrends}
              {#each monthlyTrends as item, index (item.month || index)}
                <div class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-3 rounded-[10px] border border-border bg-background px-4 py-3 text-sm">
                  <strong class="text-foreground">{item.month}</strong>
                  <span class="text-muted-foreground">Buat {item.created}</span>
                  <span class="text-muted-foreground">Selesai {item.completed}</span>
                </div>
              {/each}
            {:else}
              <EmptyStatePanel title="Belum ada data" text="Tren bulanan akan muncul di sini." compact={true} />
            {/if}
          </Card.Content>
        </Card.Root>
      </div>
    {/if}
  </div>

  <div class="grid gap-4">
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
        <PageHeader
          title="Agenda terdekat"
          description="Agenda yang paling dekat dengan waktu sekarang."
          action={links?.timelinesCalendar ? { href: links.timelinesCalendar, label: 'Buka kalender', tone: 'secondary' } : null}
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="pt-5">
        {#if upcomingTimelines.length === 0}
          <EmptyStatePanel title="Tidak ada timeline" text="Belum ada agenda mendatang yang terjadwal." icon="fas fa-calendar-xmark" compact={true} />
        {:else}
          <div class="grid gap-3">
            {#each upcomingTimelines.slice(0, 4) as timeline, index (timeline.id || index)}
              <div class="rounded-[10px] border border-border bg-background px-4 py-4">
                <strong class="block text-sm text-foreground">{timeline.title}</strong>
                <div class="mt-2 text-sm text-muted-foreground">{formatLongDate(timeline.start_date)}</div>
              </div>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>

    {#if hasRanking}
      <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
        <Card.Header class="border-b border-border pb-4">
          <PageHeader title="Top staff" description="Hasil evaluasi terbaru yang layak dipantau cepat." headingTag="h3" compact={true} />
        </Card.Header>
        <Card.Content class="grid gap-3 pt-5">
          {#each topStaff as staff, index (staff.id || index)}
            <div class="grid grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-3 rounded-[10px] border border-border bg-background px-4 py-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full border border-border text-xs font-semibold text-muted-foreground">{index + 1}</div>
              <img src={staff.avatar_url || fallbackAvatar(staff.name)} alt={staff.name} class="h-9 w-9 rounded-full object-cover" width="36" height="36" loading="lazy" decoding="async" onerror={handleImageError} />
              <div class="min-w-0">
                <strong class="block truncate text-sm text-foreground">{staff.name}</strong>
                <span class="block truncate text-sm text-muted-foreground">{staff.department?.name || 'Organisasi'}</span>
              </div>
              <div class="text-sm font-semibold text-brand-primary">{scoreForStaff(staff)}</div>
            </div>
          {/each}
        </Card.Content>
      </Card.Root>
    {/if}

    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
        <PageHeader
          title="Program yang saya tangani"
          description="Program yang sedang Anda tangani tanpa harus membuka daftar penuh."
          action={links?.programsMy ? { href: links.programsMy, label: 'Lihat semua', tone: 'secondary' } : null}
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="pt-5">
        {#if myProgramsList.length === 0}
          <EmptyStatePanel title="Belum ada program" text="Program kerja Anda akan tampil di sini setelah ditetapkan." compact={true} />
        {:else}
          <div class="grid gap-3">
            {#each myProgramsList as program, index (program.id || index)}
              <div class="rounded-[10px] border border-border bg-background px-4 py-4">
                <strong class="block text-sm text-foreground">{program.name || '-'}</strong>
                <span class="mt-1 block text-sm text-muted-foreground">{program.department?.name || '-'}</span>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                  <div class="h-full rounded-full bg-brand-primary" style={`width:${progressValue(program.progress)}%`}></div>
                </div>
                <div class="mt-2 text-sm text-muted-foreground">{progressValue(program.progress)}% selesai</div>
              </div>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>
</div>
