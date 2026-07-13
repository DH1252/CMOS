<script>
  import { onMount } from "svelte";

  let {
    assetBase = "/images/figma-taling",
    cabinetTitle = "KABINET KAMI",
    departemenUrl = "/departemen",
  } = $props();

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
  class="relative flex w-full justify-center overflow-hidden bg-transparent"
>
  <!-- The exact 1440x1062 canvas representation -->
  <div
    class="pointer-events-none relative z-10 aspect-[1440/1062] w-full max-w-[1440px]"
  >
    <!-- big blop (550:3431) -->
    <img
      src="{assetBase}/big-blop.svg"
      class="blob-glow blob-big absolute opacity-80 mix-blend-screen blur-[100px]"
      style="left: 0.01%; top: 0%; width: 48.64%; height: 88.99%;"
      alt=""
    />

    <!-- blop-1 (550:3447) -->
    <img
      src="{assetBase}/blop-1.svg"
      class="blob-glow blob-1 absolute opacity-80 mix-blend-screen blur-[90px]"
      style="left: 76.93%; top: 18.62%; width: 23.07%; height: 40.91%;"
      alt=""
    />

    <!-- blop-2 (550:3462) -->
    <img
      src="{assetBase}/blop-2.svg"
      class="blob-glow blob-2 absolute opacity-80 mix-blend-screen blur-[90px]"
      style="left: 83.76%; top: 51.55%; width: 11.67%; height: 21.65%;"
      alt=""
    />

    <!-- blop-3 (550:3477) -->
    <img
      src="{assetBase}/blop-3.svg"
      class="blob-glow blob-3 absolute opacity-80 mix-blend-screen blur-[60px]"
      style="left: 21.99%; top: 71.55%; width: 14.22%; height: 24.58%;"
      alt=""
    />

    <!-- KABINET KAMI (550:3492) -->
    <!-- Positioned exactly using Figma coordinates relative to 1440x1062 -->
    <!-- Added w-max and px-8 to prevent background-clip from cutting off the italic overhang and heavy drop shadows -->
    <div
      class="pointer-events-auto absolute flex flex-col items-center justify-center transition-all duration-1000 ease-[cubic-bezier(0.25,1,0.5,1)] {isIntersecting
        ? 'translate-y-0 opacity-100'
        : 'translate-y-12 opacity-0'}"
      style="left: 10%; top: 10%; width: 80%; height: 24%;"
    >
      <h3 class="sentra-title w-max px-8 whitespace-nowrap leading-[0.95] mb-2">
        sentra sinergi
      </h3>
      <h2 class="kabinet-title w-max px-8 whitespace-nowrap leading-[0.95]">
        {cabinetTitle}
      </h2>
    </div>

    <!-- Mask group (550:3493) -->
    <div
      class="absolute overflow-hidden"
      style="left: 0.01%; top: 34.77%; width: 99.99%; height: 61.08%;"
    >
      <picture class="contents">
        <source srcset={`${assetBase}/cabinet-masked.avif`} type="image/avif" />
        <source srcset={`${assetBase}/cabinet-masked.webp`} type="image/webp" />
        <img
          src="{assetBase}/cabinet-masked.png"
          class="absolute top-0 left-0 h-full w-full object-cover object-bottom transition-all delay-200 duration-1000 ease-[cubic-bezier(0.25,1,0.5,1)] {isIntersecting
            ? 'translate-y-0 scale-100 opacity-100'
            : 'translate-y-[15%] scale-105 opacity-0'}"
          alt=""
        />
      </picture>
    </div>

    <!-- View Department Button Overlay -->
    <div
      class="pointer-events-none absolute left-0 right-0 z-30 flex items-center justify-center transition-all duration-1000 ease-[cubic-bezier(0.25,1,0.5,1)] delay-300 {isIntersecting
        ? 'translate-y-0 opacity-100'
        : 'translate-y-12 opacity-0'}"
      style="top: 76%;"
    >
      <a
        href={departemenUrl}
        class="taling-pill taling-pill-white pointer-events-auto shadow-[0_12px_32px_rgba(255,255,255,0.15)] hover:scale-105 active:scale-95 transition-all duration-200"
      >
        <span>Jelajahi Departemen</span>
        <i class="fas fa-arrow-right"></i>
      </a>
    </div>

    <!-- Rectangle 137 (550:3496) Glowing Bar -->
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 111"
      fill="none"
      preserveAspectRatio="none"
      class="glowing-bar absolute mix-blend-screen {isIntersecting
        ? 'revealed'
        : ''}"
      style="left: 0%; top: 89.41%; width: 100%; height: 10.45%; overflow: visible;"
    >
      <g filter="url(#filter0_d_550_3496)">
        <path
          d="M128.519 15.6478L1439.9 12.4525L1358.47 81.1325L0.00499799 84.4425L128.519 15.6478Z"
          fill="url(#paint0_linear_550_3496)"
          shape-rendering="crispEdges"
        />
      </g>
      <defs>
        <filter
          id="filter0_d_550_3496"
          x="-77.8059"
          y="-7.62939e-05"
          width="1517.71"
          height="110.895"
          filterUnits="userSpaceOnUse"
          color-interpolation-filters="sRGB"
        >
          <feFlood flood-opacity="0" result="BackgroundImageFix" />
          <feColorMatrix
            in="SourceAlpha"
            type="matrix"
            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
            result="hardAlpha"
          />
          <feOffset dx="-58.3581" dy="7" />
          <feGaussianBlur stdDeviation="9.72636" />
          <feComposite in2="hardAlpha" operator="out" />
          <feColorMatrix
            type="matrix"
            values="0 0 0 0 0.216346 0 0 0 0 0.216346 0 0 0 0 0.216346 0 0 0 0.25 0"
          />
          <feBlend
            mode="normal"
            in2="BackgroundImageFix"
            result="effect1_dropShadow_550_3496"
          />
          <feBlend
            mode="normal"
            in="SourceGraphic"
            in2="effect1_dropShadow_550_3496"
            result="shape"
          />
        </filter>
        <linearGradient
          id="paint0_linear_550_3496"
          x1="955.147"
          y1="28.942"
          x2="955.215"
          y2="47.1903"
          gradientUnits="userSpaceOnUse"
        >
          <stop stop-color="#FF7A1A" stop-opacity="0.8" />
          <stop offset="1" stop-color="#2A0078" stop-opacity="0" />
        </linearGradient>
      </defs>
    </svg>

    <!-- blop-4 (550:3497) -->
    <img
      src="{assetBase}/blop-4.svg"
      class="blob-glow blob-4 absolute z-20 opacity-80 mix-blend-screen blur-[70px]"
      style="left: 73.77%; top: 65.87%; width: 19.51%; height: 34.12%;"
      alt=""
    />
  </div>
</section>

<style>
  .kabinet-title {
    font-family: "Josefin Sans", sans-serif;
    font-style: italic;
    font-weight: 700;
    font-size: 120px;
    line-height: 1.22;
    text-align: center;
    letter-spacing: -0.08em;

    background: linear-gradient(291.65deg, #5d0077 21.59%, #ff7a1a 77.09%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-fill-color: transparent;

    filter: drop-shadow(25.8908px 135.002px 38.8361px rgba(0, 0, 0, 0.01))
      drop-shadow(16.6441px 86.919px 35.1375px rgba(0, 0, 0, 0.12))
      drop-shadow(9.2467px 49.0075px 29.5894px rgba(0, 0, 0, 0.4))
      drop-shadow(3.69868px 21.2674px 22.1921px rgba(0, 0, 0, 0.68))
      drop-shadow(0.92467px 5.54802px 12.0207px rgba(0, 0, 0, 0.79));
  }

  .sentra-title {
    font-family: "The Seasons", "The Seasons", Georgia, serif !important;
    font-style: italic;
    font-weight: 300;
    font-size: 120px;
    line-height: 1.22;
    text-align: center;
    color: #ffffff;
    text-shadow: 0px 0px 20px #ffffff;

    filter: drop-shadow(25.8908px 135.002px 38.8361px rgba(0, 0, 0, 0.01))
      drop-shadow(16.6441px 86.919px 35.1375px rgba(0, 0, 0, 0.12))
      drop-shadow(9.2467px 49.0075px 29.5894px rgba(0, 0, 0, 0.4))
      drop-shadow(3.69868px 21.2674px 22.1921px rgba(0, 0, 0, 0.68))
      drop-shadow(0.92467px 5.54802px 12.0207px rgba(0, 0, 0, 0.79));
  }

  .taling-pill-white {
    background: #ffffff !important;
    color: var(--taling-purple) !important;
  }

  .taling-pill-white:hover {
    background: #f4f4f8 !important;
    color: var(--taling-purple) !important;
  }

  .blob-glow {
    will-change: transform, opacity;
  }

  .blob-big {
    animation: pulse-blob-1 18s ease-in-out infinite alternate;
  }

  .blob-1 {
    animation: pulse-blob-2 14s ease-in-out infinite alternate;
    animation-delay: -3s;
  }

  .blob-2 {
    animation: pulse-blob-3 16s ease-in-out infinite alternate;
    animation-delay: -6s;
  }

  .blob-3 {
    animation: pulse-blob-1 12s ease-in-out infinite alternate;
    animation-delay: -1s;
  }

  .blob-4 {
    animation: pulse-blob-2 20s ease-in-out infinite alternate;
    animation-delay: -8s;
  }

  .glowing-bar {
    transform: rotate(-0.082deg) scaleX(0.8);
    transform-origin: center;
    opacity: 0;
    transition:
      opacity 1.2s cubic-bezier(0.25, 1, 0.5, 1) 300ms,
      transform 1.2s cubic-bezier(0.25, 1, 0.5, 1) 300ms;
  }

  .glowing-bar.revealed {
    transform: rotate(-0.082deg) scaleX(1);
    opacity: 0.8;
  }

  @keyframes pulse-blob-1 {
    0%,
    100% {
      transform: scale(1) translate(0, 0);
      opacity: 0.7;
    }
    50% {
      transform: scale(1.08) translate(1%, -2%);
      opacity: 0.85;
    }
  }

  @keyframes pulse-blob-2 {
    0%,
    100% {
      transform: scale(1) translate(0, 0);
      opacity: 0.6;
    }
    50% {
      transform: scale(1.15) translate(-2%, 3%);
      opacity: 0.8;
    }
  }

  @keyframes pulse-blob-3 {
    0%,
    100% {
      transform: scale(1) translate(0, 0);
      opacity: 0.75;
    }
    50% {
      transform: scale(1.1) translate(2%, 2%);
      opacity: 0.9;
    }
  }

  @media (max-width: 1024px) {
    .kabinet-title,
    .sentra-title {
      font-size: 8vw;
      line-height: 8vw;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .blob-glow {
      animation: none !important;
    }
    .glowing-bar {
      transition: none !important;
      opacity: 0.8 !important;
      transform: rotate(-0.082deg) scaleX(1) !important;
    }
  }
</style>
