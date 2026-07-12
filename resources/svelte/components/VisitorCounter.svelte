<script>
  import { page } from "@inertiajs/svelte";

  const stats = $derived(page.props.visitorStats ?? {});
</script>

<div class="visitor-counter">
  <span class="visitor-counter-item">
    <i class="fas fa-eye" aria-hidden="true"></i>
    Pengunjung hari ini:
    <strong>{stats.today ?? 0}</strong>
  </span>
  <span class="visitor-counter-divider" aria-hidden="true"></span>
  <span class="visitor-counter-item">
    <i class="fas fa-calendar-check" aria-hidden="true"></i>
    Bulan ini:
    <strong>{stats.thisMonth ?? 0}</strong>
  </span>
</div>

<style>
  .visitor-counter {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem 0.75rem;
    font-size: 0.75rem;
  }

  .visitor-counter-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: currentColor;
    opacity: 0.9;
  }

  .visitor-counter-item strong {
    font-weight: 800;
  }

  .visitor-counter-divider {
    display: inline-block;
    width: 1px;
    height: 0.7rem;
    background: currentColor;
    opacity: 0.3;
  }

  @media (max-width: 480px) {
    .visitor-counter-divider {
      display: none;
    }

    .visitor-counter {
      gap: 0.35rem;
    }
  }
</style>
