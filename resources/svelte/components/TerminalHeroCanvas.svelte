<script>
  let {
    asciiArt = "",
    fallbackSrc = "",
    alt = "",
    title = "ASCII hero",
    status = "Ready",
    footerLeft = "route /",
    footerRight = "image-to-ascii",
    class: className = "",
  } = $props();

  const isReady = $derived(Boolean(asciiArt.trim()));
</script>

<div class={`terminal-hero ${className}`.trim()}>
  <div class="terminal-hero__head">
    <span>{title}</span>
    <span>{isReady ? status : "Static"}</span>
  </div>

  <div class="terminal-hero__viewport">
    {#if isReady}
      <pre class="terminal-hero__ascii" aria-label={alt}>{@html asciiArt}</pre>
    {:else if fallbackSrc}
      <img
        src={fallbackSrc}
        {alt}
        class="terminal-hero__fallback"
        loading="lazy"
        decoding="async"
      />
    {:else}
      <div class="terminal-hero__placeholder">visual pending</div>
    {/if}

    <div class="terminal-hero__scanlines" aria-hidden="true"></div>
    <div class="terminal-hero__mask" aria-hidden="true"></div>
  </div>

  <div class="terminal-hero__foot">
    <span>{footerLeft}</span>
    <span>{isReady ? footerRight : "fallback image"}</span>
  </div>
</div>
