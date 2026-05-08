<script>
  import { cn } from "$lib/utils.js";

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  } = $props();
</script>

<ul
  bind:this={ref}
  data-slot="pagination-content"
  class={cn("flex items-center gap-0.5", className)}
  {...restProps}
>
  {@render children?.()}
</ul>
