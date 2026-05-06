<script>
  let {
    src = '',
    alt = '',
    cols = 120,
    characters = ' .:\'`^,"-~+<_!i?}{1)(|\\/tfjrxnuvczmwqpdbkhao*#MW&8%B@$',
    invert = false,
    gamma = 0.55,
    class: className = '',
  } = $props();

  let ascii = $state('');
  let rows = $state(0);
  let loading = $state(true);

  function loadImage(url) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = () => resolve(img);
      img.onerror = () => reject(new Error('Failed to load image'));
      img.src = url;
    });
  }

  function generateAscii(img, targetCols, chars, invertMap, gammaVal) {
    const charAspect = 1.3;
    const targetRows = Math.round(targetCols * (img.naturalHeight / img.naturalWidth) * charAspect);
    rows = targetRows;

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    if (!ctx) {
      throw new Error('Canvas 2D not supported');
    }

    canvas.width = targetCols;
    canvas.height = targetRows;
    ctx.filter = 'brightness(0.9) contrast(5)';
    ctx.drawImage(img, 0, 0, targetCols, targetRows);
    ctx.filter = 'none';

    const imageData = ctx.getImageData(0, 0, targetCols, targetRows);
    const data = imageData.data;
    let html = '';

    for (let y = 0; y < targetRows; y++) {
      for (let x = 0; x < targetCols; x++) {
        const i = (y * targetCols + x) * 4;
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const a = data[i + 3];

        if (a < 128) {
          html += ' ';
          continue;
        }

        const brightness = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        const tRaw = invertMap ? brightness : (1 - brightness);
        const t = Math.pow(tRaw, gammaVal);
        const idx = Math.floor(t * (chars.length - 1));
        const char = chars[Math.max(0, Math.min(idx, chars.length - 1))];

        html += `<span style="color:rgb(${r},${g},${b})">${char}</span>`;
      }
      html += '\n';
    }

    return html;
  }

  $effect(() => {
    if (!src) {
      return;
    }

    rows = 0;
    loading = true;
    ascii = '';

    loadImage(src)
      .then((img) => {
        ascii = generateAscii(img, cols, characters, invert, gamma);
        loading = false;
      })
      .catch((err) => {
        console.error('ASCII generation failed:', err);
        loading = false;
        ascii = '[IMG: ' + (alt || 'image') + ']';
      });
  });
</script>

<div class="ascii-image-wrapper {className}" aria-label={alt}>
  {#if loading}
    <div class="ascii-image-loading">[loading...]</div>
  {:else}
    <div class="ascii-reveal">
      <pre class="ascii-image-pre" style="--ascii-rows: {rows}">{@html ascii}</pre>
    </div>
  {/if}
</div>

<style>
  .ascii-image-wrapper {
    display: block;
    width: 100%;
    overflow: hidden;
  }

  .ascii-reveal {
    clip-path: inset(0 0 100% 0);
    animation: terminal-reveal 5s steps(60, end) forwards;
  }

  @keyframes terminal-reveal {
    from {
      clip-path: inset(0 0 100% 0);
    }
    to {
      clip-path: inset(0 0 0% 0);
    }
  }

  .ascii-image-pre {
    font-family: 'Courier New', Courier, 'Lucida Console', monospace;
    font-size: clamp(0.18rem, 0.22rem, 0.28rem);
    line-height: 1;
    color: var(--brand-primary);
    margin: 0;
    padding: 0;
    white-space: pre;
    overflow: hidden;
    user-select: none;
    text-align: center;
    transform: scaleY(0.46);
    transform-origin: top center;
    margin-bottom: calc(var(--ascii-rows) * 1em * -0.54);
  }

  .ascii-image-loading {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.65rem;
    color: var(--text-muted);
    padding: 1rem;
    text-align: center;
  }
</style>
