<script>
  import { onMount } from 'svelte';
  import { resolveThemeColor } from '../lib/textmode-theme.js';

  let {
    source = '',
    fallbackSrc = '',
    alt = '',
    title = 'Public terminal',
    status = 'Ready',
    footerLeft = 'route /',
    footerRight = 'textmode.js',
    class: className = '',
  } = $props();

  let canvas = null;
  let frame = null;
  let isReady = $state(false);
  let fallbackReason = $state('initializing renderer');

  const characterSet = " .'`^\",:;Il!i~+_-?][}{1)(|\\/tfjrxnuvczmwqpdbkhao*#MW&8%B@$";

  const resizeCanvas = (textmodeInstance) => {
    if (!frame || !textmodeInstance) {
      return null;
    }

    const width = Math.max(320, Math.floor(frame.clientWidth || 720));
    const measuredHeight = Math.floor(frame.clientHeight || 0);
    const height = Math.max(220, measuredHeight || Math.round(width * 0.62));

    return { width, height };
  };

  onMount(() => {
    let cancelled = false;
    let resizeObserver;
    let textmodeInstance;
    let resizeFrame = 0;
    let lastWidth = 0;
    let lastHeight = 0;

    const syncCanvasSize = () => {
      if (!textmodeInstance || cancelled) {
        return;
      }

      const nextSize = resizeCanvas(textmodeInstance);

      if (!nextSize) {
        return;
      }

      if (nextSize.width === lastWidth && nextSize.height === lastHeight) {
        return;
      }

      lastWidth = nextSize.width;
      lastHeight = nextSize.height;
      textmodeInstance.resizeCanvas(nextSize.width, nextSize.height);

      if (typeof textmodeInstance.redraw === 'function') {
        textmodeInstance.redraw(1);
      }
    };

    const scheduleCanvasResize = () => {
      cancelAnimationFrame(resizeFrame);
      resizeFrame = requestAnimationFrame(() => {
        syncCanvasSize();
      });
    };

    const initialize = async () => {
      if (!canvas || !frame || !source) {
        fallbackReason = 'visual source unavailable';

        return;
      }

      try {
        const { textmode } = await import('textmode.js');

        if (cancelled) {
          return;
        }

        const nextSize = resizeCanvas();
        const accentColor = resolveThemeColor(frame, '--landing-terminal-accent', '#D9AE43');
        const panelColor = resolveThemeColor(frame, '--landing-terminal-panel', '#151018');

        if (!nextSize) {
          fallbackReason = 'terminal viewport unavailable';

          return;
        }

        lastWidth = nextSize.width;
        lastHeight = nextSize.height;

        textmodeInstance = textmode.create({
          canvas,
          width: nextSize.width,
          height: nextSize.height,
          fontSize: 12,
          frameRate: 12,
        });

        await textmodeInstance.setup(async () => {
          const image = await textmodeInstance.loadImage(source);

          image
            .characters(characterSet)
            .charColorMode('fixed')
            .charColor(accentColor)
            .cellColorMode('fixed')
            .cellColor(panelColor)
            .background(panelColor);

          textmodeInstance.draw(() => {
            if (!textmodeInstance?.grid) {
              return;
            }

            textmodeInstance.background(panelColor);
            textmodeInstance.image(
              image,
              Math.max(18, textmodeInstance.grid.cols - 4),
              Math.max(12, textmodeInstance.grid.rows - 4),
            );
          });

          if (typeof textmodeInstance.noLoop === 'function') {
            textmodeInstance.noLoop();
          }

          if (typeof textmodeInstance.redraw === 'function') {
            textmodeInstance.redraw(1);
          }
        });

        resizeObserver = new ResizeObserver(() => {
          scheduleCanvasResize();
        });

        resizeObserver.observe(frame);
        scheduleCanvasResize();
        isReady = true;
      } catch (error) {
        fallbackReason = error instanceof Error ? error.message : 'webgl2 unavailable';
        isReady = false;
      }
    };

    void initialize();

    return () => {
      cancelled = true;
      cancelAnimationFrame(resizeFrame);
      resizeObserver?.disconnect();
      textmodeInstance?.destroy();
    };
  });
</script>

<div class={`terminal-hero ${className}`.trim()}>
  <div class="terminal-hero__head">
    <span>{title}</span>
    <span>{isReady ? status : 'Static'}</span>
  </div>

  <div class="terminal-hero__viewport" bind:this={frame}>
    <canvas bind:this={canvas} class:terminal-hero__canvas-hidden={!isReady} aria-hidden={!isReady}></canvas>

    {#if !isReady}
      {#if fallbackSrc || source}
        <img src={fallbackSrc || source} alt={alt} class="terminal-hero__fallback" loading="lazy" decoding="async" />
      {:else}
        <div class="terminal-hero__placeholder">visual pending</div>
      {/if}
    {/if}

    <div class="terminal-hero__scanlines" aria-hidden="true"></div>
    <div class="terminal-hero__mask" aria-hidden="true"></div>
  </div>

  <div class="terminal-hero__foot">
    <span>{footerLeft}</span>
    <span>{isReady ? footerRight : fallbackReason}</span>
  </div>
</div>

<style>
  .terminal-hero {
    display: grid;
    gap: 0;
    border: 1px solid var(--landing-terminal-line);
    background: var(--landing-terminal-panel);
  }

  .terminal-hero__head,
  .terminal-hero__foot {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 0.9rem;
    border-bottom: 1px solid var(--landing-terminal-line);
    color: var(--landing-terminal-soft);
    font-size: 0.72rem;
    letter-spacing: 0.03em;
  }

  .terminal-hero__foot {
    border-top: 1px solid var(--landing-terminal-line);
    border-bottom: none;
    letter-spacing: 0.02em;
    text-transform: none;
  }

  .terminal-hero__viewport {
    position: relative;
    overflow: hidden;
    aspect-ratio: 50 / 31;
    min-height: 220px;
    background: var(--landing-terminal-panel);
  }

  canvas,
  .terminal-hero__fallback,
  .terminal-hero__placeholder {
    display: block;
    width: 100%;
    height: 100%;
  }

  canvas {
    image-rendering: crisp-edges;
    filter: brightness(0.95) contrast(1.08);
  }

  .terminal-hero__canvas-hidden {
    display: none;
  }

  .terminal-hero__fallback,
  .terminal-hero__placeholder {
    object-fit: cover;
    filter: grayscale(1) contrast(1.05) brightness(0.62) sepia(0.3);
  }

  .terminal-hero__placeholder {
    display: grid;
    place-items: center;
    color: var(--landing-terminal-soft);
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
    opacity: 0.54;
    mix-blend-mode: multiply;
  }

  .terminal-hero__mask {
    box-shadow: inset 0 0 0 1px rgba(217, 174, 67, 0.1), inset 0 0 48px rgba(9, 7, 10, 0.6);
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
