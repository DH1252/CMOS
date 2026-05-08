<script>
  import { Badge } from "$lib/components/ui/badge/index.js";

  let { label = "", icon = "", tone = "secondary", className = "" } = $props();

  const variantFor = (value) => {
    if (value === "secondary") {
      return "secondary";
    }

    return "outline";
  };

  const toneClass = (value) => {
    if (value === "primary") {
      return "status-badge-primary";
    }

    if (value === "success") {
      return "status-badge-success";
    }

    if (value === "warning") {
      return "status-badge-warning";
    }

    if (value === "danger") {
      return "status-badge-danger";
    }

    if (value === "info") {
      return "status-badge-info";
    }

    return "status-badge-secondary";
  };
</script>

<Badge
  variant={variantFor(tone)}
  class={`status-badge ${toneClass(tone)} ${className}`.trim()}
>
  {#if icon}
    <i class={icon}></i>
  {/if}
  <span>{label}</span>
</Badge>

<style>
  .status-badge {
    gap: 0.3rem;
    min-height: 1.55rem;
    padding-inline: 0.5rem;
    border-width: 1px;
    border-radius: 0.45rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0;
  }

  .status-badge-primary {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 34%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--brand-light) 72%, white);
    color: color-mix(in srgb, var(--brand-hover) 88%, black);
  }

  .status-badge-success {
    border-color: color-mix(
      in srgb,
      var(--signal-success) 30%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--signal-success) 18%, white);
    color: color-mix(in srgb, var(--signal-success) 82%, black);
  }

  .status-badge-warning {
    border-color: color-mix(
      in srgb,
      var(--signal-warning) 30%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--signal-warning) 18%, white);
    color: color-mix(in srgb, var(--signal-warning) 82%, black);
  }

  .status-badge-danger {
    border-color: color-mix(
      in srgb,
      var(--signal-danger) 30%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--signal-danger) 16%, white);
    color: color-mix(in srgb, var(--signal-danger) 84%, black);
  }

  .status-badge-info {
    border-color: color-mix(in srgb, var(--signal-info) 30%, var(--line-soft));
    background: color-mix(in srgb, var(--signal-info) 14%, white);
    color: color-mix(in srgb, var(--signal-info) 78%, black);
  }

  .status-badge-secondary {
    border-color: color-mix(in srgb, var(--brand-secondary) 18%, var(--border));
    background: color-mix(in srgb, var(--brand-secondary-soft) 44%, white);
    color: color-mix(in srgb, var(--brand-secondary) 52%, var(--text-strong));
  }

  :global([data-theme="dark"]) .status-badge-primary {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 30%,
      var(--line-soft)
    );
    background: color-mix(
      in srgb,
      var(--brand-primary) 16%,
      var(--panel-muted)
    );
    color: color-mix(in srgb, var(--brand-primary) 18%, white);
  }

  :global([data-theme="dark"]) .status-badge-success {
    border-color: color-mix(
      in srgb,
      var(--signal-success) 26%,
      var(--line-soft)
    );
    background: color-mix(
      in srgb,
      var(--signal-success) 14%,
      var(--panel-muted)
    );
    color: color-mix(in srgb, var(--signal-success) 18%, white);
  }

  :global([data-theme="dark"]) .status-badge-warning {
    border-color: color-mix(
      in srgb,
      var(--signal-warning) 26%,
      var(--line-soft)
    );
    background: color-mix(
      in srgb,
      var(--signal-warning) 14%,
      var(--panel-muted)
    );
    color: color-mix(in srgb, var(--signal-warning) 18%, white);
  }

  :global([data-theme="dark"]) .status-badge-danger {
    border-color: color-mix(
      in srgb,
      var(--signal-danger) 26%,
      var(--line-soft)
    );
    background: color-mix(
      in srgb,
      var(--signal-danger) 14%,
      var(--panel-muted)
    );
    color: color-mix(in srgb, var(--signal-danger) 18%, white);
  }

  :global([data-theme="dark"]) .status-badge-info {
    border-color: color-mix(in srgb, var(--signal-info) 26%, var(--line-soft));
    background: color-mix(in srgb, var(--signal-info) 12%, var(--panel-muted));
    color: color-mix(in srgb, var(--signal-info) 18%, white);
  }

  :global([data-theme="dark"]) .status-badge-secondary {
    border-color: color-mix(in srgb, var(--brand-secondary) 16%, var(--border));
    background: color-mix(
      in srgb,
      var(--brand-secondary-soft) 28%,
      var(--panel-bg)
    );
    color: var(--text-soft);
  }
</style>
