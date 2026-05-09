<script>
  let {
    src = null,
    alt = "",
    class: className = "",
    loading = "lazy",
    decoding = "async",
    fetchpriority = "auto",
    sizes = "",
    onerror = null,
  } = $props();

  const DEFAULT_WIDTHS = [320, 480, 640, 960, 1280, 1920];

  /**
   * @param {unknown} value
   * @returns {value is Record<string, unknown>}
   */
  const isRecord = (value) => typeof value === "object" && value !== null;

  const optimizedSources = $derived(
    isRecord(src) && isRecord(src.sources) ? src.sources : null,
  );

  const enhancedImage = $derived(
    isRecord(src) && isRecord(src.img) ? src.img : null,
  );

  const hasOptimizedSources = $derived(
    Boolean(
      optimizedSources?.avif ||
      optimizedSources?.webp ||
      src?.avif ||
      src?.webp,
    ),
  );

  const originalSrc = $derived(
    typeof src === "string"
      ? src
      : (enhancedImage?.src ?? src?.original ?? null),
  );

  /**
   * @param {string|null} url
   * @param {number[]} widths
   * @returns {string}
   */
  const makeSrcset = (url, widths = DEFAULT_WIDTHS) => {
    if (!url || typeof url !== "string") {
      return "";
    }

    if (/\s\d+w(?:,|$)/.test(url)) {
      return url;
    }

    const sep = url.includes("?") ? "&" : "?";

    return widths.map((w) => `${url}${sep}w=${w} ${w}w`).join(", ");
  };

  const avifSrcset = $derived(makeSrcset(optimizedSources?.avif ?? src?.avif));
  const webpSrcset = $derived(makeSrcset(optimizedSources?.webp ?? src?.webp));
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
      {onerror}
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
    {onerror}
  />
{/if}
