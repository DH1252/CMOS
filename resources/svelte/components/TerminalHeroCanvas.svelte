<script>
  let {
    asciiArt = '',
    fallbackSrc = '',
    alt = '',
    title = 'ASCII hero',
    status = 'Ready',
    footerLeft = 'route /',
    footerRight = 'image-to-ascii',
    class: className = '',
  } = $props();

  const isReady = $derived(Boolean(asciiArt.trim()));
</script>

<div class={`terminal-hero ${className}`.trim()}>
  <div class="terminal-hero__head">
    <span>{title}</span>
    <span>{isReady ? status : 'Static'}</span>
  </div>

    <div class="terminal-hero__viewport">
      {#if isReady}
      <pre class="terminal-hero__ascii" aria-label={alt}>{@html asciiArt}</pre>
    {:else}
      {#if fallbackSrc}
        <img src={fallbackSrc} alt={alt} class="terminal-hero__fallback" loading="lazy" decoding="async" />
      {:else}
        <div class="terminal-hero__placeholder">visual pending</div>
      {/if}
    {/if}

    <div class="terminal-hero__scanlines" aria-hidden="true"></div>
    <div class="terminal-hero__mask" aria-hidden="true"></div>
  </div>

  <div class="terminal-hero__foot">
    <span>{footerLeft}</span>
    <span>{isReady ? footerRight : 'fallback image'}</span>
  </div>
</div>

<style>
  .terminal-hero {
    display: grid;
    gap: 0;
    border: 1px solid var(--landing-terminal-line-resolved, var(--landing-terminal-line));
    background: var(--landing-terminal-panel-resolved, var(--landing-terminal-panel));
  }

  .terminal-hero__head,
  .terminal-hero__foot {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 0.9rem;
    border-bottom: 1px solid var(--landing-terminal-line-resolved, var(--landing-terminal-line));
    color: var(--landing-terminal-soft-resolved, var(--landing-terminal-soft));
    font-size: 0.72rem;
    letter-spacing: 0.03em;
  }

  .terminal-hero__foot {
    border-top: 1px solid var(--landing-terminal-line-resolved, var(--landing-terminal-line));
    border-bottom: none;
    letter-spacing: 0.02em;
    text-transform: none;
  }

  .terminal-hero__viewport {
    position: relative;
    display: grid;
    place-items: center;
    overflow: hidden;
    aspect-ratio: 50 / 34;
    min-height: 236px;
    background: var(--landing-terminal-hero-bg-resolved, var(--landing-terminal-hero-bg, var(--landing-terminal-panel-resolved, var(--landing-terminal-panel))));
  }

  .terminal-hero__ascii,
  .terminal-hero__fallback,
  .terminal-hero__placeholder {
    display: block;
    width: 100%;
    height: 100%;
  }

  .terminal-hero__ascii {
    margin: 0;
    padding: 0.6rem 0.8rem;
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 100%;
    overflow: hidden;
    white-space: pre;
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    font-size: clamp(0.24rem, 0.31vw, 0.38rem);
    line-height: 0.9;
    letter-spacing: 0;
    color: var(--landing-terminal-frame-accent-resolved, var(--landing-terminal-frame-accent));
    filter: brightness(1.18) saturate(1.12);
    text-shadow: 0 0 12px color-mix(in srgb, var(--landing-terminal-frame-accent-resolved, var(--landing-terminal-frame-accent)) 30%, transparent);
    user-select: none;
  }

  .terminal-hero__fallback,
  .terminal-hero__placeholder {
    object-fit: cover;
    filter: grayscale(1) contrast(1.05) brightness(0.62) sepia(0.3);
  }

  .terminal-hero__placeholder {
    display: grid;
    place-items: center;
    color: var(--landing-terminal-soft-resolved, var(--landing-terminal-soft));
    font-size: 0.88rem;
    letter-spacing: 0.03em;
  }

  .terminal-hero__scanlines,
  .terminal-hero__mask {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }

  .terminal-hero__scanlines {
    background-image: repeating-linear-gradient(
      to bottom,
      rgba(12, 10, 14, 0) 0,
      rgba(12, 10, 14, 0) 2px,
      rgba(12, 10, 14, 0.18) 3px,
      rgba(12, 10, 14, 0.18) 4px
    );
    opacity: 0.32;
    mix-blend-mode: multiply;
  }

  .terminal-hero__mask {
    box-shadow: inset 0 0 0 1px rgba(217, 174, 67, 0.12), inset 0 0 40px rgba(9, 7, 10, 0.4);
    animation: terminalFlicker 6s steps(3, end) infinite;
  }

  @keyframes terminalFlicker {
    0%,
    100% {
      opacity: 0.85;
    }

    8% {
      opacity: 0.72;
    }

    12% {
      opacity: 0.84;
    }

    32% {
      opacity: 0.76;
    }

    52% {
      opacity: 0.88;
    }

    76% {
      opacity: 0.8;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .terminal-hero__mask {
      animation: none;
    }
  }
</style>
