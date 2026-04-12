<script>
  let {
    appName = 'CMOS',
    organizationName = 'HIMATEKKOM ITS',
    navSections = [],
    links = {},
    collapsed = false,
    showCollapseButton = false,
    onCollapseToggle = () => {},
    onNavigate = () => {},
  } = $props();
</script>

<div class="flex h-full flex-col bg-sidebar text-sidebar-foreground">
  <div class="border-b border-sidebar-border px-5 py-5">
    <a href={links.dashboard || '#'} class="block text-inherit no-underline">
      <div class="truncate text-base font-semibold text-sidebar-foreground">{appName}</div>
      <div class="mt-1 truncate text-sm text-muted-foreground">{organizationName}</div>
    </a>
  </div>

  <div class="flex-1 overflow-y-auto px-3 py-4">
    {#each navSections as section, index (section.title)}
      <section class={index > 0 ? 'mt-6' : ''}>
        <div class="px-2 pb-2">
          <div class="text-sm font-semibold text-sidebar-foreground">{section.title}</div>
        </div>

        <div class="grid gap-1">
          {#each section.items as item (item.href || item.label)}
            <a
              href={item.href}
              class={`grid gap-1 rounded-[10px] border px-3 py-2.5 text-sm no-underline transition-colors ${item.active ? 'border-sidebar-border bg-background text-sidebar-foreground' : 'border-transparent text-muted-foreground hover:border-sidebar-border hover:bg-background hover:text-foreground'}`}
              onclick={onNavigate}
            >
              <div class="flex items-center gap-3">
                <span class={`inline-flex w-5 shrink-0 items-center justify-center ${item.active ? 'text-brand-primary' : 'text-muted-foreground'}`}>
                  <i class={item.icon}></i>
                </span>
                <span class="truncate font-medium">{item.label}</span>
              </div>
            </a>
          {/each}
        </div>
      </section>
    {/each}
  </div>

</div>
