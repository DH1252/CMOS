<script>
  import OptimizedImage from "./OptimizedImage.svelte";
  import { ChevronLeft, ChevronRight } from "lucide-svelte";

  let {
    events = [],
    title = "Acara Mendatang",
    description = "",
    archiveLabel = "Semua acara",
    archiveUrl = "/acara",
  } = $props();

  let currentIndex = $state(0);

  const canGoPrev = $derived(currentIndex > 0);
  const canGoNext = $derived(currentIndex < events.length - 1);

  const prev = () => {
    if (canGoPrev) currentIndex--;
  };

  const next = () => {
    if (canGoNext) currentIndex++;
  };

  const visibleEvents = $derived(
    events.slice(currentIndex, currentIndex + 3).concat(
      events.length < 3 ? events.slice(0, 3 - events.length) : [],
    ),
  );

  // Unique key that handles duplicates and null URLs
  const getEventKey = (event, index) => `carousel-${currentIndex}-${index}`;
</script>

<div class="taling-carousel-shell">
  <div class="taling-carousel-header">
    <div class="taling-carousel-title-wrap">
      <h2 class="taling-carousel-title">{title}</h2>
      <p class="taling-carousel-desc">{description}</p>
    </div>
    {#if archiveUrl}
      <a href={archiveUrl} class="taling-carousel-link">
        {archiveLabel}
        <ChevronRight size={16} />
      </a>
    {/if}
  </div>

  {#if events.length}
    <div class="taling-carousel-track">
      <button
        class="taling-carousel-nav taling-carousel-nav-prev"
        onclick={prev}
        disabled={!canGoPrev}
        aria-label="Sebelumnya"
      >
        <div class="taling-carousel-nav-icon">
          <ChevronLeft size={20} />
        </div>
      </button>

      <div class="taling-carousel-cards">
        {#each visibleEvents as event, i (getEventKey(event, i))}
          <a
            href={event.url}
            class="taling-carousel-card"
            data-slot={i}
          >
            {#if event.poster}
              <div class="taling-carousel-card-media">
                <OptimizedImage
                  src={event.poster}
                  alt={event.title}
                  class="taling-carousel-card-img"
                  loading={i === 0 ? "eager" : "lazy"}
                  decoding="async"
                  sizes="(min-width: 1024px) 400px, (min-width: 768px) 300px, 250px"
                />
                <div class="taling-carousel-card-overlay"></div>
              </div>
            {:else}
              <div class="taling-carousel-card-placeholder">
                <span>{event.title?.[0] ?? "?"}</span>
              </div>
            {/if}

            <div class="taling-carousel-card-content">
              <div class="taling-carousel-card-date">
                {event.startsAtLabel || "Segera"}
              </div>
              <h3 class="taling-carousel-card-title">{event.title}</h3>
              {#if event.location}
                <div class="taling-carousel-card-location">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  {event.location}
                </div>
              {/if}
            </div>

            {#if i === 2}
              <div class="taling-carousel-card-frame"></div>
            {/if}
          </a>
        {/each}
      </div>

      <button
        class="taling-carousel-nav taling-carousel-nav-next"
        onclick={next}
        disabled={!canGoNext}
        aria-label="Selanjutnya"
      >
        <div class="taling-carousel-nav-icon">
          <ChevronRight size={20} />
        </div>
      </button>
    </div>

    {#if events.length > 1}
      <div class="taling-carousel-dots">
        {#each Array(events.length) as _, i}
          <button
            class="taling-carousel-dot"
            class:taling-carousel-dot-active={i === currentIndex}
            onclick={() => (currentIndex = i)}
            aria-label="Ke acara {i + 1}"
          ></button>
        {/each}
      </div>
    {/if}
  {:else}
    <div class="taling-carousel-empty">
      <p>Belum ada acara mendatang.</p>
    </div>
  {/if}
</div>

<style>
  .taling-carousel-shell {
    width: 100%;
  }

  .taling-carousel-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .taling-carousel-title-wrap {
    text-align: center;
  }

  .taling-carousel-title {
    margin: 0;
    font-family: var(--taling-font-serif);
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 400;
    line-height: 1.1;
    color: var(--taling-white);
    text-shadow: 0 0 40px rgba(255, 255, 255, 1);
  }

  .taling-carousel-desc {
    margin: 0.75rem 0 0;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    max-width: 50ch;
  }

  .taling-carousel-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgba(255, 255, 255, 0.6);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: color 160ms ease;
    flex-shrink: 0;
  }

  .taling-carousel-link:hover {
    color: var(--taling-yellow);
  }

  .taling-carousel-track {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .taling-carousel-nav {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--taling-purple);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 0.5rem;
    color: var(--taling-white);
    cursor: pointer;
    transition: background 160ms ease, opacity 160ms ease;
  }

  .taling-carousel-nav:hover:not(:disabled) {
    background: color-mix(in srgb, var(--taling-purple) 80%, white);
  }

  .taling-carousel-nav:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .taling-carousel-nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .taling-carousel-cards {
    display: flex;
    justify-content: center;
    gap: 0;
    flex: 1;
    min-height: 420px;
    position: relative;
  }

  .taling-carousel-card {
    position: relative;
    width: clamp(200px, 30vw, 320px);
    height: 420px;
    border-radius: 0.75rem;
    overflow: visible;
    text-decoration: none;
    transition: transform 300ms ease;
    flex-shrink: 0;
  }

  .taling-carousel-card:nth-child(1) {
    z-index: 1;
  }

  .taling-carousel-card:nth-child(2) {
    z-index: 3;
  }

  .taling-carousel-card:nth-child(3) {
    z-index: 1;
  }

  /* Position cards based on visual slot (0=left, 1=center, 2=right) */
  .taling-carousel-card[data-slot="0"] {
    transform: translateX(15%) scale(0.9);
  }

  .taling-carousel-card[data-slot="1"] {
    transform: translateX(-5%) scale(1);
  }

  .taling-carousel-card[data-slot="2"] {
    transform: translateX(-25%) scale(0.9);
  }

  /* Hover animations - only affect non-center cards */
  .taling-carousel-card[data-slot="0"]:hover {
    transform: translateX(10%) scale(0.95);
  }

  .taling-carousel-card[data-slot="2"]:hover {
    transform: translateX(-20%) scale(0.95);
  }

  .taling-carousel-card-media {
    position: absolute;
    inset: 0;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow:
      0 25px 35px rgba(0, 0, 0, 0.5),
      0 10px 20px rgba(0, 0, 0, 0.35);
  }

  /* Left card has shifted shadow */
  .taling-carousel-card[data-slot="0"] .taling-carousel-card-media {
    box-shadow:
      26px 35px rgba(0, 0, 0, 0.5),
      0 10px 20px rgba(0, 0, 0, 0.35);
  }

  .taling-carousel-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .taling-carousel-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      to bottom,
      transparent 40%,
      rgba(0, 0, 0, 0.5) 70%,
      rgba(0, 0, 0, 0.8) 100%
    );
  }

  .taling-carousel-card-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--taling-purple) 30%, var(--taling-ink));
    border-radius: 0.75rem;
    font-size: 3rem;
    color: var(--taling-yellow);
  }

  .taling-carousel-card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1.5rem;
    z-index: 2;
  }

  .taling-carousel-card-date {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    background: var(--taling-purple);
    color: var(--taling-white);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.96px;
    border-radius: 0.25rem;
    margin-bottom: 0.75rem;
  }

  .taling-carousel-card-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--taling-white);
    line-height: 1.2;
  }

  .taling-carousel-card-location {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
  }

  /* Orange gradient frame on the rightmost card */
  .taling-carousel-card-frame {
    position: absolute;
    inset: -3px;
    border-radius: 0.85rem;
    background: linear-gradient(
      135deg,
      #ff7a1a 0%,
      #ffca3a 100%
    );
    z-index: -1;
    opacity: 0.8;
  }

  .taling-carousel-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
  }

  .taling-carousel-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    border: none;
    cursor: pointer;
    transition: background 160ms ease, transform 160ms ease;
  }

  .taling-carousel-dot:hover {
    background: rgba(255, 255, 255, 0.5);
  }

  .taling-carousel-dot-active {
    background: var(--taling-yellow);
    transform: scale(1.3);
  }

  .taling-carousel-empty {
    padding: 4rem 2rem;
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
    border: 1px dashed rgba(255, 255, 255, 0.15);
    border-radius: 1rem;
  }

  @media (max-width: 768px) {
    .taling-carousel-cards {
      min-height: 300px;
    }

    .taling-carousel-card {
      width: clamp(160px, 50vw, 220px);
      height: 300px;
    }

    .taling-carousel-nav {
      width: 36px;
      height: 36px;
    }
  }
</style>