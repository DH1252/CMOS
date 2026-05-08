<script>
  let { items = [], separator = "/" } = $props();
</script>

{#if items.length}
  <nav class="breadcrumbs" aria-label="Breadcrumb">
    {#each items as item, index (item.href || `${item.label}-${index}`)}
      {#if item.href}
        <a href={item.href} class="breadcrumbs-link">{item.label}</a>
      {:else}
        <span class="breadcrumbs-current" aria-current="page">{item.label}</span
        >
      {/if}

      {#if index < items.length - 1}
        <span class="breadcrumbs-separator" aria-hidden="true">{separator}</span
        >
      {/if}
    {/each}
  </nav>
{/if}

<style>
  .breadcrumbs {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 1rem;
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  .breadcrumbs-link {
    color: inherit;
    text-decoration: none;
  }

  .breadcrumbs-link:hover {
    color: var(--text-strong);
  }

  .breadcrumbs-current {
    color: var(--text-strong);
    font-weight: 700;
  }

  .breadcrumbs-separator {
    opacity: 0.55;
  }
</style>
