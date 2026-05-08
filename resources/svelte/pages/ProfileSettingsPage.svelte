<script>
  import { onDestroy } from "svelte";
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Edit Profil",
    description = "",
    user = {},
    profileForm = {
      action: "#",
      csrfToken: "",
      spoofMethod: "PUT",
      values: {},
      errors: {},
    },
    passwordForm = {
      action: "#",
      csrfToken: "",
      spoofMethod: "PUT",
      errors: {},
      status: "",
    },
    removeAvatarAction = null,
    backHref = "#",
  } = $props();

  let previewUrl = $state("");
  let avatarInput = $state(null);
  let removeAvatarForm = $state(null);
  let previewObjectUrl = $state(null);
  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  $effect(() => {
    if (!previewObjectUrl) {
      previewUrl = profileForm.values?.avatarUrl || user.avatarUrl || "";
    }
  });

  const confirmRemoveAvatar = async () => {
    if (!removeAvatarAction || !removeAvatarForm) {
      return;
    }

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: "Hapus foto profil?",
        text: "Avatar akan kembali ke identitas default pengguna.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal",
      });

      if (!result.isConfirmed) {
        return;
      }
    } else if (!window.confirm("Hapus foto profil?")) {
      return;
    }

    removeAvatarForm.requestSubmit();
  };

  const handleAvatarChange = (event) => {
    const file = event.currentTarget.files?.[0];
    if (!file) {
      return;
    }

    if (previewObjectUrl) {
      URL.revokeObjectURL(previewObjectUrl);
    }

    previewObjectUrl = URL.createObjectURL(file);
    previewUrl = previewObjectUrl;
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(
      event.currentTarget.alt || user.name || "User",
    );

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    previewUrl = nextSrc;
  };

  const formatJoinedDate = (value) => {
    if (!value) {
      return "";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleDateString("id-ID", {
      timeZone: "Asia/Jakarta",
      day: "2-digit",
      month: "short",
      year: "numeric",
    });
  };

  onDestroy(() => {
    if (previewObjectUrl) {
      URL.revokeObjectURL(previewObjectUrl);
    }
  });
</script>

<section class="profile-hero animate-fadeIn">
  <div class="profile-hero-main">
    <img
      src={previewUrl || fallbackAvatar(user.name)}
      alt={user.name}
      class="profile-avatar"
      onerror={handleImageError}
    />

    <div class="profile-copy">
      <StatusBadge label={user.roleName || "Akun"} tone="primary" />
      <h3>{user.name}</h3>
      <p>{user.email}</p>
      <div class="profile-fact-row">
        {#if user.department}
          <span><i class="fas fa-building"></i>{user.department}</span>
        {/if}
        {#if user.joinedAt}
          <span
            ><i class="fas fa-calendar-days"></i>Bergabung {formatJoinedDate(
              user.joinedAt,
            )}</span
          >
        {/if}
      </div>
    </div>
  </div>

  <div class="profile-hero-actions">
    <Button href={backHref} variant="secondary">
      <i class="fas fa-arrow-left"></i>
      <span>Kembali</span>
    </Button>
  </div>
</section>

{#if passwordForm.status === "password-updated"}
  <section class="profile-banner profile-banner-success animate-fadeIn">
    <i class="fas fa-circle-check"></i>
    <div>
      <strong>Password diperbarui</strong>
      <p>Perubahan sandi sudah disimpan dan sesi lain telah diamankan.</p>
    </div>
  </section>
{/if}

<div class="profile-grid">
  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-user-pen" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form
        action={profileForm.action}
        method="POST"
        enctype="multipart/form-data"
        class="profile-form"
      >
        <input type="hidden" name="_token" value={profileForm.csrfToken} />
        {#if profileForm.spoofMethod}
          <input type="hidden" name="_method" value={profileForm.spoofMethod} />
        {/if}

        <section class="profile-upload-shell">
          <div class="profile-upload-preview">
            <img
              src={previewUrl || fallbackAvatar(user.name)}
              alt={user.name}
              onerror={handleImageError}
            />
          </div>

          <div class="profile-upload-copy">
            <strong>Foto profil</strong>
            <p>
              Gunakan gambar wajah yang jelas. Format JPG, PNG, GIF, atau WebP
              hingga 2MB.
            </p>

            <input
              bind:this={avatarInput}
              type="file"
              name="avatar"
              accept="image/*"
              onchange={handleAvatarChange}
              class="sr-only"
            />

            <div class="profile-upload-actions">
              <Button type="button" onclick={() => avatarInput?.click()}>
                <i class="fas fa-upload"></i>
                <span>Ganti Foto</span>
              </Button>

              {#if removeAvatarAction}
                <Button
                  type="button"
                  variant="destructive"
                  onclick={confirmRemoveAvatar}
                >
                  <i class="fas fa-trash"></i>
                  <span>Hapus Foto</span>
                </Button>
              {/if}
            </div>

            {#if profileForm.errors.avatar}
              <div class="profile-error" role="alert">
                {profileForm.errors.avatar}
              </div>
            {/if}
          </div>
        </section>

        <div class="profile-field">
          <Label for="profile-name">Nama Lengkap</Label>
          <Input
            id="profile-name"
            type="text"
            name="name"
            class="profile-input"
            aria-invalid={Boolean(profileForm.errors.name)}
            value={profileForm.values?.name || ""}
            required
          />
          {#if profileForm.errors.name}
            <div class="profile-error" role="alert">
              {profileForm.errors.name}
            </div>
          {/if}
        </div>

        <div class="profile-actions">
          <Button type="submit">
            <i class="fas fa-save"></i>
            <span>Simpan Perubahan</span>
          </Button>
        </div>
      </form>
    </Card.Content>
  </Card.Root>

  <section class="profile-side">
    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Informasi Akun"
          icon="fas fa-id-card"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="profile-facts pt-5">
        <div>
          <span>Email</span>
          <strong>{user.email}</strong>
        </div>
        <div>
          <span>Role</span>
          <strong>{user.roleName}</strong>
        </div>
        {#if user.department}
          <div>
            <span>Departemen</span>
            <strong>{user.department}</strong>
          </div>
        {/if}
        {#if user.joinedAt}
          <div>
            <span>Bergabung</span>
            <strong>{formatJoinedDate(user.joinedAt)}</strong>
          </div>
        {/if}
      </Card.Content>
    </Card.Root>

    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Ubah Password"
          icon="fas fa-key"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="pt-5">
        <form
          action={passwordForm.action}
          method="POST"
          class="profile-password-form"
        >
          <input type="hidden" name="_token" value={passwordForm.csrfToken} />
          {#if passwordForm.spoofMethod}
            <input
              type="hidden"
              name="_method"
              value={passwordForm.spoofMethod}
            />
          {/if}

          <div class="profile-field">
            <Label for="profile-current-password">Password Saat Ini</Label>
            <Input
              id="profile-current-password"
              type="password"
              name="current_password"
              class="profile-input"
              aria-invalid={Boolean(passwordForm.errors.current_password)}
              autocomplete="current-password"
              required
            />
            {#if passwordForm.errors.current_password}
              <div class="profile-error" role="alert">
                {passwordForm.errors.current_password}
              </div>
            {/if}
          </div>

          <div class="profile-field">
            <Label for="profile-password">Password Baru</Label>
            <Input
              id="profile-password"
              type="password"
              name="password"
              class="profile-input"
              aria-invalid={Boolean(passwordForm.errors.password)}
              autocomplete="new-password"
              required
            />
            <small class="profile-password-help"
              >Minimal 8 karakter dan harus berbeda dari password lama.</small
            >
            {#if passwordForm.errors.password}
              <div class="profile-error" role="alert">
                {passwordForm.errors.password}
              </div>
            {/if}
          </div>

          <div class="profile-field">
            <Label for="profile-password-confirmation"
              >Konfirmasi Password Baru</Label
            >
            <Input
              id="profile-password-confirmation"
              type="password"
              name="password_confirmation"
              class="profile-input"
              aria-invalid={Boolean(passwordForm.errors.password_confirmation)}
              autocomplete="new-password"
              required
            />
            {#if passwordForm.errors.password_confirmation}
              <div class="profile-error" role="alert">
                {passwordForm.errors.password_confirmation}
              </div>
            {/if}
          </div>

          <div class="profile-actions">
            <Button type="submit">
              <i class="fas fa-shield-halved"></i>
              <span>Simpan Password</span>
            </Button>
          </div>
        </form>
      </Card.Content>
    </Card.Root>
  </section>
</div>

{#if removeAvatarAction}
  <form
    bind:this={removeAvatarForm}
    action={removeAvatarAction.action}
    method="POST"
    hidden
  >
    <input type="hidden" name="_token" value={removeAvatarAction.csrfToken} />
    <input type="hidden" name="_method" value="DELETE" />
  </form>
{/if}

<style>
  .profile-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1.3rem 1.4rem;
    border-radius: 0.625rem;
    background: var(--card);
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .profile-hero-main {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .profile-avatar {
    width: 5rem;
    height: 5rem;
    border-radius: 0.75rem;
    object-fit: cover;
    box-shadow: none;
    border: 1px solid var(--line-soft);
  }

  .profile-copy h3 {
    margin: 0.55rem 0 0;
    font-size: 1.5rem;
    font-weight: 600;
  }

  .profile-copy p {
    margin: 0.25rem 0 0;
    color: var(--text-soft);
  }

  .profile-fact-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    margin-top: 0.75rem;
  }

  .profile-fact-row span {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.7rem;
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-soft);
    font-size: 0.8rem;
    font-weight: 600;
  }

  .profile-banner {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.85rem;
    margin-bottom: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .profile-banner-success {
    background: color-mix(in srgb, var(--signal-success) 14%, white);
    color: color-mix(in srgb, var(--signal-success) 84%, black);
  }

  .profile-banner p {
    margin: 0.2rem 0 0;
    color: inherit;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 1rem;
  }

  .profile-side {
    display: grid;
    gap: 1rem;
  }

  .profile-form,
  .profile-password-form {
    display: grid;
    gap: 1rem;
  }

  .profile-upload-shell {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .profile-upload-preview img {
    width: 7rem;
    height: 7rem;
    border-radius: 0.75rem;
    object-fit: cover;
    box-shadow: none;
  }

  .profile-upload-copy strong {
    font-weight: 600;
  }

  .profile-upload-copy p {
    margin: 0.35rem 0 0;
    color: var(--text-soft);
  }

  .profile-upload-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
    margin-top: 1rem;
  }

  .profile-field {
    display: grid;
    gap: 0.45rem;
  }

  :global(.profile-input) {
    background: var(--background);
  }

  .profile-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  :global(.profile-facts) {
    display: grid;
    gap: 0.9rem;
  }

  :global(.profile-facts div) {
    padding: 0.95rem 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  :global(.profile-facts span) {
    display: block;
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  :global(.profile-facts strong) {
    display: block;
    margin-top: 0.3rem;
    font-size: 1rem;
  }

  .profile-password-help {
    display: block;
    margin-top: -0.1rem;
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .profile-actions {
    display: flex;
    justify-content: flex-start;
  }

  @media (max-width: 980px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .profile-hero,
    .profile-upload-shell,
    .profile-hero-main {
      flex-direction: column;
      grid-template-columns: 1fr;
      align-items: flex-start;
    }

    .profile-upload-preview img {
      width: 100%;
      max-width: 12rem;
      height: auto;
      aspect-ratio: 1;
    }

    .profile-upload-actions,
    .profile-actions,
    .profile-hero-actions {
      width: 100%;
    }

    .profile-upload-actions :global([data-slot="button"]),
    .profile-actions :global([data-slot="button"]),
    .profile-hero-actions :global([data-slot="button"]) {
      width: 100%;
    }
  }
</style>
