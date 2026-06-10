<script>
  import OptimizedImage from "../../components/OptimizedImage.svelte";
  import { onMount } from "svelte";

  let {
    assetBase = "/images/figma-taling",
    newsCards = [],
    infoUrl = "/",
    emptyText = "",
    archiveLabel = "",
  } = $props();
  const hasNewsCards = $derived(newsCards.length > 0);

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
      { threshold: 0.02 },
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
  class="relative w-full overflow-hidden bg-[#222] py-24 text-white"
>
  <picture class="contents">
    <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
    <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
    <img
      class="botanical-bg pointer-events-none absolute -top-[120%] -left-[18%] h-[260%] w-[170%] object-cover mix-blend-darken {isIntersecting
        ? 'revealed'
        : ''}"
      src={`${assetBase}/botanical.png`}
      alt=""
      width="1600"
      height="1066"
    />
  </picture>

  <div class="relative z-10 container mx-auto px-6 md:px-12">
    <div
      class="mb-16 flex flex-col items-start gap-6 transition-all duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] md:flex-row md:items-center {isIntersecting
        ? 'translate-y-0 opacity-100'
        : 'translate-y-8 opacity-0'}"
    >
      <div
        class="mt-2 ml-2 h-[54px] w-[48px] flex-shrink-0 border-[4px] border-white bg-transparent shadow-[12px_12px_0_rgba(255,255,255,0.95)] transition-transform delay-100 duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] md:mt-0 {isIntersecting
          ? 'scale-100 rotate-0'
          : 'scale-75 -rotate-6'}"
      ></div>
      <div class="ml-4 md:ml-0">
        <h2
          class="font-['Playfair_Display'] text-5xl leading-tight font-normal text-white drop-shadow-sm md:text-[64px]"
        >
          Kabar Terbaru
        </h2>
        <p class="mt-2 text-lg text-gray-300">
          Berita kegiatan, pengumuman, dan dokumentasi resmi HIMATEKKOM ITS
        </p>
      </div>
    </div>

    <div
      class="hide-scrollbar -mx-4 flex snap-x gap-6 overflow-x-auto px-4 pt-4 pb-12 md:mx-0 md:px-0"
    >
      {#if hasNewsCards}
        {#each newsCards as item, index}
          <a
            href={item.url}
            style="--delay: {index * 100}ms;"
            class="group news-card relative h-[397px] w-[280px] flex-none snap-start overflow-hidden rounded-lg bg-zinc-800 shadow-xl transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-white/10 md:w-[313px] {isIntersecting
              ? 'revealed'
              : ''}"
          >
            {#if item.coverImage}
              <OptimizedImage
                src={item.coverImage}
                alt={item.title}
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                loading={index === 0 ? "eager" : "lazy"}
                sizes="313px"
              />
            {:else}
              <div
                class="absolute inset-0 bg-gradient-to-b from-zinc-700 to-zinc-900"
              ></div>
            {/if}

            <div
              class="absolute inset-0 bg-gradient-to-t from-[#222]/95 via-[#222]/40 to-transparent transition-opacity duration-300"
            ></div>

            <div
              class="absolute inset-0 flex flex-col justify-end p-6 backdrop-blur-[1px]"
            >
              <h3
                class="mb-1 font-['Playfair_Display'] text-[24px] font-bold text-white drop-shadow-md"
              >
                {item.title}
              </h3>
              <p class="mb-4 text-xs font-medium tracking-wide text-gray-300">
                {item.publishedAtLabel ?? ""}
              </p>
              <div
                class="mb-5 h-[3px] w-[100px] bg-[#ffd344] shadow-[0_0_8px_#ffd344] transition-all duration-300 group-hover:w-[150px]"
              ></div>
              <p
                class="mb-5 w-max rounded-sm bg-[#ffd344] px-3 py-1 text-xs font-semibold tracking-wider text-[#222] uppercase"
              >
                {item.category}
              </p>
              <p
                class="mb-8 line-clamp-3 text-sm leading-relaxed text-gray-200"
              >
                {item.excerpt}
              </p>
              <p
                class="text-xs font-bold text-[#ffd344] decoration-2 underline-offset-4 transition-transform duration-300 group-hover:translate-x-1"
              >
                klik untuk baca! &rarr;
              </p>
            </div>
          </a>
        {/each}
      {:else}
        <div
          class="w-full rounded-2xl border border-zinc-700/50 bg-zinc-800/50 py-20 text-center transition-all duration-700 {isIntersecting
            ? 'translate-y-0 opacity-100'
            : 'translate-y-4 opacity-0'}"
        >
          <p class="mb-4 text-xl font-medium text-gray-400">{emptyText}</p>
          <a
            href={infoUrl}
            class="inline-block text-lg font-bold text-[#ffd344] decoration-2 underline-offset-4 transition-all hover:text-white hover:underline"
            >{archiveLabel} &rarr;</a
          >
        </div>
      {/if}
    </div>
  </div>
</section>

<style>
  .hide-scrollbar::-webkit-scrollbar {
    display: none;
  }
  .hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  .botanical-bg {
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
  }

  .botanical-bg.revealed {
    opacity: 0.1;
    animation: slow-drift 40s ease-in-out infinite alternate;
  }

  .news-card {
    opacity: 0;
    transform: translateY(30px);
    transition:
      opacity 800ms cubic-bezier(0.25, 1, 0.5, 1),
      transform 800ms cubic-bezier(0.25, 1, 0.5, 1),
      box-shadow 300ms ease,
      transform 300ms ease;
    transition-delay: var(--delay);
  }

  .news-card.revealed {
    opacity: 1;
    transform: translateY(0);
  }

  @keyframes slow-drift {
    0% {
      transform: translate(0, 0) scale(1) rotate(0deg);
    }
    50% {
      transform: translate(1%, -1%) scale(1.02) rotate(0.5deg);
    }
    100% {
      transform: translate(-1%, 1%) scale(0.98) rotate(-0.5deg);
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .botanical-bg.revealed {
      animation: none !important;
    }
    .news-card {
      transition: none !important;
      opacity: 1 !important;
      transform: none !important;
    }
  }
</style>
