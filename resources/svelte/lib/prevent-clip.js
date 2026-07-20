/**
 * Svelte action that scales an overlay down (via the `--clip-scale` CSS custom
 * property) when its rendered size would otherwise clip its image container.
 *
 * The overlay's own layout dimensions are measured with offsetWidth/Height
 * (which ignore CSS transforms) so the action does not enter a feedback loop.
 *
 * @param {HTMLElement} node
 * @param {{ availW: number, availH: number, baseScale?: number }} params
 *   - availW / availH: pixels of space available on the overlay's anchor
 *     (the smaller of the two sides around the centerpoint).
 *   - baseScale: an existing scale already applied to the node (e.g. a
 *     size-responsive card scale). The clip scale composes with it.
 */
export function preventClip(node, params) {
  let current = params;
  const ro =
    typeof ResizeObserver !== "undefined"
      ? new ResizeObserver(recompute)
      : null;

  function recompute() {
    const { availW, availH, baseScale = 1 } = current;
    if (!availW || !availH) {
      node.style.removeProperty("--clip-scale");
      return;
    }
    const w = node.offsetWidth;
    const h = node.offsetHeight;
    if (!w || !h) return;
    const visualW = w * baseScale;
    const visualH = h * baseScale;
    const sx = availW / visualW;
    const sy = availH / visualH;
    // Reasonable limits: never shrink below 0.6 (unreadable) or above 1.
    const next = Math.max(0.6, Math.min(1, sx, sy));
    node.style.setProperty("--clip-scale", String(next));
  }

  recompute();
  if (ro) ro.observe(node);

  return {
    update(nextParams) {
      current = nextParams;
      recompute();
    },
    destroy() {
      if (ro) ro.disconnect();
    },
  };
}
