<script>
  import { onMount } from "svelte";
  import { ChevronDown } from "lucide-svelte";
  import { fly, slide } from "svelte/transition";
  import { cubicOut } from "svelte/easing";
  import TalingNavbarLogo from "../TalingNavbarLogo.svelte";

  let { homeUrl = "/", loginUrl = "/login", navigationItems = [] } = $props();

  let openMenu = $state(null);
  let mobileMenuOpen = $state(false);
  let activeMobileSubmenu = $state(null);

  // Navbar logo animation state
  let logoEl = $state(null);
  let strokeColor = $state(
    typeof window !== "undefined" && window.__navbarLogoAnimated
      ? "transparent"
      : "currentColor",
  );
  let strokeWidth = $state(
    typeof window !== "undefined" && window.__navbarLogoAnimated ? "0" : "1.2",
  );
  let fillOpacity = $state(
    typeof window !== "undefined" && window.__navbarLogoAnimated ? "1" : "0",
  );

  onMount(async () => {
    if (typeof window === "undefined") return;

    if (window.__navbarLogoAnimated) {
      strokeColor = "transparent";
      strokeWidth = "0";
      fillOpacity = "1";
      return;
    }

    // Mark as animated so it only draws once per session
    window.__navbarLogoAnimated = true;

    // Dynamically import GSAP to prevent SSR issues
    const { gsap } = await import("gsap");
    const { DrawSVGPlugin } = await import("gsap/DrawSVGPlugin");
    gsap.registerPlugin(DrawSVGPlugin);

    // Wait if Svelte 5 bindings are not ready yet
    if (!logoEl) {
      await new Promise((resolve) => setTimeout(resolve, 50));
      if (!logoEl) return;
    }

    // Query only paths that are meant to be outlined (excluding path 2)
    const paths = logoEl.querySelectorAll("path[stroke]");

    const tl = gsap.timeline({
      defaults: { ease: "power2.out" },
      onComplete: () => {
        strokeColor = "transparent";
        strokeWidth = "0";
      },
    });

    // 1. Draw outline of logo paths
    tl.fromTo(
      paths,
      { drawSVG: "0%" },
      { drawSVG: "100%", duration: 1.0, stagger: 0.05 },
    );

    // 2. Smoothly fade in fills and thin the strokes
    let animObj = { fill: 0, strokeW: 1.2 };
    tl.to(
      animObj,
      {
        fill: 1,
        strokeW: 0,
        duration: 0.6,
        onUpdate: () => {
          fillOpacity = animObj.fill.toString();
          strokeWidth = animObj.strokeW.toString();
        },
      },
      "-=0.4",
    );
  });

  const toggleMenu = (label) => {
    openMenu = openMenu === label ? null : label;
  };

  const closeMenu = () => {
    openMenu = null;
  };

  const toggleMobileMenu = () => {
    mobileMenuOpen = !mobileMenuOpen;
    if (!mobileMenuOpen) {
      activeMobileSubmenu = null;
    }
  };

  const toggleMobileSubmenu = (label) => {
    activeMobileSubmenu = activeMobileSubmenu === label ? null : label;
  };

  const closeMobileMenu = () => {
    setTimeout(() => {
      mobileMenuOpen = false;
      activeMobileSubmenu = null;
    }, 0);
  };

  $effect(() => {
    if (mobileMenuOpen) {
      document.body.classList.add("overflow-hidden");
    } else {
      document.body.classList.remove("overflow-hidden");
    }
    return () => {
      document.body.classList.remove("overflow-hidden");
    };
  });
</script>

<svelte:window
  onresize={() => {
    if (window.innerWidth >= 768) closeMobileMenu();
  }}
/>

<nav
  style="view-transition-name: main-navbar;"
  class="relative z-50 flex h-[74px] w-full items-center justify-between bg-white px-6 py-[10px] shadow-sm md:px-[60px] lg:px-[139px]"
>
  <a
    href={homeUrl}
    style="view-transition-name: navbar-logo;"
    class="h-[69px] w-[75px] flex-shrink-0"
    aria-label="Home"
  >
    <TalingNavbarLogo
      bind:bindRef={logoEl}
      stroke={strokeColor}
      {strokeWidth}
      {fillOpacity}
    />
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

  <div class="hidden md:flex">
    <a
      href={loginUrl}
      class="flex h-[34px] w-[107px] items-center justify-center rounded-full bg-gradient-to-tr from-[#ff7a1a] to-[#ffd344] text-sm font-bold tracking-wide text-white shadow-md transition-all duration-150 hover:scale-105 active:scale-95"
    >
      Masuk
    </a>
  </div>

  <!-- Mobile Hamburger Button -->
  <button
    type="button"
    class="z-50 flex h-10 w-10 flex-col items-center justify-center gap-1.5 rounded-full border border-gray-100 bg-white shadow-sm transition-all duration-200 hover:scale-105 active:scale-95 md:hidden"
    onclick={toggleMobileMenu}
    aria-expanded={mobileMenuOpen}
    aria-label="Toggle menu"
  >
    <span
      class="h-[2px] w-5 rounded bg-[#222] transition-all duration-300 {mobileMenuOpen
        ? 'translate-y-[8px] rotate-45'
        : ''}"
    ></span>
    <span
      class="h-[2px] w-5 rounded bg-[#222] transition-all duration-300 {mobileMenuOpen
        ? 'scale-x-0 opacity-0'
        : ''}"
    ></span>
    <span
      class="h-[2px] w-5 rounded bg-[#222] transition-all duration-300 {mobileMenuOpen
        ? '-translate-y-[8px] -rotate-45'
        : ''}"
    ></span>
  </button>

  <!-- Mobile Drawer Menu -->
  {#if mobileMenuOpen}
    <div
      transition:fly={{ y: -20, duration: 300, easing: cubicOut }}
      class="absolute top-[74px] left-0 z-40 flex w-full flex-col border-b border-gray-100 bg-white/95 px-6 py-6 shadow-xl backdrop-blur-md md:hidden"
    >
      <div class="flex flex-col gap-5">
        {#each navigationItems as item, idx (item.href)}
          <div
            transition:fly={{
              x: -10,
              delay: idx * 40,
              duration: 300,
              easing: cubicOut,
            }}
            class="flex flex-col border-b border-gray-50 pb-4 last:border-none last:pb-0"
          >
            {#if item.children?.length}
              <button
                type="button"
                class="flex w-full items-center justify-between text-left text-base font-semibold text-[#222] transition-colors hover:text-[#ff7a1a]"
                onclick={() => toggleMobileSubmenu(item.label)}
                aria-expanded={activeMobileSubmenu === item.label}
              >
                <span>{item.label}</span>
                <ChevronDown
                  size={18}
                  strokeWidth={2.4}
                  class="transition-transform duration-300 {activeMobileSubmenu ===
                  item.label
                    ? 'rotate-180 text-[#ff7a1a]'
                    : 'text-gray-400'}"
                />
              </button>

              {#if activeMobileSubmenu === item.label}
                <div
                  transition:slide={{ duration: 250, easing: cubicOut }}
                  class="mt-3 flex flex-col gap-3 border-l border-orange-100 pl-4"
                >
                  {#each item.children as child (child.href)}
                    <a
                      href={child.href}
                      class="py-1 text-sm font-medium text-gray-600 transition-colors hover:text-[#ff7a1a]"
                      onclick={closeMobileMenu}
                    >
                      {child.label}
                    </a>
                  {/each}
                </div>
              {/if}
            {:else}
              <a
                href={item.href}
                class="text-base font-semibold text-[#222] transition-colors hover:text-[#ff7a1a]"
                onclick={closeMobileMenu}
              >
                {item.label}
              </a>
            {/if}
          </div>
        {/each}

        <div
          transition:fly={{
            y: 10,
            delay: navigationItems.length * 40,
            duration: 300,
            easing: cubicOut,
          }}
          class="mt-4 flex w-full"
        >
          <a
            href={loginUrl}
            class="flex h-11 w-full items-center justify-center rounded-full bg-gradient-to-tr from-[#ff7a1a] to-[#ffd344] text-base font-bold tracking-wide text-white shadow-md transition-all duration-150 hover:scale-[1.02] active:scale-95"
            onclick={closeMobileMenu}
          >
            Masuk
          </a>
        </div>
      </div>
    </div>
  {/if}
</nav>
