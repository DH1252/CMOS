<script>
  import { onMount } from "svelte";

  let { field } = $props();

  let graphics = $state([]);
  let isUploading = $state(false);
  let uploadError = $state("");

  onMount(() => {
    try {
      if (typeof field.value === "string" && field.value) {
        graphics = JSON.parse(field.value);
      } else if (Array.isArray(field.value)) {
        graphics = field.value;
      }
    } catch (e) {
      console.error("Failed to parse graphics", e);
      graphics = [];
    }
  });

  async function handleFileChange(event, index = null) {
    let file = event.target.files[0];
    if (!file) return;

    isUploading = true;
    uploadError = "";

    const formData = new FormData();
    formData.append("image", file);

    try {
      const response = await window.axios.post(
        "/departments/upload-graphic",
        formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        },
      );

      const data = response.data;

      if (index !== null) {
        graphics[index].image = data.url;
      } else {
        graphics = [
          ...graphics,
          { id: Date.now().toString(), image: data.url, overlays: [] },
        ];
      }
    } catch (e) {
      uploadError = e.response?.data?.message || e.message || "Upload failed";
    } finally {
      isUploading = false;
      event.target.value = "";
    }
  }

  async function handleOverlayPictureChange(event, gIndex, oIndex) {
    let file = event.target.files[0];
    if (!file) return;

    isUploading = true;
    uploadError = "";

    const formData = new FormData();
    formData.append("image", file);

    try {
      const response = await window.axios.post(
        "/departments/upload-graphic",
        formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        },
      );

      const data = response.data;

      graphics[gIndex].overlays[oIndex].picture = data.url;
      graphics = [...graphics];
    } catch (e) {
      uploadError = e.response?.data?.message || e.message || "Upload failed";
    } finally {
      isUploading = false;
      event.target.value = "";
    }
  }

  function addGraphic() {
    // triggers file input
    document.getElementById(`upload-graphic-new`).click();
  }

  function removeGraphic(index) {
    graphics = graphics.filter((_, i) => i !== index);
  }

  function addOverlay(graphicIndex) {
    graphics[graphicIndex].overlays.push({ name: "", role: "", x: 50, y: 50 });
    graphics = [...graphics];
  }

  function removeOverlay(graphicIndex, overlayIndex) {
    graphics[graphicIndex].overlays = graphics[graphicIndex].overlays.filter(
      (_, i) => i !== overlayIndex,
    );
    graphics = [...graphics];
  }

  let activeOverlay = null;

  function startDrag(e, gIndex, oIndex) {
    const overlay = graphics[gIndex].overlays[oIndex];
    if (overlay.x === undefined) overlay.x = 50;
    if (overlay.y === undefined) overlay.y = 50;

    const imgContainer = e.currentTarget.closest(".image-container");
    const rect = imgContainer.getBoundingClientRect();

    activeOverlay = {
      gIndex,
      oIndex,
      startX: e.clientX,
      startY: e.clientY,
      origX: overlay.x,
      origY: overlay.y,
      rect,
    };

    window.addEventListener("pointermove", onDrag);
    window.addEventListener("pointerup", endDrag);

    // Prevent default drag behaviors
    e.preventDefault();
  }

  function onDrag(e) {
    if (!activeOverlay) return;
    const { gIndex, oIndex, startX, startY, origX, origY, rect } =
      activeOverlay;

    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    const zoom = (graphics[gIndex].scale ?? 100) / 100;
    const dxPct = (dx / (rect.width * zoom)) * 100;
    const dyPct = (dy / (rect.height * zoom)) * 100;

    let newX = origX + dxPct;
    let newY = origY + dyPct;

    newX = Math.max(0, Math.min(100, newX));
    newY = Math.max(0, Math.min(100, newY));

    graphics[gIndex].overlays[oIndex].x = newX;
    graphics[gIndex].overlays[oIndex].y = newY;
    graphics = [...graphics];
  }

  function endDrag() {
    activeOverlay = null;
    window.removeEventListener("pointermove", onDrag);
    window.removeEventListener("pointerup", endDrag);
  }

  function moveGraphicUp(index) {
    if (index > 0) {
      const temp = graphics[index];
      graphics[index] = graphics[index - 1];
      graphics[index - 1] = temp;
      graphics = [...graphics];
    }
  }

  function moveGraphicDown(index) {
    if (index < graphics.length - 1) {
      const temp = graphics[index];
      graphics[index] = graphics[index + 1];
      graphics[index + 1] = temp;
      graphics = [...graphics];
    }
  }
</script>

<div class="staff-graphics-editor space-y-6">
  <input
    type="hidden"
    name={field.name}
    value={JSON.stringify(graphics.map(({ _height, ...rest }) => rest))}
  />

  {#if uploadError}
    <div class="mb-2 text-sm text-red-500">{uploadError}</div>
  {/if}

  {#each graphics as graphic, gIndex}
    <div
      class="graphic-block mt-4 rounded-lg border border-border/50 bg-muted/20 p-4"
    >
      <div
        class="mb-4 flex items-center justify-between border-b border-border/50 pb-2"
      >
        <h3 class="text-sm font-semibold text-foreground/80">
          Gambar {gIndex + 1}
        </h3>
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="text-muted-foreground transition-colors hover:text-primary disabled:opacity-30 disabled:hover:text-muted-foreground"
            onclick={() => moveGraphicUp(gIndex)}
            disabled={gIndex === 0}
            title="Geser gambar ke atas (Move Up)"
          >
            <i class="fas fa-arrow-up"></i>
          </button>
          <button
            type="button"
            class="text-muted-foreground transition-colors hover:text-primary disabled:opacity-30 disabled:hover:text-muted-foreground"
            onclick={() => moveGraphicDown(gIndex)}
            disabled={gIndex === graphics.length - 1}
            title="Geser gambar ke bawah (Move Down)"
          >
            <i class="fas fa-arrow-down"></i>
          </button>
          <div class="mx-1 h-4 w-[1px] bg-border"></div>
          <button
            type="button"
            class="text-muted-foreground transition-colors hover:text-destructive"
            onclick={() => removeGraphic(gIndex)}
            title="Hapus gambar (Remove)"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="flex gap-6">
        <div class="w-1/3 shrink-0">
          {#if graphic.image}
            <div
              bind:clientHeight={graphic._height}
              class="group image-container relative overflow-hidden rounded-md border border-border/50 bg-black/5"
            >
              <div
                class="relative h-full w-full transition-transform duration-75"
                style="transform: scale({(graphic.scale ?? 100) /
                  100}) translate({graphic.xOffset ?? 0}%, {graphic.yOffset ??
                  0}%);"
              >
                <img
                  src={graphic.image}
                  alt="Staff Graphic"
                  class="h-auto w-full object-cover"
                  draggable="false"
                />
                <label
                  class="absolute inset-0 z-10 flex cursor-pointer items-center justify-center bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100"
                >
                  <i class="fas fa-upload mr-2"></i> Ganti Gambar
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    onchange={(e) => handleFileChange(e, gIndex)}
                  />
                </label>

                {#each graphic.overlays as overlay, oIndex}
                  {@const parsedName = (() => {
                    const fullName = overlay.name || "Nama";
                    const match = fullName.match(/(.*?)\s+(CE\s*\d+)$/i);
                    if (match)
                      return {
                        name: match[1],
                        batch: match[2].toUpperCase().replace(/\s+/, ""),
                      };
                    return { name: fullName, batch: null };
                  })()}
                  <div
                    class="group/overlay absolute z-20 -translate-x-1/2 -translate-y-1/2 transform cursor-move select-none hover:z-30"
                    style="left: {overlay.x !== undefined
                      ? overlay.x
                      : 50}%; top: {overlay.y !== undefined ? overlay.y : 50}%;"
                    role="button"
                    tabindex="0"
                    aria-label="Seret overlay {overlay.name ||
                      overlay.role ||
                      ''}"
                    onpointerdown={(e) => startDrag(e, gIndex, oIndex)}
                  >
                    <div
                      class="pointer-events-none flex w-max max-w-[340px] origin-center flex-col justify-center border border-white/10 bg-gradient-to-br from-[#111111]/95 to-[#1a1a1a]/85 px-5 py-4 shadow-[0_8px_32px_rgba(0,0,0,0.4)] backdrop-blur-sm transition-all group-hover/overlay:shadow-[0_8px_32px_rgba(255,165,0,0.15)]"
                      style="transform: scale({Math.max(
                        0.2,
                        (graphic._height || 200) / 600,
                      )});"
                    >
                      <p
                        class="text-left font-['The_Seasons',serif] text-[20px] leading-tight font-normal tracking-wide text-balance text-white/95 drop-shadow-sm"
                      >
                        {overlay.role || "Jabatan"}
                      </p>
                      <h4
                        class="mt-1 flex items-baseline gap-3 text-left font-['The_Seasons',serif] text-[28px] font-normal tracking-wide text-white drop-shadow-md"
                      >
                        {parsedName.name}
                        {#if parsedName.batch}
                          <span
                            class="font-sans text-[20px] font-bold tracking-wider text-[#FFB52E] [text-shadow:0_0_10px_rgba(255,165,0,1),0_0_20px_rgba(255,165,0,0.8),0_0_30px_rgba(255,165,0,0.6)]"
                            >{parsedName.batch}</span
                          >
                        {/if}
                      </h4>
                    </div>
                  </div>
                {/each}
              </div>
            </div>

            <div
              class="mt-4 rounded-lg border border-border/50 bg-muted/30 p-3"
            >
              <h4
                class="mb-3 text-[11px] font-semibold tracking-wider text-foreground/70 uppercase"
              >
                <i class="fas fa-sliders-h mr-1.5"></i> Koreksi Jahitan Panorama
              </h4>
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label
                    for="graphic-scale-{gIndex}"
                    class="mb-1 block text-[10px] text-muted-foreground"
                    >Scale (%)</label
                  >
                  <input
                    id="graphic-scale-{gIndex}"
                    type="number"
                    step="0.1"
                    value={graphic.scale ?? 100}
                    oninput={(e) => {
                      graphics[gIndex].scale =
                        parseFloat(e.target.value) || 100;
                      graphics = [...graphics];
                    }}
                    class="w-full rounded border border-border bg-background px-2 py-1 text-xs outline-none focus:ring-1 focus:ring-primary"
                  />
                </div>
                <div>
                  <label
                    for="graphic-x-{gIndex}"
                    class="mb-1 block text-[10px] text-muted-foreground"
                    >Geser X (%)</label
                  >
                  <input
                    id="graphic-x-{gIndex}"
                    type="number"
                    step="0.1"
                    value={graphic.xOffset ?? 0}
                    oninput={(e) => {
                      graphics[gIndex].xOffset =
                        parseFloat(e.target.value) || 0;
                      graphics = [...graphics];
                    }}
                    class="w-full rounded border border-border bg-background px-2 py-1 text-xs outline-none focus:ring-1 focus:ring-primary"
                  />
                </div>
                <div>
                  <label
                    for="graphic-y-{gIndex}"
                    class="mb-1 block text-[10px] text-muted-foreground"
                    >Geser Y (%)</label
                  >
                  <input
                    id="graphic-y-{gIndex}"
                    type="number"
                    step="0.1"
                    value={graphic.yOffset ?? 0}
                    oninput={(e) => {
                      graphics[gIndex].yOffset =
                        parseFloat(e.target.value) || 0;
                      graphics = [...graphics];
                    }}
                    class="w-full rounded border border-border bg-background px-2 py-1 text-xs outline-none focus:ring-1 focus:ring-primary"
                  />
                </div>
              </div>
            </div>
          {:else}
            <div
              class="flex h-32 w-full items-center justify-center rounded-md border border-dashed border-border bg-muted/50 text-muted-foreground"
            >
              <label class="flex cursor-pointer flex-col items-center">
                <i class="fas fa-upload mb-2"></i> Upload Gambar
                <input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  onchange={(e) => handleFileChange(e, gIndex)}
                />
              </label>
            </div>
          {/if}
        </div>

        <div class="w-2/3 space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-foreground">Data Overlays</h4>
            <button
              type="button"
              class="text-xs text-primary hover:underline"
              onclick={() => addOverlay(gIndex)}
            >
              <i class="fas fa-plus"></i> Tambah Orang
            </button>
          </div>

          {#if graphic.overlays.length === 0}
            <div
              class="rounded border border-dashed bg-background/50 py-2 text-center text-xs text-muted-foreground"
            >
              Belum ada data orang. Klik tambah.
            </div>
          {:else}
            <div class="space-y-3">
              {#each graphic.overlays as overlay, oIndex}
                <div class="relative flex items-start gap-2">
                  <div class="flex-1 space-y-2">
                    <input
                      type="text"
                      class="entity-control w-full text-sm"
                      placeholder="Nama (ex: Panji)"
                      bind:value={overlay.name}
                    />
                    <input
                      type="text"
                      class="entity-control w-full text-sm"
                      placeholder="Jabatan (ex: Ketua Himpunan)"
                      bind:value={overlay.role}
                    />

                    <div class="mt-2 flex items-center gap-3">
                      {#if overlay.picture}
                        <img
                          src={overlay.picture}
                          class="h-10 w-10 rounded-md border border-border object-cover"
                          alt="Avatar"
                        />
                        <button
                          type="button"
                          class="text-xs text-destructive hover:underline"
                          onclick={() => {
                            graphics[gIndex].overlays[oIndex].picture = null;
                            graphics = [...graphics];
                          }}>Hapus</button
                        >
                      {/if}
                      <label
                        class="flex cursor-pointer items-center text-xs text-primary hover:underline"
                      >
                        <i class="fas fa-upload mr-1"></i>
                        {overlay.picture ? "Ganti" : "Upload"} Gambar Staff
                        <input
                          type="file"
                          accept="image/*"
                          class="hidden"
                          onchange={(e) =>
                            handleOverlayPictureChange(e, gIndex, oIndex)}
                        />
                      </label>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="mt-2 shrink-0 text-muted-foreground hover:text-destructive"
                    aria-label="Hapus overlay"
                    onclick={() => removeOverlay(gIndex, oIndex)}
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              {/each}
            </div>
          {/if}
        </div>
      </div>
    </div>
  {/each}

  <div class="flex justify-center">
    <button
      type="button"
      class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
      onclick={addGraphic}
      disabled={isUploading}
    >
      {#if isUploading}
        <i class="fas fa-circle-notch fa-spin mr-2"></i> Mengunggah...
      {:else}
        <i class="fas fa-plus mr-2"></i> Tambah Gambar Staff
      {/if}
    </button>
    <input
      type="file"
      id="upload-graphic-new"
      accept="image/*"
      class="hidden"
      onchange={(e) => handleFileChange(e, null)}
    />
  </div>
</div>
