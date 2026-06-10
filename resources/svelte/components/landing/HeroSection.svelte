<script>
  import { onMount } from "svelte";

  let { assetBase = "/images/figma-taling" } = $props();

  let logoMarkLoaded = $state(false);
  let logoTextLoaded = $state(false);
  const logoLoaded = $derived(logoMarkLoaded && logoTextLoaded);

  let logoMarkEl = $state(null);
  let logoTextEl = $state(null);

  onMount(() => {
    if (logoMarkEl?.complete) {
      logoMarkLoaded = true;
    }
    if (logoTextEl?.complete) {
      logoTextLoaded = true;
    }
  });
</script>

<section
  class="relative h-[896px] w-full overflow-hidden bg-gradient-to-br from-[#5d0077] to-[#2a0078]"
>
  <picture class="contents">
    <source srcset={`${assetBase}/hero-bg.avif`} type="image/avif" />
    <source srcset={`${assetBase}/hero-bg.webp`} type="image/webp" />
    <img
      class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-overlay"
      src={`${assetBase}/hero-bg.png`}
      alt=""
      loading="eager"
      decoding="async"
      fetchpriority="high"
    />
  </picture>
  <picture class="contents">
    <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
    <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
    <img
      class="absolute -top-[22%] -left-[20%] h-[180%] w-[170%] object-cover opacity-25 mix-blend-soft-light"
      src={`${assetBase}/botanical.png`}
      alt=""
      width="1600"
      height="1066"
    />
  </picture>

  <!-- Center Hero Graphic, Title, and Glow Wrapper -->
  <div class="hero-content-wrapper">
    <!-- Center Hero Graphic -->
    <div
      class="hero-logo-container {logoLoaded
        ? 'animate-fade-scale'
        : 'opacity-0'}"
    >
      <div class="animate-float-logo flex w-full flex-col items-center">
        <img
          bind:this={logoMarkEl}
          onload={() => {
            logoMarkLoaded = true;
          }}
          src={`${assetBase}/logo-mark.svg`}
          alt="Logo Mark"
          class="h-auto w-full drop-shadow-xl"
          loading="eager"
          decoding="async"
          fetchpriority="high"
        />
        <img
          bind:this={logoTextEl}
          onload={() => {
            logoTextLoaded = true;
          }}
          src={`${assetBase}/text-logo.svg`}
          alt="Logo Text"
          class="-mt-3 h-auto w-[83%] max-w-[330px] drop-shadow-md"
          loading="eager"
          decoding="async"
          fetchpriority="high"
        />
      </div>
    </div>

    <!-- Title -->
    <h1 class="hero-title animate-fade-up">
      dari kita<br class="md:hidden" /> untuk kita
    </h1>

    <!-- Glow Line under title -->
    <div class="hero-glow-wrapper animate-fade-in">
      <div
        class="h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[2px]"
      ></div>
    </div>
  </div>

  <img
    src={`${assetBase}/star-large.svg`}
    alt=""
    class="star-large animate-float-large pointer-events-none opacity-80 drop-shadow-2xl"
  />
  <img
    src={`${assetBase}/star-small.svg`}
    alt=""
    class="star-small animate-float-small pointer-events-none opacity-80 drop-shadow-2xl"
  />

  <div
    class="animate-fade-left absolute bottom-6 left-6 flex items-center gap-4 font-bold tracking-wider text-white md:bottom-[36px] md:left-[49px]"
  >
    <img
      src={`${assetBase}/logo-mark.svg`}
      alt=""
      class="w-[60px] md:w-[90px]"
    />
    <img
      src={`${assetBase}/text-logo.svg`}
      alt="Sentra Sinergi"
      class="w-[50px] md:w-[80px]"
    />
    <div class="h-[49px] w-px bg-white/70"></div>
    <div class="text-xs leading-tight md:text-sm">
      <p class="text-lg">#OKE</p>
      <p class="font-medium tracking-normal opacity-90">
        Optimalisasi | Kolaborasi | Ekspansi
      </p>
    </div>
  </div>
</section>

<style>
  .star-large {
    position: absolute;
    top: -60px;
    left: -90px;
    width: 200px;
    height: 188px;
  }
  @media (min-width: 768px) {
    .star-large {
      top: -100px;
      left: -150px;
      width: 320px;
      height: 301px;
    }
  }
  @media (min-width: 1024px) {
    .star-large {
      top: -155px;
      left: -239px;
      width: 507px;
      height: 476px;
    }
  }

  .star-small {
    position: absolute;
    top: 520px;
    right: -80px;
    width: 150px;
    height: 161px;
  }
  @media (min-width: 768px) {
    .star-small {
      top: 500px;
      right: -130px;
      width: 240px;
      height: 258px;
    }
  }
  @media (min-width: 1024px) {
    .star-small {
      top: 496px;
      right: -208px;
      width: 386px;
      height: 415px;
    }
  }

  .hero-content-wrapper {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    padding-top: 2rem;
    padding-bottom: 4rem;
    pointer-events: none;
    z-index: 10;
  }

  @media (min-width: 768px) {
    .hero-content-wrapper {
      position: absolute;
      inset: 0;
      display: block;
      padding: 0;
    }
  }

  .hero-logo-container {
    position: relative;
    width: 100%;
    max-width: 220px;
    margin: 0 auto;
    pointer-events: auto;
  }

  @media (min-width: 768px) {
    .hero-logo-container {
      position: absolute;
      top: 120px;
      left: 50%;
      transform: translateX(-50%);
      max-width: 398px;
      margin: 0;
    }
  }

  .hero-title {
    font-family: "Playfair Display", "Playfair_Display", Georgia, serif;
    font-size: 2.5rem;
    line-height: 1.2;
    text-align: center;
    color: #ffffff;
    text-shadow: 0px 0px 20px rgba(255, 255, 255, 0.8);
    white-space: normal;
  }

  @media (min-width: 768px) {
    .hero-title {
      position: absolute;
      top: 504px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 96px;
      line-height: 125px;
      white-space: nowrap;
    }
  }

  .hero-glow-wrapper {
    position: relative;
    width: 100%;
    max-width: 240px;
    height: 12px;
    margin: 0 auto;
    opacity: 0.8;
  }

  @media (min-width: 768px) {
    .hero-glow-wrapper {
      position: absolute;
      top: 623px;
      left: 50%;
      transform: translateX(-50%);
      max-width: 534px;
      height: 22px;
      margin: 0;
    }
  }

  .animate-fade-scale {
    animation: scaleFadeIn 1000ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }
  .animate-fade-up {
    animation: fadeInUp 1000ms cubic-bezier(0.16, 1, 0.3, 1) 200ms forwards;
    opacity: 0;
  }
  .animate-fade-in {
    animation: fadeIn 1000ms cubic-bezier(0.16, 1, 0.3, 1) 400ms forwards;
    opacity: 0;
  }
  .animate-fade-left {
    animation: fadeInLeft 1000ms cubic-bezier(0.16, 1, 0.3, 1) 500ms forwards;
    opacity: 0;
  }

  @media (min-width: 768px) {
    .animate-fade-scale {
      animation: scaleFadeInCenter 1000ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .animate-fade-up {
      animation: fadeInUpCenter 1000ms cubic-bezier(0.16, 1, 0.3, 1) 200ms
        forwards;
    }
    .animate-fade-in {
      animation: fadeInCenter 1000ms cubic-bezier(0.16, 1, 0.3, 1) 400ms
        forwards;
    }
  }

  .animate-float-large {
    animation: floatLarge 8s ease-in-out infinite;
  }
  .animate-float-small {
    animation: floatSmall 10s ease-in-out infinite;
  }
  .animate-float-logo {
    animation: floatLogo 6s ease-in-out infinite alternate;
  }

  @keyframes scaleFadeIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes scaleFadeInCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 0) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translate(-50%, 0) scale(1);
    }
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(16px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeInUpCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 16px);
    }
    to {
      opacity: 1;
      transform: translate(-50%, 0);
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 0.8;
    }
  }

  @keyframes fadeInCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 0);
    }
    to {
      opacity: 0.8;
      transform: translate(-50%, 0);
    }
  }

  @keyframes fadeInLeft {
    from {
      opacity: 0;
      transform: translateX(-16px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes floatLarge {
    0%,
    100% {
      transform: translateY(0px) rotate(5deg);
    }
    50% {
      transform: translateY(-15px) rotate(7deg);
    }
  }

  @keyframes floatSmall {
    0%,
    100% {
      transform: translateY(0px) rotate(8deg);
    }
    50% {
      transform: translateY(12px) rotate(6deg);
    }
  }

  @keyframes floatLogo {
    0% {
      transform: translateY(0px) scale(1);
    }
    100% {
      transform: translateY(-8px) scale(1.015);
      filter: drop-shadow(0 20px 20px rgba(255, 255, 255, 0.08));
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .animate-fade-scale,
    .animate-fade-up,
    .animate-fade-in,
    .animate-fade-left {
      animation: none !important;
      opacity: 1 !important;
      transform: translate(-50%, 0) !important;
    }
    .animate-fade-left {
      transform: none !important;
    }
    .animate-float-large,
    .animate-float-small,
    .animate-float-logo {
      animation: none !important;
    }
  }
</style>
