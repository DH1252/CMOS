<script>
  import { onMount } from "svelte";

  let {
    text = "",
    texts = [],
    lines = [],
    tag = "p",
    textClass = "",
    wrapperClass = "",
    delay = 0,
    animate = true,
    previewWhileAnimating = true,
    cycle = false,
    holdDuration = 1200,
    speed: userSpeed,
    deleteSpeed: userDeleteSpeed,
    frameRate: userFrameRate,
  } = $props();

  let wrapper = $state(null);
  let hasMounted = $state(false);
  let isActive = $state(false);
  let isComplete = $state(false);
  let prefersReducedMotion = $state(false);
  let typedText = $state("");
  let glitchGlyph = $state("");

  const sourceText = $derived(
    Array.isArray(lines) && lines.length ? lines.join("\n") : text,
  );
  const sequenceTexts = $derived.by(() => {
    const normalized = Array.isArray(texts)
      ? texts.filter(
          (value) => typeof value === "string" && value.trim().length,
        )
      : [];

    if (normalized.length) {
      return normalized;
    }

    return sourceText.trim() ? [sourceText] : [];
  });
  const baseText = $derived(sequenceTexts[0] ?? "");
  const layoutText = $derived.by(() => {
    return sequenceTexts.reduce(
      (largestText, currentText) =>
        currentText.length > largestText.length ? currentText : largestText,
      "",
    );
  });
  const renderStatic = $derived(!animate);
  const isServer = typeof window === "undefined";
  const baseTextVisible = $derived(
    renderStatic ||
      prefersReducedMotion ||
      (!previewWhileAnimating && (isComplete || isServer || !hasMounted)),
  );
  const baseTextPreview = $derived(
    animate && previewWhileAnimating && isActive && !isComplete && !isServer,
  );
  const overlayVisible = $derived(
    animate &&
      !prefersReducedMotion &&
      (previewWhileAnimating || (isActive && !isComplete)),
  );
  const overlayText = $derived(
    previewWhileAnimating && !isActive ? baseText : typedText,
  );
  const renderedBaseText = $derived(
    animate && previewWhileAnimating && !prefersReducedMotion
      ? layoutText
      : baseText,
  );
  const glitchChars = "._:=+/-[]{}<>|";

  const nextGlitchGlyph = (seed) =>
    glitchChars[Math.abs(seed) % glitchChars.length];

  const computeSpeed = (len) => {
    if (userSpeed !== undefined) return userSpeed;
    return Math.max(1, Math.min(4, Math.ceil(len / 50)));
  };

  const computeFrameRate = (len) => {
    if (userFrameRate !== undefined) return userFrameRate;
    return Math.max(18, Math.min(28, Math.round(18 + len / 25)));
  };

  const computeDeleteSpeed = (len) => {
    if (userDeleteSpeed !== undefined) return userDeleteSpeed;
    return Math.max(2, computeSpeed(len));
  };

  onMount(() => {
    hasMounted = true;

    if (!animate) {
      return;
    }

    if (
      typeof window !== "undefined" &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    ) {
      prefersReducedMotion = true;
      isComplete = true;
      return;
    }

    let cancelled = false;
    let started = false;
    let observer;
    let frameTimeout;

    const queue = sequenceTexts;

    const clearFrameTimeout = () => {
      if (frameTimeout) {
        window.clearTimeout(frameTimeout);
        frameTimeout = undefined;
      }
    };

    const scheduleFrame = (callback, ms) => {
      clearFrameTimeout();
      frameTimeout = window.setTimeout(callback, ms);
    };

    const runFrame = (index, mode, progress) => {
      if (cancelled) {
        return;
      }

      const currentText = queue[index] ?? "";
      const len = currentText.length;
      const speed = computeSpeed(len);
      const deleteSpeed = computeDeleteSpeed(len);
      const frameRate = computeFrameRate(len);
      const frameDelay = Math.max(36, Math.floor(1000 / frameRate));

      if (!currentText) {
        isComplete = true;
        return;
      }

      if (mode === "typing") {
        const nextProgress = Math.min(len, progress + speed);
        typedText = currentText.slice(0, nextProgress);
        glitchGlyph = nextProgress < len ? nextGlitchGlyph(nextProgress) : "";

        if (nextProgress >= len) {
          glitchGlyph = "";

          if (cycle && queue.length > 1) {
            scheduleFrame(() => runFrame(index, "deleting", len), holdDuration);
            return;
          }

          isComplete = true;
          return;
        }

        scheduleFrame(
          () => runFrame(index, "typing", nextProgress),
          frameDelay,
        );
        return;
      }

      const nextProgress = Math.max(0, progress - deleteSpeed);
      typedText = currentText.slice(0, nextProgress);
      glitchGlyph = nextProgress > 0 ? nextGlitchGlyph(nextProgress) : "";

      if (nextProgress <= 0) {
        glitchGlyph = "";
        const nextIndex = (index + 1) % queue.length;
        scheduleFrame(
          () => runFrame(nextIndex, "typing", 0),
          Math.max(120, Math.floor(frameDelay * 1.5)),
        );
        return;
      }

      scheduleFrame(
        () => runFrame(index, "deleting", nextProgress),
        frameDelay,
      );
    };

    const startAnimation = () => {
      if (started || !wrapper || !queue.length) {
        return;
      }

      started = true;
      isActive = true;
      isComplete = false;
      typedText = "";
      glitchGlyph = "";
      runFrame(0, "typing", 0);
    };

    if (typeof IntersectionObserver === "undefined") {
      const timeout = window.setTimeout(startAnimation, delay);

      return () => {
        cancelled = true;
        window.clearTimeout(timeout);
        clearFrameTimeout();
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
        rootMargin: "0px 0px 0px 0px",
      },
    );

    observer.observe(wrapper);

    return () => {
      cancelled = true;
      clearFrameTimeout();
      observer?.disconnect();
    };
  });
</script>

<div bind:this={wrapper} class={`terminal-reveal ${wrapperClass}`.trim()}>
  <svelte:element
    this={tag}
    class={`terminal-reveal__base ${baseTextVisible ? "terminal-reveal__base--visible" : ""} ${baseTextPreview ? "terminal-reveal__base--preview" : ""} ${textClass}`.trim()}
  >
    {renderedBaseText}
  </svelte:element>

  {#if overlayVisible}
    <svelte:element
      this={tag}
      class={`terminal-reveal__overlay ${textClass}`.trim()}
      aria-hidden="true"
    >
      <span class="terminal-reveal__overlay-spacer">{renderedBaseText}</span>
      <span class="terminal-reveal__animated-text"
        >{overlayText}<span class="terminal-reveal__endpoint"
          ><span class="terminal-reveal__glitch">{glitchGlyph}</span
          >{#if !isComplete}<span class="terminal-reveal__cursor">_</span
            >{/if}</span
        ></span
      >
    </svelte:element>
  {/if}
</div>
