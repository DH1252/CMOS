<script>
  import { onMount } from 'svelte';
  import * as Table from '$lib/components/ui/table/index.js';
  import EmptyStatePanel from './EmptyStatePanel.svelte';
  import TableCell from './TableCell.svelte';

  let {
    columns = [],
    rows = [],
    emptyState = {
      title: 'Belum ada data',
      text: 'Data akan muncul di halaman ini setelah dibuat.',
    },
    csrfToken = '',
    enableDataTable = false,
    tableClass = '',
  } = $props();

  let tableRef = $state(null);
  let dataTable;

  onMount(() => {
    if (!enableDataTable || !rows.length || !tableRef || !window.$?.fn?.DataTable) {
      return undefined;
    }

    if (window.$.fn.DataTable.isDataTable(tableRef)) {
      return undefined;
    }

    dataTable = window.$(tableRef).DataTable({
      responsive: true,
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(filter dari _MAX_ total data)',
        zeroRecords: 'Tidak ada data yang cocok',
        paginate: {
          first: 'Pertama',
          last: 'Terakhir',
          next: 'Selanjutnya',
          previous: 'Sebelumnya',
        },
      },
      dom: '<"d-flex justify-between align-center mb-3"lf>rt<"d-flex justify-between align-center mt-3"ip>',
      pageLength: 10,
    });

    return () => {
      dataTable?.destroy?.();
    };
  });
</script>

<div class="cn-data-table">
  <Table.Root bind:ref={tableRef} class={`cn-data-table-table ${enableDataTable ? 'datatable' : ''} ${tableClass}`.trim()}>
    <Table.Header>
      <Table.Row class="border-border bg-muted hover:bg-muted">
        {#each columns as column, columnIndex (column.key || column.label || columnIndex)}
          <Table.Head style={column.width ? `width:${column.width}` : undefined} class={column.className || ''}>
            {column.label}
          </Table.Head>
        {/each}
      </Table.Row>
    </Table.Header>

    <Table.Body>
      {#if rows.length === 0 && !enableDataTable}
        <Table.Row class="hover:bg-transparent">
          <Table.Cell colspan={columns.length} class="p-4">
            <EmptyStatePanel
              title={emptyState.title}
              text={emptyState.text}
              icon={emptyState.icon || 'fas fa-inbox'}
              action={emptyState.action || null}
              compact={true}
            />
          </Table.Cell>
        </Table.Row>
      {:else}
        {#each rows as row, rowIndex (row.id || rowIndex)}
          <Table.Row class="border-border/70 hover:bg-muted/50">
            {#each row.cells as cell, cellIndex (cell.id || `${rowIndex}-${cellIndex}`)}
              <Table.Cell class={cell.align === 'right' ? 'text-right' : ''}>
                <TableCell {cell} {csrfToken} />
              </Table.Cell>
            {/each}
          </Table.Row>
        {/each}
      {/if}
    </Table.Body>
  </Table.Root>

  {#if rows.length === 0 && enableDataTable}
    <div class="cn-data-table-empty">
      <EmptyStatePanel
        title={emptyState.title}
        text={emptyState.text}
        icon={emptyState.icon || 'fas fa-inbox'}
        action={emptyState.action || null}
        compact={true}
      />
    </div>
  {/if}
</div>

<style>
  .cn-data-table {
    width: 100%;
  }

  .cn-data-table-empty {
    padding: 1rem;
  }

  :global(.cn-data-table .dataTables_wrapper) {
    padding: 1rem;
  }

  :global(.cn-data-table .dataTables_wrapper .d-flex) {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  :global(.cn-data-table .dataTables_wrapper .dataTables_length),
  :global(.cn-data-table .dataTables_wrapper .dataTables_filter),
  :global(.cn-data-table .dataTables_wrapper .dataTables_info),
  :global(.cn-data-table .dataTables_wrapper .dataTables_paginate) {
    font-size: 0.88rem;
    color: var(--text-muted);
  }

  :global(.cn-data-table .dataTables_wrapper .dataTables_length select),
  :global(.cn-data-table .dataTables_wrapper .dataTables_filter input) {
    margin-left: 0.45rem;
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    padding: 0.45rem 0.7rem;
    background: var(--background);
    color: var(--foreground);
  }

  :global(.cn-data-table .dataTables_wrapper .dataTables_paginate .paginate_button) {
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    padding: 0.35rem 0.7rem;
    margin-left: 0.35rem;
    color: var(--foreground) !important;
    background: var(--background) !important;
  }

  :global(.cn-data-table .dataTables_wrapper .dataTables_paginate .paginate_button.current),
  :global(.cn-data-table .dataTables_wrapper .dataTables_paginate .paginate_button:hover) {
    border-color: color-mix(in srgb, var(--brand-primary) 35%, transparent) !important;
    background: var(--muted) !important;
    color: var(--foreground) !important;
  }

  @media (max-width: 767px) {
    :global(.cn-data-table .dataTables_wrapper) {
      padding: 0.75rem;
    }

    :global(.cn-data-table .dataTables_wrapper .dataTables_length),
    :global(.cn-data-table .dataTables_wrapper .dataTables_filter),
    :global(.cn-data-table .dataTables_wrapper .dataTables_info),
    :global(.cn-data-table .dataTables_wrapper .dataTables_paginate) {
      width: 100%;
    }
  }
</style>
