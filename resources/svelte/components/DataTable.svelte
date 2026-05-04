<script>
  import * as Table from '$lib/components/ui/table/index.js';
  import EmptyStatePanel from './EmptyStatePanel.svelte';
  import TableCell from './TableCell.svelte';

  let {
    columns = [],
    rows = [],
    emptyState = {
      title: 'Belum ada data',
      text: 'Belum ada data.',
    },
    csrfToken = '',
    enableDataTable = false,
    tableClass = '',
  } = $props();

  const pageSizeOptions = [10, 25, 50, -1];

  let searchQuery = $state('');
  let pageSize = $state(10);
  let currentPage = $state(1);

  const normalizedRows = $derived.by(() => {
    const columnCount = columns.length;

    return (rows || []).map((row, rowIndex) => {
      const providedCells = Array.isArray(row?.cells) ? row.cells.slice(0, columnCount) : [];
      const missingCellCount = Math.max(columnCount - providedCells.length, 0);

      const fillerCells = Array.from({ length: missingCellCount }, (_, fillerIndex) => ({
        id: `filler-${row?.id || rowIndex}-${fillerIndex}`,
        text: '',
        muted: true,
      }));

      return {
        ...row,
        cells: [...providedCells, ...fillerCells],
      };
    });
  });

  const extractCellText = (value) => {
    if (value == null) {
      return '';
    }

    if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
      return String(value);
    }

    if (Array.isArray(value)) {
      return value.map(extractCellText).join(' ');
    }

    if (typeof value === 'object') {
      return Object.values(value).map(extractCellText).join(' ');
    }

    return '';
  };

  const searchableRows = $derived.by(() =>
    normalizedRows.map((row) => ({
      ...row,
      searchText: row.cells.map((cell) => extractCellText(cell)).join(' ').toLowerCase(),
    })),
  );

  const filteredRows = $derived.by(() => {
    if (!enableDataTable) {
      return normalizedRows;
    }

    const keyword = searchQuery.trim().toLowerCase();

    if (keyword === '') {
      return searchableRows;
    }

    return searchableRows.filter((row) => row.searchText.includes(keyword));
  });

  const effectivePageSize = $derived.by(() => {
    if (!enableDataTable) {
      return filteredRows.length || 1;
    }

    if (pageSize === -1) {
      return filteredRows.length || 1;
    }

    return pageSize;
  });

  const totalPages = $derived.by(() => Math.max(1, Math.ceil(filteredRows.length / effectivePageSize)));
  const activePage = $derived.by(() => Math.min(Math.max(currentPage, 1), totalPages));

  const paginatedRows = $derived.by(() => {
    if (!enableDataTable) {
      return filteredRows;
    }

    const start = (activePage - 1) * effectivePageSize;
    return filteredRows.slice(start, start + effectivePageSize);
  });

  const currentRows = $derived.by(() => (enableDataTable ? paginatedRows : filteredRows));

  const rangeStart = $derived.by(() => {
    if (filteredRows.length === 0) {
      return 0;
    }

    return enableDataTable ? (activePage - 1) * effectivePageSize + 1 : 1;
  });

  const rangeEnd = $derived.by(() => {
    if (filteredRows.length === 0) {
      return 0;
    }

    return enableDataTable ? Math.min(rangeStart + effectivePageSize - 1, filteredRows.length) : filteredRows.length;
  });

  const handleSearchInput = (event) => {
    searchQuery = event.currentTarget.value;
    currentPage = 1;
  };

  const handlePageSizeChange = (event) => {
    pageSize = Number(event.currentTarget.value);
    currentPage = 1;
  };
</script>

<div class="cn-data-table">
  {#if enableDataTable}
    <div class="cn-data-table-toolbar">
      <label class="cn-data-table-control">
        <span>Cari</span>
        <input
          type="search"
          class="form-control"
          value={searchQuery}
          oninput={handleSearchInput}
          placeholder="Cari data"
        />
      </label>

      <label class="cn-data-table-control cn-data-table-control-small">
        <span>Tampilkan</span>
        <select class="form-select" value={pageSize} onchange={handlePageSizeChange}>
          {#each pageSizeOptions as option (option)}
            <option value={option}>{option === -1 ? 'Semua' : option}</option>
          {/each}
        </select>
      </label>
    </div>
  {/if}

  {#if currentRows.length === 0}
    <div class="cn-data-table-empty">
      <EmptyStatePanel
        title={emptyState.title}
        text={emptyState.text}
        icon={emptyState.icon || 'fas fa-inbox'}
        action={emptyState.action || null}
        compact={true}
      />
    </div>
  {:else}
    <div class="cn-data-table-desktop cn-data-table-table">
      <Table.Root class={tableClass}>
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
          {#each currentRows as row, rowIndex (row.id || rowIndex)}
            <Table.Row class="border-border/70 hover:bg-muted/50">
              {#each row.cells as cell, cellIndex (cell.id || `${rowIndex}-${cellIndex}`)}
                <Table.Cell class={cell.align === 'right' ? 'text-right' : ''}>
                  <TableCell {cell} {csrfToken} />
                </Table.Cell>
              {/each}
            </Table.Row>
          {/each}
        </Table.Body>
      </Table.Root>
    </div>

    <div class="cn-data-table-mobile">
      {#each currentRows as row, rowIndex (row.id || rowIndex)}
        <article class="cn-data-table-card">
          {#each row.cells as cell, cellIndex (cell.id || `${rowIndex}-${cellIndex}`)}
            <div class="cn-data-table-card-row">
              <div class="cn-data-table-card-label">{columns[cellIndex]?.label || `Kolom ${cellIndex + 1}`}</div>
              <div class="cn-data-table-card-value">
                <TableCell {cell} {csrfToken} />
              </div>
            </div>
          {/each}
        </article>
      {/each}
    </div>
  {/if}

  {#if enableDataTable && currentRows.length > 0}
    <div class="cn-data-table-footer">
      <div class="cn-data-table-info">
        Menampilkan {rangeStart}-{rangeEnd} dari {filteredRows.length} data
      </div>

      <div class="cn-data-table-pagination">
        <button type="button" class="btn btn-secondary btn-sm" onclick={() => (currentPage = Math.max(1, activePage - 1))} disabled={activePage === 1}>
          Sebelumnya
        </button>
        <span class="cn-data-table-page">Halaman {activePage} / {totalPages}</span>
        <button type="button" class="btn btn-secondary btn-sm" onclick={() => (currentPage = Math.min(totalPages, activePage + 1))} disabled={activePage === totalPages}>
          Selanjutnya
        </button>
      </div>
    </div>
  {/if}
</div>

<style>
  .cn-data-table {
    width: 100%;
    padding: 1rem;
  }

  .cn-data-table-toolbar,
  .cn-data-table-footer {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .cn-data-table-toolbar {
    margin-bottom: 1rem;
  }

  .cn-data-table-footer {
    margin-top: 1rem;
  }

  .cn-data-table-control {
    display: grid;
    gap: 0.45rem;
    min-width: min(100%, 18rem);
  }

  .cn-data-table-control span,
  .cn-data-table-info,
  .cn-data-table-page {
    font-size: 0.88rem;
    color: var(--text-muted);
  }

  .cn-data-table-control-small {
    min-width: 9rem;
  }

    .cn-data-table-desktop {
      display: block;
      overflow-x: auto;
    }

    .cn-data-table-table {
      width: 100%;
    }

  .cn-data-table-mobile {
    display: none;
  }

  .cn-data-table-card {
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--background);
    padding: 0.95rem;
    display: grid;
    gap: 0.9rem;
  }

  .cn-data-table-card-row {
    display: grid;
    gap: 0.4rem;
  }

  .cn-data-table-card-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-soft);
  }

  .cn-data-table-card-value {
    min-width: 0;
  }

  .cn-data-table-empty {
    padding: 0;
  }

  .cn-data-table-pagination {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  @media (max-width: 767px) {
    .cn-data-table {
      padding: 0.75rem;
    }

    .cn-data-table-toolbar,
    .cn-data-table-footer {
      align-items: stretch;
    }

    .cn-data-table-control,
    .cn-data-table-control-small,
    .cn-data-table-pagination,
    .cn-data-table-pagination :global(.btn) {
      width: 100%;
    }

    .cn-data-table-desktop {
      display: none;
    }

    .cn-data-table-mobile {
      display: grid;
      gap: 0.75rem;
    }
  }
</style>
