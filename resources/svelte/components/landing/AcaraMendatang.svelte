<script>
  import { ChevronLeft, ChevronRight } from "lucide-svelte";
  import { fade, fly } from "svelte/transition";
  import { cubicOut } from "svelte/easing";
  import OptimizedImage from "../../components/OptimizedImage.svelte";
  import { onMount } from "svelte";

  let {
    assetBase = "/images/figma-taling",
    eventItems = [],
    acaraUrl = "/",
    eventsSection = {},
  } = $props();

  let currentEventIndex = $state(0);
  const eventCount = $derived(eventItems.length);
  const hasEventItems = $derived(eventCount > 0);

  const currentEvent = $derived(
    eventItems[eventCount ? currentEventIndex % eventCount : 0] ?? null,
  );
  const previousEventItem = $derived(
    eventCount > 2
      ? eventItems[(currentEventIndex - 1 + eventCount) % eventCount]
      : null,
  );
  const nextEventItem = $derived(
    eventCount > 1 ? eventItems[(currentEventIndex + 1) % eventCount] : null,
  );

  const previousEvent = () => {
    if (eventCount >= 2)
      currentEventIndex = (currentEventIndex - 1 + eventCount) % eventCount;
  };
  const nextEvent = () => {
    if (eventCount >= 2)
      currentEventIndex = (currentEventIndex + 1) % eventCount;
  };

  const getPosterImage = (poster) => {
    if (!poster) {
      return poster;
    }
    if (typeof poster === "object") {
      const original = poster.original || "";
      if (
        original.includes("event-poster-1.png") ||
        original.includes("event-poster-1-framed.png")
      ) {
        return {
          ...poster,
          original: `${assetBase}/event-poster-1-framed.png`,
          webp: null,
          avif: null,
        };
      }
      return poster;
    }
    if (typeof poster === "string") {
      if (poster.includes("event-poster-1.png")) {
        return `${assetBase}/event-poster-1-framed.png`;
      }
    }
    return poster;
  };

  let sectionElement = $state(null);
  let isIntersecting = $state(false);

  onMount(() => {
    if (typeof window === "undefined") return;

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      isIntersecting = true;
      return;
    }

    if (typeof IntersectionObserver === "undefined") {
      isIntersecting = true;
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            isIntersecting = true;
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 },
    );

    if (sectionElement) {
      observer.observe(sectionElement);
    }

    return () => {
      observer.disconnect();
    };
  });
</script>

<section
  bind:this={sectionElement}
  class="relative w-full overflow-hidden bg-transparent pt-28 pb-16"
>
  <div class="relative z-10 container mx-auto px-6 text-center">
    <div
      class="relative mb-16 w-full transition-all duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] xl:mb-20 xl:h-[107px] {isIntersecting
        ? 'translate-y-0 opacity-100'
        : 'translate-y-8 opacity-0'}"
    >
      <h2 class="acara-title">Acara Mendatang</h2>
    </div>

    {#if hasEventItems && currentEvent}
      <div
        class="flex flex-col items-center justify-center gap-16 xl:flex-row xl:gap-24"
      >
        <!-- Carousel/Poster Stack -->
        <div
          class="relative flex h-[495px] w-full max-w-[512px] items-center justify-center transition-all delay-150 duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] {isIntersecting
            ? 'translate-y-0 scale-100 opacity-100'
            : 'translate-y-8 scale-95 opacity-0'}"
        >
          {#each eventItems as event, index}
            {@const diff =
              (index - currentEventIndex + eventCount) % eventCount}
            {@const role =
              diff === 0
                ? "role-active"
                : diff === 1
                  ? "role-middle"
                  : diff === eventCount - 1 && eventCount > 2
                    ? "role-rear"
                    : "role-hidden"}

            <a
              href={role === "role-active" ? event.url : undefined}
              class="carousel-card {role} block overflow-hidden rounded-sm"
              tabindex={role === "role-active" ? 0 : -1}
              aria-hidden={role === "role-active" ? undefined : "true"}
            >
              {#if event.poster}
                <OptimizedImage
                  src={getPosterImage(event.poster)}
                  alt={event.title}
                  class="h-full w-full object-cover"
                />
              {:else}
                <div
                  class="flex h-full w-full flex-col items-center justify-center bg-white p-8 text-center transition-all duration-500 {role ===
                  'role-active'
                    ? 'border-8 border-gray-100'
                    : ''}"
                >
                  <span
                    class="text-sm font-bold tracking-widest text-[#5d0077] uppercase"
                    >{event.startsAtLabel}</span
                  >
                  <strong
                    class="mt-4 font-['Playfair_Display'] text-4xl text-[#ff7a1a]"
                    >{event.title}</strong
                  >
                </div>
              {/if}
            </a>
          {/each}

          {#if eventCount > 1}
            <div
              class="absolute bottom-[-45px] left-[-10px] z-40 xl:top-[229px] xl:bottom-auto xl:left-[-53px]"
            >
              <button
                class="flex h-[45px] w-[45px] cursor-pointer items-center justify-center bg-[#5d0077] text-white shadow-lg transition-all duration-150 hover:scale-110 hover:bg-[#2a0078] active:scale-90"
                aria-label="Previous"
                onclick={previousEvent}
              >
                <ChevronLeft size={24} strokeWidth={3} />
              </button>
            </div>
            <div
              class="absolute right-[-10px] bottom-[-45px] z-40 xl:top-[229px] xl:bottom-auto xl:left-[517px]"
            >
              <button
                class="flex h-[45px] w-[45px] cursor-pointer items-center justify-center bg-[#5d0077] text-white shadow-lg transition-all duration-150 hover:scale-110 hover:bg-[#2a0078] active:scale-90"
                aria-label="Next"
                onclick={nextEvent}
              >
                <ChevronRight size={24} strokeWidth={3} />
              </button>
            </div>
          {/if}
        </div>

        <!-- Copy Column -->
        <div
          class="flex w-full max-w-[607px] flex-col items-center text-center transition-all delay-300 duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] {isIntersecting
            ? 'translate-y-0 opacity-100'
            : 'translate-y-8 opacity-0'}"
        >
          <div class="grid w-full grid-cols-1 grid-rows-1 justify-items-center">
            {#key currentEventIndex}
              <div
                in:fly={{ y: 12, duration: 350, delay: 80, easing: cubicOut }}
                out:fade={{ duration: 120 }}
                class="col-start-1 row-start-1 flex w-full flex-col items-center"
              >
                <div
                  class="mb-6 rounded-[6px] bg-[#5d0077] px-6 py-1.5 font-['Josefin_Sans'] text-xl font-normal tracking-[0.04em] text-white"
                >
                  {currentEvent.startsAtLabel}
                </div>
                <h3
                  class="mb-8 text-center font-['Josefin_Sans'] text-[50px] leading-none font-bold tracking-[0.04em] md:text-[70px]"
                  style="background: linear-gradient(39deg, #ff7a1a 0%, #ffd344 97%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; -webkit-text-stroke: 2px #5d0077;"
                >
                  {currentEvent.title}
                </h3>
                <!-- Glowing horizontal gradient bar -->
                <div
                  class="relative mb-8 flex h-[24px] w-full items-center justify-center"
                >
                  <div
                    class="absolute h-[12.5px] w-[90%] bg-gradient-to-r from-[#ff7a1a] to-[#ffd344] opacity-80 blur-md"
                  ></div>
                  <div
                    class="relative h-[3px] w-full bg-gradient-to-r from-[#ff7a1a] to-[#ffd344]"
                  ></div>
                </div>
                <p
                  class="mb-8 text-center font-['Plus_Jakarta_Sans'] text-base leading-relaxed font-medium tracking-[0.04em]"
                  style="background: linear-gradient(269deg, #5a0177 24%, #2d0077 43%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
                >
                  {currentEvent.excerpt}
                </p>
              </div>
            {/key}
          </div>
        </div>
      </div>
    {:else}
      <div
        class="mx-auto max-w-2xl rounded-3xl border border-white/50 bg-white/40 py-24 text-center shadow-xl backdrop-blur-sm"
      >
        <p class="mb-6 text-2xl font-medium text-[#5d0077]">
          {eventsSection.emptyText}
        </p>
        <a
          href={acaraUrl}
          class="inline-block transform rounded-full bg-[#5d0077] px-8 py-3 text-lg font-bold text-white shadow-lg transition-all hover:-translate-y-1 hover:bg-[#2a0078] hover:shadow-xl"
          >{eventsSection.archiveLabel}</a
        >
      </div>
    {/if}
  </div>
</section>

<style>
  .carousel-card {
    position: absolute;
    width: 290px;
    height: 420px;
    left: 50%;
    top: 0;
    transform: translate3d(-50%, 0, 0) scale(0.9);
    opacity: 0;
    pointer-events: none;
    transition:
      transform 500ms cubic-bezier(0.25, 1, 0.5, 1),
      opacity 500ms cubic-bezier(0.25, 1, 0.5, 1),
      box-shadow 500ms cubic-bezier(0.25, 1, 0.5, 1);
  }

  @media (min-width: 640px) {
    .carousel-card {
      width: 327px;
      height: 457px;
    }
  }

  .carousel-card.role-active {
    opacity: 1;
    pointer-events: auto;
    transform: translate3d(-50%, 0, 0) scale(1);
    z-index: 30;
  }

  @media (min-width: 1280px) {
    .carousel-card {
      width: 327px;
      height: 468px;
      left: 151px;
      top: 0px;
      transform: translate3d(-71px, 6px, 0) scale(0.9);
      opacity: 0;
      z-index: 0;
    }

    .carousel-card.role-active {
      opacity: 1;
      pointer-events: auto;
      transform: translate3d(0, 0, 0) scale(1);
      z-index: 30;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .carousel-card.role-middle {
      opacity: 1;
      transform: translate3d(-71px, 6px, 0) scaleX(0.988) scaleY(0.976);
      z-index: 20;
      box-shadow: -23px 13px 4px 0px rgba(0, 0, 0, 0.35);
    }

    .carousel-card.role-rear {
      opacity: 1;
      transform: translate3d(-151px, 6px, 0) scaleX(1.1) scaleY(0.976);
      z-index: 10;
      box-shadow: 26px 35px 4px 0px rgba(0, 0, 0, 0.5);
    }

    .carousel-card.role-hidden {
      opacity: 0;
      transform: translate3d(-71px, 6px, 0) scale(0.9);
      z-index: 0;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .carousel-card {
      transition: none !important;
    }
  }

  .acara-title {
    font-family: "Playfair Display", serif;
    font-size: 50px;
    line-height: 1.2;
    font-weight: 400;
    text-align: center;
    color: #222222;
    text-shadow: 0px 0px 20px #ffffff;
  }

  @media (min-width: 1280px) {
    .acara-title {
      position: absolute;
      width: 624px;
      height: 107px;
      left: calc(50% - 624px / 2 - 42px);
      top: calc(50% - 107px / 2 - 32px);

      font-family: "Playfair Display";
      font-style: normal;
      font-weight: 400;
      font-size: 80px;
      line-height: 107px;
      text-align: center;

      color: #222222;

      text-shadow: 0px 0px 20px #ffffff;
    }
  }
</style>
