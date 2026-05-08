<script>
  import { Button } from "$lib/components/ui/button/index.js";

  let {
    title = "",
    description = "",
    icon = "",
    headingTag = "h2",
    primaryAction = null,
    secondaryAction = null,
    action = null,
    compact = false,
  } = $props();

  const buttonVariant = (item, isPrimary = false) => {
    if (item?.variant) {
      return item.variant;
    }

    if (item?.tone === "danger") {
      return "destructive";
    }

    if (item?.tone === "secondary") {
      return "secondary";
    }

    if (item?.tone === "ghost") {
      return "ghost";
    }

    if (item?.tone === "link") {
      return "link";
    }

    return isPrimary ? "default" : "outline";
  };

  const actions = $derived.by(() =>
    [
      secondaryAction ? { config: secondaryAction, primary: false } : null,
      action ? { config: action, primary: false } : null,
      primaryAction ? { config: primaryAction, primary: true } : null,
    ].filter(Boolean),
  );
</script>

<div
  class={`flex flex-col gap-4 md:flex-row md:items-start md:justify-between ${compact ? "md:gap-3" : "md:gap-5"}`}
>
  <div class="max-w-[68ch] min-w-0">
    <div class="flex items-start gap-2">
      {#if icon}
        <span
          class={`mt-1 inline-flex w-4 shrink-0 items-center justify-center text-[color:var(--brand-hover)] ${compact ? "text-sm" : "text-base"}`}
          ><i class={icon} aria-hidden="true"></i></span
        >
      {/if}
      <svelte:element
        this={headingTag}
        class={`m-0 leading-tight font-semibold text-foreground ${compact ? "text-lg" : "text-2xl"}`}
      >
        {title}
      </svelte:element>
    </div>

    {#if description}
      <p
        class={`max-w-[62ch] text-sm text-muted-foreground ${compact ? "mt-1.5 leading-6" : "mt-2 leading-7"}`}
      >
        {description}
      </p>
    {/if}
  </div>

  {#if actions.length}
    <div class="flex w-full flex-wrap gap-2 md:w-auto md:justify-end">
      {#each actions as item (item.config.href || item.config.label)}
        <Button
          href={item.config.href}
          variant={buttonVariant(item.config, item.primary)}
          size={compact ? "sm" : "default"}
          class="shadow-none"
          target={item.config.target || undefined}
          rel={item.config.rel || undefined}
        >
          {#if item.config.icon}
            <i class={`${item.config.icon} mr-1.5`}></i>
          {/if}
          <span>{item.config.label}</span>
        </Button>
      {/each}
    </div>
  {/if}
</div>
