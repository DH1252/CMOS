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
