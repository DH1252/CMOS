<script>
  import { flip } from 'svelte/animate';
  import { useSpring } from '@humanspeak/svelte-motion';
  import { onDestroy, onMount } from 'svelte';
  import { quintOut } from 'svelte/easing';
  import { crossfade, fade, scale } from 'svelte/transition';
  import { subscribeToLiveUpdates } from '$lib/live-updates.js';
  import { dragPreviewSpring, dragRotateSpring } from '$lib/motion.js';
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import { Label } from '$lib/components/ui/label/index.js';
  import { Progress } from '$lib/components/ui/progress/index.js';
  import { Textarea } from '$lib/components/ui/textarea/index.js';
  import Breadcrumbs from '../components/Breadcrumbs.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Kanban',
    description = '',
    breadcrumbs = [],
    refreshUrl = '',
    realtimeSnapshot = '',
    context = {
      type: 'global',
      typeId: null,
    },
    endpoints = {
      storeInline: '/tasks/inline',
      taskBase: '/tasks',
    },
    csrfToken = '',
    users = [],
    columns = [],
  } = $props();

  const statusLabels = {
    todo: 'To Do',
    in_progress: 'In Progress',
    pending: 'Pending',
    done: 'Done',
  };

  const priorityLabels = {
    low: 'Rendah',
    medium: 'Sedang',
    high: 'Tinggi',
  };

  const columnTones = {
    todo: 'secondary',
    in_progress: 'warning',
    pending: 'primary',
    done: 'success',
  };

  const baseDraft = () => ({
    title: '',
    description: '',
    priority: 'medium',
    assigned_to: '',
    deadline: '',
  });

  const taskUrl = (id, suffix = '') => `${endpoints.taskBase}/${id}${suffix}`;

  const normalizeTask = (task) => ({
    ...task,
    showHref: task.showHref || taskUrl(task.id),
  });

  const cloneColumn = (column) => ({
    ...column,
    tasks: [...(column.tasks || [])].map(normalizeTask),
    draft: baseDraft(),
    addOpen: false,
  });

  const hydrateBoard = (nextColumns) => {
    boardColumns = nextColumns.map((column) => {
      const previous = boardColumns.find((item) => item.status === column.status);

      return {
        ...cloneColumn(column),
        draft: previous?.draft ? { ...previous.draft } : baseDraft(),
        addOpen: Boolean(previous?.addOpen),
      };
    });
  };

  let boardColumns = $state([]);
  let draggedTaskId = $state(null);
  let draggedFromStatus = $state(null);
  let dragTargetStatus = $state(null);
  let landedTaskId = $state(null);
  let dragSnapshot = $state(null);
  let dragCommitted = $state(false);
  let editModalOpen = $state(false);
  let editingTask = $state(null);
  let savingEdit = $state(false);
  let dragPreviewNode = null;
  let landedTaskTimer;
  let dragPlacementKey = null;
  let dragPlacementFrame = null;
  let pendingPlacement = null;
  let dragPreviewTargetX = 0;
  let dragPreviewTargetY = 0;
  let dragPreviewTargetAngle = 0;
  let dragPreviewWidth = 0;
  let dragPreviewXValue = 0;
  let dragPreviewYValue = 0;
  let dragPreviewAngleValue = 0;
  let pendingServerRefresh = $state(false);
  let liveUpdatesCleanup = $state(null);
  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;
  const reduceMotion = typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const dragPreviewX = useSpring(0, dragPreviewSpring);
  const dragPreviewY = useSpring(0, dragPreviewSpring);
  const dragPreviewAngle = useSpring(0, dragRotateSpring);
  const [sendTask, receiveTask] = crossfade({
    duration: (distance) => (reduceMotion ? 0 : Math.min(260, Math.sqrt(distance) * 22 + 120)),
    easing: quintOut,
    fallback: (node) => fade(node, { duration: reduceMotion ? 0 : 150 }),
  });

  $effect(() => {
    if (!boardColumns.length && columns.length) {
      hydrateBoard(columns);
    }
  });

  $effect(() => {
    if (pendingServerRefresh && !draggedTaskId && !editModalOpen && !savingEdit) {
      pendingServerRefresh = false;
      void refreshBoard();
    }
  });

  const totalTasks = $derived.by(() =>
    boardColumns.reduce((total, column) => total + column.tasks.length, 0),
  );

  const toast = (text, tone = 'success') => {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: tone === 'error' ? 'error' : 'success',
        title: text,
        showConfirmButton: false,
        timer: 1800,
      });
      return;
    }

    if (tone === 'error') {
      window.alert(text);
    }
  };

  const refreshBoard = async () => {
    if (!refreshUrl) {
      return;
    }

    try {
      const response = await fetch(refreshUrl, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        cache: 'no-store',
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();

      if (Array.isArray(data.columns)) {
        hydrateBoard(data.columns);
      }
    } catch (error) {
      console.error('Failed to refresh task board', error);
    }
  };

  const findColumn = (status) => boardColumns.find((column) => column.status === status);

  const findTask = (id) => {
    for (const column of boardColumns) {
      const task = column.tasks.find((candidate) => candidate.id === id);
      if (task) {
        return task;
      }
    }

    return null;
  };

  const cloneBoardColumns = (columns) =>
    columns.map((column) => ({
      ...column,
      tasks: column.tasks.map((task) => ({ ...task })),
    }));

  const getTaskLocation = (taskId) => {
    for (const column of boardColumns) {
      const index = column.tasks.findIndex((task) => task.id === taskId);

      if (index !== -1) {
        return {
          status: column.status,
          index,
        };
      }
    }

    return null;
  };

  const priorityTone = (priority) => {
    if (priority === 'high') {
      return 'danger';
    }

    if (priority === 'medium') {
      return 'warning';
    }

    return 'info';
  };

  const replaceTask = (updatedTask) => {
    const normalizedTask = normalizeTask(updatedTask);
    const currentLocation = getTaskLocation(normalizedTask.id);

    boardColumns = boardColumns.map((column) => {
      const filtered = column.tasks.filter((task) => task.id !== normalizedTask.id);

      if (column.status === normalizedTask.status) {
        const tasks = [...filtered];
        const insertIndex = currentLocation && currentLocation.status === normalizedTask.status
          ? Math.min(currentLocation.index, tasks.length)
          : 0;

        tasks.splice(insertIndex, 0, normalizedTask);

        return { ...column, tasks };
      }

      return { ...column, tasks: filtered };
    });
  };

  const moveTaskLocally = (taskId, fromStatus, toStatus) => {
    if (fromStatus === toStatus) {
      return;
    }

    let movedTask = null;

    boardColumns = boardColumns.map((column) => {
      if (column.status === fromStatus) {
        const remaining = column.tasks.filter((task) => {
          if (task.id === taskId) {
            movedTask = { ...task, status: toStatus };
            if (toStatus === 'done') {
              movedTask.progress = 100;
            }
            if (toStatus === 'todo' && movedTask.progress > 0) {
              movedTask.progress = 0;
            }
            return false;
          }

          return true;
        });

        return { ...column, tasks: remaining };
      }

      return column;
    });

    if (!movedTask) {
      return;
    }

    boardColumns = boardColumns.map((column) =>
      column.status === toStatus
        ? { ...column, tasks: [movedTask, ...column.tasks] }
        : column,
    );
  };

  const moveTaskPreview = (taskId, toStatus, toIndex) => {
    const currentLocation = getTaskLocation(taskId);

    if (!currentLocation) {
      return;
    }

    const normalizedIndex = Math.max(0, toIndex);

    if (currentLocation.status === toStatus && currentLocation.index === normalizedIndex) {
      return;
    }

    let movedTask = null;

    boardColumns = boardColumns.map((column) => {
      if (column.status !== currentLocation.status) {
        return column;
      }

      const tasks = column.tasks.filter((task) => {
        if (task.id === taskId) {
          movedTask = { ...task, status: toStatus };
          return false;
        }

        return true;
      });

      return { ...column, tasks };
    });

    if (!movedTask) {
      return;
    }

    boardColumns = boardColumns.map((column) => {
      if (column.status !== toStatus) {
        return column;
      }

      const tasks = [...column.tasks];
      const boundedIndex = Math.min(normalizedIndex, tasks.length);
      tasks.splice(boundedIndex, 0, movedTask);

      return {
        ...column,
        tasks,
      };
    });
  };

  const scheduleTaskPreviewMove = (taskId, toStatus, toIndex) => {
    const placementKey = `${taskId}:${toStatus}:${toIndex}`;

    if (dragPlacementKey === placementKey) {
      return;
    }

    dragPlacementKey = placementKey;
    pendingPlacement = { taskId, toStatus, toIndex };

    if (dragPlacementFrame) {
      return;
    }

    dragPlacementFrame = window.requestAnimationFrame(() => {
      dragPlacementFrame = null;

      if (!pendingPlacement) {
        return;
      }

      moveTaskPreview(
        pendingPlacement.taskId,
        pendingPlacement.toStatus,
        pendingPlacement.toIndex,
      );
      pendingPlacement = null;
    });
  };

  const getColumnOrders = () =>
    Object.fromEntries(
      boardColumns.map((column) => [
        column.status,
        column.tasks.map((task) => task.id),
      ]),
    );

  const getDropIndex = (container, clientY) => {
    const cards = [...container.querySelectorAll('[data-task-card="true"]')]
      .filter((card) => Number(card.dataset.taskId) !== draggedTaskId);

    const index = cards.findIndex((card) => {
      const rect = card.getBoundingClientRect();
      return clientY < rect.top + (rect.height / 2);
    });

    return index === -1 ? cards.length : index;
  };

  const createTransparentDragImage = () => {
    if (typeof document === 'undefined') {
      return null;
    }

    const canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;

    return canvas;
  };

  const renderDragPreview = () => {
    if (!dragPreviewNode) {
      return;
    }

    dragPreviewNode.style.transform = `translate3d(${dragPreviewXValue}px, ${dragPreviewYValue}px, 0) rotate(${dragPreviewAngleValue}deg) scale(1.04)`;
  };

  const destroyDragPreview = () => {
    if (dragPlacementFrame) {
      window.cancelAnimationFrame(dragPlacementFrame);
      dragPlacementFrame = null;
    }

    pendingPlacement = null;
    dragPlacementKey = null;

    if (dragPreviewNode) {
      dragPreviewNode.remove();
      dragPreviewNode = null;
    }
  };

  const createDragPreview = (node, startX, startY, angle = -3) => {
    if (reduceMotion || !node) {
      return null;
    }

    const rect = node.getBoundingClientRect();
    const preview = node.cloneNode(true);

    preview.style.position = 'fixed';
    preview.style.top = '0';
    preview.style.left = '0';
    preview.style.width = `${rect.width}px`;
    preview.style.pointerEvents = 'none';
    preview.style.margin = '0';
    preview.style.transformOrigin = 'top center';
    preview.style.transform = `translate3d(${startX}px, ${startY}px, 0) rotate(${angle}deg) scale(1.04)`;
    preview.style.boxShadow = '0 24px 44px rgba(0, 0, 0, 0.24)';
    preview.style.borderColor = 'color-mix(in srgb, var(--brand-primary) 46%, var(--line-soft))';
    preview.style.background = 'color-mix(in srgb, var(--brand-light) 12%, var(--background))';
    preview.style.opacity = '0.98';
    preview.style.zIndex = '9999';
    preview.style.willChange = 'transform';

    document.body.appendChild(preview);

    dragPreviewWidth = rect.width;

    return preview;
  };

  const onDragStart = (event, taskId, status) => {
    draggedTaskId = taskId;
    draggedFromStatus = status;
    dragCommitted = false;
    dragSnapshot = cloneBoardColumns(boardColumns);
    dragPlacementKey = null;
    pendingPlacement = null;

    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = 'move';

      const card = event.currentTarget;
      const rect = card.getBoundingClientRect();
      const horizontalGrabRatio = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
      const previewAngle = (horizontalGrabRatio - 0.5) * 8;
      dragPreviewTargetX = event.clientX - rect.width / 2;
      dragPreviewTargetY = event.clientY + 18;
      dragPreviewTargetAngle = previewAngle;
      dragPreviewX.jump(dragPreviewTargetX);
      dragPreviewY.jump(dragPreviewTargetY);
      dragPreviewAngle.jump(dragPreviewTargetAngle);
      dragPreviewNode = createDragPreview(card, dragPreviewTargetX, dragPreviewTargetY, previewAngle);

      if (dragPreviewNode) {
        const invisibleDragImage = createTransparentDragImage();

        if (invisibleDragImage) {
          event.dataTransfer.setDragImage(invisibleDragImage, 0, 0);
        }
      }
    }
  };

  const onDragMove = (event) => {
    if (!dragPreviewNode || reduceMotion) {
      return;
    }

    if (event.clientX === 0 && event.clientY === 0) {
      return;
    }

    const nextX = event.clientX - dragPreviewWidth / 2;
    const nextY = event.clientY + 18;
    const deltaX = nextX - dragPreviewTargetX;

    dragPreviewTargetX = nextX;
    dragPreviewTargetY = nextY;
    dragPreviewTargetAngle = Math.max(-7, Math.min(7, deltaX * 0.12));
    dragPreviewX.set(dragPreviewTargetX);
    dragPreviewY.set(dragPreviewTargetY);
    dragPreviewAngle.set(dragPreviewTargetAngle);
  };

  const onColumnDragOver = (event, status) => {
    if (!draggedTaskId) {
      return;
    }

    event.preventDefault();
    dragTargetStatus = status;

    const targetIndex = getDropIndex(event.currentTarget, event.clientY);
    scheduleTaskPreviewMove(draggedTaskId, status, targetIndex);
  };

  const onDragEnter = (status) => {
    if (!draggedTaskId || draggedFromStatus === status) {
      dragTargetStatus = null;
      return;
    }

    dragTargetStatus = status;
  };

  const onDragEnd = () => {
    if (!dragCommitted && dragSnapshot) {
      boardColumns = cloneBoardColumns(dragSnapshot);
    }

    draggedTaskId = null;
    draggedFromStatus = null;
    dragTargetStatus = null;
    dragSnapshot = null;
    dragCommitted = false;
    destroyDragPreview();
  };

  const markTaskLanded = (taskId) => {
    if (reduceMotion) {
      return;
    }

    landedTaskId = taskId;

    if (landedTaskTimer) {
      window.clearTimeout(landedTaskTimer);
    }

    landedTaskTimer = window.setTimeout(() => {
      landedTaskId = null;
    }, 280);
  };

  const onDrop = async (status) => {
    if (!draggedTaskId || !draggedFromStatus) {
      draggedTaskId = null;
      draggedFromStatus = null;
      dragTargetStatus = null;
      return;
    }

    const taskId = draggedTaskId;
    const fromStatus = draggedFromStatus;
    const columnOrders = getColumnOrders();
    const changed = JSON.stringify(columnOrders) !== JSON.stringify(
      Object.fromEntries(
        (dragSnapshot || []).map((column) => [
          column.status,
          column.tasks.map((task) => task.id),
        ]),
      ),
    );

    if (!changed) {
      dragCommitted = true;
      draggedTaskId = null;
      draggedFromStatus = null;
      dragTargetStatus = null;
      dragSnapshot = null;
      return;
    }

    dragCommitted = true;
    markTaskLanded(taskId);
    draggedTaskId = null;
    draggedFromStatus = null;
    dragTargetStatus = null;

    try {
      const response = await fetch(taskUrl(taskId, '/status'), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          status,
          column_orders: columnOrders,
        }),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        if (dragSnapshot) {
          boardColumns = cloneBoardColumns(dragSnapshot);
        }
        toast('Gagal update status', 'error');
        return;
      }

      replaceTask({
        ...findTask(taskId),
        ...data.task,
      });
    } catch (error) {
      if (dragSnapshot) {
        boardColumns = cloneBoardColumns(dragSnapshot);
      }
      toast('Gagal update status', 'error');
    } finally {
      dragSnapshot = null;
    }
  };

  const toggleAddForm = (status, open) => {
    boardColumns = boardColumns.map((column) =>
      column.status === status ? { ...column, addOpen: open } : column,
    );
  };

  const updateDraft = (status, field, value) => {
    boardColumns = boardColumns.map((column) =>
      column.status === status
        ? { ...column, draft: { ...column.draft, [field]: value } }
        : column,
    );
  };

  const resetDraft = (status) => {
    boardColumns = boardColumns.map((column) =>
      column.status === status
        ? {
            ...column,
            addOpen: false,
            draft: baseDraft(),
          }
        : column,
    );
  };

  const saveDraft = async (status) => {
    const column = findColumn(status);
    if (!column) {
      return;
    }

    const payload = {
      title: column.draft.title.trim(),
      description: column.draft.description.trim() || null,
      priority: column.draft.priority,
      assigned_to: column.draft.assigned_to || null,
      deadline: column.draft.deadline || null,
      status,
      type: context.type,
      program_id: context.type === 'program' ? context.typeId : null,
      department_id: context.type === 'department' ? context.typeId : null,
    };

    if (!payload.title) {
      toast('Judul tidak boleh kosong', 'error');
      return;
    }

    try {
      const response = await fetch(endpoints.storeInline, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        toast('Gagal menambah task', 'error');
        return;
      }

      boardColumns = boardColumns.map((item) =>
        item.status === status
          ? {
              ...item,
              tasks: [normalizeTask(data.task), ...item.tasks],
            }
          : item,
      );
      markTaskLanded(data.task.id);
      resetDraft(status);
      toast('Task ditambahkan');
    } catch (error) {
      toast('Gagal menambah task', 'error');
    }
  };

  const openEdit = (task) => {
    editingTask = {
      ...task,
      assigned_to: task.assigned_to || '',
      deadline: task.deadline || '',
      progress: Number(task.progress || 0),
    };
    editModalOpen = true;
  };

  const closeEdit = () => {
    if (savingEdit) {
      return;
    }

    editModalOpen = false;
    editingTask = null;
  };

  const saveEdit = async () => {
    if (!editingTask?.title?.trim()) {
      toast('Judul tidak boleh kosong', 'error');
      return;
    }

    savingEdit = true;

    const payload = {
      title: editingTask.title.trim(),
      description: editingTask.description?.trim() || null,
      status: editingTask.status,
      priority: editingTask.priority,
      assigned_to: editingTask.assigned_to || null,
      deadline: editingTask.deadline || null,
      progress: Number(editingTask.progress || 0),
    };

    try {
      const response = await fetch(taskUrl(editingTask.id, '/inline'), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        toast('Gagal menyimpan task', 'error');
        savingEdit = false;
        return;
      }

      replaceTask({
        ...findTask(editingTask.id),
        ...data.task,
      });
      closeEdit();
      toast('Task diupdate');
    } catch (error) {
      toast('Gagal menyimpan task', 'error');
    }

    savingEdit = false;
  };

  const deleteTask = async (taskId) => {
    const task = findTask(taskId);
    if (!task) {
      return;
    }

    const confirmed = window.Swal
      ? await window.Swal
          .fire({
            title: 'Konfirmasi',
            text: `Hapus task ${task.title}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
          })
          .then((result) => result.isConfirmed)
      : window.confirm(`Hapus task ${task.title}?`);

    if (!confirmed) {
      return;
    }

    try {
      const response = await fetch(taskUrl(taskId, '/inline'), {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        toast('Gagal menghapus task', 'error');
        return;
      }

      boardColumns = boardColumns.map((column) => ({
        ...column,
        tasks: column.tasks.filter((item) => item.id !== taskId),
      }));
      if (editingTask?.id === taskId) {
        closeEdit();
      }
      toast(data.message || 'Task dihapus');
    } catch (error) {
      toast('Gagal menghapus task', 'error');
    }
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  onMount(() => {
    if (!realtimeSnapshot) {
      return;
    }

    liveUpdatesCleanup = subscribeToLiveUpdates(
      realtimeSnapshot,
      async ({ changed }) => {
        if (!changed.includes('tasks')) {
          return;
        }

        if (draggedTaskId || editModalOpen || savingEdit) {
          pendingServerRefresh = true;
          return;
        }

        await refreshBoard();
      },
      { interval: 7000 },
    );
  });

  onDestroy(() => {
    liveUpdatesCleanup?.();
  });

  $effect(() => {
    const unsubscribeX = dragPreviewX.subscribe((value) => {
      dragPreviewXValue = Number(value);
      renderDragPreview();
    });

    const unsubscribeY = dragPreviewY.subscribe((value) => {
      dragPreviewYValue = Number(value);
      renderDragPreview();
    });

    const unsubscribeAngle = dragPreviewAngle.subscribe((value) => {
      dragPreviewAngleValue = Number(value);
      renderDragPreview();
    });

    return () => {
      unsubscribeX();
      unsubscribeY();
      unsubscribeAngle();

      if (landedTaskTimer) {
        window.clearTimeout(landedTaskTimer);
      }

      destroyDragPreview();
    };
  });
</script>

<Breadcrumbs items={breadcrumbs} />

<Card.Root class="mb-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-border/70 pb-4">
    <div class="min-w-0 flex-1">
      <PageHeader {title} {description} icon="fas fa-table-columns" />
    </div>

    <div class="grid gap-3 min-w-[15rem]">
      <div class="rounded-[10px] border border-border bg-background px-4 py-4">
        <div class="text-sm text-muted-foreground">Total task aktif</div>
        <div class="mt-2 text-2xl font-semibold leading-none text-foreground">{totalTasks}</div>
      </div>
      <div class="grid gap-2 rounded-[10px] border border-border bg-background px-4 py-4">
        {#each boardColumns as column, index (column.status || index)}
          <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-muted-foreground">{column.label || statusLabels[column.status]}</span>
            <span class="font-medium text-foreground">{column.tasks.length}</span>
          </div>
        {/each}
      </div>
    </div>
  </Card.Header>
</Card.Root>

<div class="flex gap-4 overflow-x-auto py-4 items-start min-h-[500px] snap-x snap-mandatory">
  {#each boardColumns as column, index (column.status || index)}
    <section class={`flex min-h-[32rem] w-[min(320px,88vw)] min-w-[min(320px,88vw)] shrink-0 snap-start flex-col rounded-[10px] border bg-card transition-colors duration-200 ${dragTargetStatus === column.status ? 'border-brand-primary/45 bg-brand-light/8 kanban-column-target' : 'border-border'}`.trim()}>
      <div class={`flex items-center justify-between gap-3 border-b border-border p-4 pb-3 ${column.status === 'todo' ? '' : ''}`}>
        <div class="flex items-center gap-2 font-bold text-sm">
          <span class={`w-3 h-3 rounded-full ${column.status === 'todo' ? 'bg-muted-foreground/30' : column.status === 'in_progress' ? 'bg-yellow-500' : column.status === 'pending' ? 'bg-[var(--brand-primary)]' : 'bg-green-600'}`}></span>
          <span>{column.label || statusLabels[column.status]}</span>
        </div>
        <StatusBadge label={String(column.tasks.length)} tone={columnTones[column.status] || 'secondary'} />
      </div>

      <div
        class="grid gap-3 p-3 min-h-[24rem]"
        role="list"
        aria-label={`Kolom ${column.label || statusLabels[column.status]}`}
        ondragover={(event) => onColumnDragOver(event, column.status)}
        ondragenter={() => onDragEnter(column.status)}
        ondrop={() => void onDrop(column.status)}
      >
        {#each column.tasks as task, taskIndex (task.id || taskIndex)}
          <article
            class={`grid gap-3 rounded-[10px] border border-border bg-background p-4 shadow-none cursor-grab active:cursor-grabbing transition-colors hover:bg-muted/60 ${draggedTaskId === task.id ? 'kanban-card-dragging' : ''} ${landedTaskId === task.id ? 'kanban-card-landed' : ''}`.trim()}
            data-task-card="true"
            data-task-id={task.id}
            draggable="true"
            ondragstart={(event) => onDragStart(event, task.id, column.status)}
            ondrag={onDragMove}
            ondragend={onDragEnd}
            in:receiveTask={{ key: task.id }}
            out:sendTask={{ key: task.id }}
            animate:flip={{ duration: reduceMotion ? 0 : 180, easing: quintOut }}
          >
            <div class="flex items-start gap-2">
              <div class="min-w-0 flex-1">
                <div class={`font-bold leading-tight ${task.status === 'done' ? 'line-through opacity-65' : ''}`.trim()}>
                  {task.title}
                </div>
                {#if task.description}
                  <p class="mt-1 mb-0 text-muted-foreground text-sm leading-relaxed line-clamp-2">{task.description}</p>
                {/if}
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <Button href={task.showHref || taskUrl(task.id)} variant="secondary" size="sm" class="shadow-none px-2.5">
                  <span>Buka</span>
                </Button>
                <DropdownMenu.Root>
                  <DropdownMenu.Trigger>
                    {#snippet child({ props })}
                      <button
                        {...props}
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-border bg-card text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        aria-label="Aksi task"
                      >
                        <i class="fas fa-ellipsis"></i>
                      </button>
                    {/snippet}
                  </DropdownMenu.Trigger>
                  <DropdownMenu.Content align="end" sideOffset={8} class="w-40 p-1">
                    <DropdownMenu.Item onSelect={() => openEdit(task)}>
                      {#snippet child({ props })}
                        <div {...props} class={`flex items-center gap-2 rounded-[8px] px-2 py-2 text-sm ${props.class || ''}`}>
                          <i class="fas fa-pen w-4"></i>
                          <span>Edit task</span>
                        </div>
                      {/snippet}
                    </DropdownMenu.Item>
                    <DropdownMenu.Item onSelect={() => void deleteTask(task.id)}>
                      {#snippet child({ props })}
                        <div {...props} class={`flex items-center gap-2 rounded-[8px] px-2 py-2 text-sm text-[var(--signal-danger)] ${props.class || ''}`}>
                          <i class="fas fa-trash w-4"></i>
                          <span>Hapus task</span>
                        </div>
                      {/snippet}
                    </DropdownMenu.Item>
                  </DropdownMenu.Content>
                </DropdownMenu.Root>
              </div>
            </div>

            <div class="flex flex-wrap gap-2">
              <StatusBadge label={task.priority_label || priorityLabels[task.priority]} tone={priorityTone(task.priority)} />
              {#if task.is_overdue}
                <StatusBadge label="Overdue" tone="danger" />
              {/if}
            </div>

            {#if task.progress > 0}
              <div class="flex items-center gap-2">
                <Progress value={task.progress} class="flex-1 h-2" />
                <span class="min-w-[2.5rem] text-right text-xs text-muted-foreground font-bold">{task.progress}%</span>
              </div>
            {/if}

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 pt-3 border-t border-border/70 text-xs text-muted-foreground">
              <div class="flex items-center gap-2">
                {#if task.assignee_name}
                  {#if task.assignee_avatar}
                    <img src={task.assignee_avatar || fallbackAvatar(task.assignee_name)} alt={task.assignee_name} class="w-5 h-5 rounded-full" width="20" height="20" loading="lazy" decoding="async" onerror={handleImageError} />
                  {/if}
                  <span>{task.assignee_name}</span>
                {:else}
                  <i class="fas fa-user-slash"></i>
                  <span>Unassigned</span>
                {/if}
              </div>

              {#if task.deadline_fmt}
                <div class={`flex items-center gap-2 ${task.is_overdue ? 'text-red-600 font-bold' : ''}`.trim()}>
                  <i class="fas fa-calendar-alt"></i>
                  {task.deadline_fmt}
                </div>
              {/if}
            </div>
          </article>
        {/each}

        {#if !column.tasks.length && !column.addOpen}
          <div class="grid min-h-[8rem] place-items-center gap-2 rounded-[10px] border border-dashed border-border text-center text-muted-foreground p-4">
            <i class="fas fa-inbox text-2xl opacity-50"></i>
            <p class="m-0 text-sm">Belum ada task di kolom ini.</p>
          </div>
        {/if}

        {#if column.addOpen}
          <div class="grid gap-3 rounded-[10px] border border-border bg-background p-4 shadow-none" transition:scale={{ duration: reduceMotion ? 0 : 180, start: 0.98, opacity: 0.35, easing: quintOut }}>
            <div class="grid gap-1.5">
              <Label for={`task-inline-title-${column.status}`}>Judul</Label>
              <Input
                id={`task-inline-title-${column.status}`}
                type="text"
                class="bg-card"
                placeholder="Judul task..."
                value={column.draft.title}
                oninput={(event) => updateDraft(column.status, 'title', event.currentTarget.value)}
              />
            </div>

            <div class="grid gap-1.5">
              <Label for={`task-inline-description-${column.status}`}>Deskripsi</Label>
              <Textarea
                id={`task-inline-description-${column.status}`}
                rows="3"
                class="bg-card"
                placeholder="Deskripsi (opsional)..."
                value={column.draft.description}
                oninput={(event) => updateDraft(column.status, 'description', event.currentTarget.value)}
              />
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
              <div class="grid gap-1.5 flex-1">
                <Label for={`task-inline-priority-${column.status}`}>Prioritas</Label>
                <select
                  id={`task-inline-priority-${column.status}`}
                  class="w-full h-10 rounded-[10px] border border-border bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary"
                  onchange={(event) => updateDraft(column.status, 'priority', event.currentTarget.value)}
                >
                  <option value="medium" selected={column.draft.priority === 'medium'}>Sedang</option>
                  <option value="high" selected={column.draft.priority === 'high'}>Tinggi</option>
                  <option value="low" selected={column.draft.priority === 'low'}>Rendah</option>
                </select>
              </div>

              <div class="grid gap-1.5 flex-1">
                <Label for={`task-inline-assignee-${column.status}`}>Assignee</Label>
                <select
                  id={`task-inline-assignee-${column.status}`}
                  class="w-full h-10 rounded-[10px] border border-border bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary"
                  onchange={(event) => updateDraft(column.status, 'assigned_to', event.currentTarget.value)}
                >
                  <option value="">Unassigned</option>
                  {#each users as user, userIndex (user.value || userIndex)}
                    <option value={user.value} selected={String(column.draft.assigned_to || '') === String(user.value)}>{user.label}</option>
                  {/each}
                </select>
              </div>
            </div>

            <div class="grid gap-1.5">
              <Label for={`task-inline-deadline-${column.status}`}>Deadline</Label>
              <Input
                id={`task-inline-deadline-${column.status}`}
                type="date"
                class="bg-card"
                value={column.draft.deadline}
                oninput={(event) => updateDraft(column.status, 'deadline', event.currentTarget.value)}
              />
            </div>

            <div class="flex flex-col sm:flex-row gap-2 mt-2">
              <Button type="button" size="sm" onclick={() => void saveDraft(column.status)}>
                <i class="fas fa-plus"></i>
                <span>Tambah</span>
              </Button>
              <Button type="button" variant="secondary" size="sm" onclick={() => resetDraft(column.status)}>
                <span>Batal</span>
              </Button>
            </div>
          </div>
        {/if}
      </div>

      <div class="p-3 mt-auto">
        <Button type="button" variant="ghost" class="w-full min-h-[2.8rem] rounded-[10px] border border-dashed border-border font-semibold text-muted-foreground hover:border-border/80 hover:text-foreground" onclick={() => toggleAddForm(column.status, !column.addOpen)}>
          <i class="fas fa-plus"></i>
          <span>{column.addOpen ? 'Tutup Form' : 'Tambah Task'}</span>
        </Button>
      </div>
    </section>
  {/each}
</div>

{#if editModalOpen && editingTask}
  <div class="fixed inset-0 grid place-items-center p-4 bg-background/80 backdrop-blur-sm z-50" transition:fade={{ duration: reduceMotion ? 0 : 140 }}>
    <button type="button" class="absolute inset-0 border-none bg-transparent" aria-label="Tutup modal" onclick={closeEdit}></button>
    <div class="relative z-10 w-full max-w-[38rem] rounded-[10px] border border-border bg-card shadow-lg" role="dialog" aria-modal="true" aria-labelledby="task-edit-title" transition:scale={{ duration: reduceMotion ? 0 : 180, start: 0.98, opacity: 0.45, easing: quintOut }}>
      <div class="flex justify-between items-start gap-4 p-5 border-b border-border/70">
        <div class="min-w-0 flex-1">
          <PageHeader title="Edit Task" description="Perbarui detail task." icon="fas fa-pen-to-square" compact={true} headingTag="h4" />
        </div>
        <Button type="button" variant="secondary" size="icon" class="w-8 h-8 shadow-none" aria-label="Tutup" onclick={closeEdit}>
          <i class="fas fa-times"></i>
        </Button>
      </div>

      <div class="grid gap-4 p-5">
        <div class="grid gap-1.5">
          <Label for="edit-title">Judul</Label>
          <Input id="edit-title" type="text" class="bg-card" bind:value={editingTask.title} />
        </div>

        <div class="grid gap-1.5">
          <Label for="edit-description">Deskripsi</Label>
          <Textarea id="edit-description" rows="4" class="bg-card" bind:value={editingTask.description} />
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
          <div class="grid gap-1.5 flex-1">
            <Label for="edit-status">Status</Label>
            <select id="edit-status" class="w-full h-10 rounded-[10px] border border-border bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" bind:value={editingTask.status}>
              <option value="todo">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="pending">Pending</option>
              <option value="done">Done</option>
            </select>
          </div>

          <div class="grid gap-1.5 flex-1">
            <Label for="edit-priority">Prioritas</Label>
            <select id="edit-priority" class="w-full h-10 rounded-[10px] border border-border bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" bind:value={editingTask.priority}>
              <option value="low">Rendah</option>
              <option value="medium">Sedang</option>
              <option value="high">Tinggi</option>
            </select>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
          <div class="grid gap-1.5 flex-1">
            <Label for="edit-assignee">Assignee</Label>
            <select id="edit-assignee" class="w-full h-10 rounded-[10px] border border-border bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary" bind:value={editingTask.assigned_to}>
              <option value="">Unassigned</option>
              {#each users as user, userIndex (user.value || userIndex)}
                <option value={user.value}>{user.label}</option>
              {/each}
            </select>
          </div>

          <div class="grid gap-1.5 flex-1">
            <Label for="edit-deadline">Deadline</Label>
            <Input id="edit-deadline" type="date" class="bg-card" bind:value={editingTask.deadline} />
          </div>
        </div>

        <div class="grid gap-1.5 pt-2">
          <div class="flex items-center justify-between gap-3">
            <Label for="edit-progress">Progress</Label>
            <span class="text-sm font-bold text-muted-foreground">{editingTask.progress}%</span>
          </div>
          <Progress value={Number(editingTask.progress || 0)} class="h-2" />
          <input id="edit-progress" type="range" min="0" max="100" step="5" class="mt-2 w-full accent-[var(--brand-primary)]" bind:value={editingTask.progress} />
        </div>
      </div>

      <div class="flex flex-col items-center justify-between gap-3 border-t border-border bg-muted/20 p-5 sm:flex-row rounded-b-[10px]">
        <Button type="button" variant="destructive" class="w-full sm:w-auto" onclick={() => void deleteTask(editingTask.id)}>
          <i class="fas fa-trash"></i>
          <span>Hapus</span>
        </Button>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
          <Button type="button" variant="secondary" class="w-full sm:w-auto" onclick={closeEdit}>Batal</Button>
          <Button type="button" class="w-full sm:w-auto" disabled={savingEdit} onclick={() => void saveEdit()}>
            <i class={`fas ${savingEdit ? 'fa-spinner fa-spin' : 'fa-save'}`}></i>
            <span>Simpan</span>
          </Button>
        </div>
      </div>
    </div>
  </div>
{/if}

<style>
  .kanban-column-target {
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--brand-primary) 28%, transparent);
  }

  .kanban-card-dragging {
    opacity: 0.38;
    transform: scale(0.975);
    border-color: color-mix(in srgb, var(--brand-primary) 40%, var(--line-soft));
    background: color-mix(in srgb, var(--brand-light) 10%, var(--background));
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  }

  .kanban-card-landed {
    animation: kanbanTaskLand 260ms cubic-bezier(0.22, 1, 0.36, 1);
  }

  @keyframes kanbanTaskLand {
    0% {
      transform: translateY(-8px) scale(1.01);
      box-shadow: 0 10px 18px rgba(0, 0, 0, 0.12);
      background: color-mix(in srgb, var(--brand-light) 16%, var(--background));
    }

    55% {
      transform: translateY(1px) scale(0.998);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
    }

    100% {
      transform: translateY(0) scale(1);
      box-shadow: none;
      background: var(--background);
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .kanban-column-target {
      box-shadow: none;
    }

    .kanban-card-dragging {
      transform: none;
      box-shadow: none;
    }

    .kanban-card-landed {
      animation: none;
    }
  }
</style>
