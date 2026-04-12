<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Program;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Landing page - Show all department cards + Global Tasks card
     * Kabinet/Staff: Redirect to their department
     * Admin/BPH: See all departments
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Kabinet and Staff: redirect to their department
        if ($user->hasRole(['kabinet', 'staff']) && $user->department_id) {
            return redirect()->route('tasks.department', $user->department_id);
        }

        // Admin and BPH: show all departments
        $departments = Department::with('cabinet')->withCount([
            'tasks as total_tasks' => fn ($q) => $q->whereNull('program_id'),
            'tasks as pending_tasks' => fn ($q) => $q->whereNull('program_id')->where('status', '!=', 'done'),
        ])->orderBy('name')->get();

        $globalTasksCount = Task::global()->count();
        $globalPendingCount = Task::global()->where('status', '!=', 'done')->count();

        return view('tasks.departments', compact('departments', 'globalTasksCount', 'globalPendingCount'));
    }

    /**
     * Global tasks kanban board
     */
    public function global()
    {
        $tasks = Task::global()
            ->with(['assignee', 'creator'])
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        return view('tasks.kanban', [
            'tasks' => $tasks,
            'users' => $users,
            'title' => 'Global Tasks',
            'backUrl' => route('tasks.index'),
            'createUrl' => route('tasks.create', ['type' => 'global']),
            'type' => 'global',
            'typeId' => null,
        ]);
    }

    /**
     * Department view - Show program cards + Department Tasks card
     * Kabinet/Staff can only access their own department
     */
    public function department(Department $department)
    {
        $programs = $department->programs()
            ->withCount([
                'tasks as total_tasks',
                'tasks as pending_tasks' => fn ($q) => $q->where('status', '!=', 'done'),
                'tasks as done_tasks' => fn ($q) => $q->where('status', 'done'),
            ])
            ->orderBy('name')
            ->get();

        $departmentTasks = Task::forDepartment($department->id);

        $deptTasksCount = (clone $departmentTasks)->count();
        $deptPendingCount = (clone $departmentTasks)->where('status', '!=', 'done')->count();
        $deptDoneCount = (clone $departmentTasks)->done()->count();

        return view('tasks.programs', compact('department', 'programs', 'deptTasksCount', 'deptPendingCount', 'deptDoneCount'));
    }

    /**
     * Department tasks kanban board
     * Kabinet/Staff can only access their own department
     */
    public function departmentTasks(Department $department)
    {
        $tasks = Task::forDepartment($department->id)
            ->with(['assignee', 'creator'])
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        return view('tasks.kanban', [
            'tasks' => $tasks,
            'users' => $users,
            'title' => "Tugas {$department->name}",
            'backUrl' => route('tasks.department', $department),
            'createUrl' => route('tasks.create', ['type' => 'department', 'id' => $department->id]),
            'type' => 'department',
            'typeId' => $department->id,
            'department' => $department,
        ]);
    }

    /**
     * Program tasks kanban board
     */
    public function program(Program $program)
    {
        $tasks = Task::forProgram($program->id)
            ->with(['assignee', 'creator'])
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $users = User::active()->with('role')->orderBy('name')->get();

        return view('tasks.kanban', [
            'tasks' => $tasks,
            'users' => $users,
            'title' => $program->name,
            'backUrl' => route('tasks.department', $program->department),
            'createUrl' => route('tasks.create', ['type' => 'program', 'id' => $program->id]),
            'type' => 'program',
            'typeId' => $program->id,
            'program' => $program,
        ]);
    }

    /**
     * Create task form
     */
    public function create(Request $request)
    {
        $type = $request->get('type');
        $typeId = $request->get('id');

        if (! $type && $request->filled('program_id')) {
            $type = 'program';
            $typeId = $request->get('program_id');
        } elseif (! $type && $request->filled('department_id')) {
            $type = 'department';
            $typeId = $request->get('department_id');
        }

        $type = $type ?: 'program';

        $users = User::active()->with('role')->orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('tasks.create', compact('type', 'typeId', 'users', 'programs', 'departments'));
    }

    /**
     * Store new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'program_id' => 'required_if:type,program|nullable|exists:programs,id',
            'department_id' => 'required_if:type,department|nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['type'] === 'program' ? $validated['program_id'] : null,
            'department_id' => $validated['type'] === 'department' ? $validated['department_id'] : null,
            'is_global' => $validated['type'] === 'global',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'status' => 'todo',
            'sort_order' => $this->nextSortOrder($validated['type'], $validated['type'] === 'program' ? $validated['program_id'] : ($validated['type'] === 'department' ? $validated['department_id'] : null), 'todo'),
            'priority' => $validated['priority'],
            'progress' => 0,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        ActivityLog::log('created', "Created task: {$task->title}", $task);

        // Redirect back to appropriate kanban
        $redirectUrl = match ($validated['type']) {
            'global' => route('tasks.global'),
            'department' => route('tasks.department.tasks', $validated['department_id']),
            'program' => route('tasks.program', $validated['program_id']),
        };

        return redirect($redirectUrl)->with('success', 'Task berhasil ditambahkan!');
    }

    /**
     * Show task detail
     */
    public function show(Task $task)
    {
        $task->load(['program.department', 'department', 'assignee.role', 'creator']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Update task (full update)
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,pending,done',
            'priority' => 'required|in:low,medium,high',
            'progress' => 'required|integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        // Auto-update status based on progress
        if ($validated['progress'] == 100) {
            $validated['status'] = 'done';
        }

        $task->update($validated);

        ActivityLog::log('updated', "Updated task: {$task->title}", $task);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task berhasil diupdate!');
    }

    /**
     * Update task status via drag-drop (AJAX)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,pending,done',
            'column_orders' => 'sometimes|array',
            'column_orders.*' => 'array',
        ]);

        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $validated): void {
            $task->status = $validated['status'];

            if ($validated['status'] === 'done') {
                $task->progress = 100;
            } elseif ($validated['status'] === 'todo' && $task->progress > 0) {
                $task->progress = 0;
            }

            $task->save();

            if (! empty($validated['column_orders'])) {
                $this->applyColumnOrders($task, $validated['column_orders']);
            }
        });

        ActivityLog::log('updated', "Changed task status: {$task->title} from {$oldStatus} to {$task->status}", $task);

        $task->load(['assignee', 'creator']);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, Task $task)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->progress = $validated['progress'];

        // Auto-update status
        if ($task->progress == 100) {
            $task->status = 'done';
        } elseif ($task->progress > 0 && $task->status === 'todo') {
            $task->status = 'in_progress';
        }

        $task->save();

        ActivityLog::log('updated', "Updated progress for task: {$task->title} to {$task->progress}%", $task);

        return back()->with('success', 'Progress berhasil diupdate!');
    }

    /**
     * Delete task
     */
    public function destroy(Task $task)
    {
        $title = $task->title;

        ActivityLog::log('deleted', "Deleted task: {$title}", $task);

        $task->delete();

        return back()->with('success', "Task {$title} berhasil dihapus!");
    }

    /**
     * Inline store — JSON response for kanban board
     */
    public function storeInline(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:global,department,program',
            'program_id' => 'required_if:type,program|nullable|exists:programs,id',
            'department_id' => 'required_if:type,department|nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:todo,in_progress,pending,done',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['type'] === 'program' ? $validated['program_id'] : null,
            'department_id' => $validated['type'] === 'department' ? $validated['department_id'] : null,
            'is_global' => $validated['type'] === 'global',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'status' => $validated['status'] ?? 'todo',
            'sort_order' => $this->nextSortOrder($validated['type'], $validated['type'] === 'program' ? $validated['program_id'] : ($validated['type'] === 'department' ? $validated['department_id'] : null), $validated['status'] ?? 'todo'),
            'priority' => $validated['priority'],
            'progress' => 0,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        ActivityLog::log('created', "Created task: {$task->title}", $task);

        $task->load(['assignee', 'creator']);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Inline update — JSON response for kanban board
     */
    public function updateInline(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:todo,in_progress,pending,done',
            'priority' => 'sometimes|required|in:low,medium,high',
            'progress' => 'sometimes|required|integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        // Auto-update status based on progress
        if (isset($validated['progress'])) {
            if ($validated['progress'] == 100) {
                $validated['status'] = 'done';
            } elseif ($validated['progress'] > 0 && $task->status === 'todo') {
                $validated['status'] = 'in_progress';
            }
        }

        $task->update($validated);
        $task->load(['assignee', 'creator']);

        ActivityLog::log('updated', "Updated task: {$task->title}", $task);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    /**
     * Inline destroy — JSON response for kanban board
     */
    public function destroyInline(Task $task)
    {
        $title = $task->title;
        ActivityLog::log('deleted', "Deleted task: {$title}", $task);
        $task->delete();

        return response()->json(['success' => true, 'message' => "Task \"{$title}\" dihapus."]);
    }

    /**
     * Format task for JSON response
     */
    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'sort_order' => $task->sort_order,
            'priority' => $task->priority,
            'priority_label' => $task->priority_label,
            'progress' => $task->progress,
            'deadline' => $task->deadline ? $task->deadline->format('Y-m-d') : null,
            'deadline_fmt' => $task->deadline ? $task->deadline->format('d M Y') : null,
            'is_overdue' => $task->is_overdue,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'assignee_avatar' => $task->assignee?->avatar_url,
            'created_by' => $task->created_by,
            'creator_name' => $task->creator?->name,
            'showHref' => route('tasks.show', $task),
        ];
    }

    private function nextSortOrder(string $type, ?int $typeId, string $status): int
    {
        $query = Task::query()->where('status', $status);

        if ($type === 'global') {
            $query->where('is_global', true);
        } elseif ($type === 'department') {
            $query->where('department_id', $typeId)->whereNull('program_id');
        } else {
            $query->where('program_id', $typeId);
        }

        return ((int) $query->max('sort_order')) + 1;
    }

    private function applyColumnOrders(Task $task, array $columnOrders): void
    {
        $contextQuery = $this->contextTaskQuery($task);

        foreach ($columnOrders as $status => $orderedIds) {
            if (! in_array($status, Task::STATUSES, true) || ! is_array($orderedIds)) {
                continue;
            }

            $allowedIds = (clone $contextQuery)
                ->where('status', $status)
                ->pluck('id')
                ->map(fn (mixed $id) => (int) $id)
                ->all();

            $filteredIds = array_values(array_filter(
                array_map(static fn (mixed $id): int => (int) $id, $orderedIds),
                static fn (int $id): bool => in_array($id, $allowedIds, true),
            ));

            $count = count($filteredIds);

            foreach ($filteredIds as $index => $id) {
                Task::query()
                    ->whereKey($id)
                    ->update(['sort_order' => $count - $index]);
            }
        }
    }

    private function contextTaskQuery(Task $task)
    {
        $query = Task::query();

        if ($task->is_global) {
            return $query->where('is_global', true);
        }

        if ($task->program_id) {
            return $query->where('program_id', $task->program_id);
        }

        return $query->where('department_id', $task->department_id)->whereNull('program_id');
    }
}
