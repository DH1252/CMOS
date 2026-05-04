<script>
  import { onMount, tick } from 'svelte';
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import { loadExternalScript } from '$lib/external-assets.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Kalender Timeline',
    description = '',
    listAction = null,
    createAction = null,
    eventsUrl = '',
    locale = 'id',
    legend = [],
  } = $props();

  let calendarElement = $state(null);
  let calendarInstance;
  let selectedEvent = $state(null);
  let initError = $state('');
  let isLoadingCalendar = $state(true);
  const fullCalendarScriptUrl = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js';

  const formatDate = (value) => {
    if (!value) {
      return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }).format(value);
  };

  const formatRange = (start, end) => {
    if (!start) {
      return '-';
    }

    if (!end) {
      return formatDate(start);
    }

    const adjustedEnd = new Date(end);
    adjustedEnd.setDate(adjustedEnd.getDate() - 1);

    if (formatDate(start) === formatDate(adjustedEnd)) {
      return formatDate(start);
    }

    return `${formatDate(start)} - ${formatDate(adjustedEnd)}`;
  };

  const closeEvent = () => {
    selectedEvent = null;
  };

  const parseRgb = (value) => {
    if (!value) {
      return null;
    }

    const match = value.match(/rgba?\(([^)]+)\)/i);
    if (!match) {
      return null;
    }

    const parts = match[1].split(',').map((part) => Number(part.trim()));
    const [r, g, b, a = 1] = parts;
    return { r, g, b, a };
  };

  const blendWithWhite = (color, amount = 0.52) => ({
    r: Math.round(color.r + (255 - color.r) * amount),
    g: Math.round(color.g + (255 - color.g) * amount),
    b: Math.round(color.b + (255 - color.b) * amount),
  });

  const blendWithBlack = (color, amount = 0.18) => ({
    r: Math.round(color.r * (1 - amount)),
    g: Math.round(color.g * (1 - amount)),
    b: Math.round(color.b * (1 - amount)),
  });

  const srgbLuminance = ({ r, g, b }) => {
    const convert = (channel) => {
      const c = channel / 255;
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    };

    return 0.2126 * convert(r) + 0.7152 * convert(g) + 0.0722 * convert(b);
  };

  const contrastRatio = (foreground, background) => {
    const lighter = Math.max(srgbLuminance(foreground), srgbLuminance(background));
    const darker = Math.min(srgbLuminance(foreground), srgbLuminance(background));

    return (lighter + 0.05) / (darker + 0.05);
  };

  const ensureEventContrast = (eventElement) => {
    const background = parseRgb(getComputedStyle(eventElement).backgroundColor);
    if (!background) {
      return;
    }

    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
    const softened = isDarkTheme ? blendWithBlack(background, 0.22) : blendWithWhite(background, 0.72);
    const border = isDarkTheme ? blendWithWhite(background, 0.12) : blendWithBlack(background, 0.08);
    const darkText = { r: 36, g: 30, b: 42 };
    const lightText = { r: 245, g: 242, b: 235 };
    const darkContrast = contrastRatio(darkText, softened);
    const lightContrast = contrastRatio(lightText, softened);
    const text = darkContrast >= lightContrast ? darkText : lightText;
    const textColor = `rgb(${text.r}, ${text.g}, ${text.b})`;

    eventElement.style.backgroundColor = `rgb(${softened.r}, ${softened.g}, ${softened.b})`;
    eventElement.style.borderColor = `rgb(${border.r}, ${border.g}, ${border.b})`;
    eventElement.style.color = textColor;
    eventElement.querySelectorAll('*').forEach((node) => {
      node.style.color = textColor;
    });
  };

  const legendStyle = (color) => `--timeline-legend-color:${color};`;

  const buildEventDetail = (event) => {
    const props = event.extendedProps || {};

    return {
      title: event.title.replace(/^(?:📋|✅)\s/u, ''),
      range: formatRange(event.start, event.end),
      description: props.description || '',
      url: event.url || '',
      accent: event.backgroundColor || event.borderColor || 'var(--brand-secondary)',
      detailRows:
        props.type === 'timeline'
          ? [
              { label: 'Tipe', value: props.timeline_type || '-' },
              ...(props.department ? [{ label: 'Departemen', value: props.department }] : []),
              ...(props.program ? [{ label: 'Program', value: props.program }] : []),
            ]
          : props.type === 'program'
            ? [
                { label: 'Departemen', value: props.department || '-' },
                { label: 'Status', value: props.status || '-' },
              ]
            : [
                { label: 'Status', value: props.status || '-' },
                { label: 'Prioritas', value: props.priority || '-' },
                { label: 'Progress', value: `${props.progress || 0}%` },
              ],
      actionLabel: props.type === 'task' ? 'Buka Task' : 'Lihat Detail',
    };
  };

  onMount(() => {
    let active = true;

    const initializeCalendar = async () => {
      try {
        await loadExternalScript(fullCalendarScriptUrl, 'FullCalendar');

        if (!active) {
          return;
        }

        const FullCalendar = window.FullCalendar;

        if (!FullCalendar?.Calendar) {
          initError = 'Kalender tidak dapat dimuat saat ini.';
          isLoadingCalendar = false;
          return;
        }

        isLoadingCalendar = false;
        await tick();

        if (!active || !calendarElement) {
          initError = 'Kalender tidak dapat dimuat saat ini.';
          return;
        }

        calendarInstance = new FullCalendar.Calendar(calendarElement, {
          initialView: 'dayGridMonth',
          locale,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth',
          },
          buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            list: 'Daftar',
          },
          events: eventsUrl,
          eventDidMount: (info) => {
            ensureEventContrast(info.el);
          },
          eventClick: (info) => {
            info.jsEvent.preventDefault();
            selectedEvent = buildEventDetail(info.event);
          },
        });

        calendarInstance.render();
      } catch (error) {
        console.error('Failed to load FullCalendar assets.', error);

        if (active) {
          initError = 'Kalender tidak dapat dimuat saat ini.';
        }
      } finally {
        if (active && initError) {
          isLoadingCalendar = false;
        }
      }
    };

    void initializeCalendar();

    return () => {
      active = false;
      calendarInstance?.destroy();
      calendarInstance = null;
    };
  });
</script>

<Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="timeline-calendar-head border-b border-border/70 pb-4">
    <div class="timeline-calendar-copy">
      <PageHeader {title} {description} icon="fas fa-calendar-days" />
    </div>

    <div class="timeline-calendar-actions">
      {#if listAction}
        <Button href={listAction.href} variant="secondary" size="sm">
          <i class={listAction.icon || 'fas fa-list'}></i>
          <span>{listAction.label}</span>
        </Button>
      {/if}

      {#if createAction}
        <Button href={createAction.href} size="sm">
          <i class={createAction.icon || 'fas fa-plus'}></i>
          <span>{createAction.label}</span>
        </Button>
      {/if}
    </div>
  </Card.Header>

  <Card.Content class="pt-5">
    <div class="timeline-calendar-legend">
      {#each legend as item, index (item.label || index)}
        <div class="timeline-calendar-legend-item" style={legendStyle(item.color)}>
          <span class="timeline-calendar-legend-dot" style={`background:${item.color};`}></span>
          <span>{item.label}</span>
        </div>
      {/each}
    </div>

    {#if initError}
      <EmptyStatePanel title="Kalender gagal dimuat" text={initError} icon="fas fa-calendar-xmark" tone="secondary" compact={true} />
    {:else}
      {#if isLoadingCalendar}
        <EmptyStatePanel title="Memuat kalender" text="Memuat..." icon="fas fa-spinner fa-spin" tone="secondary" compact={true} />
      {/if}

      <div class={`timeline-calendar-surface ${isLoadingCalendar ? 'timeline-calendar-surface-hidden' : ''}`} bind:this={calendarElement}></div>
    {/if}
  </Card.Content>
</Card.Root>

{#if selectedEvent}
  <div class="timeline-modal-overlay">
    <button type="button" class="timeline-modal-backdrop" aria-label="Tutup detail event" onclick={closeEvent}></button>
    <div class="timeline-modal" style={`--timeline-accent:${selectedEvent.accent};`} role="dialog" aria-modal="true" aria-labelledby="timeline-event-title">
      <div class="timeline-modal-head">
        <div class="timeline-modal-head-copy">
          <PageHeader title={selectedEvent.title} description={selectedEvent.range} icon="fas fa-calendar-check" compact={true} headingTag="h4" />
        </div>
        <Button type="button" variant="secondary" size="icon-sm" aria-label="Tutup" onclick={closeEvent}>
          <i class="fas fa-times"></i>
        </Button>
      </div>

      <div class="timeline-modal-body">
        {#if selectedEvent.description}
          <p class="timeline-modal-description">{selectedEvent.description}</p>
        {/if}

        <div class="timeline-modal-meta">
          {#each selectedEvent.detailRows as row, index (row.label || index)}
            <div class="timeline-modal-meta-item">
              <span>{row.label}</span>
              <strong>{row.value}</strong>
            </div>
          {/each}
        </div>
      </div>

      <div class="timeline-modal-foot">
        <Button type="button" variant="secondary" onclick={closeEvent}>Tutup</Button>
        {#if selectedEvent.url}
          <Button href={selectedEvent.url}>
            <i class="fas fa-arrow-up-right-from-square"></i>
            <span>{selectedEvent.actionLabel}</span>
          </Button>
        {/if}
      </div>
    </div>
  </div>
{/if}

<style>
  .timeline-calendar-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .timeline-calendar-copy {
    min-width: 0;
    flex: 1;
  }

  .timeline-calendar-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
  }

  .timeline-calendar-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .timeline-calendar-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid color-mix(in srgb, var(--timeline-legend-color) 24%, var(--line-soft));
    border-radius: 0.625rem;
    background: color-mix(in srgb, var(--timeline-legend-color) 12%, var(--card));
    color: color-mix(in srgb, var(--timeline-legend-color) 42%, var(--text-strong));
    font-size: 0.88rem;
  }

  .timeline-calendar-legend-dot {
    width: 0.8rem;
    height: 0.8rem;
    border-radius: 999px;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, black 10%, transparent);
  }

  .timeline-calendar-surface {
    min-height: 42rem;
  }

  .timeline-calendar-surface-hidden {
    display: none;
  }

  :global(.fc) {
    --fc-border-color: color-mix(in srgb, var(--line-soft) 84%, transparent);
    --fc-page-bg-color: transparent;
    --fc-neutral-bg-color: color-mix(in srgb, var(--panel-bg) 94%, white);
    color: var(--text-strong);
  }

  :global(.fc .fc-toolbar-title) {
    font-weight: 600;
    font-size: 1.18rem;
  }

  :global(.fc .fc-button) {
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background-color: var(--background);
    background-image: none;
    color: var(--text-strong);
    box-shadow: none;
  }

  :global(.fc .fc-button:hover) {
    border-color: var(--line-strong);
    background: color-mix(in srgb, var(--brand-light) 18%, var(--background));
    color: var(--text-strong);
  }

  :global(.fc .fc-button-primary:not(:disabled).fc-button-active),
  :global(.fc .fc-button-primary:not(:disabled):active) {
	border-color: color-mix(in srgb, var(--brand-secondary) 56%, var(--line-strong));
	background: color-mix(in srgb, var(--brand-secondary) 68%, black 32%);
	color: var(--white-soft);
  }

  :global(.fc .fc-list-day-cushion) {
    background: color-mix(in srgb, var(--brand-secondary-soft) 50%, white);
    color: var(--brand-secondary);
  }

  :global(.fc .fc-button:focus) {
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 14%, transparent);
  }

  :global(.fc .fc-scrollgrid),
  :global(.fc .fc-theme-standard td),
  :global(.fc .fc-theme-standard th) {
    border-color: color-mix(in srgb, var(--line-soft) 84%, transparent);
  }

  :global(.fc .fc-col-header-cell) {
    background: color-mix(in srgb, var(--panel-bg) 92%, white);
  }

  :global(.fc .fc-daygrid-day-frame) {
    padding: 0.2rem;
  }

  :global(.fc .fc-daygrid-day:hover) {
    background: color-mix(in srgb, var(--brand-light) 12%, transparent);
  }

  :global(.fc .fc-daygrid-day.fc-day-today) {
	background: color-mix(in srgb, var(--brand-light) 20%, var(--panel-bg-strong));
	box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--brand-primary) 42%, transparent);
  }

  :global(.fc .fc-event) {
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    padding: 0.2rem 0.45rem;
    font-size: 0.78rem;
    cursor: pointer;
    color: var(--text-strong) !important;
    box-shadow: none;
  }

  :global(.fc .fc-event:hover) {
    filter: saturate(1.05);
  }

  :global(.fc .fc-col-header-cell-cushion),
  :global(.fc .fc-daygrid-day-number) {
    color: var(--text-soft);
  }

  :global(.fc .fc-daygrid-day-number) {
	font-weight: 600;
  }

  :global(.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number) {
	color: var(--text-strong);
  }

  .timeline-modal-overlay {
    position: fixed;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 1rem;
    background: rgba(10, 10, 18, 0.55);
    z-index: 1200;
  }

  .timeline-modal-backdrop {
    position: absolute;
    inset: 0;
    border: none;
    background: transparent;
    padding: 0;
  }

  .timeline-modal {
    position: relative;
    z-index: 1;
    width: min(100%, 30rem);
    border-radius: 0.625rem;
    background: var(--card);
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .timeline-modal-head,
  .timeline-modal-foot {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--line-soft);
  }

  .timeline-modal-head {
    background: color-mix(in srgb, var(--timeline-accent) 10%, var(--card));
  }

  .timeline-modal-head-copy {
    min-width: 0;
    flex: 1;
  }

  .timeline-modal-body {
    padding: 1rem 1.15rem;
  }

  .timeline-modal-description {
    margin: 0 0 0.9rem;
    color: var(--text-soft);
  }

  .timeline-modal-meta {
    display: grid;
    gap: 0.75rem;
  }

  .timeline-modal-meta-item {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 0.85rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--timeline-accent) 8%, var(--background));
    border: 1px solid color-mix(in srgb, var(--timeline-accent) 18%, var(--line-soft));
  }

  .timeline-modal-meta-item span {
    color: var(--text-muted);
  }

  .timeline-modal-meta-item strong {
    color: color-mix(in srgb, var(--timeline-accent) 50%, var(--text-strong));
  }

  .timeline-modal-foot {
    border-bottom: none;
    border-top: 1px solid var(--line-soft);
  }

  .timeline-modal-foot :global([data-slot='button'].button-variant-default) {
    background: var(--timeline-accent);
    border-color: color-mix(in srgb, var(--timeline-accent) 72%, black);
    color: var(--white-soft);
  }

  @media (max-width: 767px) {
    .timeline-calendar-head,
    .timeline-modal-head,
    .timeline-modal-foot {
      flex-direction: column;
    }

    .timeline-calendar-surface {
      min-height: 34rem;
    }

    :global(.fc .fc-toolbar) {
      flex-direction: column;
      gap: 0.75rem;
    }
  }
</style>
