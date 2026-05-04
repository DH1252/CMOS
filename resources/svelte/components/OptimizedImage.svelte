<script>
  let {
    src = null,
    alt = '',
    class: className = '',
    loading = 'lazy',
    decoding = 'async',
    fetchpriority = 'auto',
    sizes = '',
    onerror = null,
  } = $props();

  const hasOptimizedSources = $derived(
    src && typeof src === 'object' && (src.avif || src.webp),
  );

  const originalSrc = $derived(
    typeof src === 'string' ? src : src?.original ?? null,
  );
</script>

{#if hasOptimizedSources}
  <picture>
    {#if src.avif}
      <source srcset={src.avif} type="image/avif" {sizes} />
    {/if}
    {#if src.webp}
      <source srcset={src.webp} type="image/webp" {sizes} />
    {/if}
    <img
      src={originalSrc}
      {alt}
      class={className}
      {loading}
      {decoding}
      {fetchpriority}
      {sizes}
      onerror={onerror}
    />
  </picture>
{:else if originalSrc}
  <img
    src={originalSrc}
    {alt}
    class={className}
    {loading}
    {decoding}
    {fetchpriority}
    {sizes}
    onerror={onerror}
  />
{/if}
