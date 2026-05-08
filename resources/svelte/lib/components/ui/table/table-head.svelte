<script>
  import { cn } from "$lib/utils.js";
  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  } = $props();
</script>

<th
  bind:this={ref}
  data-slot="table-head"
  class={cn(
    "h-10 px-2 text-left align-top font-medium break-words whitespace-normal text-foreground [&:has([role=checkbox])]:pr-0",
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</th>
