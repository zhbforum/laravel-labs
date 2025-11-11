<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, int $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(["message" => "Project not found"], 404);
        }

        $userId = $request->user()->id;
        $isOwner = $project->owner_id === $userId;
        $isMember = $project
            ->users()
            ->where("users.id", $userId)
            ->exists();

        if (!$isOwner && !$isMember) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        $data = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:2000",
            "status" => "nullable|string|max:50",
            "priority" => "nullable|integer|between:1,5",
            "due_date" => "nullable|date",
            "assignee_id" => "nullable|integer|exists:users,id",
        ]);

        $task = Task::create([
            "project_id" => $project->id,
            "author_id" => $userId,
            "assignee_id" => $data["assignee_id"] ?? $userId,
            "title" => $data["title"],
            "description" => $data["description"] ?? "",
            "status" => $data["status"] ?? "todo",
            "priority" => $data["priority"] ?? 1,
            "due_date" => $data["due_date"] ?? now()->toDateString(),
        ]);

        return response()->json($task, 201);
    }

    public function show(Request $request, int $id)
    {
        $task = Task::with(["project", "author", "assignee"])->find($id);
        if (!$task) {
            return response()->json(["message" => "Not found"], 404);
        }

        $project = $task->project;
        $userId = $request->user()->id;
        $isOwner = $project->owner_id === $userId;
        $isMember = $project
            ->users()
            ->where("users.id", $userId)
            ->exists();

        if (!$isOwner && !$isMember) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        return response()->json($task);
    }

    public function update(Request $request, int $id)
    {
        $task = Task::with("project")->find($id);
        if (!$task) {
            return response()->json(["message" => "Not found"], 404);
        }

        $userId = $request->user()->id;
        $isOwner = $task->project->owner_id === $userId;
        $isAuthor = $task->author_id === $userId;

        if (!$isOwner && !$isAuthor) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        $data = $request->validate([
            "title" => "sometimes|string|max:255",
            "description" => "sometimes|nullable|string|max:2000",
            "status" => "sometimes|string|max:50",
            "priority" => "sometimes|nullable|integer|between:1,5",
            "due_date" => "sometimes|nullable|date",
            "assignee_id" => "sometimes|nullable|integer|exists:users,id",
        ]);

        $task->fill($data)->save();

        return response()->json($task);
    }

    public function destroy(Request $request, int $id)
    {
        $task = Task::with("project")->find($id);
        if (!$task) {
            return response()->json(["message" => "Not found"], 404);
        }

        $userId = $request->user()->id;
        if (
            $task->author_id !== $userId &&
            $task->project->owner_id !== $userId
        ) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        $task->delete();

        return response()->json(["message" => "Deleted"]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $q = Task::query()
            ->with(["project", "author", "assignee"])
            ->whereHas("project", function ($qq) use ($user) {
                $qq->where("owner_id", $user->id)->orWhereHas(
                    "users",
                    fn($m) => $m->where("users.id", $user->id)
                );
            });

        if ($s = $request->query("status")) {
            $q->where("status", $s);
        }
        if ($assignee = $request->query("assignee_id")) {
            $q->where("assignee_id", (int) $assignee);
        }
        if ($pid = $request->query("project_id")) {
            $q->where("project_id", (int) $pid);
        }

        return response()->json($q->orderByDesc("id")->paginate(15));
    }
}
