<script>
  import { onMount } from "svelte";
  import VisitorCounter from "../VisitorCounter.svelte";

  let {
    infoUrl = "/",
    acaraUrl = "/",
    departemenUrl = "/",
    tentangUrl = "/",
    kompetisiUrl = "/",
    organizationName = "HIMATEKKOM ITS",
    footerDescription = "Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.",
  } = $props();

  let footerElement = $state(null);
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

    if (footerElement) {
      observer.observe(footerElement);
    }

    return () => {
      observer.disconnect();
    };
  });
</script>

<footer
  bind:this={footerElement}
  class="relative w-full overflow-hidden bg-white py-16 font-['Plus_Jakarta_Sans'] text-black transition-all duration-1000 ease-[cubic-bezier(0.25,1,0.5,1)] {isIntersecting
    ? 'translate-y-0 opacity-100'
    : 'translate-y-4 opacity-0'}"
>
  <div class="relative z-10 container mx-auto px-6 md:px-[5%]">
    <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-12">
      <!-- Branding & Description -->
      <div class="col-span-1 flex flex-col items-start md:col-span-6">
        <div class="mb-12 flex flex-col items-start gap-6 md:flex-row">
          <h2
            class="text-3xl leading-[1.1] font-semibold tracking-tight whitespace-pre-line text-black md:text-[40px]"
          >
            {organizationName}
          </h2>
          <!-- You can insert logo icon here if needed -->
        </div>

        <div class="mb-16 flex gap-2">
          <div
            class="h-[30px] w-[30px] bg-[#680caf] transition-transform duration-300 hover:scale-110 hover:-rotate-6"
          ></div>
          <div
            class="h-[30px] w-[30px] bg-[#ffd344] transition-transform duration-300 hover:scale-110 hover:rotate-6"
          ></div>
        </div>

        <p
          class="mb-6 font-['The_Seasons'] text-[24px] tracking-wide text-black italic"
        >
          sentra sinergi
        </p>

        <p class="max-w-md text-base leading-relaxed text-black opacity-90">
          {footerDescription}
        </p>

        <div class="mt-8 flex gap-2">
          <div
            class="h-[30px] w-[30px] cursor-pointer bg-black transition-all duration-300 hover:-translate-y-1 hover:scale-115 hover:bg-[#ff7a1a]"
          ></div>
          <div
            class="h-[30px] w-[30px] cursor-pointer bg-black transition-all duration-300 hover:-translate-y-1 hover:scale-115 hover:bg-[#ff7a1a]"
          ></div>
          <div
            class="h-[30px] w-[30px] cursor-pointer bg-black transition-all duration-300 hover:-translate-y-1 hover:scale-115 hover:bg-[#ff7a1a]"
          ></div>
          <div
            class="h-[30px] w-[30px] cursor-pointer bg-black transition-all duration-300 hover:-translate-y-1 hover:scale-115 hover:bg-[#ff7a1a]"
          ></div>
        </div>
      </div>

      <!-- Links: Hima -->
      <div class="col-span-1 flex flex-col md:col-span-3 md:items-end">
        <h3
          class="mb-6 text-left font-['The_Seasons'] text-[36px] font-semibold tracking-wide text-black lowercase md:text-right"
        >
          hima
        </h3>
        <div class="mb-6 h-[1px] w-full bg-gray-200 md:w-[144px]"></div>
        <div
          class="flex flex-col items-start gap-4 text-[16px] text-black md:items-end"
        >
          <a
            href={infoUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >kabar terbaru</a
          >
          <a
            href={infoUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >pengumuman</a
          >
          <a
            href={departemenUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >departemen</a
          >
          <a
            href={tentangUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >tentang kami</a
          >
        </div>
      </div>

      <!-- Links: Proker -->
      <div class="col-span-1 flex flex-col md:col-span-3 md:items-end">
        <h3
          class="mb-6 text-left font-['The_Seasons'] text-[36px] font-semibold tracking-wide text-black lowercase md:text-right"
        >
          proker
        </h3>
        <div class="mb-6 h-[1px] w-full bg-gray-200 md:w-[144px]"></div>
        <div
          class="flex flex-col items-start gap-4 text-[16px] text-black md:items-end"
        >
          <a
            href={kompetisiUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >kompetisi</a
          >
          <a
            href={acaraUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >acara</a
          >
          <a
            href={departemenUrl}
            class="lowercase transition-all duration-300 hover:translate-x-1 hover:text-[#ff7a1a] md:hover:-translate-x-1"
            >departemen</a
          >
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="mb-6 h-[1px] w-full bg-gray-200"></div>
    <div
      class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
      <p class="text-[16px] font-normal text-black">© HIMATEKKOM ITS 2026</p>
      <VisitorCounter />
    </div>
  </div>
</footer>

<style>
  @media (prefers-reduced-motion: reduce) {
    footer {
      transition: none !important;
      opacity: 1 !important;
      transform: none !important;
    }
    a {
      transition: none !important;
      transform: none !important;
    }
  }
</style>
