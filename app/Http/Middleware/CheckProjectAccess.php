<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = (int) ($request->route('id') ?? $request->route('project'));
        if (!$projectId) {
            return response()->json(['message' => 'Project id required'], 400);
        }

        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $userId = (int) $request->user()->id;
        $isOwner = $project->owner_id === $userId;
        $isMember = $project->users()->where('users.id', $userId)->exists();

        if (!$isOwner && !$isMember) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->attributes->set('project', $project);
        return $next($request);
    }
}
