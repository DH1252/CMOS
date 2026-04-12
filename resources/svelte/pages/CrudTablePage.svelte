<script>
  import * as Card from '$lib/components/ui/card/index.js';
  import DataTable from '../components/DataTable.svelte';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Daftar',
    description = '',
    icon = 'fas fa-table',
    primaryAction = null,
    secondaryAction = null,
    columns = [],
    rows = [],
    emptyState = {
      title: 'Belum ada data',
      text: 'Data akan muncul di halaman ini setelah dibuat.',
    },
    csrfToken = '',
    enableDataTable = false,
  } = $props();

</script>

<Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} {icon} {primaryAction} {secondaryAction} />
  </Card.Header>

  <Card.Content class="px-0 pb-0">
    <DataTable {columns} {rows} {emptyState} {csrfToken} {enableDataTable} />
  </Card.Content>
</Card.Root>
