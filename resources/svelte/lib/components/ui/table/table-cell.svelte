<script>
  import { cn } from "$lib/utils.js";
  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  } = $props();
</script>

<td
  bind:this={ref}
  data-slot="table-cell"
  class={cn(
    "p-2 align-top break-words whitespace-normal [&:has([role=checkbox])]:pr-0",
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</td>
