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
      const response = await window.axios.post("/departments/upload-graphic", formData, {
        headers: {
          "Content-Type": "multipart/form-data"
        }
      });

      const data = response.data;
      
      if (index !== null) {
        graphics[index].image = data.url;
      } else {
        graphics = [
          ...graphics,
          { id: Date.now().toString(), image: data.url, overlays: [] }
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
      const response = await window.axios.post("/departments/upload-graphic", formData, {
        headers: {
          "Content-Type": "multipart/form-data"
        }
      });

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
    graphics[graphicIndex].overlays = graphics[graphicIndex].overlays.filter((_, i) => i !== overlayIndex);
    graphics = [...graphics];
  }

  let activeOverlay = null;

  function startDrag(e, gIndex, oIndex) {
    const overlay = graphics[gIndex].overlays[oIndex];
    if (overlay.x === undefined) overlay.x = 50;
    if (overlay.y === undefined) overlay.y = 50;

    const imgContainer = e.currentTarget.closest('.image-container');
    const rect = imgContainer.getBoundingClientRect();

    activeOverlay = {
      gIndex,
      oIndex,
      startX: e.clientX,
      startY: e.clientY,
      origX: overlay.x,
      origY: overlay.y,
      rect
    };
    
    window.addEventListener('pointermove', onDrag);
    window.addEventListener('pointerup', endDrag);
    
    // Prevent default drag behaviors
    e.preventDefault();
  }

  function onDrag(e) {
    if (!activeOverlay) return;
    const { gIndex, oIndex, startX, startY, origX, origY, rect } = activeOverlay;

    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    const dxPct = (dx / rect.width) * 100;
    const dyPct = (dy / rect.height) * 100;

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
    window.removeEventListener('pointermove', onDrag);
    window.removeEventListener('pointerup', endDrag);
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
  <input type="hidden" name={field.name} value={JSON.stringify(graphics)} />

  {#if uploadError}
    <div class="text-sm text-red-500 mb-2">{uploadError}</div>
  {/if}

  {#each graphics as graphic, gIndex}
    <div class="graphic-block border border-border/50 rounded-lg p-4 bg-muted/20 mt-4">
      <div class="flex justify-between items-center mb-4 pb-2 border-b border-border/50">
        <h3 class="text-sm font-semibold text-foreground/80">Gambar {gIndex + 1}</h3>
        <div class="flex items-center gap-3">
          <button 
            type="button" 
            class="text-muted-foreground hover:text-primary disabled:opacity-30 disabled:hover:text-muted-foreground transition-colors"
            onclick={() => moveGraphicUp(gIndex)}
            disabled={gIndex === 0}
            title="Geser gambar ke atas (Move Up)"
          >
            <i class="fas fa-arrow-up"></i>
          </button>
          <button 
            type="button" 
            class="text-muted-foreground hover:text-primary disabled:opacity-30 disabled:hover:text-muted-foreground transition-colors"
            onclick={() => moveGraphicDown(gIndex)}
            disabled={gIndex === graphics.length - 1}
            title="Geser gambar ke bawah (Move Down)"
          >
            <i class="fas fa-arrow-down"></i>
          </button>
          <div class="w-[1px] h-4 bg-border mx-1"></div>
          <button 
            type="button" 
            class="text-muted-foreground hover:text-destructive transition-colors"
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
            <div class="relative group rounded-md overflow-hidden border border-border/50 image-container bg-black/5">
              <div class="relative w-full h-full transition-transform duration-75" style="transform: scale({(graphic.scale ?? 100) / 100}) translate({graphic.xOffset ?? 0}%, {graphic.yOffset ?? 0}%);">
                <img src={graphic.image} alt="Staff Graphic" class="w-full h-auto object-cover" draggable="false" />
                <label class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white cursor-pointer transition-opacity z-10">
                  <i class="fas fa-upload mr-2"></i> Ganti Gambar
                  <input type="file" accept="image/*" class="hidden" onchange={(e) => handleFileChange(e, gIndex)} />
                </label>
  
                {#each graphic.overlays as overlay, oIndex}
                  {@const parsedName = (() => {
                    const fullName = overlay.name || 'Nama';
                    const match = fullName.match(/(.*?)\s+(CE\s*\d+)$/i);
                    if (match) return { name: match[1], batch: match[2].toUpperCase().replace(/\s+/, '') };
                    return { name: fullName, batch: null };
                  })()}
                  <div 
                    class="absolute transform -translate-x-1/2 -translate-y-1/2 cursor-move select-none z-20 group/overlay hover:z-30"
                    style="left: {overlay.x !== undefined ? overlay.x : 50}%; top: {overlay.y !== undefined ? overlay.y : 50}%;"
                    onpointerdown={(e) => startDrag(e, gIndex, oIndex)}
                  >
                    <div class="scale-[0.3] origin-center bg-gradient-to-br from-[#111111]/95 to-[#1a1a1a]/85 backdrop-blur-sm px-5 py-4 flex flex-col justify-center pointer-events-none shadow-[0_8px_32px_rgba(0,0,0,0.4)] border border-white/10 transition-all group-hover/overlay:shadow-[0_8px_32px_rgba(255,165,0,0.15)] w-max max-w-[340px]">
                       <p class="text-[20px] font-['The_Seasons',serif] font-normal tracking-wide leading-tight text-white/95 drop-shadow-sm text-left text-balance">
                         {overlay.role || 'Jabatan'}
                       </p>
                       <h4 class="font-['The_Seasons',serif] text-[28px] font-normal tracking-wide text-white drop-shadow-md text-left flex items-baseline gap-3 mt-1">
                         {parsedName.name}
                         {#if parsedName.batch}
                           <span class="text-[#FFB52E] font-sans font-bold tracking-wider text-[20px] [text-shadow:0_0_10px_rgba(255,165,0,1),0_0_20px_rgba(255,165,0,0.8),0_0_30px_rgba(255,165,0,0.6)]">{parsedName.batch}</span>
                         {/if}
                       </h4>
                    </div>
                  </div>
                {/each}
              </div>
            </div>

            <div class="mt-4 p-3 bg-muted/30 rounded-lg border border-border/50">
              <h4 class="text-[11px] font-semibold text-foreground/70 uppercase tracking-wider mb-3"><i class="fas fa-sliders-h mr-1.5"></i> Koreksi Jahitan Panorama</h4>
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label class="text-[10px] text-muted-foreground block mb-1">Scale (%)</label>
                  <input type="number" step="0.1" value={graphic.scale ?? 100} oninput={(e) => { graphics[gIndex].scale = parseFloat(e.target.value) || 100; graphics = [...graphics]; }} class="w-full bg-background border border-border rounded text-xs px-2 py-1 focus:ring-1 focus:ring-primary outline-none" />
                </div>
                <div>
                  <label class="text-[10px] text-muted-foreground block mb-1">Geser X (%)</label>
                  <input type="number" step="0.1" value={graphic.xOffset ?? 0} oninput={(e) => { graphics[gIndex].xOffset = parseFloat(e.target.value) || 0; graphics = [...graphics]; }} class="w-full bg-background border border-border rounded text-xs px-2 py-1 focus:ring-1 focus:ring-primary outline-none" />
                </div>
                <div>
                  <label class="text-[10px] text-muted-foreground block mb-1">Geser Y (%)</label>
                  <input type="number" step="0.1" value={graphic.yOffset ?? 0} oninput={(e) => { graphics[gIndex].yOffset = parseFloat(e.target.value) || 0; graphics = [...graphics]; }} class="w-full bg-background border border-border rounded text-xs px-2 py-1 focus:ring-1 focus:ring-primary outline-none" />
                </div>
              </div>
            </div>
          {:else}
            <div class="w-full h-32 bg-muted/50 rounded-md border border-dashed border-border flex items-center justify-center text-muted-foreground">
              <label class="cursor-pointer flex flex-col items-center">
                <i class="fas fa-upload mb-2"></i> Upload Gambar
                <input type="file" accept="image/*" class="hidden" onchange={(e) => handleFileChange(e, gIndex)} />
              </label>
            </div>
          {/if}
        </div>

        <div class="w-2/3 space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-foreground">Data Overlays</h4>
            <button type="button" class="text-xs text-primary hover:underline" onclick={() => addOverlay(gIndex)}>
              <i class="fas fa-plus"></i> Tambah Orang
            </button>
          </div>

          {#if graphic.overlays.length === 0}
            <div class="text-xs text-muted-foreground py-2 text-center border border-dashed rounded bg-background/50">
              Belum ada data orang. Klik tambah.
            </div>
          {:else}
            <div class="space-y-3">
              {#each graphic.overlays as overlay, oIndex}
                <div class="flex gap-2 items-start relative">
                  <div class="flex-1 space-y-2">
                    <input type="text" class="entity-control w-full text-sm" placeholder="Nama (ex: Panji)" bind:value={overlay.name} />
                    <input type="text" class="entity-control w-full text-sm" placeholder="Jabatan (ex: Ketua Himpunan)" bind:value={overlay.role} />
                    
                    <div class="flex items-center gap-3 mt-2">
                      {#if overlay.picture}
                         <img src={overlay.picture} class="w-10 h-10 object-cover rounded-md border border-border" alt="Avatar" />
                         <button type="button" class="text-xs text-destructive hover:underline" onclick={() => { graphics[gIndex].overlays[oIndex].picture = null; graphics = [...graphics]; }}>Hapus</button>
                      {/if}
                      <label class="text-xs text-primary cursor-pointer hover:underline flex items-center">
                         <i class="fas fa-upload mr-1"></i> {overlay.picture ? 'Ganti' : 'Upload'} Gambar Staff
                         <input type="file" accept="image/*" class="hidden" onchange={(e) => handleOverlayPictureChange(e, gIndex, oIndex)} />
                      </label>
                    </div>
                  </div>
                  <button type="button" class="text-muted-foreground hover:text-destructive shrink-0 mt-2" onclick={() => removeOverlay(gIndex, oIndex)}>
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
      class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
      onclick={addGraphic}
      disabled={isUploading}
    >
      {#if isUploading}
        <i class="fas fa-circle-notch fa-spin mr-2"></i> Mengunggah...
      {:else}
        <i class="fas fa-plus mr-2"></i> Tambah Gambar Staff
      {/if}
    </button>
    <input type="file" id="upload-graphic-new" accept="image/*" class="hidden" onchange={(e) => handleFileChange(e, null)} />
  </div>
</div>
