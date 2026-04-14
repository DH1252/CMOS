<script module>
	import { cn } from "$lib/utils.js";
	import { tv } from "tailwind-variants";

	export const buttonVariants = tv({
		base: "focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:aria-invalid:border-destructive/50 rounded-lg border border-transparent bg-clip-padding text-sm font-medium focus-visible:ring-3 active:not-aria-[haspopup]:translate-y-px aria-invalid:ring-3 [&_svg:not([class*='size-'])]:size-4 group/button inline-flex shrink-0 items-center justify-center whitespace-nowrap transition-all outline-none select-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0",
		variants: {
			variant: {
				default: "button-variant-default",
				outline: "button-variant-outline",
				secondary: "button-variant-secondary",
				ghost: "button-variant-ghost",
				destructive: "button-variant-destructive",
				link: "button-variant-link",
			},
			size: {
				default: "h-8 gap-1.5 px-2.5 has-data-[icon=inline-end]:pr-2 has-data-[icon=inline-start]:pl-2",
				xs: "h-6 gap-1 rounded-[min(var(--radius-md),10px)] px-2 text-xs in-data-[slot=button-group]:rounded-lg has-data-[icon=inline-end]:pr-1.5 has-data-[icon=inline-start]:pl-1.5 [&_svg:not([class*='size-'])]:size-3",
				sm: "h-7 gap-1 rounded-[min(var(--radius-md),12px)] px-2.5 text-[0.8rem] in-data-[slot=button-group]:rounded-lg has-data-[icon=inline-end]:pr-1.5 has-data-[icon=inline-start]:pl-1.5 [&_svg:not([class*='size-'])]:size-3.5",
				lg: "h-9 gap-1.5 px-2.5 has-data-[icon=inline-end]:pr-2 has-data-[icon=inline-start]:pl-2",
				icon: "size-8",
				"icon-xs": "size-6 rounded-[min(var(--radius-md),10px)] in-data-[slot=button-group]:rounded-lg [&_svg:not([class*='size-'])]:size-3",
				"icon-sm": "size-7 rounded-[min(var(--radius-md),12px)] in-data-[slot=button-group]:rounded-lg",
				"icon-lg": "size-9",
			},
		},
		defaultVariants: {
			variant: "default",
			size: "default",
		},
	});

</script>

<script>
	let {
		class: className,
		variant = "default",
		size = "default",
		ref = $bindable(null),
		href = undefined,
		type = "button",
		disabled,
		children,
		...restProps
	} = $props();

	const resolveHref = (value) => {
		if (typeof value === 'string') {
			return value;
		}

		if (value && typeof value === 'object') {
			return value.href || value.url || value.path || value.src || value.default || undefined;
		}

		return undefined;
	};
</script>

{#if href}
	<a
		bind:this={ref}
		data-slot="button"
		class={cn(buttonVariants({ variant, size }), className)}
		href={disabled ? undefined : resolveHref(href)}
		aria-disabled={disabled}
		role={disabled ? "link" : undefined}
		tabindex={disabled ? -1 : undefined}
		{...restProps}
	>
		{@render children?.()}
	</a>
{:else}
	<button
		bind:this={ref}
		data-slot="button"
		class={cn(buttonVariants({ variant, size }), className)}
		{type}
		{disabled}
		{...restProps}
	>
		{@render children?.()}
	</button>
	{/if}

<style>
	:global([data-slot="button"]) {
		transition: transform 180ms var(--ease-out-quart), background 180ms var(--ease-out-quart), border-color 180ms var(--ease-out-quart), color 180ms var(--ease-out-quart), box-shadow 180ms var(--ease-out-quart);
	}

	:global([data-slot="button"]:hover:not([disabled]):not([aria-disabled="true"])) {
		transform: translateY(-1px);
	}

	:global([data-slot="button"]:active:not([disabled]):not([aria-disabled="true"])) {
		transform: translateY(0) scale(0.985);
	}

	.button-variant-default {
		background: var(--primary);
		color: var(--primary-foreground);
	}

	.button-variant-default:hover,
	.button-variant-default:focus-visible {
		background: color-mix(in srgb, var(--primary) 88%, black);
		color: var(--primary-foreground);
	}

	.button-variant-outline {
		border-color: var(--border);
		background: var(--background);
		color: var(--foreground);
	}

	.button-variant-outline:hover,
	.button-variant-outline:focus-visible {
		background: var(--muted);
		color: var(--foreground);
	}

	.button-variant-secondary {
		background: var(--secondary);
		color: var(--secondary-foreground);
	}

	.button-variant-secondary:hover,
	.button-variant-secondary:focus-visible {
		background: color-mix(in srgb, var(--secondary) 86%, black);
		color: var(--secondary-foreground);
	}

	.button-variant-ghost {
		color: var(--foreground);
	}

	.button-variant-ghost:hover,
	.button-variant-ghost:focus-visible {
		background: var(--muted);
		color: var(--foreground);
	}

	.button-variant-destructive {
		background: color-mix(in srgb, var(--destructive) 12%, white);
		color: color-mix(in srgb, var(--destructive) 82%, black);
	}

	.button-variant-destructive:hover,
	.button-variant-destructive:focus-visible {
		background: color-mix(in srgb, var(--destructive) 20%, white);
		color: color-mix(in srgb, var(--destructive) 84%, black);
	}

	.button-variant-link {
		color: color-mix(in srgb, var(--primary) 82%, black);
		text-decoration: underline;
		text-underline-offset: 0.25rem;
	}

	.button-variant-link:hover,
	.button-variant-link:focus-visible {
		color: color-mix(in srgb, var(--brand-hover) 82%, black);
	}
</style>
