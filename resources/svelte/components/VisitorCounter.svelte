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
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.75rem !important;
    font-family:
      "Public Sans",
      -apple-system,
      BlinkMacSystemFont,
      "Segoe UI",
      Roboto,
      sans-serif !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    color: #4b5563 !important; /* Tailwind text-gray-600 */
    line-height: 1.5 !important;
  }

  .visitor-counter-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem !important;
    color: #4b5563 !important;
    opacity: 0.9 !important;
  }

  .visitor-counter-item strong {
    font-weight: 700 !important;
    color: #1f2937 !important; /* Tailwind text-gray-800 */
  }

  .visitor-counter-divider {
    display: inline-block !important;
    width: 1px !important;
    height: 0.75rem !important;
    background-color: #d1d5db !important; /* Tailwind border-gray-300 */
    opacity: 1 !important;
  }

  @media (max-width: 480px) {
    .visitor-counter {
      gap: 0.4rem !important;
    }
  }
</style>
