<script>
  import { onDestroy, onMount } from "svelte";
  import { subscribeToLiveUpdates } from "$lib/live-updates.js";
  import { Button } from "$lib/components/ui/button/index.js";
  import {
    shouldSkipFormConfirmation,
    submitConfirmedForm,
  } from "$lib/confirmable-form.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Textarea } from "$lib/components/ui/textarea/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Pengumuman",
    description = "",
    csrfToken = "",
    refreshUrl = "",
    realtimeSnapshot = "",
    createForm = {
      action: "#",
      avatar: "",
      content: "",
      hasPoll: false,
      pollQuestion: "",
      pollDuration: "24",
      pollOptions: ["", ""],
      errors: {},
    },
    posts = [],
    pagination = null,
  } = $props();

  const reactionCatalog = [
    { type: "like", emoji: "👍" },
    { type: "love", emoji: "❤️" },
    { type: "haha", emoji: "😂" },
    { type: "wow", emoji: "😮" },
    { type: "sad", emoji: "😢" },
    { type: "angry", emoji: "😡" },
  ];

  let pollOpen = $state(false);
  let pollOptions = $state(["", ""]);
  let postsState = $state([]);
  let paginationState = $state(null);
  let initialized = $state(false);
  let liveUpdatesCleanup = $state(null);

  const decoratePosts = (nextPosts) => {
    const postStateMap = new Map(
      postsState.map((post) => [
        post.id,
        {
          reactionsOpen: Boolean(post.reactionsOpen),
          commentsOpen: Boolean(post.commentsOpen),
        },
      ]),
    );

    return nextPosts.map((post) => ({
      ...post,
      reactionsOpen: postStateMap.get(post.id)?.reactionsOpen ?? false,
      commentsOpen: postStateMap.get(post.id)?.commentsOpen ?? false,
    }));
  };

  $effect(() => {
    if (!paginationState && pagination) {
      paginationState = pagination;
    }
  });

  $effect(() => {
    if (!initialized) {
      pollOpen = Boolean(createForm.hasPoll);
      pollOptions = createForm.pollOptions?.length
        ? [...createForm.pollOptions]
        : ["", ""];
      postsState = decoratePosts(posts);
      initialized = true;
    }
  });

  const notify = (notifyTitle, text, icon = "success") => {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: "top-end",
        icon,
        title: notifyTitle,
        text,
        showConfirmButton: false,
        timer: 1800,
      });
      return;
    }

    if (icon === "error") {
      window.alert(text);
    }
  };

  const addPollOption = () => {
    if (pollOptions.length >= 6) {
      notify("Batas tercapai", "Maksimal 6 opsi polling.", "error");
      return;
    }

    pollOptions = [...pollOptions, ""];
  };

  const removePollOption = (index) => {
    if (pollOptions.length <= 2) {
      return;
    }

    pollOptions = pollOptions.filter((_, optionIndex) => optionIndex !== index);
  };

  const updatePollOption = (index, value) => {
    pollOptions = pollOptions.map((option, optionIndex) =>
      optionIndex === index ? value : option,
    );
  };

  const toggleReactions = (postId) => {
    postsState = postsState.map((post) =>
      post.id === postId
        ? { ...post, reactionsOpen: !post.reactionsOpen }
        : post,
    );
  };

  const toggleComments = (postId) => {
    postsState = postsState.map((post) =>
      post.id === postId ? { ...post, commentsOpen: !post.commentsOpen } : post,
    );
  };

  const confirmSubmission = async (event, payload) => {
    const form = event.currentTarget;

    if (shouldSkipFormConfirmation(form)) {
      return;
    }

    if (!payload?.confirm) {
      return;
    }

    event.preventDefault();

    const text =
      payload.confirmText || `Lanjutkan tindakan untuk ${payload.confirm}?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: payload.confirmTitle || "Konfirmasi",
        text,
        icon: payload.confirmIcon || "warning",
        showCancelButton: true,
        confirmButtonText: payload.confirmButtonText || "Lanjutkan",
        cancelButtonText: "Batal",
      });

      if (result.isConfirmed) {
        submitConfirmedForm(form);
      }

      return;
    }

    if (window.confirm(text)) {
      submitConfirmedForm(form);
    }
  };

  const updateReactionSummary = (summary, previousType, nextType) => {
    const counts = new Map(
      (summary || []).map((item) => [item.type, item.count]),
    );

    if (previousType) {
      counts.set(
        previousType,
        Math.max((counts.get(previousType) || 1) - 1, 0),
      );
    }

    if (nextType) {
      counts.set(nextType, (counts.get(nextType) || 0) + 1);
    }

    return reactionCatalog
      .map((reaction) => ({
        type: reaction.type,
        emoji: reaction.emoji,
        count: counts.get(reaction.type) || 0,
      }))
      .filter((item) => item.count > 0);
  };

  const react = async (postId, type) => {
    const post = postsState.find((item) => item.id === postId);
    if (!post) {
      return;
    }

    try {
      const response = await fetch(post.reactUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({ type }),
      });

      const data = await response.json();

      if (!response.ok) {
        notify(
          "Gagal memperbarui reaksi",
          data.error || "Terjadi kesalahan.",
          "error",
        );
        return;
      }

      const previousType = post.userReaction;
      const nextType = data.removed ? null : type;

      postsState = postsState.map((item) =>
        item.id === postId
          ? {
              ...item,
              userReaction: nextType,
              reactionSummary: updateReactionSummary(
                item.reactionSummary,
                previousType,
                nextType,
              ),
            }
          : item,
      );
    } catch (error) {
      notify(
        "Gagal memperbarui reaksi",
        "Periksa koneksi lalu coba lagi.",
        "error",
      );
    }
  };

  const votePoll = async (postId, optionId) => {
    const post = postsState.find((item) => item.id === postId);
    if (!post?.poll || post.poll.hasVoted || !post.poll.isActive) {
      return;
    }

    try {
      const response = await fetch(post.voteUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({ option_id: optionId }),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        notify(
          "Voting gagal",
          data.error || "Tidak bisa mengirim pilihan.",
          "error",
        );
        return;
      }

      postsState = postsState.map((item) =>
        item.id === postId
          ? {
              ...item,
              poll: {
                ...item.poll,
                hasVoted: true,
                userVoteOptionId: optionId,
                totalVotes: data.total_votes,
                options: item.poll.options.map((option) => {
                  const updated = data.options.find(
                    (candidate) => candidate.id === option.id,
                  );
                  return updated
                    ? {
                        ...option,
                        votes: updated.votes,
                        percentage: updated.percentage,
                      }
                    : option;
                }),
              },
            }
          : item,
      );
    } catch (error) {
      notify("Voting gagal", "Periksa koneksi lalu coba lagi.", "error");
    }
  };

  const refreshFeed = async () => {
    if (!refreshUrl) {
      return;
    }

    try {
      const response = await fetch(refreshUrl, {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        cache: "no-store",
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();

      postsState = decoratePosts(Array.isArray(data.posts) ? data.posts : []);
      paginationState = data.pagination || paginationState;
    } catch (error) {
      console.error("Failed to refresh announcement feed", error);
    }
  };

  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || "User");

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  const formatRelativeTime = (value) => {
    if (!value) {
      return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    const diffMinutes = Math.round((date.getTime() - Date.now()) / 60000);

    if (Math.abs(diffMinutes) < 1) {
      return "baru saja";
    }

    const formatter = new Intl.RelativeTimeFormat("id-ID", { numeric: "auto" });

    if (Math.abs(diffMinutes) < 60) {
      return formatter.format(diffMinutes, "minute");
    }

    const diffHours = Math.round(diffMinutes / 60);

    if (Math.abs(diffHours) < 24) {
      return formatter.format(diffHours, "hour");
    }

    const diffDays = Math.round(diffHours / 24);

    if (Math.abs(diffDays) < 30) {
      return formatter.format(diffDays, "day");
    }

    const diffMonths = Math.round(diffDays / 30);

    if (Math.abs(diffMonths) < 12) {
      return formatter.format(diffMonths, "month");
    }

    return formatter.format(Math.round(diffDays / 365), "year");
  };

  const formatPollEnds = (value) => {
    if (!value) {
      return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return null;
    }

    return date.getTime() > Date.now()
      ? `Berakhir ${formatRelativeTime(value)}`
      : "Sudah berakhir";
  };

  const reactionTotal = (summary = []) =>
    summary.reduce((total, item) => total + Number(item.count || 0), 0);

  onMount(() => {
    if (!realtimeSnapshot) {
      return;
    }

    liveUpdatesCleanup = subscribeToLiveUpdates(
      realtimeSnapshot,
      async ({ changed }) => {
        if (!changed.includes("announcements")) {
          return;
        }

        await refreshFeed();
      },
      { interval: 7000 },
    );
  });

  onDestroy(() => {
    liveUpdatesCleanup?.();
  });
</script>

<Card.Root
  class="animate-fadeIn announcement-intro rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="announcement-intro-head border-b border-border/70 pb-4">
    <PageHeader
      {title}
      description="Pengumuman internal dan polling kabinet."
      icon="fas fa-bullhorn"
    />
  </Card.Header>
  <Card.Content class="pt-5">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div
        class="announcement-summary-card announcement-summary-primary rounded-[10px] border px-4 py-4"
      >
        <div class="text-sm text-muted-foreground">Publikasi aktif</div>
        <div class="mt-2 text-2xl font-semibold text-foreground">
          {paginationState?.total || postsState.length}
        </div>
      </div>
      <div
        class="announcement-summary-card announcement-summary-warning rounded-[10px] border px-4 py-4"
      >
        <div class="text-sm text-muted-foreground">Polling terbuka</div>
        <div class="mt-2 text-2xl font-semibold text-foreground">
          {postsState.filter((post) => post.poll?.isActive).length}
        </div>
      </div>
    </div>
  </Card.Content>
</Card.Root>

<Card.Root
  class="announcement-composer animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Content class="pt-5">
    <form action={createForm.action} method="POST">
      <input type="hidden" name="_token" value={csrfToken} />
      <input type="hidden" name="has_poll" value={pollOpen ? "1" : "0"} />

      <div class="announcement-composer-row">
        <img
          src={createForm.avatar || fallbackAvatar("Avatar")}
          alt="Avatar"
          class="avatar-md"
          width="48"
          height="48"
          decoding="async"
          onerror={handleImageError}
        />
        <div class="announcement-composer-main">
          <Textarea
            name="content"
            rows="4"
            class="announcement-textarea"
            aria-invalid={Boolean(createForm.errors?.content)}
            placeholder="Tulis pengumuman..."
            value={createForm.content || ""}
          />
          {#if createForm.errors?.content}
            <div class="announcement-error" role="alert">
              {createForm.errors.content}
            </div>
          {/if}

          {#if pollOpen}
            <div class="announcement-poll-builder">
              <div class="announcement-field">
                <Label for="poll-question">Pertanyaan Polling</Label>
                <Input
                  id="poll-question"
                  type="text"
                  name="poll_question"
                  class="announcement-input"
                  aria-invalid={Boolean(createForm.errors?.poll_question)}
                  value={createForm.pollQuestion || ""}
                  placeholder="Pertanyaan polling..."
                />
                {#if createForm.errors?.poll_question}
                  <div class="announcement-error" role="alert">
                    {createForm.errors.poll_question}
                  </div>
                {/if}
              </div>

              <div class="announcement-poll-options">
                {#each pollOptions as option, index (index)}
                  <div class="announcement-poll-option">
                    <Input
                      type="text"
                      name="poll_options[]"
                      class="announcement-input"
                      aria-invalid={Boolean(
                        createForm.errors?.poll_options ||
                        createForm.errors?.poll_options_items,
                      )}
                      value={option}
                      placeholder={`Opsi ${index + 1}`}
                      oninput={(event) =>
                        updatePollOption(index, event.currentTarget.value)}
                    />
                    <Button
                      type="button"
                      variant="secondary"
                      size="icon-sm"
                      aria-label="Hapus opsi"
                      title="Hapus opsi"
                      onclick={() => removePollOption(index)}
                    >
                      <i class="fas fa-times"></i>
                    </Button>
                  </div>
                {/each}
              </div>

              {#if createForm.errors?.poll_options}
                <div class="announcement-error" role="alert">
                  {createForm.errors.poll_options}
                </div>
              {/if}
              {#if createForm.errors?.poll_options_items}
                <div class="announcement-error" role="alert">
                  {createForm.errors.poll_options_items}
                </div>
              {/if}

              <div class="announcement-poll-footer">
                <Button
                  type="button"
                  variant="secondary"
                  size="sm"
                  onclick={addPollOption}
                >
                  <i class="fas fa-plus"></i>
                  <span>Tambah Opsi</span>
                </Button>

                <select
                  name="poll_duration"
                  class="announcement-select announcement-poll-duration"
                  aria-invalid={Boolean(createForm.errors?.poll_duration)}
                >
                  <option
                    value="24"
                    selected={String(createForm.pollDuration) === "24"}
                    >1 Hari</option
                  >
                  <option
                    value="72"
                    selected={String(createForm.pollDuration) === "72"}
                    >3 Hari</option
                  >
                  <option
                    value="168"
                    selected={String(createForm.pollDuration) === "168"}
                    >7 Hari</option
                  >
                </select>
              </div>

              {#if createForm.errors?.poll_duration}
                <div class="announcement-error" role="alert">
                  {createForm.errors.poll_duration}
                </div>
              {/if}
            </div>
          {/if}

          <div class="announcement-composer-actions">
            <Button
              type="button"
              variant="secondary"
              size="sm"
              onclick={() => (pollOpen = !pollOpen)}
            >
              <i class="fas fa-chart-simple"></i>
              <span>{pollOpen ? "Tutup Poll" : "Tambah Poll"}</span>
            </Button>

            <Button type="submit">
              <i class="fas fa-paper-plane"></i>
              <span>Posting</span>
            </Button>
          </div>
        </div>
      </div>
    </form>
  </Card.Content>
</Card.Root>

{#if !postsState.length}
  <Card.Root
    class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="pt-5">
      <EmptyStatePanel
        title="Belum ada pengumuman"
        text="Belum ada pengumuman."
        icon="fas fa-bullhorn"
        tone="primary"
      />
    </Card.Content>
  </Card.Root>
{:else}
  <div class="announcement-feed">
    {#each postsState as post, index (post.id || index)}
      <Card.Root
        class="animate-fadeIn announcement-post rounded-[10px] border border-border bg-card shadow-none"
      >
        <Card.Content class="pt-5">
          <div class="announcement-post-head">
            <div class="announcement-post-head-main">
              <img
                src={post.author.avatar || fallbackAvatar(post.author.name)}
                alt={post.author.name}
                class="avatar-md"
                width="48"
                height="48"
                loading="lazy"
                decoding="async"
                onerror={handleImageError}
              />
              <div>
                <div class="announcement-post-author">{post.author.name}</div>
                <div class="announcement-post-time">
                  {formatRelativeTime(post.createdAt)}
                </div>
              </div>
            </div>

            <div class="announcement-post-head-side">
              {#if post.poll}
                <StatusBadge label="Polling" tone="secondary" />
              {/if}
              {#if post.canDelete}
                <form
                  method="POST"
                  action={post.deleteAction}
                  class="announcement-delete-form"
                  onsubmit={(event) =>
                    confirmSubmission(event, post.deletePayload)}
                >
                  <input type="hidden" name="_token" value={csrfToken} />
                  <input type="hidden" name="_method" value="DELETE" />
                  <Button
                    type="submit"
                    variant="ghost"
                    size="sm"
                    aria-label="Hapus"
                  >
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                  </Button>
                </form>
              {/if}
            </div>
          </div>

          <div class="announcement-post-content">{post.content}</div>

          {#if post.poll}
            <section class="announcement-poll">
              <div class="announcement-poll-question">
                Polling internal: {post.poll.question}
              </div>

              <div class="announcement-poll-options-view">
                {#each post.poll.options as option, optionIndex (option.id || optionIndex)}
                  <button
                    type="button"
                    class={`announcement-poll-choice ${post.poll.hasVoted ? "announcement-poll-choice-voted" : ""} ${post.poll.userVoteOptionId === option.id ? "announcement-poll-choice-selected" : ""}`.trim()}
                    onclick={() => votePoll(post.id, option.id)}
                    disabled={post.poll.hasVoted || !post.poll.isActive}
                  >
                    {#if post.poll.hasVoted}
                      <div
                        class="announcement-poll-bar"
                        style={`width:${option.percentage}%`}
                      ></div>
                    {/if}
                    <div class="announcement-poll-copy">
                      <span>{option.text}</span>
                      {#if post.poll.hasVoted}
                        <span>{option.percentage}%</span>
                      {/if}
                    </div>
                  </button>
                {/each}
              </div>

              <div class="announcement-poll-meta">
                {post.poll.totalVotes} suara
                {#if formatPollEnds(post.poll.pollEndsAt)}
                  • {formatPollEnds(post.poll.pollEndsAt)}
                {/if}
              </div>
            </section>
          {/if}

          <div class="announcement-supporting-meta">
            <span>{reactionTotal(post.reactionSummary)} tanggapan cepat</span>
            <span>{post.comments.length} catatan diskusi</span>
          </div>

          {#if post.reactionsOpen}
            <div class="announcement-reactions-panel">
              {#each reactionCatalog as reaction, reactionIndex (reaction.type || reactionIndex)}
                <button
                  type="button"
                  class={`announcement-reaction-button ${post.userReaction === reaction.type ? "announcement-reaction-button-active" : ""}`.trim()}
                  onclick={() => react(post.id, reaction.type)}
                >
                  {reaction.emoji}
                </button>
              {/each}
            </div>
          {/if}

          <div class="announcement-post-actions">
            <Button
              type="button"
              variant="ghost"
              onclick={() => toggleReactions(post.id)}
            >
              <i class="far fa-thumbs-up"></i>
              <span>Tanggapan</span>
            </Button>
            <Button
              type="button"
              variant="ghost"
              onclick={() => toggleComments(post.id)}
            >
              <i class="far fa-comment"></i>
              <span>Diskusi ({post.comments.length})</span>
            </Button>
          </div>

          {#if post.commentsOpen}
            <section class="announcement-comments">
              {#each post.comments as comment, commentIndex (comment.createdAt || commentIndex)}
                <div class="announcement-comment">
                  <img
                    src={comment.user.avatar ||
                      fallbackAvatar(comment.user.name)}
                    alt={comment.user.name}
                    class="avatar-sm"
                    width="32"
                    height="32"
                    loading="lazy"
                    decoding="async"
                    onerror={handleImageError}
                  />
                  <div class="announcement-comment-body">
                    <div class="announcement-comment-name">
                      {comment.user.name}
                    </div>
                    <div class="announcement-comment-text">
                      {comment.content}
                    </div>
                    <div class="announcement-comment-time">
                      {formatRelativeTime(comment.createdAt)}
                    </div>
                  </div>
                </div>
              {/each}

              <form
                method="POST"
                action={post.commentAction}
                class="announcement-comment-form"
              >
                <input type="hidden" name="_token" value={csrfToken} />
                <Input
                  type="text"
                  name="content"
                  class="announcement-comment-input"
                  placeholder="Tulis komentar..."
                  required
                />
                <Button
                  type="submit"
                  size="icon-sm"
                  aria-label="Kirim komentar"
                  title="Kirim komentar"
                >
                  <i class="fas fa-paper-plane"></i>
                </Button>
              </form>
            </section>
          {/if}
        </Card.Content>
      </Card.Root>
    {/each}
  </div>
{/if}

{#if paginationState && paginationState.total > 0}
  <Card.Root
    class="announcement-pagination-card animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="announcement-pagination pt-5">
      <p class="announcement-pagination-copy">
        Menampilkan {paginationState.from} - {paginationState.to} dari {paginationState.total}
        pengumuman
      </p>
      <div class="announcement-pagination-actions">
        <StatusBadge
          label={`Halaman ${paginationState.currentPage} / ${paginationState.lastPage}`}
          tone="secondary"
        />
        {#if paginationState.prevUrl}
          <Button href={paginationState.prevUrl} variant="secondary" size="sm">
            <i class="fas fa-arrow-left"></i>
            <span>Sebelumnya</span>
          </Button>
        {/if}
        {#if paginationState.nextUrl}
          <Button href={paginationState.nextUrl} variant="secondary" size="sm">
            <span>Selanjutnya</span>
            <i class="fas fa-arrow-right"></i>
          </Button>
        {/if}
      </div>
    </Card.Content>
  </Card.Root>
{/if}

<style>
  .announcement-composer,
  .announcement-feed,
  .announcement-pagination-card {
    margin-top: 1rem;
  }

  .announcement-summary-card {
    border-color: var(--line-soft);
    background: var(--background);
  }

  .announcement-summary-primary {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 24%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--brand-light) 20%, var(--background));
  }

  .announcement-summary-warning {
    border-color: color-mix(
      in srgb,
      var(--signal-warning) 22%,
      var(--line-soft)
    );
    background: color-mix(
      in srgb,
      var(--signal-warning) 10%,
      var(--background)
    );
  }

  .announcement-summary-info {
    border-color: color-mix(in srgb, var(--signal-info) 20%, var(--line-soft));
    background: color-mix(in srgb, var(--signal-info) 8%, var(--background));
  }

  .announcement-composer-row {
    display: flex;
    gap: 1rem;
  }

  .announcement-composer-main {
    flex: 1;
  }

  :global(.announcement-textarea) {
    min-height: 7rem;
    resize: vertical;
    background: var(--background);
  }

  .announcement-poll-builder {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: color-mix(in srgb, var(--brand-light) 14%, var(--background));
    border: 1px solid
      color-mix(in srgb, var(--brand-primary) 18%, var(--line-soft));
  }

  .announcement-field {
    display: grid;
    gap: 0.45rem;
  }

  :global(.announcement-input) {
    background: var(--background);
  }

  .announcement-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  .announcement-poll-options {
    display: grid;
    gap: 0.75rem;
    margin-top: 1rem;
  }

  .announcement-poll-option {
    display: flex;
    gap: 0.6rem;
  }

  .announcement-poll-footer {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: center;
    margin-top: 1rem;
  }

  .announcement-select {
    width: 100%;
    min-width: 0;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-strong);
    outline: none;
    transition:
      border-color 160ms ease,
      box-shadow 160ms ease;
  }

  .announcement-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .announcement-poll-duration {
    max-width: 12rem;
  }

  .announcement-composer-actions {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: center;
    margin-top: 1rem;
  }

  .announcement-feed {
    display: grid;
    gap: 1rem;
  }

  .announcement-delete-form {
    margin: 0;
  }

  .announcement-post-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .announcement-post-head-main,
  .announcement-post-head-side {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }

  .announcement-post-head-side {
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .announcement-post-author {
    font-weight: 700;
    font-weight: 600;
  }

  .announcement-post-time {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .announcement-post-content {
    margin-top: 1rem;
    white-space: pre-wrap;
    line-height: 1.72;
  }

  .announcement-poll {
    margin-top: 1rem;
  }

  .announcement-poll-question {
    margin-bottom: 0.75rem;
    font-weight: 600;
  }

  .announcement-poll-options-view {
    display: grid;
    gap: 0.55rem;
  }

  .announcement-poll-choice {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
    padding: 0.85rem 1rem;
    text-align: left;
    cursor: pointer;
  }

  .announcement-poll-choice:focus-visible,
  .announcement-reaction-button:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--brand-primary) 48%, transparent);
    outline-offset: 2px;
  }

  .announcement-poll-choice-voted {
    cursor: default;
  }

  .announcement-poll-choice-selected {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 32%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--brand-light) 12%, var(--background));
  }

  .announcement-poll-bar {
    position: absolute;
    inset: 0 auto 0 0;
    background: color-mix(in srgb, var(--brand-primary) 18%, transparent);
  }

  .announcement-poll-copy {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    font-weight: 600;
  }

  .announcement-poll-meta,
  .announcement-supporting-meta {
    margin-top: 0.7rem;
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .announcement-supporting-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
  }

  .announcement-reactions-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.9rem;
  }

  .announcement-reaction-button {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 0.5rem;
    border: 1px solid var(--line-soft);
    background: var(--background);
    cursor: pointer;
    font-size: 1.05rem;
  }

  .announcement-reaction-button-active {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 34%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--brand-light) 20%, var(--background));
    color: color-mix(in srgb, var(--brand-hover) 72%, black);
  }

  .announcement-post-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line-soft);
  }

  .announcement-comments {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line-soft);
  }

  .announcement-comment {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
  }

  .announcement-comment-body {
    flex: 1;
    padding: 0.8rem 0.9rem;
    border-radius: 0.625rem;
    background: var(--background);
  }

  .announcement-comment-name {
    font-weight: 700;
    font-size: 0.88rem;
  }

  .announcement-comment-text {
    margin-top: 0.2rem;
    font-size: 0.9rem;
  }

  .announcement-comment-time {
    margin-top: 0.35rem;
    color: var(--text-muted);
    font-size: 0.74rem;
  }

  .announcement-comment-form {
    display: flex;
    gap: 0.6rem;
    margin: 0;
  }

  :global(.announcement-comment-input) {
    flex: 1;
    background: var(--background);
  }

  .announcement-pagination {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
  }

  .announcement-pagination-copy {
    margin: 0;
    color: var(--text-soft);
  }

  .announcement-pagination-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
  }

  @media (max-width: 767px) {
    .announcement-composer-row,
    .announcement-poll-footer,
    .announcement-composer-actions,
    .announcement-post-actions,
    .announcement-comment-form,
    .announcement-pagination {
      flex-direction: column;
      align-items: stretch;
    }

    .announcement-post-head {
      flex-direction: column;
    }

    .announcement-post-head-side {
      justify-content: flex-start;
    }

    .announcement-poll-duration {
      max-width: none;
    }
  }
</style>
