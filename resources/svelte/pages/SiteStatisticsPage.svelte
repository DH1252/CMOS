<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import { Button } from "$lib/components/ui/button/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import MetricCard from "../components/MetricCard.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";
  import axios from "axios";

  let {
    title = "Statistik Situs",
    description = "",
    stats = [],
    visitorTrend = [],
    topUrls = [],
    recentVisitors = [],
    competitionSettings = {
      spreadsheetUrl: "",
      scheduleInfo: "",
      lastFetched: null,
      hasApiKey: false,
    },
  } = $props();

  let isFetching = $state(false);
  let diagnosticLog = $state("");
  let lastSyncTime = $state(null);

  let isEditing = $state(false);
  let editSpreadsheetUrl = $state("");
  let editScheduleTime = $state("");
  let isSaving = $state(false);

  let currentSpreadsheetUrl = $state("");
  let currentScheduleTime = $state("01:00");
  let currentScheduleInfo = $state("");

  $effect(() => {
    currentSpreadsheetUrl = competitionSettings.spreadsheetUrl;
    currentScheduleTime = competitionSettings.scheduleTime || "01:00";
    currentScheduleInfo = competitionSettings.scheduleInfo;
  });

  async function saveSettings() {
    if (isSaving) {
      return;
    }

    if (!editSpreadsheetUrl || !editSpreadsheetUrl.startsWith("http")) {
      notify("Peringatan", "URL Spreadsheet tidak valid.", "warning");
      return;
    }

    if (
      !editScheduleTime ||
      !editScheduleTime.match(/^(?:[01]\d|2[0-3]):[0-5]\d$/)
    ) {
      notify(
        "Peringatan",
        "Format waktu tidak valid (harus HH:MM).",
        "warning",
      );
      return;
    }

    isSaving = true;
    try {
      const response = await axios.post("/statistics/settings", {
        spreadsheetUrl: editSpreadsheetUrl,
        scheduleTime: editScheduleTime,
      });

      if (response.data.success) {
        notify(
          "Sukses",
          response.data.message || "Pengaturan berhasil disimpan.",
          "success",
        );
        currentSpreadsheetUrl = editSpreadsheetUrl;
        currentScheduleTime = editScheduleTime;
        currentScheduleInfo = `Setiap hari pukul ${editScheduleTime} WIB`;
        isEditing = false;
      } else {
        notify("Gagal", "Gagal menyimpan pengaturan.", "error");
      }
    } catch (err) {
      console.error(err);
      notify(
        "Gagal",
        err.response?.data?.message || "Terjadi kesalahan server.",
        "error",
      );
    } finally {
      isSaving = false;
    }
  }

  const notify = (titleText, text, icon = "success") => {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: "top-end",
        icon,
        title: titleText,
        text,
        showConfirmButton: false,
        timer: 3000,
      });
      return;
    }
    alert(`${titleText}: ${text}`);
  };

  async function triggerManualSync() {
    if (isFetching) {
      return;
    }

    isFetching = true;
    diagnosticLog =
      "[System]: Memulai sinkronisasi kompetisi dari Google Sheets...\n";

    try {
      const response = await axios.post("/statistics/fetch-competitions");
      const { success, output } = response.data;

      diagnosticLog += output || "";

      if (success) {
        lastSyncTime = new Date().toISOString();
        diagnosticLog +=
          "\n[System]: Sukses! Sinkronisasi kompetisi selesai dilakukan.";
        notify("Sukses", "Data kompetisi berhasil diperbarui!", "success");
      } else {
        diagnosticLog += "\n[System]: Error! Sinkronisasi gagal.";
        notify(
          "Gagal",
          "Proses sinkronisasi kompetisi menemui kegagalan.",
          "error",
        );
      }
    } catch (err) {
      console.error(err);
      diagnosticLog += `\n[System]: Exception terjadi saat memanggil endpoint: ${err.message}`;
      notify("Gagal", "Terjadi kesalahan jaringan atau server.", "error");
    } finally {
      isFetching = false;
    }
  }

  const fallbackPalette = {
    primary: "#7c3aed",
    info: "#2563eb",
    warning: "#d97706",
    success: "#059669",
    secondary: "#64748b",
  };

  const colorMap = {
    primary: "--brand-primary",
    info: "--signal-info",
    warning: "--signal-warning",
    success: "--signal-success",
    secondary: "--text-muted",
  };

  const resolveToneColor = (tone) => {
    if (typeof document === "undefined") {
      return fallbackPalette[tone] || fallbackPalette.secondary;
    }

    const variable = colorMap[tone] || colorMap.secondary;
    const value = getComputedStyle(document.documentElement)
      .getPropertyValue(variable)
      .trim();
    return value || fallbackPalette[tone] || fallbackPalette.secondary;
  };

  const maxTrendValue = $derived.by(
    () =>
      visitorTrend.reduce(
        (max, item) => Math.max(max, Number(item?.count || 0)),
        0,
      ) || 1,
  );

  const CHART_W = 760;
  const CHART_H = 260;
  const CHART_PAD = { top: 16, right: 16, bottom: 32, left: 36 };

  const chartWidth = $derived(CHART_W - CHART_PAD.left - CHART_PAD.right);
  const chartHeight = $derived(CHART_H - CHART_PAD.top - CHART_PAD.bottom);

  const trendPoints = $derived.by(() => {
    if (!visitorTrend.length) return [];
    const max = maxTrendValue || 1;
    const step =
      visitorTrend.length > 1 ? chartWidth / (visitorTrend.length - 1) : 0;

    return visitorTrend.map((item, i) => {
      const x = CHART_PAD.left + i * step;
      const y =
        CHART_PAD.top + chartHeight * (1 - Number(item.count || 0) / max);
      return { x, y, ...item };
    });
  });

  const trendLinePath = $derived.by(() =>
    trendPoints
      .map(
        (p, i) => `${i === 0 ? "M" : "L"} ${p.x.toFixed(2)} ${p.y.toFixed(2)}`,
      )
      .join(" "),
  );

  const trendAreaPath = $derived.by(() => {
    if (!trendPoints.length) return "";
    const base = CHART_PAD.top + chartHeight;
    const first = trendPoints[0];
    const last = trendPoints[trendPoints.length - 1];

    return (
      `M ${first.x.toFixed(2)} ${base} ` +
      trendPoints
        .map((p) => `L ${p.x.toFixed(2)} ${p.y.toFixed(2)}`)
        .join(" ") +
      ` L ${last.x.toFixed(2)} ${base} Z`
    );
  });

  const trendYTicks = $derived.by(() => {
    const max = maxTrendValue || 1;
    const steps = 4;
    return Array.from({ length: steps + 1 }, (_, i) => {
      const value = Math.round((max / steps) * i);
      const y = CHART_PAD.top + chartHeight * (1 - value / max);
      return { value, y };
    });
  });

  const trendXLabels = $derived.by(() => {
    if (!trendPoints.length) return [];
    const labelEvery = Math.ceil(trendPoints.length / 7);
    return trendPoints.filter((_, i) => i % labelEvery === 0);
  });

  const chartGradientId = "visitor-trend-gradient";
  let hoveredIndex = $state(null);

  const formatVisitedAt = (iso) => {
    if (!iso) return "-";
    try {
      return new Date(iso).toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
      });
    } catch {
      return iso;
    }
  };

  const truncate = (value, length = 64) => {
    if (!value) return "-";
    return value.length > length ? `${value.slice(0, length)}…` : value;
  };
</script>

<Card.Root
  class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} icon="fas fa-chart-line" />
  </Card.Header>
</Card.Root>

<div class="stats-grid">
  {#each stats as stat, index (index)}
    <MetricCard
      label={stat.label}
      value={stat.value}
      icon={stat.icon}
      description={stat.meta || ""}
      tone={stat.tone || "primary"}
    />
  {/each}
</div>

<div class="stats-chart-grid">
  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Tren Pengunjung"
        description="Kunjungan harian 14 hari terakhir."
        icon="fas fa-chart-column"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>

    <Card.Content class="pt-5">
      {#if visitorTrend.length}
        <div class="trend-chart-wrap">
          <svg
            class="trend-chart"
            viewBox={`0 0 ${CHART_W} ${CHART_H}`}
            role="img"
            aria-label="Grafik tren pengunjung harian 14 hari terakhir"
            preserveAspectRatio="xMidYMid meet"
          >
            <defs>
              <linearGradient id={chartGradientId} x1="0" y1="0" x2="0" y2="1">
                <stop
                  offset="0%"
                  stop-color={resolveToneColor("primary")}
                  stop-opacity="0.32"
                />
                <stop
                  offset="100%"
                  stop-color={resolveToneColor("primary")}
                  stop-opacity="0.02"
                />
              </linearGradient>
            </defs>

            {#each trendYTicks as tick, index (index)}
              <line
                x1={CHART_PAD.left}
                y1={tick.y}
                x2={CHART_W - CHART_PAD.right}
                y2={tick.y}
                class="trend-grid-line"
              />
              <text
                x={CHART_PAD.left - 8}
                y={tick.y + 4}
                class="trend-axis-label"
                text-anchor="end">{tick.value}</text
              >
            {/each}

            <path d={trendAreaPath} fill={`url(#${chartGradientId})`} />
            <path
              d={trendLinePath}
              fill="none"
              stroke={resolveToneColor("primary")}
              stroke-width="2.5"
              stroke-linejoin="round"
              stroke-linecap="round"
            />

            {#each trendPoints as point, index (index)}
              <g
                class="trend-point-group"
                role="button"
                tabindex="0"
                onmouseenter={() => (hoveredIndex = index)}
                onmouseleave={() => (hoveredIndex = null)}
                onfocus={() => (hoveredIndex = index)}
                onblur={() => (hoveredIndex = null)}
              >
                <rect
                  x={point.x - 12}
                  y={CHART_PAD.top}
                  width="24"
                  height={chartHeight}
                  fill="transparent"
                />
                <circle
                  cx={point.x}
                  cy={point.y}
                  r={hoveredIndex === index ? 5 : 3.5}
                  class="trend-point"
                  fill={resolveToneColor("primary")}
                />
              </g>
            {/each}

            {#each trendXLabels as point, index (index)}
              <text
                x={point.x}
                y={CHART_H - 8}
                class="trend-axis-label"
                text-anchor="middle">{point.label}</text
              >
            {/each}

            {#if hoveredIndex !== null && trendPoints[hoveredIndex]}
              <g class="trend-tooltip">
                <rect
                  x={Math.min(
                    Math.max(trendPoints[hoveredIndex].x - 40, CHART_PAD.left),
                    CHART_W - CHART_PAD.right - 80,
                  )}
                  y={Math.max(trendPoints[hoveredIndex].y - 38, CHART_PAD.top)}
                  width="80"
                  height="28"
                  rx="6"
                  class="trend-tooltip-bg"
                />
                <text
                  x={Math.min(
                    Math.max(trendPoints[hoveredIndex].x - 40, CHART_PAD.left),
                    CHART_W - CHART_PAD.right - 80,
                  ) + 40}
                  y={Math.max(trendPoints[hoveredIndex].y - 38, CHART_PAD.top) +
                    18}
                  class="trend-tooltip-text"
                  text-anchor="middle"
                  >{trendPoints[hoveredIndex].count} kunjungan</text
                >
              </g>
            {/if}
          </svg>
        </div>
      {:else}
        <EmptyStatePanel
          title="Belum ada data kunjungan"
          text="Belum ada data pengunjung yang tercatat."
          icon="fas fa-chart-column"
          tone="secondary"
          compact={true}
        />
      {/if}
    </Card.Content>
  </Card.Root>

  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Halaman Terpopuler"
        description="URL dengan kunjungan terbanyak."
        icon="fas fa-ranking-star"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>

    <Card.Content class="pt-5">
      {#if topUrls.length}
        <ol class="url-list">
          {#each topUrls as item, index (index)}
            <li class="url-item">
              <span class="url-rank">#{index + 1}</span>
              <div class="url-copy">
                <span class="url-text" title={item.url}>
                  {truncate(item.url, 72)}
                </span>
              </div>
              <span class="url-count">{item.visits}</span>
            </li>
          {/each}
        </ol>
      {:else}
        <EmptyStatePanel
          title="Belum ada URL tercatat"
          text="Belum ada data halaman yang dikunjungi."
          icon="fas fa-link"
          tone="secondary"
          compact={true}
        />
      {/if}
    </Card.Content>
  </Card.Root>
</div>

<!-- Competition Script Settings & Control Panel -->
<Card.Root
  class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader
      title="Sinkronisasi & Pengaturan Script Kompetisi"
      description="Pengaturan dan diagnosis script parser data kompetisi dari Google Sheets."
      icon="fas fa-database"
      compact={true}
      headingTag="h3"
    />
  </Card.Header>

  <Card.Content class="pt-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Settings Info -->
      <div class="flex flex-col gap-4">
        {#if !isEditing}
          <div class="flex flex-col gap-1 border-b border-border/70 pb-3">
            <span
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              >Spreadsheet Target</span
            >
            <a
              href={currentSpreadsheetUrl}
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm font-semibold text-primary hover:underline flex items-center gap-1.5 break-all mt-1"
            >
              <span>Buka Google Sheets</span>
              <i class="fas fa-external-link-alt text-xs"></i>
            </a>
          </div>

          <div class="flex flex-col gap-1 border-b border-border/70 pb-3">
            <span
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              >Jadwal Sync Otomatis</span
            >
            <span class="text-sm font-semibold mt-1">
              {currentScheduleInfo || "Setiap hari pukul 01:00 WIB"}
            </span>
          </div>

          <div class="flex flex-col gap-1 border-b border-border/70 pb-3">
            <span
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              >Status Google API Key</span
            >
            <div class="mt-1 flex">
              {#if competitionSettings.hasApiKey}
                <StatusBadge
                  label="Google API Key Aktif"
                  tone="success"
                  icon="fas fa-check-circle text-xs"
                />
              {:else}
                <StatusBadge
                  label="API Key Tidak Ditemukan (Menggunakan Fallback)"
                  tone="warning"
                  icon="fas fa-exclamation-triangle text-xs"
                />
              {/if}
            </div>
          </div>

          <div class="flex flex-col gap-1 pb-3">
            <span
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              >Sinkronisasi Terakhir</span
            >
            <span class="text-sm font-semibold mt-1">
              {lastSyncTime
                ? formatVisitedAt(lastSyncTime)
                : competitionSettings.lastFetched
                  ? formatVisitedAt(competitionSettings.lastFetched)
                  : "Belum pernah dilakukan"}
            </span>
          </div>

          <Button
            variant="outline"
            class="self-start flex items-center gap-1.5"
            onclick={() => {
              editSpreadsheetUrl = currentSpreadsheetUrl;
              editScheduleTime = currentScheduleTime;
              isEditing = true;
            }}
          >
            <i class="fas fa-edit text-xs"></i>
            <span>Ubah Pengaturan</span>
          </Button>
        {:else}
          <div class="flex flex-col gap-1.5">
            <label
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              for="spreadsheetUrl">Spreadsheet Target URL</label
            >
            <input
              id="spreadsheetUrl"
              type="text"
              bind:value={editSpreadsheetUrl}
              class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary disabled:opacity-50"
              disabled={isSaving}
              placeholder="https://docs.google.com/spreadsheets/d/.../edit"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <label
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              for="scheduleTime">Jadwal Sync Otomatis (HH:MM WIB)</label
            >
            <input
              id="scheduleTime"
              type="text"
              bind:value={editScheduleTime}
              class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary disabled:opacity-50"
              disabled={isSaving}
              placeholder="e.g. 01:00"
            />
          </div>

          <div class="flex items-center gap-2 mt-2">
            <Button
              onclick={saveSettings}
              disabled={isSaving}
              class="flex items-center gap-1.5"
            >
              {#if isSaving}
                <i class="fas fa-spinner fa-spin animate-spin"></i>
                <span>Menyimpan...</span>
              {:else}
                <i class="fas fa-save text-xs"></i>
                <span>Simpan</span>
              {/if}
            </Button>
            <Button
              variant="outline"
              onclick={() => (isEditing = false)}
              disabled={isSaving}
            >
              Batal
            </Button>
          </div>
        {/if}
      </div>

      <!-- Sync Controls & Logs -->
      <div class="flex flex-col justify-between">
        <div>
          <h4 class="text-sm font-bold text-foreground">Sinkronisasi Manual</h4>
          <p class="text-xs text-muted-foreground mt-1 leading-relaxed">
            Anda dapat memicu sinkronisasi manual untuk memperbarui data
            kompetisi di cache lokal (`storage/app/competitions.json`) secara
            instan.
          </p>

          <Button
            onclick={triggerManualSync}
            disabled={isFetching}
            class="mt-4 flex items-center gap-2"
          >
            {#if isFetching}
              <i class="fas fa-spinner fa-spin animate-spin"></i>
              <span>Memproses Sinkronisasi...</span>
            {:else}
              <i class="fas fa-sync-alt"></i>
              <span>Sinkronisasi Sekarang</span>
            {/if}
          </Button>
        </div>

        {#if diagnosticLog}
          <div class="mt-6">
            <span
              class="text-xs font-bold text-muted-foreground uppercase tracking-widest"
              >Logs / Diagnostik</span
            >
            <pre class="diagnostic-console">{diagnosticLog}</pre>
          </div>
        {/if}
      </div>
    </div>
  </Card.Content>
</Card.Root>

<Card.Root
  class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader
      title="Pengunjung Terbaru"
      description="Kunjungan situs yang paling baru tercatat."
      icon="fas fa-clock-rotate-left"
      compact={true}
      headingTag="h3"
    />
  </Card.Header>

  <Card.Content class="px-0 pb-0">
    {#if recentVisitors.length}
      <div class="recent-table" role="table">
        <div class="recent-row recent-head" role="row">
          <span class="recent-cell recent-ip" role="columnheader">IP</span>
          <span class="recent-cell recent-url" role="columnheader">URL</span>
          <span class="recent-cell recent-agent" role="columnheader"
            >User Agent</span
          >
          <span class="recent-cell recent-time" role="columnheader">Waktu</span>
        </div>
        {#each recentVisitors as visitor, index (index)}
          <div class="recent-row" role="row">
            <span class="recent-cell recent-ip" role="cell">
              {truncate(visitor.ip, 24)}
            </span>
            <span
              class="recent-cell recent-url"
              role="cell"
              title={visitor.url}
            >
              {truncate(visitor.url, 56)}
            </span>
            <span
              class="recent-cell recent-agent"
              role="cell"
              title={visitor.userAgent}
            >
              {truncate(visitor.userAgent, 48)}
            </span>
            <span class="recent-cell recent-time" role="cell">
              {formatVisitedAt(visitor.visitedAt)}
            </span>
          </div>
        {/each}
      </div>
    {:else}
      <div class="px-4 py-5">
        <EmptyStatePanel
          title="Belum ada kunjungan"
          text="Belum ada aktivitas pengunjung yang tercatat."
          icon="fas fa-clock-rotate-left"
          tone="secondary"
          compact={true}
        />
      </div>
    {/if}
  </Card.Content>
</Card.Root>

<style>
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
  }

  .diagnostic-console {
    background: #111;
    color: #38bdf8;
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    padding: 1rem;
    border-radius: 0.75rem;
    overflow-y: auto;
    max-height: 240px;
    margin-top: 0.5rem;
    border: 1px solid var(--line-soft);
    white-space: pre-wrap;
    word-break: break-all;
  }

  .stats-chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
  }

  .trend-chart-wrap {
    width: 100%;
    overflow: hidden;
  }

  .trend-chart {
    display: block;
    width: 100%;
    height: auto;
  }

  .trend-grid-line {
    stroke: var(--line-soft);
    stroke-width: 1;
    stroke-dasharray: 3 4;
    opacity: 0.6;
  }

  .trend-axis-label {
    fill: var(--text-muted);
    font-size: 10px;
    font-family: inherit;
  }

  .trend-point {
    transition: r 0.15s ease;
    filter: drop-shadow(
      0 0 4px color-mix(in srgb, var(--brand-primary) 40%, transparent)
    );
  }

  .trend-point-group:focus-visible {
    outline: none;
  }

  .trend-point-group:focus-visible circle {
    stroke: var(--foreground);
    stroke-width: 2;
  }

  .trend-tooltip-bg {
    fill: var(--foreground);
    opacity: 0.92;
  }

  .trend-tooltip-text {
    fill: var(--background);
    font-size: 11px;
    font-weight: 600;
    font-family: inherit;
  }

  .url-list {
    display: grid;
    gap: 0.6rem;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .url-item {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.75rem;
    border-radius: 0.5rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .url-rank {
    width: 1.9rem;
    height: 1.9rem;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: var(--card);
    color: var(--text-soft);
    font-size: 0.75rem;
    font-weight: 700;
  }

  .url-copy {
    min-width: 0;
  }

  .url-text {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.82rem;
  }

  .url-count {
    min-width: 2.5rem;
    padding: 0.3rem 0.55rem;
    border-radius: 0.45rem;
    background: color-mix(in srgb, var(--brand-primary) 12%, transparent);
    color: var(--brand-hover);
    font-weight: 700;
    text-align: center;
    font-size: 0.8rem;
  }

  .recent-table {
    display: flex;
    flex-direction: column;
  }

  .recent-row {
    display: grid;
    grid-template-columns:
      minmax(120px, 0.8fr) minmax(0, 1.4fr) minmax(0, 1.1fr)
      minmax(0, 0.8fr);
    gap: 0.75rem;
    padding: 0.65rem 1rem;
    border-bottom: 1px solid var(--line-soft);
  }

  .recent-row:last-child {
    border-bottom: none;
  }

  .recent-head {
    background: var(--muted);
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-soft);
  }

  .recent-cell {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.82rem;
  }

  .recent-time {
    color: var(--text-muted);
  }

  @media (max-width: 900px) {
    .recent-agent {
      display: none;
    }

    .recent-row {
      grid-template-columns: minmax(110px, 0.8fr) minmax(0, 1.6fr) minmax(
          0,
          0.9fr
        );
    }
  }

  @media (max-width: 640px) {
    .recent-row {
      grid-template-columns: 1fr;
      gap: 0.2rem;
    }

    .recent-head {
      display: none;
    }
  }
</style>
