<script>
  import { ChevronDown } from "lucide-svelte";
  import { fly } from "svelte/transition";
  import { cubicOut } from "svelte/easing";
  import TalingNavbarLogo from "../TalingNavbarLogo.svelte";

  let { homeUrl = "/", loginUrl = "/login", navigationItems = [] } = $props();

  let openMenu = $state(null);

  const toggleMenu = (label) => {
    openMenu = openMenu === label ? null : label;
  };

  const closeMenu = () => {
    openMenu = null;
  };
</script>

<nav
  class="relative z-50 flex h-[74px] w-full items-center justify-between bg-white px-6 py-[10px] shadow-sm md:px-[60px] lg:px-[139px]"
>
  <a href={homeUrl} class="h-[69px] w-[75px] flex-shrink-0" aria-label="Home">
    <TalingNavbarLogo />
  </a>

  <div
    class="hidden flex-1 items-center justify-center gap-8 md:flex lg:gap-[35px]"
  >
    {#each navigationItems as item (item.href)}
      {#if item.children?.length}
        <div class="group relative">
          <button
            type="button"
            class="flex items-center gap-2 font-medium text-[#222] transition-colors hover:text-[#ff7a1a]"
            onclick={() => toggleMenu(item.label)}
            aria-expanded={openMenu === item.label}
          >
            {item.label}
            <ChevronDown size={16} strokeWidth={2.4} />
          </button>

          {#if openMenu === item.label}
            <div
              transition:fly={{ y: 8, duration: 200, easing: cubicOut }}
              class="absolute top-full left-1/2 mt-4 flex min-w-[190px] -translate-x-1/2 flex-col gap-1 rounded-lg border border-gray-100 bg-white p-2 shadow-xl"
            >
              {#each item.children as child (child.href)}
                <a
                  href={child.href}
                  class="rounded-md px-4 py-2.5 text-sm font-medium transition-colors hover:bg-orange-50"
                  onclick={closeMenu}
                >
                  {child.label}
                </a>
              {/each}
            </div>
          {/if}
        </div>
      {:else}
        <a
          href={item.href}
          class="font-medium text-[#222] transition-colors hover:text-[#ff7a1a]"
          >{item.label}</a
        >
      {/if}
    {/each}
  </div>

  <a
    href={loginUrl}
    class="hidden h-[34px] w-[107px] items-center justify-center rounded-full bg-gradient-to-tr from-[#ff7a1a] to-[#ffd344] text-sm font-bold tracking-wide text-white shadow-md transition-all duration-150 hover:scale-105 active:scale-95 md:flex"
  >
    Masuk
  </a>
</nav>
