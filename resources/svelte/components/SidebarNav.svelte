<script>
  let {
    appName = 'CMOS',
    organizationName = 'HIMATEKKOM ITS',
    navSections = [],
    links = {},
    onNavigate = () => {},
  } = $props();
</script>

<nav class="flex h-full flex-col bg-sidebar text-sidebar-foreground" aria-label="Navigasi utama">
  <div class="border-b border-sidebar-border px-5 py-5">
    <a href={links.dashboard || '#'} class="block text-inherit no-underline">
      <div class="truncate text-base font-semibold text-sidebar-foreground">{appName}</div>
      <div class="mt-1 truncate text-sm text-muted-foreground">{organizationName}</div>
    </a>
  </div>

  <div class="flex-1 overflow-y-auto px-3 py-4">
    {#each navSections as section, index (section.title)}
      <section class={index > 0 ? 'mt-6 border-t border-sidebar-border/70 pt-6' : ''}>
        <h2 class="px-2 pb-3 text-sm font-semibold text-sidebar-foreground">{section.title}</h2>

        <ul class="grid gap-1.5 list-none m-0 p-0">
          {#each section.items as item (item.href || item.label)}
            <li>
              <a
                href={item.href}
                class={`flex items-center gap-3 rounded-[10px] border px-3 py-3 text-sm no-underline transition-colors ${item.active ? 'border-sidebar-border bg-background text-sidebar-foreground' : 'border-transparent text-muted-foreground hover:border-sidebar-border hover:bg-background hover:text-foreground'}`}
                onclick={onNavigate}
                aria-current={item.active ? 'page' : undefined}
              >
                <span class={`inline-flex w-5 shrink-0 items-center justify-center ${item.active ? 'text-[color:var(--brand-hover)]' : 'text-muted-foreground'}`}>
                  <i class={item.icon} aria-hidden="true"></i>
                </span>
                <span class="truncate font-medium">{item.label}</span>
              </a>
            </li>
          {/each}
        </ul>
      </section>
    {/each}
  </div>
</nav>
