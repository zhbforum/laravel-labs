<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owners = User::inRandomOrder()->take(4)->get();
        $projects = collect();

        foreach ($owners as $owner) {
            $projects = $projects->merge(
                Project::factory()->count(2)->create(['owner_id' => $owner->id])
            );
        }

        $roles = ['owner', 'admin', 'member', 'viewer'];
        $users = User::all();

        foreach ($projects as $project) {
            $attach = [$project->owner_id => ['role' => 'owner']];

            $others = $users->where('id', '!=', $project->owner_id)->random(3);
            foreach ($others as $u) {
                $attach[$u->id] = ['role' => collect($roles)->except(['owner'])->random()];
            }

            $project->users()->syncWithoutDetaching($attach);
        }
    }
}
