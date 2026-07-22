const focusableSelector = [
  "a[href]",
  "button:not([disabled])",
  "input:not([disabled]):not([type='hidden'])",
  "select:not([disabled])",
  "textarea:not([disabled])",
  "[tabindex]:not([tabindex='-1'])",
].join(",");

export function modalFocus(node, options = {}) {
  const { initialFocus, onClose, returnFocus } = options;

  const getFocusableElements = () =>
    [...node.querySelectorAll(focusableSelector)].filter(
      (element) => !element.hidden && element.getClientRects().length > 0,
    );

  const focusInitialElement = () => {
    const target = initialFocus
      ? node.querySelector(initialFocus)
      : getFocusableElements()[0];

    (target || node).focus({ preventScroll: true });
  };

  const handleKeydown = (event) => {
    if (event.key === "Escape") {
      event.preventDefault();
      onClose?.();
      return;
    }

    if (event.key !== "Tab") {
      return;
    }

    const focusableElements = getFocusableElements();
    if (focusableElements.length === 0) {
      event.preventDefault();
      node.focus({ preventScroll: true });
      return;
    }

    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };

  node.addEventListener("keydown", handleKeydown);
  queueMicrotask(focusInitialElement);

  return {
    destroy() {
      node.removeEventListener("keydown", handleKeydown);
      queueMicrotask(() => {
        if (returnFocus?.isConnected) {
          returnFocus.focus({ preventScroll: true });
          return;
        }

        const fallback = document.querySelector(
          "[data-modal-focus-fallback], #main-content",
        );
        if (fallback instanceof HTMLElement) {
          if (fallback.tabIndex < 0 && !fallback.hasAttribute("tabindex")) {
            fallback.tabIndex = -1;
          }
          fallback.focus({ preventScroll: true });
        }
      });
    },
  };
}
