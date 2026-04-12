<script>
	import { cn } from "$lib/utils.js";

	let {
		ref = $bindable(null),
		class: className,
		children,
		size = "default",
		...restProps
	} = $props();
</script>

<div
	bind:this={ref}
	data-slot="card"
	data-size={size}
	class={cn("ring-foreground/8 bg-card text-card-foreground gap-3 overflow-hidden rounded-[1.1rem] py-3 text-sm ring-1 has-data-[slot=card-footer]:pb-0 has-[>img:first-child]:pt-0 data-[size=sm]:gap-2.5 data-[size=sm]:py-2.5 data-[size=sm]:has-data-[slot=card-footer]:pb-0 *:[img:first-child]:rounded-t-[1.1rem] *:[img:last-child]:rounded-b-[1.1rem] group/card flex flex-col", className)}
	{...restProps}
>
	{@render children?.()}
</div>
