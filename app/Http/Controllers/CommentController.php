<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, int $id)
    {
        $task = Task::with('project')->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $userId   = $request->user()->id;
        $isOwner  = $task->project->owner_id === $userId;
        $isMember = $task->project->users()->where('users.id', $userId)->exists();
        if (!$isOwner && !$isMember) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comments = $task->comments()->with('author')->orderBy('id')->paginate(20);
        return response()->json($comments);
    }

    public function store(Request $request, int $id)
    {
        $task = Task::with('project')->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $userId   = $request->user()->id;
        $isOwner  = $task->project->owner_id === $userId;
        $isMember = $task->project->users()->where('users.id', $userId)->exists();
        if (!$isOwner && !$isMember) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = Comment::create([
            'task_id'   => $task->id,
            'author_id' => $userId,
            'body'      => $data['body'],
        ]);

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, int $id)
    {
        $comment = Comment::find($id);
        if (!$comment) return response()->json(['message' => 'Not found'], 404);

        if ($comment->author_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
