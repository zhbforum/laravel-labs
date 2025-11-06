<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with('users')->get();           
        $tasksTotal = random_int(5, 8);                      

        for ($i = 0; $i < $tasksTotal; $i++) {
            /** @var \App\Models\Project $project */
            $project  = $projects->random();
            $users    = $project->users;
            $author   = $users->random();
            $assignee = $users->random();

            Task::factory()->create([
                'project_id'  => $project->id,
                'author_id'   => $author->id,
                'assignee_id' => $assignee->id,
            ]);
        }
    }
}
