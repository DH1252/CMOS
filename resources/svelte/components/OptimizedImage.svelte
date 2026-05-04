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

  const DEFAULT_WIDTHS = [320, 480, 640, 960, 1280, 1920];

  const hasOptimizedSources = $derived(
    src && typeof src === 'object' && (src.avif || src.webp),
  );

  const originalSrc = $derived(
    typeof src === 'string' ? src : src?.original ?? null,
  );

  /**
   * @param {string|null} url
   * @param {number[]} widths
   * @returns {string}
   */
  const makeSrcset = (url, widths = DEFAULT_WIDTHS) => {
    if (!url || typeof url !== 'string') {
      return '';
    }

    const sep = url.includes('?') ? '&' : '?';

    return widths.map((w) => `${url}${sep}w=${w} ${w}w`).join(', ');
  };

  const avifSrcset = $derived(makeSrcset(src?.avif));
  const webpSrcset = $derived(makeSrcset(src?.webp));
</script>

{#if hasOptimizedSources}
  <picture>
    {#if avifSrcset}
      <source srcset={avifSrcset} type="image/avif" {sizes} />
    {/if}
    {#if webpSrcset}
      <source srcset={webpSrcset} type="image/webp" {sizes} />
    {/if}
    <img
      src={originalSrc}
      srcset={webpSrcset || avifSrcset}
      {alt}
      class="h-auto {className}"
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
