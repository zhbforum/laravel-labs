<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $projects = Project::query()
            ->where("owner_id", $userId)
            ->orWhereHas("users", fn($q) => $q->where("users.id", $userId))
            ->orderByDesc("id")
            ->paginate(15);

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "members" => "array",
            "members.*" => "integer|exists:users,id",
        ]);

        $project = Project::create([
            "name" => $data["name"],
            "owner_id" => $request->user()->id,
        ]);

        if (!empty($data["members"])) {
            $project->users()->sync($data["members"]);
        }

        return response()->json($project, 201);
    }

    public function show(Request $request, int $id)
    {
        $project = Project::with(["owner", "users"])->find($id);
        if (!$project) {
            return response()->json(["message" => "Not found"], 404);
        }

        $userId = $request->user()->id;
        $isOwner = $project->owner_id === $userId;
        $isMember = $project->users->contains("id", $userId);

        if (!$isOwner && !$isMember) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        return response()->json($project);
    }

    public function update(Request $request, int $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(["message" => "Not found"], 404);
        }

        if ($project->owner_id !== $request->user()->id) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        $data = $request->validate([
            "name" => "sometimes|string|max:255",
            "members" => "sometimes|array",
            "members.*" => "integer|exists:users,id",
        ]);

        if (array_key_exists("name", $data)) {
            $project->name = $data["name"];
        }
        $project->save();

        if (array_key_exists("members", $data)) {
            $project->users()->sync($data["members"] ?? []);
        }

        return response()->json($project);
    }

    public function destroy(Request $request, int $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(["message" => "Not found"], 404);
        }

        if ($project->owner_id !== $request->user()->id) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        $project->delete();
        return response()->json(["message" => "Deleted"]);
    }

    public function tasks(Request $request, int $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(["message" => "Not found"], 404);
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

        $tasks = $project
            ->tasks()
            ->with(["author", "assignee"])
            ->orderByDesc("id")
            ->paginate(15);

        return response()->json($tasks);
    }
}
