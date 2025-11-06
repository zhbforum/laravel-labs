<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Task;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = Task::with('project.users')->inRandomOrder()->get();
        $commentsTotal = random_int(5, 8);

        for ($i = 0; $i < $commentsTotal; $i++) {
            $task   = $tasks->random();
            $author = $task->project->users->random();

            Comment::factory()->create([
                'task_id'   => $task->id,
                'author_id' => $author->id,
            ]);
        }
    }
}
