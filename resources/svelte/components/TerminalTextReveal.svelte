<script>
  import { onMount } from 'svelte';

  let {
    text = '',
    lines = [],
    tag = 'p',
    textClass = '',
    wrapperClass = '',
    delay = 0,
    animate = true,
    speed: userSpeed,
    frameRate: userFrameRate,
  } = $props();

  let wrapper = null;
  let isActive = $state(false);
  let isComplete = $state(false);
  let typedText = $state('');
  let glitchGlyph = $state('');

  const sourceText = $derived(Array.isArray(lines) && lines.length ? lines.join('\n') : text);
  const renderStatic = $derived(!animate);
  const glitchChars = '._:=+/-[]{}<>|';

  const nextGlitchGlyph = (seed) => glitchChars[Math.abs(seed) % glitchChars.length];

  const computeSpeed = (len) => {
    if (userSpeed !== undefined) return userSpeed;
    return Math.max(1, Math.min(4, Math.ceil(len / 50)));
  };

  const computeFrameRate = (len) => {
    if (userFrameRate !== undefined) return userFrameRate;
    return Math.max(18, Math.min(28, Math.round(18 + len / 25)));
  };

  onMount(() => {
    if (!animate) {
      return;
    }

    if (typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      isComplete = true;
      return;
    }

    let cancelled = false;
    let started = false;
    let observer;
    let progressTimer;
    let progress = 0;

    const trimmed = sourceText.trim();
    const len = trimmed.length;
    const speed = computeSpeed(len);
    const frameRate = computeFrameRate(len);

    const startAnimation = () => {
      if (started || !wrapper || !trimmed) {
        return;
      }

      started = true;
      isActive = true;
      typedText = '';
      glitchGlyph = '';
      progress = 0;

      progressTimer = window.setInterval(() => {
        if (cancelled) {
          return;
        }

        progress = Math.min(len, progress + speed);
        typedText = sourceText.slice(0, progress);
        glitchGlyph = progress < len ? nextGlitchGlyph(progress) : '';

        if (progress >= len) {
          window.clearInterval(progressTimer);
          progressTimer = undefined;
          glitchGlyph = '';
          isComplete = true;
        }
      }, Math.max(36, Math.floor(1000 / frameRate)));
    };

    if (typeof IntersectionObserver === 'undefined') {
      const timeout = window.setTimeout(startAnimation, delay);

      return () => {
        cancelled = true;
        window.clearTimeout(timeout);
        if (progressTimer) {
          window.clearInterval(progressTimer);
        }
      };
    }

    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          observer?.unobserve(entry.target);
          window.setTimeout(startAnimation, delay);
        });
      },
      {
        threshold: 0.2,
        rootMargin: '0px 0px 0px 0px',
      },
    );

    observer.observe(wrapper);

    return () => {
      cancelled = true;
      if (progressTimer) {
        window.clearInterval(progressTimer);
      }
      observer?.disconnect();
    };
  });
</script>

<div bind:this={wrapper} class={`terminal-reveal ${wrapperClass}`.trim()}>
  <svelte:element
    this={tag}
    class={`terminal-reveal__base ${isComplete || renderStatic ? 'terminal-reveal__base--visible' : ''} ${textClass}`.trim()}
  >
    {sourceText}
  </svelte:element>

  {#if animate && isActive && !isComplete}
    <svelte:element
      this={tag}
      class={`terminal-reveal__overlay ${textClass}`.trim()}
      aria-hidden="true"
    >
      {typedText}<span class="terminal-reveal__glitch">{glitchGlyph}</span><span class="terminal-reveal__cursor">_</span>
    </svelte:element>
  {/if}
</div>

<style>
  .terminal-reveal {
    position: relative;
  }

  .terminal-reveal__base {
    position: relative;
    z-index: 1;
    opacity: 0;
    transition: opacity 280ms var(--ease-out-quart);
  }

  .terminal-reveal__base--visible {
    opacity: 1;
  }

  .terminal-reveal__overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    white-space: pre-wrap;
    text-shadow: 0 0 8px color-mix(in srgb, currentColor 18%, transparent);
  }

  .terminal-reveal__glitch {
    color: color-mix(in srgb, currentColor 72%, var(--landing-terminal-command-resolved, var(--landing-terminal-command, var(--landing-terminal-accent-resolved, var(--landing-terminal-accent)))) 28%);
  }

  .terminal-reveal__cursor {
    display: inline-block;
    margin-left: 0.02em;
    color: var(--landing-terminal-command-resolved, var(--landing-terminal-command, var(--landing-terminal-accent-resolved, var(--landing-terminal-accent))));
    animation: terminalCursorBlink 720ms steps(1, end) infinite;
  }

  @keyframes terminalCursorBlink {
    0%,
    49% {
      opacity: 1;
    }

    50%,
    100% {
      opacity: 0;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .terminal-reveal__base {
      opacity: 1;
      transition: none;
    }

    .terminal-reveal__overlay {
      display: none;
    }

    .terminal-reveal__cursor {
      animation: none;
    }
  }
</style>
